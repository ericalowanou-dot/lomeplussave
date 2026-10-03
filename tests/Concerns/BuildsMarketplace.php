<?php

namespace Tests\Concerns;

use App\Models\Article;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Données de base communes aux tests du site : catégories, annonces, vendeurs.
 * Les photos envoyées pendant un test sont effacées à la fin du test.
 */
trait BuildsMarketplace
{
    protected int $categorieId;
    protected int $sousCategorieId;

    /** @var array<string, true> */
    private array $uploadsBefore = [];

    protected function setUpMarketplace(): void
    {
        $this->uploadsBefore = array_fill_keys($this->listUploads(), true);
        $this->beforeApplicationDestroyed(function () {
            foreach ($this->listUploads() as $file) {
                if (! isset($this->uploadsBefore[$file])) {
                    @unlink(public_path($file));
                }
            }
        });

        $this->categorieId = DB::table('categories')->insertGetId(['nom' => 'Électronique', 'created_at' => now(), 'updated_at' => now()]);
        $this->sousCategorieId = DB::table('sous_categories')->insertGetId([
            'nom' => 'Téléphones', 'categorie_id' => $this->categorieId, 'created_at' => now(), 'updated_at' => now(),
        ]);
    }

    protected function otherSousCategorieId(): int
    {
        $categorieId = DB::table('categories')->insertGetId(['nom' => 'Maison', 'created_at' => now(), 'updated_at' => now()]);

        return DB::table('sous_categories')->insertGetId([
            'nom' => 'Meubles', 'categorie_id' => $categorieId, 'created_at' => now(), 'updated_at' => now(),
        ]);
    }

    protected function articleData(array $overrides = []): array
    {
        return $overrides + [
            'categorie' => $this->categorieId,
            'sous_categorie_id' => $this->sousCategorieId,
            'titre' => 'iPhone 13 Pro',
            'prix_ht' => 350000,
            'lieu' => 'Lomé',
            'description' => 'Très bon état, vendu avec chargeur et boîte.',
            'etat' => 'occasion',
        ];
    }

    protected function makeArticle(User $owner, array $attributes = []): Article
    {
        $article = new Article();
        $article->forceFill($attributes + [
            'user_id' => $owner->id,
            'titre' => 'iPhone 13 Pro',
            'description' => 'Très bon état, vendu avec chargeur et boîte.',
            'prix_ht' => 350000,
            'lieu' => 'Lomé',
            'sous_categorie_id' => $this->sousCategorieId,
            'neuf' => false,
            'livraison' => false,
            'photo' => 'articles/test.jpg',
            'status' => 'approved',
        ])->save();

        return $article;
    }

    protected function seller(array $attributes = []): User
    {
        return User::factory()->create($attributes + ['role' => 'user', 'telephone' => '+22890000000']);
    }

    protected function uploadExists(?string $path): bool
    {
        return $path !== null && $path !== '' && is_file(public_path($path));
    }

    /** @return list<string> */
    private function listUploads(): array
    {
        $files = [];
        foreach (['articles', 'users/profil'] as $dir) {
            foreach (glob(public_path($dir) . '/*') ?: [] as $file) {
                $files[] = $dir . '/' . basename($file);
            }
        }

        return $files;
    }

    protected function admin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }
}
