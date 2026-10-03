<?php

namespace Tests\Feature\Api;

use App\Models\User;
use App\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Tests\Concerns\BuildsMarketplace;
use Tests\TestCase;

class ApiAuthTest extends TestCase
{
    use RefreshDatabase, BuildsMarketplace;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpMarketplace();
        Mail::fake();
    }

    public function test_register_returns_a_token_and_notifies_the_admin(): void
    {
        $this->admin();

        $response = $this->postJson('/api/v1/auth/register', [
            'name' => 'Kofi',
            'email' => 'kofi@example.com',
            'password' => 'secret1',
            'telephone' => '+22890000000',
            'whatsapp' => '+22890000000',
            'device_name' => 'Pixel 7',
        ])->assertCreated()
            ->assertJsonPath('token_type', 'Bearer')
            ->assertJsonPath('user.email', 'kofi@example.com')
            ->assertJsonPath('user.coins', 0);

        $user = User::where('email', 'kofi@example.com')->sole();
        $this->assertSame('+22890000000', $user->telephone);
        $this->assertSame('Pixel 7', $user->tokens()->sole()->name);
        $this->assertDatabaseHas('admin_notifications', ['type' => 'user_registered']);

        $this->withToken($response->json('token'))->getJson('/api/v1/me')
            ->assertOk()->assertJsonPath('data.id', $user->id);
    }

    public function test_register_validates_like_the_site(): void
    {
        $this->seller(['email' => 'pris@example.com']);

        $this->postJson('/api/v1/auth/register', ['email' => 'pris@example.com', 'password' => '123'])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'email', 'password', 'telephone', 'whatsapp']);
    }

    public function test_login_and_logout(): void
    {
        $user = $this->seller(['email' => 'ama@example.com']);

        $this->postJson('/api/v1/auth/login', ['email' => 'ama@example.com', 'password' => 'mauvais'])
            ->assertStatus(422)->assertJsonValidationErrors('email');

        $token = $this->postJson('/api/v1/auth/login', ['email' => 'ama@example.com', 'password' => 'password'])
            ->assertOk()->assertJsonPath('user.id', $user->id)->json('token');

        $this->withToken($token)->postJson('/api/v1/auth/logout')->assertOk();
        $this->assertSame(0, $user->tokens()->count());

        $this->app['auth']->forgetGuards();
        $this->withToken($token)->getJson('/api/v1/me')->assertUnauthorized();
    }

    public function test_protected_routes_need_a_token(): void
    {
        $this->getJson('/api/v1/me')->assertUnauthorized()->assertJsonStructure(['message']);
        $this->postJson('/api/v1/articles')->assertUnauthorized();
        $this->getJson('/api/v1/messages')->assertUnauthorized();
    }

    public function test_a_blocked_user_cannot_log_in_and_loses_existing_tokens(): void
    {
        $user = $this->seller(['email' => 'bloque@example.com']);
        $token = $user->createToken('appli')->plainTextToken;
        $user->block('Fraude');

        $this->postJson('/api/v1/auth/login', ['email' => 'bloque@example.com', 'password' => 'password'])
            ->assertForbidden()->assertJsonPath('code', 'account_blocked');

        $this->withToken($token)->getJson('/api/v1/me')->assertForbidden()->assertJsonPath('code', 'account_blocked');
        $this->assertSame(0, $user->tokens()->count());
    }

    public function test_forgot_password_sends_the_site_email_without_revealing_accounts(): void
    {
        Notification::fake();
        $user = $this->seller(['email' => 'oubli@example.com']);

        $known = $this->postJson('/api/v1/auth/forgot-password', ['email' => 'oubli@example.com'])->assertOk()->json('message');
        $unknown = $this->postJson('/api/v1/auth/forgot-password', ['email' => 'personne@example.com'])->assertOk()->json('message');

        $this->assertSame($known, $unknown);
        Notification::assertSentTo($user, ResetPassword::class);
    }

    public function test_login_is_rate_limited(): void
    {
        $this->seller(['email' => 'cible@example.com']);

        foreach (range(1, 10) as $i) {
            $this->postJson('/api/v1/auth/login', ['email' => 'cible@example.com', 'password' => 'faux']);
        }

        $this->postJson('/api/v1/auth/login', ['email' => 'cible@example.com', 'password' => 'password'])
            ->assertStatus(429);
    }

    public function test_user_can_delete_their_account_from_the_app(): void
    {
        $user = $this->seller();
        $this->makeArticle($user);
        $token = $user->createToken('appli')->plainTextToken;

        $this->withToken($token)->deleteJson('/api/v1/me', ['password' => 'mauvais'])
            ->assertStatus(422)->assertJsonValidationErrors('password');

        $this->withToken($token)->deleteJson('/api/v1/me', ['password' => 'password'])->assertOk();
        $this->assertDatabaseMissing('users', ['id' => $user->id]);
        $this->assertDatabaseCount('articles', 0);
    }
}
