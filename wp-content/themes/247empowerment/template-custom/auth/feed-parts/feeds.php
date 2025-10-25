<?php
// Use $user_id from including file or current user if not set
if (!isset($user_id)) {
    $current_user = wp_get_current_user();
    $user_id = $current_user->ID;
}

if (!$user_id) {
    echo '<p>Please log in to see your feed.</p>';
    return;
}

$profile_instance = new UserProfileData($user_id);
$referral_users = $profile_instance->getReferredUsers();

$user_ids = [$user_id];
foreach ($referral_users as $ref_user) {
    $user_ids[] = $ref_user->ID;
}
$user_ids = array_unique($user_ids);

$args = [
    'post_type'      => 'post',
    'post_status'    => 'publish',
    'author__in'     => $user_ids,
    'posts_per_page' => 10,
    'orderby'        => 'date',
    'order'          => 'DESC',
];

$query = new WP_Query($args);

if ($query->have_posts()):
    while ($query->have_posts()): $query->the_post();

        $post_id = get_the_ID();
        $post_time = get_the_date('F j, Y \a\t g:i A');
        $post_author_id = get_post_field('post_author', $post_id);
        $post_image = get_the_post_thumbnail_url($post_id, 'large');

        // Get author profile data similarly
        $author_profile = new UserProfileData($post_author_id);
        $author_data = $author_profile->getProfile();

        // Fallbacks if no profile data
        $author_first_name = $author_data['first_name'] ?? get_the_author_meta('first_name', $post_author_id);
        $author_last_name  = $author_data['last_name'] ?? get_the_author_meta('last_name', $post_author_id);
        $author_photo      = get_user_meta($post_author_id, 'profile_photo', true) ?: get_template_directory_uri() . '/assets/img/loggedin_images/banner.jpg';

        // Prepare URLs for delete link
        $redirect_url = urlencode(is_singular() ? get_permalink() : (wp_get_referer() ?: home_url()));
        $delete_url = wp_nonce_url(
            admin_url('admin-post.php?action=delete_custom_post&post_id=' . $post_id . '&redirect_to=' . $redirect_url),
            'delete_post_' . $post_id
        );
?>
        <div class="bg-white mb-3 post-card">
            <div class="p-3">
                <!-- Post Header with Author and Menu -->
                <div class="d-flex align-items-start justify-content-between">
                    <div class="d-flex post-author">
                        <div class="me-3 post-author-img">
                            <img
                                class="rounded-circle w-100 h-100 object-fit-cover"
                                src="<?php echo esc_url($author_photo); ?>"
                                alt="<?php echo esc_attr($author_first_name . ' ' . $author_last_name); ?>" />
                        </div>
                        <div>
                            <h5 class="post-author-name">
                                <?php echo esc_html($author_first_name . ' ' . $author_last_name); ?>
                            </h5>
                            <div class="d-flex align-items-center gap-2">
                                <span class="post-time"><?php echo esc_html($post_time); ?></span>
                                <img src="<?php echo get_template_directory_uri(); ?>/assets/img/loggedin_images/dot2.png" alt="" />
                                <img src="<?php echo get_template_directory_uri(); ?>/assets/img/loggedin_images/earth.png" alt="" />
                            </div>
                        </div>
                    </div>

                    <?php
                    $current_user_id = get_current_user_id();
                    $post_author_id = get_post_field('post_author', $post_id);

                    if ($current_user_id === (int) $post_author_id): ?>
                        <!-- Three-dot dropdown menu -->
                        <div class="dropdown">
                            <button class="p-0 border-0 text-dark btn btn-link" type="button" id="postOptions<?php echo $post_id; ?>" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-three-dots fs-5"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="postOptions<?php echo $post_id; ?>">
                                <li>
                                    <a class="text-danger dropdown-item"
                                        href="<?php echo esc_url($delete_url); ?>"
                                        onclick="return confirm('Are you sure you want to delete this post?');">
                                        Delete
                                    </a>
                                </li>
                            </ul>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Post Content -->
                <?php
                $raw_content = get_the_content();
                $allowed_tags = ['br' => []];
                $sanitized_content = wp_kses($raw_content, $allowed_tags);
                $formatted_content = nl2br($sanitized_content);
                $trimmed_content = wp_trim_words(strip_tags($formatted_content), 40, '...');
                ?>
                <p class="mt-3 mb-1 post-content-text"
                    data-full="<?php echo esc_attr($formatted_content); ?>"
                    data-trimmed="<?php echo esc_attr($trimmed_content); ?>">
                    <?php echo $trimmed_content; ?>
                    <span class="text-primary read-more-text" style="cursor:pointer;"> See more</span>
                </p>
            </div>

            <?php if ($post_image): ?>
                <div class="post-content-img">
                    <img class="w-100 h-100 object-fit-cover"
                        src="<?php echo esc_url($post_image); ?>"
                        alt="" />
                </div>
            <?php endif; ?>
        </div>
<?php
    endwhile;
    wp_reset_postdata();
else:
    echo '<p class="p-3">No posts found.</p>';
endif;
?>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Delegate click on all current and future read-more-text spans
        document.body.addEventListener('click', function(event) {
            if (event.target.classList.contains('read-more-text')) {
                const span = event.target;
                const p = span.parentElement;

                const fullText = p.getAttribute('data-full');
                const trimmedText = p.getAttribute('data-trimmed');

                if (span.textContent.trim() === 'See more') {
                    p.innerHTML = fullText + ' <span class="text-primary read-more-text" style="cursor:pointer;"> See less</span>';
                } else {
                    p.innerHTML = trimmedText + ' <span class="text-primary read-more-text" style="cursor:pointer;"> See more</span>';
                }
            }
        });
    });
</script>