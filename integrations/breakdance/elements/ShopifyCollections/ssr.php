<?php

use Shoplink\ShoplinkAPI;

// Defaults
$limitRaw = $propertiesData['content']['query']['limit'] ?? 8;
if (is_array($limitRaw)) {
    $limitRaw = $limitRaw['breakpoint_base'] ?? array_values($limitRaw)[0] ?? 8;
}
$limit = intval($limitRaw);
if ($limit < 1) $limit = 8;

// Output
$layout = $propertiesData['design']['list']['layout'] ?? 'grid';
$columns = $propertiesData['design']['list']['columns'] ?? 3;
$gap = $propertiesData['design']['list']['gap'] ?? 20;
$align = $propertiesData['design']['list']['align'] ?? 'left';

// Fetch collections
$collections = ShoplinkAPI::get_collections($limit);

if (is_wp_error($collections)) {
    if (isset($_GET['breakdance_builder'])) {
        echo '<div class="bde-shoplink-collections-error">Shopify Error: ' . esc_html($collections->get_error_message()) . '</div>';
    }
    return;
}

if (empty($collections)) {
    echo '<div class="bde-shoplink-collections-empty">No collections found.</div>';
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

// Custom Areas (Global Blocks)
$customAreas = $propertiesData['design']['elements']['custom_areas']['areas'] ?? [];
foreach ($customAreas as $area) {
     $gbId = $area['global_block'] ?? 0;
     if ($gbId) {
         $components[] = [
             'type' => 'global_block',
             'order' => intval($area['order'] ?? 0),
             'id' => $gbId
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
        ShoplinkAPI::push_collection_context($collection['handle']);
        
        $title = $collection['title'];
        $description = $collection['descriptionHtml'] ?? $collection['description'] ?? '';
        $image_url = $collection['image']['url'] ?? '';
        $image_alt = $collection['image']['altText'] ?? $title;
        $url = home_url('/shop/?collection=' . $collection['handle']); 

        echo '<div class="swiper-slide bde-shoplink-collection-card">';
        
        foreach ($components as $component) {
            switch ($component['type']) {
                case 'image':
                    if ($image_url) {
                        echo '<a href="' . esc_url($url) . '" class="bde-shoplink-collection-link">';
                        echo '<div class="bde-shoplink-collection-image-wrapper" style="margin-bottom: 10px;">';
                        echo '<img src="' . esc_url($image_url) . '" alt="' . esc_attr($image_alt) . '" style="max-width: 100%; height: auto; display: block;">';
                        echo '</div>';
                        echo '</a>';
                    }
                    break;
                case 'title':
                    echo '<a href="' . esc_url($url) . '" class="bde-shoplink-collection-link" style="text-decoration: none;">';
                    echo '<h3 class="bde-shoplink-collection-title">' . esc_html($title) . '</h3>';
                    echo '</a>';
                    break;
                case 'description':
                    echo '<div class="bde-shoplink-collection-description">' . wp_kses_post($description) . '</div>';
                    break;
                case 'global_block':
                    echo \Breakdance\Render\renderGlobalBlock($component['id']);
                    break;
            }
        }
        
        echo '</div>'; // End slide
        ShoplinkAPI::pop_collection_context();
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
    echo '<div class="bde-shoplink-collections-grid" style="display: grid; grid-template-columns: repeat(' . intval($columns) . ', 1fr); gap: ' . intval($gap) . 'px; text-align: ' . esc_attr($align) . ';">';
    
    foreach ($collections as $collection) {
        ShoplinkAPI::push_collection_context($collection['handle']);
        
        $title = $collection['title'];
        $description = $collection['descriptionHtml'] ?? $collection['description'] ?? '';
        $image_url = $collection['image']['url'] ?? '';
        $image_alt = $collection['image']['altText'] ?? $title;
        $url = home_url('/shop/?collection=' . $collection['handle']);
        
        echo '<div class="bde-shoplink-collection-card">';
        
        foreach ($components as $component) {
            switch ($component['type']) {
                case 'image':
                    if ($image_url) {
                        echo '<a href="' . esc_url($url) . '" class="bde-shoplink-collection-link">';
                        echo '<div class="bde-shoplink-collection-image-wrapper" style="margin-bottom: 10px;">';
                        echo '<img src="' . esc_url($image_url) . '" alt="' . esc_attr($image_alt) . '" style="max-width: 100%; height: auto; display: block;">';
                        echo '</div>';
                        echo '</a>';
                    }
                    break;
                case 'title':
                    echo '<a href="' . esc_url($url) . '" class="bde-shoplink-collection-link" style="text-decoration: none;">';
                    echo '<h3 class="bde-shoplink-collection-title">' . esc_html($title) . '</h3>';
                    echo '</a>';
                    break;
                 case 'description':
                    echo '<div class="bde-shoplink-collection-description">' . wp_kses_post($description) . '</div>';
                    break;
                 case 'global_block':
                    echo \Breakdance\Render\renderGlobalBlock($component['id']);
                    break;
            }
        }
        
        echo '</div>'; // End card
        ShoplinkAPI::pop_collection_context();
    }
    
    echo '</div>'; // End grid
}
