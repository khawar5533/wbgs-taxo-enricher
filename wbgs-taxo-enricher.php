<?php
/**
* Plugin Name: Taxonomy Enricher
* Plugin URI: https://www.webbuggs.com/
* Description: Adds short description and gallery image fields to all default and custom taxonomies in WordPress.
* Version: 1.0.2
* Author: webbuggs
* Author URI: https://www.webbuggs.com/
* Text Domain: taxonomy-enricher
*/

if (!class_exists('WBGS_TaxoPlus')) {

    class WBGS_TaxoPlus {

        public function __construct() {
            // Load Script and css
            add_action('admin_enqueue_scripts', [$this, 'wbgs_enqueue_admin_scripts']);
            //Register Taxonomy
            add_action('init', [$this, 'wbgs_register_taxonomy_hooks']);
            //Register Taxonomy Shortcode
            add_action('init', [$this, 'wbgs_register_shortcodes']);
            // Create Taxonomy Shortcode Column
            add_action('admin_init', [$this, 'wbgs_add_taxonomy_admin_columns']);
        }
       /**
        * This function registers and enqueues the JavaScript and CSS files required
        * for the plugin's admin interface. It also localizes script data to be used
        * in AJAX requests.
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
            wp_localize_script('wbgs-admin-tax-js', 'wbgs_data', [
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
        * This function is used to register taxonomy hook
        * save taxonomy meta field
        * edit taxonomy meta field
        */ 
        public function wbgs_register_taxonomy_hooks() {
            $taxonomies = get_taxonomies(['public' => true], 'names');

            foreach ($taxonomies as $taxonomy) {
                add_action("{$taxonomy}_add_form_fields", [$this, 'wbgs_add_taxonomy_meta_field'], 10, 2);
                add_action("{$taxonomy}_edit_form_fields", [$this, 'wbgs_edit_taxonomy_meta_field'], 10, 2);

                add_action("created_{$taxonomy}", [$this, 'wbgs_save_taxonomy_meta_field'], 10, 2);
                add_action("edited_{$taxonomy}", [$this, 'wbgs_save_taxonomy_meta_field'], 10, 2);
            }
        }
        /**
        * create the taxonomy html structure
        */
        public function wbgs_add_taxonomy_meta_field($taxonomy) {
            ?>
            <div class="form-field">
                <label for="wbgs_short_description"><?php _e('Short Description', 'taxonomy-enricher'); ?></label>
                <textarea name="wbgs_short_description" id="wbgs_short_description" rows="4" cols="50"></textarea>
                <p class="description"><?php _e('Enter a short description value for this term.', 'taxonomy-enricher'); ?></p>
            </div>
            <div class="form-field">
                <label for="wbgs_gallery"><?php _e('Gallery Images', 'taxonomy-enricher'); ?></label>
                <button class="button wbgs-add-gallery"><?php _e('Add Images', 'taxonomy-enricher'); ?></button>
                <ul class="wbgs-gallery-preview"></ul>
                <input type="hidden" name="wbgs_gallery" id="wbgs_gallery" class="wbgs-gallery" value="">
                <p class="description"><?php _e('Select multiple images for this term.', 'taxonomy-enricher'); ?></p>
            </div>
            <?php
        }
        /**
        * edit the taxonomy html structure
        */
        public function wbgs_edit_taxonomy_meta_field($term, $taxonomy) {
            $desc = get_term_meta($term->term_id, 'wbgs_short_description', true);
            $gallery = get_term_meta($term->term_id, 'wbgs_gallery', true);
            $gallery = is_array($gallery) ? $gallery : [];

            ?>
            <tr class="form-field">
                <th scope="row"><label for="wbgs_short_description"><?php _e('Short Description', 'taxonomy-enricher'); ?></label></th>
                <td>
                    <textarea name="wbgs_short_description" id="wbgs_short_description" rows="4" cols="50"><?php echo esc_textarea($desc); ?></textarea>
                    <p class="description"><?php _e('Update the short description for this term.', 'taxonomy-enricher'); ?></p>
                </td>
            </tr>
            <tr class="form-field">
                <th scope="row"><label for="wbgs_gallery"><?php _e('Gallery Images', 'taxonomy-enricher'); ?></label></th>
                <td>
                    <button class="button wbgs-add-gallery"><?php _e('Add Images', 'taxonomy-enricher'); ?></button>
                    <ul class="wbgs-gallery-preview">
                        <?php foreach ($gallery as $img_id): ?>
                            <li><img src="<?php echo esc_url(wp_get_attachment_image_url($img_id, 'thumbnail')); ?>" /></li>
                        <?php endforeach; ?>
                    </ul>
                    <input type="hidden" name="wbgs_gallery" id="wbgs_gallery" class="wbgs-gallery" value="<?php echo esc_attr(implode(',', $gallery)); ?>">
                    <p class="description"><?php _e('Update gallery images for this term.', 'taxonomy-enricher'); ?></p>
                </td>
            </tr>
            <?php
        }
        /**
        * Save taxonomy meta field
        */
        public function wbgs_save_taxonomy_meta_field($term_id) {
            if (isset($_POST['wbgs_short_description'])) {
                update_term_meta($term_id, 'wbgs_short_description', sanitize_text_field($_POST['wbgs_short_description']));
            }

            if (!empty($_POST['wbgs_gallery'])) {
                $ids = array_map('intval', explode(',', sanitize_text_field($_POST['wbgs_gallery'])));
                update_term_meta($term_id, 'wbgs_gallery', $ids);
            } else {
                delete_term_meta($term_id, 'wbgs_gallery');
            }
        }
        /**
        * Register Taxomony ShortCode
        */
        public function wbgs_register_shortcodes() {
            add_shortcode('wbgs_taxonomy_display', [$this, 'wbgs_taxonomy_display_shortcode']);
        }
        /**
        * Display Taxomony ShortCode
        */
        public function wbgs_taxonomy_display_shortcode($atts) {
            $atts = shortcode_atts([
                'id'   => 0,
                'show' => 'both',
            ], $atts, 'wbgs_taxonomy_display');

            $term_id = intval($atts['id']);
            if (!$term_id) {
                return '<p>' . __('Invalid taxonomy term ID.', 'taxonomy-enricher') . '</p>';
            }

            $output = '';
            $description = get_term_meta($term_id, 'wbgs_short_description', true);
            $gallery = get_term_meta($term_id, 'wbgs_gallery', true);
            $gallery = is_array($gallery) ? $gallery : [];

            if (in_array($atts['show'], ['short_description', 'both'])) {
                if ($description) {
                    $output .= '<div class="wbgs-term-description"><p>' . esc_html($description) . '</p></div>';
                }
            }

            if (in_array($atts['show'], ['gallery', 'both']) && !empty($gallery)) {
                $output .= '<div class="wbgs-term-gallery">';
                foreach ($gallery as $img_id) {
                    $img_url = wp_get_attachment_image_url($img_id, 'medium');
                    $output .= '<img src="' . esc_url($img_url) . '" alt="" style="margin:5px; max-width:150px; height:auto;">';
                }
                $output .= '</div>';
            }

            return $output ? $output : '<p>' . __('No data available.', 'taxonomy-enricher') . '</p>';
        }
        /**
        * Display Taxomony admin column
        */
        public function wbgs_add_taxonomy_admin_columns() {
            $taxonomies = get_taxonomies(['public' => true], 'names');

            foreach ($taxonomies as $taxonomy) {
                add_filter("manage_edit-{$taxonomy}_columns", function ($columns) {
                    $new_columns = [];

                    foreach ($columns as $key => $value) {
                        $new_columns[$key] = $value;
                        if ($key === 'slug') {
                            $new_columns['wbgs_shortcode'] = __('Shortcode', 'taxonomy-enricher');
                        }
                    }

                    return $new_columns;
                });

                add_filter("manage_{$taxonomy}_custom_column", function ($content, $column_name, $term_id) {
                    if ($column_name === 'wbgs_shortcode') {
                        $content = '<code>[wbgs_taxonomy_display id="' . $term_id . '" show="both"]</code>';
                    }
                    return $content;
                }, 10, 3);
            }
        }

        public static function activate() {
            // Setup tasks
        }

        public static function deactivate() {
            // Cleanup tasks
        }
    }
       /**
        * Load the plugin class
        */
    function wbgs_taxoplus_init() {
        new WBGS_TaxoPlus();
    }
    add_action('plugins_loaded', 'wbgs_taxoplus_init');

    register_activation_hook(__FILE__, ['WBGS_TaxoPlus', 'activate']);
    register_deactivation_hook(__FILE__, ['WBGS_TaxoPlus', 'deactivate']);
}
