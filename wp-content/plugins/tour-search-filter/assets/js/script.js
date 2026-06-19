(function ($) {
    $(document).ready(function () {

        var selectedStart = $('#tsf-ngaykhoihanh').val();
        var selectedEnd = $('#tsf-ngayketthuc').val();

        // Move dropdowns to body to completely escape builder stacking contexts
        var $whereDropdown = $('#tsf-where-dropdown-menu').appendTo('body');
        var $calDropdown = $('#tsf-calendar-dropdown-menu').appendTo('body');

        function positionWhereDropdown() {
            var $trigger = $('#tsf-where-trigger');
            var offset = $trigger.offset();
            $whereDropdown.css({
                top: offset.top + $trigger.outerHeight() + 'px',
                left: offset.left + 'px',
                position: 'absolute',
                zIndex: 999999
            });
        }

        function positionCalDropdown() {
            var $trigger = $('#tsf-when-trigger');
            var offset = $trigger.offset();
            $calDropdown.css({
                top: offset.top + $trigger.outerHeight() + 'px',
                left: offset.left + ($trigger.outerWidth() / 2) + 'px',
                position: 'absolute',
                zIndex: 999999
            });
        }

        $(window).on('resize', function () {
            if ($whereDropdown.hasClass('show')) positionWhereDropdown();
            if ($calDropdown.hasClass('show')) positionCalDropdown();
        });

        // Parse initial dates if present
        var startDateObj = selectedStart ? parseDateString(selectedStart) : null;
        var endDateObj = selectedEnd ? parseDateString(selectedEnd) : null;

        // Active input state ('start' or 'end')
        var activeInputField = 'start';

        function updateActiveInputVisuals() {
            $('.tsf-cal-input-box').removeClass('active-field');
            if (activeInputField === 'start') {
                $('#tsf-start-input-display').parent().addClass('active-field');
            } else {
                $('#tsf-end-input-display').parent().addClass('active-field');
            }
        }

        // Calendar state
        var currentDate = new Date(2026, 5, 18); // Default to June 18, 2026 (local time is June 2026)
        if (startDateObj) {
            currentDate = new Date(startDateObj.getFullYear(), startDateObj.getMonth(), 1);
        }
        var viewYear = currentDate.getFullYear();
        var viewMonth = currentDate.getMonth();

        // Toggle Where Dropdown
        $('#tsf-where-trigger').on('click', function (e) {
            if ($(e.target).closest('#tsf-where-dropdown-menu').length) return;
            $calDropdown.removeClass('show');

            if ($whereDropdown.hasClass('show')) {
                $whereDropdown.removeClass('show');
            } else {
                positionWhereDropdown();
                $whereDropdown.addClass('show');
            }
            e.stopPropagation();
        });

        // Toggle Calendar Dropdown
        $('#tsf-when-trigger').on('click', function (e) {
            if ($(e.target).closest('#tsf-calendar-dropdown-menu').length) return;
            $whereDropdown.removeClass('show');

            if ($calDropdown.hasClass('show')) {
                $calDropdown.removeClass('show');
            } else {
                positionCalDropdown();
                $calDropdown.addClass('show');
                activeInputField = 'start';
                updateActiveInputVisuals();
            }
            e.stopPropagation();
        });

        // Listen to focus/click on display inputs
        $('#tsf-start-input-display').on('focus click', function (e) {
            activeInputField = 'start';
            updateActiveInputVisuals();
        });

        $('#tsf-end-input-display').on('focus click', function (e) {
            activeInputField = 'end';
            updateActiveInputVisuals();
        });

        // Close all dropdowns when clicking outside
        $(document).on('click', function (e) {
            if (!$(e.target).closest('.tsf-container').length && !$(e.target).closest('.tsf-dropdown').length) {
                $('.tsf-dropdown').removeClass('show');
            }
        });

        // Validate date parts
        function isValidDate(d, m, y) {
            var date = new Date(y, m - 1, d);
            return date.getFullYear() === y && date.getMonth() === m - 1 && date.getDate() === d;
        }

        // Parse DD/MM/YYYY text into Date object
        function parseTypedDate(str) {
            var regex = /^(\d{2})\/(\d{2})\/(\d{4})$/;
            var match = str.match(regex);
            if (match) {
                var d = parseInt(match[1], 10);
                var m = parseInt(match[2], 10);
                var y = parseInt(match[3], 10);
                if (isValidDate(d, m, y)) {
                    return new Date(y, m - 1, d);
                }
            }
            return null;
        }

        function updateLabel() {
            var startFormatted = startDateObj ? formatDateString(startDateObj) : '';
            var endFormatted = endDateObj ? formatDateString(endDateObj) : '';

            if (startDateObj && endDateObj) {
                $('#tsf-when-display-text').text(startFormatted + ' - ' + endFormatted);
            } else if (startDateObj) {
                $('#tsf-when-display-text').text(startFormatted);
            } else {
                $('#tsf-when-display-text').html('<span class="notranslate"><span class="menu-lang-en">When?</span><span class="menu-lang-vi">Khi nào?</span></span>');
            }
        }

        // Mask inputs for DD/MM/YYYY formatting
        function maskDateInput(e) {
            var input = e.target;
            var val = input.value;
            var cleaned = val.replace(/\D/g, '');
            var isDeleting = false;
            if (e.originalEvent && e.originalEvent.inputType === 'deleteContentBackward') {
                isDeleting = true;
            }

            var formatted = '';
            if (cleaned.length > 0) {
                if (cleaned.length <= 2) {
                    formatted = cleaned;
                    if (cleaned.length === 2 && !isDeleting) {
                        formatted += '/';
                    }
                } else if (cleaned.length <= 4) {
                    formatted = cleaned.substring(0, 2) + '/' + cleaned.substring(2);
                    if (cleaned.length === 4 && !isDeleting) {
                        formatted += '/';
                    }
                } else {
                    formatted = cleaned.substring(0, 2) + '/' + cleaned.substring(2, 4) + '/' + cleaned.substring(4, 8);
                }
            }

            if (val !== formatted) {
                input.value = formatted;
            }
        }

        // Allow typing in Starting on input
        $('#tsf-start-input-display').on('input', function (e) {
            maskDateInput(e);
            var val = $(this).val().trim();
            if (val === '') {
                startDateObj = null;
                $('#tsf-ngaykhoihanh').val('');
                updateLabel();
                renderCalendar();
                return;
            }
            var date = parseTypedDate(val);
            if (date) {
                startDateObj = date;
                viewYear = date.getFullYear();
                viewMonth = date.getMonth();
                updateYearTabs();
                $('#tsf-ngaykhoihanh').val(val);
                updateLabel();
                renderCalendar();
            }
        });

        // Allow typing in Ending on input
        $('#tsf-end-input-display').on('input', function (e) {
            maskDateInput(e);
            var val = $(this).val().trim();
            if (val === '') {
                endDateObj = null;
                $('#tsf-ngayketthuc').val('');
                updateLabel();
                renderCalendar();
                return;
            }
            var date = parseTypedDate(val);
            if (date) {
                endDateObj = date;
                $('#tsf-ngayketthuc').val(val);
                updateLabel();
                renderCalendar();
            }
        });

        // Handle Where selection
        $('#tsf-where-dropdown-menu li').on('click', function () {
            var slug = $(this).data('slug');
            var htmlContent = $(this).html();
            $('#tsf-noiden').val(slug);
            $('#tsf-where-display-text').html(htmlContent);
            $('#tsf-where-dropdown-menu li').removeClass('active');
            $(this).addClass('active');
            $('#tsf-where-dropdown-menu').removeClass('show');
        });

        // Handle Year tab click
        $('.tsf-year-tab').on('click', function (e) {
            e.stopPropagation();
            var yr = parseInt($(this).data('year'));
            viewYear = yr;
            updateYearTabs();
            renderCalendar();
        });

        // Handle Month Navigation
        $('#tsf-month-prev').on('click', function (e) {
            e.stopPropagation();
            viewMonth--;
            if (viewMonth < 0) {
                viewMonth = 11;
                viewYear--;
            }
            updateYearTabs();
            renderCalendar();
        });

        $('#tsf-month-next').on('click', function (e) {
            e.stopPropagation();
            viewMonth++;
            if (viewMonth > 11) {
                viewMonth = 0;
                viewYear++;
            }
            updateYearTabs();
            renderCalendar();
        });

        function updateYearTabs() {
            $('.tsf-year-tab').removeClass('active');
            $('.tsf-year-tab[data-year="' + viewYear + '"]').addClass('active');
        }

        function parseDateString(str) {
            // parses dd/mm/yyyy
            var parts = str.split('/');
            if (parts.length === 3) {
                return new Date(parseInt(parts[2]), parseInt(parts[1]) - 1, parseInt(parts[0]));
            }
            return null;
        }

        function formatDateString(date) {
            var dd = String(date.getDate()).padStart(2, '0');
            var mm = String(date.getMonth() + 1).padStart(2, '0');
            var yyyy = date.getFullYear();
            return dd + '/' + mm + '/' + yyyy;
        }

        // Removing selectWholeMonth function completely as it's no longer needed

        var monthNames = [
            '<span class="notranslate"><span class="menu-lang-en">January</span><span class="menu-lang-vi">Tháng 1</span></span>',
            '<span class="notranslate"><span class="menu-lang-en">February</span><span class="menu-lang-vi">Tháng 2</span></span>',
            '<span class="notranslate"><span class="menu-lang-en">March</span><span class="menu-lang-vi">Tháng 3</span></span>',
            '<span class="notranslate"><span class="menu-lang-en">April</span><span class="menu-lang-vi">Tháng 4</span></span>',
            '<span class="notranslate"><span class="menu-lang-en">May</span><span class="menu-lang-vi">Tháng 5</span></span>',
            '<span class="notranslate"><span class="menu-lang-en">June</span><span class="menu-lang-vi">Tháng 6</span></span>',
            '<span class="notranslate"><span class="menu-lang-en">July</span><span class="menu-lang-vi">Tháng 7</span></span>',
            '<span class="notranslate"><span class="menu-lang-en">August</span><span class="menu-lang-vi">Tháng 8</span></span>',
            '<span class="notranslate"><span class="menu-lang-en">September</span><span class="menu-lang-vi">Tháng 9</span></span>',
            '<span class="notranslate"><span class="menu-lang-en">October</span><span class="menu-lang-vi">Tháng 10</span></span>',
            '<span class="notranslate"><span class="menu-lang-en">November</span><span class="menu-lang-vi">Tháng 11</span></span>',
            '<span class="notranslate"><span class="menu-lang-en">December</span><span class="menu-lang-vi">Tháng 12</span></span>'
        ];

        function renderCalendar() {
            $('#tsf-month-year-title').html(monthNames[viewMonth]);

            var firstDayIndex = new Date(viewYear, viewMonth, 1).getDay();
            var totalDays = new Date(viewYear, viewMonth + 1, 0).getDate();
            var prevTotalDays = new Date(viewYear, viewMonth, 0).getDate();

            var html = '';

            // Prev month empty/padded days
            for (var i = firstDayIndex - 1; i >= 0; i--) {
                var d = prevTotalDays - i;
                var m = viewMonth - 1;
                var y = viewYear;
                if (m < 0) { m = 11; y--; }
                html += '<span class="tsf-day padded" data-date="' + y + '-' + m + '-' + d + '">' + d + '</span>';
            }

            // Current month days
            for (var d = 1; d <= totalDays; d++) {
                var cellDateObj = new Date(viewYear, viewMonth, d);
                var cellTime = cellDateObj.getTime();

                var classes = 'tsf-day';

                if (startDateObj && cellDateObj.toDateString() === startDateObj.toDateString()) {
                    classes += ' active start-date';
                } else if (endDateObj && cellDateObj.toDateString() === endDateObj.toDateString()) {
                    classes += ' active end-date';
                } else if (startDateObj && endDateObj && cellTime > startDateObj.getTime() && cellTime < endDateObj.getTime()) {
                    classes += ' in-range';
                }

                html += '<span class="' + classes + '" data-day="' + d + '">' + d + '</span>';
            }

            $('#tsf-days-grid').html(html);
        }

        // Init calendar rendering
        renderCalendar();

        // Add day click handlers via event delegation
        $('#tsf-days-grid').on('click', '.tsf-day:not(.padded)', function (e) {
            e.stopPropagation();
            var day = parseInt($(this).data('day'));
            var clickedDate = new Date(viewYear, viewMonth, day);

            if (activeInputField === 'start') {
                startDateObj = clickedDate;
                if (endDateObj && endDateObj < startDateObj) {
                    endDateObj = null;
                }
                activeInputField = 'end';
                updateActiveInputVisuals();
            } else {
                // activeInputField === 'end'
                if (startDateObj && clickedDate < startDateObj) {
                    startDateObj = clickedDate;
                    endDateObj = null;
                    activeInputField = 'end';
                } else {
                    endDateObj = clickedDate;
                }
                updateActiveInputVisuals();
            }

            // Update inputs and display
            var startFormatted = startDateObj ? formatDateString(startDateObj) : '';
            var endFormatted = endDateObj ? formatDateString(endDateObj) : '';

            $('#tsf-ngaykhoihanh').val(startFormatted);
            $('#tsf-start-input-display').val(startFormatted);
            $('#tsf-ngayketthuc').val(endFormatted);
            $('#tsf-end-input-display').val(endFormatted);

            if (startDateObj && endDateObj) {
                $('#tsf-when-display-text').text(startFormatted + ' - ' + endFormatted);
                setTimeout(function () {
                    $('#tsf-calendar-dropdown-menu').removeClass('show');
                }, 300);
            } else if (startDateObj) {
                $('#tsf-when-display-text').text(startFormatted);
            } else {
                $('#tsf-when-display-text').html('<span class="notranslate"><span class="menu-lang-en">When?</span><span class="menu-lang-vi">Khi nào?</span></span>');
            }

            renderCalendar();
        });

        // Add hover effect for range via event delegation
        $('#tsf-days-grid').on('mouseenter', '.tsf-day:not(.padded)', function () {
            if (activeInputField === 'end' && startDateObj && !endDateObj) {
                var hoverDay = parseInt($(this).data('day'));
                var hoverDateObj = new Date(viewYear, viewMonth, hoverDay);

                $('#tsf-days-grid .tsf-day:not(.padded)').each(function () {
                    var cellDay = parseInt($(this).data('day'));
                    var cellDateObj = new Date(viewYear, viewMonth, cellDay);

                    if (cellDateObj > startDateObj && cellDateObj <= hoverDateObj) {
                        $(this).addClass('in-range-hover'); // changed class to avoid conflict with actual in-range
                    } else {
                        if (!$(this).hasClass('active')) {
                            $(this).removeClass('in-range-hover');
                        }
                    }
                });
            }
        }).on('mouseleave', '.tsf-day:not(.padded)', function () {
            if (activeInputField === 'end' && startDateObj && !endDateObj) {
                $('#tsf-days-grid .tsf-day').removeClass('in-range-hover');
            }
        });

        // Clear Dates functionality
        $('#tsf-clear-dates').on('click', function (e) {
            e.stopPropagation();
            startDateObj = null;
            endDateObj = null;

            $('#tsf-start-input-display').val('');
            $('#tsf-end-input-display').val('');
            $('#tsf-ngaykhoihanh').val('');
            $('#tsf-ngayketthuc').val('');
            $('#tsf-when-display-text').html('<span class="notranslate"><span class="menu-lang-en">When?</span><span class="menu-lang-vi">Khi nào?</span></span>');

            activeInputField = 'start';
            updateActiveInputVisuals();
            renderCalendar();
        }).on('mouseenter', function () {
            $(this).css('background-color', '#fee2e2');
        }).on('mouseleave', function () {
            $(this).css('background-color', 'transparent');
        });

    });
})(jQuery);
