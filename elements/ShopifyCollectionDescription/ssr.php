<?php

use BreakdanceShopify\ShopifyApi;

$description = ShopifyApi::get_collection_description();

if (!$description) {
    if (isset($_GET['breakdance_builder'])) {
        $description = 'This is a sample collection description used for preview purposes in the builder.';
    } else {
        return;
    }
}

$tag = $propertiesData['design']['typography']['tag'] ?? 'div';

echo '<' . $tag . ' class="bde-shopify-collection-description">' . wp_kses_post($description) . '</' . $tag . '>';
