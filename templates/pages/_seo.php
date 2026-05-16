<?php namespace ProcessWire;

if(!function_exists('buildSeoData')) {
    function buildSeoData(Page $page, array $overrides = []) {
        $fieldSeoTitle = (isset($page->seo_title) && trim((string) $page->seo_title) !== '') ? trim((string) $page->seo_title) : '';
        $fieldSeoDescription = (isset($page->seo_description) && trim((string) $page->seo_description) !== '') ? trim((string) $page->seo_description) : '';
        $fieldSeoRobots = (isset($page->seo_robots) && trim((string) $page->seo_robots) !== '') ? trim((string) $page->seo_robots) : '';
        $fieldSeoCanonical = (isset($page->seo_canonical) && trim((string) $page->seo_canonical) !== '') ? trim((string) $page->seo_canonical) : '';

        $title = trim((string) ($overrides['title'] ?? ($fieldSeoTitle !== '' ? $fieldSeoTitle : $page->title)));

        $description = trim((string) ($overrides['description'] ?? $fieldSeoDescription));
        if($description === '') {
            if(isset($page->summary) && trim((string) $page->summary) !== '') {
                $description = trim((string) $page->summary);
            } elseif(isset($page->headline) && trim((string) $page->headline) !== '') {
                $description = trim((string) $page->headline);
            } else {
                $description = trim((string) $page->title);
            }
        }

        $canonical = trim((string) ($overrides['canonical'] ?? ($fieldSeoCanonical !== '' ? $fieldSeoCanonical : $page->httpUrl)));
        $robots = trim((string) ($overrides['robots'] ?? ($fieldSeoRobots !== '' ? $fieldSeoRobots : 'index,follow')));

        $image = trim((string) ($overrides['image'] ?? ''));
        if($image === '' && isset($page->seo_image) && $page->seo_image && $page->seo_image->count()) {
            $image = $page->seo_image->first()->httpUrl;
        }
        if($image === '' && isset($page->images) && $page->images && $page->images->count()) {
            $image = $page->images->first()->httpUrl;
        }

        return [
            'title' => $title,
            'description' => $description,
            'canonical' => $canonical,
            'robots' => $robots,
            'og_type' => (string) ($overrides['og_type'] ?? 'website'),
            'og_image' => $image,
            'twitter_card' => (string) ($overrides['twitter_card'] ?? ($image !== '' ? 'summary_large_image' : 'summary')),
        ];
    }
}
