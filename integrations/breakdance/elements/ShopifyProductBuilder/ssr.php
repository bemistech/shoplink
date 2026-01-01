<?php
namespace Shoplink;

use Shoplink\ShoplinkAPI;

/**
 * @var array $properties
 */

$handle = $properties['content']['context']['manual_handle'] ?? null;

ShoplinkAPI::render_product_context($handle, function() use ($handle, $product) {
    $json = $product ? htmlspecialchars(json_encode($product), ENT_QUOTES, 'UTF-8') : '{}';
    $storeUrl = get_option('shopify_store_url');
    echo '<div class="bde-shoplink-product-builder" data-product-json="' . $json . '" data-store-url="' . esc_attr($storeUrl) . '">';?>
        %%CHILDREN%%
    <?php echo '</div>';
});
