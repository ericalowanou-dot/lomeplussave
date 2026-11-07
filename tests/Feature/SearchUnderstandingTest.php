<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Services\Search\QueryIntent;
use App\Services\Search\TextNormalizer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Concerns\BuildsMarketplace;
use Tests\TestCase;

/**
 * Moteur en cascade, lots 2 et 3 : débuts de mot, fautes, phrases, secours, suggestions.
 */
class SearchUnderstandingTest extends TestCase
{
    use RefreshDatabase, BuildsMarketplace;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpMarketplace();
        config(['search.engine' => 'cascade']);
    }

    private function search(string $q, array $query = [])
    {
        return $this->getJson('/api/v1/articles?' . http_build_query(['q' => $q, 'per_page' => 50] + $query))->assertOk();
    }

    private function titles(string $q): array
    {
        $response = $this->search($q);

        return $response->json('recherche.secours') ? [] : collect($response->json('data'))->pluck('titre')->all();
    }

    private function ad(string $titre, string $description = 'Bien entretenu.', array $attributes = []): Article
    {
        return $this->makeArticle($this->seller(), ['titre' => $titre, 'description' => $description] + $attributes);
    }

    public function test_truncated_words(): void
    {
        $this->ad('Batterie lithium 100Ah');
        $this->ad('Canne à pêche télescopique');
        $this->ad('Guitare acoustique');

        $this->assertSame(['Batterie lithium 100Ah'], $this->titles('batterie li'));
        $this->assertSame(['Canne à pêche télescopique'], $this->titles('canne a pech'));
        $this->assertSame(['Guitare acoustique'], $this->titles('guit'));
        $this->assertSame(['Batterie lithium 100Ah'], $this->titles('batt lith'));
    }

    public function test_ell_finds_electronique_before_belle_vaisselle(): void
    {
        // Catégorie Électronique, dans le jeu de base
        $this->ad('Tablette Samsung', attributes: ['created_at' => now()]);
        $this->ad('Casque Sony', attributes: ['created_at' => now()->subDay()]);
        $this->ad('Belle vaisselle', 'Un service complet.', ['sous_categorie_id' => $this->otherSousCategorieId()]);

        $this->assertSame(['Tablette Samsung', 'Casque Sony', 'Belle vaisselle'], $this->titles('ell'));
        $this->assertSame(['Tablette Samsung', 'Casque Sony'], $this->titles('elec'));
    }

    public function test_truncated_word_with_a_typo(): void
    {
        $this->ad('iPhone 11');
        $this->ad('iPhone 12');

        foreach (['ipo', 'ipk', 'ipj'] as $q) {
            $this->assertContains('iPhone 11', $this->titles($q), $q);
        }
    }

    public function test_typos_and_phonetic_mistakes(): void
    {
        $this->ad('Téléphone Tecno');
        $this->ad('Ordinateur portable');
        $this->ad('Chaussures en cuir');
        $this->ad('Concombre de mer séché');
        $this->ad('Siège coiffeur');
        $this->ad('Mini réfrigérateur');

        $this->assertSame('Téléphone Tecno', $this->titles('telefone')[0]); // les autres sont dans la catégorie Téléphones
        $this->assertSame(['Ordinateur portable'], $this->titles('ordinatuer'));
        $this->assertSame(['Chaussures en cuir'], $this->titles('chausure'));
        $this->assertSame(['Concombre de mer séché'], $this->titles('cocombre de mer'));
        $this->assertSame(['Siège coiffeur'], $this->titles('siege coffeur'));
        $this->assertSame(['Mini réfrigérateur'], $this->titles('mini refligilateur'));
    }

    public function test_correction_never_replaces_the_original_word(): void
    {
        $this->ad('Telefone importé');
        $this->ad('Téléphone Tecno');

        $this->assertSame('Telefone importé', $this->titles('telefone')[0]);
    }

    public function test_short_words_are_never_corrected(): void
    {
        $this->ad('Lot de vis');

        $this->assertSame([], $this->titles('vus'));
    }

    public function test_phrases_keep_useful_words(): void
    {
        $this->ad('Frigo en bon état', 'Marche bien.');
        $this->ad('Ordinateur portable HP');
        $this->ad('Moto Yamaha', attributes: ['prix_ht' => 250000]);

        $this->assertContains('Frigo en bon état', $this->titles("j'ai besoin d'un frigo en bon état"));
        $this->assertContains('Ordinateur portable HP', $this->titles('je veux acheter un ordinatuer portable'));
        $this->assertContains('Moto Yamaha', $this->titles('vends moto yamaha moins de 300000'));
        $this->assertContains('Frigo en bon état', $this->titles('bonjour je voudrais vraiment pouvoir trouver rapidement pour ma grande famille un frigo si possible aujourd hui merci beaucoup'));
    }

    public function test_city_and_price_rank_higher_but_never_exclude(): void
    {
        $this->ad('Téléphone Kara', attributes: ['lieu' => 'Kara', 'prix_ht' => 500000, 'created_at' => now()]);
        $this->ad('Téléphone Lomé premium', attributes: ['lieu' => 'Lomé', 'prix_ht' => 900000, 'created_at' => now()->subDays(100)]);
        $this->ad('Téléphone Lomé abordable', attributes: ['lieu' => 'Lomé', 'prix_ht' => 50000, 'created_at' => now()->subDays(100)]);
        $this->artisan('search:refresh')->assertSuccessful();

        $titles = $this->titles('je cherche un téléphone pas cher à Lomé');

        $this->assertSame('Téléphone Lomé abordable', $titles[0]);
        $this->assertCount(3, $titles, 'city and price never exclude an ad');
    }

    public function test_number_goes_before_the_word_it_qualifies_and_never_stays_alone(): void
    {
        $this->ad('iPhone 12');
        $this->ad('Lot de 7 chaises');

        $this->assertSame(['iPhone 12'], $this->titles('iphone 7'));
    }

    public function test_price_expressions(): void
    {
        $text = new TextNormalizer();
        $parse = fn (string $q) => QueryIntent::parse($text->normalize($q), ['lome', 'kara'], $text);

        $this->assertSame(50000.0, $parse('frigo moins de 50000')->prixMax);
        $this->assertSame(50000.0, $parse('frigo max 50 000 F')->prixMax);
        $this->assertSame(50000.0, $parse('frigo 50k')->prixMax);
        $this->assertSame([20000.0, 40000.0], [$parse('entre 20000 et 40000')->prixMin, $parse('entre 20000 et 40000')->prixMax]);
        $this->assertNull($parse('tv 4k')->prixMax);
        $this->assertTrue($parse('frigo bon marché')->prixBas);
        $this->assertSame('kara', $parse('frigo à Kara')->ville);
        $this->assertSame(['frigo'], $parse('je cherche un frigo pas cher à Kara')->words);
    }

    public function test_cities_come_from_the_ads(): void
    {
        $this->ad('Vélo', attributes: ['lieu' => 'Vogan']);
        $this->ad('Vélo rouge', attributes: ['lieu' => 'Lomé']);

        $this->assertSame('vogan', $this->search('velo a vogan')->json('recherche.comprehension.ville'));
    }

    public function test_median_is_used_not_the_average(): void
    {
        $this->ad('A', attributes: ['prix_ht' => 100]);
        $this->ad('B', attributes: ['prix_ht' => 200]);
        $this->ad('C', attributes: ['prix_ht' => 9000000]);

        $this->artisan('search:refresh')->assertSuccessful();

        $this->assertSame(200.0, (float) DB::table('search_category_stats')->where('sous_categorie_id', $this->sousCategorieId)->value('prix_median'));
    }

    public function test_fallback_shows_ads_but_counts_as_no_result(): void
    {
        $this->ad('Lampe solaire');

        $this->getJson('/search?q=qsdfghjk', ['X-Requested-With' => 'XMLHttpRequest'])->assertOk()->assertJsonPath('total', 1);
        $response = $this->search('poiuytreza');

        $this->assertTrue($response->json('recherche.secours'));
        $this->assertSame('Lampe solaire', $response->json('data.0.titre'));
        $this->assertDatabaseHas('stat_recherches', ['terme_normalise' => 'qsdfghjk', 'resultats' => 0, 'niveau' => 9]);
    }

    public function test_fallback_prefers_the_closest_category(): void
    {
        $this->ad('Armoire', attributes: ['sous_categorie_id' => $this->otherSousCategorieId(), 'created_at' => now()->subDay()]);
        $this->ad('Nouveau produit');

        $response = $this->search('moubbes zzz');

        $this->assertSame('Meubles', $response->json('recherche.categorie_proche'));
        $this->assertSame('Armoire', $response->json('data.0.titre'));
    }

    public function test_page_shows_what_was_understood_and_how_to_remove_it(): void
    {
        $this->ad('Téléphone Tecno');

        $this->get(route('article.search', ['q' => 'je cherche un telefone pas cher à Lomé']))
            ->assertOk()
            ->assertSee('Résultats pour')
            ->assertSee('telephone')
            ->assertSee('prix bas')
            ->assertSee('Lome');
    }

    public function test_page_shows_the_fallback_message(): void
    {
        $this->ad('Lampe solaire');

        $this->get(route('article.search', ['q' => 'poiuytreza']))->assertOk()->assertSee('Vous cherchiez peut-être')->assertSee('Lampe solaire');
    }

    public function test_suggestions(): void
    {
        $this->ad('Lampe solaire');

        $this->getJson('/search/suggestions?q=tele')->assertOk()->assertJsonPath('suggestions.0', ['type' => 'categorie', 'label' => 'Téléphones']);
        $this->getJson('/search/suggestions?q=lam')->assertJsonPath('suggestions.0', ['type' => 'titre', 'label' => 'Lampe solaire']);
        $this->getJson('/api/v1/articles/suggestions?q=sol')->assertOk()->assertJsonPath('suggestions.0.label', 'Lampe solaire');
        $this->getJson('/search/suggestions?q=lampr')->assertJsonPath('correction', 'lampe');
        $this->getJson('/search/suggestions?q=l')->assertJsonPath('suggestions', []);
    }

    public function test_suggestions_never_show_what_other_visitors_typed(): void
    {
        DB::table('stat_recherches')->insert(['terme' => 'recherche privée', 'terme_normalise' => 'recherche privee', 'resultats' => 0, 'visiteur_hash' => 'x', 'created_at' => now(), 'updated_at' => now()]);

        $this->getJson('/search/suggestions?q=recherche')->assertJsonPath('suggestions', []);
    }

    public function test_a_new_word_works_exact_with_a_typo_truncated_and_in_a_sentence(): void
    {
        $this->ad('Autre annonce');
        $this->ad('Trampoline Zorgluxe');

        foreach (['zorgluxe', 'zorgluse', 'zorgl', 'je cherche un zorgluxe pas cher'] as $q) {
            $this->assertContains('Trampoline Zorgluxe', $this->titles($q), $q);
        }
    }

    public function test_refresh_reconciles_the_index_with_the_ads(): void
    {
        $missing = $this->ad('Oublié');
        $stale = $this->ad('Bloqué');
        DB::table('search_documents')->where('article_id', $missing->id)->delete();
        DB::table('articles')->where('id', $stale->id)->update(['status' => 'blocked']);

        $this->artisan('search:refresh')->assertSuccessful();

        $this->assertDatabaseHas('search_documents', ['article_id' => $missing->id]);
        $this->assertDatabaseMissing('search_documents', ['article_id' => $stale->id]);
    }

    public function test_compare_command_reports_no_regression_on_recorded_searches(): void
    {
        $this->ad('Belle lampe');
        DB::table('stat_recherches')->insert([
            ['terme' => 'lampe', 'terme_normalise' => 'lampe', 'resultats' => 1, 'visiteur_hash' => 'a', 'created_at' => now(), 'updated_at' => now()],
            ['terme' => 'ell', 'terme_normalise' => 'ell', 'resultats' => 1, 'visiteur_hash' => 'b', 'created_at' => now(), 'updated_at' => now()],
            ['terme' => 'zzzzzz', 'terme_normalise' => 'zzzzzz', 'resultats' => 0, 'visiteur_hash' => 'c', 'created_at' => now(), 'updated_at' => now()],
        ]);

        $this->artisan('search:compare')->assertSuccessful();
    }

    public function test_the_refresh_is_scheduled_every_night(): void
    {
        $events = collect(app(\Illuminate\Console\Scheduling\Schedule::class)->events());

        $this->assertTrue($events->contains(fn ($event) => str_contains((string) $event->command, 'search:refresh') && $event->expression === '0 3 * * *'));
    }
}
