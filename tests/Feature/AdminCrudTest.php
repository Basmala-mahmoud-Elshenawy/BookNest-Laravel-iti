<?php

namespace Tests\Feature;

use App\Models\Author;
use App\Models\Book;
use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminCrudTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User { return User::factory()->create(['role' => 'admin']); }

    public function test_admin_can_crud_categories_and_authors(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin)->post(route('admin.categories.store'), ['name'=>'Science','description'=>'Science'])->assertRedirect(route('admin.categories.index'));
        $category = Category::where('name','Science')->firstOrFail();
        $this->actingAs($admin)->put(route('admin.categories.update',$category), ['name'=>'Natural Science','description'=>'Updated'])->assertRedirect();
        $authorResponse = $this->actingAs($admin)->post(route('admin.authors.store'), ['name'=>'Ada Lovelace','bio'=>'Pioneer'])->assertRedirect(route('admin.authors.index'));
        $author = Author::where('name','Ada Lovelace')->firstOrFail();
        $this->assertSame('ada-lovelace', $author->slug);
        $this->actingAs($admin)->put(route('admin.authors.update',$author), ['name'=>'Ada Byron','bio'=>'Updated'])->assertRedirect();
        $this->assertDatabaseHas('authors',['id'=>$author->id,'slug'=>'ada-byron']);
        $this->actingAs($admin)->delete(route('admin.authors.destroy',$author))->assertRedirect();
        $this->assertDatabaseMissing('authors',['id'=>$author->id]);
    }

    public function test_author_with_books_cannot_be_deleted(): void
    {
        $admin = $this->admin();
        $author = Author::create(['name'=>'Test Author','slug'=>'test-author']);
        $category = Category::create(['name'=>'Books','slug'=>'books']);
        $book = Book::create(['title'=>'Test Book','slug'=>'test-book-abcde','category_id'=>$category->id,'total_copies'=>1,'available_copies'=>1,'language'=>'en']);
        $book->authors()->attach($author);
        $this->actingAs($admin)->delete(route('admin.authors.destroy',$author))->assertSessionHasErrors('author');
        $this->assertDatabaseHas('authors',['id'=>$author->id]);
    }

    public function test_admin_can_crud_books_and_public_category_browsing_works(): void
    {
        $admin = $this->admin();
        $category = Category::create(['name'=>'Science','slug'=>'science']);
        $this->get(route('categories.index'))->assertOk();
        $this->actingAs($admin)->post(route('admin.books.store'), [
            'title'=>'Admin Created Book','authors'=>'Test Author','category_id'=>$category->id,'description'=>'A real test book',
            'isbn'=>'9999999999','published_at'=>'2025-01-01','total_copies'=>3,'available_copies'=>3,
        ])->assertRedirect(route('admin.books.index'));
        $book = Book::where('title','Admin Created Book')->firstOrFail();
        $this->actingAs($admin)->get(route('admin.books.edit',$book))->assertOk();
        $this->actingAs($admin)->put(route('admin.books.update',$book), [
            'title'=>'Updated Book','authors'=>'Test Author','category_id'=>$category->id,'description'=>'Updated',
            'isbn'=>'9999999999','published_at'=>'2025-01-01','total_copies'=>3,'available_copies'=>3,
        ])->assertRedirect(route('admin.books.index'));
        $this->actingAs($admin)->delete(route('admin.books.destroy',$book->fresh()))->assertRedirect(route('admin.books.index'));
    }

    public function test_normal_user_gets_403_on_every_admin_resource(): void
    {
        $user = User::factory()->create(['role'=>'user']);
        foreach ([
            ['get', route('admin.books.index')],
            ['get', route('admin.categories.index')],
            ['get', route('admin.authors.index')],
            ['get', route('admin.users.index')],
            ['get', route('admin.borrowings.index')],
        ] as [$method,$url]) {
            $this->actingAs($user)->{$method}($url)->assertForbidden();
        }
    }
}
