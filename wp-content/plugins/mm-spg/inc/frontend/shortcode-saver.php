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

        <label class="mb-2 form-label">
            Please prioritize your interests:
        </label>

        <?php foreach ($categories as $cat):
            $priority = $saved_priorities[$cat->term_id] ?? '';
        ?>
            <div class="d-flex align-items-center gap-2 mb-2">
                <input
                    type="checkbox"
                    class="form-check-input"
                    name="user_categories[]"
                    value="<?= esc_attr($cat->term_id); ?>"
                    <?= checked(isset($saved_priorities[$cat->term_id]), true, false); ?>>

                <label class="flex-grow-1 form-check-label">
                    <?= esc_html($cat->name); ?>
                </label>

                <select
                    name="user_categories_priority[<?= esc_attr($cat->term_id); ?>]"
                    class="w-auto form-select-sm form-select">
                    <option value="">Priority</option>
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                        <option value="<?= $i ?>" <?= selected($priority, $i); ?>>
                            <?= $i ?><?= $i === 1 ? 'st' : ($i === 2 ? 'nd' : ($i === 3 ? 'rd' : 'th')) ?>
                        </option>
                    <?php endfor; ?>
                </select>
            </div>
        <?php endforeach; ?>

        <button type="submit" name="mm_spg_interest_submit" class="mt-3 btn btn-primary">
            Save Interests
        </button>
    </form>

<?php
    return ob_get_clean();
});


add_shortcode('mm_spg_social_links_form', function () {

    if (!is_user_logged_in()) {
        return '<p>Please log in to manage your links.</p>';
    }

    $user_id = get_current_user_id();

    /* =========================
       PLATFORM OPTIONS
    ========================= */
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

    /* =========================
       HANDLE SUBMIT
    ========================= */
    if (
        $_SERVER['REQUEST_METHOD'] === 'POST' &&
        isset($_POST['mm_spg_links_submit'])
    ) {
        check_admin_referer('mm_spg_links_save', 'mm_spg_links_nonce');

        $links = $_POST['links'] ?? [];
        $clean_links = [];

        foreach ($links as $link) {
            if (!empty($link['url'])) {
                $clean_links[] = [
                    'platform' => sanitize_text_field($link['platform']),
                    'label'    => sanitize_text_field($link['label']),
                    'url'      => esc_url_raw($link['url']),
                ];
            }
        }

        update_user_meta($user_id, 'custom_social_links', $clean_links);
        $saved_links = $clean_links;

        echo '<div class="mb-3 alert alert-success">Links updated successfully.</div>';
    }

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

        <button type="submit" name="mm_spg_links_submit" class="mt-3 btn btn-primary">
            Update Links
        </button>
    </form>

<?php
    return ob_get_clean();
});
