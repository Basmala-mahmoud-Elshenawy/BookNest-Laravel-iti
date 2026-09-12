<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Borrowing;
use App\Models\Category;
use App\Models\Favorite;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class LibraryWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private function book(int $copies = 2): Book
    {
        $category = Category::create(['name' => 'Programming', 'slug' => 'programming', 'description' => null]);
        return Book::create(['title' => 'Clean Code', 'slug' => 'clean-code-abcde', 'category_id' => $category->id, 'description' => 'Software design and programming', 'total_copies' => $copies, 'available_copies' => $copies, 'language' => 'en']);
    }

    public function test_registration_and_login_work(): void
    {
        $this->post(route('register'), ['name'=>'Reader','email'=>'reader@example.com','password'=>'password123','password_confirmation'=>'password123'])->assertRedirect(route('dashboard'));
        $this->assertAuthenticated();
        $this->post(route('logout'))->assertRedirect('/');
        $this->assertGuest();
        $this->post(route('login'), ['email'=>'reader@example.com','password'=>'password123'])->assertRedirect(route('dashboard'));
        $this->assertAuthenticated();
    }

    public function test_guest_cannot_borrow_and_is_redirected_to_login(): void
    {
        $book = $this->book();
        $this->post(route('borrowings.borrow', $book))->assertRedirect(route('login'));
        $this->assertDatabaseCount('borrowings', 0);
    }

    public function test_user_can_borrow_available_book_and_cannot_duplicate(): void
    {
        $user = User::factory()->create(['role'=>'user']);
        $book = $this->book(2);
        $this->actingAs($user)->post(route('borrowings.borrow',$book))->assertSessionHas('status');
        $this->assertDatabaseHas('borrowings', ['user_id'=>$user->id,'book_id'=>$book->id,'status'=>'active']);
        $this->assertDatabaseHas('books', ['id'=>$book->id,'available_copies'=>1]);
        $this->actingAs($user)->post(route('borrowings.borrow',$book))->assertSessionHasErrors('borrow');
        $this->assertDatabaseCount('borrowings', 1);
    }

    public function test_unavailable_book_cannot_be_borrowed(): void
    {
        $user = User::factory()->create(); $book = $this->book(1); $book->update(['available_copies'=>0]);
        $this->actingAs($user)->post(route('borrowings.borrow',$book))->assertSessionHasErrors('borrow');
        $this->assertDatabaseCount('borrowings', 0);
    }

    public function test_return_increments_copies_and_invalid_return_is_rejected(): void
    {
        $user = User::factory()->create(); $book = $this->book(1);
        $borrowing = Borrowing::create(['user_id'=>$user->id,'book_id'=>$book->id,'borrowed_at'=>now()->subDay(),'due_at'=>now()->addDay(),'status'=>'active']);
        $book->update(['available_copies'=>0]);
        $this->actingAs($user)->post(route('borrowings.return',$borrowing))->assertSessionHas('status');
        $this->assertDatabaseHas('borrowings',['id'=>$borrowing->id,'status'=>'returned']);
        $this->assertDatabaseHas('books',['id'=>$book->id,'available_copies'=>1]);
        $this->actingAs($user)->post(route('borrowings.return',$borrowing))->assertSessionHasErrors('return');
    }

    public function test_overdue_is_derived_from_due_date(): void
    {
        $user = User::factory()->create(); $book = $this->book();
        $borrowing = Borrowing::create(['user_id'=>$user->id,'book_id'=>$book->id,'borrowed_at'=>now()->subDays(20),'due_at'=>now()->subDay(),'status'=>'active']);
        $this->assertSame('overdue', $borrowing->status);
        $this->actingAs($user)->get(route('borrowings.index'))->assertSee('Overdue');
    }

    public function test_favorites_are_private_and_duplicate_safe(): void
    {
        $user = User::factory()->create(); $other = User::factory()->create(); $book = $this->book();
        $this->actingAs($user)->post(route('favorites.toggle',$book))->assertSessionHas('status');
        $this->assertDatabaseCount('favorites',1);
        $this->actingAs($user)->post(route('favorites.toggle',$book));
        $this->assertDatabaseCount('favorites',0);
        $favorite = Favorite::create(['user_id'=>$other->id,'book_id'=>$book->id]);
        $this->actingAs($user)->delete(route('favorites.destroy',$favorite))->assertForbidden();
        $this->assertDatabaseHas('favorites',['id'=>$favorite->id]);
    }

    public function test_admin_can_manage_borrowings_and_normal_user_cannot(): void
    {
        $user = User::factory()->create(); $admin = User::factory()->create(['role'=>'admin']); $book = $this->book();
        $borrowing = Borrowing::create(['user_id'=>$user->id,'book_id'=>$book->id,'borrowed_at'=>now(),'due_at'=>now()->addDays(14),'status'=>'active']);
        $book->update(['available_copies'=>0]);
        $this->actingAs($user)->get(route('admin.borrowings.index'))->assertForbidden();
        $this->actingAs($admin)->get(route('admin.borrowings.index'))->assertOk()->assertSee($user->name);
        $this->actingAs($admin)->post(route('admin.borrowings.return',$borrowing))->assertSessionHas('status');
        $this->assertDatabaseHas('borrowings',['id'=>$borrowing->id,'status'=>'returned']);
        $this->assertDatabaseHas('books',['id'=>$book->id,'available_copies'=>1]);
    }

    public function test_search_covers_title_description_isbn_author_and_category(): void
    {
        $category = Category::create(['name'=>'Artificial Intelligence','slug'=>'artificial-intelligence']);
        $book = Book::create(['title'=>'Neural Systems','slug'=>'neural-systems-abcde','category_id'=>$category->id,'description'=>'Deep learning guide','isbn'=>'1234567890','total_copies'=>1,'available_copies'=>1,'language'=>'en']);
        $book->authors()->create(['name'=>'Ada Lovelace','slug'=>'ada-lovelace']);
        foreach (['Neural','Deep learning','1234567890','Ada Lovelace','Artificial Intelligence'] as $term) {
            $this->get(route('books.index',['q'=>$term]))->assertOk()->assertSee('Neural Systems');
        }
    }
}
