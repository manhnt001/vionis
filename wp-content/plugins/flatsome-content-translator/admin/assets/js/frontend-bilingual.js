(function($) {
    $(document).ready(function() {
        var $transData = $('#manual-vi-translations');
        if (!$transData.length) return;

        // Tải dữ liệu bản dịch Tiếng Việt thủ công
        var viData = {
            title: $transData.data('title'),
            excerpt: $transData.data('excerpt'),
            place: $transData.data('place'),
            content: $('#manual-vi-content').html(),
            schedule: []
        };

        try {
            var rawSchedule = $('#manual-vi-schedule').text();
            if (rawSchedule) {
                viData.schedule = JSON.parse(rawSchedule);
            }
        } catch (e) {
            console.error('Error parsing Vietnamese schedule JSON', e);
        }

        // Lưu giữ nội dung Tiếng Anh gốc ban đầu để khôi phục khi đổi lại ngôn ngữ
        var origData = {
            title: '',
            excerpt: '',
            content: '',
            schedule: []
        };

        var isSwapped = false;

        // Hàm đọc cookie
        function getCookie(name) {
            var value = "; " + document.cookie;
            var parts = value.split("; " + name + "=");
            if (parts.length == 2) return parts.pop().split(";").shift();
        }

        // Kiểm tra xem Tiếng Việt (GTranslate) có đang hoạt động hay không
        function isVietnameseActive() {
            var googtrans = getCookie('googtrans');
            
            // 1. Kiểm tra cookie googtrans của Google Translate
            if (googtrans && googtrans.indexOf('/vi') !== -1) {
                return true;
            }
            
            // 2. Kiểm tra thuộc tính lang của thẻ html
            var htmlLang = $('html').attr('lang');
            if (htmlLang && (htmlLang.toLowerCase().indexOf('vi') === 0 || htmlLang.toLowerCase().indexOf('vi-vn') === 0)) {
                return true;
            }
            
            // 3. Kiểm tra class translated-ltr của Google Translate
            if ($('html').hasClass('translated-ltr') && googtrans && googtrans.indexOf('/vi') !== -1) {
                return true;
            }
            
            return false;
        }

        // Thực hiện hoán đổi sang bản dịch Tiếng Việt thủ công chuẩn xác
        function performSwap() {
            if (isSwapped) return;

            // 1. Hoán đổi Tiêu đề sản phẩm
            var $title = $('h1.product-title, .page-header h1, h1.entry-title');
            if ($title.length && viData.title) {
                origData.title = $title.html();
                $title.html(viData.title).addClass('notranslate');
            }

            // 2. Hoán đổi Địa điểm khởi hành (Place)
            var origPlaceText = $transData.data('orig-place');
            if (origPlaceText && viData.place) {
                // Quét qua các thẻ chứa nội dung chính xác là origPlaceText
                $('*').each(function() {
                    var $this = $(this);
                    if ($this.children().length === 0 && $this.text().trim() === origPlaceText) {
                        $this.data('orig-text', $this.html());
                        $this.html(viData.place).addClass('notranslate');
                    }
                });
            }

            // 3. Hoán đổi Mô tả ngắn (Excerpt)
            var $excerpt = $('.product-short-description, .woocommerce-product-details__short-description, .short-description');
            if ($excerpt.length && viData.excerpt) {
                origData.excerpt = $excerpt.html();
                $excerpt.html(viData.excerpt).addClass('notranslate');
            }

            // 4. Hoán đổi Mô tả chi tiết (Content)
            var $content = $('#tab-description, .product-description, #accordion-description-content');
            if ($content.length && viData.content) {
                origData.content = $content.html();
                $content.html(viData.content).addClass('notranslate');
            }

            // 5. Hoán đổi Lịch trình Tour (Itinerary/Schedule Accordion)
            if (viData.schedule && viData.schedule.length) {
                $('.accordion-item').each(function(index) {
                    var $item = $(this);
                    var itemData = viData.schedule[index];
                    if (itemData) {
                        var $titleEl = $item.find('.accordion-title span');
                        var $contentEl = $item.find('.accordion-inner');
                        
                        var origRow = {
                            title: $titleEl.length ? $titleEl.html() : '',
                            content: $contentEl.length ? $contentEl.html() : ''
                        };
                        origData.schedule[index] = origRow;

                        if ($titleEl.length && itemData.title) {
                            $titleEl.html(itemData.title).addClass('notranslate');
                        }
                        if ($contentEl.length && itemData.content) {
                            $contentEl.html(itemData.content).addClass('notranslate');
                        }
                    }
                });
            }

            isSwapped = true;
        }

        // Khôi phục lại bản Tiếng Anh gốc khi người dùng đổi về Tiếng Anh
        function restoreOriginal() {
            if (!isSwapped) return;

            // 1. Khôi phục Tiêu đề
            var $title = $('h1.product-title, .page-header h1, h1.entry-title');
            if ($title.length && origData.title) {
                $title.html(origData.title).removeClass('notranslate');
            }

            // 2. Khôi phục Địa điểm
            $('*').each(function() {
                var $this = $(this);
                if ($this.data('orig-text')) {
                    $this.html($this.data('orig-text')).removeClass('notranslate');
                    $this.removeData('orig-text');
                }
            });

            // 3. Khôi phục Mô tả ngắn
            var $excerpt = $('.product-short-description, .woocommerce-product-details__short-description, .short-description');
            if ($excerpt.length && origData.excerpt) {
                $excerpt.html(origData.excerpt).removeClass('notranslate');
            }

            // 4. Khôi phục Mô tả chi tiết
            var $content = $('#tab-description, .product-description, #accordion-description-content');
            if ($content.length && origData.content) {
                $content.html(origData.content).removeClass('notranslate');
            }

            // 5. Khôi phục Lịch trình
            $('.accordion-item').each(function(index) {
                var $item = $(this);
                var origRow = origData.schedule[index];
                if (origRow) {
                    var $titleEl = $item.find('.accordion-title span');
                    var $contentEl = $item.find('.accordion-inner');

                    if ($titleEl.length && origRow.title) {
                        $titleEl.html(origRow.title).removeClass('notranslate');
                    }
                    if ($contentEl.length && origRow.content) {
                        $contentEl.html(origRow.content).removeClass('notranslate');
                    }
                }
            });

            isSwapped = false;
        }

        // Theo dõi định kỳ để hoán đổi ngôn ngữ siêu tốc mà không cần tải lại trang
        setInterval(function() {
            if (isVietnameseActive()) {
                performSwap();
            } else {
                restoreOriginal();
            }
        }, 600);
    });
})(jQuery);
