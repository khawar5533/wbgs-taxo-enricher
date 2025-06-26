<?php
/**
 * Plugin Name:       TaxoPlus Fields
 * Plugin URI:        https://www.webbuggs.com/
 * Description:       Friendly and developer-usable
 * Version:           1.0.0
 * Requires at least: 6.8
 * Author:            Webbuggs
 * Author URI:        https://www.webbuggs.com/
 * Text Domain:       taxo-plus
 * License:           GPLv2 or later
 */

defined('ABSPATH') || exit;
// Activation hook to set a one-time flag

if (!class_exists('WBGS_TaxoPlus')) {
    class WBGS_TaxoPlus {

        public function __construct() {

            // add_action('admin_enqueue_scripts',[$this,'wbgs_enqueue_admin_scripts']);

        }


        //include the css using js and css for admin side
        // public function wbgs_enqueue_admin_scripts() {

        //     // Enqueue media uploader
        //     // wp_enqueue_media();
        //     // // Enqueue your JS file
        //     // wp_enqueue_script(
        //     //     'wbgs-admin-js',
        //     //     plugin_dir_url(__FILE__) . 'assets/js/wbgs-main.js',
        //     //     ['jquery'],
        //     //     true
        //     // );
        //     // // Localize the script to pass PHP data to JS
        //     //  wp_localize_script('wbgs-admin-js', 'wbgs_data', [
        //     //     'ajaxurl' => admin_url('admin-ajax.php'),
        //     //     'nonce'   => wp_create_nonce('wbgs_nonce')
        //     // ]);
        //     // Enqueue CSS
        //     // wp_enqueue_style(
        //     //     'wbgs-admin-css',
        //     //     // plugin_dir_url(__FILE__) . 'assets/css/wbgs-styles.css',
        //     //     [],
        //     //     null // version (you can specify a version if needed)
        //     // );
        
        
        // }

    }

    // Instantiate the class
    new WBGS_SmartCountdownScarcityWBGS_TaxoPlus();
}

//  Include additional files

// require_once plugin_dir_path(__FILE__) . 'includes/page-settings-fields.php';


