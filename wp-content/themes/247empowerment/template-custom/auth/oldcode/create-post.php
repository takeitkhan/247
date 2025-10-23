<div
    class="bg-white custom-box-shadow mb-3 p-3 custom-border-radius">
    <div>
        <div class="post-create-section">
            <div class="d-flex gap-2">
                <div>
                    <div class="img-box-li">
                        <img
                            class="rounded-circle w-100 h-100 object-fit-cover"
                            src="<?php echo esc_url(get_user_meta(get_current_user_id(), 'profile_photo', true) ?: get_template_directory_uri() . '/assets/img/loggedin_images/banner.jpg'); ?>"
                            alt="" />
                    </div>
                </div>
                <div class="w-100">
                    <button
                        class="t-bg-gray custom-border-radius w-100 custom-btn"
                        data-bs-toggle="modal"
                        data-bs-target="#createPostModal">
                        Create post
                    </button>
                </div>
            </div>
        </div>
        <div
            class="modal fade"
            id="createPostModal"
            tabindex="-1"
            aria-labelledby="createPostModalLabel"
            aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="createPostModalLabel">
                            Create a Post
                        </h5>
                        <button
                            type="button"
                            class="btn-close"
                            data-bs-dismiss="modal"
                            aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <div class="post-author">
                                <div class="post-author-img">
                                    <img
                                        src="<?php echo esc_url(get_user_meta(get_current_user_id(), 'profile_photo', true) ?: get_template_directory_uri() . '/assets/img/loggedin_images/banner.jpg'); ?>"
                                        class="rounded-circle w-100 h-100 object-fit-cover"
                                        alt="Profile Photo" />
                                </div>
                                <div class="">
                                    <h5 class="post-author-name">
                                        <?php
                                        echo esc_html($profile['first_name'] . ' ' . $profile['last_name']);
                                        ?>
                                    </h5>
                                    <!-- <div class="d-flex align-items-center gap-2">
                                                                    <span class="post-time">Yesterday at 14:03</span>
                                                                    <img src="<?php echo get_template_directory_uri(); ?>/assets/img/loggedin_images//dot2.png" alt="" />
                                                                    <img src="<?php echo get_template_directory_uri(); ?>/assets/img/loggedin_images/earth.png" alt="" />
                                                                </div> -->
                                </div>
                            </div>
                        </div>

                        <form id="createPostForm" enctype="multipart/form-data">
                            <input type="hidden" name="action" value="create_post">
                            <input type="hidden" name="create_post_nonce" value="<?php echo wp_create_nonce('create_post_action'); ?>">

                            <div class="mb-3">
                                <textarea name="post_content" class="form-control" placeholder="What's on your mind?" rows="4"></textarea>
                            </div>
                            <div class="d-flex align-items-center justify-content-between px-3 py-2 border rounded">
                                <span class="text-muted fw-semibold">Add to your post</span>
                                <div class="d-flex align-items-center gap-3">
                                    <label for="photoUpload" style="cursor: pointer;" title="Photo/Video">
                                        <img width="24" height="24" src="http://pet.test/wp-content/themes/mm/assets/img/loggedin_images/photoicon.svg" alt="">
                                    </label>

                                    <input type="file" name="post_image" id="photoUpload" class="d-none">

                                </div>
                            </div>

                            <div class="d-flex justify-content-end pt-3 border-0 modal-footer">
                                <button type="button" class="custom-btn" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn-bg-color custom-btn">Post</button>
                            </div>
                        </form>


                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="pt-3">
        <div>
            <div class="d-flex justify-content-between">
                <div class="">
                    <img
                        class="img-res-width"
                        src="<?php echo get_template_directory_uri(); ?>/assets/img/loggedin_images/gallery.png"
                        alt="" />
                    <span class="ms-2 text-res">Photo/Video</span>
                </div>
                <div class="">
                    <img
                        class="img-res-width"
                        src="<?php echo get_template_directory_uri(); ?>/assets/img/loggedin_images/location2.png"
                        alt="" />
                    <span class="ms-2 text-res">Check in </span>
                </div>
                <div class="d-md-block d-none">
                    <img
                        class="img-res-width"
                        src="<?php echo get_template_directory_uri(); ?>/assets/img/loggedin_images/tag.png"
                        alt="" />
                    <span class="ms-2 text-res">Tags Friends</span>
                </div>
            </div>
        </div>
    </div>
</div>