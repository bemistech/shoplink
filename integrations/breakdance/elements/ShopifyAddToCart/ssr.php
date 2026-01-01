<?php
/**
 * @var array $propertiesData
 * @var array $parentPropertiesData
 */

// 1. Try to get context from parent Product Builder
$manual_handle = $parentPropertiesData['content']['context']['manual_handle'] ?? null;

// Check if product exists contextually
$url = \Shoplink\ShoplinkAPI::get_product_url($manual_handle);

if (empty($url)) {
    return;
}

$text = $propertiesData['content']['text'] ?? 'Add to Cart';
echo "<a href='#' class='bde-shoplink-add-to-cart-btn breakdance-button'>" . esc_html($text) . "</a>";
