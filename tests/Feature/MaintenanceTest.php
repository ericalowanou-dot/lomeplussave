<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\User;
use App\Services\ImageOptimizer;
use App\Services\Maintenance;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class MaintenanceTest extends TestCase
{
    use RefreshDatabase;

    private int $sousCategorieId;

    /** Fichiers créés dans public/articles pendant un test, supprimés à la fin. */
    private array $createdFiles = [];

    protected function setUp(): void
    {
        parent::setUp();

        $categorieId = DB::table('categories')->insertGetId(['nom' => 'Maison', 'created_at' => now(), 'updated_at' => now()]);
        $this->sousCategorieId = DB::table('sous_categories')->insertGetId([
            'nom' => 'Meubles', 'categorie_id' => $categorieId, 'created_at' => now(), 'updated_at' => now(),
        ]);
    }

    protected function tearDown(): void
    {
        foreach ($this->createdFiles as $file) {
            @unlink($file);
        }
        File::deleteDirectory(storage_path('app/photos-orphelines'));

        parent::tearDown();
    }

    private function photo(string $name, int $ageDays = 0): string
    {
        $path = public_path('articles/' . $name);
        File::ensureDirectoryExists(dirname($path));
        file_put_contents($path, 'image');
        if ($ageDays > 0) {
            touch($path, now()->subDays($ageDays)->getTimestamp());
        }
        $this->createdFiles[] = $path;

        return 'articles/' . $name;
    }

    private function article(User $owner, array $attributes = []): Article
    {
        $article = new Article();
        $article->forceFill($attributes + [
            'user_id' => $owner->id, 'titre' => 'Table basse', 'description' => 'Table en bois massif',
            'prix_ht' => 25000, 'photo' => 'articles/absente.jpg', 'sous_categorie_id' => $this->sousCategorieId,
            'lieu' => 'Lomé', 'neuf' => 0, 'livraison' => 0, 'status' => 'approved',
        ])->save();

        return $article;
    }

    // ------------------------------------------------------------------
    // Likes en double
    // ------------------------------------------------------------------

    public function test_the_same_like_cannot_be_stored_twice(): void
    {
        $article = $this->article(User::factory()->create());
        $fan = User::factory()->create();

        DB::table('article_user_like')->insert(['article_id' => $article->id, 'user_id' => $fan->id, 'created_at' => now()]);

        $this->expectException(UniqueConstraintViolationException::class);
        DB::table('article_user_like')->insert(['article_id' => $article->id, 'user_id' => $fan->id, 'created_at' => now()]);
    }

    public function test_like_toggle_still_works_with_the_unique_index(): void
    {
        $article = $this->article(User::factory()->create());
        $fan = User::factory()->create();

        $this->actingAs($fan)->postJson(route('articles.like', $article))->assertJson(['liked' => true, 'likeCount' => 1]);
        $this->actingAs($fan)->postJson(route('articles.like', $article))->assertJson(['liked' => false, 'likeCount' => 0]);
        $this->actingAs($fan)->postJson(route('articles.like', $article))->assertJson(['liked' => true, 'likeCount' => 1]);
    }

    // ------------------------------------------------------------------
    // Photos supprimées avec l'annonce
    // ------------------------------------------------------------------

    public function test_deleting_an_article_deletes_its_photos(): void
    {
        $owner = User::factory()->create();
        $main = $this->photo('test-principale.jpg');
        $second = $this->photo('test-seconde.jpg');
        $article = $this->article($owner, ['photo' => $main, 'photo1' => $second]);

        $this->actingAs($owner)->delete(route('articles.destroy', $article))->assertRedirect();

        $this->assertFileDoesNotExist(public_path($main));
        $this->assertFileDoesNotExist(public_path($second));
    }

    public function test_a_photo_used_by_another_article_is_kept(): void
    {
        $owner = User::factory()->create();
        $shared = $this->photo('test-partagee.jpg');
        $first = $this->article($owner, ['photo' => $shared]);
        $this->article($owner, ['photo' => $shared, 'titre' => 'Autre annonce']);

        $first->delete();

        $this->assertFileExists(public_path($shared));
    }

    public function test_only_files_inside_public_articles_are_deleted(): void
    {
        $outside = public_path('test-hors-dossier.jpg');
        file_put_contents($outside, 'image');
        $this->createdFiles[] = $outside;

        $article = $this->article(User::factory()->create(), ['photo' => 'articles/../test-hors-dossier.jpg']);
        $article->delete();

        $this->assertFileExists($outside);
    }

    public function test_deleting_a_user_deletes_their_articles_and_photos(): void
    {
        $seller = User::factory()->create(['role' => 'user']);
        $photo = $this->photo('test-vendeur.jpg');
        $article = $this->article($seller, ['photo' => $photo]);
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->delete(route('admin.users.delete', $seller))->assertRedirect();

        $this->assertDatabaseMissing('articles', ['id' => $article->id]);
        $this->assertFileDoesNotExist(public_path($photo));
    }

    public function test_orphan_photos_are_listed_then_moved_aside(): void
    {
        $owner = User::factory()->create();
        $used = $this->photo('test-utilisee.jpg', ageDays: 3);
        $orphan = $this->photo('test-orpheline.jpg', ageDays: 3);
        $recent = $this->photo('test-recente.jpg'); // moins de 24 h : ignorée
        $this->article($owner, ['photo' => $used]);

        $listed = array_map('basename', array_column((new Maintenance())->orphanPhotos(), 'path'));
        $this->assertContains('test-orpheline.jpg', $listed);
        $this->assertNotContains('test-utilisee.jpg', $listed);
        $this->assertNotContains('test-recente.jpg', $listed);

        // Sans option : rien n'est modifié
        $this->artisan('lomeplus:photos-orphelines')->assertSuccessful();
        $this->assertFileExists(public_path($orphan));

        $this->artisan('lomeplus:photos-orphelines --deplacer --force')->assertSuccessful();
        $this->assertFileDoesNotExist(public_path($orphan));
        $this->assertFileExists(public_path($used));
        $this->assertFileExists(public_path($recent));
        $this->assertNotEmpty(File::glob(storage_path('app/photos-orphelines/*/test-orpheline.jpg')));
    }

    // ------------------------------------------------------------------
    // Sessions et cache
    // ------------------------------------------------------------------

    public function test_old_guest_sessions_and_expired_cache_are_cleaned(): void
    {
        config(['session.driver' => 'database', 'cache.default' => 'database']);
        $member = User::factory()->create();
        $old = now()->subDays(5)->getTimestamp();

        DB::table('sessions')->insert([
            ['id' => 'invite-ancien', 'user_id' => null, 'payload' => '', 'last_activity' => $old],
            ['id' => 'invite-recent', 'user_id' => null, 'payload' => '', 'last_activity' => now()->getTimestamp()],
            ['id' => 'membre-ancien', 'user_id' => $member->id, 'payload' => '', 'last_activity' => $old],
        ]);
        DB::table('cache')->insert([
            ['key' => 'expiree', 'value' => 'x', 'expiration' => time() - 60],
            ['key' => 'valide', 'value' => 'x', 'expiration' => time() + 3600],
        ]);

        $this->artisan('lomeplus:nettoyer')->assertSuccessful();

        $this->assertSame(['invite-recent', 'membre-ancien'], DB::table('sessions')->orderBy('id')->pluck('id')->all());
        $this->assertSame(['valide'], DB::table('cache')->pluck('key')->all());
    }

    public function test_bots_do_not_create_sessions(): void
    {
        config(['session.driver' => 'database']);

        // Un vrai visiteur : sa session est enregistrée
        $this->withHeader('User-Agent', 'Mozilla/5.0 (Linux; Android 14) Chrome/128.0 Mobile Safari/537.36')
            ->get('/a-propos')->assertOk();
        $this->assertSame(1, DB::table('sessions')->count());

        // Un robot : rien de plus en base (la page s'affiche normalement)
        $this->withHeader('User-Agent', 'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)')
            ->get('/a-propos')->assertOk();
        $this->assertSame(1, DB::table('sessions')->count());
    }

    public function test_daily_maintenance_runs_once_per_day_from_admin(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->get(route('admin.statistics.index'))->assertOk();
        $this->assertTrue(cache()->has('lomeplus:maintenance-quotidienne'));
    }

    // ------------------------------------------------------------------
    // Photos trop grandes
    // ------------------------------------------------------------------

    /**
     * En-tête PNG seul : getimagesize lit les dimensions sans décoder l'image.
     */
    private function pngHeader(int $width, int $height): UploadedFile
    {
        $path = tempnam(sys_get_temp_dir(), 'png');
        file_put_contents($path, "\x89PNG\r\n\x1a\n" . pack('N', 13) . 'IHDR' . pack('NN', $width, $height) . "\x08\x02\x00\x00\x00" . pack('N', 0));

        return new UploadedFile($path, 'photo-geante.png', 'image/png', null, true);
    }

    public function test_huge_photos_are_refused_with_a_clear_message(): void
    {
        $message = ImageOptimizer::memoryProblem($this->pngHeader(12000, 9000)); // 108 mégapixels
        $this->assertNotNull($message);
        $this->assertStringContainsString('trop grande', $message);
        $this->assertStringContainsString('12000 × 9000', $message);

        $this->assertNull(ImageOptimizer::memoryProblem($this->pngHeader(4000, 3000))); // 12 mégapixels : accepté
    }

    public function test_publishing_a_huge_photo_returns_a_clear_error_instead_of_a_crash(): void
    {
        $seller = User::factory()->create(['telephone' => '90000000']);
        $categorieId = DB::table('sous_categories')->where('id', $this->sousCategorieId)->value('categorie_id');

        $response = $this->actingAs($seller)->postJson(route('articles.store'), [
            'categorie' => $categorieId,
            'sous_categorie_id' => $this->sousCategorieId,
            'titre' => 'Canapé trois places',
            'prix_ht' => 50000,
            'lieu' => 'Lomé',
            'description' => 'Canapé en très bon état, tissu gris.',
            'etat' => 'occasion',
            'photos' => [$this->pngHeader(12000, 9000)],
        ]);

        $this->assertContains($response->status(), [422, 400]);
        $this->assertStringContainsString('trop grande', $response->getContent());
        $this->assertSame(0, Article::count());
    }
}
