<?php

namespace Tests\Feature\Api;

use App\Models\Article;
use App\Models\Comment;
use App\Models\Message;
use App\Models\User;
use App\Models\UserReport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Laravel\Sanctum\Sanctum;
use Tests\Concerns\BuildsMarketplace;
use Tests\TestCase;

class ApiAccountAndSupportTest extends TestCase
{
    use RefreshDatabase, BuildsMarketplace;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpMarketplace();
        Mail::fake();
    }

    private function actingAsApi(User $user): User
    {
        Sanctum::actingAs($user);

        return $user;
    }

    // ── Backoffice partagé ───────────────────────────────────

    public function test_the_site_backoffice_moderates_what_the_app_publishes(): void
    {
        $admin = $this->admin();
        $seller = $this->seller();

        Sanctum::actingAs($seller);
        $id = $this->postJson('/api/v1/articles', $this->articleData([
            'titre' => 'Posté depuis l appli',
            'photos' => [UploadedFile::fake()->image('a.jpg')],
        ]))->assertCreated()->json('data.id');

        $this->getJson('/api/v1/articles')->assertJsonCount(0, 'data');

        // L'admin valide depuis le backoffice du site
        $this->app['auth']->forgetGuards();
        $this->actingAs($admin, 'web')
            ->get(route('admin.articles.index'))->assertOk()->assertSee('Posté depuis l appli');
        $this->actingAs($admin, 'web')
            ->post(route('admin.articles.approve', $id))->assertRedirect();

        $this->app['auth']->forgetGuards();
        $this->getJson('/api/v1/articles')->assertJsonPath('data.0.id', $id);
        $this->get(Article::find($id)->url())->assertOk()->assertSee('Posté depuis l appli');
    }

    public function test_support_conversation_between_the_app_and_the_backoffice(): void
    {
        $admin = $this->admin();
        $user = $this->actingAsApi($this->seller());

        $this->postJson('/api/v1/messages', ['body' => 'Bonjour, mon annonce est bloquée.'])
            ->assertCreated()->assertJsonPath('data.de_moi', true);
        $message = Message::sole();
        $this->assertSame([$admin->id], $message->recipients()->pluck('users.id')->all());

        // Réponse de l'admin depuis le backoffice
        $this->app['auth']->forgetGuards();
        $this->actingAs($admin, 'web')->post(route('admin.messages.send'), [
            'body' => 'Elle est débloquée.',
            'recipient_scope' => 'selected',
            'recipients' => [$user->id],
            'parent_message_id' => $message->id,
        ]);
        $reply = Message::where('parent_message_id', $message->id)->sole();

        $this->app['auth']->forgetGuards();
        Sanctum::actingAs($user);
        $this->getJson('/api/v1/me')->assertJsonPath('messages_non_lus', 1);
        $this->getJson('/api/v1/messages')
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.contenu', 'Elle est débloquée.')
            ->assertJsonPath('data.0.expediteur.nom', 'Équipe Lome+')
            ->assertJsonPath('data.0.lu', false);

        $this->getJson("/api/v1/messages/{$reply->id}")->assertOk()->assertJsonPath('data.lu', true);
        $this->getJson('/api/v1/me')->assertJsonPath('messages_non_lus', 0);

        $this->postJson('/api/v1/messages', ['body' => 'Merci !', 'parent_message_id' => $reply->id])->assertCreated();
    }

    public function test_messages_are_private(): void
    {
        $this->admin();
        $this->actingAsApi($this->seller());
        $this->postJson('/api/v1/messages', ['body' => 'Privé'])->assertCreated();
        $message = Message::sole();

        $this->actingAsApi($this->seller());
        $this->getJson("/api/v1/messages/{$message->id}")->assertForbidden();
        $this->postJson('/api/v1/messages', ['body' => 'Intrus', 'parent_message_id' => $message->id])->assertForbidden();
        $this->getJson('/api/v1/messages')->assertJsonCount(0, 'data');
    }

    // ── Compte ───────────────────────────────────────────────

    public function test_profile_update_with_photo(): void
    {
        $user = $this->actingAsApi($this->seller(['email' => 'ama@example.com']));

        $this->post('/api/v1/me', [
            'name' => 'Ama K.',
            'whatsapp' => '+22891111111',
            'photo' => UploadedFile::fake()->image('moi.jpg', 200, 200),
            'email' => 'pirate@example.com',
            'coins' => 9999,
            'role' => 'admin',
        ], ['Accept' => 'application/json'])
            ->assertOk()
            ->assertJsonPath('data.nom', 'Ama K.')
            ->assertJsonPath('data.whatsapp', '+22891111111');

        $user->refresh();
        $this->assertTrue($this->uploadExists($user->photo_profil));
        // Champs sensibles ignorés
        $this->assertSame('ama@example.com', $user->email);
        $this->assertSame(0, (int) $user->coins);
        $this->assertFalse($user->isAdmin());
    }

    public function test_certification_with_coins(): void
    {
        $this->actingAsApi($this->seller(['coins' => 10]));

        $this->postJson('/api/v1/me/certification', ['days' => 10])
            ->assertOk()->assertJsonPath('data.certifie', true)->assertJsonPath('data.coins', 0);
        $this->postJson('/api/v1/me/certification', ['days' => 1])
            ->assertStatus(422)->assertJsonPath('code', 'insufficient_coins');
    }

    // ── Commentaires ─────────────────────────────────────────

    public function test_comments(): void
    {
        $article = $this->makeArticle($this->seller());
        $author = $this->actingAsApi($this->seller(['name' => 'Yao']));

        $id = $this->postJson("/api/v1/articles/{$article->id}/comments", ['content' => 'Encore dispo ?'])
            ->assertCreated()->assertJsonPath('data.auteur.nom', 'Yao')->assertJsonPath('data.is_owner', true)
            ->json('data.id');

        $this->getJson("/api/v1/articles/{$article->id}/comments")->assertJsonCount(1, 'data');
        $this->patchJson("/api/v1/comments/{$id}", ['content' => 'Toujours dispo ?'])->assertOk();

        $this->actingAsApi($this->seller());
        $this->patchJson("/api/v1/comments/{$id}", ['content' => 'Piraté'])->assertForbidden();
        $this->deleteJson("/api/v1/comments/{$id}")->assertForbidden();
        $this->postJson("/api/v1/comments/{$id}/report", ['reason' => 'Spam'])->assertOk();
        $this->postJson("/api/v1/comments/{$id}/report", ['reason' => 'Spam'])->assertStatus(409);

        $this->actingAsApi($author);
        $this->deleteJson("/api/v1/comments/{$id}")->assertOk();
        $this->assertSame(0, Comment::count());
    }

    public function test_cannot_comment_an_unapproved_article(): void
    {
        $article = $this->makeArticle($this->seller(), ['status' => 'pending']);
        $this->actingAsApi($this->seller());

        $this->postJson("/api/v1/articles/{$article->id}/comments", ['content' => 'Bonjour'])->assertNotFound();
        $this->getJson("/api/v1/articles/{$article->id}/comments")->assertNotFound();
    }

    // ── Catalogue, boutiques, signalements ───────────────────

    public function test_categories_with_subcategories(): void
    {
        $this->getJson('/api/v1/categories')
            ->assertOk()
            ->assertJsonPath('data.0.nom', 'Électronique')
            ->assertJsonPath('data.0.sous_categories.0.nom', 'Téléphones');
    }

    public function test_shop_page_and_report(): void
    {
        $this->admin();
        $shop = $this->seller(['name' => 'Chez Afi']);
        $this->makeArticle($shop, ['titre' => 'En ligne']);
        $this->makeArticle($shop, ['titre' => 'En attente', 'status' => 'pending']);

        $this->getJson("/api/v1/shops/{$shop->id}")
            ->assertOk()
            ->assertJsonPath('vendeur.nom', 'Chez Afi')
            ->assertJsonCount(1, 'data');
        $this->assertDatabaseHas('stat_visites', ['type' => 'boutique', 'vendeur_id' => $shop->id]);

        $this->actingAsApi($this->seller());
        $reason = array_key_first(UserReport::REASONS);
        $this->postJson("/api/v1/shops/{$shop->id}/report", ['reason' => $reason])->assertCreated();
        $this->postJson("/api/v1/shops/{$shop->id}/report", ['reason' => $reason])->assertStatus(409);
        $this->assertDatabaseHas('admin_notifications', ['type' => 'user_report']);
    }

    public function test_problem_report_reaches_the_backoffice(): void
    {
        $this->postJson('/api/v1/reports', ['message' => 'L appli plante à l ouverture.'])->assertCreated();

        $this->assertDatabaseHas('problem_reports', ['message' => 'L appli plante à l ouverture.', 'status' => 'open']);
    }

    public function test_malformed_utf8_is_refused_cleanly(): void
    {
        // Envoi en formulaire (comme un multipart) : postJson ne saurait pas encoder ce texte
        $this->post('/api/v1/reports', ['message' => "Lom\xE9 : texte envoyé en Latin-1"], ['Accept' => 'application/json'])
            ->assertStatus(422)
            ->assertJsonPath('errors.message.0', 'Texte mal encodé (UTF-8 attendu).');
    }

    public function test_errors_are_json(): void
    {
        $this->getJson('/api/v1/articles/999999')->assertNotFound()->assertJsonStructure(['message']);
        $this->get('/api/v1/articles/999999')->assertNotFound()->assertJsonStructure(['message']);
    }
}
