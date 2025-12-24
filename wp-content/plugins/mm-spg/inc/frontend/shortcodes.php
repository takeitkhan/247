<?php

add_action('wp_ajax_mm_spg_render_shortcode', 'mm_spg_render_shortcode');

function mm_spg_render_shortcode()
{
    if (!is_user_logged_in()) {
        wp_send_json_error('Not logged in');
    }

    $shortcode = sanitize_text_field($_POST['shortcode'] ?? '');

    if (empty($shortcode)) {
        wp_send_json_error('Empty shortcode');
    }

    ob_start();
    echo do_shortcode($shortcode);
    $html = ob_get_clean();

    wp_send_json_success([
        'html' => $html,
    ]);
}

add_shortcode('mm_spg_interest_form', function () {

    if (!is_user_logged_in()) {
        return;
    }

    $user_id = get_current_user_id();


    /* =========================
       HANDLE SUBMIT
    ========================= */
    if (
        $_SERVER['REQUEST_METHOD'] === 'POST' &&
        isset($_POST['mm_spg_interest_submit'])
    ) {
        check_admin_referer('mm_spg_interest_save', 'mm_spg_interest_nonce');

        $selected = array_map('intval', $_POST['user_categories'] ?? []);
        $priorities_raw = $_POST['user_categories_priority'] ?? [];

        $priorities = [];

        foreach ($priorities_raw as $term_id => $priority) {
            $term_id = (int) $term_id;
            $priority = (int) $priority;

            if (in_array($term_id, $selected, true) && $priority >= 1 && $priority <= 5) {
                $priorities[$term_id] = $priority;
            }
        }

        update_user_meta($user_id, 'user_categories', $selected);
        update_user_meta($user_id, 'user_categories_priority', $priorities);

        echo '<div class="mb-3 alert alert-success">Interests saved successfully.</div>';
    }

    /* =========================
       LOAD DATA
    ========================= */
    $categories = get_categories([
        'hide_empty'   => false,
        'slug__not_in' => ['uncategorized'],
    ]);

    $saved_priorities = get_user_meta($user_id, 'user_categories_priority', true);
    $saved_priorities = is_array($saved_priorities) ? $saved_priorities : [];

    ob_start();
?>

    <form method="post" class="mm-spg-interest-form">
        <?php wp_nonce_field('mm_spg_interest_save', 'mm_spg_interest_nonce'); ?>

        <label class="mb-3 form-label fw-bold">
            Please prioritize your interests:
        </label>

        <div class="row g-2">
            <?php foreach ($categories as $cat):
                $priority = $saved_priorities[$cat->term_id] ?? '';
            ?>
                <div class="col-12 col-md-6">
                    <div class="d-flex align-items-center gap-2 bg-light p-2 border rounded h-100">

                        <input
                            type="checkbox"
                            class="ms-1 form-check-input"
                            name="user_categories[]"
                            value="<?= esc_attr($cat->term_id); ?>"
                            <?= checked(isset($saved_priorities[$cat->term_id]), true, false); ?>>

                        <label class="flex-grow-1 mb-0 text-truncate form-check-label"
                            title="<?= esc_attr($cat->name); ?>">
                            <?= esc_html($cat->name); ?>
                        </label>

                        <select
                            name="user_categories_priority[<?= esc_attr($cat->term_id); ?>]"
                            class="form-select-sm form-select"
                            style="max-width: 90px;">
                            <option value="">Priority</option>
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <option value="<?= $i ?>" <?= selected($priority, $i); ?>>
                                    <?= $i ?><?= $i === 1 ? 'st' : ($i === 2 ? 'nd' : ($i === 3 ? 'rd' : 'th')) ?>
                                </option>
                            <?php endfor; ?>
                        </select>

                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <button type="submit" name="mm_spg_interest_submit" class="mt-3 btn btn-primary">
            Save Interests
        </button>
    </form>


<?php
    return ob_get_clean();
});

add_action('wp_ajax_mm_spg_save_interests', 'mm_spg_save_interests');

function mm_spg_save_interests()
{
    if (!is_user_logged_in()) {
        wp_send_json_error('Not logged in');
    }

    check_ajax_referer('mm_spg_interest_save', 'nonce');

    $user_id = get_current_user_id();

    $selected = array_map('intval', $_POST['user_categories'] ?? []);
    $priorities_raw = $_POST['user_categories_priority'] ?? [];

    $priorities = [];

    foreach ($priorities_raw as $term_id => $priority) {
        $term_id  = (int) $term_id;
        $priority = (int) $priority;

        if (
            in_array($term_id, $selected, true) &&
            $priority >= 1 &&
            $priority <= 5
        ) {
            $priorities[$term_id] = $priority;
        }
    }

    // Ensure unique priorities
    if (count($priorities) !== count(array_unique($priorities))) {
        wp_send_json_error('Duplicate priorities are not allowed.');
    }

    update_user_meta($user_id, 'user_categories', $selected);
    update_user_meta($user_id, 'user_categories_priority', $priorities);

    // Mark step completed (important for guide)
    update_user_meta($user_id, 'mm_spg_interest_completed', 1);

    wp_send_json_success('Interests saved successfully.');
}


add_shortcode('mm_spg_social_links_form', function () {

    if (!is_user_logged_in()) {
        return '<p>Please log in to manage your links.</p>';
    }

    $user_id = get_current_user_id();

    $platform_options = [
        'facebook'  => 'Facebook',
        'instagram' => 'Instagram',
        'linkedin'  => 'LinkedIn',
        'twitter'   => 'Twitter / X',
        'youtube'   => 'YouTube',
        'website'   => 'Website',
    ];

    $saved_links = get_user_meta($user_id, 'custom_social_links', true);
    $saved_links = is_array($saved_links) ? $saved_links : [];

    ob_start();
?>
    <form method="post" class="mm-spg-social-links-form">
        <?php wp_nonce_field('mm_spg_links_save', 'mm_spg_links_nonce'); ?>

        <label class="mb-2 form-label">Social / Business Links</label>

        <div id="social-links-group">
            <?php foreach ($saved_links as $index => $item): ?>
                <div class="align-items-center mb-2 row g-2 social-link-row">
                    <div class="col-md-3">
                        <select name="links[<?= $index ?>][platform]" class="form-select">
                            <?php foreach ($platform_options as $value => $label): ?>
                                <option value="<?= esc_attr($value); ?>" <?= selected($item['platform'], $value); ?>>
                                    <?= esc_html($label); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <input type="text"
                            name="links[<?= $index ?>][label]"
                            class="form-control"
                            value="<?= esc_attr($item['label']); ?>"
                            placeholder="Custom label">
                    </div>

                    <div class="col-md-4">
                        <input type="url"
                            name="links[<?= $index ?>][url]"
                            class="form-control"
                            value="<?= esc_url($item['url']); ?>"
                            placeholder="https://example.com">
                    </div>

                    <div class="col-md-2">
                        <button type="button" class="w-100 btn btn-danger remove-link">
                            Remove
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="d-flex gap-3">
            <button type="button" class="mt-3 btn btn-secondary btn-sm" id="mm-spg-add-social-link">+ Add Link</button>

            <button type="submit" name="mm_spg_links_submit" class="mt-3 btn btn-info btn-sm">
                Update Links
            </button>
        </div>

    </form>
<?php
    return ob_get_clean();
});


add_shortcode('mm_spg_additional_profile_details', function () {

    if (!is_user_logged_in()) {
        return '<p>Please log in.</p>';
    }

    $user_id = get_current_user_id();

    $designation  = get_user_meta($user_id, 'designation', true);
    $about_short  = get_user_meta($user_id, 'digital_card_about', true);
    $keywords     = get_user_meta($user_id, 'user_keywords', true);
    $hashtags     = get_user_meta($user_id, 'user_hashtags', true);

    ob_start();
?>
    <form class="mm-spg-additional-profile-form" novalidate>

        <?php wp_nonce_field('mm_spg_save_additional_profile', 'mm_spg_additional_nonce'); ?>

        <h5 class="mb-4">Additional Profile Details</h5>

        <!-- Designation -->
        <div class="mb-3">
            <label class="form-label">Title</label>
            <input type="text"
                name="designation"
                class="form-control"
                maxlength="60"
                value="<?= esc_attr($designation); ?>"
                required>
        </div>

        <!-- About -->
        <div class="mb-3">
            <label class="form-label">About Me (Max 150)</label>
            <textarea name="about_me_short"
                id="about_me_short"
                class="form-control"
                maxlength="150"
                rows="3"
                required><?= esc_textarea($about_short); ?></textarea>
            <div class="text-muted small">
                <span id="charCount">0</span>/150
            </div>
        </div>

        <!-- Keywords -->
        <div class="mb-3">
            <label class="form-label">Keywords</label>

            <div id="keyword-tags"
                class="d-flex flex-wrap gap-2 form-control mm-spg-keyword-tags"
                style="min-height:44px; padding:6px;">
                <?php if ($keywords): ?>
                    <?php foreach (explode(',', $keywords) as $kw): ?>
                        <span class="bg-light border text-dark badge keyword-tag">
                            <?= esc_html(trim($kw)); ?>
                            <button type="button" class="btn-close remove-tag"></button>
                        </span>
                    <?php endforeach; ?>
                <?php endif; ?>
                <input type="text" id="keywordInput" class="flex-grow-1 border-0">
            </div>

            <input type="hidden" name="user_keywords" class="mm-spg-keywords-hidden">
        </div>

        <!-- Hashtags -->
        <div class="mb-3">
            <label class="form-label">Hashtags</label>

            <div id="hashtag-tags"
                class="d-flex flex-wrap gap-2 form-control mm-spg-hashtag-tags"
                style="min-height:44px; padding:6px;">
                <?php if ($hashtags): ?>
                    <?php foreach (explode(',', $hashtags) as $tag): ?>
                        <span class="bg-light border text-dark badge hashtag-tag">
                            <?= esc_html(trim($tag)); ?>
                            <button type="button" class="btn-close remove-hashtag"></button>
                        </span>
                    <?php endforeach; ?>
                <?php endif; ?>
                <input type="text" id="hashtagInput" class="flex-grow-1 border-0">
            </div>

            <input type="hidden" name="user_hashtags" class="mm-spg-hashtags-hidden">
        </div>


        <button type="submit" class="btn btn-primary">
            Save Details
        </button>

    </form>
<?php
    return ob_get_clean();
});

add_action('wp_ajax_mm_spg_save_additional_profile', function () {

    if (!is_user_logged_in()) {
        wp_send_json_error('Not logged in');
    }

    check_ajax_referer('mm_spg_save_additional_profile', 'nonce');

    $user_id = get_current_user_id();

    update_user_meta($user_id, 'designation',
        sanitize_text_field($_POST['designation'] ?? '')
    );

    $about = sanitize_text_field($_POST['about_me_short'] ?? '');
    update_user_meta($user_id, 'digital_card_about', mb_substr($about, 0, 150));

    update_user_meta($user_id, 'user_keywords',
        sanitize_text_field($_POST['user_keywords'] ?? '')
    );

    update_user_meta($user_id, 'user_hashtags',
        sanitize_text_field($_POST['user_hashtags'] ?? '')
    );

    update_user_meta($user_id, 'mm_spg_additional_profile_completed', 1);

    wp_send_json_success('Profile details saved successfully.');
});