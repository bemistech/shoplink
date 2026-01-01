<?php

use Shoplink\ShoplinkAPI;

/**
 * @var array $propertiesData
 * @var array $parentPropertiesData
 */

// 1. Try to get context from parent Product Builder
$handle = $parentPropertiesData['content']['context']['manual_handle'] ?? null;

$description = ShoplinkAPI::get_product_description($handle);

if (empty($description)) {
    return;
}

echo "<div class='bde-shoplink-product-description'>" . wp_kses_post($description) . "</div>";