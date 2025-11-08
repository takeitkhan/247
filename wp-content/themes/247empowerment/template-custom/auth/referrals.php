<?php
get_header_based_on_login();
$referral_user_slug = get_query_var('referral_user');
$current_user_id    = get_current_user_id();
$current_user       = wp_get_current_user();

// Determine target user
if ($referral_user_slug) {
    // Try to get user by slug from URL
    $user = get_user_by('slug', $referral_user_slug);
} else {
    // Fall back to logged-in user
    $user = get_user_by('ID', $current_user_id);
}

// Check if user exists
if ($user) {
    // Initialize UserProfileData based on what the class accepts
    // ✅ Option A: If constructor accepts WP_User object
    $profileData = new UserProfileData($user);

    // ✅ Option B (fallback): If constructor only accepts slug or username
    // $profileData = new UserProfileData($user->user_login);

    // Get profile data
    $profile = $profileData->getProfile();
} else {
    // Handle invalid user (e.g., show error message or redirect)
    $profile = null;
    echo '<div class="py-5 container"><h2>User not found.</h2></div>';
    get_footer_based_on_login();
    return;
}


if (!$user) {
    echo '<div class="py-5 container"><h2>User not found.</h2></div>';
    get_header_based_on_login();
    return;
}

$profileData = new UserProfileData($user);
$referrals = $profileData::getReferredUsersBy($user);
$total_referrals = count_users()['total_users'];
$search = isset($_GET['search']) ? sanitize_text_field($_GET['search']) : '';

if ($search) {
    $search_terms = array_filter(explode(' ', strtolower($search)));

    $referrals = array_filter($referrals, function ($user) use ($search_terms) {
        $first_name = strtolower(get_user_meta($user->ID, 'first_name', true));
        $last_name = strtolower(get_user_meta($user->ID, 'last_name', true));
        $display_name = strtolower($user->display_name);
        $user_login = strtolower($user->user_login);

        $haystack = "{$first_name} {$last_name} {$display_name} {$user_login}";

        foreach ($search_terms as $term) {
            if (!str_contains($haystack, $term)) {
                return false;
            }
        }

        return true;
    });

    $referrals = array_values($referrals); // Re-index
}

$referred_users_count = count($referrals);
?>

<div class="container profile-page pt20">
    <div class="row">
        <div class="col-lg-3">
            <?php get_template_part('template-custom/auth/feed-parts/profile-card', null, ['profile' => $profile, 'user' => $user]); ?>
            <?php get_template_part('template-custom/auth/profile-parts/navlink', null, ['profile' => $profile]); ?>
        </div>
        <div class="col-lg-6">

            <div class="bg-white referral_partner custom-card">
                <div class="w-100">
                    <div class="upcoming-events">
                        <div class="d-flex justify-content-start pb-3 text-start">
                            <h5 class="portal-title">
                                <?php echo esc_html($user->first_name . ' ' . $user->last_name); ?>’s Referral Partners
                            </h5>
                        </div>
                        <div class="pb-4">
                            <?php echo $referred_users_count; ?> referral partners
                        </div>

                    </div>
                </div>
                <div class="">
                    <div class="d-flex align-items-center justify-content-between gap-3 filter-bar">
                        <!-- Sort Section -->
                        <div class="d-flex align-items-center gap-2 w-100">
                            <p class="fs14">Sort by</p>

                            <div class="select-wrapper">

                                <div class="d-flex align-items-center gap-0">
                                    <img class="" src="<?= get_template_directory_uri(); ?>/assets/img/nd/filter.png" alt="">
                                    <div class="">
                                        <select class="custom-select">
                                            <option>Recently joined</option>
                                            <option>Name (A-Z)</option>
                                            <option>Name (Z-A)</option>
                                            <option>Most Active</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Search Section -->
                        <div class="flex-grow-1 w-100 search-wrapper">
                            <input type="text" id="search-input" class="form-control input" placeholder="Search by name" value="<?php echo esc_attr($search); ?>">
                        </div>
                    </div>

                </div>
            </div>

            <div class="bg-white custom-card">
                <div class="flex flex-column gap-3" id="referrals-grid">
                    <?php
                    $count = 0;
                    foreach ($referrals as $ref_user) {

                        $ref_user = is_array($ref_user) ? (object) $ref_user : $ref_user;
                        if ($count >= 8) break;

                        $ref_id = isset($ref_user->ID) ? $ref_user->ID : 0;
                        $ref_email = isset($ref_user->user_email) ? trim($ref_user->user_email) : '';
                        $ref_login = isset($ref_user->user_login) ? $ref_user->user_login : '';
                        $ref_username = isset($ref_user->username) ? $ref_user->username : '';
                        $photo = get_user_meta($ref_id, 'profile_photo', true);
                        $photo = $photo ?: 'https://www.gravatar.com/avatar/' . md5(strtolower($ref_email)) . '?s=150&d=mm';
                        $profile_url = site_url('/' . $ref_username);

                    ?>

                        <div class="d-flex flex-column flex-lg-row justify-content-between py-3" data-index="<?php echo $count; ?>">
                            <div>
                                <div class="d-flex align-items-center gap-3 pb-3">
                                    <div class="img60">
                                        <img src="<?php echo esc_url($photo); ?>" class="w-100 h-100 object-fit-contain" alt="<?php echo esc_html($ref_user->first_name . ' ' . $ref_user->last_name); ?>">
                                    </div>
                                    <div class="d-flex flex-column gap-1 post-user">
                                        <div class="d-flex flex-wrap gap-1 gap-sm-4">
                                            <span class="text-black p_name fw-bold">
                                                <a href="<?php echo esc_url($profile_url); ?>">
                                                    <?php echo esc_html($ref_user->first_name . ' ' . $ref_user->last_name); ?>
                                                </a>
                                            </span>
                                            <!-- <div class="d-flex align-items-center gap-2 text-blue-color fs14">
                                                <div class="img16">
                                                    <img class="w-100 h-100 object-fit-contain" src="<?= get_template_directory_uri(); ?>/assets/img/nd/location.png" alt="">
                                                </div> New York, NY
                                            </div> -->
                                        </div>
                                        <?php is_array($ref_user->user_category_names) && !empty($ref_user->user_category_names) ? $has_categories = true : $has_categories = false; ?>
                                        <?php if ($has_categories) : ?>
                                            <div class="d-flex align-items-center gap-1 mt-1 n-text a">
                                                <div class="img24">
                                                    <img class="w-100 h-100 object-fit-contain" src="<?= get_template_directory_uri(); ?>/assets/img/nd/market_bag.png" alt="">
                                                </div>
                                                <p class="">
                                                    <?php echo implode(', ', $ref_user->user_category_names); ?>
                                                </p>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <!-- <button class="custom-btn-outline-none">Message</button> -->

                                <div class="d-flex align-items-center justify-content-end my-3">
                                    <div class="dropdown">
                                        <button
                                            class="d-flex align-items-center justify-content-center rounded-circle h-bg btn"
                                            type="button"
                                            data-bs-toggle="dropdown"
                                            data-bs-offset="0,8"
                                            aria-expanded="false">
                                            <i class="bi bi-three-dots-vertical fs-5"></i>
                                        </button>
                                        <ul class="shadow-sm p-2 dropdown-menu dropdown-menu-end custom-modal">
                                            <li>
                                                <button
                                                    class="d-flex align-items-center gap-2 border-0 w-100 btn remove-partner-btn">
                                                    <i class="bi bi-trash fs-5"></i>
                                                    <span>Remove Partner</span>
                                                </button>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
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

        <!-- Upcoming Events -->
        <div class="col-lg-3">
            <div class="bg-white upcoming-events custom-card">
                <div class="d-flex align-items-center justify-content-between pb-4 u-title">
                    <h5 class="portal-title">Events from partners</h5>
                    <span class="">12</span>
                </div>
                <div class="d-flex align-items-center gap-3 pb-3 border-underline event">
                    <span class="event-date">Oct 20</span>
                    <div>
                        <span class="fw-medium">Birthday</span><br>
                        <span class="fs14">Dr. Alicia Stone</span>
                    </div>
                </div>
                <div>
                    <button class="d-flex align-items-center justify-content-center gap-2 pt-3 w-100 more-option"><img src="<?= get_template_directory_uri(); ?>/assets/img/nd/loading.png" alt=""> More</button>
                </div>
            </div>
        </div>

    </div>
</div>

<script>
    document.getElementById('search-input')?.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            const searchValue = this.value.trim();
            const grid = document.getElementById('referrals-grid');

            fetch('<?php echo admin_url('admin-ajax.php'); ?>?action=load_more_referrals&user=<?php echo esc_attr($user->ID); ?>&search=' + encodeURIComponent(searchValue))
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        grid.innerHTML = data.data;
                    } else {
                        grid.innerHTML = '<p>No results found</p>';
                    }
                });
        }
    });
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

<?php get_footer_based_on_login(); ?>