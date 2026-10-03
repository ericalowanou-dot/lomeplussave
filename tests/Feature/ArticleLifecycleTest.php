<?php

namespace Tests\Feature;

use App\Models\Article;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Tests\Concerns\BuildsMarketplace;
use Tests\TestCase;

/**
 * Cycle de vie d'une annonce côté vendeur : publication, modification,
 * suppression, transfert, et ce que le public a le droit de voir.
 */
class ArticleLifecycleTest extends TestCase
{
    use RefreshDatabase, BuildsMarketplace;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpMarketplace();
        Mail::fake();
    }

    public function test_guest_cannot_publish(): void
    {
        $this->post(route('articles.store'), $this->articleData())->assertRedirect(route('login'));
        $this->assertSame(0, Article::count());
    }

    public function test_seller_publishes_an_article_pending_review_with_its_photos(): void
    {
        $seller = $this->seller();

        $this->actingAs($seller)
            ->post(route('articles.store'), $this->articleData([
                'livraison' => '1',
                'photos' => [
                    UploadedFile::fake()->image('a.jpg', 400, 400),
                    UploadedFile::fake()->image('b.png', 300, 300),
                ],
            ]))
            ->assertRedirect(route('mes_annonces'))
            ->assertSessionHas('success');

        $article = Article::sole();
        $this->assertSame($seller->id, $article->user_id);
        $this->assertSame('pending', $article->status);
        $this->assertSame('iPhone 13 Pro', $article->titre);
        $this->assertFalse((bool) $article->neuf);
        $this->assertTrue((bool) $article->livraison);
        $this->assertStringStartsWith('articles/', $article->photo);
        $this->assertTrue($this->uploadExists($article->photo));
        $this->assertTrue($this->uploadExists($article->photo1));
        $this->assertNull($article->photo2);
    }

    public function test_publishing_over_ajax_answers_in_json(): void
    {
        $this->actingAs($this->seller())
            ->postJson(route('articles.store'), $this->articleData([
                'photos' => [UploadedFile::fake()->image('a.jpg', 400, 400)],
            ]))
            ->assertOk()
            ->assertJson(['success' => true, 'redirect' => route('mes_annonces')]);
    }

    public function test_publishing_requires_at_least_one_photo(): void
    {
        $this->actingAs($this->seller())
            ->post(route('articles.store'), $this->articleData())
            ->assertSessionHasErrors('photos');

        $this->actingAs($this->seller())
            ->postJson(route('articles.store'), $this->articleData())
            ->assertStatus(422)
            ->assertJsonValidationErrors('photos');

        $this->assertSame(0, Article::count());
    }

    public function test_publishing_refuses_more_than_six_photos(): void
    {
        $photos = array_map(fn ($i) => UploadedFile::fake()->image("p$i.jpg", 50, 50), range(1, 7));

        $this->actingAs($this->seller())
            ->post(route('articles.store'), $this->articleData(['photos' => $photos]))
            ->assertSessionHasErrors('photos');

        $this->assertSame(0, Article::count());
    }

    public function test_publishing_refuses_a_subcategory_from_another_category(): void
    {
        $this->actingAs($this->seller())
            ->post(route('articles.store'), $this->articleData([
                'sous_categorie_id' => $this->otherSousCategorieId(),
                'photos' => [UploadedFile::fake()->image('a.jpg', 50, 50)],
            ]))
            ->assertSessionHasErrors('sous_categorie_id');

        $this->assertSame(0, Article::count());
    }

    public function test_publishing_validates_the_fields(): void
    {
        $this->actingAs($this->seller())
            ->post(route('articles.store'), $this->articleData([
                'titre' => '',
                'prix_ht' => 'abc',
                'description' => 'trop court',
                'etat' => 'cassé',
                'photos' => [UploadedFile::fake()->image('a.jpg', 50, 50)],
            ]))
            ->assertSessionHasErrors(['titre', 'prix_ht', 'description', 'etat']);

        $this->assertSame(0, Article::count());
    }

    public function test_only_the_owner_can_edit_update_delete_or_transfer(): void
    {
        $article = $this->makeArticle($this->seller());
        $intruder = $this->seller();

        $this->actingAs($intruder)->get(route('articles.edit', $article))->assertForbidden();
        $this->actingAs($intruder)->put(route('articles.update', $article), $this->articleData(['titre' => 'Piraté']))->assertForbidden();
        $this->actingAs($intruder)->delete(route('articles.destroy', $article))->assertForbidden();
        $this->actingAs($intruder)->get(route('articles.transfer', $article))->assertForbidden();
        $this->actingAs($intruder)->post(route('articles.doTransfer', $article), ['user_id' => $intruder->id])->assertForbidden();

        $article->refresh();
        $this->assertSame('iPhone 13 Pro', $article->titre);
        $this->assertNotSame($intruder->id, $article->user_id);
    }

    public function test_updating_an_approved_article_sends_it_back_to_review(): void
    {
        $seller = $this->seller();
        $article = $this->makeArticle($seller, ['status' => 'approved', 'approved_at' => now()->subDay()]);
        $createdAt = $article->created_at->toDateTimeString();

        $this->actingAs($seller)->get(route('articles.edit', $article))->assertOk();

        $this->actingAs($seller)
            ->put(route('articles.update', $article), $this->articleData(['titre' => 'iPhone 13 Pro Max', 'etat' => 'neuf']))
            ->assertRedirect(route('mes_annonces'))
            ->assertSessionHasNoErrors();

        $article->refresh();
        $this->assertSame('iPhone 13 Pro Max', $article->titre);
        $this->assertTrue((bool) $article->neuf);
        $this->assertSame('pending', $article->status);
        $this->assertSame($createdAt, $article->created_at->toDateTimeString());
    }

    public function test_updating_a_pending_article_keeps_it_pending(): void
    {
        $seller = $this->seller();
        $article = $this->makeArticle($seller, ['status' => 'pending']);

        $this->actingAs($seller)
            ->put(route('articles.update', $article), $this->articleData(['titre' => 'Nouveau titre']))
            ->assertRedirect(route('mes_annonces'));

        $this->assertSame('pending', $article->refresh()->status);
    }

    public function test_updating_with_new_photos_replaces_the_old_files(): void
    {
        $seller = $this->seller();
        $this->actingAs($seller)->post(route('articles.store'), $this->articleData([
            'photos' => [UploadedFile::fake()->image('old.jpg', 100, 100)],
        ]));
        $article = Article::sole();
        $oldPhoto = $article->photo;
        $this->assertTrue($this->uploadExists($oldPhoto));

        $this->actingAs($seller)
            ->put(route('articles.update', $article), $this->articleData([
                'photos' => [UploadedFile::fake()->image('new.jpg', 100, 100)],
            ]))
            ->assertRedirect(route('mes_annonces'));

        $article->refresh();
        $this->assertNotSame($oldPhoto, $article->photo);
        $this->assertTrue($this->uploadExists($article->photo));
        $this->assertFalse($this->uploadExists($oldPhoto));
    }

    public function test_updating_refuses_a_subcategory_from_another_category(): void
    {
        $seller = $this->seller();
        $article = $this->makeArticle($seller);

        $this->actingAs($seller)
            ->put(route('articles.update', $article), $this->articleData(['sous_categorie_id' => $this->otherSousCategorieId()]))
            ->assertSessionHasErrors('sous_categorie_id');

        $this->assertSame($this->sousCategorieId, (int) $article->refresh()->sous_categorie_id);
    }

    public function test_owner_deletes_an_article_and_its_photos(): void
    {
        $seller = $this->seller();
        $this->actingAs($seller)->post(route('articles.store'), $this->articleData([
            'photos' => [UploadedFile::fake()->image('a.jpg', 100, 100)],
        ]));
        $article = Article::sole();
        $photo = $article->photo;

        $this->actingAs($seller)
            ->delete(route('articles.destroy', $article))
            ->assertRedirect(route('mes_annonces'));

        $this->assertSame(0, Article::count());
        $this->assertFalse($this->uploadExists($photo));
    }

    public function test_owner_transfers_an_article(): void
    {
        $seller = $this->seller();
        $buyer = $this->seller();
        $article = $this->makeArticle($seller);

        $this->actingAs($seller)->get(route('articles.transfer', $article))->assertOk();
        $this->actingAs($seller)
            ->post(route('articles.doTransfer', $article), ['user_id' => $buyer->id])
            ->assertRedirect(route('mes_annonces'));

        $this->assertSame($buyer->id, $article->refresh()->user_id);
    }

    public function test_home_page_lists_only_approved_articles(): void
    {
        $seller = $this->seller();
        $this->makeArticle($seller, ['titre' => 'Annonce visible', 'status' => 'approved']);
        $this->makeArticle($seller, ['titre' => 'Annonce en attente', 'status' => 'pending']);
        $this->makeArticle($seller, ['titre' => 'Annonce bloquée', 'status' => 'blocked']);

        $this->get(route('articles.index'))
            ->assertOk()
            ->assertSee('Annonce visible')
            ->assertDontSee('Annonce en attente')
            ->assertDontSee('Annonce bloquée');
    }

    public function test_search_finds_only_approved_articles(): void
    {
        $seller = $this->seller();
        $this->makeArticle($seller, ['titre' => 'Samsung visible', 'status' => 'approved']);
        $this->makeArticle($seller, ['titre' => 'Samsung caché', 'status' => 'pending']);

        $this->get(route('article.search', ['q' => 'Samsung']))
            ->assertOk()
            ->assertSee('Samsung visible')
            ->assertDontSee('Samsung caché');
    }

    public function test_detail_page_of_an_approved_article_is_public(): void
    {
        $article = $this->makeArticle($this->seller(), ['titre' => 'Canapé cuir']);

        $this->get($article->url())->assertOk()->assertSee('Canapé cuir');
        $this->get(route('article.details.legacy', $article->id))->assertRedirect($article->url());
    }

    public function test_seller_shop_lists_only_approved_articles(): void
    {
        $seller = $this->seller(['name' => 'Boutique Koffi']);
        $this->makeArticle($seller, ['titre' => 'Montre visible', 'status' => 'approved']);
        $this->makeArticle($seller, ['titre' => 'Montre en attente', 'status' => 'pending']);

        $this->get($seller->shopUrl())
            ->assertOk()
            ->assertSee('Montre visible')
            ->assertDontSee('Montre en attente');
    }

    public function test_my_ads_page_lists_all_my_articles_and_only_mine(): void
    {
        $seller = $this->seller();
        $this->makeArticle($seller, ['titre' => 'Mon annonce en attente', 'status' => 'pending']);
        $this->makeArticle($this->seller(), ['titre' => 'Annonce d un autre']);

        $this->actingAs($seller)->get(route('mes_annonces'))
            ->assertOk()
            ->assertSee('Mon annonce en attente')
            ->assertDontSee('Annonce d un autre');
    }
}
