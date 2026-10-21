@php
    $homeBlogLocale = (string) app()->getLocale();
    $homeBlogFallbackLocale = (string) config('app.fallback_locale', 'hr');
    $homeHasManagedBlog = collect([
        $homeHeroBlocks, $homeHeroBenefitsBlocks, $homeCategoriesBlocks,
        $homeBeforeProductsBlocks, $homeAfterProductsBlocks, $homeBottomBlocks,
    ])->flatten(1)->contains(fn ($item) => in_array((string) data_get($item, 'block.type'), ['blogs_carousel', 'blog_grid_3', 'blogs'], true));
    $homeBlogPosts = collect();

    if (! $homeHasManagedBlog && app(\App\Services\Catalog\CatalogFeatureService::class)->useBlog()) {
        $homeBlogPosts = \App\Models\Content\Blog\BlogPost::query()
            ->where('is_active', true)
            ->where('published_at', '<=', now())
            ->whereHas('translations', fn ($query) => $query->whereIn('locale', [$homeBlogLocale, $homeBlogFallbackLocale]))
            ->with(['translations' => fn ($query) => $query->whereIn('locale', [$homeBlogLocale, $homeBlogFallbackLocale]), 'media'])
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->limit(3)
            ->get();
    }
@endphp

@if ($homeBlogPosts->isNotEmpty())
    @once('herrera-home-b2b-styles')
        @push('styles')
            <link rel="stylesheet" href="{{ asset('front-theme/styles/herrera-home-b2b.css') }}?v={{ filemtime(public_path('front-theme/styles/herrera-home-b2b.css')) }}">
        @endpush
    @endonce
    @once('libermedia-home-blog-styles')
        @push('styles')
            <link rel="stylesheet" href="{{ asset('front-theme/styles/herrera-home-blog.css') }}?v={{ filemtime(public_path('front-theme/styles/herrera-home-blog.css')) }}">
        @endpush
    @endonce

    <section class="herrera-home-section herrera-b2b-products herrera-home-blog-widget libermedia-home-blog" aria-labelledby="libermedia-home-blog-title" data-libermedia-home-blog>
        <div class="herrera-home-heading">
            <div>
                <p class="herrera-home-section-eyebrow">{{ __('LIBER MEDIA BLOG') }}</p>
                <h2 id="libermedia-home-blog-title">{{ __('Ideje za igru i učenje') }}</h2>
            </div>
            <a class="herrera-home-products-link" href="{{ route('blog.index') }}">{{ __('Svi članci') }} <x-fa-icon name="arrow-right" /></a>
        </div>
        <ul class="libermedia-home-blog-grid">
            @foreach ($homeBlogPosts as $homeBlogPost)
                @php
                    $homeBlogTranslation = $homeBlogPost->translations->firstWhere('locale', $homeBlogLocale)
                        ?? $homeBlogPost->translations->firstWhere('locale', $homeBlogFallbackLocale);
                @endphp
                <li>
                    @include('front.partials.herrera-blog-card', ['blogCardPost' => $homeBlogPost, 'blogCardLocale' => $homeBlogLocale, 'blogCardFallbackLocale' => $homeBlogFallbackLocale])
                    <a class="libermedia-home-blog-read" href="{{ route('blog.show', ['slug' => $homeBlogTranslation->slug]) }}">{{ __('Pročitaj članak') }} <x-fa-icon name="arrow-right" /></a>
                </li>
            @endforeach
        </ul>
    </section>
@endif
