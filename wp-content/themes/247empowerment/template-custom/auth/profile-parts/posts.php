<?php
// Get the username from the URL (author_name is default WP var)
$profile = $args['profile'] ?? null;
$username = $profile['display_name'] ?? '';
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


        <div class="post custom-card">
            <!-- Post Header -->
            <div class="d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-3">
                    <div class="position-relative img44">
                        <img src="<?php echo esc_url(get_user_meta($user_id, 'profile_photo', true) ?: get_template_directory_uri() . '/assets/img/nd/profile.png'); ?>" class="rounded-circle w-100 h-100 object-fit-cover" alt="Profile">
                        <img class="position-absolute active-icon" src="<?php echo get_template_directory_uri(); ?>/assets/img/nd/active_icon.png" alt="">
                    </div>
                    <div class="d-flex flex-column post-user">
                        <span class="p_name"><?php echo esc_html($profile['first_name'] . ' ' . $profile['last_name']); ?></span>
                        <span><?php echo esc_html($post_time); ?></span>
                    </div>
                </div>
                <div>
                    <img src="<?php echo get_template_directory_uri(); ?>/assets/img/nd/post_option_icon.png" alt="Post Options">
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
            <p class="mt-2 mb-2 post-content-text"
                data-full="<?php echo esc_attr($formatted_content); ?>"
                data-trimmed="<?php echo esc_attr($trimmed_content); ?>">
                <?php echo $trimmed_content; ?>
                <?php if (wp_strip_all_tags($formatted_content) !== $trimmed_content): ?>
                    <span class="text-primary read-more-text" style="cursor:pointer;"> Read more</span>
                <?php endif; ?>
            </p>

            <!-- Post Image -->
            <?php if ($post_image): ?>
                <div class="mt-3 profile-pic post-image-container">
                    <img src="<?php echo esc_url($post_image); ?>" class="w-100 h-100 object-fit-cover" alt="Post Image">
                </div>
            <?php endif; ?>


            <!-- <hr class="border-underline"> -->

            <!-- Post Actions -->
            <!-- <div class="d-flex gap-5">
                <span class="d-flex align-items-center gap-1"><img src="<?php echo get_template_directory_uri(); ?>/assets/img/nd/like.png" alt=""> <?php echo esc_html($like_count ?? 0); ?></span>
                <span class="d-flex align-items-center gap-1"><img src="<?php echo get_template_directory_uri(); ?>/assets/img/nd/comment.png" alt=""> <?php echo esc_html($comment_count ?? 0); ?></span>
                <span class="d-flex align-items-center gap-1"><img src="<?php echo get_template_directory_uri(); ?>/assets/img/nd/share.png" alt=""> <?php echo esc_html($share_count ?? 0); ?></span>
            </div> -->

            <!-- Comment Input -->
            <!-- <div class="d-flex align-items-center gap-3 pt-3">
                <div>
                    <div class="position-relative img44">
                        <img src="<?php echo esc_url(get_user_meta(get_current_user_id(), 'profile_photo', true) ?: get_template_directory_uri() . '/assets/img/nd/profile.png'); ?>" class="rounded-circle w-100 h-100 object-fit-cover" alt="Profile">
                        <img class="position-absolute active-icon" src="<?php echo get_template_directory_uri(); ?>/assets/img/nd/active_icon.png" alt="">
                    </div>
                </div>
                <div class="position-relative w-100">
                    <input type="text" class="w-100 input" placeholder="Write a comment..." data-post-id="<?php echo esc_attr($post_id); ?>">
                    <img class="position-absolute emoji-pos" src="<?php echo get_template_directory_uri(); ?>/assets/img/nd/imogi.png" alt="Emoji">
                </div>
            </div> -->
        </div>



<?php
    endwhile;
    wp_reset_postdata();
else:
    echo '<p class="p-3">No posts found.</p>';
endif;
?>