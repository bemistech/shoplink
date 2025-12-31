<?php

use Shoplink\ShopifyApi;

$title = ShopifyApi::get_collection_title();

if (!$title) {
    if (isset($_GET['breakdance_builder'])) {
        $title = 'Sample Collection Title';
    } else {
        return;
    }
}

$tag = $propertiesData['design']['typography']['tag'] ?? 'h1';

echo '<' . $tag . ' class="bde-shoplink-collection-title">' . esc_html($title) . '</' . $tag . '>';
