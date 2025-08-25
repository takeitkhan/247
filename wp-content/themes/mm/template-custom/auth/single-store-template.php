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
        get_header();
    }
    echo '<p class="text-danger">Course not found.</p>';
    if (!$is_shareable) {
        get_footer();
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

// Only load header if NOT shareable
if (!$is_shareable) {
    get_header();
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

    <main>
        <div class="main-container s-main-con">
            <div class="row g-3">
                <div class="d-md-block bottom-0 position-sticky col d-none">
                    <div class="bg-white custom-box-shadow p-3 custom-border-radius h-100">
                        <?php include 'store-parts/left-column.php'; ?>
                    </div>
                </div>
                <div class="ms-md-auto col-12 col-md-8 col-lg-9 col-xl-9">
                    <div class="bg-white custom-box-shadow mb-3 p-3 custom-border-radius">
                        <nav aria-label="breadcrumb" class="mb-4">
                            <ol class="d-flex align-items-center breadcrumb">
                                <li class="breadcrumb-item">
                                    <a href="<?= esc_url(home_url("/{$store_user}/store")); ?>">Store</a>
                                </li>
                                <li class="breadcrumb-item active" aria-current="page">
                                    <?= esc_html(get_the_title($course)); ?>
                                </li>
                            </ol>
                        </nav>

                        <div class="col-md-12">
                            <img
                                src="<?= esc_url($thumbnail_url); ?>"
                                alt="<?= esc_attr(get_the_title($course)); ?>"
                                class="shadow-sm rounded img-fluid" />
                        </div>
                        <div class="col-md-12">
                            <div class="d-flex flex-wrap align-items-start justify-content-between gap-3 mt-3 mb-4">
                                <h1 class="flex-grow-1 mb-0 h3"><?= esc_html(get_the_title($course)); ?></h1>
                            </div>

                            <div class="d-flex flex-wrap align-items-center gap-4 mb-3 text-muted fs-6">
                                <?php if ($instructor): ?>
                                    <div class="d-flex align-items-center">
                                        <i class="me-2 text-primary bi bi-person-fill"></i>
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



                            <?php if ($short_details || $price): ?>
                                <div class="d-flex flex-wrap gap-3 mb-4">
                                    <?php if ($short_details): ?>
                                        <div style="flex: 1 1 60%; background: #f8f9fa; padding: 1.5rem; border: 1px solid #e4e7eb; box-shadow: 0 0 8px rgb(0 0 0 / 0.05); border-radius: 0.375rem;">
                                            <p class="mb-3 lead"><?= esc_html($short_details); ?></p>

                                            <?php
                                            $shareable_link = home_url("/{$store_user}/store/{$course_slug}/?shareable=1");
                                            ?>

                                            <div class="text-end">
                                                <button class="btn-outline-primary btn btn-sm" onclick="copySharableLink('<?= esc_url($shareable_link); ?>')">
                                                    <i class="bi bi-link-45deg"></i> Copy Sharable Link
                                                </button>
                                            </div>
                                        </div>
                                    <?php endif; ?>


                                    <?php
                                    $buyer_id = get_current_user_id();
                                    $purchased_courses = get_user_meta($buyer_id, 'purchased_courses', true);

                                    // Ensure it's an array (in case it's unserialized)
                                    if (!is_array($purchased_courses)) {
                                        $purchased_courses = maybe_unserialize($purchased_courses);
                                    }

                                    $already_purchased = is_array($purchased_courses) && in_array($course->ID, $purchased_courses);
                                    ?>

                                    <?php if ($price): ?>
                                        <div style="flex: 1 1 35%; background-color: #d1e7dd; padding: 1.5rem; border: 1px solid #badbcc; box-shadow: 0 0 8px rgb(0 0 0 / 0.1); border-radius: 0.375rem; text-align: center;">
                                            <div class="d-flex align-items-center justify-content-center gap-2 mb-3">
                                                <span class="text-success fw-semibold fs-5">Price:</span>
                                                <span class="text-primary fw-bold h3">$<?= esc_html($price); ?></span>
                                            </div>

                                            <?php if ($already_purchased): ?>
                                                <div class="alert alert-success fw-semibold">✅ You already purchased this course.</div>
                                            <?php else: ?>
                                                <div id="paypal-button-container"></div>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>

                                </div>
                            <?php endif; ?>


                            <h2 class="mb-3 h4">Course Description</h2>

                            <div class="mb-4">
                                <?= apply_filters('the_content', $course->post_content); ?>
                            </div>
                            <?php
                            if (is_user_logged_in()) {
                                $current_user_id = get_current_user_id();
                                $referrer = get_user_meta($current_user_id, 'referrer', true);

                                if ($referrer) {
                                    // Get user object by ID or login
                                    $referrer_user = is_numeric($referrer)
                                        ? get_user_by('ID', $referrer)
                                        : get_user_by('login', $referrer);

                                    if ($referrer_user) {
                                        $referrer_name = $referrer_user->display_name;
                                        $referrer_profile = home_url('/' . $referrer_user->user_login);
                            ?>
                                        <div class="mb-5">
                                            <div class="d-flex align-items-center alert alert-info">
                                                <i class="text-primary bi bi-stars fs-6"></i>
                                                <div>
                                                    <strong>Referred by:</strong>
                                                    <a href="<?= esc_url($referrer_profile); ?>" class="text-primary">
                                                        <?= esc_html($referrer_name); ?>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                            <?php
                                    }
                                }
                            }
                            ?>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
<?php
endif;

wp_reset_postdata();

if (!$is_shareable) {
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

        function copySharableLink(link) {
            navigator.clipboard.writeText(link)
                .then(() => alert("Sharable link copied!"))
                .catch(() => alert("Failed to copy the link."));
        }
    </script>


<?php
    get_footer();
}
