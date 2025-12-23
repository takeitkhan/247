<?php
/**
 * Interests Paths
 */
add_shortcode('mm_spg_interest_form', function () {

    if (!is_user_logged_in()) {
        return '<p>Please log in to select your interests.</p>';
    }

    $user_id = get_current_user_id();

    // Handle submit
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mm_spg_interest_submit'])) {

        check_admin_referer('mm_spg_interest_save', 'mm_spg_interest_nonce');

        $categories = array_map('intval', $_POST['user_categories'] ?? []);
        $priorities = $_POST['user_categories_priority'] ?? [];

        $clean_priorities = [];

        foreach ($priorities as $term_id => $priority) {
            if (in_array((int)$term_id, $categories, true)) {
                $clean_priorities[(int)$term_id] = (int)$priority;
            }
        }

        update_user_meta($user_id, 'user_categories', $categories);
        update_user_meta($user_id, 'user_categories_priority', $clean_priorities);

        echo '<div class="mb-3 alert alert-success">Interests updated successfully.</div>';
    }

    // Load data
    $categories = get_categories([
        'hide_empty'   => false,
        'slug__not_in' => ['uncategorized'],
    ]);

    $priorities = get_user_meta($user_id, 'user_categories_priority', true);
    $priorities = is_array($priorities) ? $priorities : [];

    ob_start();
    ?>

    <form method="post">
        <?php wp_nonce_field('mm_spg_interest_save', 'mm_spg_interest_nonce'); ?>

        <label class="mb-3 form-label fw-bold">Please prioritize your interests:</label>

        <div class="row">
            <?php foreach ($categories as $cat):
                $priority = $priorities[$cat->term_id] ?? '';
            ?>
                <div class="mb-2 col-md-6">
                    <div class="d-flex align-items-center gap-2 bg-light p-2 border rounded">
                        <input
                            type="checkbox"
                            class="ms-1 form-check-input"
                            name="user_categories[]"
                            value="<?= esc_attr($cat->term_id); ?>"
                            <?= checked(isset($priorities[$cat->term_id]), true, false); ?>>

                        <label class="flex-grow-1 text-truncate form-check-label" style="font-size: 0.9rem;">
                            <?= esc_html($cat->name); ?>
                        </label>

                        <select
                            name="user_categories_priority[<?= esc_attr($cat->term_id); ?>]"
                            class="w-auto form-select-sm form-select" 
                            style="min-width: 85px;">
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
/**
 * Social Links
 */
add_shortcode('mm_spg_social_links_form', function () {

    if (!is_user_logged_in()) {
        return '<p>Please log in to manage your links.</p>';
    }

    $user_id = get_current_user_id();

    // Example platform options
    $platform_options = [
        'facebook'  => 'Facebook',
        'instagram' => 'Instagram',
        'linkedin'  => 'LinkedIn',
        'website'   => 'Website',
    ];

    $saved_links = get_user_meta($user_id, 'custom_social_links', true);
    $saved_links = is_array($saved_links) ? $saved_links : [];

    // Handle submit
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mm_spg_links_submit'])) {

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

    <form method="post">
        <?php wp_nonce_field('mm_spg_links_save', 'mm_spg_links_nonce'); ?>

        <label class="form-label">Social / Business Links</label>

        <div id="social-links-group">
            <?php foreach ($saved_links as $index => $item): ?>
                <div class="align-items-center mb-2 row g-2">
                    <div class="col-md-3">
                        <select name="links[<?= $index ?>][platform]" class="form-select">
                            <?php foreach ($platform_options as $value => $label): ?>
                                <option value="<?= esc_attr($value) ?>" <?= selected($item['platform'], $value); ?>>
                                    <?= esc_html($label) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <input type="text" name="links[<?= $index ?>][label]" class="form-control"
                               value="<?= esc_attr($item['label']) ?>" placeholder="Custom name">
                    </div>

                    <div class="col-md-4">
                        <input type="url" name="links[<?= $index ?>][url]" class="form-control"
                               value="<?= esc_url($item['url']) ?>" placeholder="https://example.com">
                    </div>

                    <div class="col-md-2">
                        <button type="button" class="w-100 btn btn-danger remove-link">Remove</button>
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

?>