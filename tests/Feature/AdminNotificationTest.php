<?php

namespace Tests\Feature;

use App\Events\ArticlePending;
use App\Events\UserRegistered;
use App\Models\AdminNotification;
use App\Models\Article;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AdminNotificationTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => 'admin']);
    }

    private function pendingArticle(User $owner): Article
    {
        $categorieId = DB::table('categories')->insertGetId(['nom' => 'Mode', 'created_at' => now(), 'updated_at' => now()]);
        $sousCategorieId = DB::table('sous_categories')->insertGetId([
            'nom' => 'Bijoux', 'categorie_id' => $categorieId, 'created_at' => now(), 'updated_at' => now(),
        ]);

        $article = new Article();
        $article->forceFill([
            'user_id' => $owner->id,
            'titre' => 'Chaîne pour femme',
            'description' => 'Chaîne dorée, jamais portée.',
            'prix_ht' => 5000,
            'lieu' => 'Lomé',
            'sous_categorie_id' => $sousCategorieId,
            'neuf' => true,
            'livraison' => false,
            'photo' => 'articles/test.jpg',
            'status' => 'pending',
        ])->save();

        return $article;
    }

    public function test_new_user_creates_a_single_notification_per_admin(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        event(new UserRegistered($user));

        $this->assertSame(1, AdminNotification::where('type', 'user_registered')->where('related_id', $user->id)->count());
    }

    public function test_pending_article_creates_a_single_notification_per_admin(): void
    {
        $article = $this->pendingArticle(User::factory()->create(['role' => 'user']));

        event(new ArticlePending($article));

        $notifications = AdminNotification::where('type', 'article_pending')->get();
        $this->assertCount(1, $notifications);
        $this->assertSame(route('admin.articles.show', $article), $notifications->first()->url);
    }

    public function test_dedupe_command_removes_duplicates_and_keeps_read_state(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        $base = [
            'admin_id' => $this->admin->id,
            'type' => 'user_registered',
            'title' => 'Nouvel utilisateur inscrit',
            'message' => 'x',
            'related_id' => $user->id,
            'related_type' => User::class,
        ];
        $kept = AdminNotification::create($base);
        AdminNotification::create($base + ['is_read' => true, 'read_at' => now()]);
        $other = AdminNotification::create(['related_id' => $user->id + 1] + $base);

        // Aperçu : rien n'est supprimé sans --force.
        $this->artisan('notifications:dedupe')->assertSuccessful();
        $this->assertSame(3, AdminNotification::count());

        $this->artisan('notifications:dedupe', ['--force' => true])->assertSuccessful();

        $this->assertSame([$kept->id, $other->id], AdminNotification::orderBy('id')->pluck('id')->all());
        $this->assertTrue($kept->fresh()->is_read);
        $this->assertFalse($other->fresh()->is_read);
    }
}
