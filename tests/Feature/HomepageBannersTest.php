<?php

namespace Tests\Feature;

use App\Models\Publicite;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\BuildsMarketplace;
use Tests\TestCase;

/**
 * Carrousel « À la une » au-dessus des annonces (style new.lomeplus.com).
 */
class HomepageBannersTest extends TestCase
{
    use RefreshDatabase, BuildsMarketplace;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpMarketplace();
        // La page d'accueil « vide » affiche un autre bloc : il faut au moins une annonce.
        $this->makeArticle($this->seller());
    }

    private function pub(array $attrs): Publicite
    {
        return Publicite::create(array_merge([
            'image' => 'advertisements/test.png',
            'is_active' => true,
            'ordre' => 0,
        ], $attrs));
    }

    public function test_carousel_sits_above_annonces_with_top_and_header_ads(): void
    {
        $this->pub(['titre' => 'Pub-header', 'position' => 'header', 'lien_url' => 'https://exemple.tg']);
        $this->pub(['titre' => 'Pub-top', 'position' => 'homepage_top']);
        $this->pub(['titre' => 'Pub-inactive', 'position' => 'header', 'is_active' => false]);
        $this->pub(['titre' => 'Pub-footer', 'position' => 'footer']);

        $html = $this->get(route('articles.index'))->assertOk()->getContent();

        $carousel = strpos($html, 'class="site-banners"');
        $this->assertNotFalse($carousel);
        $this->assertLessThan(strpos($html, '</i>Annonces</h4>'), $carousel);

        // homepage_top d'abord, puis header ; ni inactive ni autre position.
        $this->assertLessThan(strpos($html, 'Pub-header'), strpos($html, 'Pub-top'));
        $this->assertStringNotContainsString('Pub-inactive', $html);
        // Image en entier : pas de titre par-dessus, le titre sert de texte alternatif.
        $this->assertStringContainsString('alt="Pub-header"', $html);
        $this->assertStringNotContainsString('site-banner-headline">Pub-header', $html);
        $this->assertSame(2, substr_count($html, 'data-site-banners-dot='));
        $this->assertStringContainsString('href="https://exemple.tg"', $html);

        // Les anciens bandeaux empilés ne sont plus rendus.
        $this->assertStringNotContainsString('publicites-header', $html);
        $this->assertStringNotContainsString('publicites-homepage_top', $html);
    }

    public function test_ad_without_image_falls_back_to_orange_text_banner(): void
    {
        $this->pub(['titre' => 'Pub-texte', 'position' => 'header', 'image' => '']);

        $html = $this->get(route('articles.index'))->assertOk()->getContent();

        $this->assertStringContainsString('site-banner-headline">Pub-texte', $html);
    }

    public function test_no_carousel_without_ads(): void
    {
        $html = $this->get(route('articles.index'))->assertOk()->getContent();

        $this->assertStringNotContainsString('class="site-banners"', $html);
    }
}
