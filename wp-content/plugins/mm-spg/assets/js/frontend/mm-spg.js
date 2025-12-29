console.log('MM-SPG JS LOADED');
(function ($) {
    'use strict';    
    let steps = [];
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
            return;
        }

        // paused or stopped
        $('#mm-spg-launcher')
            .removeClass('mm-spg-hidden')
            .text(status === 'paused' ? 'Resume Guide' : 'Start Guide');
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
                    const id = 'mm-spg-shortcode-' + Math.random().toString(36).substring(2);

                    html += `<div class="mm-spg-shortcode" id="${id}">Loading…</div>`;

                    $.post(MM_SPG.ajax_url, {
                        action: 'mm_spg_render_shortcode',
                        nonce: MM_SPG.nonce,
                        shortcode: block.shortcode
                    }, function (res) {
                        if (res.success) {
                            $('#' + id).html(res.data.html);
                        } else {
                            $('#' + id).html('<div class="alert alert-danger">Failed to load form.</div>');
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
            nonce: MM_SPG.nonce,
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
            nonce: MM_SPG.nonce,
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
                    nonce: MM_SPG.nonce,
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
    $(document).on('click', '#mm-spg-launcher, .mm-spg-start', function () {

        $.post(MM_SPG.ajax_url, {
            action: 'mm_spg_set_state',
            nonce: MM_SPG.nonce,
            status: 'active',
            step: 0
        }, function () {

            // 🔥 ALWAYS reload steps AFTER state change
            $.post(MM_SPG.ajax_url, {
                action: 'mm_spg_get_steps'
            }, function (res) {

                if (!res.success) {
                    console.error('Failed to load steps');
                    return;
                }

                steps = res.data.steps;
                currentStep = 0;

                showModal();
                renderStep();
            });
        });
    });

    function loadGuideState() {
        $.post(MM_SPG.ajax_url, {
            action: 'mm_spg_get_state'
        }, function (res) {

            if (!res.success) return;

            guideState = res.data;

            if (guideState.status === 'active') {

                // 🔥 load steps dynamically
                $.post(MM_SPG.ajax_url, {
                    action: 'mm_spg_get_steps'
                }, function (res2) {

                    if (!res2.success) return;

                    steps = res2.data.steps;
                    currentStep = guideState.step || 0;

                    showModal();
                    renderStep();
                });
            }
        });
    }


    $(document).on('change', '.mm-spg-interest-form input[type=checkbox]', function () {
        $(this).closest('.d-flex').find('select')
            .prop('disabled', !this.checked);
    }).trigger('change');

    /* ========================
        INTERESTS SAVE
    ========================== */
    $(document).on('submit', '.mm-spg-interest-form', function (e) {
        e.preventDefault();

        const $form = $(this);
        const formData = $form.serialize();

        $.post(MM_SPG.ajax_url, {
            action: 'mm_spg_save_interests',
            nonce: $form.find('[name="mm_spg_interest_nonce"]').val(),
            ...Object.fromEntries(new URLSearchParams(formData))
        }, function (res) {

            $form.find('.alert').remove();

            if (res.success) {
                $form.prepend(
                    '<div class="alert alert-success mb-2">' + res.data + '</div>'
                );

                // OPTIONAL: auto-advance guide
                // currentStep++;
                // saveState('active');
                // renderStep();

            } else {
                $form.prepend(
                    '<div class="alert alert-danger mb-2">' + res.data + '</div>'
                );
            }
        });
    });

    /* =====================
    SOCIAL MANAGEMENT
    =================== */
    // Add new social link row
    $(document).on('click', '#mm-spg-add-social-link', function () {

        const index = $('#social-links-group .social-link-row').length;

        const row = `
        <div class="align-items-center mb-2 row g-2 social-link-row">
            <div class="col-md-3">
                <select name="links[${index}][platform]" class="form-select">
                    <option value="facebook">Facebook</option>
                    <option value="instagram">Instagram</option>
                    <option value="linkedin">LinkedIn</option>
                    <option value="twitter">Twitter / X</option>
                    <option value="youtube">YouTube</option>
                    <option value="website">Website</option>
                </select>
            </div>

            <div class="col-md-3">
                <input type="text"
                       name="links[${index}][label]"
                       class="form-control"
                       placeholder="Custom label">
            </div>

            <div class="col-md-4">
                <input type="url"
                       name="links[${index}][url]"
                       class="form-control"
                       placeholder="https://example.com">
            </div>

            <div class="col-md-2">
                <button type="button"
                        class="w-100 btn btn-danger remove-link">
                    Remove
                </button>
            </div>
        </div>
    `;

        $('#social-links-group').append(row);
    });

    // Remove social link row
    $(document).on('click', '.remove-link', function () {
        $(this).closest('.social-link-row').remove();
    });


    $(document).on('submit', '.mm-spg-social-links-form', function (e) {
        e.preventDefault();

        const $form = $(this);
        const formData = $form.serialize();

        $.post(MM_SPG.ajax_url, {
            action: 'mm_spg_save_social_links',
            nonce: $form.find('[name="mm_spg_links_nonce"]').val(),
            ...Object.fromEntries(new URLSearchParams(formData))
        }, function (res) {

            $form.find('.alert').remove();

            if (res.success) {
                $form.prepend(
                    '<div class="alert alert-success mb-2">' + res.data + '</div>'
                );

                // OPTIONAL: auto-advance guide
                // currentStep++;
                // saveState('active');
                // renderStep();

            } else {
                $form.prepend(
                    '<div class="alert alert-danger mb-2">' + res.data + '</div>'
                );
            }
        });
    });

    /* =====================
    ADDITIONAL PROFILE
    =================== */
    $(document).on('submit', '.mm-spg-additional-profile-form', function (e) {
        e.preventDefault();

        const $form = $(this);

        // Keywords
        const keywords = [];
        $form.find('#keyword-tags .keyword-tag').each(function () {
            keywords.push(
                $(this).clone().children().remove().end().text().trim()
            );
        });
        $form.find('.mm-spg-keywords-hidden').val(keywords.join(', '));

        // Hashtags
        const hashtags = [];
        $form.find('#hashtag-tags .hashtag-tag').each(function () {
            hashtags.push(
                $(this).clone().children().remove().end().text().trim()
            );
        });
        $form.find('.mm-spg-hashtags-hidden').val(hashtags.join(', '));

        console.log('KEYWORDS:', $form.find('#keywords-hidden').val());
        console.log('HASHTAGS:', $form.find('#hashtags-hidden').val());

        $.post(MM_SPG.ajax_url, {
            action: 'mm_spg_save_additional_profile',
            nonce: $form.find('[name="mm_spg_additional_nonce"]').val(),
            designation: $form.find('[name="designation"]').val(),
            about_me_short: $form.find('[name="about_me_short"]').val(),
            user_keywords: $form.find('.mm-spg-keywords-hidden').val(),
            user_hashtags: $form.find('.mm-spg-hashtags-hidden').val()
        }, function (res) {
            $form.find('.alert').remove();

            if (res.success) {
                $form.prepend('<div class="alert alert-success mb-2">' + res.data + '</div>');
            } else {
                $form.prepend('<div class="alert alert-danger mb-2">' + res.data + '</div>');
            }
        });
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
