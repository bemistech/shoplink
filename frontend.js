(function() {
    document.addEventListener('DOMContentLoaded', function() {
        const builders = document.querySelectorAll('.bde-shopify-product-builder');

        builders.forEach(builder => {
            const productData = JSON.parse(builder.dataset.productJson || '{}');
            const storeUrl = builder.dataset.storeUrl;
            const variantSelect = builder.querySelector('.bde-shopify-variant-select');
            const quantityInput = builder.querySelector('.bde-shopify-quantity-input');
            const priceEl = builder.querySelector('.bde-shopify-product-price');
            const imageEl = builder.querySelector('.bde-shopify-product-image');
            const addToCartBtn = builder.querySelector('.bde-shopify-add-to-cart-btn');
            const buyNowBtn = builder.querySelector('.bde-shopify-buy-now-btn');

            if (!productData || !productData.variants) return;

            // Helper to get current state
            const getState = () => {
                const variantId = variantSelect ? variantSelect.value : (productData.variants.edges[0]?.node.id || '');
                const quantity = quantityInput ? quantityInput.value : 1;
                const variant = productData.variants.edges.find(e => e.node.id === variantId)?.node;
                return { variantId, quantity, variant };
            };

            // Update UI
            const updateUI = () => {
                const { variant } = getState();
                if (!variant) return;

                // Update Price
                if (priceEl) {
                    const symbol = priceEl.getAttribute('data-currency-symbol') || '$'; // Fallback
                    const price = variant.price.amount;
                    const compareAt = variant.compareAtPrice?.amount;
                    
                    let html = `<span class='bde-shopify-price-regular'>${symbol}${price}</span>`;
                    if (compareAt && parseFloat(compareAt) > parseFloat(price)) {
                        html = `<span class='bde-shopify-price-compare'>${symbol}${compareAt}</span> ` +
                               `<span class='bde-shopify-price-regular bde-shopify-price-sale'>${symbol}${price}</span>`;
                    }
                    priceEl.innerHTML = html;
                }

                // Update Image (if variant has specific image)
                if (imageEl && variant.image && variant.image.url) {
                    imageEl.src = variant.image.url;
                    if(variant.image.altText) imageEl.alt = variant.image.altText;
                }
            };

            // Listeners
            if (variantSelect) {
                variantSelect.addEventListener('change', updateUI);
            }

            // Quantity Buttons
            const qtyMinus = builder.querySelector('.bde-shopify-qty-minus');
            const qtyPlus = builder.querySelector('.bde-shopify-qty-plus');

            if (qtyMinus && quantityInput) {
                qtyMinus.addEventListener('click', () => {
                    let val = parseInt(quantityInput.value) || 1;
                    if (val > 1) quantityInput.value = val - 1;
                });
            }

            if (qtyPlus && quantityInput) {
                qtyPlus.addEventListener('click', () => {
                    let val = parseInt(quantityInput.value) || 1;
                    quantityInput.value = val + 1;
                });
            }

            // Button Actions
            const handleAction = (isBuyNow) => {
                const { variantId, quantity } = getState();
                if (!variantId) return;

                // Base URL handling
                let url = storeUrl;
                if (!url.startsWith('http')) url = 'https://' + url;
                if (url.endsWith('/')) url = url.slice(0, -1);

                if (isBuyNow) {
                    // Direct Checkout: /cart/{variant_id}:{quantity}
                    // ID often needs to be numeric for permalinks, but GraphQL returns gid://... 
                    // We need to extract the numeric ID.
                    const numericId = variantId.split('/').pop();
                    window.location.href = `${url}/cart/${numericId}:${quantity}`;
                } else {
                    // Add to Cart: /cart/add?id={variant_id}&quantity={quantity}
                    // Works with numeric ID usually
                    const numericId = variantId.split('/').pop();
                    window.location.href = `${url}/cart/add?id=${numericId}&quantity=${quantity}`;
                }
            };

            if (addToCartBtn) {
                addToCartBtn.addEventListener('click', (e) => {
                    e.preventDefault();
                    handleAction(false);
                });
            }

            if (buyNowBtn) {
                buyNowBtn.addEventListener('click', (e) => {
                    e.preventDefault();
                    handleAction(true);
                });
            }
        });
    });
})();
