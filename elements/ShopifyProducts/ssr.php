<?php

use BreakdanceShopify\ShopifyApi;

// Defaults
$limitRaw = $propertiesData['content']['query']['limit'] ?? 8;
// Handle responsive array
if (is_array($limitRaw)) {
    $limitRaw = $limitRaw['breakpoint_base'] ?? array_values($limitRaw)[0] ?? 8;
}
$limit = intval($limitRaw);
if ($limit < 1) $limit = 8; // Enforce minimum limit

$collection = $propertiesData['content']['query']['collection_handle'] ?? '';
$sortKey = $propertiesData['content']['query']['sort_key'] ?? 'BEST_SELLING';

// Disable responsive array if present (Breakdance passes arrays for responsive controls)
if (is_array($sortKey)) {
    $sortKey = $sortKey['breakpoint_base'] ?? array_values($sortKey)[0] ?? 'BEST_SELLING';
}

$reverse = isset($propertiesData['content']['query']['reverse']) ? (bool) $propertiesData['content']['query']['reverse'] : false;

// Output
$layout = $propertiesData['design']['list']['layout'] ?? 'grid';
$columns = $propertiesData['design']['list']['columns'] ?? 3;
$gap = $propertiesData['design']['list']['gap'] ?? 20;
$align = $propertiesData['design']['list']['align'] ?? 'left';

// Fetch products
$products = ShopifyApi::get_collection_products($collection, $limit, $sortKey, $reverse);

if (is_wp_error($products)) {
    file_put_contents(WP_CONTENT_DIR . '/breakdance_debug.log', date('Y-m-d H:i:s') . ' - Error: ' . $products->get_error_message() . "\n", FILE_APPEND);
    if (isset($_GET['breakdance_builder'])) {
        echo '<div class="bde-shopify-products-error">Shopify Error: ' . esc_html($products->get_error_message()) . '</div>';
    }
    return;
}

if (empty($products)) {
    file_put_contents(WP_CONTENT_DIR . '/breakdance_debug.log', date('Y-m-d H:i:s') . ' - Empty result. Collection: ' . $collection . "\n", FILE_APPEND);
    echo '<div class="bde-shopify-products-empty">No products found.</div>';
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
if ($layout === 'slider') {
    // Slider Layout
    echo '<div class="breakdance-swiper-wrapper">';
    echo '<div class="swiper">';
    echo '<div class="swiper-wrapper">';
    
    foreach ($products as $product) {
        ShopifyApi::push_context($product['handle']);
        
        $title = ShopifyApi::get_product_title();
        $handle = ShopifyApi::get_product_handle();
        $priceDisplay = ShopifyApi::get_product_price();
        $image_url = ShopifyApi::get_product_image_src();
        $image_alt = ShopifyApi::get_product_image_alt();
        $url = ShopifyApi::get_product_url();
        
        echo '<div class="swiper-slide bde-shopify-product-card">';
        
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
                    echo \Breakdance\Render\renderGlobalBlock($component['id']);
                    break;
            }
        }
        
        echo '</div>'; // End slide
        ShopifyApi::pop_context();
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
    echo '<div class="bde-shopify-products-grid" style="display: grid; grid-template-columns: repeat(' . intval($columns) . ', 1fr); gap: ' . intval($gap) . 'px; text-align: ' . esc_attr($align) . ';">';
    
    foreach ($products as $product) {
        ShopifyApi::push_context($product['handle']);
        
        $title = ShopifyApi::get_product_title();
        $handle = ShopifyApi::get_product_handle();
        $priceDisplay = ShopifyApi::get_product_price();
        $image_url = ShopifyApi::get_product_image_src();
        $image_alt = ShopifyApi::get_product_image_alt();
        $url = ShopifyApi::get_product_url();
        
        echo '<div class="bde-shopify-product-card">';
        
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
                    echo \Breakdance\Render\renderGlobalBlock($component['id']);
                    break;
            }
        }
        
        echo '</div>'; // End card
        ShopifyApi::pop_context();
    }
    
    echo '</div>'; // End grid
}
