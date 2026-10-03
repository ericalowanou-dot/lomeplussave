<?php

namespace Tests\Feature;

use App\Models\Comment;
use App\Models\Message;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Tests\Concerns\BuildsMarketplace;
use Tests\TestCase;

/**
 * Likes, commentaires, messages au support et dépenses de coins.
 */
class InteractionsTest extends TestCase
{
    use RefreshDatabase, BuildsMarketplace;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpMarketplace();
        Mail::fake();
    }

    // ── Likes ────────────────────────────────────────────────

    public function test_like_then_unlike(): void
    {
        $article = $this->makeArticle($this->seller());
        $fan = $this->seller();

        $this->actingAs($fan)->postJson(route('articles.like', $article))
            ->assertOk()->assertExactJson(['liked' => true, 'likeCount' => 1]);
        $this->assertNotNull(DB::table('article_user_like')->value('created_at'));

        $this->actingAs($fan)->postJson(route('articles.like', $article))
            ->assertOk()->assertExactJson(['liked' => false, 'likeCount' => 0]);
    }

    public function test_likes_of_several_users_add_up(): void
    {
        $article = $this->makeArticle($this->seller());

        $this->actingAs($this->seller())->postJson(route('articles.like', $article));
        $this->actingAs($this->seller())->postJson(route('articles.like', $article))
            ->assertJson(['liked' => true, 'likeCount' => 2]);
    }

    public function test_guest_cannot_like(): void
    {
        $article = $this->makeArticle($this->seller());

        $this->postJson(route('articles.like', $article))->assertUnauthorized();
        $this->assertSame(0, DB::table('article_user_like')->count());
    }

    public function test_liked_articles_appear_in_favorites(): void
    {
        $article = $this->makeArticle($this->seller(), ['titre' => 'Vélo favori']);
        $fan = $this->seller();
        $this->actingAs($fan)->postJson(route('articles.like', $article));

        $this->actingAs($fan)->get(route('mes_favoris'))->assertOk()->assertSee('Vélo favori');
    }

    // ── Commentaires ─────────────────────────────────────────

    public function test_comment_lifecycle(): void
    {
        $article = $this->makeArticle($this->seller());
        $author = $this->seller();

        $id = $this->actingAs($author)
            ->postJson(route('comments.store', $article), ['content' => '  Toujours disponible ?  '])
            ->assertOk()
            ->assertJsonPath('comment.content', 'Toujours disponible ?')
            ->assertJsonPath('comment.user.id', $author->id)
            ->json('comment.id');

        $comment = Comment::findOrFail($id);

        $this->actingAs($author)->putJson(route('comments.update', $comment), ['content' => 'Encore dispo ?'])
            ->assertOk()->assertJsonPath('comment.content', 'Encore dispo ?');

        $this->actingAs($author)->deleteJson(route('comments.destroy', $comment))->assertOk();
        $this->assertSame(0, Comment::count());
    }

    public function test_comment_content_is_validated(): void
    {
        $article = $this->makeArticle($this->seller());

        $this->actingAs($this->seller())->postJson(route('comments.store', $article), ['content' => 'ok'])
            ->assertStatus(422)->assertJsonValidationErrors('content');
    }

    public function test_only_the_author_edits_and_author_or_admin_deletes_a_comment(): void
    {
        $article = $this->makeArticle($this->seller());
        $comment = Comment::create(['content' => 'Bonjour', 'article_id' => $article->id, 'user_id' => $this->seller()->id]);
        $intruder = $this->seller();

        $this->actingAs($intruder)->putJson(route('comments.update', $comment), ['content' => 'Piraté !'])->assertForbidden();
        $this->actingAs($intruder)->deleteJson(route('comments.destroy', $comment))->assertForbidden();
        $this->assertSame('Bonjour', $comment->refresh()->content);

        $this->actingAs($this->admin())->deleteJson(route('comments.destroy', $comment))->assertOk();
        $this->assertSame(0, Comment::count());
    }

    public function test_a_comment_can_be_reported_once_per_user(): void
    {
        $article = $this->makeArticle($this->seller());
        $comment = Comment::create(['content' => 'Arnaque', 'article_id' => $article->id, 'user_id' => $this->seller()->id]);
        $reporter = $this->seller();

        $this->actingAs($reporter)->postJson(route('comments.report', $comment), ['reason' => 'Spam'])->assertOk();
        $this->actingAs($reporter)->postJson(route('comments.report', $comment), ['reason' => 'Spam'])->assertStatus(400);
        $this->assertSame(1, DB::table('comment_reports')->count());
    }

    public function test_comments_can_be_paginated(): void
    {
        $article = $this->makeArticle($this->seller());
        $author = $this->seller();
        foreach (range(1, 3) as $i) {
            Comment::create(['content' => "Commentaire $i", 'article_id' => $article->id, 'user_id' => $author->id]);
        }

        $this->actingAs($author)
            ->getJson(route('comments.loadMore', ['article' => $article, 'offset' => 1, 'limit' => 1]))
            ->assertOk()
            ->assertJsonCount(1, 'comments')
            ->assertJsonPath('has_more', true);
    }

    // ── Messages au support ──────────────────────────────────

    public function test_user_message_goes_to_the_admin_who_can_reply(): void
    {
        $admin = $this->admin();
        $user = $this->seller();

        $this->actingAs($user)->post(route('messages.send'), ['body' => 'Comment booster mon annonce ?'])
            ->assertRedirect(route('messages.inbox'));

        $message = Message::sole();
        $this->assertSame($user->id, $message->sender_id);
        $this->assertSame([$admin->id], $message->recipients()->pluck('users.id')->all());

        $this->actingAs($admin)->post(route('admin.messages.send'), [
            'body' => 'Utilisez vos coins.',
            'recipient_scope' => 'selected',
            'recipients' => [$user->id],
            'parent_message_id' => $message->id,
        ])->assertRedirect(route('admin.dashboard'));

        $reply = Message::where('parent_message_id', $message->id)->sole();
        $this->actingAs($user)->get(route('messages.inbox'))->assertOk()->assertSee(route('messages.show', $reply));

        $this->actingAs($user)->get(route('messages.show', $reply))->assertOk()->assertSee('Utilisez vos coins.');
        $this->assertNotNull($reply->recipients()->first()->pivot->read_at);
    }

    public function test_a_message_is_private_to_its_sender_and_recipients(): void
    {
        $this->admin();
        $user = $this->seller();
        $this->actingAs($user)->post(route('messages.send'), ['body' => 'Message privé']);
        $message = Message::sole();

        $this->actingAs($this->seller())->get(route('messages.show', $message))->assertForbidden();
        $this->actingAs($user)->get(route('messages.show', $message))->assertOk();
    }

    public function test_cannot_reply_to_someone_elses_message(): void
    {
        $this->admin();
        $this->actingAs($this->seller())->post(route('messages.send'), ['body' => 'Message privé']);
        $message = Message::sole();

        $this->actingAs($this->seller())
            ->post(route('messages.send'), ['body' => 'Je m incruste', 'parent_message_id' => $message->id])
            ->assertForbidden();
        $this->assertSame(1, Message::count());
    }

    public function test_message_body_is_required(): void
    {
        $this->admin();
        $this->actingAs($this->seller())->post(route('messages.send'), ['body' => ''])->assertSessionHasErrors('body');
        $this->assertSame(0, Message::count());
    }

    // ── Coins : boost et certification ───────────────────────

    public function test_boosting_spends_one_coin_per_day(): void
    {
        $seller = $this->seller(['coins' => 10]);
        $article = $this->makeArticle($seller);

        $this->actingAs($seller)->postJson(route('user.boost', $article), ['days' => 3])
            ->assertOk()->assertJson(['success' => true, 'coins' => 7]);

        $this->assertEqualsWithDelta(now()->addDays(3)->timestamp, $article->refresh()->boosted_until->timestamp, 5);
        $this->assertSame(7, (int) $seller->refresh()->coins);
    }

    public function test_boosting_again_extends_the_current_boost(): void
    {
        $seller = $this->seller(['coins' => 10]);
        $article = $this->makeArticle($seller, ['boosted_until' => now()->addDays(2)]);

        $this->actingAs($seller)->postJson(route('user.boost', $article), ['days' => 1])->assertOk();

        $this->assertEqualsWithDelta(now()->addDays(3)->timestamp, $article->refresh()->boosted_until->timestamp, 5);
    }

    public function test_boosting_needs_enough_coins_and_ownership(): void
    {
        $poor = $this->seller(['coins' => 1]);
        $article = $this->makeArticle($poor);

        $this->actingAs($poor)->postJson(route('user.boost', $article), ['days' => 5])->assertStatus(400);
        $this->assertNull($article->refresh()->boosted_until);
        $this->assertSame(1, (int) $poor->refresh()->coins);

        $rich = $this->seller(['coins' => 50]);
        $this->actingAs($rich)->postJson(route('user.boost', $article), ['days' => 5])->assertForbidden();
        $this->assertSame(50, (int) $rich->refresh()->coins);

        $this->actingAs($poor)->postJson(route('user.boost', $article), ['days' => 0])->assertStatus(422);
    }

    public function test_certification_spends_coins_and_certifies(): void
    {
        $user = $this->seller(['coins' => 30]);

        $this->actingAs($user)->postJson(route('user.certify'), ['days' => 30])
            ->assertOk()->assertJson(['success' => true, 'coins' => 0]);

        $user->refresh();
        $this->assertTrue($user->estCertifie());
        $this->assertSame(0, (int) $user->coins);

        $this->actingAs($user)->postJson(route('user.certify'), ['days' => 1])->assertStatus(400);
    }

    // ── Signalements ─────────────────────────────────────────

    public function test_a_shop_can_be_reported_once_but_not_your_own(): void
    {
        $shop = $this->seller();
        $reporter = $this->seller();

        $this->actingAs($reporter)->postJson(route('boutique.report', $shop), ['reason' => array_key_first(\App\Models\UserReport::REASONS)])
            ->assertOk();
        $this->actingAs($reporter)->postJson(route('boutique.report', $shop), ['reason' => array_key_first(\App\Models\UserReport::REASONS)])
            ->assertStatus(422);
        $this->actingAs($shop)->postJson(route('boutique.report', $shop), ['reason' => array_key_first(\App\Models\UserReport::REASONS)])
            ->assertStatus(422);
    }

    // ── Comptes bloqués ──────────────────────────────────────

    public function test_a_blocked_user_is_logged_out(): void
    {
        $blocked = $this->seller(['is_blocked' => true, 'block_reason' => 'Fraude']);

        $this->actingAs($blocked)->get(route('mes_annonces'))->assertRedirect(route('login'));
        $this->assertGuest();
    }
}
