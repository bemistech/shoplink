<?php
namespace BreakdanceShopify;

use BreakdanceShopify\ShopifyApi;

/**
 * @var array $properties
 */

$handle = $properties['content']['context']['manual_handle'] ?? null;

ShopifyApi::render_product_context($handle, function() use ($handle, $product) {
    $json = $product ? htmlspecialchars(json_encode($product), ENT_QUOTES, 'UTF-8') : '{}';
    $storeUrl = get_option('shopify_store_url');
    echo '<div class="bde-shopify-product-builder" data-product-json="' . $json . '" data-store-url="' . esc_attr($storeUrl) . '">';?>
        %%CHILDREN%%
    <?php echo '</div>';
});
