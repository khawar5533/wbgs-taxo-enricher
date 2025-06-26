<?php
/*
Plugin Name: WBGS TaxoPlus
Description: Adds custom fields to taxonomy terms and displays them on the front-end.
Version: 1.1
Author: Simpliplugin.com
*/

if (!class_exists('WBGS_TaxoPlus')) {

    class WBGS_TaxoPlus {

        /**
         * Constructor.
         */
        public function __construct() {
            // Enqueue admin assets
            add_action('admin_enqueue_scripts', [$this, 'wbgs_enqueue_admin_scripts']);

            // Register hooks for all public taxonomies
            add_action('init', [$this, 'wbgs_register_taxonomy_hooks']);
        }

        /**
         * Enqueue admin scripts and styles.
         */
        public function wbgs_enqueue_admin_scripts() {
            wp_enqueue_media();
            wp_enqueue_script(
                'wbgs-admin-tax-js',
                plugin_dir_url(__FILE__) . 'assets/js/wbgs-main-tax.js',
                ['jquery'],
                null,
                true
            );
            wp_localize_script('wbgs-admin-js', 'wbgs_data', [
                'ajaxurl' => admin_url('admin-ajax.php'),
                'nonce'   => wp_create_nonce('wbgs_nonce'),
            ]);
            wp_enqueue_style(
                'wbgs-admin-tax-css',
                plugin_dir_url(__FILE__) . 'assets/css/wbgs-styles-tax.css',
                [],
                null
            );
        }

        /**
         * Register custom field hooks for all public taxonomies.
         */
        public function wbgs_register_taxonomy_hooks() {
            $taxonomies = get_taxonomies(['public' => true], 'names');

            foreach ($taxonomies as $taxonomy) {
                // Add fields to add/edit forms
                add_action("{$taxonomy}_add_form_fields", [$this, 'wbgs_add_taxonomy_meta_field'], 10, 2);
                add_action("{$taxonomy}_edit_form_fields", [$this, 'wbgs_edit_taxonomy_meta_field'], 10, 2);

                // Save custom field value
                add_action("created_{$taxonomy}", [$this, 'wbgs_save_taxonomy_meta_field'], 10, 2);
                add_action("edited_{$taxonomy}", [$this, 'wbgs_save_taxonomy_meta_field'], 10, 2);
            }
        }

        /**
         * Add custom field to Add New taxonomy form.
         */
        public function wbgs_add_taxonomy_meta_field($taxonomy) {
            ?>
            <div class="form-field">
                <label for="wbgs_short_description"><?php _e('Short Description', 'wbgs'); ?></label>
                <textarea name="wbgs_short_description" id="wbgs_short_description" rows="4" cols="50"></textarea>
                <p class="description"><?php _e('Enter a short description value for this term.', 'wbgs'); ?></p>
            </div>
            <?php
        }

        /**
         * Edit custom field in Edit taxonomy form.
         */
        public function wbgs_edit_taxonomy_meta_field($term, $taxonomy) {
            $value = get_term_meta($term->term_id, 'wbgs_short_description', true);
            ?>
            <tr class="form-field">
                <th scope="row"><label for="wbgs_short_description"><?php _e('Short Description', 'wbgs'); ?></label></th>
                <td>
                    <textarea name="wbgs_short_description" id="wbgs_short_description" rows="4" cols="50"><?php echo esc_textarea($value); ?></textarea>
                    <p class="description"><?php _e('Update the short description for this term.', 'wbgs'); ?></p>
                </td>
            </tr>
            <?php
        }

        /**
         * Save the custom field when a term is created or edited.
         */
        public function wbgs_save_taxonomy_meta_field($term_id) {
            if (isset($_POST['wbgs_short_description'])) {
                update_term_meta($term_id, 'wbgs_short_description', sanitize_text_field($_POST['wbgs_short_description']));
            }
        }

        /**
         * Plugin activation hook.
         */
        public static function activate() {
            // Setup tasks
        }

        /**
         * Plugin deactivation hook.
         */
        public static function deactivate() {
            // Cleanup tasks
        }
    }

    // Initialize the plugin
    function wbgs_taxoplus_init() {
        new WBGS_TaxoPlus();
    }
    add_action('plugins_loaded', 'wbgs_taxoplus_init');

    // Register activation/deactivation hooks
    register_activation_hook(__FILE__, ['WBGS_TaxoPlus', 'activate']);
    register_deactivation_hook(__FILE__, ['WBGS_TaxoPlus', 'deactivate']);
}
