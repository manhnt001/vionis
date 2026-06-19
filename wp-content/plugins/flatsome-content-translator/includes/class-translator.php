<?php
if (!defined('ABSPATH')) {
    exit;
}

class FL_Translator_Core {

    /**
     * Danh sách các thuộc tính shortcode Flatsome chứa văn bản cần dịch.
     */
    private static $translatable_attributes = array(
        'text',
        'title',
        'label',
        'placeholder',
        'alt',
        'headline',
        'sub_title',
        'btn_text',
        'button_text',
        'tooltip',
        'name',
        'phone',
        'address'
    );

    /**
     * Hàm dịch chung điều hướng theo Engine lựa chọn (Google hoặc Gemini)
     */
    public static function translate_string($text, $from = 'vi', $to = 'en', $engine = 'google', $api_key = '') {
        if ($engine === 'gemini' && !empty($api_key)) {
            return self::translate_string_gemini($text, $api_key, $from, $to);
        }
        return self::translate_string_google($text, $from, $to);
    }

    /**
     * Dịch một chuỗi văn bản sử dụng Google Translate API miễn phí.
     */
    public static function translate_string_google($text, $from = 'vi', $to = 'en') {
        $text = trim($text);
        if (empty($text)) {
            return '';
        }

        // Bỏ qua nếu là số hoặc ký tự không có chữ cái
        if (is_numeric($text) || !preg_match('/[\p{L}]/u', $text)) {
            return $text;
        }

        // Gọi Google Translate API
        $url = "https://translate.googleapis.com/translate_a/single?client=gtx&sl=" . urlencode($from) . "&tl=" . urlencode($to) . "&dt=t&ie=UTF-8&oe=UTF-8&q=" . urlencode($text);

        $response = wp_remote_get($url, array(
            'timeout'     => 15,
            'user-agent'  => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36'
        ));

        if (is_wp_error($response)) {
            return $text; // Trả về chuỗi gốc nếu lỗi mạng
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        if (empty($data) || !isset($data[0])) {
            return $text;
        }

        $translated = '';
        foreach ($data[0] as $segment) {
            if (isset($segment[0])) {
                $translated .= $segment[0];
            }
        }

        return $translated ? $translated : $text;
    }

    /**
     * Dịch chất lượng cao sử dụng Google Gemini API
     */
    public static function translate_string_gemini($text, $api_key, $from = 'vi', $to = 'en') {
        $text = trim($text);
        if (empty($text)) {
            return '';
        }

        // Bỏ qua nếu là số hoặc không có chữ
        if (is_numeric($text) || !preg_match('/[\p{L}]/u', $text)) {
            return $text;
        }

        $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key=" . urlencode($api_key);

        $langs = array(
            'vi' => 'Vietnamese',
            'en' => 'English'
        );
        $from_name = isset($langs[$from]) ? $langs[$from] : $from;
        $to_name = isset($langs[$to]) ? $langs[$to] : $to;

        $prompt = "Translate the following text from {$from_name} to {$to_name}. Return ONLY the exact translation, without quotes, explanations, intro or additional text. Preserve original structure, formatting, case, and punctuation:\n\n" . $text;

        $payload = array(
            'contents' => array(
                array(
                    'parts' => array(
                        array('text' => $prompt)
                    )
                )
            )
        );

        $response = wp_remote_post($url, array(
            'timeout'     => 20,
            'headers'     => array('Content-Type' => 'application/json'),
            'body'        => json_encode($payload)
        ));

        if (is_wp_error($response)) {
            return self::translate_string_google($text, $from, $to); // Fallback nếu lỗi kết nối
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        if (!empty($data) && isset($data['candidates'][0]['content']['parts'][0]['text'])) {
            $translated = trim($data['candidates'][0]['content']['parts'][0]['text']);
            return $translated ? $translated : $text;
        }

        return self::translate_string_google($text, $from, $to); // Fallback
    }

    /**
     * Kiểm tra tính hợp lệ của Gemini API Key
     */
    public static function check_gemini_key($api_key) {
        $api_key = trim($api_key);
        if (empty($api_key)) {
            return false;
        }

        $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key=" . urlencode($api_key);
        
        $payload = array(
            'contents' => array(
                array(
                    'parts' => array(
                        array('text' => 'Respond with exactly the word "OK".')
                    )
                )
            )
        );

        $response = wp_remote_post($url, array(
            'timeout'     => 10,
            'headers'     => array('Content-Type' => 'application/json'),
            'body'        => json_encode($payload)
        ));

        if (is_wp_error($response)) {
            return false;
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        if (!empty($data) && isset($data['candidates'][0]['content']['parts'][0]['text'])) {
            return true;
        }

        return false;
    }

    /**
     * Trích xuất các chuỗi văn bản từ các trường tùy biến (ACF, WooCommerce, Repeaters...)
     */
    public static function extract_meta_strings($post_id) {
        $strings = array();
        $meta = get_post_meta($post_id);
        if (empty($meta)) {
            return $strings;
        }

        foreach ($meta as $key => $values) {
            // Bỏ qua các key hệ thống hoặc khóa ACF bắt đầu bằng _
            if (strpos($key, '_') === 0) {
                continue;
            }

            foreach ($values as $val) {
                // Bỏ qua nếu rỗng, là số, là mảng/đối tượng đã serialize hoặc JSON
                if (empty(trim($val)) || is_numeric($val) || is_serialized($val)) {
                    continue;
                }

                // Bỏ qua các chuỗi JSON cấu trúc phức tạp
                if (strpos($val, '{') === 0 && strpos($val, '}') !== false) {
                    continue;
                }

                // Bỏ qua các chuỗi chứa mã nhúng iframe (bản đồ, video...)
                if (stripos($val, '<iframe') !== false) {
                    continue;
                }

                $cleaned = html_entity_decode(trim($val), ENT_QUOTES, 'UTF-8');
                if (!empty($cleaned) && preg_match('/[\p{L}]/u', $cleaned)) {
                    $strings[] = $cleaned;
                }
            }
        }

        return array_unique($strings);
    }

    /**
     * Trích xuất các chuỗi cần dịch từ Tiêu đề, Nội dung (Shortcode Flatsome) và Mô tả ngắn.
     */
    public static function extract_translatable_strings($title, $content, $excerpt = '') {
        $strings = array();

        // 1. Trích xuất Tiêu đề
        if (!empty(trim($title))) {
            $strings[] = trim($title);
        }

        // 2. Trích xuất Mô tả ngắn
        if (!empty(trim($excerpt))) {
            $strings[] = trim($excerpt);
        }

        // 3. Phân tích nội dung (Shortcodes)
        if (!empty($content)) {
            // Tách shortcode và văn bản nằm ngoài shortcode
            $parts = preg_split('/(\[\/?[a-zA-Z0-9_\-]+(?:\s+[a-zA-Z0-9_\-]+(?:\s*=\s*(?:"[^"]*"|\'[^\']*\'|[^\s\]]+))?)*\s*\])/', $content, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);

            foreach ($parts as $part) {
                if (empty(trim($part))) {
                    continue;
                }

                if (self::is_shortcode($part)) {
                    // Nếu là thẻ shortcode, trích xuất các thuộc tính chứa văn bản cần dịch
                    $attrs = self::extract_shortcode_attributes($part);
                    foreach ($attrs as $attr_val) {
                        if (!empty(trim($attr_val)) && preg_match('/[\p{L}]/u', $attr_val)) {
                            $strings[] = trim($attr_val);
                        }
                    }
                } else {
                    // Nếu là đoạn văn bản/HTML, phân tích sâu để chỉ lấy văn bản bên trong thẻ HTML
                    $html_parts = preg_split('/(<[^>]+>)/', $part, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
                    foreach ($html_parts as $html_part) {
                        if (empty(trim($html_part))) {
                            continue;
                        }
                        if (!self::is_html_tag($html_part)) {
                            // Là đoạn văn bản thuần tuý
                            $clean_text = html_entity_decode(trim($html_part), ENT_QUOTES, 'UTF-8');
                            if (!empty($clean_text) && preg_match('/[\p{L}]/u', $clean_text)) {
                                $strings[] = $clean_text;
                            }
                        }
                    }
                }
            }
        }

        // Lọc các giá trị trùng, loại bỏ iframe và sắp xếp theo chiều dài giảm dần
        $strings = array_unique($strings);
        $strings = array_filter($strings, function($str) {
            return stripos($str, '<iframe') === false;
        });
        usort($strings, function($a, $b) {
            return strlen($b) - strlen($a);
        });

        return array_values($strings);
    }

    /**
     * Dựng lại nội dung Flatsome bằng cách thay thế các chuỗi gốc thành bản dịch.
     * Cực kỳ an toàn: Chỉ thực hiện khớp chính xác (Exact match) thay vì replace chuỗi con bừa bãi.
     */
    public static function rebuild_content_with_translations($content, $translations) {
        if (empty($content) || empty($translations)) {
            return $content;
        }

        // Tách shortcode và văn bản nằm ngoài shortcode
        $parts = preg_split('/(\[\/?[a-zA-Z0-9_\-]+(?:\s+[a-zA-Z0-9_\-]+(?:\s*=\s*(?:"[^"]*"|\'[^\']*\'|[^\s\]]+))?)*\s*\])/', $content, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);

        $new_parts = array();

        foreach ($parts as $part) {
            if (self::is_shortcode($part)) {
                // Thay thế thuộc tính của shortcode
                $new_parts[] = self::replace_shortcode_attributes($part, $translations);
            } else {
                // Thay thế văn bản thuần tuý nằm ngoài/trong HTML tags
                $html_parts = preg_split('/(<[^>]+>)/', $part, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
                $new_html_parts = array();

                foreach ($html_parts as $html_part) {
                    if (self::is_html_tag($html_part)) {
                        $new_html_parts[] = $html_part;
                    } else {
                        $clean_text = html_entity_decode($html_part, ENT_QUOTES, 'UTF-8');
                        $trimmed = trim($clean_text);
                        
                        if (isset($translations[$trimmed])) {
                            // Thay thế đúng bản dịch khớp chính xác
                            $translated_text = $translations[$trimmed];
                            // Giữ lại khoảng trống nguyên bản ở hai đầu
                            $left_space = strlen($html_part) - strlen(ltrim($html_part));
                            $right_space = strlen($html_part) - strlen(rtrim($html_part));
                            
                            $new_html_parts[] = str_repeat(' ', $left_space) . esc_html($translated_text) . str_repeat(' ', $right_space);
                        } else {
                            // Không khớp thì giữ nguyên bản gốc để tránh lỗi biên tập
                            $new_html_parts[] = $html_part;
                        }
                    }
                }
                $new_parts[] = implode('', $new_html_parts);
            }
        }

        return implode('', $new_parts);
    }

    /**
     * Trích xuất các thuộc tính văn bản từ một thẻ Shortcode.
     */
    private static function extract_shortcode_attributes($shortcode_tag) {
        $attrs = array();
        
        // Regex tìm các thuộc tính dạng key="value" hoặc key='value'
        $pattern = '/(' . implode('|', self::$translatable_attributes) . ')\s*=\s*(?:"([^"]*)"|\'([^\']*)\')/i';
        if (preg_match_all($pattern, $shortcode_tag, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                // Giá trị nằm ở index 2 (ngoặc kép) hoặc index 3 (ngoặc đơn)
                $val = !empty($match[2]) ? $match[2] : (!empty($match[3]) ? $match[3] : '');
                if (!empty($val)) {
                    $attrs[] = $val;
                }
            }
        }

        return $attrs;
    }

    /**
     * Thay thế giá trị của thuộc tính shortcode bằng bản dịch tương ứng.
     * Cực kỳ an toàn: Khớp chính xác hoàn toàn.
     */
    private static function replace_shortcode_attributes($shortcode_tag, $translations) {
        $pattern = '/(' . implode('|', self::$translatable_attributes) . ')\s*=\s*(?:"([^"]*)"|\'([^\']*)\')/i';
        
        return preg_replace_callback($pattern, function($matches) use ($translations) {
            $key = $matches[1];
            $quote = !empty($matches[2]) || $matches[2] === '' ? '"' : "'";
            $val = !empty($matches[2]) ? $matches[2] : (!empty($matches[3]) ? $matches[3] : '');
            
            $trimmed = trim($val);
            if (isset($translations[$trimmed])) {
                return $key . '=' . $quote . esc_attr($translations[$trimmed]) . $quote;
            } else {
                // Không khớp thì giữ nguyên thuộc tính gốc
                return $key . '=' . $quote . esc_attr($val) . $quote;
            }
        }, $shortcode_tag);
    }

    /**
     * Kiểm tra một chuỗi có phải là thẻ Shortcode hay không.
     */
    private static function is_shortcode($string) {
        $string = trim($string);
        return (strpos($string, '[') === 0 && strrpos($string, ']') === (strlen($string) - 1));
    }

    /**
     * Kiểm tra một chuỗi có phải là thẻ HTML hay không.
     */
    private static function is_html_tag($string) {
        $string = trim($string);
        return (strpos($string, '<') === 0 && strrpos($string, '>') === (strlen($string) - 1));
    }
}
