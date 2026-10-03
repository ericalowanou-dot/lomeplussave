<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Services\AdminStatistics;
use App\Services\MediaStorage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\BuildsMarketplace;
use Tests\TestCase;

/**
 * Toutes les photos passent par un seul disque : on peut le remplacer par un
 * stockage cloud (MEDIA_DISK) sans changer le code ni les chemins en base.
 */
class MediaStorageTest extends TestCase
{
    use RefreshDatabase, BuildsMarketplace;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpMarketplace();
        Mail::fake();
    }

    /** Simule un stockage cloud servi par un CDN. */
    private function useCloudDisk(): void
    {
        Storage::fake('cloud', ['url' => 'https://cdn.example.com']);
        config([
            'filesystems.media' => 'cloud',
            'filesystems.disks.cloud.driver' => 's3',
        ]);
    }

    public function test_local_urls_point_to_the_site_and_fall_back_to_the_placeholder(): void
    {
        Storage::disk('uploads')->put('articles/a.jpg', 'x');

        $this->assertSame(asset('articles/a.jpg'), MediaStorage::url('articles/a.jpg'));
        $this->assertSame(asset('articles/a.jpg'), MediaStorage::url('/articles\\a.jpg'));
        $this->assertSame(asset(MediaStorage::PLACEHOLDER), MediaStorage::url('articles/manquante.jpg'));
        $this->assertSame(asset(MediaStorage::PLACEHOLDER), MediaStorage::url(null));
        $this->assertSame('https://exemple.com/p.jpg', MediaStorage::url('https://exemple.com/p.jpg'));
    }

    public function test_only_files_inside_image_folders_can_be_deleted(): void
    {
        Storage::disk('uploads')->put('articles/a.jpg', 'x');
        Storage::disk('uploads')->put('index.php', 'x');
        Storage::disk('uploads')->put('build/manifest.json', 'x');

        $this->assertFalse(MediaStorage::delete('index.php'));
        $this->assertFalse(MediaStorage::delete('articles/../index.php'));
        $this->assertFalse(MediaStorage::delete('build/manifest.json'));
        $this->assertTrue(Storage::disk('uploads')->exists('index.php'));

        $this->assertTrue(MediaStorage::delete('articles/a.jpg'));
        $this->assertFalse(Storage::disk('uploads')->exists('articles/a.jpg'));
    }

    public function test_images_can_only_be_stored_in_image_folders(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        MediaStorage::storeImage(UploadedFile::fake()->image('a.jpg'), 'build');
    }

    public function test_profile_photo_is_replaced_and_the_old_one_deleted(): void
    {
        $user = $this->seller();

        $this->actingAs($user)->post(route('user.update.ajax'), [
            'name' => 'Ama',
            'photo' => UploadedFile::fake()->image('moi.jpg', 200, 200),
        ])->assertOk()->assertJson(['success' => true, 'name' => 'Ama']);
        $first = $user->refresh()->photo_profil;
        $this->assertStringStartsWith('users/profil/', $first);
        $this->assertTrue($this->uploadExists($first));
        $this->assertSame(asset($first), $user->getProfilPhotoUrl());

        $this->actingAs($user)->patch(route('profile.update'), [
            'name' => 'Ama',
            'email' => $user->email,
            'telephone' => '+22890000000',
            'photo' => UploadedFile::fake()->image('moi2.jpg', 200, 200),
        ])->assertRedirect(route('profile.edit'));

        $second = $user->refresh()->photo_profil;
        $this->assertNotSame($first, $second);
        $this->assertTrue($this->uploadExists($second));
        $this->assertFalse($this->uploadExists($first));
    }

    public function test_admin_category_images_go_to_the_media_disk(): void
    {
        $this->actingAs($this->admin())->post(route('admin.categories.store'), [
            'nom' => 'Sport',
            'image' => UploadedFile::fake()->image('sport.png', 100, 100),
        ]);

        $image = \App\Models\Categorie::where('nom', 'Sport')->value('image');
        $this->assertStringStartsWith('categories/images/', $image);
        $this->assertTrue($this->uploadExists($image));
    }

    public function test_switching_to_a_cloud_disk_needs_no_code_change(): void
    {
        $this->useCloudDisk();
        $seller = $this->seller();

        $this->actingAs($seller)->post(route('articles.store'), $this->articleData([
            'photos' => [UploadedFile::fake()->image('a.jpg', 100, 100)],
        ]))->assertRedirect(route('mes_annonces'));

        $article = Article::sole();
        $this->assertTrue(Storage::disk('cloud')->exists($article->photo));
        $this->assertFalse(Storage::disk('uploads')->exists($article->photo));
        $this->assertSame('https://cdn.example.com/' . $article->photo, $article->photo_url);

        // Même chemin en base que sur le serveur : migrer = copier les fichiers + changer MEDIA_DISK
        $this->assertStringStartsWith('articles/', $article->photo);

        // Les statistiques admin gardent l'adresse complète du CDN
        $this->assertSame($article->photo_url, AdminStatistics::fromRequest(request())->relativeUrl($article->photo_url));
        $this->assertSame('/boutique/x-1', AdminStatistics::fromRequest(request())->relativeUrl('http://localhost/boutique/x-1'));

        $this->actingAs($seller)->delete(route('articles.destroy', $article));
        $this->assertFalse(Storage::disk('cloud')->exists($article->photo));
    }
}
