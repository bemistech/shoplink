<?php
/**
 * @var array $propertiesData
 * @var array $parentPropertiesData
 */

// 1. Try to get context from parent Product Builder
$handle = $parentPropertiesData['content']['context']['manual_handle'] ?? null;

$title = ShopifyApi::get_product_title($handle);

if (empty($title)) {
    return;
}

$tag = $propertiesData['content']['content']['tag'] ?? 'h1';

echo "<{$tag} class='bde-shoplink-product-title'>" . esc_html($title) . "</{$tag}>";
