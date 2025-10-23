
     <div class="mb-3 custom-card post-search">
         <div class="d-flex align-items-center gap-3 mb-2 post-row">
             <div>
                 <div class="position-relative img44">
                     <img src="<?php echo get_template_directory_uri(); ?>/assets/img/nd/profile.png" class="rounded-circle w-100 h-100 object-fit-cover" alt="Profile">
                     <img class="position-absolute active-icon" src="<?php echo get_template_directory_uri(); ?>/assets/img/nd/active_icon.png" alt="">
                 </div>
             </div>
             <!-- Input -->
             <input
                 id="txt"
                 type="text"
                 class="w-100 input"
                 placeholder="What's on your mind?"
                 data-bs-toggle="modal"
                 data-bs-target="#myModal">


             <!-- <button class="d-lg-block ms-2 custom-btn d-none">Post</button> -->
         </div>

         <!-- input Modal -->
         <div class="modal fade" id="myModal" tabindex="-1" aria-labelledby="modalLabel" aria-hidden="true">
             <div class="modal-dialog modal-dialog-centered"> <!-- Centered here -->
                 <div class="modal-content">
                     <div class="modal-header">

                         <div class="d-flex align-items-center gap-3">
                             <div class="position-relative img44">
                                 <img src="<?php echo get_template_directory_uri(); ?>/assets/img/nd/profile.png" class="rounded-circle w-100 h-100 object-fit-cover" alt="Profile">
                                 <img class="position-absolute active-icon" src="<?php echo get_template_directory_uri(); ?>/assets/img/nd/active_icon.png" alt="">
                             </div>
                             <div class="d-flex flex-column post-user">
                                 <span class="p_name">Maria Johnson</span>
                                 <select class="bg-neutral-color mt-2 p-size border-0 w-auto custom-select">
                                     <option selected>Los Angeles, CA</option>
                                     <option value="1">New York, NY</option>
                                     <option value="2">Chicago, IL</option>
                                     <option value="3">San Francisco, CA</option>
                                     <option value="4">Miami, FL</option>
                                 </select>

                             </div>
                         </div>
                         <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                     </div>
                     <div class="modal-body">
                         <div class="position-relative">
                             <textarea class="input" rows="8" placeholder="What's on your mind?"></textarea>
                             <img class="modal-emoji" src="<?php echo get_template_directory_uri(); ?>/assets/img/nd/imogi.png" alt="">
                         </div>
                         <div>
                         </div>
                     </div>
                     <div class="modal-profile">
                         <div class="d-flex flex-md-wrap gap-2 gap-md-4">
                             <button class="d-flex align-items-center gap-2">
                                 <img src="<?php echo get_template_directory_uri(); ?>/assets/img/nd/gallery.png" alt="">
                                 Image/Video
                             </button>
                             <button class="d-flex align-items-center gap-2"> <img src="<?php echo get_template_directory_uri(); ?>/assets/img/nd/location.png" alt="">Location</button>
                             <button class="d-md-block d-flex align-items-center gap-2 d-none"> <img src="<?php echo get_template_directory_uri(); ?>/assets/img/nd/partner.png" alt="">Tag partner</button>
                         </div>
                     </div>
                     <div class="mt-0 custom-card">
                         <button type="button" class="w-100 custom-btn">Share</button>
                     </div>
                 </div>
             </div>
         </div>

         <!-- input modal -->

         <div class="d-flex flex-md-wrap gap-2 gap-md-4 mt-3">
             <button class="d-flex align-items-center gap-2">
                 <img src="<?php echo get_template_directory_uri(); ?>/assets/img/nd/gallery.png" alt="">
                 Image/Video
             </button>
             <button class="d-flex align-items-center gap-2"> <img src="<?php echo get_template_directory_uri(); ?>/assets/img/nd/location.png" alt="">Location</button>
             <button class="d-md-block d-flex align-items-center gap-2 d-none"> <img src="<?php echo get_template_directory_uri(); ?>/assets/img/nd/partner.png" alt="">Tag partner</button>
         </div>
     </div>
     <div class="bg-white custom-box-shadow mb-3 p-3 custom-border-radius">
         <div>
             <div class="post-create-section">
                 <div class="d-flex align-items-center gap-2">
                     <div>
                         <div class="img-box-li">
                             <img
                                 class="rounded-circle w-100 h-100 object-fit-cover"
                                 src="<?php echo esc_url(get_user_meta(get_current_user_id(), 'profile_photo', true) ?: get_template_directory_uri() . '/assets/img/loggedin_images/banner.jpg'); ?>"
                                 alt="Profile" />
                             <img class="position-absolute active-icon" src="<?php echo get_template_directory_uri(); ?>/assets/img/loggedin_images/active_icon.png" alt="">
                         </div>
                     </div>
                     <div class="w-100">
                         <button
                             class="t-bg-gray custom-border-radius w-100 custom-btn"
                             data-bs-toggle="modal"
                             data-bs-target="#createPostModal">
                             What's on your mind?
                         </button>
                     </div>
                 </div>
             </div>

             <!-- Create Post Modal -->
             <div class="modal fade" id="createPostModal" tabindex="-1" aria-labelledby="createPostModalLabel" aria-hidden="true">
                 <div class="modal-dialog modal-dialog-centered">
                     <div class="modal-content">
                         <form id="create-post-form" enctype="multipart/form-data">
                             <div class="modal-header">
                                 <div class="d-flex align-items-center gap-3">
                                     <div class="img-box-li">
                                         <img
                                             class="rounded-circle w-100 h-100 object-fit-cover"
                                             src="<?php echo esc_url(get_user_meta(get_current_user_id(), 'profile_photo', true) ?: get_template_directory_uri() . '/assets/img/loggedin_images/banner.jpg'); ?>"
                                             alt="Profile Photo" />
                                         <img class="position-absolute active-icon" src="<?php echo get_template_directory_uri(); ?>/assets/img/loggedin_images/active_icon.png" alt="">
                                     </div>
                                     <div class="d-flex flex-column post-user">
                                         <span class="p_name">
                                             <?php
                                                $profile = wp_get_current_user();
                                                echo esc_html($profile->first_name . ' ' . $profile->last_name);
                                                ?>
                                         </span>
                                         <select name="post_privacy" class="bg-neutral-color mt-2 p-size border-0 w-auto custom-select">
                                             <option selected value="only_me">Only Me</option>
                                             <option value="referral_partners">Only Referral Partners</option>
                                             <option value="public">Public</option>
                                         </select>

                                     </div>
                                 </div>
                                 <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                             </div>

                             <div class="modal-body">
                                 <input type="hidden" name="action" value="create_post">
                                 <input type="hidden" name="create_post_nonce" value="<?php echo wp_create_nonce('create_post_action'); ?>">
                                 <div class="mb-3">
                                     <textarea name="post_content" class="form-control" placeholder="What's on your mind?" rows="6"></textarea>
                                     <!-- <img class="modal-emoji" src="<?php echo get_template_directory_uri(); ?>/assets/img/loggedin_images/imogi.png" alt=""> -->
                                 </div>

                                 <div class="d-flex flex-md-wrap gap-2 gap-md-4 mb-3">
                                     <label for="photoUpload" class="d-flex align-items-center gap-2" style="cursor:pointer;">
                                         <img src="<?php echo get_template_directory_uri(); ?>/assets/img/loggedin_images/gallery.png" alt="">
                                         Image/Video
                                     </label>
                                     <input type="file" name="post_image" id="photoUpload" class="d-none">
                                 </div>
                                 <div class="mb-3 image-preview-container">
                                     <img id="image-preview" src="" alt="Preview" style="display:none; max-width:100%; border-radius:8px;" />
                                 </div>

                                 <div class="d-flex gap-2 pt-3 border-0 modal-footer">
                                     <button type="button" class="custom-btn">Cancel</button>
                                     <button type="submit" class="btn-bg-color custom-btn">Share</button>
                                 </div>
                             </div>
                         </form>
                     </div>
                 </div>
             </div>
         </div>
     </div>