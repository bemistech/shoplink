<?php

use BreakdanceShopify\ShopifyApi;

$handle = $propertiesData['content']['query']['handle'] ?? '';

if (empty($handle)) {
    // Avoid echoing text to prevent AJAX issues
    return;
}

$page = ShopifyApi::get_page($handle);

if (empty($page) || is_wp_error($page)) {
    // Avoid echoing text to prevent AJAX issues
    return;
}

echo "<div class='bde-shopify-page-content'>";
echo "<h1 class='bde-shopify-page-title'>" . esc_html($page['title']) . "</h1>";
echo "<div class='bde-shopify-page-body'>" . wp_kses_post($page['body']) . "</div>";
echo "</div>";
