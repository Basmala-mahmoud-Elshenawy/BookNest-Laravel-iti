<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Proves the "AI must never control authorization" requirement at the
 * HTTP layer: a regular user hitting an admin route or the admin-only
 * chatbot intent is rejected by the backend, regardless of what the
 * request body claims.
 */
class AdminAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_admin_dashboard(): void
    {
        $this->get(route('admin.dashboard'))->assertRedirect(route('login'));
    }

    public function test_regular_user_cannot_access_admin_dashboard(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $this->actingAs($user)->get(route('admin.dashboard'))->assertForbidden();
    }

    public function test_admin_can_access_admin_dashboard(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->get(route('admin.dashboard'))->assertOk();
    }

    public function test_regular_user_chatbot_request_for_admin_data_is_rejected(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $response = $this->actingAs($user)
            ->postJson(route('chatbot.send'), ['message' => 'How many registered users are there?']);

        $response->assertOk();
        $response->assertJson(['rejected' => true]);
    }

    public function test_a_role_field_in_the_request_body_cannot_grant_admin_access(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        // Even if a malicious client tries to smuggle a role/admin flag into
        // the request, EnsureUserIsAdmin only ever reads $request->user()->role
        // from the authenticated model, never from input.
        $response = $this->actingAs($user)->get(route('admin.dashboard').'?role=admin&is_admin=1');

        $response->assertForbidden();
    }
}
