<?php

namespace Tests\Feature;

use App\Mail\AdminActivityMail;
use App\Models\Article;
use App\Models\Message;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class AdminMailNotificationTest extends TestCase
{
    use RefreshDatabase;

    private int $categorieId;
    private int $sousCategorieId;
    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->categorieId = DB::table('categories')->insertGetId(['nom' => 'Électronique', 'created_at' => now(), 'updated_at' => now()]);
        $this->sousCategorieId = DB::table('sous_categories')->insertGetId([
            'nom' => 'Téléphones', 'categorie_id' => $this->categorieId, 'created_at' => now(), 'updated_at' => now(),
        ]);
        $this->admin = User::factory()->create(['role' => 'admin']);
    }

    private function articleData(array $overrides = []): array
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

    private function makeArticle(User $owner, array $attributes = []): Article
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

    public function test_seller_creating_an_article_emails_the_admin(): void
    {
        Mail::fake();
        $seller = User::factory()->create(['role' => 'user', 'name' => 'Koffi']);

        $this->actingAs($seller)
            ->post(route('articles.store'), $this->articleData([
                'photos' => [UploadedFile::fake()->image('photo.jpg', 400, 400)],
            ]))
            ->assertRedirect(route('mes_annonces'));

        $article = Article::where('user_id', $seller->id)->firstOrFail();
        foreach (['photo', 'photo1', 'photo2', 'photo3', 'photo4', 'photo5', 'photo6'] as $col) {
            if ($article->$col) {
                File::delete(public_path($article->$col));
            }
        }

        Mail::assertSent(AdminActivityMail::class, function (AdminActivityMail $mail) use ($article) {
            return $mail->hasTo('lomeplus80@gmail.com')
                && str_contains($mail->subjectLine, 'Nouvel article')
                && $mail->details['Vendeur'] === 'Koffi'
                && $mail->actionUrl === route('admin.articles.show', $article);
        });
        Mail::assertSentCount(1);
    }

    public function test_seller_updating_an_article_emails_the_admin_with_changed_fields(): void
    {
        Mail::fake();
        $seller = User::factory()->create(['role' => 'user']);
        $article = $this->makeArticle($seller);

        $this->actingAs($seller)
            ->put(route('articles.update', $article), $this->articleData(['prix_ht' => 300000, 'titre' => 'iPhone 13 Pro Max']))
            ->assertRedirect(route('mes_annonces'));

        Mail::assertSent(AdminActivityMail::class, function (AdminActivityMail $mail) {
            return str_contains($mail->subjectLine, 'à revalider')
                && str_contains($mail->details['Champs modifiés'], 'Titre')
                && str_contains($mail->details['Champs modifiés'], 'Prix')
                && ! str_contains($mail->details['Champs modifiés'], 'Lieu');
        });
    }

    public function test_seller_message_emails_the_admin(): void
    {
        Mail::fake();
        $seller = User::factory()->create(['role' => 'user', 'name' => 'Ama']);

        $this->actingAs($seller)
            ->post(route('messages.send'), ['body' => "Bonjour,\nmon article n'apparaît pas."])
            ->assertRedirect(route('messages.inbox'));

        $message = Message::where('sender_id', $seller->id)->firstOrFail();
        Mail::assertSent(AdminActivityMail::class, function (AdminActivityMail $mail) use ($message, $seller) {
            return $mail->subjectLine === 'Nouveau message de Ama'
                && $mail->bodyText === $message->body
                && $mail->replyToAddress === $seller->email
                && $mail->actionUrl === route('admin.messages.show', $message);
        });
    }

    public function test_admin_actions_do_not_send_emails(): void
    {
        Mail::fake();
        $article = $this->makeArticle($this->admin);

        $this->actingAs($this->admin)
            ->put(route('articles.update', $article), $this->articleData(['prix_ht' => 1000]))
            ->assertRedirect(route('mes_annonces'));

        Mail::assertNothingSent();
    }

    public function test_address_can_be_changed_in_config(): void
    {
        Mail::fake();
        config(['mail.admin_notification_address' => 'autre@example.com']);
        $seller = User::factory()->create(['role' => 'user']);

        $this->actingAs($seller)->post(route('messages.send'), ['body' => 'Bonjour admin']);

        Mail::assertSent(AdminActivityMail::class, fn ($mail) => $mail->hasTo('autre@example.com'));
    }

    public function test_mail_failure_does_not_break_the_seller_action(): void
    {
        Mail::shouldReceive('to')->andThrow(new \RuntimeException('SMTP down'));
        $seller = User::factory()->create(['role' => 'user']);

        $this->actingAs($seller)
            ->post(route('messages.send'), ['body' => 'Bonjour admin'])
            ->assertRedirect(route('messages.inbox'));

        $this->assertDatabaseHas('messages', ['sender_id' => $seller->id, 'body' => 'Bonjour admin']);
    }

    public function test_email_renders_and_escapes_user_content(): void
    {
        $mail = new AdminActivityMail(
            subjectLine: 'Test',
            heading: 'Titre',
            details: ['Vendeur' => '<b>Koffi</b>'],
            bodyText: "Ligne 1\n<script>x</script>",
            actionUrl: 'https://www.lomeplus.com/admin',
            actionLabel: 'Ouvrir',
        );

        $html = $mail->render();
        $this->assertStringContainsString('&lt;b&gt;Koffi&lt;/b&gt;', $html);
        $this->assertStringContainsString('&lt;script&gt;', $html);
        $this->assertStringContainsString('Ligne 1<br', $html);
    }
}
