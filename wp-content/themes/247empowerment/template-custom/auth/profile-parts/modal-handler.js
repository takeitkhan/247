/**
 * Modern Posting Modal - JavaScript Handler
 * Phase 1: Design Improvements
 * Features: Character Counter, Emoji Picker, Image Handling, Live Preview
 */

(function($) {
    'use strict';

    // Configuration
    const CONFIG = {
        MAX_CHARS: 2000,
        IMAGE_SIZES: {
            PREVIEW: 'large',
            THUMBNAIL: 'medium'
        }
    };

    /**
     * Initialize Modal Components
     */
    $(document).ready(function() {
        initializeCharacterCounters();
        initializeFormattingToolbar();
        initializeEmojiPickers();
        initializeImageUpload();
        initializePrivacySelector();
        initializePostPreview();
        initializeScheduleDateTime();
        initializeFormSubmission();
    });

    /**
     * CHARACTER COUNTER
     */
    function initializeCharacterCounters() {
        const counters = [
            { textarea: '#post-content-instant', count: '#char-count', progress: '#char-progress-bar' },
            { textarea: '#post-content-schedule', count: '#char-count-schedule', progress: '#char-progress-bar-schedule' }
        ];

        counters.forEach(counter => {
            $(counter.textarea).on('input', function() {
                updateCharacterCounter($(this), counter.count, counter.progress);
            });
        });
    }

    function updateCharacterCounter($textarea, $countSelector, $progressSelector) {
        const text = $textarea.val();
        const length = text.length;
        const percentage = Math.min((length / CONFIG.MAX_CHARS) * 100, 100);

        // Update counter display
        $(document).find($countSelector).text(length);

        // Update progress bar
        const $progress = $(document).find($progressSelector);
        $progress.css('width', percentage + '%');

        // Change color based on usage
        $progress.removeClass('warning danger');
        if (length > CONFIG.MAX_CHARS * 0.8) {
            $progress.addClass('warning');
        }
        if (length > CONFIG.MAX_CHARS) {
            $progress.addClass('danger');
            $textarea.val(text.substring(0, CONFIG.MAX_CHARS));
        }
    }

    /**
     * TEXT FORMATTING TOOLBAR
     */
    function initializeFormattingToolbar() {
        $('.formatting-btn').on('click', function(e) {
            e.preventDefault();
            const format = $(this).data('format');
            applyTextFormat(format);
        });
    }

    function applyTextFormat(format) {
        // Get the active textarea
        const $activeTab = $('.tab-pane.active');
        const $textarea = $activeTab.find('.posting-textarea');
        
        if (!$textarea.length) return;

        const textarea = $textarea[0];
        const start = textarea.selectionStart;
        const end = textarea.selectionEnd;
        const text = textarea.value;
        const selectedText = text.substring(start, end);

        let newText = selectedText;
        let offset = 0;

        switch(format) {
            case 'bold':
                newText = `**${selectedText}**`;
                offset = 2;
                break;
            case 'italic':
                newText = `*${selectedText}*`;
                offset = 1;
                break;
            case 'underline':
                newText = `__${selectedText}__`;
                offset = 2;
                break;
            case 'ul':
                newText = selectedText ? `\n• ${selectedText}` : '\n• ';
                break;
            case 'ol':
                newText = selectedText ? `\n1. ${selectedText}` : '\n1. ';
                break;
        }

        textarea.value = text.substring(0, start) + newText + text.substring(end);
        textarea.selectionStart = textarea.selectionEnd = start + offset + (format === 'bold' || format === 'italic' || format === 'underline' ? selectedText.length : 0);
        textarea.focus();

        // Update character counter and preview
        $textarea.trigger('input');
    }

    /**
     * EMOJI PICKER
     */
    function initializeEmojiPickers() {
        // Instant post emoji button
        $('#emoji-btn').on('click', function(e) {
            e.preventDefault();
            toggleEmojiPicker($('#emoji-picker-container'));
        });

        // Schedule post emoji button
        $('.emoji-btn-schedule').on('click', function(e) {
            e.preventDefault();
            toggleEmojiPicker($('#emoji-picker-container-schedule'));
        });

        // Handle emoji selection
        $(document).on('emoji-click', function(e) {
            const emoji = e.detail.unicode;
            insertEmojiToTextarea(emoji);
        });
    }

    function toggleEmojiPicker($container) {
        $container.toggleClass('d-none');
        $container.slideToggle(200);
    }

    function insertEmojiToTextarea(emoji) {
        const $activeTab = $('.tab-pane.active');
        const $textarea = $activeTab.find('.posting-textarea');
        
        if (!$textarea.length) return;

        const textarea = $textarea[0];
        const start = textarea.selectionStart;
        const end = textarea.selectionEnd;
        const text = textarea.value;

        textarea.value = text.substring(0, start) + emoji + text.substring(end);
        textarea.selectionStart = textarea.selectionEnd = start + emoji.length;
        textarea.focus();

        // Update counter and preview
        $textarea.trigger('input');
    }

    /**
     * IMAGE UPLOAD & PREVIEW
     */
    function initializeImageUpload() {
        // File input change
        $('#photoUpload, #photoUpload-schedule').on('change', function() {
            handleImageUpload($(this));
        });

        // Drag and drop
        $('.image-upload-box, #image-upload-box, #image-upload-box-schedule').on('dragover', function(e) {
            e.preventDefault();
            $(this).addClass('dragover');
        }).on('dragleave', function() {
            $(this).removeClass('dragover');
        }).on('drop', function(e) {
            e.preventDefault();
            $(this).removeClass('dragover');
            
            const files = e.originalEvent.dataTransfer.files;
            if (files.length > 0) {
                const fileInput = $(this).find('input[type="file"]')[0];
                fileInput.files = files;
                $(fileInput).trigger('change');
            }
        });

        // Remove image button
        $(document).on('click', '.remove-image-btn', function(e) {
            e.preventDefault();
            $(this).closest('.image-preview-container').slideUp(200, function() {
                $(this).remove();
                const $uploadBox = $(this).closest('.col-lg-6').find('.image-upload-box');
                $uploadBox.slideDown(200);
            });
            
            // Clear file input
            const $activeTab = $('.tab-pane.active');
            $activeTab.find('input[type="file"]').val('');
        });
    }

    function handleImageUpload($fileInput) {
        const file = $fileInput[0].files[0];
        if (!file) return;

        // Validate file
        const validTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'video/mp4', 'video/webm'];
        if (!validTypes.includes(file.type)) {
            alert('Please upload a valid image or video file');
            return;
        }

        // Check file size (max 50MB)
        if (file.size > 50 * 1024 * 1024) {
            alert('File size must be less than 50MB');
            return;
        }

        // Get container for preview
        const $activeTab = $('.tab-pane.active');
        const $container = $activeTab.find('.image-preview-container');
        const $uploadBox = $activeTab.find('.image-upload-box');

        // Read and display image
        const reader = new FileReader();
        reader.onload = function(e) {
            $uploadBox.slideUp(200);
            
            // Update image preview
            $container.find('img').attr('src', e.target.result);
            $container.find('#image-name, #image-name-schedule').text(file.name + ' (' + formatFileSize(file.size) + ')');
            
            if (!$container.is(':visible')) {
                $container.slideDown(200);
            }

            // Update post preview
            updatePostPreview($activeTab.find('.posting-textarea'));
        };
        reader.readAsDataURL(file);
    }

    function formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
    }

    /**
     * PRIVACY SELECTOR
     */
    function initializePrivacySelector() {
        // Visual feedback for selected privacy option
        $('.privacy-option input[type="radio"]').on('change', function() {
            $(this).closest('.privacy-options').find('.privacy-label').removeClass('selected');
            $(this).closest('.privacy-option').find('.privacy-label').addClass('selected');
        });
    }

    /**
     * POST PREVIEW
     */
    function initializePostPreview() {
        $('#post-content-instant, #post-content-schedule').on('input', function() {
            updatePostPreview($(this));
        });

        // Update preview on privacy change
        $('.privacy-option input[type="radio"]').on('change', function() {
            const $activeTab = $('.tab-pane.active');
            updatePostPreview($activeTab.find('.posting-textarea'));
        });
    }

    function updatePostPreview($textarea) {
        const $activeTab = $textarea.closest('.tab-pane');
        const content = $textarea.val();
        const $preview = $activeTab.find('.preview-content');

        if (content.trim()) {
            const formattedContent = formatPreviewContent(content);
            $preview.html(formattedContent).addClass('has-content');
        } else {
            $preview.html('<p class="text-muted text-center py-4">Your post preview will appear here</p>').removeClass('has-content');
        }

        // Add image preview if exists
        const $imagePreview = $activeTab.find('#image-preview, #image-preview-schedule');
        if ($imagePreview.is(':visible')) {
            const $imageContainer = $('<div class="mt-3"></div>');
            $imageContainer.append($imagePreview.clone());
            $preview.find('p').after($imageContainer);
        }

        // Show privacy info
        const selectedPrivacy = $activeTab.find('input[name="post_privacy"]:checked').val();
        const privacyLabel = getPrivacyLabel(selectedPrivacy);
        $preview.append(`<div class="mt-2 pt-2 border-top"><small class="text-muted"><i class="bi bi-shield-check me-1"></i>${privacyLabel}</small></div>`);
    }

    function formatPreviewContent(content) {
        let formatted = content
            .replace(/\n/g, '<br>')
            .replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>')
            .replace(/\*(.+?)\*/g, '<em>$1</em>')
            .replace(/__(.+?)__/g, '<u>$1</u>');
        
        return `<p>${formatted}</p>`;
    }

    function getPrivacyLabel(privacy) {
        const labels = {
            'only_me': 'Only Me - Private',
            'referral_partners': 'Referral Partners Only',
            'public': 'Public - Everyone'
        };
        return labels[privacy] || 'Public';
    }

    /**
     * SCHEDULE DATE & TIME
     */
    function initializeScheduleDateTime() {
        const $dateInput = $('#schedule-date');
        const $timeInput = $('#schedule-time');
        const $timezoneInput = $('#schedule-timezone');

        // Set minimum date to today
        const today = new Date().toISOString().split('T')[0];
        $dateInput.attr('min', today);

        // Update scheduled time preview
        $dateInput.on('change', updateSchedulePreview);
        $timeInput.on('change', updateSchedulePreview);
        $timezoneInput.on('change', updateSchedulePreview);
    }

    function updateSchedulePreview() {
        const date = $('#schedule-date').val();
        const time = $('#schedule-time').val();
        const timezone = $('#schedule-timezone').val();
        const $preview = $('#schedule-preview-time');

        if (date && time) {
            const dateObj = new Date(`${date}T${time}`);
            const formatted = dateObj.toLocaleString('en-US', {
                year: 'numeric',
                month: 'short',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit',
                hour12: true
            });
            
            $preview.text(`${formatted} ${timezone}`);

            // Store timestamp
            const timestamp = Math.floor(dateObj.getTime() / 1000);
            $('#schedule_timestamp').val(timestamp);
        } else {
            $preview.text('Select a date and time');
        }
    }

    /**
     * FORM SUBMISSION
     */
    function initializeFormSubmission() {
        $('#create-post-form-redesigned').on('submit', function(e) {
            e.preventDefault();

            const $form = $(this);
            const $activeTab = $('.tab-pane.active');
            const isScheduled = $activeTab.attr('id') === 'schedulePost';

            // Validate required fields
            const content = $activeTab.find('.posting-textarea').val().trim();
            if (!content) {
                alert('Please write something before posting');
                return;
            }

            if (isScheduled) {
                const date = $('#schedule-date').val();
                const time = $('#schedule-time').val();
                if (!date || !time) {
                    alert('Please select a date and time for scheduled post');
                    return;
                }
            }

            // Disable submit button
            const $submitBtn = $activeTab.find('.posting-submit-btn');
            $submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Processing...');

            // Submit form via AJAX
            submitPostForm($form, isScheduled, function() {
                // Reset form
                $form[0].reset();
                $('#createPostModalRedesigned').modal('hide');
                $submitBtn.prop('disabled', false).html($activeTab.attr('id') === 'instantPost' ? '<i class="bi bi-send me-2"></i>Share Now' : '<i class="bi bi-calendar-event me-2"></i>Schedule Post');

                // Show success message
                showSuccessMessage(isScheduled ? 'Post scheduled successfully!' : 'Post published successfully!');

                // Reload posts (if using existing post display)
                if (typeof location !== 'undefined') {
                    setTimeout(() => location.reload(), 1500);
                }
            });
        });
    }

    function submitPostForm($form, isScheduled, callback) {
        const formData = new FormData($form[0]);
        
        $.ajax({
            type: 'POST',
            url: ajaxurl,
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    callback();
                } else {
                    alert('Error: ' + (response.data || 'Unknown error'));
                }
            },
            error: function() {
                alert('Error submitting post. Please try again.');
            }
        });
    }

    function showSuccessMessage(message) {
        const $alert = $(`
            <div class="alert alert-success alert-dismissible fade show position-fixed top-0 end-0 m-3" role="alert" style="z-index: 9999;">
                <i class="bi bi-check-circle me-2"></i>${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `);
        $('body').append($alert);

        setTimeout(() => {
            $alert.fadeOut(300, function() {
                $(this).remove();
            });
        }, 3000);
    }

})(jQuery);
