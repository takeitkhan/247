<?php
/**
 * Modern Enhanced Posting Modal - Phase 1 (Reorganized)
 * 
 * STRUCTURE:
 * 1. Trigger Card - Quick access to create post
 * 2. Modal Container
 *    2a. Header (User info + Close)
 *    2b. Tab Navigation (Instant | Schedule)
 *    2c. Tab Content
 *        - Instant Post Tab
 *        - Schedule Post Tab
 *    2d. Footer (Actions)
 * 
 * DESIGN PRINCIPLES:
 * - DRY (Don't Repeat Yourself): Shared components are consistent
 * - Two-column layout: Text editor (L) + Options (R)
 * - Progressive disclosure: Options collapse by tab type
 * - Clear visual hierarchy
 */

$current_user = wp_get_current_user();
$user_photo = get_user_meta(get_current_user_id(), 'profile_photo', true) ?: get_template_directory_uri() . '/assets/img/loggedin_images/banner.jpg';
$template_uri = get_template_directory_uri();
$nonce = wp_create_nonce('create_post_action');
?>

<!-- ============================================
     1. TRIGGER CARD - Opens Modal
     ============================================ -->
<div class="mb-3 custom-card post-search">
    <div class="d-flex align-items-center gap-3 mb-2 post-row">
        <div>
            <div class="position-relative img44">
                <img src="<?php echo esc_url($user_photo); ?>" class="rounded-circle w-100 h-100 object-fit-cover" alt="Profile">
                <img class="position-absolute active-icon" src="<?php echo esc_url($template_uri); ?>/assets/img/nd/active_icon.png" alt="Active">
            </div>
        </div>
        <input
            id="post-trigger"
            type="text"
            class="w-100 input"
            placeholder="What's on your mind?"
            data-bs-toggle="modal"
            data-bs-target="#createPostModalRedesigned"
            readonly>
    </div>
</div>

<!-- ============================================
     2. MODAL CONTAINER
     ============================================ -->
<div class="modal fade" id="createPostModalRedesigned" tabindex="-1" aria-labelledby="modalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl posting-modal-dialog">
        <div class="modal-content posting-modal-redesigned">
            <form id="create-post-form-redesigned" enctype="multipart/form-data">

                <!-- 2a. MODAL HEADER -->
                <div class="modal-header posting-modal-header">
                    <div class="d-flex align-items-center gap-3 flex-grow-1">
                        <div class="position-relative img44">
                            <img src="<?php echo esc_url($user_photo); ?>" class="rounded-circle w-100 h-100 object-fit-cover" alt="Profile">
                            <img class="position-absolute active-icon" src="<?php echo esc_url($template_uri); ?>/assets/img/nd/active_icon.png" alt="Active">
                        </div>
                        <div class="d-flex flex-column post-user">
                            <span class="p_name fw-bold"><?php echo esc_html($current_user->first_name . ' ' . $current_user->last_name); ?></span>
                            <span class="small text-muted">Public account</span>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <!-- 2b. TAB NAVIGATION -->
                <div class="posting-modal-tabs">
                    <ul class="nav nav-tabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="instant-tab" data-bs-toggle="tab" data-bs-target="#instantPost" type="button" role="tab" aria-selected="true">
                                <i class="bi bi-send me-2"></i>Instant Post
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="schedule-tab" data-bs-toggle="tab" data-bs-target="#schedulePost" type="button" role="tab" aria-selected="false">
                                <i class="bi bi-calendar-event me-2"></i>Schedule Post
                            </button>
                        </li>
                    </ul>
                </div>

                <!-- 2c. TAB CONTENT -->
                <div class="tab-content posting-modal-content">
                    
                    <!-- INSTANT POST TAB -->
                    <div class="tab-pane fade show active" id="instantPost" role="tabpanel" aria-labelledby="instant-tab">
                        <div class="modal-body posting-modal-body">
                            <?php include 'create-post-parts/instant-post-body.php'; ?>
                        </div>
                        <!-- Modal Footer - Instant Post -->
                        <div class="modal-footer posting-modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary btn-lg posting-submit-btn" id="submit-instant-post">
                                <i class="bi bi-send me-2"></i>Share Now
                            </button>
                        </div>
                    </div>

                    <!-- SCHEDULE POST TAB -->
                    <div class="tab-pane fade" id="schedulePost" role="tabpanel" aria-labelledby="schedule-tab">
                        <div class="modal-body posting-modal-body">
                            <?php include 'create-post-parts/schedule-post-body.php'; ?>
                        </div>
                        <!-- Modal Footer - Schedule Post -->
                        <div class="modal-footer posting-modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary btn-lg posting-submit-btn" id="submit-schedule-post">
                                <i class="bi bi-calendar-event me-2"></i>Schedule Post
                            </button>
                        </div>
                    </div>

                </div>
            </form>
        </div>
    </div>
</div>
