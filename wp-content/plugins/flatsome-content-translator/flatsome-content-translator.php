<?php
/**
 * Plugin Name: Content Translator
 * Plugin URI: https://vionis.vn
 * Description: Công cụ dịch nội dung thiết kế bằng Flatsome UX Builder từ Tiếng Việt sang Tiếng Anh một cách an toàn, không hỏng bố cục, tích hợp chế độ Clone và Ghi đè có Backup.
 * Version: 1.0.0
 * Author: Antigravity & Vionis Team
 * Author URI: https://vionis.vn
 * License: GPLv2 or later
 * Text Domain: flatsome-translator
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Define Constants
define('FL_TRANSLATOR_VERSION', '1.0.0');
define('FL_TRANSLATOR_PATH', plugin_dir_path(__FILE__));
define('FL_TRANSLATOR_URL', plugin_dir_url(__FILE__));

// Require Core Files
require_once FL_TRANSLATOR_PATH . 'includes/class-translator.php';
require_once FL_TRANSLATOR_PATH . 'admin/class-admin.php';

// Initialization
function fl_translator_init() {
    if (is_admin()) {
        new FL_Translator_Admin();
    }
}
add_action('plugins_loaded', 'fl_translator_init');

// Activation Hook
register_activation_hook(__FILE__, 'fl_translator_activate');
function fl_translator_activate() {
    // Perform any setup or option initialization here if needed
}

// Đăng ký script frontend cho hoán đổi song ngữ song song
add_action('wp_enqueue_scripts', 'fl_translator_frontend_assets');
function fl_translator_frontend_assets() {
    if (is_singular(array('product', 'tour', 'service'))) {
        wp_enqueue_script(
            'fl-frontend-bilingual-js',
            FL_TRANSLATOR_URL . 'admin/assets/js/frontend-bilingual.js',
            array('jquery'),
            FL_TRANSLATOR_VERSION,
            true
        );
    }
}

// In container ẩn chứa các trường dịch Tiếng Việt ở chân trang chi tiết
add_action('wp_footer', 'fl_translator_render_hidden_translations');
function fl_translator_render_hidden_translations() {
    if (!is_singular(array('product', 'tour', 'service'))) {
        return;
    }
    
    $post_id = get_the_ID();
    $vi_title = get_post_meta($post_id, '_vi_title', true);
    $vi_excerpt = get_post_meta($post_id, '_vi_excerpt', true);
    $vi_content = get_post_meta($post_id, '_vi_content', true);
    $vi_place = get_post_meta($post_id, '_vi_place', true);
    $vi_schedule = get_post_meta($post_id, '_vi_schedule', true);
    
    $orig_place = get_field('place', $post_id);

    // Bỏ qua nếu hoàn toàn không có bản dịch Tiếng Việt nào được nhập
    if (empty($vi_title) && empty($vi_excerpt) && empty($vi_content) && empty($vi_place) && empty($vi_schedule)) {
        return;
    }
    
    if (!is_array($vi_schedule)) {
        $vi_schedule = array();
    }
    ?>
    <div id="manual-vi-translations" style="display:none;" class="notranslate"
         data-title="<?php echo esc_attr($vi_title); ?>"
         data-place="<?php echo esc_attr($vi_place); ?>"
         data-orig-place="<?php echo esc_attr($orig_place); ?>"
         data-excerpt="<?php echo esc_attr(wp_strip_all_tags(wpautop(do_shortcode($vi_excerpt)))); ?>">
         <div id="manual-vi-content"><?php echo wpautop(do_shortcode($vi_content)); ?></div>
         <div id="manual-vi-schedule"><?php echo esc_html(json_encode($vi_schedule)); ?></div>
    </div>
    <?php
}

