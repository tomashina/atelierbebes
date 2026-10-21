<?php

namespace Tests\Feature\Front;

use App\Models\Content\Blog\BlogPost;
use App\Models\Content\ContentBlock;
use App\Models\Settings\Local\Language;
use App\Services\Settings\SystemSettingsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class LibermediaHomeBlogTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'app.locale' => 'hr',
            'app.fallback_locale' => 'hr',
            'storefront.theme' => 'herrera',
            'storefront.home' => 'libermedia',
            'storefront_cache.enabled' => false,
            'legacy_media.enabled' => true,
            'legacy_media.origin' => 'https://legacy.example.test',
        ]);
        app(SystemSettingsService::class)->putMany([
            'store_brand_name' => 'Liber Media',
            'catalog_use_blog' => true,
        ]);
    }

    public function test_home_shows_the_latest_three_published_translated_articles_with_their_covers_and_links(): void
    {
        $this->article('older', 'Stariji članak', now()->subDays(4)->toDateTimeString());
        $third = $this->article('third', 'Treći članak', now()->subDays(3)->toDateTimeString());
        $second = $this->article('second', 'Drugi članak', now()->subDays(2)->toDateTimeString());
        $latest = $this->article('latest', 'Najnoviji članak', now()->subDay()->toDateTimeString());
        $this->article('inactive', 'Neaktivan članak', now()->subHour()->toDateTimeString())->update(['is_active' => false]);
        $this->article('future', 'Budući članak', now()->addDay()->toDateTimeString());
        $this->article('untranslated', 'Članak bez prijevoda', now()->subMinutes(10)->toDateTimeString(), 'it');
        BlogPost::query()->create(['code' => 'no-translation', 'is_active' => true, 'published_at' => now()]);

        $response = $this->get('/')->assertOk()
            ->assertSee('data-libermedia-home-blog', false)
            ->assertSee('LIBER MEDIA BLOG')
            ->assertSee('Svi članci')
            ->assertSee('href="'.route('blog.index').'"', false)
            ->assertSeeInOrder(['Najnoviji članak', 'Drugi članak', 'Treći članak'])
            ->assertSee('Sažetak: Najnoviji članak')
            ->assertSee('https://legacy.example.test/image/catalog/blog/latest.jpg', false)
            ->assertDontSee('Stariji članak')
            ->assertDontSee('Neaktivan članak')
            ->assertDontSee('Budući članak')
            ->assertDontSee('Članak bez prijevoda');

        foreach ([$latest, $second, $third] as $post) {
            $response->assertSee('href="'.route('blog.show', ['slug' => $post->code]).'"', false);
        }
        $this->assertSame(3, substr_count($response->getContent(), 'class="herrera-blog-card"'));
    }

    public function test_home_prefers_the_requested_language_and_uses_the_store_language_as_fallback(): void
    {
        Language::query()->create(['code' => 'en', 'locale' => 'en', 'name' => 'English', 'is_active' => true, 'is_default' => true]);
        $bilingual = $this->article('bilingual', 'Naslov na hrvatskom', now()->subDay()->toDateTimeString());
        $bilingual->translations()->create(['locale' => 'en', 'title' => 'English article title', 'slug' => 'english-article', 'excerpt' => 'English introduction']);
        $this->article('fallback', 'Hrvatski rezervni članak', now()->subDays(2)->toDateTimeString());

        $this->withSession(['front_locale' => 'en'])->get('/')->assertOk()
            ->assertSee('English article title')
            ->assertSee('English introduction')
            ->assertSee('Hrvatski rezervni članak')
            ->assertSee('href="'.route('blog.show', ['slug' => 'english-article']).'"', false)
            ->assertDontSee('Naslov na hrvatskom');
    }

    public function test_disabled_blog_does_not_render_the_home_article_section(): void
    {
        $this->article('disabled-blog', 'Članak isključenog bloga', now()->subDay()->toDateTimeString());
        app(SystemSettingsService::class)->put('catalog_use_blog', false);

        $this->get('/')->assertOk()
            ->assertDontSee('data-libermedia-home-blog', false)
            ->assertDontSee('Članak isključenog bloga');
    }

    #[DataProvider('managedBlogTypes')]
    public function test_an_existing_managed_blog_section_prevents_the_fallback_section(string $type): void
    {
        $this->article('managed-article', 'Članak iz upravljanog bloka', now()->subDay()->toDateTimeString());
        $this->blogBlock($type);

        $this->get('/')->assertOk()
            ->assertSee('Upravljani blog')
            ->assertDontSee('data-libermedia-home-blog', false);
    }

    public static function managedBlogTypes(): array
    {
        return [
            'carousel' => ['blogs_carousel'],
            'grid' => ['blog_grid_3'],
            'selected' => ['blogs'],
        ];
    }

    public function test_an_inactive_managed_blog_slot_does_not_hide_the_home_articles(): void
    {
        $this->article('active-article', 'Aktivan članak', now()->subDay()->toDateTimeString());
        $this->blogBlock('blogs_carousel')->slots()->update(['is_active' => false]);

        $this->get('/')->assertOk()
            ->assertSee('data-libermedia-home-blog', false)
            ->assertSee('Aktivan članak')
            ->assertDontSee('Upravljani blog');
    }

    private function article(string $code, string $title, string $publishedAt, string $locale = 'hr'): BlogPost
    {
        $post = BlogPost::query()->create([
            'code' => $code, 'is_active' => true, 'published_at' => $publishedAt,
            'payload' => ['opencart' => ['image' => 'catalog/blog/'.$code.'.jpg']],
        ]);
        $post->translations()->create([
            'locale' => $locale, 'title' => $title, 'slug' => $code,
            'excerpt' => 'Sažetak: '.$title, 'body_html' => '<p>Sadržaj članka</p>',
        ]);

        return $post;
    }

    private function blogBlock(string $type): ContentBlock
    {
        $block = ContentBlock::query()->create([
            'code' => 'managed-home-blog', 'name' => 'Upravljani blog', 'type' => $type, 'is_active' => true,
        ]);
        $block->translations()->create(['locale' => 'hr', 'title' => 'Upravljani blog']);
        $block->slots()->create(['placement' => 'home.bottom', 'frontend_variant' => 'desktop', 'is_active' => true]);

        return $block;
    }
}
