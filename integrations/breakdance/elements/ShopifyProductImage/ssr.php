<?php

use Shoplink\ShoplinkAPI;

/**
 * @var array $propertiesData
 * @var array $parentPropertiesData
 */

// 1. Try to get context from parent Product Builder
$handle = $parentPropertiesData['content']['context']['manual_handle'] ?? null;

$url = ShoplinkAPI::get_product_image_src($handle);
$alt = ShoplinkAPI::get_product_image_alt($handle);

if (empty($url)) {
    return;
}

echo "<img src='" . esc_url($url) . "' alt='" . esc_attr($alt) . "' class='bde-shoplink-product-image' />";
