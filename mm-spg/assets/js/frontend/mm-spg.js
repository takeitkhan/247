jQuery(document).ready(function ($) {
    'use strict';

    if (typeof MM_SPG === 'undefined') {
        return;
    }

    /**
     * Handle "Resume Guide" click
     * Triggers the transition from Paused -> Phase 3 (Active)
     */
    $(document).on('click', '.mm-spg-resume-btn', function (e) {
        e.preventDefault();
        
        var $btn = $(this);
        var originalText = $btn.text();
        $btn.prop('disabled', true).text('Resuming...');

        // 1. Update state to Phase 3 / Active
        $.ajax({
            url: MM_SPG.ajax_url,
            type: 'POST',
            dataType: 'json',
            data: {
                action: 'mm_spg_update_guide_state',
                nonce: MM_SPG.nonce,
                phase: 3,       // Set Phase to 3
                status: 'active' // Set Status to active
            },
            success: function (response) {
                if (response.success) {
                    // 2. Fetch the new Phase 3 steps
                    mm_spg_fetch_steps();
                } else {
                    $btn.prop('disabled', false).text(originalText);
                }
            },
            error: function () {
                $btn.prop('disabled', false).text(originalText);
            }
        });
    });

    function mm_spg_fetch_steps() {
        $.ajax({
            url: MM_SPG.ajax_url,
            type: 'POST',
            dataType: 'json',
            data: {
                action: 'mm_spg_fetch_steps',
                nonce: MM_SPG.nonce
            },
            success: function (response) {
                if (response.success && response.data.steps) {
                    // 3. Inject steps (Trigger an event for your Modal/Guide renderer to pick up)
                    $(document).trigger('mm_spg_steps_loaded', [response.data.steps]);
                    
                    // Optional: Hide the resume button or UI
                    $('.mm-spg-resume-container').fadeOut();
                }
            }
        });
    }

    // ======================================================
    // GUIDE RENDERER LOGIC
    // ======================================================

    var guideSteps = [];
    var currentStepIndex = 0;

    // DOM Elements (Adjust selectors to match your modal.php HTML)
    var $modal      = $('#mm-spg-modal'); 
    var $modalTitle = $modal.find('.mm-spg-title');
    var $modalBody  = $modal.find('.mm-spg-body');
    var $nextBtn    = $modal.find('.mm-spg-next');
    var $prevBtn    = $modal.find('.mm-spg-prev');
    var $closeBtn   = $modal.find('.mm-spg-close');

    /**
     * Listen for steps loaded event
     */
    $(document).on('mm_spg_steps_loaded', function (e, steps) {
        if (!steps || !steps.length) {
            return;
        }

        guideSteps = steps;
        currentStepIndex = 0;

        // Show the modal
        $modal.removeClass('mm-spg-hidden').fadeIn();

        // Render the first step
        mm_spg_render_step(currentStepIndex);
    });

    /**
     * Render a specific step
     */
    function mm_spg_render_step(index) {
        var step = guideSteps[index];
        if (!step) return;

        // 1. Update Title
        $modalTitle.text(step.title);

        // 2. Update Body
        var html = '';
        if (step.blocks && step.blocks.length) {
            $.each(step.blocks, function(i, block) {
                switch (block.type) {
                    case 'text':
                        html += '<p>' + block.content + '</p>';
                        break;
                    case 'shortcode':
                        // PHP has processed this into 'content'
                        html += '<div class="mm-spg-shortcode-wrapper">' + (block.content || '') + '</div>';
                        break;
                    case 'video':
                        html += '<div class="mm-spg-video-responsive" style="margin:15px 0;"><iframe src="' + block.src + '" frameborder="0" allowfullscreen style="width:100%; height:300px;"></iframe></div>';
                        break;
                    case 'redirect':
                        html += '<div class="mm-spg-action-row" style="text-align:center; margin-top:15px;"><a href="' + block.url + '" class="button button-primary" target="_blank">Open Link &rarr;</a></div>';
                        break;
                }
            });
        }
        $modalBody.html(html);

        // 3. Update Navigation Buttons
        if (index === 0) {
            $prevBtn.hide();
        } else {
            $prevBtn.show();
        }

        if (index === guideSteps.length - 1) {
            $nextBtn.text('Finish');
        } else {
            $nextBtn.text('Next');
        }
    }

    // Navigation Events
    $nextBtn.on('click', function(e) {
        e.preventDefault();
        if (currentStepIndex < guideSteps.length - 1) {
            currentStepIndex++;
            mm_spg_render_step(currentStepIndex);
        } else {
            $modal.fadeOut(); // Finish
        }
    });

    $prevBtn.on('click', function(e) {
        e.preventDefault();
        if (currentStepIndex > 0) {
            currentStepIndex--;
            mm_spg_render_step(currentStepIndex);
        }
    });

    $closeBtn.on('click', function(e) {
        e.preventDefault();
        $modal.fadeOut();
    });

    // ======================================================
    // INITIALIZATION & LAUNCHER
    // ======================================================

    var $launcher = $('#mm-spg-launcher');

    // Handle Launcher Click
    $launcher.on('click', function(e) {
        e.preventDefault();
        // If steps are already loaded, just show the modal
        if (guideSteps.length > 0) {
            $modal.removeClass('mm-spg-hidden').fadeIn();
        } else {
            // Otherwise fetch them (which will trigger the open event)
            mm_spg_fetch_steps();
        }
    });

    // Check state on page load
    function mm_spg_init() {
        $.ajax({
            url: MM_SPG.ajax_url,
            type: 'POST',
            dataType: 'json',
            data: {
                action: 'mm_spg_get_state',
                nonce: MM_SPG.nonce
            },
            success: function (response) {
                if (response.success) {
                    // Show the launcher button
                    $launcher.removeClass('mm-spg-hidden');

                    // If the guide is currently active, auto-open it
                    if (response.data.status === 'active') {
                        mm_spg_fetch_steps();
                    }
                }
            }
        });
    }

    mm_spg_init();
});