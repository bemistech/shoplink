<?php

namespace Shoplink\DynamicData;

use Breakdance\DynamicData\StringField;
use Breakdance\DynamicData\ImageField;
use Breakdance\DynamicData\StringData;
use Breakdance\DynamicData\ImageData;
use Shoplink\ShoplinkAPI;

/**
 * Common Logic for Shopify Fields
 */
trait ShopifyFieldTrait {
    public function category() {
        return 'Shopify';
    }

    protected function getProduct() {
        // Dynamic Data fields don't natively support manual handle context passed from element controls easily
        // because they are used in generic elements.
        // However, they SHOULD respect the global context set by the ShopifyProductBuilder.
        $product = ShoplinkAPI::get_current_product();
        if (is_wp_error($product)) {
            return null;
        }
        return $product;
    }
}

class ShopifyProductTitleField extends StringField {
    use ShopifyFieldTrait;

    public function label() { return 'Product Title'; }
    public function slug() { return 'shopify_product_title'; }

    public function handler($attributes): StringData {
        return StringData::fromString(ShoplinkAPI::get_product_title());
    }
}

class ShopifyProductDescriptionField extends StringField {
    use ShopifyFieldTrait;

    public function label() { return 'Product Description (HTML)'; }
    public function slug() { return 'shopify_product_description'; }

    public function handler($attributes): StringData {
        return StringData::fromString(ShoplinkAPI::get_product_description());
    }
}

class ShopifyProductPriceField extends StringField {
    use ShopifyFieldTrait;

    public function label() { return 'Product Price'; }
    public function slug() { return 'shopify_product_price'; }

    public function handler($attributes): StringData {
        return StringData::fromString(ShoplinkAPI::get_product_price());
    }
}

class ShopifyProductHandleField extends StringField {
    use ShopifyFieldTrait;

    public function label() { return 'Product Handle'; }
    public function slug() { return 'shopify_product_handle'; }

    public function handler($attributes): StringData {
        return StringData::fromString(ShoplinkAPI::get_product_handle());
    }
}

class ShopifyProductUrlField extends StringField {
    use ShopifyFieldTrait;

    public function label() { return 'Product URL'; }
    public function slug() { return 'shopify_product_url'; }
    public function returnTypes() { return ['url']; }

    public function handler($attributes): StringData {
        return StringData::fromString(ShoplinkAPI::get_product_url());
    }
}

class ShopifyProductImageField extends ImageField {
    use ShopifyFieldTrait;

    public function label() { return 'Product Image'; }
    public function slug() { return 'shopify_product_image'; }

    public function handler($attributes): ImageData {
        $url = ShoplinkAPI::get_product_image_src();
        $alt = ShoplinkAPI::get_product_image_alt();

        if ($url) {
            $imageData = ImageData::fromUrl($url);
            $imageData->alt = $alt;
            return $imageData;
        }
        
        return ImageData::emptyImage();
    }
}
