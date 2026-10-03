<?php

namespace Tests\Feature\Api;

use App\Mail\AdminActivityMail;
use App\Models\Article;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Laravel\Sanctum\Sanctum;
use Tests\Concerns\BuildsMarketplace;
use Tests\TestCase;

/**
 * Les annonces dans l'appli suivent exactement les règles du site.
 */
class ApiArticlesTest extends TestCase
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

    public function test_public_list_shows_only_approved_articles_with_card_fields(): void
    {
        $seller = $this->seller(['name' => 'Koffi']);
        $this->makeArticle($seller, ['titre' => 'Visible', 'prix_ht' => 1500]);
        $this->makeArticle($seller, ['titre' => 'En attente', 'status' => 'pending']);

        $this->getJson('/api/v1/articles')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.titre', 'Visible')
            ->assertJsonPath('data.0.prix', 1500)
            ->assertJsonPath('data.0.vendeur.nom', 'Koffi')
            ->assertJsonPath('data.0.categorie.nom', 'Électronique')
            ->assertJsonPath('data.0.liked', false)
            ->assertJsonMissingPath('data.0.status')
            ->assertJsonMissingPath('data.0.vendeur.email')
            ->assertJsonStructure(['data' => [['id', 'photo', 'likes', 'url', 'created_at']], 'links', 'meta' => ['current_page', 'total']]);
    }

    public function test_list_uses_the_site_filters_and_paginates(): void
    {
        $seller = $this->seller();
        $this->makeArticle($seller, ['titre' => 'Neuf', 'neuf' => true, 'prix_ht' => 100]);
        $this->makeArticle($seller, ['titre' => 'Occasion', 'neuf' => false, 'prix_ht' => 900]);

        $this->getJson('/api/v1/articles?etat=neuf')->assertJsonCount(1, 'data')->assertJsonPath('data.0.titre', 'Neuf');
        $this->getJson('/api/v1/articles?order_by=prix_desc')->assertJsonPath('data.0.titre', 'Occasion');
        $this->getJson('/api/v1/articles?per_page=1')->assertJsonCount(1, 'data')->assertJsonPath('meta.total', 2);
        $this->getJson('/api/v1/articles?order_by=hack')->assertStatus(422);
    }

    public function test_search_looks_in_seller_and_category_names_and_is_tracked(): void
    {
        $this->makeArticle($this->seller(['name' => 'Boutique Ablodé']), ['titre' => 'Chaussures']);

        $this->getJson('/api/v1/articles/search?q=Ablodé')->assertOk()->assertJsonPath('data.0.titre', 'Chaussures');
        $this->getJson('/api/v1/articles/search?q=Téléphones')->assertJsonCount(1, 'data');
        $this->getJson('/api/v1/articles/search?q=x')->assertStatus(422);

        $this->assertDatabaseHas('stat_recherches', ['terme' => 'Ablodé', 'source' => 'appli']);
    }

    public function test_detail_shows_everything_and_counts_the_view(): void
    {
        $seller = $this->seller(['name' => 'Koffi', 'telephone' => '90000000']);
        $article = $this->makeArticle($seller, ['titre' => 'Vélo', 'photo1' => 'articles/b.jpg']);

        $this->getJson("/api/v1/articles/{$article->id}")
            ->assertOk()
            ->assertJsonPath('data.description', 'Très bon état, vendu avec chargeur et boîte.')
            ->assertJsonCount(2, 'data.photos')
            ->assertJsonPath('data.vendeur.telephone', '90000000')
            ->assertJsonPath('data.vendeur.whatsapp_url', 'https://wa.me/22890000000')
            ->assertJsonPath('data.comments_count', 0);

        $this->assertDatabaseHas('stat_visites', ['type' => 'article', 'article_id' => $article->id]);
    }

    public function test_unapproved_article_detail_is_only_for_its_owner_and_admin(): void
    {
        $seller = $this->seller();
        $article = $this->makeArticle($seller, ['status' => 'pending']);

        $this->getJson("/api/v1/articles/{$article->id}")->assertNotFound();

        $this->actingAsApi($this->seller());
        $this->getJson("/api/v1/articles/{$article->id}")->assertNotFound();
        $this->postJson("/api/v1/articles/{$article->id}/like")->assertNotFound();

        $this->actingAsApi($seller);
        $this->getJson("/api/v1/articles/{$article->id}")->assertOk()->assertJsonPath('data.status', 'pending');
    }

    public function test_publishing_from_the_app_goes_to_moderation_and_emails_the_admin(): void
    {
        $this->admin();
        $seller = $this->actingAsApi($this->seller());

        $this->postJson('/api/v1/articles', $this->articleData([
            'livraison' => true,
            'photos' => [UploadedFile::fake()->image('a.jpg', 300, 300), UploadedFile::fake()->image('b.jpg', 300, 300)],
        ]))
            ->assertCreated()
            ->assertJsonPath('data.status', 'pending')
            ->assertJsonPath('data.livraison', true)
            ->assertJsonCount(2, 'data.photos');

        $article = Article::sole();
        $this->assertSame($seller->id, $article->user_id);
        $this->assertTrue($this->uploadExists($article->photo));
        Mail::assertSent(AdminActivityMail::class);
    }

    public function test_publishing_validation_is_the_same_as_the_site(): void
    {
        $this->actingAsApi($this->seller());

        $this->postJson('/api/v1/articles', $this->articleData())
            ->assertStatus(422)->assertJsonValidationErrors('photos');

        $svg = UploadedFile::fake()->createWithContent('x.svg', '<svg xmlns="http://www.w3.org/2000/svg"/>');
        $this->postJson('/api/v1/articles', $this->articleData(['photos' => [$svg]]))
            ->assertStatus(422)->assertJsonValidationErrors('photos');

        $this->postJson('/api/v1/articles', $this->articleData([
            'sous_categorie_id' => $this->otherSousCategorieId(),
            'photos' => [UploadedFile::fake()->image('a.jpg')],
        ]))->assertStatus(422)->assertJsonValidationErrors('sous_categorie_id');

        $this->assertSame(0, Article::count());
    }

    public function test_updating_from_the_app_sends_back_to_review_and_replaces_photos(): void
    {
        $seller = $this->actingAsApi($this->seller());
        $this->postJson('/api/v1/articles', $this->articleData(['photos' => [UploadedFile::fake()->image('old.jpg')]]))->assertCreated();
        $article = Article::sole();
        $article->approve();
        $old = $article->photo;

        $this->post("/api/v1/articles/{$article->id}", $this->articleData([
            'titre' => 'Nouveau titre',
            'photos' => [UploadedFile::fake()->image('new.jpg')],
        ]), ['Accept' => 'application/json'])
            ->assertOk()
            ->assertJsonPath('en_validation', true)
            ->assertJsonPath('data.titre', 'Nouveau titre')
            ->assertJsonPath('data.status', 'pending');

        $article->refresh();
        $this->assertNotSame($old, $article->photo);
        $this->assertFalse($this->uploadExists($old));

        // PUT sans photo
        $this->putJson("/api/v1/articles/{$article->id}", $this->articleData(['titre' => 'Encore']))->assertOk();
        $this->assertSame('Encore', $article->refresh()->titre);
    }

    public function test_only_the_owner_updates_deletes_or_boosts(): void
    {
        $article = $this->makeArticle($this->seller());
        $this->actingAsApi($this->seller(['coins' => 50]));

        $this->putJson("/api/v1/articles/{$article->id}", $this->articleData(['titre' => 'Piraté']))->assertForbidden();
        $this->deleteJson("/api/v1/articles/{$article->id}")->assertForbidden();
        $this->postJson("/api/v1/articles/{$article->id}/boost", ['days' => 3])->assertForbidden();

        $this->assertSame('iPhone 13 Pro', $article->refresh()->titre);
        $this->assertNull($article->boosted_until);
    }

    public function test_owner_deletes_an_article(): void
    {
        $seller = $this->actingAsApi($this->seller());
        $article = $this->makeArticle($seller);

        $this->deleteJson("/api/v1/articles/{$article->id}")->assertOk();
        $this->assertSame(0, Article::count());
    }

    public function test_like_toggle_and_liked_flag_in_lists(): void
    {
        $article = $this->makeArticle($this->seller());
        $fan = $this->actingAsApi($this->seller());

        $this->postJson("/api/v1/articles/{$article->id}/like")->assertExactJson(['liked' => true, 'likeCount' => 1]);
        $this->getJson('/api/v1/articles')->assertJsonPath('data.0.liked', true)->assertJsonPath('data.0.likes', 1);
        $this->getJson('/api/v1/me/favorites')->assertJsonCount(1, 'data')->assertJsonPath('data.0.liked', true);

        $this->postJson("/api/v1/articles/{$article->id}/like")->assertExactJson(['liked' => false, 'likeCount' => 0]);
        $this->assertSame(0, DB::table('article_user_like')->count());
    }

    public function test_boost_spends_coins(): void
    {
        $seller = $this->actingAsApi($this->seller(['coins' => 4]));
        $article = $this->makeArticle($seller);

        $this->postJson("/api/v1/articles/{$article->id}/boost", ['days' => 3])
            ->assertOk()->assertJsonPath('coins', 1);
        $this->postJson("/api/v1/articles/{$article->id}/boost", ['days' => 3])
            ->assertStatus(422)->assertJsonPath('code', 'insufficient_coins');

        $this->assertSame(1, (int) $seller->refresh()->coins);
    }

    public function test_my_articles_lists_all_statuses_with_stats(): void
    {
        $seller = $this->actingAsApi($this->seller());
        $this->makeArticle($seller, ['status' => 'pending']);
        $this->makeArticle($seller, ['status' => 'blocked', 'block_reason' => 'Photo floue']);
        $this->makeArticle($this->seller());

        $this->getJson('/api/v1/me/articles')
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('stats.total', 2)
            ->assertJsonPath('stats.blocked', 1);

        $this->getJson('/api/v1/me/articles?status=blocked')
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.block_reason', 'Photo floue');
    }
}
