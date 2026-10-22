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

    /**
     * Deux vendeurs : Kossi (2 contacts, dont un depuis l'appli) et Ama (1 contact à Kara).
     */
    private function seedContacts(): array
    {
        $kossi = $this->makeUser(['name' => 'Kossi Mensah']);
        $ama = $this->makeUser(['name' => 'Ama Doe']);
        $iphone = $this->makeArticle($kossi);
        $velo = $this->makeArticle($ama, ['titre' => 'Vélo de course', 'lieu' => 'Kara']);
        $afi = $this->makeUser(['name' => 'Afi Acheteuse', 'telephone' => '+22891112233']);

        $this->actingAs($afi)->clic(['canal' => 'whatsapp', 'vendeur_id' => $kossi->id, 'article_id' => $iphone->id]);
        $this->actingAs($afi)->clic(['canal' => 'appel', 'vendeur_id' => $ama->id, 'article_id' => $velo->id]);
        Sanctum::actingAs($this->makeUser(['name' => 'Yao Mobile']));
        $this->postJson("/api/v1/shops/{$kossi->id}/contact", ['canal' => 'appel'])->assertNoContent();
        auth()->forgetGuards();

        return [$kossi, $ama, $iphone, $velo, $this->makeUser(['role' => 'admin'])];
    }

    public function test_statistics_page_has_a_contacts_tab_and_cards(): void
    {
        [$kossi, , , , $admin] = $this->seedContacts();

        $response = $this->actingAs($admin)->get(route('admin.statistics.index', ['periode' => '7j']))->assertOk();
        $stats = $response->viewData('stats');

        $this->assertSame(2, $stats['kpis']['appels']['valeur']);
        $this->assertSame(1, $stats['kpis']['whatsapp']['valeur']);
        $this->assertSame(2, $stats['kpis']['vendeurs_contactes']);
        $this->assertSame($kossi->id, $stats['top_contactes'][0]['id']);
        $this->assertSame(2, $stats['top_contactes'][0]['contacts']);
        $this->assertCount(3, $stats['derniers_contacts']);
        $this->assertSame(2, array_sum($stats['series']['appels']));

        $response->assertSee('data-panel="contacts"', false)
            ->assertSee('Vendeur le plus contacté')
            ->assertSee('Appels aux vendeurs')
            ->assertSee('Vendeurs les plus contactés')
            ->assertSee('Afi Acheteuse')
            ->assertSee(route('admin.statistics.details', ['type' => 'contacts', 'canal' => 'appel', 'periode' => '7j']));

        // Le filtre ville ne garde que les contacts liés à Kara
        $kara = $this->actingAs($admin)->get(route('admin.statistics.index', ['periode' => '7j', 'ville' => 'Kara']))->viewData('stats');
        $this->assertSame(1, $kara['kpis']['appels']['valeur']);
        $this->assertSame(0, $kara['kpis']['whatsapp']['valeur']);
        $this->assertSame('Ama Doe', $kara['top_contactes'][0]['nom']);

        $this->actingAs($admin)->get(route('admin.statistics.export', ['section' => 'contactes', 'periode' => '7j']))
            ->assertOk()
            ->assertDownload();
    }

    public function test_contacts_journal_lists_filters_searches_and_exports(): void
    {
        [$kossi, $ama, , $velo, $admin] = $this->seedContacts();
        $this->actingAs($admin);
        $journal = fn (array $q = []) => $this->get(route('admin.statistics.details', ['type' => 'contacts', 'periode' => '7j'] + $q))->assertOk();

        $this->assertSame(3, $journal()->viewData('detail')['total']);
        $journal()->assertSee('Afi Acheteuse')->assertSee('+22891112233')->assertSee('Yao Mobile')->assertSee('Sa boutique')->assertSee('Appli mobile');

        $this->assertSame(2, $journal(['canal' => 'appel'])->viewData('detail')['total']);
        $this->assertSame(1, $journal(['origine' => 'appli'])->viewData('detail')['total']);
        $this->assertSame(2, $journal(['vendeur' => $kossi->id])->viewData('detail')['total']);
        $this->assertSame(1, $journal(['annonce' => $velo->id])->viewData('detail')['total']);
        $this->assertSame(1, $journal(['q' => 'Vélo'])->viewData('detail')['total']);
        $journal(['canal' => 'whatsapp'])->assertSee('Canal : WhatsApp');
        foreach (['date', 'canal', 'vendeur', 'visiteur'] as $tri) {
            $journal(['tri' => $tri, 'sens' => 'asc']);
        }

        $csv = $this->get(route('admin.statistics.details.export', ['type' => 'contacts', 'periode' => '7j']))->assertOk()->streamedContent();
        $this->assertStringContainsString('Afi Acheteuse', $csv);
        $this->assertStringContainsString('WhatsApp', $csv);

        $ranking = $this->get(route('admin.statistics.details', ['type' => 'vendeurs-contactes', 'periode' => '7j']))->assertOk()->viewData('detail');
        $this->assertSame(2, $ranking['total']);
        $this->assertSame('Kossi Mensah', $ranking['lignes'][0]['personne']['nom']);
        $this->assertSame(1, $ranking['lignes'][0]['whatsapp']);
        $this->assertSame($ama->id, (int) $this->get(route('admin.statistics.details', ['type' => 'vendeurs-contactes', 'periode' => '7j', 'q' => 'Ama']))->viewData('detail')['paginator']->items()[0]->id);
    }

    public function test_article_fiche_shows_its_contacts(): void
    {
        [, , $iphone, , $admin] = $this->seedContacts();

        $fiche = $this->actingAs($admin)->get(route('admin.statistics.annonce', ['article' => $iphone->id, 'periode' => '7j']))
            ->assertOk()
            ->assertSee('Contacts WhatsApp')
            ->assertSee(route('admin.statistics.details', ['type' => 'contacts', 'annonce' => $iphone->id, 'canal' => 'whatsapp', 'periode' => '7j']))
            ->viewData('fiche');

        $this->assertSame(1, $fiche['kpis']['whatsapp']['valeur']);
        $this->assertSame(0, $fiche['kpis']['appels']['valeur']);
        $this->assertSame(1, array_sum($fiche['series']['contacts']));
    }

    public function test_contact_screens_render_without_contacts_table(): void
    {
        \Illuminate\Support\Facades\Schema::drop('stat_contacts');
        $seller = $this->makeUser();
        $article = $this->makeArticle($seller);
        $this->actingAs($this->makeUser(['role' => 'admin']));

        $this->get(route('admin.statistics.index'))->assertOk()->assertSee('Aucun appel ni WhatsApp');
        $this->get(route('admin.statistics.details', ['type' => 'contacts', 'tri' => 'vendeur']))->assertOk()->assertSee('pas encore activé');
        $this->get(route('admin.statistics.details', ['type' => 'vendeurs-contactes']))->assertOk()->assertSee('pas encore activé');
        $this->get(route('admin.statistics.annonce', ['article' => $article->id]))->assertOk();
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
