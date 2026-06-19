jQuery(document).ready(function($) {
    // Chỉ hoạt động trên trang biên dịch chuyên dụng
    if ($('.fl-workspace-wrapper').length === 0) {
        return;
    }

    // 1. Tự động giãn nở chiều cao của Textarea chặng lịch trình (Auto-expanding textareas)
    function autoExpandTextarea() {
        $('textarea').each(function() {
            this.style.height = 'auto';
            this.style.height = (this.scrollHeight + 2) + 'px';
        });
    }

    autoExpandTextarea();
    $(document).on('input', 'textarea', function() {
        this.style.height = 'auto';
        this.style.height = (this.scrollHeight + 2) + 'px';
    });

    // 2. Đồng bộ hoá cuộn giữa 2 cột (Sync Scroll) khi di chuyển chuột qua
    var $colLeft = $('.original-col');
    var $colRight = $('.translation-col');
    
    // Đồng bộ cuộn giữa các hàng lịch trình (Schedule) tương ứng
    $('.editable-schedule-row').hover(
        function() {
            var index = $(this).data('index');
            var $origRow = $('.readonly-schedule-row').eq(index);
            if ($origRow.length) {
                $origRow.css({
                    'border-color': '#0284c7',
                    'background-color': '#f0f9ff',
                    'transition': 'all 0.2s ease'
                });
                $(this).css({
                    'border-color': '#16a34a',
                    'background-color': '#f0fdf4',
                    'transition': 'all 0.2s ease'
                });
            }
        },
        function() {
            var index = $(this).data('index');
            var $origRow = $('.readonly-schedule-row').eq(index);
            if ($origRow.length) {
                $origRow.css({
                    'border-color': '',
                    'background-color': ''
                });
                $(this).css({
                    'border-color': '',
                    'background-color': ''
                });
            }
        }
    );

    // 3. Xử lý Dịch tự động AI
    function flashField($field) {
        if (!$field || $field.length === 0) return;
        $field.addClass('field-translated-flash');
        setTimeout(function() {
            $field.removeClass('field-translated-flash');
        }, 1500);
    }

    function setEditorContent(id, content) {
        if (typeof tinymce !== 'undefined' && tinymce.get(id)) {
            var editor = tinymce.get(id);
            if (editor && !editor.isHidden()) {
                editor.setContent(content);
                return;
            }
        }
        $('#' + id).val(content);
    }

    $('#btn-workspace-auto-translate').on('click', function(e) {
        e.preventDefault();
        
        var $btn = $(this);
        var engine = $('#workspace-engine-select').val();
        
        if (!flTranslatorOpts || !flTranslatorOpts.post_id) {
            alert('Lỗi: Không tìm thấy ID bài viết.');
            return;
        }

        if ($btn.hasClass('disabled') || $btn.prop('disabled')) {
            return;
        }

        // Thay đổi trạng thái nút bấm sang Loading
        var originalHtml = $btn.html();
        $btn.prop('disabled', true).addClass('disabled');
        $btn.html('<span class="dashicons dashicons-update fl-spinner"></span> Đang dịch tự động...');

        $.ajax({
            url: flTranslatorOpts.ajax_url,
            type: 'POST',
            dataType: 'json',
            data: {
                action: 'fl_translator_workspace_translate',
                post_id: flTranslatorOpts.post_id,
                engine: engine,
                security: flTranslatorOpts.nonce
            },
            success: function(response) {
                if (response.success && response.data) {
                    var data = response.data;

                    // 1. Tiêu đề
                    if (data.title) {
                        var $title = $('#vi-title');
                        $title.val(data.title);
                        flashField($title);
                    }

                    // 2. Địa điểm
                    if (data.place) {
                        var $place = $('#vi-place');
                        $place.val(data.place);
                        flashField($place);
                    }

                    // 3. Mô tả ngắn (Excerpt)
                    if (data.excerpt !== undefined) {
                        setEditorContent('viexcerpt', data.excerpt);
                        flashField($('#wp-viexcerpt-wrap'));
                    }

                    // 4. Mô tả chi tiết (Content)
                    if (data.content !== undefined) {
                        setEditorContent('vicontent', data.content);
                        flashField($('#wp-vicontent-wrap'));
                    }

                    // 5. Lịch trình (Schedule)
                    if (data.schedule && data.schedule.length > 0) {
                        data.schedule.forEach(function(row, index) {
                            var $rowTitleInput = $('input[name="vi_schedule[' + index + '][title]"]');
                            var $rowContentTextarea = $('textarea[name="vi_schedule[' + index + '][content]"]');

                            $rowTitleInput.val(row.title);
                            $rowContentTextarea.val(row.content);

                            flashField($rowTitleInput);
                            flashField($rowContentTextarea);
                        });
                    }

                    // Co giãn lại các textarea lịch trình cho vừa văn bản mới dịch
                    autoExpandTextarea();

                } else {
                    alert('Lỗi dịch thuật: ' + (response.data.message || 'Không rõ nguyên nhân.'));
                }
            },
            error: function(xhr, status, error) {
                console.error(xhr, status, error);
                alert('Lỗi kết nối máy chủ. Vui lòng kiểm tra lại.');
            },
            complete: function() {
                // Khôi phục nút bấm
                $btn.prop('disabled', false).removeClass('disabled');
                $btn.html(originalHtml);
            }
        });
    });

    // 4. Lắng nghe sự kiện chuyển đổi chế độ dịch trong không gian dịch
    $('input[name="workspace-mode"]').on('change', function() {
        var mode = $(this).val();

        // Cập nhật class active cho nhãn
        var $manualLabel = $('input[name="workspace-mode"][value="manual"]').closest('.mode-option');
        var $autoLabel   = $('input[name="workspace-mode"][value="auto"]').closest('.mode-option');

        if (mode === 'auto') {
            $manualLabel.removeClass('active-manual');
            $autoLabel.addClass('active-auto');
            $('.workspace-translate-bar').slideDown(250);
            checkGeminiApiKey(); // Kích hoạt kiểm tra Key khi hiện thanh tự động
        } else {
            $autoLabel.removeClass('active-auto');
            $manualLabel.addClass('active-manual');
            $('.workspace-translate-bar').slideUp(250);
            $('.workspace-api-key-warning').slideUp(200, function() { $(this).remove(); });
        }
    });

    // 5. Kiểm tra API Key của Google Gemini
    function checkGeminiApiKey() {
        // Chỉ chạy kiểm tra khi chế độ Dịch tự động đang hoạt động
        if ($('input[name="workspace-mode"]:checked').val() !== 'auto') {
            return;
        }

        var engine = $('#workspace-engine-select').val();
        var $btn = $('#btn-workspace-auto-translate');
        
        // Xóa cảnh báo cũ nếu có
        $('.workspace-api-key-warning').remove();
        
        if (engine === 'gemini' && flTranslatorOpts && flTranslatorOpts.has_gemini_key === false) {
            // Khóa nút dịch và chuyển sang chế độ cảnh báo
            $btn.prop('disabled', true).addClass('disabled fl-btn-warning-disabled');
            $btn.html('<span class="dashicons dashicons-warning"></span> Cần cấu hình API Key');
            $btn.css({
                'background': '#cbd5e1',
                'border-color': '#cbd5e1',
                'color': '#64748b',
                'cursor': 'not-allowed',
                'box-shadow': 'none'
            });
            
            // Dựng thông báo cảnh báo và nút chuyển hướng đẹp mắt
            var warningHtml = 
                '<div class="workspace-api-key-warning" style="margin-top: 15px; width: 100%; background: #fffbeb; border: 1px solid #fef3c7; border-radius: 8px; padding: 16px 20px; display: flex; align-items: center; justify-content: space-between; gap: 16px; box-shadow: 0 2px 4px rgba(0,0,0,0.02); box-sizing: border-box; clear: both; transition: all 0.2s ease;">' +
                    '<div style="display: flex; align-items: center; gap: 10px; color: #b45309; font-size: 13.5px; font-weight: 500; font-family: \'Inter\', sans-serif;">' +
                        '<span class="dashicons dashicons-warning" style="color: #d97706; font-size: 22px; width: 22px; height: 22px; line-height: 1;"></span>' +
                        '<span>Bạn đã chọn <strong>Google Gemini AI (Premium)</strong> nhưng chưa cấu hình API Key. Vui lòng cấu hình Key trước khi tiến hành dịch.</span>' +
                    '</div>' +
                    '<a href="' + flTranslatorOpts.configure_url + '" target="_blank" class="button button-primary" style="background: #d97706; border-color: #d97706; color: #fff; box-shadow: none; text-shadow: none; border-radius: 4px; padding: 6px 16px; text-decoration: none; font-size: 13px; font-weight: 600; font-family: \'Outfit\', sans-serif; display: inline-flex; align-items: center; gap: 6px; transition: all 0.15s ease; height: auto; line-height: 1.4; white-space: nowrap;">' +
                        '<span class="dashicons dashicons-admin-generic" style="font-size: 16px; width: 16px; height: 16px; margin: 0; line-height: 1;"></span> Cấu hình API Key ngay' +
                    '</a>' +
                '</div>';
            
            $('.workspace-translate-bar').after(warningHtml);
        } else {
            // Phục hồi lại trạng thái hoạt động bình thường
            $btn.prop('disabled', false).removeClass('disabled fl-btn-warning-disabled');
            $btn.html('<span class="dashicons dashicons-controls-play"></span> Dịch tự động toàn bộ');
            $btn.css({
                'background': '',
                'border-color': '',
                'color': '',
                'cursor': '',
                'box-shadow': ''
            });
        }
    }

    // Lắng nghe sự thay đổi của bộ máy dịch
    $('#workspace-engine-select').on('change', function() {
        checkGeminiApiKey();
    });

    // Chạy kiểm tra ngay khi load nếu chế độ đang là dịch tự động
    checkGeminiApiKey();
});
