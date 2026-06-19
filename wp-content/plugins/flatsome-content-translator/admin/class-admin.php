<?php
if (!defined('ABSPATH')) {
    exit;
}

class FL_Translator_Admin {

    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_assets'));

        // Đăng ký Metabox biên dịch song ngữ
        add_action('add_meta_boxes', array($this, 'add_bilingual_meta_box'));

        // Đăng ký AJAX hooks
        add_action('wp_ajax_fl_translator_get_posts', array($this, 'ajax_get_posts'));
        add_action('wp_ajax_fl_translator_analyze', array($this, 'ajax_analyze_post'));
        add_action('wp_ajax_fl_translator_translate_batch', array($this, 'ajax_translate_batch'));
        add_action('wp_ajax_fl_translator_save', array($this, 'ajax_save_translation'));
        add_action('wp_ajax_fl_translator_get_backups', array($this, 'ajax_get_backups'));
        add_action('wp_ajax_fl_translator_restore', array($this, 'ajax_restore_backup'));
        add_action('wp_ajax_fl_translator_save_settings', array($this, 'ajax_save_settings'));
        add_action('wp_ajax_fl_translator_check_key', array($this, 'ajax_check_key'));
        add_action('wp_ajax_fl_translator_workspace_translate', array($this, 'ajax_workspace_translate'));
    }

    /**
     * Tạo menu Dịch trong admin
     */
    public function add_admin_menu() {
        add_menu_page(
            'Dịch',
            'Dịch',
            'manage_options',
            'flatsome-translator',
            array($this, 'render_admin_page'),
            'dashicons-translation',
            30
        );

        // Đăng ký trang Biên dịch chuyên dụng ẩn (null parent)
        add_submenu_page(
            null, // Ẩn khỏi menu
            'Biên dịch Sản phẩm & Tour Song ngữ',
            'Biên dịch sản phẩm',
            'manage_options',
            'fl-product-translator',
            array($this, 'render_product_translator_page')
        );
    }

    /**
     * Enqueue CSS & JS
     */
    public function enqueue_assets($hook) {
        if ($hook === 'toplevel_page_flatsome-translator') {
            wp_enqueue_style('google-font-inter', 'https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@400;500;600;700&display=swap', array(), null);
            wp_enqueue_style('fl-translator-admin-css', FL_TRANSLATOR_URL . 'admin/assets/css/admin.css', array(), FL_TRANSLATOR_VERSION);
            wp_enqueue_script('fl-translator-admin-js', FL_TRANSLATOR_URL . 'admin/assets/js/admin.js', array('jquery'), FL_TRANSLATOR_VERSION, true);

            // Truyền các biến toàn cục sang JS
            wp_localize_script('fl-translator-admin-js', 'flTranslatorOpts', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce'    => wp_create_nonce('fl_translator_nonce'),
            ));
        }

        // Tải asset cho trang biên dịch song ngữ chuyên dụng toàn màn hình
        if ($hook === 'admin_page_fl-product-translator') {
            $current_gemini_key = get_option('fl_translator_gemini_key', '');
            $has_gemini_key = !empty($current_gemini_key);

            wp_enqueue_style('google-font-inter', 'https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@400;500;600;700&display=swap', array(), null);
            wp_enqueue_style('fl-workspace-css', FL_TRANSLATOR_URL . 'admin/assets/css/translator-workspace.css', array(), FL_TRANSLATOR_VERSION);
            wp_enqueue_script('fl-bilingual-editor-js', FL_TRANSLATOR_URL . 'admin/assets/js/bilingual-editor.js', array('jquery'), FL_TRANSLATOR_VERSION, true);

            // Truyền các biến toàn cục sang JS cho workspace
            wp_localize_script('fl-bilingual-editor-js', 'flTranslatorOpts', array(
                'ajax_url'       => admin_url('admin-ajax.php'),
                'nonce'          => wp_create_nonce('fl_translator_nonce'),
                'post_id'        => isset($_GET['post_id']) ? intval($_GET['post_id']) : 0,
                'mode'           => isset($_GET['mode']) ? sanitize_text_field($_GET['mode']) : 'manual',
                'has_gemini_key' => $has_gemini_key,
                'configure_url'  => admin_url('admin.php?page=flatsome-translator')
            ));
        }
    }

    /**
     * Hiển thị giao diện Admin Dashboard
     */
    public function render_admin_page() {
        $current_engine = get_option('fl_translator_engine', 'google');
        $current_gemini_key = get_option('fl_translator_gemini_key', '');
        ?>
        <div class="wrap fl-translator-wrapper">
            <header class="fl-header">
                <div class="fl-header-brand">
                    <span class="dashicons dashicons-translation fl-brand-icon"></span>
                    <div>
                        <h1>Content Translator</h1>
                        <p class="fl-subtitle">Dịch thuật an toàn chuyên biệt cho UX Builder</p>
                    </div>
                </div>
                <div class="fl-header-badge">
                    <span>Phiên bản <?php echo FL_TRANSLATOR_VERSION; ?></span>
                </div>
            </header>

            <main class="fl-main-layout">
                <!-- Cột trái: Bộ điều khiển chọn bài viết & Cấu hình dịch -->
                <section class="fl-card fl-config-panel">
                    <h2 class="fl-section-title"><span class="dashicons dashicons-admin-settings"></span> Cấu hình dịch thuật</h2>
                    
                    <!-- Cấu hình Bộ máy dịch thuật -->
                    <div class="fl-form-group">
                        <label for="fl-engine-select">Bộ máy dịch thuật</label>
                        <select id="fl-engine-select" class="fl-select">
                            <option value="google" <?php selected($current_engine, 'google'); ?>>Google Translate (Miễn phí)</option>
                            <option value="gemini" <?php selected($current_engine, 'gemini'); ?>>Google Gemini AI (Premium - Chuẩn nhất)</option>
                        </select>
                    </div>

                    <div id="fl-gemini-key-group" class="fl-form-group" style="<?php echo $current_engine === 'gemini' ? '' : 'display: none;'; ?>">
                        <label for="fl-gemini-key">Gemini API Key</label>
                        <div class="fl-api-key-row">
                            <input type="password" id="fl-gemini-key" class="fl-input" value="<?php echo esc_attr($current_gemini_key); ?>" placeholder="Nhập Gemini API Key...">
                            <button type="button" id="btn-check-key" class="fl-btn-check">Xác nhận & Kiểm tra</button>
                        </div>
                        <div id="fl-key-status" class="fl-key-status-msg"></div>
                        <p class="fl-info-desc" style="margin-top: 5px;">Lấy khóa miễn phí tại <a href="https://aistudio.google.com/" target="_blank" rel="noopener">Google AI Studio</a> để có bản dịch hoàn hảo ngữ cảnh.</p>
                    </div>

                    <hr class="fl-divider" style="margin: 15px 0 20px 0;">

                    <div class="fl-form-group">
                        <label for="fl-post-type">1. Chọn loại nội dung</label>
                        <select id="fl-post-type" class="fl-select">
                            <option value="page">Trang (Page)</option>
                            <option value="post">Bài viết (Post)</option>
                            <option value="product">Sản phẩm (Product)</option>
                            <option value="service">Dịch vụ (Service - CPT)</option>
                            <option value="tour">Tour du lịch (CPT)</option>
                        </select>
                    </div>

                    <div class="fl-form-group">
                        <label for="fl-post-id">2. Chọn trang/bài viết cần dịch</label>
                        <select id="fl-post-id" class="fl-select" disabled>
                            <option value="">-- Đang tải danh sách... --</option>
                        </select>
                    </div>

                    <div class="fl-form-group">
                        <label>3. Chiều dịch thuật</label>
                        <div class="fl-lang-flow-selector">
                            <select id="fl-source-lang" class="fl-lang-select">
                                <option value="vi" selected>Tiếng Việt (VI)</option>
                                <option value="en">Tiếng Anh (EN)</option>
                            </select>
                            <button type="button" id="btn-swap-langs" class="fl-btn-swap" title="Đổi chiều dịch">
                                <span class="dashicons dashicons-sort"></span>
                            </button>
                            <select id="fl-target-lang" class="fl-lang-select">
                                <option value="en" selected>Tiếng Anh (EN)</option>
                                <option value="vi">Tiếng Việt (VI)</option>
                            </select>
                        </div>
                    </div>

                    <div class="fl-form-group">
                        <label>4. Phương thức xuất bản</label>
                        <div class="fl-radio-group">
                            <label class="fl-radio-label active">
                                <input type="radio" name="fl-save-mode" value="clone" checked>
                                <div class="fl-radio-content">
                                    <strong>Nhân bản trang mới (Clone)</strong>
                                    <span>Sao chép toàn bộ thuộc tính, meta, danh mục & dịch an toàn.</span>
                                </div>
                            </label>
                            
                            <label class="fl-radio-label warning">
                                <input type="radio" name="fl-save-mode" value="overwrite">
                                <div class="fl-radio-content">
                                    <strong>Ghi đè trực tiếp (Overwrite)</strong>
                                    <span>Thay thế nội dung trang gốc, tự động lưu bản dự phòng.</span>
                                </div>
                            </label>
                        </div>
                    </div>

                    <div class="fl-action-buttons">
                        <button id="btn-analyze" class="fl-btn primary disabled" disabled>
                            <span class="dashicons dashicons-performance"></span> Phân tích & Dịch tự động
                        </button>
                    </div>

                    <!-- Phần lịch sử backup -->
                    <div id="fl-backup-section" class="fl-backup-section" style="display: none;">
                        <hr class="fl-divider">
                        <h3 class="fl-section-subtitle"><span class="dashicons dashicons-backup"></span> Lịch sử khôi phục (Backup)</h3>
                        <p class="fl-info-desc">Bạn có thể phục hồi lại nội dung gốc của trang trước khi ghi đè tại đây.</p>
                        <ul id="fl-backup-list" class="fl-backup-list">
                            <!-- Danh sách backup load qua AJAX -->
                        </ul>
                    </div>
                </section>

                <!-- Cột phải: Không gian dịch song ngữ (Split-Screen Workspace) -->
                <section class="fl-card fl-workspace-panel">
                    <div id="workspace-idle" class="fl-workspace-idle">
                        <span class="dashicons dashicons-welcome-write-blog fl-idle-icon"></span>
                        <h3>Không gian dịch thuật song ngữ</h3>
                        <p>Vui lòng cấu hình và chọn trang cần dịch ở cột bên trái, sau đó nhấn nút "Phân tích & Dịch tự động".</p>
                    </div>

                    <div id="workspace-active" class="fl-workspace-active" style="display: none;">
                        <div class="fl-workspace-header">
                            <h2 class="fl-section-title"><span class="dashicons dashicons-editor-code"></span> Biên tập song ngữ</h2>
                            <div class="fl-translation-progress-container">
                                <div class="fl-progress-bar-wrapper">
                                    <div id="fl-progress-bar" class="fl-progress-bar" style="width: 0%;"></div>
                                </div>
                                <span id="fl-progress-text" class="fl-progress-text">Đang tải...</span>
                            </div>
                        </div>

                        <!-- Tiêu đề và slug của Post -->
                        <div class="fl-post-meta-translator">
                            <div class="fl-meta-row">
                                <div class="fl-meta-col font-bold fl-meta-title-label-orig">Tiêu đề gốc (VI)</div>
                                <div class="fl-meta-col font-bold fl-meta-title-label-trans">Tiêu đề dịch (EN)</div>
                            </div>
                            <div class="fl-meta-row">
                                <div class="fl-meta-col">
                                    <input type="text" id="fl-orig-title" class="fl-input" readonly>
                                </div>
                                <div class="fl-meta-col">
                                    <input type="text" id="fl-trans-title" class="fl-input edit-field" placeholder="Tiêu đề tiếng Anh...">
                                </div>
                            </div>
                        </div>

                        <div class="fl-split-labels">
                            <div class="fl-split-label">Bản gốc Tiếng Việt</div>
                            <div class="fl-split-label">Bản dịch Tiếng Anh (Có thể chỉnh sửa)</div>
                        </div>

                        <!-- Khu vực danh sách các chuỗi dịch thuật -->
                        <div id="fl-translation-list" class="fl-translation-list">
                            <!-- Các dòng dịch thuật sẽ được render động ở đây -->
                        </div>

                        <div class="fl-workspace-footer">
                            <div class="fl-info-alert" id="overwrite-alert" style="display: none;">
                                <span class="dashicons dashicons-warning"></span> 
                                <strong>Cảnh báo:</strong> Bạn đang chọn chế độ ghi đè trực tiếp lên trang gốc. Một bản sao lưu của trang gốc sẽ được tạo tự động để khôi phục khi cần.
                            </div>
                            <button id="btn-save-translation" class="fl-btn success large">
                                <span class="dashicons dashicons-cloud-saved"></span> Hoàn thành & Lưu bản dịch
                            </button>
                        </div>
                    </div>
                </section>
            </main>
            
            <!-- Custom Modal Popup Premium -->
            <div id="fl-modal-container" class="fl-modal-overlay" style="display: none;">
                <div class="fl-modal-box">
                    <span class="dashicons dashicons-yes-alt fl-modal-success-icon"></span>
                    <h3 class="fl-modal-title">Dịch thuật hoàn tất!</h3>
                    <p id="fl-modal-message" class="fl-modal-desc">Nội dung của bạn đã được dịch thành công.</p>
                    <div class="fl-modal-buttons">
                        <button type="button" id="btn-modal-close" class="fl-btn-modal secondary">Tiếp tục dịch</button>
                        <a id="link-modal-view" href="#" class="fl-btn-modal primary" target="_blank">Xem sản phẩm</a>
                    </div>
                </div>
            </div>

            <!-- Custom Confirmation Modal Popup Premium -->
            <div id="fl-confirm-modal-container" class="fl-modal-overlay" style="display: none;">
                <div class="fl-modal-box">
                    <span class="dashicons dashicons-warning fl-modal-warning-icon"></span>
                    <h3 class="fl-modal-title">Xác nhận ghi đè!</h3>
                    <p class="fl-modal-desc">Bạn đang chọn ghi đè trực tiếp nội dung dịch lên trang gốc.<br><br>Hệ thống sẽ <strong>tự động tạo một bản sao lưu (Backup)</strong> để bạn có thể khôi phục lại bất cứ lúc nào.</p>
                    <div class="fl-modal-buttons">
                        <button type="button" id="btn-confirm-cancel" class="fl-btn-modal secondary">Hủy bỏ</button>
                        <button type="button" id="btn-confirm-ok" class="fl-btn-modal primary warning-btn">Đồng ý & Ghi đè</button>
                    </div>
                </div>
            </div>

        </div>
        <?php
    }

    /* ==========================================================================
       CÁC HÀM AJAX XỬ LÝ BACKEND
       ========================================================================== */

    private function verify_request() {
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Bạn không có quyền thực hiện hành động này.'));
        }
        if (!check_ajax_referer('fl_translator_nonce', 'security', false)) {
            wp_send_json_error(array('message' => 'Lỗi bảo mật (Invalid nonce).'));
        }
    }

    /**
     * AJAX lưu cấu hình bộ máy dịch thuật
     */
    public function ajax_save_settings() {
        $this->verify_request();

        $engine = sanitize_text_field($_POST['engine']);
        $gemini_key = sanitize_text_field($_POST['gemini_key']);

        update_option('fl_translator_engine', $engine);
        update_option('fl_translator_gemini_key', $gemini_key);

        wp_send_json_success(array('message' => 'Đã lưu cấu hình dịch thuật!'));
    }

    /**
     * AJAX kiểm tra và lưu Gemini API Key
     */
    public function ajax_check_key() {
        $this->verify_request();

        $api_key = sanitize_text_field($_POST['api_key']);

        if (empty($api_key)) {
            wp_send_json_error(array('message' => 'Vui lòng nhập API Key.'));
        }

        $is_valid = FL_Translator_Core::check_gemini_key($api_key);

        if ($is_valid) {
            update_option('fl_translator_gemini_key', $api_key);
            update_option('fl_translator_engine', 'gemini');
            wp_send_json_success(array('message' => 'API Key hợp lệ! Đã kết nối thành công với Google Gemini AI.'));
        } else {
            wp_send_json_error(array('message' => 'API Key không hợp lệ hoặc lỗi kết nối. Vui lòng kiểm tra lại.'));
        }
    }

    /**
     * AJAX dịch tự động các trường của sản phẩm trong không gian dịch chuyên dụng
     */
    public function ajax_workspace_translate() {
        $this->verify_request();

        $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
        $engine = isset($_POST['engine']) ? sanitize_text_field($_POST['engine']) : 'google';
        $api_key = get_option('fl_translator_gemini_key', '');

        $post = get_post($post_id);
        if (!$post) {
            wp_send_json_error(array('message' => 'Không tìm thấy bài viết này.'));
        }

        $title = $post->post_title;
        $excerpt = $post->post_excerpt;
        $content = $post->post_content;
        $place = get_field('place', $post_id);
        $schedule = get_field('schedule', $post_id);

        if (!is_array($schedule)) {
            $schedule = array();
        }

        // Gom tất cả các chuỗi cần dịch
        $raw_strings = array();

        if (!empty($title)) {
            $raw_strings[] = trim($title);
        }
        if (!empty($place)) {
            $raw_strings[] = trim($place);
        }

        // Trích xuất các chuỗi từ HTML/Shortcodes của Excerpt và Content
        if (!empty($excerpt)) {
            $excerpt_strings = FL_Translator_Core::extract_translatable_strings('', $excerpt, '');
            $raw_strings = array_merge($raw_strings, $excerpt_strings);
        }
        if (!empty($content)) {
            $content_strings = FL_Translator_Core::extract_translatable_strings('', $content, '');
            $raw_strings = array_merge($raw_strings, $content_strings);
        }

        // Lịch trình chi tiết
        foreach ($schedule as $row) {
            if (isset($row['title']) && !empty($row['title'])) {
                $raw_strings[] = trim($row['title']);
            }
            if (isset($row['content']) && !empty($row['content'])) {
                $sch_strings = FL_Translator_Core::extract_translatable_strings('', $row['content'], '');
                $raw_strings = array_merge($raw_strings, $sch_strings);
            }
        }

        // Lọc các giá trị trùng lặp và chuỗi rỗng
        $raw_strings = array_unique(array_filter(array_map('trim', $raw_strings)));

        // Thực hiện dịch
        $translations = array();
        foreach ($raw_strings as $str) {
            $translations[$str] = FL_Translator_Core::translate_string($str, 'en', 'vi', $engine, $api_key);
        }

        // Dựng lại nội dung tiếng Việt
        $translated_title = isset($translations[trim($title)]) ? $translations[trim($title)] : $title;
        $translated_place = isset($translations[trim($place)]) ? $translations[trim($place)] : $place;

        $translated_excerpt = !empty($excerpt) ? FL_Translator_Core::rebuild_content_with_translations($excerpt, $translations) : '';
        $translated_content = !empty($content) ? FL_Translator_Core::rebuild_content_with_translations($content, $translations) : '';

        $translated_schedule = array();
        foreach ($schedule as $row) {
            $row_title = isset($row['title']) ? trim($row['title']) : '';
            $row_content = isset($row['content']) ? trim($row['content']) : '';

            $raw_translated_title = isset($translations[$row_title]) ? $translations[$row_title] : $row_title;
            $raw_translated_content = !empty($row_content) ? FL_Translator_Core::rebuild_content_with_translations($row_content, $translations) : '';

            // Lọc bỏ triệt để HTML tag cho các ô input/textarea thuần túy của Lịch trình
            $clean_translated_title = html_entity_decode(strip_tags($raw_translated_title));
            $clean_translated_content = html_entity_decode(strip_tags($raw_translated_content));

            $translated_schedule[] = array(
                'title'   => trim($clean_translated_title),
                'content' => trim($clean_translated_content)
            );
        }

        wp_send_json_success(array(
            'title'    => $translated_title,
            'place'    => $translated_place,
            'excerpt'  => $translated_excerpt,
            'content'  => $translated_content,
            'schedule' => $translated_schedule
        ));
    }

    /**
     * AJAX lấy danh sách bài viết dựa trên Post Type
     */
    public function ajax_get_posts() {
        $this->verify_request();

        $post_type = sanitize_text_field($_POST['post_type']);
        
        $posts = get_posts(array(
            'post_type'      => $post_type,
            'posts_per_page' => -1,
            'post_status'    => array('publish', 'draft', 'pending', 'private'),
            'orderby'        => 'title',
            'order'          => 'ASC'
        ));

        $list = array();
        foreach ($posts as $p) {
            $list[] = array(
                'id'    => $p->ID,
                'title' => !empty($p->post_title) ? esc_html($p->post_title) : '(Không có tiêu đề - ID: ' . $p->ID . ')'
            );
        }

        wp_send_json_success($list);
    }

    /**
     * AJAX phân tích trang và trích xuất các đoạn text cần dịch
     * Chuyển các key thành dạng Base64 cực kỳ an toàn
     */
    public function ajax_analyze_post() {
        $this->verify_request();

        $post_id = intval($_POST['post_id']);
        $post = get_post($post_id);

        if (!$post) {
            wp_send_json_error(array('message' => 'Không tìm thấy trang/bài viết này.'));
        }

        // Gọi engine trích xuất chuỗi
        $strings = FL_Translator_Core::extract_translatable_strings(
            $post->post_title,
            $post->post_content,
            $post->post_excerpt
        );

        // Trích xuất thêm từ custom fields (ACF, Woocommerce text fields...)
        $meta_strings = FL_Translator_Core::extract_meta_strings($post_id);
        if (!empty($meta_strings)) {
            $strings = array_merge($strings, $meta_strings);
            $strings = array_unique($strings);
            
            // Sắp xếp giảm dần theo chiều dài
            usort($strings, function($a, $b) {
                return strlen($b) - strlen($a);
            });
        }

        $structured_strings = array();
        foreach ($strings as $str) {
            $structured_strings[] = array(
                'key'      => base64_encode($str),
                'original' => $str
            );
        }

        wp_send_json_success(array(
            'title'   => $post->post_title,
            'excerpt' => $post->post_excerpt,
            'strings' => $structured_strings
        ));
    }

    /**
     * AJAX dịch hàng loạt một mảng các chuỗi (Nhận key Base64 và dịch)
     */
    public function ajax_translate_batch() {
        $this->verify_request();

        $batch = isset($_POST['batch']) ? (array) $_POST['batch'] : array();
        $source_lang = isset($_POST['source_lang']) ? sanitize_text_field($_POST['source_lang']) : 'vi';
        $target_lang = isset($_POST['target_lang']) ? sanitize_text_field($_POST['target_lang']) : 'en';

        $engine = get_option('fl_translator_engine', 'google');
        $api_key = get_option('fl_translator_gemini_key', '');

        $translations = array();

        foreach ($batch as $item) {
            $key = sanitize_text_field($item['key']);
            $decoded_original = base64_decode($key);
            
            if ($decoded_original !== false) {
                $cleaned = html_entity_decode(trim($decoded_original), ENT_QUOTES, 'UTF-8');
                $translations[$key] = FL_Translator_Core::translate_string($cleaned, $source_lang, $target_lang, $engine, $api_key);
            }
        }

        wp_send_json_success($translations);
    }

    /**
     * AJAX lưu trữ bản dịch (Tạo trang mới hoặc Ghi đè kèm sao lưu)
     */
    public function ajax_save_translation() {
        $this->verify_request();

        $post_id = intval($_POST['post_id']);
        $save_mode = sanitize_text_field($_POST['save_mode']);
        $translated_title = sanitize_text_field($_POST['translated_title']);
        $source_lang = isset($_POST['source_lang']) ? sanitize_text_field($_POST['source_lang']) : 'vi';
        $target_lang = isset($_POST['target_lang']) ? sanitize_text_field($_POST['target_lang']) : 'en';
        $raw_translations = isset($_POST['translations']) ? (array) $_POST['translations'] : array();

        $post = get_post($post_id);
        if (!$post) {
            wp_send_json_error(array('message' => 'Không tìm thấy trang gốc.'));
        }

        // Giải mã khóa Base64 thành chuỗi gốc để khớp chuẩn xác
        $translations = array();
        foreach ($raw_translations as $base64_key => $trans_val) {
            $orig_val = base64_decode($base64_key);
            if ($orig_val !== false) {
                $translations[trim($orig_val)] = $trans_val;
            }
        }

        // Tái cấu trúc nội dung từ bản dịch của người dùng
        $new_content = FL_Translator_Core::rebuild_content_with_translations($post->post_content, $translations);
        
        // Mô tả ngắn mới nếu có
        $new_excerpt = $post->post_excerpt;
        if (!empty($post->post_excerpt)) {
            $trimmed_excerpt = trim($post->post_excerpt);
            if (isset($translations[$trimmed_excerpt])) {
                $new_excerpt = $translations[$trimmed_excerpt];
            }
        }

        if ($save_mode === 'overwrite') {
            // --- CHẾ ĐỘ GHI ĐÈ TRỰC TIẾP ---
            
            // 1. Sao lưu toàn bộ Custom Fields (Meta Data) gốc trước khi đè
            $meta_backup = array();
            $all_meta = get_post_meta($post_id);
            if (!empty($all_meta)) {
                foreach ($all_meta as $key => $values) {
                    if (strpos($key, '_') !== 0) {
                        $meta_backup[$key] = $values;
                    }
                }
            }

            // 2. Tạo bản sao lưu toàn bộ trước khi ghi đè
            $backups = get_post_meta($post_id, '_fl_translator_backups', true);
            if (!is_array($backups)) {
                $backups = array();
            }

            // Thêm bản backup mới vào đầu danh sách
            array_unshift($backups, array(
                'id'      => time(),
                'date'    => current_time('Y-m-d H:i:s'),
                'title'   => $post->post_title,
                'content' => $post->post_content,
                'excerpt' => $post->post_excerpt,
                'meta'    => $meta_backup // Sao lưu thêm metadata của ACF
            ));

            // Chỉ giữ tối đa 10 bản backup gần nhất
            $backups = array_slice($backups, 0, 10);
            update_post_meta($post_id, '_fl_translator_backups', $backups);

            // 3. Cập nhật bài viết gốc (Title, Content, Excerpt)
            $updated_post = wp_update_post(array(
                'ID'           => $post_id,
                'post_title'   => $translated_title,
                'post_content' => $new_content,
                'post_excerpt' => $new_excerpt
            ), true);

            if (is_wp_error($updated_post)) {
                wp_send_json_error(array('message' => 'Không thể cập nhật bài viết: ' . $updated_post->get_error_message()));
            }

            // 4. Dịch các Custom Fields (ACF, Woocommerce text fields...) gốc
            if (!empty($all_meta)) {
                foreach ($all_meta as $key => $values) {
                    if (strpos($key, '_') !== 0) {
                        foreach ($values as $val) {
                            $trimmed_val = trim($val);
                            if (isset($translations[$trimmed_val])) {
                                update_post_meta($post_id, $key, $translations[$trimmed_val]);
                            }
                        }
                    }
                }
            }

            wp_send_json_success(array(
                'message' => 'Đã dịch xong!',
                'url'     => get_permalink($post_id)
            ));

        } else {
            // --- CHẾ ĐỘ NHÂN BẢN TRANG MỚI (CLONE DÀNH CHO WOOCOMMERCE & FLATSOME) ---
            
            $new_post_args = array(
                'post_title'   => $translated_title,
                'post_content' => $new_content,
                'post_excerpt' => $new_excerpt,
                'post_status'  => 'draft', // Lưu nháp để người dùng kiểm duyệt
                'post_type'    => $post->post_type,
                'post_parent'  => $post->post_parent,
                'menu_order'   => $post->menu_order
            );

            $new_post_id = wp_insert_post($new_post_args);

            if (is_wp_error($new_post_id) || $new_post_id === 0) {
                $err = is_wp_error($new_post_id) ? $new_post_id->get_error_message() : 'Không rõ nguyên nhân';
                wp_send_json_error(array('message' => 'Lỗi khi tạo trang bản sao dịch: ' . $err));
            }

            // 1. Sao chép toàn bộ Meta Data gốc (ACF, Giá sản phẩm, Gallery ảnh, SKU, Cài đặt Flatsome...)
            $meta_keys = get_post_custom_keys($post_id);
            if ($meta_keys) {
                foreach ($meta_keys as $key) {
                    if ($key === '_fl_translator_backups') continue; // Bỏ qua backups
                    $meta_value = get_post_meta($post_id, $key, false);
                    delete_post_meta($new_post_id, $key);
                    foreach ($meta_value as $val) {
                        add_post_meta($new_post_id, $key, $val);
                    }
                }
            }

            // 2. Dịch các Custom Fields (ACF, Lịch trình, Vị trí...) trên sản phẩm/bài viết mới vừa clone
            $new_meta = get_post_meta($new_post_id);
            if (!empty($new_meta)) {
                foreach ($new_meta as $key => $values) {
                    if (strpos($key, '_') !== 0) {
                        foreach ($values as $val) {
                            $trimmed_val = trim($val);
                            if (isset($translations[$trimmed_val])) {
                                update_post_meta($new_post_id, $key, $translations[$trimmed_val]);
                            }
                        }
                    }
                }
            }

            // 3. Sao chép toàn bộ Taxonomies (Danh mục Woo, Thẻ sản phẩm, Thuộc tính biến thể, danh mục bài viết...)
            // Đồng thời tự động dịch và liên kết sang danh mục Tiếng Anh/Việt nếu có Polylang hoặc WPML
            $taxonomies = get_object_taxonomies($post->post_type);
            foreach ($taxonomies as $taxonomy) {
                $terms = wp_get_object_terms($post_id, $taxonomy, array('fields' => 'ids'));
                if (!is_wp_error($terms) && !empty($terms)) {
                    $translated_terms = array();
                    foreach ($terms as $term_id) {
                        $trans_term_id = $term_id;
                        if (function_exists('pll_get_term')) {
                            $pll_term = pll_get_term($term_id, $target_lang);
                            if ($pll_term) {
                                $trans_term_id = $pll_term;
                            }
                        } elseif (function_exists('icl_object_id')) {
                            $wpml_term = icl_object_id($term_id, $taxonomy, false, $target_lang);
                            if ($wpml_term) {
                                $trans_term_id = $wpml_term;
                            }
                        }
                        $translated_terms[] = (int) $trans_term_id;
                    }
                    wp_set_object_terms($new_post_id, $translated_terms, $taxonomy);
                }
            }

            // Hỗ trợ tự liên kết bằng Polylang nếu Polylang được kích hoạt
            if (function_exists('pll_set_post_language') && function_exists('pll_save_post_translations')) {
                $orig_lang = pll_get_post_language($post_id);
                if (!$orig_lang) {
                    pll_set_post_language($post_id, $source_lang);
                    $orig_lang = $source_lang;
                }
                
                pll_set_post_language($new_post_id, $target_lang);

                pll_save_post_translations(array(
                    $source_lang => $post_id,
                    $target_lang => $new_post_id
                ));
            }

            wp_send_json_success(array(
                'message' => 'Đã dịch xong!',
                'url'     => get_edit_post_link($new_post_id, 'raw')
            ));
        }
    }

    /**
     * AJAX lấy danh sách backup của post
     */
    public function ajax_get_backups() {
        $this->verify_request();

        $post_id = intval($_POST['post_id']);
        $backups = get_post_meta($post_id, '_fl_translator_backups', true);

        if (!is_array($backups) || empty($backups)) {
            wp_send_json_success(array());
        }

        $list = array();
        foreach ($backups as $b) {
            $list[] = array(
                'id'   => $b['id'],
                'date' => date_i18n('d/m/Y H:i:s', strtotime($b['date'])),
                'title'=> esc_html($b['title'])
            );
        }

        wp_send_json_success($list);
    }

    /**
     * AJAX phục hồi trang từ bản backup
     */
    public function ajax_restore_backup() {
        $this->verify_request();

        $post_id = intval($_POST['post_id']);
        $backup_id = intval($_POST['backup_id']);

        $backups = get_post_meta($post_id, '_fl_translator_backups', true);
        if (!is_array($backups)) {
            wp_send_json_error(array('message' => 'Không tìm thấy dữ liệu sao lưu cho trang này.'));
        }

        $target_backup = null;
        foreach ($backups as $b) {
            if (intval($b['id']) === $backup_id) {
                $target_backup = $b;
                break;
            }
        }

        if (!$target_backup) {
            wp_send_json_error(array('message' => 'Không tìm thấy bản sao lưu được chọn.'));
        }

        // Cập nhật lại post về nội dung backup
        $restored = wp_update_post(array(
            'ID'           => $post_id,
            'post_title'   => $target_backup['title'],
            'post_content' => $target_backup['content'],
            'post_excerpt' => $target_backup['excerpt']
        ), true);

        if (is_wp_error($restored)) {
            wp_send_json_error(array('message' => 'Lỗi khi khôi phục dữ liệu: ' . $restored->get_error_message()));
        }

        // Khôi phục thêm các custom fields (Meta Data) gốc
        if (isset($target_backup['meta']) && is_array($target_backup['meta'])) {
            foreach ($target_backup['meta'] as $key => $values) {
                delete_post_meta($post_id, $key);
                foreach ($values as $val) {
                    add_post_meta($post_id, $key, $val);
                }
            }
        }

    }

    /**
     * Đăng ký Metabox biên dịch song ngữ cho WooCommerce Product, Tour và Service
     */
    public function add_bilingual_meta_box() {
        $screens = array('product', 'tour', 'service');
        foreach ($screens as $screen) {
            add_meta_box(
                'fl-bilingual-trigger-metabox',
                'Bản dịch Tiếng Việt (Vietnamese Translation)',
                array($this, 'render_bilingual_trigger_metabox'),
                $screen,
                'side',
                'high'
            );
        }
    }

    /**
     * Hiển thị nút dẫn đến trình biên dịch song ngữ trong sidebar sản phẩm
     */
    public function render_bilingual_trigger_metabox($post) {
        $vi_title = get_post_meta($post->ID, '_vi_title', true);
        $vi_excerpt = get_post_meta($post->ID, '_vi_excerpt', true);
        $vi_content = get_post_meta($post->ID, '_vi_content', true);
        $vi_place = get_post_meta($post->ID, '_vi_place', true);
        $vi_schedule = get_post_meta($post->ID, '_vi_schedule', true);

        $has_translation = (!empty($vi_title) || !empty($vi_excerpt) || !empty($vi_content) || !empty($vi_place) || !empty($vi_schedule));
        
        if (isset($_GET['fl_translation_saved'])) : ?>
            <div class="notice notice-success is-dismissible" style="margin: 0 0 15px 0; padding: 8px 12px; border-left-color: #16a34a; background: #f0fdf4; border-radius: 4px; border-left-width: 4px; border-left-style: solid;">
                <p style="margin: 0; color: #166534; font-size: 13px;"><strong>Thành công!</strong> Đã lưu bản dịch Tiếng Việt.</p>
            </div>
        <?php endif; ?>

        <div class="fl-translation-trigger-wrapper" style="padding: 5px 0;">
            <!-- <p style="margin-top: 0; font-size: 13px; line-height: 1.5; color: #64748b;">
                Sản phẩm này có ngôn ngữ gốc là <strong>Tiếng Anh</strong>. Bạn có thể biên dịch thủ công chất lượng cao sang Tiếng Việt để hiển thị ở frontend.
            </p> -->
            
            <div class="status-indicator" style="margin-bottom: 15px; display: flex; align-items: center; gap: 8px;">
                <span style="width: 10px; height: 10px; border-radius: 50%; background-color: <?php echo $has_translation ? '#16a34a' : '#f59e0b'; ?>; display: inline-block;"></span>
                <span style="font-weight: 600; font-size: 12px; color: <?php echo $has_translation ? '#166534' : '#b45309'; ?>;">
                    <?php echo $has_translation ? 'Đã có bản dịch Tiếng Việt' : 'Chưa có bản dịch Tiếng Việt'; ?>
                </span>
            </div>

            <div style="display: flex; flex-direction: column; gap: 8px;">
                <a href="<?php echo esc_url(admin_url('admin.php?page=fl-product-translator&post_id=' . $post->ID . '&mode=manual')); ?>" 
                   class="button button-secondary button-large" 
                   style="width: 100%; text-align: center; display: inline-flex !important; align-items: center !important; justify-content: center !important; gap: 8px; font-weight: 600; font-family: 'Outfit', sans-serif; border-radius: 4px; padding: 4px 10px; height: auto; min-height: 38px; line-height: 1 !important; vertical-align: middle;">
                    <span class="dashicons dashicons-edit" style="margin: 0; display: inline-flex; align-items: center; justify-content: center; height: 20px; width: 20px; font-size: 18px;"></span>
                    <span style="display: inline-block; line-height: 1;"><?php echo $has_translation ? 'Dịch thủ công (Sửa TV)' : 'Dịch thủ công (Tự viết)'; ?></span>
                </a>
                
                <a href="<?php echo esc_url(admin_url('admin.php?page=fl-product-translator&post_id=' . $post->ID . '&mode=auto')); ?>" 
                   class="button button-primary button-large" 
                   style="width: 100%; text-align: center; display: inline-flex !important; align-items: center !important; justify-content: center !important; gap: 8px; font-weight: 600; font-family: 'Outfit', sans-serif; background: #16a34a; border-color: #16a34a; box-shadow: none; border-radius: 4px; padding: 4px 10px; height: auto; min-height: 38px; line-height: 1 !important; vertical-align: middle;">
                    <span class="dashicons dashicons-translation" style="margin: 0; display: inline-flex; align-items: center; justify-content: center; height: 20px; width: 20px; font-size: 20px;"></span>
                    <span style="display: inline-block; line-height: 1;">Dịch tự động AI</span>
                </a>
            </div>
        </div>
        <?php
    }

    /**
     * Hiển thị giao diện Không gian biên dịch song ngữ chuyên dụng toàn màn hình
     */
    public function render_product_translator_page() {
        if (!isset($_GET['post_id'])) {
            wp_die('Không tìm thấy ID bài viết.');
        }
        $post_id = intval($_GET['post_id']);
        if (!current_user_can('edit_post', $post_id)) {
            wp_die('Bạn không có quyền chỉnh sửa bài viết này.');
        }

        $post = get_post($post_id);
        if (!$post) {
            wp_die('Bài viết không tồn tại.');
        }

        // Xử lý lưu dữ liệu khi form được submit
        if (isset($_POST['action']) && $_POST['action'] === 'save_product_translation') {
            check_admin_referer('fl_save_product_translation_nonce', 'fl_translation_security');

            if (isset($_POST['vi_title'])) {
                update_post_meta($post_id, '_vi_title', sanitize_text_field($_POST['vi_title']));
            }
            if (isset($_POST['vi_content'])) {
                update_post_meta($post_id, '_vi_content', wp_kses_post($_POST['vi_content']));
            }
            if (isset($_POST['vi_excerpt'])) {
                update_post_meta($post_id, '_vi_excerpt', wp_kses_post($_POST['vi_excerpt']));
            }
            if (isset($_POST['vi_place'])) {
                update_post_meta($post_id, '_vi_place', sanitize_text_field($_POST['vi_place']));
            }
            if (isset($_POST['vi_schedule']) && is_array($_POST['vi_schedule'])) {
                $vi_schedule = array();
                foreach ($_POST['vi_schedule'] as $index => $row) {
                    if (!empty($row['title']) || !empty($row['content'])) {
                        $vi_schedule[] = array(
                            'title'   => sanitize_text_field($row['title']),
                            'content' => wp_kses_post($row['content'])
                        );
                    }
                }
                update_post_meta($post_id, '_vi_schedule', $vi_schedule);
            }

            // Chuyển hướng trở lại trang sửa sản phẩm với cờ thông báo thành công
            wp_redirect(admin_url('post.php?post=' . $post_id . '&action=edit&fl_translation_saved=1'));
            exit;
        }

        // Lấy dữ liệu Tiếng Anh gốc
        $orig_title = $post->post_title;
        $orig_excerpt = $post->post_excerpt;
        $orig_content = $post->post_content;
        $orig_place = get_field('place', $post_id);
        $orig_schedule = get_field('schedule', $post_id);

        // Lấy bản dịch Tiếng Việt
        $vi_title = get_post_meta($post_id, '_vi_title', true);
        $vi_excerpt = get_post_meta($post_id, '_vi_excerpt', true);
        $vi_content = get_post_meta($post_id, '_vi_content', true);
        $vi_place = get_post_meta($post_id, '_vi_place', true);
        $vi_schedule = get_post_meta($post_id, '_vi_schedule', true);
        if (!is_array($vi_schedule)) {
            $vi_schedule = array();
        }

        $current_engine = get_option('fl_translator_engine', 'google');
        $current_gemini_key = get_option('fl_translator_gemini_key', '');
        $has_gemini_key = !empty($current_gemini_key);
        $selected_mode = isset($_GET['mode']) ? sanitize_text_field($_GET['mode']) : 'manual';
        ?>
        <div class="wrap fl-workspace-wrapper">
            <form method="post" action="">
                <?php wp_nonce_field('fl_save_product_translation_nonce', 'fl_translation_security'); ?>
                <input type="hidden" name="action" value="save_product_translation">

                <header class="workspace-header">
                    <div class="header-brand">
                        <span class="dashicons dashicons-translation brand-icon"></span>
                        <div>
                            <h2>Không gian biên dịch Tiếng Việt chuyên dụng</h2>
                            <p class="subtitle">Bản dịch Tiếng Việt thủ công chất lượng cao cho sản phẩm: <strong><?php echo esc_html($orig_title); ?></strong></p>
                        </div>
                    </div>
                    <div class="header-actions">
                        <a href="<?php echo esc_url(admin_url('post.php?post=' . $post_id . '&action=edit')); ?>" class="fl-btn-secondary">
                            <span class="dashicons dashicons-arrow-left-alt"></span> Quay lại
                        </a>
                        <button type="submit" class="fl-btn-primary">
                            <span class="dashicons dashicons-cloud-saved"></span> Lưu bản dịch
                        </button>
                    </div>
                </header>

                <div class="workspace-mode-selector">
                    <span class="mode-label">
                        <span class="dashicons dashicons-randomize" style="margin-right: 6px; font-size: 16px; width: 16px; height: 16px;"></span>
                        Chế độ dịch:
                    </span>
                    <label class="mode-option <?php echo $selected_mode === 'manual' ? 'active-manual' : ''; ?>">
                        <input type="radio" name="workspace-mode" value="manual" <?php checked($selected_mode, 'manual'); ?>>
                        <span class="dashicons dashicons-edit" style="font-size: 16px; width: 16px; height: 16px;"></span>
                        Dịch thủ công (Tự biên tập)
                    </label>
                    <label class="mode-option <?php echo $selected_mode === 'auto' ? 'active-auto' : ''; ?>">
                        <input type="radio" name="workspace-mode" value="auto" <?php checked($selected_mode, 'auto'); ?>>
                        <span class="dashicons dashicons-translation" style="font-size: 16px; width: 16px; height: 16px;"></span>
                        Dịch tự động AI
                    </label>
                </div>

                <div class="workspace-translate-bar" style="<?php echo $selected_mode === 'manual' ? 'display: none;' : ''; ?>">
                    <div class="translate-bar-info">
                        <span class="dashicons dashicons-admin-site bar-icon"></span>
                        <div>
                            <strong>Dịch tự động AI (Auto Translate)</strong>
                            <p>Hệ thống tự động dịch nhanh các trường từ bản gốc Tiếng Anh sang Tiếng Việt. Bạn có thể chỉnh sửa lại sau đó.</p>
                        </div>
                    </div>
                    <div class="translate-bar-actions">
                        <div class="engine-selector-wrapper">
                            <select id="workspace-engine-select" class="fl-select-inline">
                                <option value="google" <?php selected($current_engine, 'google'); ?>>Google Translate (Miễn phí)</option>
                                <option value="gemini" <?php selected($current_engine, 'gemini'); ?>>
                                    Google Gemini AI (Premium)<?php echo !$has_gemini_key ? ' (Chưa cấu hình API Key ⚠️)' : ''; ?>
                                </option>
                            </select>
                        </div>
                        <button type="button" id="btn-workspace-auto-translate" class="fl-btn-translate">
                            <span class="dashicons dashicons-controls-play"></span> Dịch tự động toàn bộ
                        </button>
                    </div>
                </div>

                <div class="workspace-layout">
                    <!-- CỘT TRÁI - BẢN GỐC TIẾNG ANH (READ-ONLY) -->
                    <div class="workspace-col original-col">
                        <div class="col-header">
                            <span class="lang-badge en">EN</span> Bản gốc Tiếng Anh
                        </div>

                        <!-- Tiêu đề gốc -->
                        <div class="field-group">
                            <label>Tiêu đề sản phẩm</label>
                            <div class="readonly-box"><?php echo esc_html($orig_title); ?></div>
                        </div>

                        <!-- Mô tả ngắn gốc -->
                        <div class="field-group">
                            <label>Mô tả ngắn</label>
                            <div class="readonly-box rich-text"><?php 
                                $clean_excerpt = preg_replace('/\[[^\]]+\]/', '', strip_tags($orig_excerpt));
                                echo wpautop(esc_html(trim($clean_excerpt))); 
                            ?></div>
                        </div>

                        <!-- Mô tả chi tiết gốc -->
                        <div class="field-group">
                            <label>Mô tả chi tiết</label>
                            <div class="readonly-box rich-text-large"><?php 
                                $clean_content = preg_replace('/\[[^\]]+\]/', '', strip_tags($orig_content));
                                echo wpautop(esc_html(trim($clean_content))); 
                            ?></div>
                        </div>

                        <!-- Nơi khởi hành gốc -->
                        <div class="field-group">
                            <label>Nơi khởi hành (Location)</label>
                            <div class="readonly-box"><?php echo esc_html($orig_place); ?></div>
                        </div>

                        <!-- Lịch trình gốc -->
                        <div class="field-group">
                            <label>Lịch trình chi tiết (Itinerary / Schedule)</label>
                            <div class="readonly-schedule-repeater">
                                <?php if (!empty($orig_schedule) && is_array($orig_schedule)) : ?>
                                    <?php foreach ($orig_schedule as $i => $row) : $index = $i + 1; ?>
                                        <div class="readonly-schedule-row">
                                            <span class="row-badge">Ngày <?php echo $index; ?></span>
                                            <strong><?php echo esc_html($row['title']); ?></strong>
                                            <p><?php 
                                                $clean_sch_content = preg_replace('/\[[^\]]+\]/', '', strip_tags($row['content']));
                                                echo wpautop(esc_html(trim($clean_sch_content))); 
                                            ?></p>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else : ?>
                                    <p class="no-data">Không có lịch trình Tiếng Anh.</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- CỘT PHẢI - BẢN DỊCH TIẾNG VIỆT (EDITABLE) -->
                    <div class="workspace-col translation-col">
                        <div class="col-header vi">
                            <span class="lang-badge vi">VI</span> Bản dịch Tiếng Việt
                        </div>

                        <!-- Tiêu đề dịch -->
                        <div class="field-group">
                            <label for="vi-title">Tiêu đề sản phẩm (Tiếng Việt)</label>
                            <input type="text" id="vi-title" name="vi_title" class="input-text" value="<?php echo esc_attr($vi_title); ?>" placeholder="Nhập tiêu đề Tiếng Việt...">
                        </div>

                        <!-- Mô tả ngắn dịch -->
                        <div class="field-group">
                            <label>Mô tả ngắn (Tiếng Việt)</label>
                            <?php 
                            wp_editor($vi_excerpt, 'viexcerpt', array(
                                'textarea_name' => 'vi_excerpt',
                                'textarea_rows' => 6,
                                'media_buttons' => false,
                                'tinymce' => true,
                                'quicktags' => true
                            )); 
                            ?>
                        </div>

                        <!-- Mô tả chi tiết dịch -->
                        <div class="field-group">
                            <label>Mô tả chi tiết (Tiếng Việt)</label>
                            <?php 
                            wp_editor($vi_content, 'vicontent', array(
                                'textarea_name' => 'vi_content',
                                'textarea_rows' => 15,
                                'media_buttons' => true,
                                'tinymce' => true,
                                'quicktags' => true
                            )); 
                            ?>
                        </div>

                        <!-- Nơi khởi hành dịch -->
                        <div class="field-group">
                            <label for="vi-place">Nơi khởi hành (Tiếng Việt)</label>
                            <input type="text" id="vi-place" name="vi_place" class="input-text" value="<?php echo esc_attr($vi_place); ?>" placeholder="Nhập nơi khởi hành Tiếng Việt...">
                        </div>

                        <!-- Lịch trình dịch -->
                        <div class="field-group">
                            <label>Lịch trình chi tiết (Tiếng Việt)</label>
                            <div class="editable-schedule-repeater">
                                <div id="vi-schedule-rows-container">
                                    <?php 
                                    $en_count = !empty($orig_schedule) ? count($orig_schedule) : 0;
                                    for ($i = 0; $i < $en_count; $i++) : 
                                        $index = $i + 1;
                                        $row_title = isset($vi_schedule[$i]['title']) ? html_entity_decode(strip_tags($vi_schedule[$i]['title'])) : '';
                                        $row_content = isset($vi_schedule[$i]['content']) ? html_entity_decode(strip_tags($vi_schedule[$i]['content'])) : '';
                                        ?>
                                        <div class="editable-schedule-row" data-index="<?php echo $i; ?>">
                                            <span class="row-badge">Ngày <?php echo $index; ?></span>
                                            <div class="inner-field">
                                                <label>Tiêu đề chặng <?php echo $index; ?></label>
                                                <input type="text" name="vi_schedule[<?php echo $i; ?>][title]" class="input-text" value="<?php echo esc_attr($row_title); ?>" placeholder="Nhập tiêu đề chặng Tiếng Việt...">
                                            </div>
                                            <div class="inner-field">
                                                <label>Nội dung chặng <?php echo $index; ?></label>
                                                <textarea name="vi_schedule[<?php echo $i; ?>][content]" rows="3" class="input-text" placeholder="Nhập chi tiết lịch trình Tiếng Việt..."><?php echo esc_textarea($row_content); ?></textarea>
                                            </div>
                                        </div>
                                    <?php endfor; ?>
                                    <?php if ($en_count === 0) : ?>
                                        <p class="no-data">Không có chặng lịch trình Tiếng Anh để biên dịch.</p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
        <?php
    }
}
