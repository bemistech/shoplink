<?php

namespace Shoplink\Admin;

use Shoplink\StyleController;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Settings Page UI
 */
class SettingsPage {
    
    public static function render() {
        $active_tab = $_GET['tab'] ?? 'welcome';
        ?>
        <div class="wrap">
            <h1>Shoplink Shopify Integration</h1>
            
            <h2 class="nav-tab-wrapper">
                <a href="?page=shoplink&tab=welcome" class="nav-tab <?php echo $active_tab === 'welcome' ? 'nav-tab-active' : ''; ?>">Getting Started</a>
                <a href="?page=shoplink&tab=styles" class="nav-tab <?php echo $active_tab === 'styles' ? 'nav-tab-active' : ''; ?>">Styles</a>
                <a href="?page=shoplink&tab=settings" class="nav-tab <?php echo $active_tab === 'settings' ? 'nav-tab-active' : ''; ?>">Settings</a>
            </h2>
            
            <div class="shoplink-content" style="margin-top: 20px;">
                <?php
                switch ( $active_tab ) {
                    case 'styles':
                        self::render_styles_tab();
                        break;
                    case 'settings':
                        self::render_settings_tab();
                        break;
                    default:
                        self::render_welcome_tab();
                        break;
                }
                ?>
            </div>
        </div>
        <?php
    }

    private static function render_welcome_tab() {
        ?>
        <div class="card" style="max-width: 800px; padding: 20px;">
            <h2>Welcome to Shoplink</h2>
            <p>Connect your Shopify store with your favorite WordPress builders.</p>
            <h3>Getting Started</h3>
            <ol>
                <li>Go to the <strong>Settings</strong> tab to configure your Shopify API keys.</li>
                <li>Use the <strong>Styles</strong> tab to set your global brand colors.</li>
                <li>Open your builder (Breakdance, etc.) and start adding Shoplink elements!</li>
            </ol>
        </div>
        <?php
    }

    private static function render_styles_tab() {
        if ( isset( $_POST['shoplink_save_styles'] ) && check_admin_referer( 'shoplink_save_styles_nonce' ) ) {
            $styles = [
                'primary_color'   => sanitize_hex_color( $_POST['primary_color'] ) ?: '#000000',
                'secondary_color' => sanitize_hex_color( $_POST['secondary_color'] ) ?: '#ffffff',
                'heading_color'   => sanitize_hex_color( $_POST['heading_color'] ) ?: '#111111',
                'text_color'      => sanitize_hex_color( $_POST['text_color'] ) ?: '#333333',
                'link_color'      => sanitize_hex_color( $_POST['link_color'] ) ?: '#000000',
            ];
            update_option( 'shoplink_styles', $styles );
            echo '<div class="notice notice-success is-dismissible"><p>Styles saved.</p></div>';
        }

        $styles = StyleController::get_styles();
        ?>
        <form method="post" action="">
            <?php wp_nonce_field( 'shoplink_save_styles_nonce' ); ?>
            <table class="form-table">
                <tr>
                    <th scope="row"><label for="primary_color">Primary Color (Buttons)</label></th>
                    <td><input name="primary_color" type="text" id="primary_color" value="<?php echo esc_attr( $styles['primary_color'] ); ?>" class="shoplink-color-field" data-default-color="#000000"></td>
                </tr>
                <tr>
                    <th scope="row"><label for="secondary_color">Secondary Color (Button Text)</label></th>
                    <td><input name="secondary_color" type="text" id="secondary_color" value="<?php echo esc_attr( $styles['secondary_color'] ); ?>" class="shoplink-color-field" data-default-color="#ffffff"></td>
                </tr>
                <tr>
                    <th scope="row"><label for="heading_color">Heading Color</label></th>
                    <td><input name="heading_color" type="text" id="heading_color" value="<?php echo esc_attr( $styles['heading_color'] ); ?>" class="shoplink-color-field" data-default-color="#111111"></td>
                </tr>
                <tr>
                    <th scope="row"><label for="text_color">Text Color</label></th>
                    <td><input name="text_color" type="text" id="text_color" value="<?php echo esc_attr( $styles['text_color'] ); ?>" class="shoplink-color-field" data-default-color="#333333"></td>
                </tr>
                <tr>
                    <th scope="row"><label for="link_color">Link Color</label></th>
                    <td><input name="link_color" type="text" id="link_color" value="<?php echo esc_attr( $styles['link_color'] ); ?>" class="shoplink-color-field" data-default-color="#000000"></td>
                </tr>
            </table>
            <p class="submit">
                <input type="submit" name="shoplink_save_styles" id="submit" class="button button-primary" value="Save Styles">
            </p>
        </form>
        <?php
    }

    private static function render_settings_tab() {
        if ( isset( $_POST['shoplink_save_settings'] ) && check_admin_referer( 'shoplink_save_settings_nonce' ) ) {
            update_option( 'shopify_store_url', sanitize_text_field( $_POST['shopify_store_url'] ) );
            $api_key = sanitize_text_field( $_POST['shopify_api_key'] );
            // Simple validation could be added here
            update_option( 'shopify_api_key', $api_key );
            echo '<div class="notice notice-success is-dismissible"><p>Settings saved.</p></div>';
        }

        $url = get_option( 'shopify_store_url' );
        $key = get_option( 'shopify_api_key' );
        ?>
        <form method="post" action="">
            <?php wp_nonce_field( 'shoplink_save_settings_nonce' ); ?>
            <table class="form-table">
                <tr>
                    <th scope="row"><label for="shopify_store_url">Shopify Store URL</label></th>
                    <td>
                        <input name="shopify_store_url" type="text" id="shopify_store_url" value="<?php echo esc_attr( $url ); ?>" class="regular-text" placeholder="yourstore.myshopify.com">
                        <p class="description">Your Shopify "myshopify.com" domain.</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="shopify_api_key">Shopify Storefront Access Token</label></th>
                    <td>
                        <input name="shopify_api_key" type="password" id="shopify_api_key" value="<?php echo esc_attr( $key ); ?>" class="regular-text">
                        <p class="description">Manage private apps in Shopify to get this token.</p>
                    </td>
                </tr>
            </table>
            <p class="submit">
                <input type="submit" name="shoplink_save_settings" id="submit" class="button button-primary" value="Save Settings">
            </p>
        </form>
        <?php
    }
}
