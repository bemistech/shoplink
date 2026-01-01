document.addEventListener('DOMContentLoaded', function() {
    // wpColorPicker is a jQuery plugin, so we must use jQuery to initialize it.
    if (typeof jQuery !== 'undefined' && typeof jQuery.fn.wpColorPicker === 'function') {
        jQuery('.shoplink-color-field').wpColorPicker();
    }
});
