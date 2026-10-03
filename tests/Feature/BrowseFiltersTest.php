<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\BuildsMarketplace;
use Tests\TestCase;

/**
 * Filtres et tri de la page d'accueil (mêmes règles pour l'API mobile).
 */
class BrowseFiltersTest extends TestCase
{
    use RefreshDatabase, BuildsMarketplace;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpMarketplace();
    }

    private function titles(array $query): array
    {
        $html = $this->get(route('articles.index', $query))->assertOk()->getContent();
        preg_match_all('/Annonce-(\w+)/', $html, $m);

        return array_values(array_unique($m[1]));
    }

    public function test_filters_and_sorting(): void
    {
        $seller = $this->seller();
        $other = $this->otherSousCategorieId();

        $this->makeArticle($seller, ['titre' => 'Annonce-cher', 'prix_ht' => 900, 'neuf' => true, 'lieu' => 'Lomé', 'created_at' => now()->subDays(3)]);
        $this->makeArticle($seller, ['titre' => 'Annonce-moyen', 'prix_ht' => 500, 'livraison' => true, 'lieu' => 'Kara', 'created_at' => now()->subDays(2)]);
        $this->makeArticle($seller, ['titre' => 'Annonce-meuble', 'prix_ht' => 100, 'sous_categorie_id' => $other, 'lieu' => 'Lomé', 'created_at' => now()->subDay()]);
        $this->makeArticle($seller, ['titre' => 'Annonce-boost', 'prix_ht' => 300, 'lieu' => 'Sokodé', 'boosted_until' => now()->addDay(), 'created_at' => now()->subDays(5)]);

        $this->assertSame(['boost', 'meuble', 'moyen', 'cher'], $this->titles([]));
        $this->assertSame(['meuble', 'boost', 'moyen', 'cher'], $this->titles(['order_by' => 'prix_asc']));
        $this->assertSame(['cher', 'moyen', 'boost', 'meuble'], $this->titles(['order_by' => 'prix_desc']));
        $this->assertSame(['meuble'], $this->titles(['sous_categorie' => $other]));
        $this->assertSame(['boost', 'moyen', 'cher'], $this->titles(['categorie' => $this->categorieId]));
        $this->assertSame(['boost', 'moyen'], $this->titles(['prix_min' => 300, 'prix_max' => 500]));
        $this->assertSame(['cher'], $this->titles(['etat' => 'neuf']));
        $this->assertSame(['moyen'], $this->titles(['livraison_only' => 1]));
        $this->assertSame(['boost'], $this->titles(['pro_only' => 1]));
        $this->assertSame(['meuble', 'cher'], $this->titles(['ville' => 'Lomé']));
        $this->assertSame(['meuble'], $this->titles(['q' => 'meuble']));
    }
}
