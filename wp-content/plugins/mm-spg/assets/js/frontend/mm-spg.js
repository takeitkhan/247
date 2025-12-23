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
        if (step.blocks) {
            $('.mm-spg-body').html(renderBlocks(step.blocks));
        } else if (step.message) {
            // backward compatibility
            $('.mm-spg-body').html(`<p>${step.message}</p>`);
        }

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


    function renderBlocks(blocks) {
        let html = '';

        blocks.forEach(block => {

            switch (block.type) {

                case 'text':
                    html += `<div class="mm-spg-text">${block.content}</div>`;
                    break;

                case 'video':
                    html += `
                    <div class="mm-spg-video">
                        <iframe src="${block.src}" frameborder="0" allowfullscreen></iframe>
                    </div>
                `;
                    break;

                case 'shortcode':
                    const id = 'mm-spg-shortcode-' + Math.random().toString(36).substr(2, 9);

                    html += `<div class="mm-spg-shortcode" id="${id}">Loading...</div>`;

                    // AJAX render
                    $.post(MM_SPG.ajax_url, {
                        action: 'mm_spg_render_shortcode',
                        shortcode: block.shortcode
                    }, function (res) {
                        if (res.success) {
                            $('#' + id).html(res.data.html);
                        } else {
                            $('#' + id).html('<p>Error loading form.</p>');
                        }
                    });

                    break;

                case 'html':
                    html += block.content;
                    break;
            }
        });

        return html;
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

            // If step requires reading time
            if (step.min_read_time) {
                const waitUntil = Math.floor(Date.now() / 1000) + step.min_read_time;

                $.post(MM_SPG.ajax_url, {
                    action: 'mm_spg_set_wait',
                    step: currentStep + 1,
                    wait_until: waitUntil
                });
            } else {
                currentStep++;
                saveState('active');
            }

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

            // 🔹 Phase 1 always blocks everything
            if (!avatar) {
                showModal();
                return;
            }

            // 🔹 WAITING STATE (⏱️ reading time enforcement)
            if (res.data.status === 'waiting' && res.data.wait_until) {
                const now = Math.floor(Date.now() / 1000);

                if (now >= res.data.wait_until) {
                    saveState('active');
                    showModal();
                } else {
                    setTimeout(() => {
                        saveState('active');
                        showModal();
                    }, (res.data.wait_until - now) * 1000);
                }

                return; // ⛔ stop further logic
            }

            // 🔹 Normal states
            if (res.data.status === 'active') {
                showModal();
            } else {
                updateLauncher(res.data.status);
            }
        });
    });


})(jQuery);
