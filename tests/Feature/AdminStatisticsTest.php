<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\User;
use App\Services\AdminStatistics;
use App\Services\StatTracker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AdminStatisticsTest extends TestCase
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

    // ------------------------------------------------------------------
    // Accès à la page
    // ------------------------------------------------------------------

    public function test_admin_can_view_statistics_page(): void
    {
        $admin = $this->makeUser(['role' => 'admin']);
        $this->makeArticle($this->makeUser());

        $this->actingAs($admin)
            ->get(route('admin.statistics.index', ['periode' => '7j']))
            ->assertOk()
            ->assertSee('Vendeurs qui publient le plus')
            ->assertSee('Ce que cherchent les visiteurs');
    }

    public function test_statistics_page_is_forbidden_to_non_admins(): void
    {
        $this->actingAs($this->makeUser())
            ->get(route('admin.statistics.index'))
            ->assertForbidden();

        auth()->logout();

        $this->get(route('admin.statistics.index'))->assertRedirect(route('login'));
    }

    public function test_every_period_and_grouping_renders(): void
    {
        $admin = $this->makeUser(['role' => 'admin']);
        $this->makeArticle($this->makeUser());

        foreach (array_keys(AdminStatistics::PERIODS) as $periode) {
            foreach (array_keys(AdminStatistics::GRANULARITIES) as $par) {
                $this->actingAs($admin)
                    ->get(route('admin.statistics.index', ['periode' => $periode, 'par' => $par, 'du' => '2026-01-01', 'au' => '2026-02-01']))
                    ->assertOk();
            }
        }
    }

    public function test_csv_export_is_downloadable_and_neutralises_formulas(): void
    {
        $admin = $this->makeUser(['role' => 'admin']);
        $seller = $this->makeUser(['name' => '=HYPERLINK("http://evil")']);
        $this->makeArticle($seller);

        $response = $this->actingAs($admin)->get(route('admin.statistics.export', ['section' => 'vendeurs']));

        $response->assertOk();
        $this->assertStringContainsString('text/csv', $response->headers->get('Content-Type'));

        $csv = $response->streamedContent();
        $this->assertStringStartsWith("\xEF\xBB\xBF", $csv);
        $this->assertStringContainsString("'=HYPERLINK", $csv);

        $this->actingAs($admin)->get('/admin/statistiques/export/inconnu')->assertNotFound();
    }

    // ------------------------------------------------------------------
    // Suivi des vues et visites
    // ------------------------------------------------------------------

    public function test_article_view_is_counted_once_per_visitor_per_hour(): void
    {
        $seller = $this->makeUser();
        $article = $this->makeArticle($seller);
        $visitor = $this->makeUser();

        $this->actingAs($visitor)->get($article->url())->assertOk();
        $this->actingAs($visitor)->get($article->url())->assertOk();

        $this->assertSame(1, DB::table('stat_visites')->where('type', 'article')->count());
        $this->assertDatabaseHas('stat_visites', [
            'article_id' => $article->id,
            'vendeur_id' => $seller->id,
            'visiteur_id' => $visitor->id,
        ]);

        // Un autre visiteur compte
        $this->actingAs($this->makeUser())->get($article->url())->assertOk();
        $this->assertSame(2, DB::table('stat_visites')->where('type', 'article')->count());
    }

    public function test_owner_admin_and_bots_are_not_counted(): void
    {
        $seller = $this->makeUser();
        $article = $this->makeArticle($seller);

        $this->actingAs($seller)->get($article->url())->assertOk();
        $this->actingAs($this->makeUser(['role' => 'admin']))->get($article->url())->assertOk();
        $this->actingAs($this->makeUser())
            ->withHeader('User-Agent', 'Mozilla/5.0 (compatible; Googlebot/2.1)')
            ->get($article->url())
            ->assertOk();

        $this->assertSame(0, DB::table('stat_visites')->count());
    }

    public function test_shop_visit_is_counted(): void
    {
        $seller = $this->makeUser(['name' => 'Kossi Mensah']);
        $this->makeArticle($seller);

        $this->actingAs($this->makeUser())->get($seller->shopUrl())->assertOk();

        $this->assertDatabaseHas('stat_visites', ['type' => 'boutique', 'vendeur_id' => $seller->id, 'article_id' => null]);
    }

    // ------------------------------------------------------------------
    // Suivi des recherches
    // ------------------------------------------------------------------

    public function test_live_search_keystrokes_are_merged_into_one_search(): void
    {
        $this->makeArticle($this->makeUser(), ['titre' => 'Ordinateur portable HP']);
        $visitor = $this->makeUser();

        foreach (['ord', 'ordi', 'ordinateur'] as $q) {
            $this->actingAs($visitor)->getJson('/search?q=' . $q, ['X-Requested-With' => 'XMLHttpRequest'])->assertOk();
        }

        $this->assertSame(1, DB::table('stat_recherches')->count());
        $this->assertDatabaseHas('stat_recherches', ['terme' => 'ordinateur', 'terme_normalise' => 'ordinateur', 'resultats' => 1]);

        // Une nouvelle recherche différente crée une nouvelle ligne
        $this->actingAs($visitor)->getJson('/search?q=frigo', ['X-Requested-With' => 'XMLHttpRequest'])->assertOk();
        $this->assertSame(2, DB::table('stat_recherches')->count());
        $this->assertDatabaseHas('stat_recherches', ['terme_normalise' => 'frigo', 'resultats' => 0]);
    }

    public function test_search_from_homepage_is_recorded(): void
    {
        $this->makeArticle($this->makeUser(), ['titre' => 'Réfrigérateur Samsung']);

        $this->actingAs($this->makeUser())->get('/?q=Réfrigérateur')->assertOk();

        $this->assertDatabaseHas('stat_recherches', ['terme_normalise' => 'refrigerateur', 'source' => 'accueil', 'resultats' => 1]);
    }

    public function test_tracking_never_breaks_the_page_when_tables_are_missing(): void
    {
        $article = $this->makeArticle($this->makeUser());
        \Illuminate\Support\Facades\Schema::drop('stat_visites');

        $this->actingAs($this->makeUser())->get($article->url())->assertOk();
    }

    // ------------------------------------------------------------------
    // Calculs
    // ------------------------------------------------------------------

    public function test_rankings_and_counts_are_computed(): void
    {
        $top = $this->makeUser(['name' => 'Gros vendeur']);
        $small = $this->makeUser(['name' => 'Petit vendeur']);

        $this->makeArticle($top, ['titre' => 'iPhone 11 propre', 'lieu' => 'Lomé']);
        $this->makeArticle($top, ['titre' => 'iPhone 12', 'lieu' => 'lome']);
        $this->makeArticle($top, ['titre' => 'Samsung Galaxy', 'lieu' => 'Kara']);
        $viewed = $this->makeArticle($small, ['titre' => 'Robe wax', 'lieu' => 'LOMÉ', 'status' => 'pending']);

        $visitor = $this->makeUser();
        $this->actingAs($visitor)->get($viewed->url());
        $this->actingAs($visitor)->get($small->shopUrl());

        $stats = AdminStatistics::fromRequest(Request::create('/', 'GET', ['periode' => '7j']))->all(refresh: true);

        $this->assertSame(4, $stats['kpis']['annonces']['valeur']);
        $this->assertSame(3, $stats['kpis']['inscriptions']['valeur']); // 2 vendeurs + 1 visiteur
        $this->assertSame('Gros vendeur', $stats['top_vendeurs'][0]['nom']);
        $this->assertSame(3, $stats['top_vendeurs'][0]['annonces']);

        // « Lomé », « lome » et « LOMÉ » sont une seule ville
        $this->assertSame(3, $stats['top_villes'][0]['annonces']);
        $this->assertSame(2, count($stats['top_villes']));

        $this->assertSame('iphone', mb_strtolower($stats['top_mots']['mots'][0]['mot']));
        $this->assertSame(2, $stats['top_mots']['mots'][0]['annonces']);

        $this->assertSame('Électronique', $stats['top_categories'][0]['nom']);
        $this->assertSame(100.0, $stats['top_categories'][0]['part']);

        $this->assertSame($viewed->id, $stats['top_articles_vus'][0]['id']);
        $this->assertSame('Petit vendeur', $stats['top_boutiques'][0]['nom']);
        $this->assertSame(1, $stats['top_boutiques'][0]['visites_boutique']);
        $this->assertSame($viewed->id, $stats['top_boutiques'][0]['meilleure_annonce']['id']);

        $this->assertSame(1, $stats['repartitions']['statut']['En attente']);
        $this->assertSame(3, $stats['repartitions']['statut']['Approuvées']);

        // URLs mises en cache sans hôte
        $this->assertStringStartsWith('/boutique/', $stats['top_boutiques'][0]['boutique_url']);
    }

    public function test_previous_period_comparison(): void
    {
        $this->travelTo(now()->subDays(10));
        $this->makeUser();
        $this->travelBack();

        $this->makeUser();
        $this->makeUser();

        $stats = AdminStatistics::fromRequest(Request::create('/', 'GET', ['periode' => '7j']))->all(refresh: true);

        $this->assertSame(2, $stats['kpis']['inscriptions']['valeur']);
        $this->assertSame(1, $stats['kpis']['inscriptions']['precedent']);
        $this->assertSame(100.0, $stats['kpis']['inscriptions']['evolution']);
        $this->assertSame(3, end($stats['series']['inscrits_cumules']));
    }

    public function test_cached_result_is_not_reused_after_tracking_tables_are_created(): void
    {
        $request = Request::create('/', 'GET', ['periode' => '7j']);

        // Calcul (mis en cache) avant le `migrate` des tables de suivi
        \Illuminate\Support\Facades\Schema::rename('stat_visites', 'stat_visites_tmp');
        $before = AdminStatistics::fromRequest($request)->all();
        $this->assertFalse($before['tracking']['visites']);

        // Tables créées : le résultat sans suivi ne doit pas être resservi
        \Illuminate\Support\Facades\Schema::rename('stat_visites_tmp', 'stat_visites');
        $after = AdminStatistics::fromRequest($request)->all();
        $this->assertTrue($after['tracking']['visites']);
    }

    public function test_ville_filter_restricts_article_statistics_only(): void
    {
        $seller = $this->makeUser();
        $this->makeArticle($seller, ['lieu' => 'Lomé', 'titre' => 'iPhone 13']);
        $this->makeArticle($seller, ['lieu' => 'lome', 'titre' => 'iPhone 12']);
        $kara = $this->makeArticle($seller, ['lieu' => 'Kara', 'titre' => 'Moto Haojue']);

        $visitor = $this->makeUser();
        $this->actingAs($visitor)->get($kara->url());

        $stats = AdminStatistics::fromRequest(Request::create('/', 'GET', ['periode' => '7j', 'ville' => 'LOMÉ']))->all(refresh: true);

        $this->assertSame(2, $stats['kpis']['annonces']['valeur']);   // Lomé + lome
        $this->assertSame(1, count($stats['top_villes']));
        $this->assertSame(0, $stats['kpis']['vues_annonces']['valeur']); // la vue concerne Kara
        $this->assertSame(2, $stats['kpis']['inscriptions']['valeur']);  // global : vendeur + visiteur
        $this->assertSame('LOMÉ', $stats['filtre']);
        $this->assertSame('iphone', mb_strtolower($stats['top_mots']['mots'][0]['mot']));

        $karaStats = AdminStatistics::fromRequest(Request::create('/', 'GET', ['periode' => '7j', 'ville' => 'Kara']))->all(refresh: true);
        $this->assertSame(1, $karaStats['kpis']['annonces']['valeur']);
        $this->assertSame(1, $karaStats['kpis']['vues_annonces']['valeur']);
        $this->assertSame($kara->id, $karaStats['top_articles_vus'][0]['id']);
    }

    public function test_categorie_and_sous_categorie_filters(): void
    {
        $autreCategorie = DB::table('categories')->insertGetId(['nom' => 'Mode', 'created_at' => now(), 'updated_at' => now()]);
        $chaussures = DB::table('sous_categories')->insertGetId(['nom' => 'Chaussures', 'categorie_id' => $autreCategorie, 'created_at' => now(), 'updated_at' => now()]);

        $seller = $this->makeUser();
        $this->makeArticle($seller);
        $this->makeArticle($seller, ['sous_categorie_id' => $chaussures, 'titre' => 'Baskets Nike']);
        $this->makeArticle($seller, ['sous_categorie_id' => $chaussures, 'titre' => 'Sandales cuir']);

        $categoryStats = AdminStatistics::fromRequest(Request::create('/', 'GET', ['periode' => '7j', 'categorie' => 'c' . $autreCategorie]))->all(refresh: true);
        $this->assertSame(2, $categoryStats['kpis']['annonces']['valeur']);
        $this->assertSame('Mode', $categoryStats['top_categories'][0]['nom']);
        $this->assertSame(1, count($categoryStats['top_categories']));
        $this->assertSame('Mode', $categoryStats['filtre']);

        $sousStats = AdminStatistics::fromRequest(Request::create('/', 'GET', ['periode' => '7j', 'categorie' => 's' . $this->sousCategorieId]))->all(refresh: true);
        $this->assertSame(1, $sousStats['kpis']['annonces']['valeur']);
        $this->assertSame('Électronique › Téléphones', $sousStats['filtre']);

        // Filtre inconnu : aucune annonce, pas d'erreur
        $none = AdminStatistics::fromRequest(Request::create('/', 'GET', ['periode' => '7j', 'categorie' => 'c999999']))->all(refresh: true);
        $this->assertSame(0, $none['kpis']['annonces']['valeur']);

        $admin = $this->makeUser(['role' => 'admin']);
        $this->actingAs($admin)
            ->get(route('admin.statistics.index', ['categorie' => 's' . $chaussures, 'ville' => 'Lomé']))
            ->assertOk()
            ->assertSee('Filtre actif');
    }

    public function test_custom_dates_and_curve_bounds(): void
    {
        $statistics = AdminStatistics::fromRequest(Request::create('/', 'GET', ['du' => '2026-03-04', 'au' => '2026-03-20', 'par' => 'semaine']));

        $this->assertSame('perso', $statistics->period); // dates sans période = personnalisée
        $series = $statistics->all(refresh: true)['series'];

        // Les semaines à cheval sont bornées à la période choisie
        $this->assertSame('2026-03-04', $series['debuts'][0]);
        $this->assertSame('2026-03-20', end($series['fins']));
        $this->assertSame(count($series['labels']), count($series['debuts']));

        // Date invalide : période par défaut
        $invalid = AdminStatistics::fromRequest(Request::create('/', 'GET', ['periode' => 'perso', 'du' => '2026-02-31']));
        $this->assertNotSame('2026-03-03', $invalid->from->toDateString());

        // Mois dernier : du 1er au dernier jour du mois précédent
        $this->travelTo(\Carbon\Carbon::parse('2026-10-15 10:00'));
        $lastMonth = AdminStatistics::fromRequest(Request::create('/', 'GET', ['periode' => 'mois_dernier']));
        $this->assertSame('2026-09-01', $lastMonth->from->toDateString());
        $this->assertSame('2026-09-30', $lastMonth->to->toDateString());
    }

    public function test_normalize_groups_accents_and_case(): void
    {
        $this->assertSame('telephone samsung', StatTracker::normalize('  Téléphone   SAMSUNG! '));
        $this->assertSame('machine a laver', StatTracker::normalize('Machine à laver'));
    }
}
