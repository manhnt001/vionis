/**
 * Flatsome Content Translator - Admin JS
 * Handles asynchronous batch translation with Base64 encoding keys, premium settings, and safe publishing.
 */

jQuery(document).ready(function($) {
    'use strict';

    // State Variables
    let extractedStrings = []; // Chứa danh sách đối tượng { key: base64, original: text }
    let isTranslating = false;

    // DOM Elements
    const $engineSelect = $('#fl-engine-select');
    const $geminiKeyGroup = $('#fl-gemini-key-group');
    const $geminiKeyInput = $('#fl-gemini-key');
    
    const $postTypeSelect = $('#fl-post-type');
    const $postIdSelect = $('#fl-post-id');
    const $btnAnalyze = $('#btn-analyze');
    const $workspaceIdle = $('#workspace-idle');
    const $workspaceActive = $('#workspace-active');
    const $progressBar = $('#fl-progress-bar');
    const $progressText = $('#fl-progress-text');
    const $translationList = $('#fl-translation-list');
    const $btnSaveTranslation = $('#btn-save-translation');
    
    const $origTitle = $('#fl-orig-title');
    const $transTitle = $('#fl-trans-title');
    const $saveModeRadios = $('input[name="fl-save-mode"]');
    const $overwriteAlert = $('#overwrite-alert');
    
    const $backupSection = $('#fl-backup-section');
    const $backupList = $('#fl-backup-list');

    /* ==========================================================================
       1. CẤU HÌNH BỘ MÁY DỊCH THUẬT (ENGINE SETTINGS)
       ========================================================================== */

    // Thay đổi engine dịch thuật
    $engineSelect.on('change', function() {
        const val = $(this).val();
        if (val === 'gemini') {
            $geminiKeyGroup.slideDown(250);
        } else {
            $geminiKeyGroup.slideUp(250);
        }
        saveSettings();
    });

    // Lưu API Key khi blur hoặc thay đổi
    $geminiKeyInput.on('blur change', function() {
        saveSettings();
    });

    /**
     * Hàm lưu cấu hình tức thì bằng AJAX
     */
    function saveSettings() {
        const engine = $engineSelect.val();
        const geminiKey = $geminiKeyInput.val().trim();

        $.ajax({
            url: flTranslatorOpts.ajax_url,
            type: 'POST',
            data: {
                action: 'fl_translator_save_settings',
                engine: engine,
                gemini_key: geminiKey,
                security: flTranslatorOpts.nonce
            }
        });
    }

    // Đồng bộ chiều dịch thuật
    const $sourceLang = $('#fl-source-lang');
    const $targetLang = $('#fl-target-lang');
    
    $sourceLang.on('change', function() {
        if ($(this).val() === 'vi') {
            $targetLang.val('en');
        } else {
            $targetLang.val('vi');
        }
        updateWorkspaceLabels();
    });

    $targetLang.on('change', function() {
        if ($(this).val() === 'vi') {
            $sourceLang.val('en');
        } else {
            $sourceLang.val('vi');
        }
        updateWorkspaceLabels();
    });

    // Đổi chiều dịch bằng nút Swap
    $('#btn-swap-langs').on('click', function(e) {
        e.preventDefault();
        const srcVal = $sourceLang.val();
        $sourceLang.val($targetLang.val());
        $targetLang.val(srcVal);
        updateWorkspaceLabels();
        
        // Hiệu ứng micro-animation xoay nút swap khi click
        $(this).css('transform', 'scale(0.9) rotate(180deg)');
        const $btn = $(this);
        setTimeout(function() {
            $btn.css('transform', '');
        }, 300);
    });

    function updateWorkspaceLabels() {
        const src = $sourceLang.val();
        const $labels = $('.fl-split-label');
        const $titleLabelOrig = $('.fl-meta-title-label-orig');
        const $titleLabelTrans = $('.fl-meta-title-label-trans');
        
        if ($labels.length === 2) {
            if (src === 'vi') {
                $labels.eq(0).text('Bản gốc Tiếng Việt');
                $labels.eq(1).text('Bản dịch Tiếng Anh (Có thể chỉnh sửa)');
                $titleLabelOrig.text('Tiêu đề gốc (VI)');
                $titleLabelTrans.text('Tiêu đề dịch (EN)');
                $transTitle.attr('placeholder', 'Tiêu đề tiếng Anh...');
            } else {
                $labels.eq(0).text('Bản gốc Tiếng Anh');
                $labels.eq(1).text('Bản dịch Tiếng Việt (Có thể chỉnh sửa)');
                $titleLabelOrig.text('Tiêu đề gốc (EN)');
                $titleLabelTrans.text('Tiêu đề dịch (VI)');
                $transTitle.attr('placeholder', 'Tiêu đề tiếng Việt...');
            }
        }
    }

    // Kiểm tra tính hợp lệ của Gemini API Key
    $('#btn-check-key').on('click', function(e) {
        e.preventDefault();
        const geminiKey = $geminiKeyInput.val().trim();
        const $btn = $(this);
        const $status = $('#fl-key-status');

        if (!geminiKey) {
            $status.removeClass('success').addClass('error').text('Vui lòng nhập API Key trước khi kiểm tra.').fadeIn();
            $geminiKeyInput.focus();
            return;
        }

        $btn.addClass('checking').prop('disabled', true).text('Đang kiểm tra...');
        $status.fadeOut().removeClass('success error').text('');

        $.ajax({
            url: flTranslatorOpts.ajax_url,
            type: 'POST',
            data: {
                action: 'fl_translator_check_key',
                api_key: geminiKey,
                security: flTranslatorOpts.nonce
            },
            success: function(response) {
                $btn.removeClass('checking').prop('disabled', false).text('Xác nhận & Kiểm tra');
                if (response.success) {
                    $status.removeClass('error').addClass('success').text(response.data.message).fadeIn();
                } else {
                    $status.removeClass('success').addClass('error').text(response.data.message).fadeIn();
                }
            },
            error: function() {
                $btn.removeClass('checking').prop('disabled', false).text('Xác nhận & Kiểm tra');
                $status.removeClass('success').addClass('error').text('Không thể kết nối tới máy chủ hoặc lỗi mạng. Vui lòng kiểm tra lại.').fadeIn();
            }
        });
    });

    /* ==========================================================================
       2. KHỞI TẠO & LOAD DANH SÁCH BÀI VIẾT
       ========================================================================== */

    // Load danh sách bài viết khi load trang
    loadPostsList($postTypeSelect.val());

    // Thay đổi Post Type -> Load lại bài viết tương ứng
    $postTypeSelect.on('change', function() {
        const selectedType = $(this).val();
        loadPostsList(selectedType);
    });

    // Thay đổi bài viết được chọn -> Kích hoạt nút Phân tích và tải Backup
    $postIdSelect.on('change', function() {
        const postId = $(this).val();
        if (postId) {
            $btnAnalyze.removeClass('disabled').prop('disabled', false);
            loadBackupsList(postId);
        } else {
            $btnAnalyze.addClass('disabled').prop('disabled', true);
            $backupSection.fadeOut();
        }
    });

    // Thay đổi phương thức xuất bản (Clone / Overwrite)
    $saveModeRadios.on('change', function() {
        $('.fl-radio-label').removeClass('active');
        $(this).closest('.fl-radio-label').addClass('active');

        if ($(this).val() === 'overwrite') {
            $overwriteAlert.fadeIn();
        } else {
            $overwriteAlert.fadeOut();
        }
    });

    /**
     * Hàm gọi AJAX tải danh sách bài viết từ Backend
     */
    function loadPostsList(postType) {
        $postIdSelect.prop('disabled', true).html('<option value="">-- Đang tải danh sách... --</option>');
        $btnAnalyze.addClass('disabled').prop('disabled', true);
        $backupSection.hide();

        $.ajax({
            url: flTranslatorOpts.ajax_url,
            type: 'POST',
            data: {
                action: 'fl_translator_get_posts',
                post_type: postType,
                security: flTranslatorOpts.nonce
            },
            success: function(response) {
                if (response.success) {
                    let html = '<option value="">-- Chọn một bài viết/trang --</option>';
                    if (response.data.length > 0) {
                        response.data.forEach(function(item) {
                            html += `<option value="${item.id}">${item.title}</option>`;
                        });
                        $postIdSelect.html(html).prop('disabled', false);
                    } else {
                        $postIdSelect.html('<option value="">-- Không có nội dung nào thuộc loại này --</option>');
                    }
                } else {
                    alert('Lỗi tải danh sách: ' + response.data.message);
                }
            },
            error: function() {
                alert('Có lỗi mạng xảy ra khi tải danh sách bài viết.');
            }
        });
    }

    /**
     * Tải danh sách các bản backup của bài viết
     */
    function loadBackupsList(postId) {
        $.ajax({
            url: flTranslatorOpts.ajax_url,
            type: 'POST',
            data: {
                action: 'fl_translator_get_backups',
                post_id: postId,
                security: flTranslatorOpts.nonce
            },
            success: function(response) {
                if (response.success && response.data.length > 0) {
                    let html = '';
                    response.data.forEach(function(backup) {
                        html += `
                            <li class="fl-backup-item">
                                <div class="fl-backup-info">
                                    <span class="fl-backup-date">${backup.date}</span>
                                    <span class="fl-backup-name" title="${backup.title}">Tiêu đề: ${backup.title}</span>
                                </div>
                                <button class="fl-btn-restore btn-restore-backup" data-id="${backup.id}">
                                    <span class="dashicons dashicons-image-rotate"></span> Khôi phục
                                </button>
                            </li>
                        `;
                    });
                    $backupList.html(html);
                    $backupSection.fadeIn();
                } else {
                    $backupSection.fadeOut();
                    $backupList.html('');
                }
            }
        });
    }

    /* ==========================================================================
       3. PHÂN TÍCH & DỊCH TỰ ĐỘNG BẰNG AJAX BATCH (BASE64 SAFE MAPPING)
       ========================================================================== */

    $btnAnalyze.on('click', function() {
        const postId = $postIdSelect.val();
        if (!postId || isTranslating) return;

        // Reset trạng thái & giao diện
        $workspaceIdle.hide();
        $workspaceActive.fadeIn();
        $translationList.empty();
        $progressBar.css('width', '0%');
        $progressText.text('Đang phân tích trang...');
        $btnSaveTranslation.prop('disabled', true);
        
        isTranslating = true;
        $btnAnalyze.addClass('disabled').text('Đang phân tích...');

        $.ajax({
            url: flTranslatorOpts.ajax_url,
            type: 'POST',
            data: {
                action: 'fl_translator_analyze',
                post_id: postId,
                security: flTranslatorOpts.nonce
            },
            success: function(response) {
                if (response.success) {
                    const data = response.data;
                    $origTitle.val(data.title);
                    
                    extractedStrings = data.strings;

                    if (extractedStrings.length === 0) {
                        $progressText.text('Hoàn thành (0 chuỗi)');
                        $progressBar.css('width', '100%');
                        translateSingleTitle(data.title);
                    } else {
                        // Bắt đầu dịch tiêu đề trước
                        translateSingleTitle(data.title);
                        // Dịch hàng loạt theo đợt (batch size = 5)
                        translateBatch(0, 5);
                    }
                } else {
                    alert('Lỗi phân tích: ' + response.data.message);
                    resetWorkspaceIdle();
                }
            },
            error: function() {
                alert('Có lỗi mạng xảy ra khi phân tích bài viết.');
                resetWorkspaceIdle();
            }
        });
    });

    /**
     * Dịch tiêu đề của trang và gán vào form
     */
    function translateSingleTitle(title) {
        const base64TitleKey = btoa(unescape(encodeURIComponent(title)));
        $.ajax({
            url: flTranslatorOpts.ajax_url,
            type: 'POST',
            data: {
                action: 'fl_translator_translate_batch',
                batch: [{ key: base64TitleKey, original: title }],
                source_lang: $sourceLang.val(),
                target_lang: $targetLang.val(),
                security: flTranslatorOpts.nonce
            },
            success: function(response) {
                if (response.success && response.data[base64TitleKey]) {
                    $transTitle.val(response.data[base64TitleKey]);
                } else {
                    $transTitle.val(title + ' (EN)');
                }
            }
        });
    }

    /**
     * Đệ quy dịch các chuỗi văn bản theo đợt sử dụng key Base64 an toàn
     */
    function translateBatch(startIndex, batchSize) {
        if (startIndex >= extractedStrings.length) {
            $progressText.text('Đã dịch xong 100%');
            $progressBar.css('width', '100%');
            $btnSaveTranslation.prop('disabled', false);
            isTranslating = false;
            $btnAnalyze.removeClass('disabled').html('<span class="dashicons dashicons-performance"></span> Phân tích & Dịch tự động');
            return;
        }

        const endIndex = Math.min(startIndex + batchSize, extractedStrings.length);
        const batch = extractedStrings.slice(startIndex, endIndex);

        $progressText.text(`Đang dịch ${startIndex}/${extractedStrings.length} chuỗi...`);
        const percent = Math.round((startIndex / extractedStrings.length) * 100);
        $progressBar.css('width', percent + '%');

        $.ajax({
            url: flTranslatorOpts.ajax_url,
            type: 'POST',
            data: {
                action: 'fl_translator_translate_batch',
                batch: batch,
                source_lang: $sourceLang.val(),
                target_lang: $targetLang.val(),
                security: flTranslatorOpts.nonce
            },
            success: function(response) {
                if (response.success) {
                    const results = response.data;
                    
                    batch.forEach(function(item) {
                        const translatedStr = results[item.key] || item.original;
                        // Render hàng song ngữ gán khóa key Base64 vào data-key
                        appendTranslationRow(item.key, item.original, translatedStr);
                    });

                    translateBatch(endIndex, batchSize);
                } else {
                    // Fallback
                    batch.forEach(function(item) {
                        appendTranslationRow(item.key, item.original, item.original);
                    });
                    translateBatch(endIndex, batchSize);
                }
            },
            error: function() {
                batch.forEach(function(item) {
                    appendTranslationRow(item.key, item.original, item.original);
                });
                translateBatch(endIndex, batchSize);
            }
        });
    }

    /**
     * Render và thêm hàng dịch song ngữ vào workspace
     */
    function appendTranslationRow(key, original, translated) {
        const originalEscaped = escapeHtml(original);
        const translatedEscaped = escapeHtml(translated);
        const rows = Math.min(Math.max(Math.ceil(original.length / 50), 2), 6);

        const rowHtml = `
            <div class="fl-translation-row" data-key="${key}">
                <div class="fl-source-box" style="min-height: ${rows * 24}px;">${originalEscaped}</div>
                <textarea class="edit-field" rows="${rows}" placeholder="Nhập bản dịch tiếng Anh...">${translatedEscaped}</textarea>
            </div>
        `;
        
        $translationList.append(rowHtml);
    }

    function resetWorkspaceIdle() {
        $workspaceActive.hide();
        $workspaceIdle.fadeIn();
        isTranslating = false;
        $btnAnalyze.removeClass('disabled').html('<span class="dashicons dashicons-performance"></span> Phân tích & Dịch tự động');
    }

    /* ==========================================================================
       4. XỬ LÝ LƯU BẢN DỊCH CUỐI CÙNG (BASE64 DICTIONARY SAFE)
       ========================================================================== */

    function executeSaveTranslation() {
        const postId = $postIdSelect.val();
        const saveMode = $('input[name="fl-save-mode"]:checked').val();
        const translatedTitle = $transTitle.val().trim();

        $btnSaveTranslation.prop('disabled', true).text('Đang tiến hành lưu bản dịch...');

        // Thu thập bản dịch map dạng { base64_key: userEditedStr } cực kỳ an toàn
        const finalTranslations = {};
        
        // Tạo Base64 key cho tiêu đề gốc để dịch chuẩn xác
        const origTitleText = $origTitle.val().trim();
        const base64TitleKey = btoa(unescape(encodeURIComponent(origTitleText)));
        finalTranslations[base64TitleKey] = translatedTitle;

        $translationList.find('.fl-translation-row').each(function() {
            const key = $(this).attr('data-key');
            const userEditedStr = $(this).find('textarea').val();
            finalTranslations[key] = userEditedStr;
        });

        $.ajax({
            url: flTranslatorOpts.ajax_url,
            type: 'POST',
            data: {
                action: 'fl_translator_save',
                post_id: postId,
                save_mode: saveMode,
                translated_title: translatedTitle,
                translations: finalTranslations,
                source_lang: $sourceLang.val(),
                target_lang: $targetLang.val(),
                security: flTranslatorOpts.nonce
            },
            success: function(response) {
                if (response.success) {
                    // Hiển thị Toast thông báo nhanh thân thiện
                    showToast("Đã dịch xong!", "success");
                    
                    // Cấu hình Modal Popup thân thiện dựa theo phương thức lưu
                    const $modal = $('#fl-modal-container');
                    const $modalMsg = $('#fl-modal-message');
                    const $modalLink = $('#link-modal-view');
                    const targetLangLabel = $targetLang.val() === 'vi' ? 'tiếng Việt' : 'tiếng Anh';
                    
                    if (saveMode === 'clone') {
                        $modalMsg.text(`Đã dịch thành công sang sản phẩm ${targetLangLabel} mới! Bản dịch hiện đang được lưu dưới dạng bản nháp (Draft) để bạn dễ dàng kiểm duyệt.`);
                        $modalLink.text("Chỉnh sửa bản dịch").attr('href', response.data.url);
                    } else {
                        $modalMsg.text(`Nội dung sản phẩm gốc đã được cập nhật thành công bản dịch ${targetLangLabel} mới (đã tự động tạo bản sao lưu khôi phục).`);
                        $modalLink.text("Xem sản phẩm").attr('href', response.data.url);
                        loadBackupsList(postId);
                    }
                    
                    // Hiển thị Modal với hiệu ứng fadeIn mượt mà
                    setTimeout(function() {
                        $modal.fadeIn(300);
                    }, 400);

                    $btnSaveTranslation.prop('disabled', false).html('<span class="dashicons dashicons-cloud-saved"></span> Hoàn thành & Lưu bản dịch');
                } else {
                    showToast("Lỗi: " + response.data.message, "error");
                    $btnSaveTranslation.prop('disabled', false).html('<span class="dashicons dashicons-cloud-saved"></span> Hoàn thành & Lưu bản dịch');
                }
            },
            error: function() {
                showToast("Có lỗi mạng xảy ra khi gửi yêu cầu lưu bản dịch.", "error");
                $btnSaveTranslation.prop('disabled', false).html('<span class="dashicons dashicons-cloud-saved"></span> Hoàn thành & Lưu bản dịch');
            }
        });
    }

    $btnSaveTranslation.on('click', function() {
        const postId = $postIdSelect.val();
        const saveMode = $('input[name="fl-save-mode"]:checked').val();
        const translatedTitle = $transTitle.val().trim();

        if (!postId) return;
        if (!translatedTitle) {
            alert('Vui lòng nhập tiêu đề dịch.');
            $transTitle.focus();
            return;
        }

        if (saveMode === 'overwrite') {
            // Hiển thị Modal Xác nhận Ghi đè Premium
            $('#fl-confirm-modal-container').fadeIn(300);
        } else {
            // Chế độ clone thì lưu trực tiếp không cần xác nhận
            executeSaveTranslation();
        }
    });

    // Hủy bỏ ghi đè
    $('#btn-confirm-cancel').on('click', function(e) {
        e.preventDefault();
        $('#fl-confirm-modal-container').fadeOut(250);
    });

    // Đồng ý ghi đè
    $('#btn-confirm-ok').on('click', function(e) {
        e.preventDefault();
        $('#fl-confirm-modal-container').fadeOut(250);
        executeSaveTranslation();
    });

    /* ==========================================================================
       5. KHÔI PHỤC DỮ LIỆU TỪ BẢN SAO LƯU (BACKUP)
       ========================================================================== */

    $(document).on('click', '.btn-restore-backup', function() {
        const postId = $postIdSelect.val();
        const backupId = $(this).attr('data-id');

        if (!postId || !backupId) return;

        const confirmRestore = confirm('Bạn có chắc chắn muốn KHÔI PHỤC lại phiên bản này?\n\nToàn bộ tiêu đề và nội dung hiện tại của trang gốc sẽ bị thay thế bằng nội dung của bản sao lưu này.');
        if (!confirmRestore) return;

        const $btn = $(this);
        $btn.prop('disabled', true).text('Đang khôi phục...');

        $.ajax({
            url: flTranslatorOpts.ajax_url,
            type: 'POST',
            data: {
                action: 'fl_translator_restore',
                post_id: postId,
                backup_id: backupId,
                security: flTranslatorOpts.nonce
            },
            success: function(response) {
                if (response.success) {
                    alert(response.data.message);
                    $btnAnalyze.trigger('click');
                    loadBackupsList(postId);
                } else {
                    alert('Lỗi khôi phục: ' + response.data.message);
                    $btn.prop('disabled', false).html('<span class="dashicons dashicons-image-rotate"></span> Khôi phục');
                }
            },
            error: function() {
                alert('Có lỗi mạng xảy ra khi yêu cầu khôi phục.');
                $btn.prop('disabled', false).html('<span class="dashicons dashicons-image-rotate"></span> Khôi phục');
            }
        });
    });

    // Sự kiện đóng modal popup (Tiếp tục dịch - Tải lại trang)
    $('#btn-modal-close').on('click', function(e) {
        e.preventDefault();
        window.location.reload();
    });

    /* ==========================================================================
       CÁC HÀM HELPER HỖ TRỢ
       ========================================================================== */

    /**
     * Hiển thị thông báo Toast nổi ở góc phải màn hình
     */
    function showToast(message, type = 'success') {
        // Tạo container nếu chưa có
        let $container = $('.fl-toast-container');
        if ($container.length === 0) {
            $container = $('<div class="fl-toast-container"></div>').appendTo('body');
        }

        const iconClass = type === 'success' ? 'dashicons-yes' : 'dashicons-warning';
        const $toast = $(`
            <div class="fl-toast ${type}">
                <span class="dashicons ${iconClass} fl-toast-icon"></span>
                <span>${message}</span>
            </div>
        `).appendTo($container);

        // Slide in
        setTimeout(function() {
            $toast.addClass('show');
        }, 50);

        // Tự động Slide out và xóa sau 3.5 giây
        setTimeout(function() {
            $toast.removeClass('show');
            setTimeout(function() {
                $toast.remove();
            }, 400);
        }, 3500);
    }

    function escapeHtml(string) {
        if (!string) return '';
        return string
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }
});
