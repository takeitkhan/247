<?php

/**
 * Template Name: Wallet Page
 */

if (!is_user_logged_in()) {
    wp_redirect(wp_login_url());
    exit;
}

get_header();

$current_user_id = get_current_user_id();
$balance = (float) get_user_meta($current_user_id, 'referral_commission', true);
$logs = get_user_meta($current_user_id, 'referral_logs', true);
$logs = is_array($logs) ? $logs : [];


$current_user = get_userdata($current_user_id);
$user_slug = $current_user ? $current_user->user_nicename : '';

$profile = (new UserProfileData($user_slug))->getProfile();

// echo '<pre>';
// print_r($profile); 
// echo '</pre>';

?>
<main>
    <div class="main-container" style="padding-top: 80px">
        <div class="row g-3">
            <?php include get_template_directory() . '/template-custom/auth/profile-parts/edit-profile-left-sidebar.php'; ?>

            <div class="ms-md-auto col-12 col-md-8 col-lg-9 col-xl-9">
                <div class="bg-white custom-box-shadow mb-3 p-3 custom-border-radius">
                    <div class="row">
                        <div class="col-6">
                            <h5 class="mb-5">🎉 Referral Wallet</h5>
                        </div>
                        <div class="text-end col-6">
                            <div class="mb-5">
                                <h3>💰 Current Balance: <span class="text-success">$<?= number_format($balance, 2); ?></span></h3>
                            </div>
                        </div>
                    </div>

                    <?php if (empty($logs)): ?>
                        <p>You haven't earned any referral commissions yet.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Earned for</th>
                                        <th>Referred User</th>
                                        <th>Amount</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach (array_reverse($logs) as $log):
                                        $referred_user = get_user_by('ID', $log['referred_user_id']);
                                    ?>
                                        <tr>
                                            <?php
                                            // Get user slug (profile owner)
                                            $current_user = get_userdata(get_current_user_id());
                                            $user_slug = $current_user ? $current_user->user_nicename : esc_html($profile['display_name']);

                                            // Get post slug
                                            $post = get_post($log['earned_for_id']);
                                            $post_slug = $post ? $post->post_name : '';

                                            // Build the full profile link
                                            $profile_link = home_url("/{$user_slug}/store/{$post_slug}/");
                                            ?>

                                            <td>
                                                <a href="<?= esc_url($profile_link); ?>">
                                                    <?= esc_html($log['earned_for']); ?>
                                                </a>
                                            </td>

                                            <td>
                                                <a href="<?= home_url("/{$referred_user->display_name}"); ?>">
                                                    <?= esc_html($referred_user ? $referred_user->display_name : 'User #' . $log['referred_user_id']); ?>
                                                </a>
                                            </td>
                                            <td class="text-success">
                                                $<?= number_format((float)$log['amount'], 2); ?>
                                            </td>
                                            <td>
                                                <?= esc_html(date('F j, Y H:i', strtotime($log['date']))); ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</main>
<?php get_footer(); ?>