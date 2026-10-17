<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * Traçabilité des clics « Appeler » / « WhatsApp » vers les vendeurs (site, appli, admin).
 */
class ContactTrackingTest extends TestCase
{
    use RefreshDatabase;

    private int $sousCategorieId;

    protected function setUp(): void
    {
        parent::setUp();

        $categorieId = DB::table('categories')->insertGetId(['nom' => 'Électronique', 'created_at' => now(), 'updated_at' => now()]);
        $this->sousCategorieId = DB::table('sous_categories')->insertGetId([
            'nom' => 'Téléphones', 'categorie_id' => $categorieId, 'created_at' => now(), 'updated_at' => now(),
        ]);
    }

    private function makeUser(array $attributes = []): User
    {
        return User::factory()->create($attributes + ['role' => 'user']);
    }

    private function makeArticle(User $owner, array $attributes = []): Article
    {
        $article = new Article();
        $article->forceFill($attributes + [
            'user_id' => $owner->id,
            'titre' => 'iPhone 13 Pro',
            'description' => 'Très bon état',
            'prix_ht' => 250000,
            'photo' => 'articles/demo.jpg',
            'sous_categorie_id' => $this->sousCategorieId,
            'lieu' => 'Lomé',
            'neuf' => 0,
            'livraison' => 1,
            'status' => 'approved',
        ])->save();

        return $article;
    }

    private function clic(array $data)
    {
        return $this->post(route('contact-vendeur.store'), $data);
    }

    // ------------------------------------------------------------------
    // Site
    // ------------------------------------------------------------------

    public function test_contact_buttons_carry_tracking_attributes(): void
    {
        $seller = $this->makeUser(['telephone' => '+22890000000', 'whatsapp' => '+22890000000']);
        $article = $this->makeArticle($seller);

        $this->get($article->url())
            ->assertOk()
            ->assertSee('data-contact-canal="appel"', false)
            ->assertSee('data-contact-canal="whatsapp"', false)
            ->assertSee('data-contact-article="' . $article->id . '"', false);

        $this->get($seller->shopUrl())
            ->assertOk()
            ->assertSee('data-contact-vendeur="' . $seller->id . '"', false);
    }

    public function test_click_is_recorded_once_per_ten_minutes_and_per_channel(): void
    {
        $seller = $this->makeUser();
        $article = $this->makeArticle($seller);
        $visitor = $this->makeUser();

        $this->actingAs($visitor);
        $this->clic(['canal' => 'whatsapp', 'vendeur_id' => $seller->id, 'article_id' => $article->id])->assertNoContent();
        $this->clic(['canal' => 'whatsapp', 'vendeur_id' => $seller->id, 'article_id' => $article->id])->assertNoContent();
        $this->clic(['canal' => 'appel', 'vendeur_id' => $seller->id, 'article_id' => $article->id])->assertNoContent();
        $this->clic(['canal' => 'appel', 'vendeur_id' => $seller->id])->assertNoContent(); // depuis la boutique

        $this->assertSame(3, DB::table('stat_contacts')->count());
        $this->assertDatabaseHas('stat_contacts', [
            'canal' => 'whatsapp',
            'source' => 'site',
            'article_id' => $article->id,
            'vendeur_id' => $seller->id,
            'visiteur_id' => $visitor->id,
        ]);
        $this->assertDatabaseHas('stat_contacts', ['canal' => 'appel', 'article_id' => null, 'vendeur_id' => $seller->id]);

        $this->travel(11)->minutes();
        $this->clic(['canal' => 'whatsapp', 'vendeur_id' => $seller->id, 'article_id' => $article->id])->assertNoContent();
        $this->assertSame(4, DB::table('stat_contacts')->count());
    }

    public function test_guest_click_is_recorded_without_visitor(): void
    {
        $seller = $this->makeUser();

        $this->clic(['canal' => 'appel', 'vendeur_id' => $seller->id])->assertNoContent();

        $this->assertDatabaseHas('stat_contacts', ['vendeur_id' => $seller->id, 'visiteur_id' => null]);
    }

    public function test_owner_admin_bots_and_inconsistent_data_are_ignored(): void
    {
        $seller = $this->makeUser();
        $other = $this->makeUser();
        $article = $this->makeArticle($seller);

        $this->actingAs($seller)->clic(['canal' => 'appel', 'vendeur_id' => $seller->id, 'article_id' => $article->id])->assertNoContent();
        $this->actingAs($this->makeUser(['role' => 'admin']))->clic(['canal' => 'appel', 'vendeur_id' => $seller->id])->assertNoContent();
        $this->actingAs($this->makeUser())
            ->withHeader('User-Agent', 'Mozilla/5.0 (compatible; Googlebot/2.1)')
            ->clic(['canal' => 'appel', 'vendeur_id' => $seller->id])
            ->assertNoContent();

        // Annonce d'un autre vendeur, vendeur inconnu
        $this->actingAs($this->makeUser(), 'web');
        $this->withHeader('User-Agent', 'Mozilla/5.0');
        $this->clic(['canal' => 'appel', 'vendeur_id' => $other->id, 'article_id' => $article->id])->assertNoContent();
        $this->clic(['canal' => 'appel', 'vendeur_id' => 999999])->assertNoContent();

        $this->assertSame(0, DB::table('stat_contacts')->count());

        // Canal inconnu
        $this->postJson(route('contact-vendeur.store'), ['canal' => 'sms', 'vendeur_id' => $seller->id])
            ->assertUnprocessable();
    }

    // ------------------------------------------------------------------
    // Appli mobile
    // ------------------------------------------------------------------

    public function test_api_records_contacts_from_article_and_shop(): void
    {
        $seller = $this->makeUser();
        $article = $this->makeArticle($seller);
        $visitor = $this->makeUser();
        Sanctum::actingAs($visitor);

        $this->postJson("/api/v1/articles/{$article->id}/contact", ['canal' => 'whatsapp'])->assertNoContent();
        $this->postJson("/api/v1/shops/{$seller->id}/contact", ['canal' => 'appel'])->assertNoContent();
        $this->postJson("/api/v1/shops/{$seller->id}/contact", ['canal' => 'fax'])->assertUnprocessable();

        $this->assertDatabaseHas('stat_contacts', [
            'canal' => 'whatsapp', 'source' => 'appli', 'article_id' => $article->id,
            'vendeur_id' => $seller->id, 'visiteur_id' => $visitor->id,
        ]);
        $this->assertDatabaseHas('stat_contacts', ['canal' => 'appel', 'source' => 'appli', 'article_id' => null]);
    }

    public function test_api_contact_on_hidden_article_is_not_found(): void
    {
        $article = $this->makeArticle($this->makeUser(), ['status' => 'pending']);

        $this->postJson("/api/v1/articles/{$article->id}/contact", ['canal' => 'appel'])->assertNotFound();
        $this->postJson('/api/v1/articles/999999/contact', ['canal' => 'appel'])->assertNotFound();

        $this->assertSame(0, DB::table('stat_contacts')->count());
    }

    // ------------------------------------------------------------------
    // Admin
    // ------------------------------------------------------------------

    public function test_seller_fiche_shows_contacts_and_who_made_them(): void
    {
        $seller = $this->makeUser(['name' => 'Kossi Mensah']);
        $article = $this->makeArticle($seller);
        $visitor = $this->makeUser(['name' => 'Afi Acheteuse']);

        $this->actingAs($visitor)->clic(['canal' => 'whatsapp', 'vendeur_id' => $seller->id, 'article_id' => $article->id]);
        $this->actingAs($visitor)->clic(['canal' => 'appel', 'vendeur_id' => $seller->id]);
        auth()->logout();
        $this->clic(['canal' => 'appel', 'vendeur_id' => $seller->id]);

        $admin = $this->makeUser(['role' => 'admin']);
        $this->actingAs($admin)->get(route('admin.statistics.vendeur', ['user' => $seller->id, 'periode' => '7j', 'tri' => 'contacts']))
            ->assertOk()
            ->assertSee('Contacts WhatsApp')
            ->assertSee("Qui l'a contacté", false)
            ->assertSee('Afi Acheteuse')
            ->assertSee('Visiteur non connecté')
            ->assertSee('Sa boutique');
    }

    public function test_seller_fiche_renders_without_contacts_table(): void
    {
        \Illuminate\Support\Facades\Schema::drop('stat_contacts');
        $seller = $this->makeUser();
        $this->makeArticle($seller);

        $this->clic(['canal' => 'appel', 'vendeur_id' => $seller->id])->assertNoContent();

        $this->actingAs($this->makeUser(['role' => 'admin']))
            ->get(route('admin.statistics.vendeur', ['user' => $seller->id, 'periode' => '7j', 'tri' => 'contacts']))
            ->assertOk()
            ->assertSee('Aucun contact enregistré');
    }
}
