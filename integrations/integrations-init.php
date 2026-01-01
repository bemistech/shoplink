<?php


// Load Breakdance
if(is_plugin_active('breakdance/plugin.php')) {
function shoplink_load_breakdance() {
    require_once SHOPLINK_PATH . 'integrations/breakdance/bd-init.php';
}
add_action( 'breakdance_loaded', 'shoplink_load_breakdance' );
};