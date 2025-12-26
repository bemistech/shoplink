<?php

use BreakdanceShopify\ShopifyApi;

// Defaults
$limit = $propertiesData['content']['query']['limit'] ?? 8;

// Output
$layout = $propertiesData['design']['list']['layout'] ?? 'grid';
$columns = $propertiesData['design']['list']['columns'] ?? 3;
$gap = $propertiesData['design']['list']['gap'] ?? 20;
$align = $propertiesData['design']['list']['align'] ?? 'left';

// Fetch collections
$collections = ShopifyApi::get_collections($limit);

if (is_wp_error($collections)) {
    return;
}

if (empty($collections)) {
    return;
}

// 1. Build Component List for Sorting
$components = [];

// Standard Components
$standardElements = ['image', 'title', 'description'];
foreach ($standardElements as $type) {
    $settings = $propertiesData['design']['elements'][$type] ?? [];
    if (($settings['include'] ?? 'enable') === 'enable') {
        $components[] = [
            'type' => $type,
            'order' => intval($settings['order'] ?? 0),
            'data' => $settings
        ];
    }
}

// Sort by Order
usort($components, function($a, $b) {
    return $a['order'] <=> $b['order'];
});

// Render Grid
if ($layout === 'slider') {
    // Slider Layout
    echo '<div class="breakdance-swiper-wrapper">';
    echo '<div class="swiper">';
    echo '<div class="swiper-wrapper">';
    
    foreach ($collections as $collection) {
        ShopifyApi::push_collection_context($collection['handle']);
        
        $title = ShopifyApi::get_collection_title();
        $description = ShopifyApi::get_collection_description();
        $image_url = ShopifyApi::get_collection_image_src();
        $image_alt = ShopifyApi::get_collection_image_alt();
        // Assuming we want to link to collection URL. 
        // Note: URL structure depends on rewrites. For now assuming /collections/handle or using a store URL if available.
        // Actually, ShopifyApi doesn't have get_collection_url yet, but standard structure is predictable.
        // Or we can rely on Global Settings for rewrite structure.
        // We'll mimic product link behavior but for collections.
        $url = '/collections/' . $collection['handle']; 

        echo '<div class="swiper-slide bde-shopify-collection-card">';
        
        foreach ($components as $component) {
            switch ($component['type']) {
                case 'image':
                    if ($image_url) {
                        echo '<a href="' . esc_url($url) . '" class="bde-shopify-collection-link">';
                        echo '<div class="bde-shopify-collection-image-wrapper" style="margin-bottom: 10px;">';
                        echo '<img src="' . esc_url($image_url) . '" alt="' . esc_attr($image_alt) . '" style="max-width: 100%; height: auto; display: block;">';
                        echo '</div>';
                        echo '</a>';
                    }
                    break;
                case 'title':
                    echo '<a href="' . esc_url($url) . '" class="bde-shopify-collection-link" style="text-decoration: none; color: inherit;">';
                    echo '<h3 class="bde-shopify-collection-title">' . esc_html($title) . '</h3>';
                    echo '</a>';
                    break;
                case 'description':
                    // Truncate description or just show summary? Showing regular description for now.
                    echo '<div class="bde-shopify-collection-description">' . wp_kses_post($description) . '</div>';
                    break;
            }
        }
        
        echo '</div>'; // End slide
        ShopifyApi::pop_collection_context();
    }
    
    echo '</div>'; // End swiper-wrapper
    
    // Pagination & Navigation
    echo '<div class="swiper-pagination"></div>';
    echo '<div class="swiper-button-prev"></div>';
    echo '<div class="swiper-button-next"></div>';
    
    echo '</div>'; // End swiper
    echo '</div>'; // End breakdance-swiper-wrapper

} else {
    // Grid Layout
    echo '<div class="bde-shopify-collections-grid" style="display: grid; grid-template-columns: repeat(' . intval($columns) . ', 1fr); gap: ' . intval($gap) . 'px; text-align: ' . esc_attr($align) . ';">';
    
    foreach ($collections as $collection) {
        ShopifyApi::push_collection_context($collection['handle']);
        
        $title = ShopifyApi::get_collection_title();
        $description = ShopifyApi::get_collection_description();
        $image_url = ShopifyApi::get_collection_image_src();
        $image_alt = ShopifyApi::get_collection_image_alt();
        $url = '/collections/' . $collection['handle'];
        
        echo '<div class="bde-shopify-collection-card">';
        
        foreach ($components as $component) {
            switch ($component['type']) {
                case 'image':
                    if ($image_url) {
                        echo '<a href="' . esc_url($url) . '" class="bde-shopify-collection-link">';
                        echo '<div class="bde-shopify-collection-image-wrapper" style="margin-bottom: 10px;">';
                        echo '<img src="' . esc_url($image_url) . '" alt="' . esc_attr($image_alt) . '" style="max-width: 100%; height: auto; display: block;">';
                        echo '</div>';
                        echo '</a>';
                    }
                    break;
                case 'title':
                    echo '<a href="' . esc_url($url) . '" class="bde-shopify-collection-link" style="text-decoration: none; color: inherit;">';
                    echo '<h3 class="bde-shopify-collection-title">' . esc_html($title) . '</h3>';
                    echo '</a>';
                    break;
                 case 'description':
                    echo '<div class="bde-shopify-collection-description">' . wp_kses_post($description) . '</div>';
                    break;
            }
        }
        
        echo '</div>'; // End card
        ShopifyApi::pop_collection_context();
    }
    
    echo '</div>'; // End grid
}
