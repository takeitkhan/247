(function ($) {
    'use strict';

    let steps = MM_SPG.steps || [];
    let currentStep = 0;
    let avatar = MM_SPG.avatar || '';

    /* =========================
       LAUNCHER
    ========================= */
    function updateLauncher(status) {
        if (!avatar) {
            $('#mm-spg-launcher').addClass('mm-spg-hidden');
            return;
        }

        if (status === 'active') {
            $('#mm-spg-launcher').addClass('mm-spg-hidden');
        } else {
            $('#mm-spg-launcher')
                .removeClass('mm-spg-hidden')
                .text(status === 'paused' ? 'Resume Guide' : 'Start Guide');
        }
    }

    /* =========================
       RENDER STEP
    ========================= */
    function renderStep() {

        // -------- Phase 1: Avatar --------
        if (!avatar) {
            $('.mm-spg-title').text('Choose your avatar');
            $('.mm-spg-body').html(`
                <p>Please select an avatar to continue.</p>
                <div class="mm-spg-avatar-choice">
                    <div class="mm-spg-avatar-btn" data-avatar="male">👨</div>
                    <div class="mm-spg-avatar-btn" data-avatar="female">👩</div>
                </div>
            `);

            $('.mm-spg-avatar').html('');
            $('.mm-spg-next').hide();
            $('.mm-spg-pause').hide();
            clearHighlight();
            return;
        }

        // -------- Phase 2+ Steps --------
        let step = steps[currentStep];

        if (!step) {
            hideModal();
            saveState('stopped');
            updateLauncher('stopped');
            clearHighlight();
            return;
        }

        $('.mm-spg-title').text(step.title || '');
        $('.mm-spg-body').html(`<p>${step.message || ''}</p>`);
        $('.mm-spg-avatar').html(avatar === 'male' ? '👨' : '👩');

        $('.mm-spg-next').show();
        $('.mm-spg-pause').show();
        $('.mm-spg-avatar-choice').remove();

        if (step.video) {
            $('.mm-spg-body').append(`
                <div class="mm-spg-video">
                    <iframe src="${step.video}" frameborder="0" allowfullscreen></iframe>
                </div>
            `);
        }

        if (step.highlight) {
            applyHighlight(step.highlight);
        } else {
            clearHighlight();
        }

        $('.mm-spg-next').text(step.redirect ? 'Go' : 'Next');
    }

    /* =========================
       MODAL
    ========================= */
    function showModal() {
        $('#mm-spg-modal').removeClass('mm-spg-hidden');
        updateLauncher('active');
        renderStep();
    }

    function hideModal() {
        $('#mm-spg-modal').addClass('mm-spg-hidden');
    }

    /* =========================
       STATE
    ========================= */
    function saveState(status) {
        $.post(MM_SPG.ajax_url, {
            action: 'mm_spg_set_state',
            status: status,
            step: currentStep
        });
    }

    function clearHighlight() {
        $('.mm-spg-highlighted').removeClass('mm-spg-highlighted');
        $('body').removeClass('mm-spg-highlighting');
    }

    function applyHighlight(selector) {
        clearHighlight();
        let $el = $(selector).first();
        if ($el.length) {
            $el.addClass('mm-spg-highlighted');
            $('body').addClass('mm-spg-highlighting');
        }
    }

    /* =========================
       EVENTS
    ========================= */

    // Avatar select
    $(document).on('click', '.mm-spg-avatar-btn', function () {
        let selected = $(this).data('avatar');

        $.post(MM_SPG.ajax_url, {
            action: 'mm_spg_save_avatar',
            avatar: selected
        }, function (res) {
            if (res.success) {
                avatar = selected;
                currentStep = 0;
                saveState('active');
                showModal();
            }
        });
    });

    // Next
    $(document).on('click', '.mm-spg-next', function () {
        let step = steps[currentStep];
        clearHighlight();

        if (step && step.redirect) {
            saveState('stopped');
            window.location.href = step.redirect;
            return;
        }

        currentStep++;
        saveState('active');
        renderStep();
    });

    // Pause / Close
    $(document).on('click', '.mm-spg-pause, .mm-spg-close', function () {
        clearHighlight();
        saveState('paused');
        hideModal();
        updateLauncher('paused');
    });

    // Launcher click
    $(document).on('click', '#mm-spg-launcher', function () {
        saveState('active');
        showModal();
    });

    /* =========================
       INITIAL LOAD
    ========================= */
    $(document).ready(function () {
        $.post(MM_SPG.ajax_url, { action: 'mm_spg_get_state' }, function (res) {
            if (!res.success) return;

            currentStep = res.data.step || 0;

            if (!avatar) {
                showModal(); // Phase 1 forces open
                return;
            }

            if (res.data.status === 'active') {
                showModal();
            } else {
                updateLauncher(res.data.status);
            }
        });
    });

})(jQuery);
