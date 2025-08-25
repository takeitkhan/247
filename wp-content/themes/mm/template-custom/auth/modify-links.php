<?php
/* Template Name: Modify Social Links */
get_header();

if (!is_user_logged_in()) {
    wp_redirect(wp_login_url());
    exit;
}

$current_user = wp_get_current_user();
$user_id = $current_user->ID;

// Platforms dropdown
$platform_options = [
    'youtube' => 'YouTube',
    'facebook' => 'Facebook',
    'linkedin' => 'LinkedIn',
    'x' => 'X (Twitter)',
    'instagram' => 'Instagram',
    'google_business' => 'Google Business',
    'yelp' => 'Yelp',
    'meetup' => 'Meetup',
    'website' => 'Website',
    'tiktok' => 'TikTok',
    'twitch' => 'Twitch',
    'pinterest' => 'Pinterest',
    'snapchat' => 'Snapchat',
    'whatsapp' => 'WhatsApp',
    'zoom' => 'Zoom',
    'discord' => 'Discord',
    'github' => 'GitHub',
    'google' => 'Google',
    'custom' => 'Custom Link',
    'other' => 'Other',
    'email' => 'Email',
    'phone' => 'Phone',
    'telegram' => 'Telegram',
    'signal' => 'Signal',
    'viber' => 'Viber',
    'sheet' => 'Sheet',
    'slack' => 'Slack',
    'reddit' => 'Reddit',
    'messenger' => 'Messenger',
    'meet' => 'Meet',
    'whatsapp_business' => 'WhatsApp Business',
    'calendar' => 'Calendar',
    'default' => 'Default Link',
];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_links'])) {
    if (!isset($_POST['frontend_links_nonce']) || !wp_verify_nonce($_POST['frontend_links_nonce'], 'frontend_links_update')) {
        echo '<div class="alert alert-danger">Security check failed.</div>';
    } else {
        $sanitized_links = [];
        foreach ($_POST['links'] ?? [] as $link) {
            if (!empty($link['url'])) {
                $sanitized_links[] = [
                    'platform' => sanitize_text_field($link['platform']),
                    'label'    => sanitize_text_field($link['label']),
                    'url'      => esc_url_raw($link['url']),
                ];
            }
        }
        update_user_meta($user_id, 'custom_social_links', $sanitized_links);

        echo "<script>
        document.addEventListener('DOMContentLoaded', function() {
            Toastify({
                text: 'Links updated successfully.',
                duration: 4000,
                gravity: 'bottom',
                position: 'left',
                backgroundColor: '#28a745',
                close: true
            }).showToast();
        });
        </script>";
    }
}

// Get saved links
$saved_links = get_user_meta($user_id, 'custom_social_links', true) ?: [];

?>
<main>
    <div class="main-container" style="padding-top: 80px">
        <div class="row g-3">
            <?php include get_template_directory() . '/template-custom/auth/profile-parts/edit-profile-left-sidebar.php'; ?>

            <div class="ms-md-auto col-12 col-md-8 col-lg-9 col-xl-9">
                <div class="bg-white custom-box-shadow mb-3 p-3 custom-border-radius">
                    <div class="row">
                        <div class="col-12">
                            <h5 class="mb-5">🔗 Manage Social/Business Links</h5>
                        </div>
                    </div>                    
                    <form method="post">
                        <?php wp_nonce_field('frontend_links_update', 'frontend_links_nonce'); ?>

                        <div class="mb-4">
                            <label class="form-label">Social / Business Links</label>
                            <div id="social-links-group">
                                <?php foreach ($saved_links as $index => $item): ?>
                                    <div class="align-items-center mb-2 row g-2 social-link-row">
                                        <div class="col-md-3">
                                            <select name="links[<?php echo $index; ?>][platform]" class="form-select">
                                                <?php foreach ($platform_options as $value => $label): ?>
                                                    <option value="<?php echo esc_attr($value); ?>" <?php selected($item['platform'], $value); ?>>
                                                        <?php echo esc_html($label); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <input type="text" name="links[<?php echo $index; ?>][label]" class="form-control" placeholder="Custom name" value="<?php echo esc_attr($item['label']); ?>">
                                        </div>
                                        <div class="col-md-4">
                                            <input type="url" name="links[<?php echo $index; ?>][url]" class="form-control" placeholder="https://example.com" value="<?php echo esc_url($item['url']); ?>">
                                        </div>
                                        <div class="col-md-2">
                                            <button type="button" class="w-100 btn btn-danger remove-link">Remove</button>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <button type="button" class="btn btn-secondary btn-sm" id="add-social-link">+ Add Link</button>
                        </div>

                        <button type="submit" name="update_links" class="btn btn-primary">Update Links</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</main>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        let counter = <?php echo count($saved_links); ?>;
        const platforms = <?php echo json_encode($platform_options); ?>;

        document.getElementById('add-social-link').addEventListener('click', function() {
            const container = document.getElementById('social-links-group');
            const row = document.createElement('div');
            row.className = 'row g-2 mb-2 align-items-center social-link-row';
            row.innerHTML = `
            <div class="col-md-3">
                <select name="links[${counter}][platform]" class="form-select">
                    ${Object.entries(platforms).map(([val, label]) => `<option value="${val}">${label}</option>`).join('')}
                </select>
            </div>
            <div class="col-md-3">
                <input type="text" name="links[${counter}][label]" class="form-control" placeholder="Custom name">
            </div>
            <div class="col-md-4">
                <input type="url" name="links[${counter}][url]" class="form-control" placeholder="https://example.com">
            </div>
            <div class="col-md-2">
                <button type="button" class="w-100 btn btn-danger remove-link">Remove</button>
            </div>`;
            container.appendChild(row);
            counter++;
        });

        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('remove-link')) {
                e.target.closest('.social-link-row').remove();
            }
        });
    });
</script>

<?php get_footer(); ?>