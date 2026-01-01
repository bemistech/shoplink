<?php

use Shoplink\ShoplinkAPI;

$widgets = $propertiesData['content']['widgets'] ?? [];

if (empty($widgets)) {
    if (isset($_GET['breakdance_builder'])) {
        echo '<div class="bde-shoplink-filters-empty">Add filter widgets to start.</div>';
    }
    return;
}

// Current Values
$current_sort = $_GET['sort_key'] ?? '';
$current_collection = $_GET['collection'] ?? '';
$current_product_type = $_GET['product_type'] ?? '';
$current_min_price = $_GET['min_price'] ?? '';
$current_max_price = $_GET['max_price'] ?? '';
$current_tags_string = $_GET['tags'] ?? '';
$current_tags = array_filter(explode(',', $current_tags_string));

echo '<div class="bde-shoplink-filters">';
echo '<form class="bde-shoplink-filters-form" method="GET">';

// Preserve other GET params? Like 'q'
if (isset($_GET['q'])) {
    echo '<input type="hidden" name="q" value="' . esc_attr($_GET['q']) . '">';
}

foreach ($widgets as $widget) {
    $type = $widget['type'];
    // Handle responsive array value if present (compatibility)
    if (is_array($type)) {
        $type = $type['breakpoint_base'] ?? reset($type);
    }
    $label = $widget['label'] ?? ucfirst($type);

    echo '<div class="bde-shoplink-filter-widget bde-filter-type-' . esc_attr($type) . '">';
    if ($label) {
        echo '<div class="bde-shoplink-filter-label">' . esc_html($label) . '</div>';
    }

    switch ($type) {
        case 'sort':
            echo '<select name="sort_key" class="bde-shoplink-filter-select">';
            $options = [
                'BEST_SELLING' => 'Best Selling',
                'TITLE' => 'Alphabetically, A-Z',
                'PRICE' => 'Price, low to high',
                'CREATED' => 'Date, new to old',
                'MANUAL' => 'Featured'
            ];
            foreach ($options as $val => $text) {
                $selected = ($current_sort === $val) ? 'selected' : '';
                echo '<option value="' . esc_attr($val) . '" ' . $selected . '>' . esc_html($text) . '</option>';
            }
            // Add reverse option handling? Usually handled by combined keys in Shopify but we kept it separate in API.
            // For simple UI, we might just map "Price High to Low" to a specific value logic in JS or backend.
            // But API takes 'reverse'.
            // Let's keep it simple for V1: standard keys.
            echo '</select>';
            
            // Reverse toggle? Or mapped values?
            // "Price, high to low" -> sort=PRICE&reverse=true
            // We'll need JS to split this or hidden input.
            // For HTML-only, standard select works if we map values to "PRICE_REVERSE" and handle in backend.
            // But let's stick to API keys.
            break;

        case 'price':
            echo '<div class="bde-shoplink-filter-price-range">';
            echo '<input type="number" name="min_price" placeholder="Min" value="' . esc_attr($current_min_price) . '" min="0" step="0.01">';
            echo '<span>-</span>';
            echo '<input type="number" name="max_price" placeholder="Max" value="' . esc_attr($current_max_price) . '" min="0" step="0.01">';
            echo '</div>';
            break;

        case 'collection':
            $options = ShoplinkAPI::get_collections_options();
            echo '<select name="collection" class="bde-shoplink-filter-select">';
            foreach ($options as $opt) {
                $selected = ($current_collection === $opt['value']) ? 'selected' : '';
                echo '<option value="' . esc_attr($opt['value']) . '" ' . $selected . '>' . esc_html($opt['text']) . '</option>';
            }
            echo '</select>';
            break;

        case 'categories':
            $types = ShoplinkAPI::get_product_types();
            echo '<select name="product_type" class="bde-shoplink-filter-select">';
            echo '<option value="">All Categories</option>';
            foreach ($types as $type) {
                $selected = ($current_product_type === $type) ? 'selected' : '';
                echo '<option value="' . esc_attr($type) . '" ' . $selected . '>' . esc_html($type) . '</option>';
            }
            echo '</select>';
            break;

        case 'tags':
            $tags = ShoplinkAPI::get_shop_tags();
            echo '<div class="bde-shoplink-filter-tags">';
            if (empty($tags)) {
                echo '<div class="bde-shoplink-filter-empty">No tags found</div>';
            }
            foreach ($tags as $tag) {
                $checked = in_array($tag, $current_tags) ? 'checked' : '';
                // Since GET params with same name arrays work in PHP, but JS/Shopify often likes comma list.
                // We'll use name="tags[]" for PHP handling easier, but our API expects comma string or array.
                // Our API `get_products_filtered` handles array.
                // So name="tags[]" is fine.
                echo '<label class="bde-shoplink-filter-tag">';
                echo '<input type="checkbox" name="tags[]" value="' . esc_attr($tag) . '" ' . $checked . '> ';
                echo esc_html($tag);
                echo '</label>';
            }
            echo '</div>';
            break;
    }

    echo '</div>'; // End widget
}

// Check if any filters are active
$has_filters = !empty($current_sort) || !empty($current_collection) || !empty($current_product_type) || !empty($current_min_price) || !empty($current_max_price) || !empty($current_tags);

$clear_style = ($has_filters || isset($_GET['breakdance_builder'])) ? '' : 'style="display:none;"';
$clear_url = strtok($_SERVER["REQUEST_URI"], '?');

echo '<a href="' . esc_url($clear_url) . '" class="bde-shoplink-filter-clear" ' . $clear_style . '>Clear Filters</a>';

echo '</form>';
echo '</div>';

// Output Filter JS
// We use a unique ID to prevent double loading if multiple filters exist, mostly handled by JS logic but good to include once.
// Ideally usage of wp_enqueue_script is best, but inline simpler for Element context.
echo '<script src="' . plugin_dir_url(__FILE__) . 'filter.js?ver=1.0.0"></script>';
