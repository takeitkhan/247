<?php

/**
 * Template Name: Single Store Course Template
 */

$store_user = get_query_var('store_user');
$course_slug = get_query_var('course_slug');
$is_shareable = get_query_var('shareable'); // '1' if shareable URL

$course = get_page_by_path($course_slug, OBJECT, 'course');

if (!$course) {
    if (!$is_shareable) {
        get_header_based_on_login();
    }
    echo '<p class="text-danger">Course not found.</p>';
    if (!$is_shareable) {
        get_header_based_on_login();
    }
    exit;
}

// Redirect non-logged-in users to login for non-shareable URLs
if (!is_user_logged_in() && !$is_shareable) {
    wp_redirect(wp_login_url(get_permalink()));
    exit;
}

setup_postdata($course);

$price = get_field('price', $course->ID);
$instructor = get_field('instructor', $course->ID);
$duration = get_field('duration', $course->ID);
$short_details = get_field('short_details', $course->ID);
$thumbnail_url = get_the_post_thumbnail_url($course->ID, 'large') ?: '/img/banner.jpg';
$custom_permalink = home_url("/{$store_user}/store/{$course_slug}/");


$current_user = wp_get_current_user();

// Get current logged-in user ID (used as a fallback if no slug is provided)
$current_user_id = get_current_user_id();

// 1. Get the user slug from the query variable
$user_slug = get_query_var('user_profile');

// 2. Determine the target user
if ($user_slug) {
    // If a slug is present, try to get the user by their slug (login or nicename)
    $user = get_user_by('slug', $user_slug);
} else {
    // If no slug, fall back to the currently logged-in user
    $user = get_user_by('ID', $current_user_id);
}

// 3. Instantiate the UserProfileData class and get the profile array
if ($user) {
    // We pass the WP_User object to the class constructor, or the ID/slug depending on the class's constructor.
    // Given your original line: $profile = (new UserProfileData($user_slug))->getProfile();
    // We'll update it to pass the $user object for better data handling, assuming the class supports it.
    // If the class REQUIRES a slug, use $user_slug or $user->user_login.

    // Option A: If UserProfileData takes a WP_User object (Recommended)
    $profile_data_instance = new UserProfileData($user);

    // Option B: If UserProfileData only takes the slug (Sticking closer to your original code)
    // Use the slug if present, otherwise use the current user's login.
    $target_identifier = $user_slug ? $user_slug : $user->user_login;
    $profile_data_instance = new UserProfileData($target_identifier);

    // Get the profile array
    $profile = $profile_data_instance->getProfile();
} else {
    // Set variables to null if no user could be determined
    $user = null;
    $profile = null;
}

// Only load header if NOT shareable
if (!$is_shareable) {
    get_header_based_on_login();
}

?>

<?php if ($is_shareable): ?>

    <?php
    $path = $_SERVER['REQUEST_URI'];
    $segments = explode('/', trim($path, '/'));

    $referrer_username = $segments[0] ?? null;

    $login_url = home_url('/signin');
    $register_url = home_url('/signup');

    if ($referrer_username) {
        $login_url    = add_query_arg('ref', $referrer_username, $login_url);
        $register_url = add_query_arg('ref', $referrer_username, $register_url);
    }

    include __DIR__ . '/store-parts/shareable-course.php';
    ?>

<?php else: ?>


    <div class="pb-5 container profile-page pt20">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-lg-3">

                <?php get_template_part('template-custom/auth/feed-parts/profile-card', null, ['profile' => $profile]); ?>
                <?php
                $current_user = wp_get_current_user();
                $username = get_query_var('store_user') ?: $current_user->user_nicename;

                $terms = get_terms([
                    'taxonomy' => 'course_category',
                    'hide_empty' => false,
                    'orderby' => 'name',
                    'order' => 'ASC',
                ]);
                ?>
                <div class="bg-white custom-card navbar-link">
                    <ul class="d-flex flex-column gap-2 nav">
                        <!-- Browse All -->
                        <li class="d-flex align-items-center gap-2 nav-item">
                            <img src="<?= get_template_directory_uri(); ?>/assets/img/nd/empower.png" alt="empower" class="icon-img">
                            <a href="<?php echo esc_url(home_url("/$username/store")); ?>" class="p-0 text">Browse All</a>
                        </li>

                        <!-- Dynamic Category List -->
                        <?php foreach ($terms as $term): ?>
                            <li class="d-flex align-items-center gap-2 nav-item">
                                <?php
                                $icon_id = get_term_meta($term->term_id, 'term_icon', true) ??  get_template_directory_uri() . '/assets/img/marketplace/default.png';
                                if ($icon_id) {
                                    echo '<img class="icon-img" src="' . esc_url(wp_get_attachment_url($icon_id)) . '" alt="' . esc_attr($term->name) . '">';
                                }
                                ?>
                                <a href="<?php echo esc_url(home_url("/$username/store?category={$term->slug}")); ?>" class="p-0 text">
                                    <?php echo esc_html($term->name); ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php get_template_part('template-custom/auth/profile-parts/navlink', null, ['profile' => $profile]); ?>
            </div>
            <div class="bg-white mb-0 col-lg-9 custom-card">
                <div class="row">
                    <div class="col-lg-9">
                        <div class="post-search">
                            <div class="gap-3 post-row">
                                <div class="d-flex align-items-center justify-content-between u-title">
                                    <h5 class="portal-title">
                                        <?= esc_html(get_the_title($course)); ?>
                                    </h5>
                                    <button class="d-flex align-items-center gap-2 w-auto text-primary">
                                        <img class="copy-img" src="<?= get_template_directory_uri(); ?>/assets/img/nd/copy-link.png" alt=""> Copy link
                                    </button>
                                </div>
                                <button class="d-flex align-items-center gap-2 w-auto text-primary fs18 fw-medium">
                                    <a href="<?= esc_url(home_url("/{$store_user}/store")); ?>">
                                        <img class="object-fit-contain w14" src="<?= get_template_directory_uri(); ?>/assets/img/nd/back-emp.png" alt=""> Go back
                                    </a>
                                </button>
                            </div>
                        </div>
                        <div class="d-flex flex-column">
                            <div>
                                <div class="d-flex justify-content-between my-1">
                                    <?php if ($instructor): ?>
                                        <div class="d-flex align-items-center">
                                            <i class="me-2 text-primary-color bi bi-person-fill"></i>
                                            <span><strong>Instructor:</strong> <?= esc_html($instructor); ?></span>
                                        </div>
                                    <?php endif; ?>

                                    <?php if ($duration): ?>
                                        <div class="d-flex align-items-center">
                                            <i class="me-2 text-success bi bi-clock-fill"></i>
                                            <span><strong>Duration:</strong> <?= esc_html($duration); ?></span>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <div class="pb-4">
                                    <div class="img271">
                                        <img class="w-100 h-100 object-fit-cover" src="<?= get_template_directory_uri(); ?>/assets/img/nd/legacy.png" alt="">
                                    </div>
                                </div>
                                <div>
                                    <?php if ($short_details): ?>
                                        <div class="mb-4 custom-card">
                                            <p class="mb-3 lead"><?= esc_html($short_details); ?></p>

                                            <?php
                                            $shareable_link = home_url("/{$store_user}/store/{$course_slug}/?shareable=1");
                                            ?>

                                            <div class="text-end">
                                                <button id="copyLinkBtn" class="btn-outline-primary btn btn-sm">
                                                    <i class="bi bi-link-45deg"></i> Copy Sharable Link
                                                </button>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                    <?= apply_filters('the_content', $course->post_content); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3">
                        <div class="upcoming-events">
                            <div class="d-flex u-title">
                                <h5 class="pb-4 portal-title">Benefits</h5>
                            </div>
                            <div class="d-flex flex-column gap-3 pb-4 border-underline">
                                <div class="d-flex gap-2">
                                    <div>
                                        <img src="<?= get_template_directory_uri(); ?>/assets/img/nd/right-sign.png" alt="">
                                    </div>
                                    <span>Full-day guided experience</span>
                                </div>
                                <div class="d-flex gap-2">
                                    <div>
                                        <img src="<?= get_template_directory_uri(); ?>/assets/img/nd/right-sign.png" alt="">
                                    </div>
                                    <span>Personalized empowerment coaching</span>
                                </div>
                                <div class="d-flex gap-2">
                                    <div>
                                        <img src="<?= get_template_directory_uri(); ?>/assets/img/nd/right-sign.png" alt="">
                                    </div>
                                    <span>Deep clarity and purpose</span>
                                </div>
                                <div class="d-flex gap-2">
                                    <div>
                                        <img src="<?= get_template_directory_uri(); ?>/assets/img/nd/right-sign.png" alt="">
                                    </div>
                                    <span>Reconnection with inner self</span>
                                </div>
                                <div class="d-flex gap-2">
                                    <div>
                                        <img src="<?= get_template_directory_uri(); ?>/assets/img/nd/right-sign.png" alt="">
                                    </div>
                                    <span>Transformative legacy activation</span>
                                </div>
                            </div>
                            <?php
                            $buyer_id = get_current_user_id();
                            $purchased_courses = get_user_meta($buyer_id, 'purchased_courses', true);

                            // Ensure it's an array (in case it's unserialized)
                            if (!is_array($purchased_courses)) {
                                $purchased_courses = maybe_unserialize($purchased_courses);
                            }

                            $already_purchased = is_array($purchased_courses) && in_array($course->ID, $purchased_courses);
                            ?>
                            <div>
                                <?php if ($price): ?>
                                    <div class="my-4">
                                        <p class="d-flex align-items-center gap-3">
                                            <span class="fs24">Price:</span>
                                            <span class="fs32">$<?= esc_html($price); ?></span>
                                        </p>
                                        <?php if ($already_purchased): ?>
                                            <div class="alert alert-success fw-semibold">✅ You already purchased this course.</div>
                                        <?php else: ?>
                                            <div id="paypal-button-container"></div>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php
endif;

wp_reset_postdata();

if (!$is_shareable) :
?>
    <!-- ✅ Load PayPal SDK -->
    <script src="https://www.paypal.com/sdk/js?client-id=AZa6Vc9ozb9u_jAWi2osWWV_l5N118jTksbXvPJVID0HXixe_7NYbI4L9TV6OdpY110MEUgW4j7zqAal&currency=USD"></script>

    <!-- ✅ PayPal Button Script -->
    <script>
        function initializePayPalButtons() {
            if (typeof paypal === 'undefined') {
                console.error("PayPal SDK failed to load.");
                return;
            }

            paypal.Buttons({
                createOrder: function(data, actions) {
                    return actions.order.create({
                        purchase_units: [{
                            amount: {
                                value: "<?= esc_js($price ?: '10.00'); ?>"
                            },
                            description: "<?= esc_js(get_the_title($course)); ?>"
                        }]
                    });
                },
                onApprove: async function(data, actions) {
                    try {
                        const details = await actions.order.capture();

                        //alert('Transaction completed by ' + details.payer.name.given_name);

                        const response = await fetch("<?= admin_url('admin-ajax.php'); ?>", {
                            method: "POST",
                            headers: {
                                "Content-Type": "application/x-www-form-urlencoded"
                            },
                            body: new URLSearchParams({
                                action: "handle_course_purchase",
                                course_id: "<?= esc_js($course->ID); ?>",
                                amount: "<?= esc_js($price); ?>",
                                referrer: sessionStorage.getItem("referrer") || ""
                            })
                        });

                        const result = await response.json();
                        //console.log(result);

                        window.location.href = "<?= esc_url($custom_permalink); ?>";
                    } catch (err) {
                        console.error("Error during payment:", err);
                        alert("An error occurred while processing your purchase. Please try again.");
                    }
                }
            }).render('#paypal-button-container');
        }

        document.addEventListener("DOMContentLoaded", function() {
            // Referral logic
            const urlParams = new URLSearchParams(window.location.search);
            const isShareable = urlParams.get('shareable');

            if (isShareable === '1') {
                const segments = window.location.pathname.split('/');
                const referrer = segments[1]; // e.g., "joseph"

                if (referrer) {
                    sessionStorage.setItem("referred_by", referrer);
                    alert(`You were referred by ${referrer}. Your referral link is now saved!`);

                    const cleanUrl = window.location.origin + window.location.pathname;
                    window.history.replaceState({}, document.title, cleanUrl);
                }
            }

            // Wait until PayPal is fully available
            if (typeof paypal === 'undefined') {
                const interval = setInterval(() => {
                    if (typeof paypal !== 'undefined') {
                        clearInterval(interval);
                        initializePayPalButtons();
                    }
                }, 100);
            } else {
                initializePayPalButtons();
            }
        });

        document.addEventListener('DOMContentLoaded', function() {
            var button = document.getElementById('copyLinkBtn');
            var link = <?= json_encode($shareable_link); ?>;

            button.addEventListener('click', function() {
                if (navigator.clipboard && navigator.clipboard.writeText) {
                    navigator.clipboard.writeText(link)
                        .then(() => alert("Sharable link copied!"))
                        .catch(() => fallbackCopy(link));
                } else {
                    fallbackCopy(link);
                }
            });

            function fallbackCopy(text) {
                var textarea = document.createElement("textarea");
                textarea.value = text;
                document.body.appendChild(textarea);
                textarea.select();
                try {
                    document.execCommand('copy');
                    alert("Sharable link copied!");
                } catch (err) {
                    alert("Failed to copy the link.");
                }
                document.body.removeChild(textarea);
            }
        });
    </script>
<?php endif; ?>

<?php get_footer_based_on_login(); ?>