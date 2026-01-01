<?php

namespace ShopLink;

require_once SHOPLINK_PATH . 'includes/search.php';
require_once SHOPLINK_PATH . 'includes/styles/StyleController.php';
require_once SHOPLINK_PATH . 'includes/admin/Menu.php';
require_once SHOPLINK_PATH . 'includes/admin/SettingsPage.php';

// Initialize Settings Sync
require_once SHOPLINK_PATH . 'includes/admin/SettingsSync.php';
\Shoplink\Admin\SettingsSync::init();