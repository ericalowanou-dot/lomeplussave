<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
    }

    public function test_new_users_can_register(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'phone_full' => '+22890000000',
            'whatsapp_full' => '+22890000000',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('articles.index', absolute: false));
    }

    public function test_togo_numbers_in_72_can_register(): void
    {
        $response = $this->post('/register', [
            'name' => 'Togo User',
            'email' => 'togo@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'phone_full' => '+22872123456',
            'whatsapp_full' => '+22873123456',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('articles.index', absolute: false));
        $this->assertDatabaseHas('users', ['email' => 'togo@example.com', 'telephone' => '+22872123456']);
    }

    public function test_registration_page_accepts_every_togo_mobile_prefix(): void
    {
        // La règle Togo côté navigateur doit couvrir tous les préfixes 2x, 7x et 9x.
        $this->get('/register')
            ->assertOk()
            ->assertSee('/^[279]\d{7}$/', false);
    }
}
