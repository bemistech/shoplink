<?php

use Shoplink\ShopifyApi;

/**
 * @var array $propertiesData
 * @var array $parentPropertiesData
 */

// 1. Try to get context from parent Product Builder
$handle = $parentPropertiesData['content']['context']['manual_handle'] ?? null;

$priceDisplay = ShopifyApi::get_product_price($handle);
$compareAtDisplay = ShopifyApi::get_product_compare_at_price($handle);

if (empty($priceDisplay)) {
    return;
}

// Get raw currency symbol for data attribute if possible, or just extract from string
// Ideally API should provide get_currency_symbol but we can live without updating the data attr or just guessing '$' for now if complex.
// The data-currency-symbol was for JS. JS uses it to update price.
// If we just output the initial server render correctly, JS will overwrite it anyway on variation change.
// So strict data-attr isn't critical for INITIAL render if JS immediately takes over, but good for SEO/No-JS.
// I'll skip data-currency-symbol complexity or parse it.
// Actually, I can just grab the first char.
$symbol = mb_substr($priceDisplay, 0, 1);

echo "<div class='bde-shoplink-product-price' data-currency-symbol='" . esc_attr($symbol) . "'>";
if ($compareAtDisplay) {
     echo "<span class='bde-shoplink-price-compare'>{$compareAtDisplay}</span> ";
     echo "<span class='bde-shoplink-price-regular bde-shoplink-price-sale'>{$priceDisplay}</span>";
} else {
    echo "<span class='bde-shoplink-price-regular'>{$priceDisplay}</span>";
}
echo "</div>";
