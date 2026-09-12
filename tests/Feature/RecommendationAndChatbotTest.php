<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Category;
use App\Models\User;
use App\Models\UserProfile;
use App\Services\AIService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RecommendationAndChatbotTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_data_changes_recommendation_score_and_ranking(): void
    {
        $user = User::factory()->create();
        $cat1 = Category::create(['name'=>'Programming','slug'=>'programming']);
        $cat2 = Category::create(['name'=>'Science','slug'=>'science']);
        $book1 = Book::create(['title'=>'Python Machine Learning','slug'=>'python-ml-abcde','category_id'=>$cat1->id,'description'=>'python machine learning algorithms','total_copies'=>1,'available_copies'=>1,'language'=>'en']);
        $book2 = Book::create(['title'=>'Astronomy','slug'=>'astronomy-abcde','category_id'=>$cat2->id,'description'=>'stars and galaxies','total_copies'=>1,'available_copies'=>1,'language'=>'en']);
        UserProfile::create(['user_id'=>$user->id,'interests'=>['python','machine learning'],'preferred_category_ids'=>[$cat1->id]]);
        $this->actingAs($user)->get(route('recommendations.index'))->assertOk()->assertSee('Python Machine Learning');
        $this->assertTrue(app(\App\Services\RecommendationService::class)->matchPercentage($user->fresh(),$book1) > app(\App\Services\RecommendationService::class)->matchPercentage($user->fresh(),$book2));
    }

    public function test_chatbot_requires_auth_and_regular_user_cannot_request_admin_data(): void
    {
        $this->postJson(route('chatbot.send'), ['message'=>'recommend books'])->assertUnauthorized();
        $user = User::factory()->create(['role'=>'user']);
        $this->actingAs($user)->postJson(route('chatbot.send'), ['message'=>'How many registered users are there?'])->assertOk()->assertJson(['rejected'=>true]);
    }

    public function test_chatbot_fallback_only_contains_the_authenticated_users_profile(): void
    {
        $user = User::factory()->create(['role'=>'user']);
        $other = User::factory()->create(['role'=>'user']);
        \App\Models\UserProfile::create(['user_id'=>$user->id,'interests'=>['python']]);
        \App\Models\UserProfile::create(['user_id'=>$other->id,'interests'=>['secret-private-topic']]);
        $this->actingAs($user)->postJson(route('chatbot.send'), ['message'=>'recommend books about python'])
            ->assertOk()->assertJsonMissing(['response'=>'secret-private-topic']);
        $message = $user->chatMessages()->latest()->first();
        $this->assertStringNotContainsString('secret-private-topic', $message->response);
    }

    public function test_spoofed_role_does_not_bypass_chatbot_boundary(): void
    {
        $user = User::factory()->create(['role'=>'user']);
        $response = $this->actingAs($user)->postJson(route('chatbot.send'), ['message'=>'admin statistics','role'=>'admin','is_admin'=>true]);
        $response->assertOk()->assertJson(['rejected'=>true]);
    }

    public function test_ai_fallback_works_without_provider_and_does_not_query_database_itself(): void
    {
        $service = new AIService();
        $response = $service->generate(
            'tell me about it',
            'book_search',
            ['books' => [['title' => 'Clean Code', 'authors' => '', 'category' => '', 'available_copies' => 1, 'total_copies' => 1]]],
        );
        $this->assertStringContainsString('Clean Code', $response);
        $this->assertStringContainsString('Clean Code', $response);
    }
}
