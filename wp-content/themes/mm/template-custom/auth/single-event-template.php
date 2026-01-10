<?php

/**
 * Template Name: Single Event Template
 */

$event_user = get_query_var('event_user');
$is_shareable = get_query_var('shareable'); // '1' if shareable URL

$event_slug = get_query_var('event_slug');
$event = get_page_by_path($event_slug, OBJECT, 'event');


if (!$event) {
    if (!$is_shareable) {
        get_header();
    }
    echo '<p class="text-danger">Event not found.</p>';
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

setup_postdata($event);

$price = get_field('price', $event->ID);
$instructor = get_field('instructor', $event->ID);
$duration = get_field('duration', $event->ID);
$short_details = get_field('short_details', $event->ID);
$thumbnail_url = get_the_post_thumbnail_url($event->ID, 'large') ?: '/img/banner.jpg';
$custom_permalink = home_url("/{$event_user}/event/{$event_slug}/");

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

    include __DIR__ . '/event-parts/shareable-event.php';
    ?>

<?php else: ?>

    <main>
        <div class="main-container s-main-con">
            <div class="row g-3">
                <div class="d-md-block bottom-0 position-sticky col d-none">
                    <div class="bg-white custom-box-shadow p-3 custom-border-radius h-100">
                        <?php include 'event-parts/left-column.php'; ?>
                    </div>
                </div>
                <div class="ms-md-auto col-12 col-md-8 col-lg-9 col-xl-9">
                    <div class="bg-white custom-box-shadow mb-3 p-3 custom-border-radius">
                        <nav aria-label="breadcrumb" class="mb-4">
                            <ol class="d-flex align-items-center breadcrumb">
                                <li class="breadcrumb-item">
                                    <a href="<?= esc_url(home_url("/{$event_user}/event")); ?>">Event</a>
                                </li>
                                <li class="breadcrumb-item active" aria-current="page">
                                    <?= esc_html(get_the_title($event)); ?>
                                </li>
                            </ol>
                        </nav>

                        <div class="col-md-12">
                            <img
                                src="<?= esc_url($thumbnail_url); ?>"
                                alt="<?= esc_attr(get_the_title($event)); ?>"
                                class="shadow-sm rounded img-fluid" />
                        </div>
                        <div class="col-md-12">
                            <div class="d-flex flex-wrap align-items-start justify-content-between gap-3 mt-3 mb-4">
                                <h1 class="flex-grow-1 mb-0 h3"><?= esc_html(get_the_title($event)); ?></h1>
                            </div>

                            <div class="text-end" style="flex: 1 1 100%; margin-top:1rem;">
                                <?php
                                $shareable_link = home_url("/{$event_user}/event/{$event_slug}/?shareable=1");
                                ?>
                                <button id="copyLinkBtn" class="btn-outline-primary btn btn-sm">
                                    <i class="bi bi-link-45deg"></i> Copy Shareable Link
                                </button>
                            </div>

                            <h2 class="mb-3 h4">Event Description</h2>

                            <div class="mb-4">
                                <?= apply_filters('the_content', $event->post_content); ?>
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
    <script>
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
<?php
    get_footer();
}
