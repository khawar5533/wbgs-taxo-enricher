<?php
/*
Plugin Name: WBGS TaxoPlus
Description: Adds custom fields (short description and image gallery) to taxonomy terms.
Version: 1.2
Text Domain: wbgs-taxoplus
Author: Simpliplugin.com
License: GPLv2 or later
*/

if (!class_exists('WBGS_TaxoPlus')) {

    class WBGS_TaxoPlus {

        public function __construct() {
            add_action('admin_enqueue_scripts', [$this, 'wbgs_enqueue_admin_scripts']);
            add_action('init', [$this, 'wbgs_register_taxonomy_hooks']);
        }

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

        public function wbgs_register_taxonomy_hooks() {
            $taxonomies = get_taxonomies(['public' => true], 'names');

            foreach ($taxonomies as $taxonomy) {
                add_action("{$taxonomy}_add_form_fields", [$this, 'wbgs_add_taxonomy_meta_field'], 10, 2);
                add_action("{$taxonomy}_edit_form_fields", [$this, 'wbgs_edit_taxonomy_meta_field'], 10, 2);

                add_action("created_{$taxonomy}", [$this, 'wbgs_save_taxonomy_meta_field'], 10, 2);
                add_action("edited_{$taxonomy}", [$this, 'wbgs_save_taxonomy_meta_field'], 10, 2);
            }
        }

        public function wbgs_add_taxonomy_meta_field($taxonomy) {
            ?>
            <div class="form-field">
                <label for="wbgs_short_description"><?php _e('Short Description', 'wbgs-taxoplus'); ?></label>
                <textarea name="wbgs_short_description" id="wbgs_short_description" rows="4" cols="50"></textarea>
                <p class="description"><?php _e('Enter a short description value for this term.', 'wbgs-taxoplus'); ?></p>
            </div>
            <div class="form-field">
                <label for="wbgs_gallery"><?php _e('Gallery Images', 'wbgs-taxoplus'); ?></label>
                <button class="button wbgs-add-gallery"><?php _e('Add Images', 'wbgs-taxoplus'); ?></button>
                <ul class="wbgs-gallery-preview"></ul>
                <input type="hidden" name="wbgs_gallery" id="wbgs_gallery" class="wbgs-gallery" value="">
                <p class="description"><?php _e('Select multiple images for this term.', 'wbgs-taxoplus'); ?></p>
            </div>
            <?php
        }

        public function wbgs_edit_taxonomy_meta_field($term, $taxonomy) {
            $desc = get_term_meta($term->term_id, 'wbgs_short_description', true);
            $gallery = get_term_meta($term->term_id, 'wbgs_gallery', true);
            $gallery = is_array($gallery) ? $gallery : [];

            ?>
            <tr class="form-field">
                <th scope="row"><label for="wbgs_short_description"><?php _e('Short Description', 'wbgs-taxoplus'); ?></label></th>
                <td>
                    <textarea name="wbgs_short_description" id="wbgs_short_description" rows="4" cols="50"><?php echo esc_textarea($desc); ?></textarea>
                    <p class="description"><?php _e('Update the short description for this term.', 'wbgs-taxoplus'); ?></p>
                </td>
            </tr>
            <tr class="form-field">
                <th scope="row"><label for="wbgs_gallery"><?php _e('Gallery Images', 'wbgs-taxoplus'); ?></label></th>
                <td>
                    <button class="button wbgs-add-gallery"><?php _e('Add Images', 'wbgs-taxoplus'); ?></button>
                    <ul class="wbgs-gallery-preview">
                        <?php foreach ($gallery as $img_id): ?>
                            <li><img src="<?php echo esc_url(wp_get_attachment_image_url($img_id, 'thumbnail')); ?>" /></li>
                        <?php endforeach; ?>
                    </ul>
                   <input type="hidden" name="wbgs_gallery" id="wbgs_gallery" class="wbgs-gallery" value="<?php echo esc_attr(implode(',', $gallery)); ?>">
                    <p class="description"><?php _e('Update gallery images for this term.', 'wbgs-taxoplus'); ?></p>
                </td>
            </tr>
            <?php
        }

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

        public static function activate() {
            // Setup tasks
        }

        public static function deactivate() {
            // Cleanup tasks
        }
    }

    function wbgs_taxoplus_init() {
        new WBGS_TaxoPlus();
    }
    add_action('plugins_loaded', 'wbgs_taxoplus_init');

    register_activation_hook(__FILE__, ['WBGS_TaxoPlus', 'activate']);
    register_deactivation_hook(__FILE__, ['WBGS_TaxoPlus', 'deactivate']);
}
