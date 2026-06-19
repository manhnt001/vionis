<?php
/*
Plugin Name: Tour Search Filter
Description: A custom filter for searching tours with interactive 'Where' and 'When' selectors.
Version: 1.0.0
Author: MaiATech
*/

// Enqueue styles and scripts
function tsf_enqueue_assets() {
    wp_enqueue_style('tsf-style', plugin_dir_url(__FILE__) . 'assets/css/style.css', array(), '1.0.0');
    wp_enqueue_script('tsf-script', plugin_dir_url(__FILE__) . 'assets/js/script.js', array('jquery'), '1.0.0', true);
}
add_action('wp_enqueue_scripts', 'tsf_enqueue_assets');

// Tour Search Filter (Where & When) Shortcode
function tour_search_filter_shortcode() {
    ob_start();
    
    // Fetch destinations from taxonomy product_end
    $destinations = get_terms(array(
        'taxonomy' => 'product_end',
        'hide_empty' => true,
    ));
    ?>
    <form action="<?php echo esc_url(home_url('/')); ?>tim-kiem-tour" method="GET" id="tsf-form">
        <!-- Hidden inputs for actual search parameters -->
        <input type="hidden" name="noi-den" id="tsf-noiden" value="<?php echo esc_attr($_GET['noi-den'] ?? ''); ?>">
        <input type="hidden" name="ngay-khoi-hanh" id="tsf-ngaykhoihanh" value="<?php echo esc_attr($_GET['ngay-khoi-hanh'] ?? ''); ?>">
        <input type="hidden" name="ngay-ket-thuc" id="tsf-ngayketthuc" value="<?php echo esc_attr($_GET['ngay-ket-thuc'] ?? ''); ?>">

        <div class="tsf-container">
            <!-- Where Field -->
            <div class="tsf-field tsf-where-field" id="tsf-where-trigger">
                <span class="tsf-field-icon">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
                </span>
                <div class="tsf-field-content">
                    <span class="tsf-field-label" id="tsf-where-display-text">
                        <?php 
                        $selected_slug = $_GET['noi-den'] ?? '';
                        $selected_name = '';
                        if ($selected_slug && !empty($destinations) && !is_wp_error($destinations)) {
                            foreach ($destinations as $dest) {
                                if ($dest->slug === $selected_slug) {
                                    $selected_name = $dest->name;
                                    break;
                                }
                            }
                        }
                        // Dictionary for common Vietnamese cities
                        $city_dictionary = array(
                            'Da Lat' => 'Đà Lạt',
                            'Ha Long' => 'Hạ Long',
                            'Ha Noi' => 'Hà Nội',
                            'Nha Trang' => 'Nha Trang',
                            'Ninh Binh' => 'Ninh Bình',
                            'Phu Quoc' => 'Phú Quốc',
                            'Da Nang' => 'Đà Nẵng',
                            'Hoi An' => 'Hội An',
                            'Sapa' => 'Sa Pa',
                            'Ho Chi Minh' => 'Hồ Chí Minh',
                            'Hue' => 'Huế'
                        );

                        $vi_selected_name = isset($city_dictionary[$selected_name]) ? $city_dictionary[$selected_name] : $selected_name;
                        
                        echo $selected_name ? '<span class="notranslate"><span class="menu-lang-en">' . esc_html($selected_name) . '</span><span class="menu-lang-vi">' . esc_html($vi_selected_name) . '</span></span>' : '<span class="notranslate"><span class="menu-lang-en">Where do you want to go?</span><span class="menu-lang-vi">Bạn muốn đi đâu?</span></span>';
                        ?>
                    </span>
                </div>
                <!-- Custom Dropdown Menu for Where -->
                <div class="tsf-dropdown tsf-where-dropdown" id="tsf-where-dropdown-menu">
                    <ul>
                        <li data-slug="" class="<?php echo empty($selected_slug) ? 'active' : ''; ?>"><span class="notranslate"><span class="menu-lang-en">All Destinations</span><span class="menu-lang-vi">Tất cả điểm đến</span></span></li>
                        <?php if (!empty($destinations) && !is_wp_error($destinations)) : ?>
                            <?php foreach ($destinations as $dest) : ?>
                                <li data-slug="<?php echo esc_attr($dest->slug); ?>" class="<?php echo $selected_slug === $dest->slug ? 'active' : ''; ?>">
                                    <?php 
                                    $vi_name = isset($city_dictionary[$dest->name]) ? $city_dictionary[$dest->name] : $dest->name;
                                    ?>
                                    <span class="notranslate"><span class="menu-lang-en"><?php echo esc_html($dest->name); ?></span><span class="menu-lang-vi"><?php echo esc_html($vi_name); ?></span></span>
                                </li>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>

            <div class="tsf-separator"></div>

            <!-- When Field -->
            <div class="tsf-field tsf-when-field" id="tsf-when-trigger">
                <span class="tsf-field-icon">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                </span>
                <div class="tsf-field-content">
                    <span class="tsf-field-label" id="tsf-when-display-text">
                        <?php 
                        $start_date = $_GET['ngay-khoi-hanh'] ?? '';
                        $end_date = $_GET['ngay-ket-thuc'] ?? '';
                        if ($start_date && $end_date) {
                            echo esc_html($start_date . ' - ' . $end_date);
                        } elseif ($start_date) {
                            echo esc_html($start_date);
                        } else {
                            echo '<span class="notranslate"><span class="menu-lang-en">When?</span><span class="menu-lang-vi">Khi nào?</span></span>';
                        }
                        ?>
                    </span>
                </div>
                <!-- Custom Calendar Dropdown -->
                <div class="tsf-dropdown tsf-calendar-dropdown" id="tsf-calendar-dropdown-menu">
                    <div class="tsf-calendar-inputs">
                        <div class="tsf-cal-input-box">
                            <label><span class="notranslate"><span class="menu-lang-en">Starting on</span><span class="menu-lang-vi">Bắt đầu từ</span></span></label>
                            <input type="text" id="tsf-start-input-display" value="<?php echo esc_attr($start_date); ?>">
                        </div>
                        <div class="tsf-cal-input-box">
                            <label><span class="notranslate"><span class="menu-lang-en">Ending on</span><span class="menu-lang-vi">Kết thúc lúc</span></span></label>
                            <input type="text" id="tsf-end-input-display" value="<?php echo esc_attr($end_date); ?>">
                        </div>
                    </div>
                    
                    <div class="tsf-calendar-years" id="tsf-years-container">
                        <button type="button" class="tsf-year-tab active" data-year="2026">2026</button>
                        <button type="button" class="tsf-year-tab" data-year="2027">2027</button>
                        <button type="button" class="tsf-year-tab" data-year="2028">2028</button>
                    </div>

                    <div class="tsf-calendar-month-selector">
                        <button type="button" class="tsf-month-nav prev" id="tsf-month-prev">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"></polyline></svg>
                        </button>
                        <div class="tsf-month-title-wrap">
                            <span id="tsf-month-year-title"><span class="notranslate"><span class="menu-lang-en">June</span><span class="menu-lang-vi">Tháng 6</span></span></span>
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="6 9 12 15 18 9"></polyline></svg>
                        </div>
                        <button type="button" class="tsf-month-nav next" id="tsf-month-next">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"></polyline></svg>
                        </button>
                    </div>

                    <div class="tsf-calendar-grid">
                        <div class="tsf-weekdays">
                            <span class="notranslate"><span class="menu-lang-en">Su</span><span class="menu-lang-vi">CN</span></span><span class="notranslate"><span class="menu-lang-en">Mo</span><span class="menu-lang-vi">T2</span></span><span class="notranslate"><span class="menu-lang-en">Tu</span><span class="menu-lang-vi">T3</span></span><span class="notranslate"><span class="menu-lang-en">We</span><span class="menu-lang-vi">T4</span></span><span class="notranslate"><span class="menu-lang-en">Th</span><span class="menu-lang-vi">T5</span></span><span class="notranslate"><span class="menu-lang-en">Fr</span><span class="menu-lang-vi">T6</span></span><span class="notranslate"><span class="menu-lang-en">Sa</span><span class="menu-lang-vi">T7</span></span>
                        </div>
                        <div class="tsf-days" id="tsf-days-grid">
                            <!-- Rendered dynamically by JS -->
                        </div>
                    </div>
                    <div class="tsf-calendar-footer" style="text-align: right; margin-top: 12px;">
                        <button type="button" id="tsf-clear-dates" style="background: none; border: none; color: #ef4444; font-weight: 600; cursor: pointer; font-size: 14px; padding: 4px 8px; border-radius: 4px; transition: background 0.2s;"><span class="notranslate"><span class="menu-lang-en">Clear Dates</span><span class="menu-lang-vi">Xóa ngày</span></span></button>
                    </div>
                </div>
            </div>

            <!-- Submit button -->
            <button type="submit" class="tsf-submit-button" aria-label="Search">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
            </button>
        </div>
    </form>
    <?php
    $output = ob_get_clean();
    return $output;
}
add_shortcode('tour_search_filter', 'tour_search_filter_shortcode');
