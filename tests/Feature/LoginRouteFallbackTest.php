<?php

namespace Tests\Feature;

use Tests\TestCase;

class LoginRouteFallbackTest extends TestCase
{
    public function test_post_login_redirects_to_login_page_for_web_requests(): void
    {
        $response = $this->post('/login', [
            'email' => 'user@example.com',
            'password' => 'secret',
        ]);

        $response->assertRedirect('/login');
    }

    public function test_post_login_returns_json_method_not_allowed_for_api_requests(): void
    {
        $response = $this->postJson('/login', [
            'email' => 'user@example.com',
            'password' => 'secret',
        ]);

        $response
            ->assertStatus(405)
            ->assertJson([
                'message' => 'Use the login page at /login.',
            ]);
    }
}
