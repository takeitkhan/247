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

        // Check privacy before displaying the post
        if (!UserProfileData::canViewPost($post_id, get_current_user_id())) {
            continue; // Skip this post if user doesn't have permission
        }

        // Clean duplicate reactions for current user
        if (is_user_logged_in()) {
            clean_user_reactions($post_id, get_current_user_id());
        }

        $post_time   = get_the_date('F j, Y \a\t g:i A');
        $post_author = get_the_author();
        $post_image  = get_the_post_thumbnail_url($post_id, 'large');
?>


        <div class="post custom-card">
            <!-- Post Header -->
            <div class="d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-3">
                    <div class="position-relative img44">
                        <img src="<?php echo esc_url(get_user_meta($user_id, 'profile_photo', true) ?: get_template_directory_uri() . '/assets/img/loggedin_images/banner.jpg'); ?>" class="rounded-circle w-100 h-100 object-fit-cover" alt="Profile">
                        <img class="position-absolute active-icon" src="<?php echo get_template_directory_uri(); ?>/assets/img/nd/active_icon.png" alt="">
                    </div>
                    <div class="d-flex flex-column post-user">
                        <span class="p_name"><?php echo esc_html($profile['first_name'] . ' ' . $profile['last_name']); ?></span>
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <span><?php echo esc_html($post_time); ?></span>
                            <?php display_post_status_badge($post_id); ?>
                        </div>
                        <?php display_scheduled_time_info($post_id); ?>
                    </div>
                </div>
                <div class="dropdown">
                    <button class="p-0 border-0 text-dark btn btn-link" type="button" id="postOptions<?php echo $post_id; ?>" data-bs-toggle="dropdown" aria-expanded="false">
                        <img src="<?php echo get_template_directory_uri(); ?>/assets/img/nd/post_option_icon.png" alt="Post Options">
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="postOptions<?php echo $post_id; ?>">
                        <li>
                            <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#privacyModal<?php echo $post_id; ?>">
                                Edit Privacy
                            </a>
                        </li>
                    </ul>
                </div>

                <!-- Privacy Edit Modal -->
                <?php
                $post_privacy = get_post_meta($post_id, '_post_privacy', true) ?: 'only_me';
                ?>
                <div class="modal fade" id="privacyModal<?php echo $post_id; ?>" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Edit Post Privacy</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <select class="form-select post-privacy-select" data-post-id="<?php echo $post_id; ?>" data-current-privacy="<?php echo esc_attr($post_privacy); ?>">
                                    <option value="only_me" <?php selected($post_privacy, 'only_me'); ?>>Only Me</option>
                                    <option value="referral_partners" <?php selected($post_privacy, 'referral_partners'); ?>>Only Referral Partners</option>
                                    <option value="public" <?php selected($post_privacy, 'public'); ?>>Public</option>
                                </select>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button type="button" class="btn btn-primary save-privacy-btn" data-post-id="<?php echo $post_id; ?>">Save</button>
                            </div>
                        </div>
                    </div>
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

            <!-- Reactions Section -->
            <?php
            $reaction_counts = get_post_reaction_counts($post_id);
            $user_reactions = is_user_logged_in() ? get_user_reactions($post_id, get_current_user_id()) : [];
            $total_reactions = array_sum($reaction_counts);
            
            // Get comments count
            $comments_count = get_comments([
                'post_id' => $post_id,
                'status'  => 'approve',
                'count'   => true
            ]);
            
            $template_uri = get_template_directory_uri();
            $icons_path = $template_uri . '/assets/img/nd/icons';
            $comment_nonce = wp_create_nonce('post_comment_nonce');
            ?>
            
            <hr class="border-underline my-2" />
            
            <!-- Reactions & Comments Stats -->
            <div class="post-stats d-flex gap-4 px-3 py-2 small text-muted">
                <?php if ($total_reactions > 0): ?>
                    <span class="d-flex align-items-center gap-1 reaction-stat">
                        <img src="<?php echo esc_url($icons_path . '/reaction.svg'); ?>" alt="Reactions" style="width: 16px; height: 16px;">
                        <span><?php echo esc_html($total_reactions); ?></span>
                    </span>
                <?php endif; ?>
                
                <?php if ($comments_count > 0): ?>
                    <span class="d-flex align-items-center gap-1 comment-stat">
                        <img src="<?php echo esc_url($icons_path . '/comments.svg'); ?>" alt="Comments" style="width: 16px; height: 16px;">
                        <span id="comment-count-<?php echo $post_id; ?>"><?php echo esc_html($comments_count); ?></span>
                    </span>
                <?php endif; ?>
            </div>

            <hr class="border-underline my-0" />

            <!-- Action Buttons -->
            <div class="d-flex gap-2 px-2 pb-2 flex-wrap">
                <button class="btn btn-sm btn-light reaction-btn" data-post-id="<?php echo $post_id; ?>" data-reaction="like" title="Like">
                    <img src="<?php echo esc_url($icons_path . '/reaction.svg'); ?>" alt="Like" style="width: 16px; height: 16px; margin-right: 4px;"> Like
                </button>
                <button class="btn btn-sm btn-light reaction-btn" data-post-id="<?php echo $post_id; ?>" data-reaction="love" title="Love">
                    <img src="<?php echo esc_url($icons_path . '/reaction.svg'); ?>" alt="Love" style="width: 16px; height: 16px; margin-right: 4px;"> Love
                </button>
                <button class="btn btn-sm btn-light reaction-btn" data-post-id="<?php echo $post_id; ?>" data-reaction="happy" title="Happy">
                    <img src="<?php echo esc_url($icons_path . '/reaction.svg'); ?>" alt="Happy" style="width: 16px; height: 16px; margin-right: 4px;"> Haha
                </button>
                <button class="btn btn-sm btn-light reaction-btn" data-post-id="<?php echo $post_id; ?>" data-reaction="wow" title="Wow">
                    <img src="<?php echo esc_url($icons_path . '/reaction.svg'); ?>" alt="Wow" style="width: 16px; height: 16px; margin-right: 4px;"> Wow
                </button>
                <button class="btn btn-sm btn-light reaction-btn" data-post-id="<?php echo $post_id; ?>" data-reaction="sad" title="Sad">
                    <img src="<?php echo esc_url($icons_path . '/reaction.svg'); ?>" alt="Sad" style="width: 16px; height: 16px; margin-right: 4px;"> Sad
                </button>
                <button class="btn btn-sm btn-light reaction-btn" data-post-id="<?php echo $post_id; ?>" data-reaction="angry" title="Angry">
                    <img src="<?php echo esc_url($icons_path . '/reaction.svg'); ?>" alt="Angry" style="width: 16px; height: 16px; margin-right: 4px;"> Angry
                </button>
            </div>

            <hr class="border-underline my-0" />

            <!-- Comments Section -->
            <div class="pt-3 px-3">
                <!-- Comments Container -->
                <div class="comment-section mb-3" id="comments-<?php echo $post_id; ?>" data-nonce="<?php echo esc_attr($comment_nonce); ?>"></div>

                <!-- Comment Input -->
                <?php if (is_user_logged_in()): ?>
                <div class="mt-2 pt-2 border-top">
                    <div class="d-flex gap-2 align-items-start">
                        <div class="position-relative" style="width: 36px; height: 36px; flex-shrink: 0;">
                            <img src="<?php echo esc_url(get_user_meta(get_current_user_id(), 'profile_photo', true) ?: get_template_directory_uri() . '/assets/img/loggedin_images/banner.jpg'); ?>" alt="You" class="rounded-circle w-100 h-100" style="object-fit: cover;">
                        </div>
                        <div class="flex-grow-1 position-relative">
                            <input type="text" class="form-control comment-input" data-post-id="<?php echo $post_id; ?>" data-nonce="<?php echo esc_attr($comment_nonce); ?>" placeholder="Write a comment..." style="border-radius: 18px; padding: 10px 14px; border: 1px solid #e0e0e0; background-color: #f0f2f5; font-size: 14px;">
                            <img src="<?php echo esc_url($icons_path . '/smile.svg'); ?>" alt="Emoji" class="position-absolute" style="right: 10px; top: 50%; transform: translateY(-50%); width: 18px; height: 18px; cursor: pointer;">
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>



<?php
    endwhile;
    wp_reset_postdata();
else:
    echo '<p class="p-3">No posts found.</p>';
endif;
?>