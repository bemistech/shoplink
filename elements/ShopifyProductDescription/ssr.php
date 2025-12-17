<?php

use BreakdanceShopify\ShopifyApi;

/**
 * @var array $propertiesData
 * @var array $parentPropertiesData
 */

// 1. Try to get context from parent Product Builder
$handle = $parentPropertiesData['content']['context']['manual_handle'] ?? null;

$description = ShopifyApi::get_product_description($handle);

if (empty($description)) {
    return;
}

echo "<div class='bde-shopify-product-description'>" . wp_kses_post($description) . "</div>";