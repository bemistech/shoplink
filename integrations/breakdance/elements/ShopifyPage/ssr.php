<?php

use Shoplink\ShopifyApi;

// Defaults for Shop Page Element
$limit = 12; // Standard default
$columns = 3;
$gap = 20;

// URL Parameter Overrides (Filtering)
$collection = '';
if (isset($_GET['collection']) && !empty($_GET['collection'])) {
    $collection = sanitize_text_field($_GET['collection']);
}
$sortKey = 'BEST_SELLING';
if (isset($_GET['sort_key']) && !empty($_GET['sort_key'])) {
    $sortKey = sanitize_text_field($_GET['sort_key']);
}
$reverse = false;
if (isset($_GET['reverse'])) {
    $reverse = filter_var($_GET['reverse'], FILTER_VALIDATE_BOOLEAN);
}

// Prepare Arguments
$args = [
    'limit' => $limit,
    'collection' => $collection,
    'sort_key' => $sortKey,
    'reverse' => $reverse,
    'min_price' => $_GET['min_price'] ?? '',
    'max_price' => $_GET['max_price'] ?? '',
    'tags' => (isset($_GET['tags']) && is_array($_GET['tags'])) 
        ? $_GET['tags'] 
        : (isset($_GET['tags']) ? array_filter(explode(',', $_GET['tags'])) : []),
    'product_type' => $_GET['product_type'] ?? '',
    'q' => $_GET['q'] ?? ''
];

// Fetch products
$products = ShopifyApi::get_products_filtered($args);

// Debug
if (is_wp_error($products)) {
    if (isset($_GET['breakdance_builder'])) {
        echo '<div class="bde-shoplink-products-error">Shopify Error: ' . esc_html($products->get_error_message()) . '</div>';
    }
    return;
}

if (empty($products)) {
    echo '<div class="bde-shoplink-products-empty">No products found.</div>';
    return;
}

// Render Grid
echo '<div class="bde-shoplink-page-grid" style="display: grid; grid-template-columns: repeat(' . intval($columns) . ', 1fr); gap: ' . intval($gap) . 'px;">';

foreach ($products as $product) {
    ShopifyApi::push_context($product['handle']);
    
    // Direct Data Access (Optimization)
    $title = $product['title'];
    $handle = $product['handle'];
    
    // Price
    $price = '';
    if (!empty($product['priceRange'])) {
        $min = $product['priceRange']['minVariantPrice']['amount'];
        $max = $product['priceRange']['maxVariantPrice']['amount'];
        $currency = $product['priceRange']['minVariantPrice']['currencyCode'];
        
        if ($min != $max) {
             $price = $currency . ' ' . $min . ' - ' . $currency . ' ' . $max;
        } else {
             $price = $currency . ' ' . $min;
        }
    }
    
    // Image
    $image_url = $product['featuredImage']['url'] ?? '';
    $image_alt = $product['featuredImage']['altText'] ?? $title;
    $url = home_url('/products/' . $handle);
    
    echo '<div class="bde-shoplink-product-card">';
    
    // Image
    if ($image_url) {
        echo '<a href="' . esc_url($url) . '" class="bde-shoplink-product-link">';
        echo '<div class="bde-shoplink-product-image-wrapper" style="margin-bottom: 10px;">';
        echo '<img src="' . esc_url($image_url) . '" alt="' . esc_attr($image_alt) . '" style="max-width: 100%; height: auto; display: block;">';
        echo '</div>';
        echo '</a>';
    }
    
    // Title
    echo '<a href="' . esc_url($url) . '" class="bde-shoplink-product-link" style="text-decoration: none;">';
    echo '<h3 class="bde-shoplink-product-title">' . esc_html($title) . '</h3>';
    echo '</a>';
    
    // Price
    echo '<div class="bde-shoplink-product-price">' . esc_html($price) . '</div>';
    
    // Button
    echo '<a href="' . esc_url($url) . '" class="bde-shoplink-product-button" style="display: inline-block; margin-top: 10px; padding: 8px 16px; background: var(--shoplink-primary-color, #000); color: var(--shoplink-secondary-color, #fff); text-decoration: none;">View Product</a>';
    
    echo '</div>'; // End card
    ShopifyApi::pop_context();
}

echo '</div>'; // End grid
