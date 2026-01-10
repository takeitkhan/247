<?php
get_header();

$referral_user_slug = get_query_var('referral_user');
$user = get_user_by('slug', $referral_user_slug);



if (!$user) {
    echo '<div class="py-5 container"><h2>User not found.</h2></div>';
    get_footer();
    return;
}

$profileData = new UserProfileData($user);
$referrals = $profileData::getReferredUsersBy($user);

$total_referrals = count_users()['total_users'];

$search = isset($_GET['search']) ? sanitize_text_field($_GET['search']) : '';

if ($search) {
    $search_terms = array_filter(explode(' ', strtolower($search))); // ['soyaad', 'khan']

    $referrals = array_filter($referrals, function ($user) use ($search_terms) {
        $first_name = strtolower(get_user_meta($user->ID, 'first_name', true));
        $last_name = strtolower(get_user_meta($user->ID, 'last_name', true));
        $display_name = strtolower($user->display_name);
        $user_login = strtolower($user->user_login);

        $haystack = "{$first_name} {$last_name} {$display_name} {$user_login}";

        // Match if ALL words are found
        foreach ($search_terms as $term) {
            if (!str_contains($haystack, $term)) {
                return false;
            }
        }

        return true;
    });

    $referrals = array_values($referrals); // Re-index
}

?>
<main>
    <div class="main-container" style="padding-top: 80px">
        <div class="py-5 container">
            <h2 class="mb-4">
                <?php echo esc_html($user->first_name . ' ' . $user->last_name); ?>’s Referral Partners
            </h2>

            <div id="referrals-grid" class="row row-cols-1 row-cols-sm-2 row-cols-md-4 g-4">
                <?php
                $count = 0;
                foreach ($referrals as $ref_user) {
                    $ref_user = is_array($ref_user) ? (object) $ref_user : $ref_user;
                    if ($count >= 4) break;

                    $ref_id = isset($ref_user->ID) ? $ref_user->ID : 0;
                    $ref_email = isset($ref_user->user_email) ? trim($ref_user->user_email) : '';
                    $ref_login = isset($ref_user->user_login) ? $ref_user->user_login : '';
                    $ref_registered = isset($ref_user->user_registered) ? $ref_user->user_registered : '';

                    $photo = get_user_meta($ref_id, 'profile_photo', true);
                    $photo = $photo ?: 'https://www.gravatar.com/avatar/' . md5(strtolower($ref_email)) . '?s=150&d=mm';
                    $profile_url = site_url('/' . $ref_login);
                    $join_date = $ref_registered ? date('F j, Y', strtotime($ref_registered)) : '—';

                ?>
                    <div class="col referral-card" data-index="<?php echo $count; ?>">
                        <a href="<?php echo esc_url($profile_url); ?>" class="text-dark text-decoration-none">
                            <div class="h-100 text-center card">
                                <img src="<?php echo esc_url($photo); ?>" class="card-img-top mx-auto mt-3 rounded-circle" alt="<?php echo esc_attr($ref_user->display_name); ?>" style="width: 100px; height: 100px; object-fit: cover;">
                                <div class="card-body">
                                    <h6 class="card-title">
                                        <?php echo esc_html($ref_user->first_name . ' ' . $ref_user->last_name); ?>
                                        <?php //echo esc_html($ref_user->display_name); 
                                        ?>
                                    </h6>
                                    <p class="text-muted text-small card-text">
                                        <small><?php echo esc_html($join_date); ?></small>
                                    </p>
                                </div>
                            </div>
                        </a>
                    </div>
                <?php $count++;
                } ?>
            </div>

            <?php if ($count >= 4): ?>
                <div class="mt-4 text-center">
                    <button id="load-more-referrals" class="btn btn-primary">Load More</button>
                </div>
            <?php endif; ?>
        </div>
    </div>
</main>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const button = document.getElementById('load-more-referrals');
        let offset = 4;
        button?.addEventListener('click', function() {
            button.disabled = true;
            button.textContent = 'Loading...';

            fetch('<?php echo admin_url('admin-ajax.php'); ?>?action=load_more_referrals&user=<?php echo esc_attr($user->ID); ?>&offset=' + offset)
                .then(response => response.json()) // parse JSON
                .then(data => {
                    if (data.success && data.data) {
                        document.getElementById('referrals-grid').insertAdjacentHTML('beforeend', data.data);
                        offset += 40;
                        button.disabled = false;
                        button.textContent = 'Load More';
                    } else {
                        // No more data or error
                        button.disabled = true;
                        button.textContent = 'No more referrals';
                    }
                });
        });
    });
</script>

<?php get_footer(); ?>