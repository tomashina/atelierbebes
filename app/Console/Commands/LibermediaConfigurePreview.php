<?php

namespace App\Console\Commands;

use App\Models\Catalog\Category\Category;
use App\Models\Catalog\Product\Product;
use App\Models\Content\Page\InfoPage;
use App\Services\Front\NavigationMenuService;
use App\Services\Settings\SystemSettingsService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class LibermediaConfigurePreview extends Command
{
    protected $signature = 'libermedia:configure-preview';

    protected $description = 'Apply the Herrera visual foundation with Liber Media content and branding';

    public function handle(SystemSettingsService $settings): int
    {
        if (! app()->environment('local') || config('app.name') !== 'Libermedia') {
            $this->error('Restricted to the local Libermedia preview.');

            return self::FAILURE;
        }

        $source = DB::connection('libermedia_source')->table('oc_setting')->where('store_id', 0)->pluck('value', 'key');
        $contactLinks = [];
        $email = trim((string) $source->get('config_email', ''));
        $phone = trim((string) $source->get('config_telephone', ''));
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $contactLinks[] = ['label' => $email, 'url' => 'mailto:'.$email, 'is_active' => true, 'sort_order' => 0];
        }
        if ($phone !== '') {
            $contactLinks[] = ['label' => $phone, 'url' => 'tel:'.preg_replace('/[^+0-9]/', '', $phone), 'is_active' => true, 'sort_order' => 1];
        }
        $aboutId = InfoPage::whereHas('translations', fn ($query) => $query->where('locale', 'hr')->where('slug', 'o-nama'))->value('id');
        $navigation = [
            ['type' => 'catalog', 'label' => 'Sve kategorije', 'url' => '/shop', 'is_active' => true, 'show_dropdown' => true, 'sort_order' => 0],
            ['type' => 'custom', 'label' => 'Brandovi', 'url' => '/brendovi', 'is_active' => true, 'sort_order' => 1],
            ['type' => 'custom', 'label' => 'Akcije', 'url' => '/akcije', 'is_active' => true, 'sort_order' => 2],
        ];
        if ($aboutId) {
            $navigation[] = ['type' => 'page', 'label' => 'O nama', 'page_id' => $aboutId, 'is_active' => true, 'sort_order' => 3];
        }
        $navigation[] = ['type' => 'custom', 'label' => 'Kontakt', 'url' => '/contact', 'is_active' => true, 'sort_order' => 4];
        $navigation[] = ['type' => 'custom', 'label' => 'Blog', 'url' => '/blog', 'is_active' => true, 'sort_order' => 5];

        $settings->putMany([
            'store_brand_logo_path' => 'assets/brand/libermedia-logo.png',
            'store_brand_logo_width' => 190, 'store_brand_logo_height' => 70,
            'store_announcement_enabled' => true,
            'store_announcement_text' => 'Liber Media · Didaktičke igračke i oprema za vrtiće',
            'store_announcement_background_color' => '#00B5CC', 'store_announcement_text_color' => '#ffffff',
            'store_benefits_bar_enabled' => false,
            'store_search_autocomplete_enabled' => true,
            'store_pricing_b2c_enabled' => true,
            'store_newsletter_title' => 'Novosti iz svijeta igre i učenja',
            'store_newsletter_subtitle' => 'Prijavite se za novosti iz Liber Media ponude.',
            'store_newsletter_button_label' => 'Prijavi se',
            'store_newsletter_consent_label' => 'Pristajem na primanje newslettera i obradu podataka za tu svrhu.',
            'store_footer_contact_intro' => 'Informacije o proizvodima i narudžbama.',
            'store_footer_col_1_title_translations' => ['hr' => 'Proizvodi'],
            'store_footer_col_2_title_translations' => ['hr' => 'Informacije'],
            'store_footer_col_3_title' => 'Korisne poveznice',
            'store_footer_col_3_title_translations' => ['hr' => 'Korisne poveznice'],
            'store_footer_col_3_custom_links' => "Moj račun|/auth/login\nBlog|/blog\nKontakt|/contact",
            'store_footer_social_facebook_enabled' => false,
            'store_footer_social_instagram_enabled' => false,
            'store_footer_social_tiktok_enabled' => false,
            'store_footer_social_youtube_enabled' => false,
            NavigationMenuService::SETTINGS_KEY => $navigation,
            NavigationMenuService::TOP_BAR_SETTINGS_KEY => [
                'is_enabled' => true, 'height' => 34, 'font_size' => 12,
                'background_color' => '#32373B', 'text_color' => '#ffffff', 'border_color' => '#32373B',
                'links' => $contactLinks, 'socials' => [],
            ],
            NavigationMenuService::APPEARANCE_SETTINGS_KEY => [
                'container_width' => 1860, 'header_content_width' => 1860,
                'background_color' => '#00B5CC', 'text_color' => '#ffffff', 'highlight_color' => '#32373B',
            ],
        ]);
        // The legacy shop has no category pictures. Use an existing product
        // photo from each category, without changing the original source data.
        $profile = config('media_profiles.models.'.Category::class);
        foreach ($profile['collections'] as &$collection) {
            $collection['conversions'] = [];
        }
        unset($collection);
        config(['media_profiles.models.'.Category::class => $profile]);
        foreach (Category::where('scope', 'catalog')->whereNull('parent_id')->with('media')->get() as $category) {
            if ($category->getFirstMedia('category_icon') || $category->getFirstMedia('category_banner')) {
                continue;
            }
            $ids = Category::descendantsAndSelf($category->id)->pluck('id');
            $product = Product::where('is_active', true)->whereHas('media', fn ($q) => $q->where('collection_name', 'product_main'))
                ->whereHas('categories', fn ($q) => $q->whereIn('categories.id', $ids))
                ->orderByRaw('CASE WHEN stock_qty > 0 THEN 0 ELSE 1 END')->orderBy('id')->first();
            $media = $product?->getFirstMedia('product_main');
            if ($media && is_file($media->getPath())) {
                $category->addMedia($media->getPath())->preservingOriginal()
                    ->withCustomProperties(['preview_product_id' => $product->id])
                    ->toMediaCollection('category_icon', 'public');
            }
        }
        $this->info('Liber Media preview configured using the Herrera layout.');

        return self::SUCCESS;
    }
}
