<?php

use BreakdanceShopify\ShopifyApi;

$image_src = ShopifyApi::get_collection_image_src();
$image_alt = ShopifyApi::get_collection_image_alt();

if (!$image_src) {
    if (isset($_GET['breakdance_builder'])) {
        $image_src = 'https://via.placeholder.com/600';
        $image_alt = 'Sample Collection Image';
    } else {
        return;
    }
}

$width = $propertiesData['design']['width']['number'] . $propertiesData['design']['width']['unit'];
$height = $propertiesData['design']['height']['number'] . $propertiesData['design']['height']['unit'];
$objectFit = $propertiesData['design']['object_fit'] ?? 'cover';

echo '<img src="' . esc_url($image_src) . '" alt="' . esc_attr($image_alt) . '" style="width: ' . esc_attr($width) . '; height: ' . esc_attr($height) . '; object-fit: ' . esc_attr($objectFit) . '; display: block;" class="bde-shopify-collection-image" />';
