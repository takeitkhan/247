<?php
// Get the username from the URL (author_name is default WP var)
$username = $profile['display_name'] ?? '';


// echo '<pre>';
// var_dump($username);
// echo '</pre>';

// Get the user profile instance
$profile_instance = new UserProfileData($username);
$profile = $profile_instance->getProfile();

// Validate user exists
if (!$profile || empty($profile['id'])) {
    echo '<p class="p-3">User not found.</p>';
    return;
}

$user_id = $profile['id'];

// Fetch user posts
$args = [
    'post_type'      => 'post',
    'post_status'    => 'publish',
    'author'         => $user_id,
    'posts_per_page' => 10,
];

$user_posts = new WP_Query($args);

if ($user_posts->have_posts()):
    while ($user_posts->have_posts()): $user_posts->the_post();
        $post_id     = get_the_ID();
        $post_time   = get_the_date('F j, Y \a\t g:i A');
        $post_author = get_the_author();
        $post_image  = get_the_post_thumbnail_url($post_id, 'large');
?>
        <div class="bg-white mb-3 post-card">
            <div class="p-3">
                <!-- Post Header with Author and Menu -->
                <div class="d-flex align-items-start justify-content-between">
                    <div class="d-flex post-author">
                        <div class="me-3 post-author-img">
                            <img
                                class="rounded-circle w-100 h-100 object-fit-cover"
                                src="<?php echo esc_url(get_user_meta($user_id, 'profile_photo', true) ?: get_template_directory_uri() . '/assets/img/loggedin_images/banner.jpg'); ?>"
                                alt="" />
                        </div>
                        <div>
                            <h5 class="post-author-name">
                                <?php echo esc_html($profile['first_name'] . ' ' . $profile['last_name']); ?>
                            </h5>
                            <div class="d-flex align-items-center gap-2">
                                <span class="post-time"><?php echo esc_html($post_time); ?></span>
                                <img src="<?php echo get_template_directory_uri(); ?>/assets/img/loggedin_images/dot2.png" alt="" />
                                <img src="<?php echo get_template_directory_uri(); ?>/assets/img/loggedin_images/earth.png" alt="" />
                            </div>
                        </div>
                    </div>

                    <!-- Three-dot dropdown menu -->
                    <div class="dropdown">
                        <button class="p-0 border-0 text-dark btn btn-link" type="button" id="postOptions<?php echo $post_id; ?>" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-three-dots fs-5"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="postOptions<?php echo $post_id; ?>">
                            <!-- <li><a class="dropdown-item" href="#">Edit</a></li> -->
                            <?php
                            $redirect_url = urlencode(is_singular() ? get_permalink() : (wp_get_referer() ?: home_url()));

                            $delete_url = wp_nonce_url(
                                admin_url('admin-post.php?action=delete_custom_post&post_id=' . $post_id . '&redirect_to=' . $redirect_url),
                                'delete_post_' . $post_id
                            );
                            ?>
                            <li>
                                <a class="text-danger dropdown-item"
                                    href="<?php echo esc_url($delete_url); ?>"
                                    onclick="return confirm('Are you sure you want to delete this post?');">
                                    Delete
                                </a>
                            </li>

                        </ul>
                    </div>
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
                    <span class="text-primary read-more-text" style="cursor:pointer;"> Read more</span>
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