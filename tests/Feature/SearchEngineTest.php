<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\Categorie;
use App\Models\SousCategorie;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Tests\Concerns\BuildsMarketplace;
use Tests\TestCase;

/**
 * Moteur en cascade, lot 1 : niveaux 1 à 4, filet de l'ancien moteur, mises à jour de l'index.
 */
class SearchEngineTest extends TestCase
{
    use RefreshDatabase, BuildsMarketplace;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpMarketplace();
        config(['search.engine' => 'cascade']);
    }

    private function titles(string $q, array $query = []): array
    {
        return $this->matched($this->getJson('/api/v1/articles?' . http_build_query(['q' => $q, 'per_page' => 50] + $query))->assertOk());
    }

    private function searchTitles(string $q): array
    {
        return $this->matched($this->getJson('/api/v1/articles/search?' . http_build_query(['q' => $q, 'per_page' => 50]))->assertOk());
    }

    /** Titres trouvés. Le secours n'en fait pas partie : il propose, il ne trouve pas. */
    private function matched($response): array
    {
        return $response->json('recherche.secours') ? [] : collect($response->json('data'))->pluck('titre')->all();
    }

    private function ad(string $titre, string $description = 'Bien entretenu.', array $attributes = []): Article
    {
        return $this->makeArticle($this->seller(), ['titre' => $titre, 'description' => $description] + $attributes);
    }

    public function test_exact_phrase_comes_before_words_in_any_order(): void
    {
        $this->ad('Pro 13 iPhone reconditionné');
        $this->ad('iPhone 13 Pro');

        $this->assertSame(['iPhone 13 Pro', 'Pro 13 iPhone reconditionné'], $this->titles('iphone 13'));
    }

    public function test_words_found_in_any_field_and_any_order(): void
    {
        $this->ad('Samsung Galaxy', 'Excellent appareil photo, modèle S21.');

        $this->assertSame(['Samsung Galaxy'], $this->titles('s21 samsung'));
    }

    public function test_most_of_the_weight_is_enough_and_a_number_alone_never_is(): void
    {
        foreach (range(1, 6) as $i) {
            $this->ad("Chaise bois {$i}", 'Lot de 8 pièces.');
        }
        $this->ad('Frigo Samsung');
        $this->ad('Etagere 2000', 'Vendue seule.');

        $this->assertContains('Frigo Samsung', $this->titles('frigo chaise'));
        $this->assertNotContains('Etagere 2000', $this->titles('frigo 2000'), 'level 3 alone must not match on a number');
    }

    public function test_stop_words_never_earn_a_level(): void
    {
        $this->ad('Table basse', 'Je cherche un acheteur pour cette table.');
        $this->ad('Téléphone Tecno');

        $this->assertSame('Téléphone Tecno', $this->titles('je cherche un telephone')[0]);
    }

    public function test_case_accents_and_plurals_are_ignored(): void
    {
        $this->ad('Téléphones portables');

        foreach (['telephones portables', 'TÉLÉPHONES', 'telephone', 'portable'] as $q) {
            $this->assertSame(['Téléphones portables'], $this->titles($q), $q);
        }
    }

    public function test_glued_and_cut_words_are_found(): void
    {
        $seller = $this->seller();
        $this->makeArticle($seller, ['titre' => 'iPhone 13 noir', 'description' => 'Comme neuf.']);
        $this->makeArticle($seller, ['titre' => 'iPhone SE 2020', 'description' => 'Comme neuf.']);
        $this->makeArticle($seller, ['titre' => 'Chaussures Lanvin', 'description' => 'Pointure 42.']);

        $this->assertContains('iPhone 13 noir', $this->titles('iphone13'));
        $this->assertContains('iPhone SE 2020', $this->titles('iphonese2020'));
        $this->assertContains('Chaussures Lanvin', $this->titles('chaussureslanvin'));
        $this->assertContains('iPhone 13 noir', $this->titles('i phone'));
    }

    public function test_safety_net_keeps_everything_the_old_engine_found_after_better_matches(): void
    {
        foreach (range(1, 35) as $i) {
            $this->ad("Ordi {$i}", 'Ordi portable.');
        }
        $this->ad('Belle vaisselle ordinaire', 'Service complet.');

        $titles = $this->titles('ordi');

        $this->assertCount(36, $titles);
        $this->assertSame('Belle vaisselle ordinaire', end($titles), 'the middle-of-word match stays, in last position');
    }

    public function test_new_engine_returns_at_least_what_the_old_one_returned(): void
    {
        $seller = $this->seller(['name' => 'Koffi Ablodé', 'ville' => 'Sokodé']);
        $this->makeArticle($seller, ['titre' => 'Sac à main']);
        $this->ad('Belle lampe', 'Elle éclaire bien.');
        $this->ad('Vélo', 'Pour une balade, à Kpalimé.', ['lieu' => 'Kpalimé']);

        foreach (['Ablodé', 'Sokodé', 'Téléphones', 'Électronique', 'ell', 'bal', 'Kpal'] as $q) {
            config(['search.engine' => 'legacy']);
            $old = $this->searchTitles($q);
            Cache::flush();
            config(['search.engine' => 'cascade']);
            $new = $this->searchTitles($q);

            $this->assertNotEmpty($old, $q);
            $this->assertEqualsCanonicalizing($old, array_values(array_intersect($new, $old)), $q);
        }
    }

    public function test_user_filters_stay_strict_at_every_level(): void
    {
        $this->ad('Frigo Kara', attributes: ['lieu' => 'Kara']);
        $this->ad('Frigo Lomé', attributes: ['lieu' => 'Lomé']);
        $this->ad('Refrigo Kara', attributes: ['lieu' => 'Kara']);

        $this->assertEqualsCanonicalizing(['Frigo Kara', 'Refrigo Kara'], $this->titles('frigo', ['ville' => 'Kara']));
        $this->assertSame(['Frigo Lomé'], $this->titles('frigo', ['prix_max' => 350000, 'ville' => 'Lomé']));
    }

    public function test_price_sort_replaces_the_cascade_order(): void
    {
        $this->ad('Frigo cher', attributes: ['prix_ht' => 900]);
        $this->ad('Frigo moyen', attributes: ['prix_ht' => 500]);
        $this->ad('Refrigo pas cher', attributes: ['prix_ht' => 100]);

        $this->assertSame(['Refrigo pas cher', 'Frigo moyen', 'Frigo cher'], $this->titles('frigo', ['order_by' => 'prix_asc']));
        $this->assertSame(['Frigo cher', 'Frigo moyen', 'Refrigo pas cher'], $this->titles('frigo', ['order_by' => 'prix_desc']));
    }

    public function test_boost_never_beats_a_stricter_level(): void
    {
        $this->ad('Lampe solaire', 'Grande lampe.', ['boosted_until' => now()->addDay()]);
        $this->ad('Solaire lampe');
        $this->ad('Lampe solaire puissante');

        $titles = $this->titles('solaire lampe');

        $this->assertSame('Solaire lampe', $titles[0]);
        $this->assertSame('Lampe solaire', $titles[1], 'within a level, the boosted ad comes first');
    }

    public function test_pagination_has_no_duplicate_and_no_missing_ad(): void
    {
        foreach (range(1, 12) as $i) {
            $this->ad("Frigo {$i}");
        }
        foreach (range(1, 12) as $i) {
            $this->ad("Refrigo {$i}");
        }

        $seen = [];
        foreach ([1, 2, 3] as $page) {
            $response = $this->getJson('/api/v1/articles/search?' . http_build_query(['q' => 'frigo', 'per_page' => 10, 'page' => $page]))->assertOk();
            array_push($seen, ...collect($response->json('data'))->pluck('titre')->all());
        }

        $this->assertCount(24, $seen);
        $this->assertCount(24, array_unique($seen));
        $this->assertSame(24, $response->json('meta.total'));
    }

    public function test_moderation_decides_what_is_searchable(): void
    {
        $article = $this->makeArticle($this->seller(), ['titre' => 'Xylophone rare', 'status' => 'pending']);
        $this->assertSame([], $this->titles('xylophone'));

        $article->approve();
        $this->assertSame(['Xylophone rare'], $this->titles('xylophone'));

        $article->block('Interdit');
        $this->assertSame([], $this->titles('xylophone'));
        $this->assertDatabaseMissing('search_documents', ['article_id' => $article->id]);
    }

    public function test_a_new_word_is_searchable_as_soon_as_the_ad_is_published_edited_or_deleted(): void
    {
        $article = $this->ad('Trottinette Zorglub');
        $this->assertSame(['Trottinette Zorglub'], $this->titles('zorglub'));

        $article->update(['titre' => 'Trottinette Bidule']);
        $this->assertSame(['Trottinette Bidule'], $this->titles('bidule'));
        $this->assertSame([], $this->titles('zorglub'));

        $article->delete();
        $this->assertSame([], $this->titles('bidule'));
        $this->assertDatabaseMissing('search_postings', ['article_id' => $article->id]);
    }

    public function test_renaming_a_seller_or_a_category_updates_the_index(): void
    {
        $seller = $this->seller(['name' => 'Ancien Nom']);
        $article = $this->makeArticle($seller, ['titre' => 'Sac']);

        $seller->update(['name' => 'Nouveaunom']);
        SousCategorie::find($this->sousCategorieId)->update(['nom' => 'Maroquinerie']);
        Categorie::find($this->categorieId)->update(['nom' => 'Accessoires']);

        $document = DB::table('search_documents')->where('article_id', $article->id)->first();
        $this->assertStringContainsString('nouveaunom', $document->vendeur);
        $this->assertStringContainsString('maroquinerie accessoires', $document->categories);
    }

    public function test_deleting_a_seller_removes_their_ads_from_the_index(): void
    {
        $seller = $this->seller();
        $article = $this->makeArticle($seller, ['titre' => 'Sac']);

        $seller->delete();

        $this->assertDatabaseMissing('search_documents', ['article_id' => $article->id]);
    }

    public function test_rebuild_command_recreates_the_index(): void
    {
        $article = $this->ad('Tondeuse');
        $this->makeArticle($this->seller(), ['titre' => 'Bloquée', 'status' => 'blocked']);
        DB::table('search_postings')->delete();
        DB::table('search_documents')->delete();

        $this->artisan('search:rebuild')->assertSuccessful();

        $this->assertSame(1, DB::table('search_documents')->count());
        $this->assertSame(['Tondeuse'], $this->titles('tondeuse'));
        $this->assertDatabaseHas('search_terms', ['terme' => 'tondeuse', 'df' => 1]);
        $this->assertDatabaseHas('search_documents', ['article_id' => $article->id]);
    }

    public function test_an_index_failure_never_blocks_saving_an_ad(): void
    {
        DB::statement('DROP TABLE search_postings');

        $article = $this->ad('Annonce sans index');

        $this->assertDatabaseHas('articles', ['id' => $article->id]);
    }

    public function test_short_and_unusual_queries_work(): void
    {
        $this->ad('PC portable');
        $this->ad('TV 50 pouces', '100% neuve.');

        $this->assertContains('PC portable', $this->searchTitles('pc'));
        $this->assertContains('PC portable', $this->searchTitles('p'));
        $this->assertSame(['TV 50 pouces'], $this->searchTitles('100%'));
        $this->assertSame([], $this->searchTitles('%%%'));
        $this->assertSame([], $this->searchTitles('zzzzzz'));
        $this->get(route('article.search', ['q' => 'pc']))->assertOk()->assertSee('PC portable');
    }

    public function test_site_pages_use_the_engine_and_stats_keep_the_level(): void
    {
        $this->ad('Téléphones portables');

        $this->get(route('articles.index', ['q' => 'telephone']))->assertOk()->assertSee('Téléphones portables');
        $this->getJson(route('search.articles', ['q' => 'portable']), ['X-Requested-With' => 'XMLHttpRequest'])->assertOk()->assertJsonPath('total', 1);

        $this->assertDatabaseHas('stat_recherches', ['terme_normalise' => 'telephone', 'resultats' => 1]);
        $this->assertSame(4, (int) DB::table('stat_recherches')->where('terme_normalise', 'portable')->value('niveau'), 'portable matches portables after normalization');
    }
}
