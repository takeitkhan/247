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
                <div class="mm-spg-avatar-choice">
                    <div class="mm-spg-avatar-btn" data-avatar="male">
                        <img src="${MM_SPG.avatar_male_url}" alt="Male Avatar">
                    </div>
                    <div class="mm-spg-avatar-btn" data-avatar="female">
                        <img src="${MM_SPG.avatar_female_url}" alt="Female Avatar">
                    </div>
                </div>
                <p class="text-center">Please select a Guide to Empower Your Journey.</p>
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
            // Phase 2 just ended → auto start Phase 3
            if (steps[currentStep - 1]?.phase === 2) {

                $.post(MM_SPG.ajax_url, {
                    action: 'mm_spg_prepare_phase_3',
                    nonce: MM_SPG.nonce
                }, function () {
                    window.location.reload();
                });

                return;
            }

            hideModal();
            saveState('stopped');
            updateLauncher('stopped');
            return;
        }



        $('.mm-spg-title').text(step.title || '');
        if (step.blocks) {
            $('.mm-spg-body').html(renderBlocks(step.blocks));
        } else if (step.message) {
            // backward compatibility
            $('.mm-spg-body').html(`<p>${step.message}</p>`);
        }

        $('.mm-spg-avatar').html(`
                <img
                    src="${avatar === 'male' ? MM_SPG.avatar_male_url : MM_SPG.avatar_female_url}"
                    alt="${avatar === 'male' ? 'Male Avatar' : 'Female Avatar'}"
                    class="mm-spg-selected-avatar"
                >
            `);


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

        /** Phase 3 */

        if (step.phase === 3) {
            $('.mm-spg-pause').hide();
        } else {
            $('.mm-spg-pause').show();
        }
    }

    function nl2br(str) {
        if (!str) return '';
        return str
            .replace(/\r\n/g, '\n')
            .replace(/\n{2,}/g, '</p><p>')
            .replace(/\n/g, '<br>');
    }

    function renderBlocks(blocks) {
        let html = '';

        blocks.forEach(block => {

            switch (block.type) {

                case 'text':
                    html += `
                    <div class="mm-spg-text">
                        <p>${nl2br(block.content)}</p>
                    </div>
                `;
                    break;

                case 'html':
                    html += `<div class="mm-spg-html">${block.content}</div>`;
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
            }
        });

        return html;
    }





    /* =========================
       MODAL
    ========================= */
    function openModal() {
        $('#mm-spg-modal').removeClass('mm-spg-hidden');
    }

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

    function findPhaseStart(phase) {
        return steps.findIndex(step => step.phase === phase);
    }

    function getPhaseStartIndex(phase) {
        for (let i = 0; i < steps.length; i++) {
            if (steps[i].phase === phase) {
                return i;
            }
        }
        return -1;
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
    // $(document).on('click', '.mm-spg-next', function () {
    //     let step = steps[currentStep];
    //     clearHighlight();

    //     /* =========================
    //        SAVE INTERESTS (IF PRESENT)
    //     ========================= */
    //     const $interestForm = $('.mm-spg-interest-form');
    //     if ($interestForm.length) {
    //         const formData = $interestForm.serialize();

    //         $.post(MM_SPG.ajax_url, {
    //             action: 'mm_spg_save_interests',
    //             nonce: $interestForm.find('[name="mm_spg_interest_nonce"]').val(),
    //             ...Object.fromEntries(new URLSearchParams(formData))
    //         });
    //     }

    //     /* =========================
    //        SAVE ADDITIONAL PROFILE (IF PRESENT)
    //     ========================= */
    //     const $profileForm = $('.mm-spg-additional-profile-form');
    //     if ($profileForm.length) {

    //         // ensure hidden fields are populated (keywords / hashtags)
    //         const keywords = [];
    //         $('#keyword-tags .keyword-tag').each(function () {
    //             keywords.push(
    //                 $(this).clone().children().remove().end().text().trim()
    //             );
    //         });
    //         $('#keywords-hidden').val(keywords.join(', '));

    //         const hashtags = [];
    //         $('#hashtag-tags .hashtag-tag').each(function () {
    //             hashtags.push(
    //                 $(this).clone().children().remove().end().text().trim()
    //             );
    //         });
    //         $('#hashtags-hidden').val(hashtags.join(', '));

    //         const formData = $profileForm.serialize();

    //         $.post(MM_SPG.ajax_url, {
    //             action: 'mm_spg_save_additional_profile',
    //             nonce: $profileForm.find('[name="mm_spg_additional_nonce"]').val(),
    //             ...Object.fromEntries(new URLSearchParams(formData))
    //         });
    //     }

    //     /* =========================
    //     SAVE SOCIAL LINKS (IF PRESENT)
    //     ========================= */
    //     const $socialForm = $('.mm-spg-social-links-form');
    //     if ($socialForm.length) {

    //         const formData = $socialForm.serialize();

    //         $.post(MM_SPG.ajax_url, {
    //             action: 'mm_spg_save_social_links',
    //             nonce: $socialForm.find('[name="mm_spg_links_nonce"]').val(),
    //             ...Object.fromEntries(new URLSearchParams(formData))
    //         });
    //     }


    //     /* =========================
    //        PHASE 2 → PHASE 3
    //     ========================= */
    //     if (
    //         step.phase === 2 &&
    //         MM_SPG.phase_3_start_index !== null &&
    //         currentStep === MM_SPG.phase_3_start_index - 1
    //     ) {
    //         $.post(MM_SPG.ajax_url, {
    //             action: 'mm_spg_complete_phase_2',
    //             nonce: MM_SPG.nonce
    //         }, function () {
    //             currentStep = MM_SPG.phase_3_start_index;
    //             saveState('active');
    //             renderStep();
    //         });
    //         return;
    //     }

    //     /* =========================
    //        PHASE 3 COMPLETION
    //     ========================= */
    //     if (step.phase === 3 && currentStep >= steps.length - 1) {
    //         saveState('stopped');
    //         hideModal();
    //         updateLauncher('stopped');
    //         return;
    //     }

    //     /* =========================
    //        REDIRECT
    //     ========================= */
    //     if (step.redirect) {
    //         window.location.href = step.redirect;
    //         return;
    //     }

    //     /* =========================
    //        NORMAL STEP
    //     ========================= */
    //     currentStep++;
    //     saveState('active');
    //     renderStep();
    // });

    $(document).on('click', '.mm-spg-next', function () {
        let step = steps[currentStep];
        clearHighlight();

        /* =========================
           SAVE FORMS (NON-BLOCKING)
        ========================= */

        const $interestForm = $('.mm-spg-interest-form');
        if ($interestForm.length) {
            const formData = $interestForm.serialize();
            $.post(MM_SPG.ajax_url, {
                action: 'mm_spg_save_interests',
                nonce: $interestForm.find('[name="mm_spg_interest_nonce"]').val(),
                ...Object.fromEntries(new URLSearchParams(formData))
            });
        }

        const $profileForm = $('.mm-spg-additional-profile-form');
        if ($profileForm.length) {

            const keywords = [];
            $('#keyword-tags .keyword-tag').each(function () {
                keywords.push($(this).clone().children().remove().end().text().trim());
            });
            $('#keywords-hidden').val(keywords.join(', '));

            const hashtags = [];
            $('#hashtag-tags .hashtag-tag').each(function () {
                hashtags.push($(this).clone().children().remove().end().text().trim());
            });
            $('#hashtags-hidden').val(hashtags.join(', '));

            const formData = $profileForm.serialize();
            $.post(MM_SPG.ajax_url, {
                action: 'mm_spg_save_additional_profile',
                nonce: $profileForm.find('[name="mm_spg_additional_nonce"]').val(),
                ...Object.fromEntries(new URLSearchParams(formData))
            });
        }

        const $socialForm = $('.mm-spg-social-links-form');
        if ($socialForm.length) {
            const formData = $socialForm.serialize();
            $.post(MM_SPG.ajax_url, {
                action: 'mm_spg_save_social_links',
                nonce: $socialForm.find('[name="mm_spg_links_nonce"]').val(),
                ...Object.fromEntries(new URLSearchParams(formData))
            });
        }

        /* =========================
           🔒 HARD GUARD: PHASE 2 IS DONE
        ========================= */
        if (
            step &&
            step.phase === 2 &&
            currentStep >= MM_SPG.phase_3_start_index
        ) {
            currentStep = MM_SPG.phase_3_start_index;
            renderStep();
            return;
        }

        /* =========================
           PHASE 2 → PHASE 3 (ONE-WAY)
        ========================= */
        if (
            step.phase === 2 &&
            currentStep === MM_SPG.phase_3_start_index - 1
        ) {
            $.post(MM_SPG.ajax_url, {
                action: 'mm_spg_complete_phase_2',
                nonce: MM_SPG.nonce
            }, function () {
                currentStep = MM_SPG.phase_3_start_index; // 🔐 lock Phase 2
                saveState('active');
                renderStep();
            });
            return;
        }

        /* =========================
           PHASE 3 COMPLETION
        ========================= */
        if (step.phase === 3 && currentStep >= steps.length - 1) {
            saveState('stopped');
            hideModal();
            updateLauncher('stopped');
            return;
        }

        /* =========================
           REDIRECT
        ========================= */
        if (step.redirect) {
            window.location.href = step.redirect;
            return;
        }

        /* =========================
           NORMAL STEP ADVANCE
        ========================= */
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

        // ✅ Avatar must exist
        if (!avatar) {
            currentStep = 0;
            showModal();
            return;
        }

        // ✅ Only jump to Phase 3 if Phase 2 completed
        if (
            MM_SPG.phase_3_start_index !== null &&
            MM_SPG.phase_3_start_index < steps.length
        ) {
            currentStep = MM_SPG.phase_3_start_index;
        } else {
            currentStep = 0; // Phase 2
        }

        saveState('active');
        showModal();
    });




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
        $form.find('#keywords-hidden').val(keywords.join(', '));

        // Hashtags
        const hashtags = [];
        $form.find('#hashtag-tags .hashtag-tag').each(function () {
            hashtags.push(
                $(this).clone().children().remove().end().text().trim()
            );
        });
        $form.find('#hashtags-hidden').val(hashtags.join(', '));

        console.log('KEYWORDS:', $form.find('#keywords-hidden').val());
        console.log('HASHTAGS:', $form.find('#hashtags-hidden').val());

        $.post(MM_SPG.ajax_url, {
            action: 'mm_spg_save_additional_profile',
            nonce: $form.find('[name="mm_spg_additional_nonce"]').val(),
            designation: $form.find('[name="designation"]').val(),
            about_me_short: $form.find('[name="about_me_short"]').val(),
            user_keywords: $form.find('#keywords-hidden').val(),
            user_hashtags: $form.find('#hashtags-hidden').val()
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
        $.post(MM_SPG.ajax_url, {
            action: 'mm_spg_get_state',
            nonce: MM_SPG.nonce
        }, function (res) {
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
