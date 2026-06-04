<?php

namespace Tests\Feature;

use Tests\TestCase;

class RouteSmokeTest extends TestCase
{
    public function test_public_pages_render_successfully(): void
    {
        foreach ([
            '/',
            '/docs',
            '/materials',
            '/ai/summarize',
            '/ai/quiz',
            '/ai/study-plan',
            '/documents/upload',
            '/documents/chat',
            '/login',
            '/register',
        ] as $uri) {
            $this->get($uri)->assertOk();
        }
    }

    public function test_history_page_requires_login(): void
    {
        $this->get('/ai/history')
            ->assertRedirect('/login');
    }

    public function test_api_material_route_names_do_not_conflict_with_web_material_route(): void
    {
        $this->assertSame('/materials', parse_url(route('materials.index'), PHP_URL_PATH));
        $this->assertSame('/api/materials', parse_url(route('api.materials.index'), PHP_URL_PATH));
    }

    public function test_google_callback_rejects_missing_state(): void
    {
        $this->get('/auth/google/callback?code=fake-code')
            ->assertRedirect('/login')
            ->assertSessionHasErrors('google');
    }

    public function test_auth_forms_reject_crlf_in_email_like_fields(): void
    {
        $this->from('/register')->post('/register', [
            'name' => 'Test User',
            'email' => "user@example.com\r\nBcc:target@example.com",
            'phone' => null,
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertRedirect('/register')
            ->assertSessionHasErrors('email');

        $this->from('/login')->post('/login', [
            'login' => "user@example.com\r\nBcc:target@example.com",
            'password' => 'password123',
        ])->assertRedirect('/login')
            ->assertSessionHasErrors('login');
    }
}
