<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Services\ImageOptimizer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Tests\Concerns\BuildsMarketplace;
use Tests\TestCase;

/**
 * Une annonce non validée n'est visible que par son vendeur et l'admin ;
 * les coins ne se dépensent qu'une fois ; une modification ratée ne perd pas de photos.
 */
class VisibilityAndIntegrityTest extends TestCase
{
    use RefreshDatabase, BuildsMarketplace;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpMarketplace();
        Mail::fake();
    }

    public function test_pending_or_blocked_detail_page_is_hidden_from_the_public(): void
    {
        $seller = $this->seller();

        foreach (['pending', 'blocked'] as $status) {
            $article = $this->makeArticle($seller, ['status' => $status, 'titre' => "Annonce $status"]);

            $this->get($article->url())->assertNotFound();
            $this->actingAs($this->seller())->get($article->url())->assertNotFound();
            $this->actingAs($seller)->get($article->url())->assertOk()->assertSee("Annonce $status");
            $this->actingAs($this->admin())->get($article->url())->assertOk();
            auth()->logout();
        }
    }

    public function test_legacy_pages_do_not_leak_unapproved_articles(): void
    {
        $seller = $this->seller();
        $pending = $this->makeArticle($seller, ['status' => 'pending', 'titre' => 'Secret en attente']);

        $this->get('/paginate')->assertOk()->assertDontSee('Secret en attente');
        $this->actingAs($this->seller())->get(route('annonce.show', $pending->id))->assertNotFound();
        $this->actingAs($seller)->get(route('annonce.show', $pending->id))->assertRedirect($pending->url());
    }

    public function test_coins_cannot_be_spent_twice_from_a_stale_balance(): void
    {
        $seller = $this->seller(['coins' => 5]);
        $article = $this->makeArticle($seller);

        // Simule deux requêtes simultanées : la seconde a lu le solde avant que la première ne l'écrive.
        $stale = $seller->fresh();
        $this->actingAs($seller)->postJson(route('user.boost', $article), ['days' => 5])->assertOk();

        $this->assertFalse($stale->spendCoins(5));
        $this->assertSame(0, (int) $seller->refresh()->coins);
    }

    public function test_a_failed_update_keeps_the_previous_photos(): void
    {
        $seller = $this->seller();
        $this->actingAs($seller)->post(route('articles.store'), $this->articleData([
            'photos' => [UploadedFile::fake()->image('old.jpg', 100, 100)],
        ]));
        $article = Article::sole();
        $oldPhoto = $article->photo;

        Article::saving(fn () => throw new \RuntimeException('Base indisponible'));

        $this->actingAs($seller)
            ->put(route('articles.update', $article), $this->articleData([
                'photos' => [UploadedFile::fake()->image('new.jpg', 100, 100)],
            ]))
            ->assertSessionHasErrors('general');

        Article::flushEventListeners();

        $this->assertSame($oldPhoto, $article->refresh()->photo);
        $this->assertTrue($this->uploadExists($oldPhoto));
    }

    public function test_svg_files_are_refused_as_article_photos(): void
    {
        $svg = UploadedFile::fake()->createWithContent('logo.svg', '<svg xmlns="http://www.w3.org/2000/svg"><script>alert(1)</script></svg>');

        $this->actingAs($this->seller())
            ->post(route('articles.store'), $this->articleData(['photos' => [$svg]]))
            ->assertSessionHasErrors('photos');

        $this->assertSame(0, Article::count());
    }

    public function test_image_optimizer_never_publishes_svg_as_article_photo(): void
    {
        $svg = UploadedFile::fake()->createWithContent('logo.svg', '<svg xmlns="http://www.w3.org/2000/svg"></svg>');

        $this->assertNotNull(ImageOptimizer::articlePhotoProblem($svg));
        $this->assertNull(ImageOptimizer::articlePhotoProblem(UploadedFile::fake()->image('a.jpg', 10, 10)));
    }
}
