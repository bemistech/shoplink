<?php

use BreakdanceShopify\ShopifyApi;

/**
 * @var array $propertiesData
 * @var array $parentPropertiesData
 */

// 1. Try to get context from parent Product Builder
$handle = $parentPropertiesData['content']['context']['manual_handle'] ?? null;

$product = ShopifyApi::get_current_product($handle);

if(is_wp_error($product) || empty($product)) {
    echo "Product not found";
    return;
}

$variants = $product['variants']['edges'] ?? [];

if (empty($variants)) {
    echo "No variants found";
    return;
}

echo "<div class='bde-shopify-variant-selector-wrapper'>";
echo "<select class='bde-shopify-variant-select'>";

foreach ($variants as $variantNode) {
    if (!isset($variantNode['node'])) continue;
    
    $variant = $variantNode['node'];
    $id = $variant['id'];
    $title = $variant['title'];
    $price = $variant['price']['amount'] ?? '';
    // $currency = $variant['price']['currencyCode'] ?? ''; // Optional
    
    // Formatting title
    $display = esc_html($title);
    if ($price) {
        $display .= " - $" . esc_html($price);
    }
    
    echo "<option value='" . esc_attr($id) . "'>" . $display . "</option>";
}

echo "</select>";
echo "</div>";
