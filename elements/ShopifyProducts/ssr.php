<?php

use BreakdanceShopify\ShopifyApi;

// Defaults
$limit = $propertiesData['content']['query']['limit'] ?? 8;
$collection = $propertiesData['content']['query']['collection_handle'] ?? '';
// $sortKey = $propertiesData['content']['query']['sort_key'] ?? 'BEST_SELLING'; // API helper might need update for sort, ignoring for now or just passing limit/collection
// $reverse = isset($propertiesData['content']['query']['reverse']) ? true : false;

$columns = $propertiesData['design']['layout']['columns'] ?? 3;
$gap = $propertiesData['design']['layout']['gap'] ?? 20;
$align = $propertiesData['design']['layout']['align'] ?? 'left';

// Fetch products
$products = ShopifyApi::get_collection_products($collection, $limit);

if (is_wp_error($products)) {
    // Return silently to avoid AJAX errors
    return;
}

if (empty($products)) {
    // Return silently
    return;
}

// 1. Build Component List for Sorting
$components = [];

// Standard Components
$standardElements = ['image', 'title', 'price', 'button'];
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
echo '<div class="bde-shopify-products-grid" style="display: grid; grid-template-columns: repeat(' . intval($columns) . ', 1fr); gap: ' . intval($gap) . 'px; text-align: ' . esc_attr($align) . ';">';

foreach ($products as $product) {
    // 2. Set Context for this product loop
    ShopifyApi::push_context($product['handle']);

    $title = ShopifyApi::get_product_title();
    $handle = ShopifyApi::get_product_handle();
    $priceDisplay = ShopifyApi::get_product_price();
    $image_url = ShopifyApi::get_product_image_src();
    $image_alt = ShopifyApi::get_product_image_alt();
    $url = ShopifyApi::get_product_url();

    echo '<div class="bde-shopify-product-card">';
    
    // Iterate sorted components
    foreach ($components as $component) {
        switch ($component['type']) {
            case 'image':
                if ($image_url) {
                    echo '<a href="' . esc_url($url) . '" class="bde-shopify-product-link">';
                    echo '<div class="bde-shopify-product-image-wrapper" style="margin-bottom: 10px;">';
                    echo '<img src="' . esc_url($image_url) . '" alt="' . esc_attr($image_alt) . '" style="max-width: 100%; height: auto; display: block;">';
                    echo '</div>';
                    echo '</a>';
                }
                break;
            case 'title':
                echo '<a href="' . esc_url($url) . '" class="bde-shopify-product-link" style="text-decoration: none; color: inherit;">';
                echo '<h3 class="bde-shopify-product-title">' . esc_html($title) . '</h3>';
                echo '</a>';
                break;
            case 'price':
                echo '<div class="bde-shopify-product-price">' . esc_html($priceDisplay) . '</div>';
                break;
            case 'button':
                echo '<a href="' . esc_url($url) . '" class="bde-shopify-product-button" style="display: inline-block; margin-top: 10px; padding: 8px 16px; background: #000; color: #fff; text-decoration: none;">View Product</a>';
                break;
            case 'global_block':
                // Render the Global Block. 
                // Elements inside it will pick up the context we pushed.
                echo \Breakdance\Render\renderGlobalBlock($component['id']);
                break;
        }
    }
    
    echo '</div>'; // End card

    // 3. Reset Context
    ShopifyApi::pop_context();
}

echo '</div>'; // End grid
