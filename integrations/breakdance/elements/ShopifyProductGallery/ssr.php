<?php

use Shoplink\ShoplinkAPI;

/**
 * @var array $propertiesData
 * @var array $parentPropertiesData
 */

// 1. Try to get context from parent Product Builder
$handle = $parentPropertiesData['content']['context']['manual_handle'] ?? null;

$product = ShoplinkAPI::get_current_product($handle);

if(is_wp_error($product) || empty($product)) {
    return;
}

$images = $product['images']['edges'] ?? [];

if (empty($images)) {
    return;
}

echo "<div class='bde-shoplink-product-gallery'>";
foreach ($images as $imageNode) {
    $url = $imageNode['node']['url'] ?? '';
    $alt = $imageNode['node']['altText'] ?? $product['title'];
    
    if ($url) {
        echo "<div class='bde-shoplink-product-gallery-item'>";
        echo "<img src='" . esc_url($url) . "' alt='" . esc_attr($alt) . "' class='bde-shoplink-product-gallery-img' />";
        echo "</div>";
    }
}
echo "</div>";
