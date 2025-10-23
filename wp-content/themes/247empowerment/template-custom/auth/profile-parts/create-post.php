     <div class="mb-3 custom-card post-search">
         <div class="d-flex align-items-center gap-3 mb-2 post-row">
             <div>
                 <div class="position-relative img44">
                     <img src="<?php echo esc_url(get_user_meta(get_current_user_id(), 'profile_photo', true) ?: get_template_directory_uri() . '/assets/img/nd/profile.png'); ?>" class="rounded-circle w-100 h-100 object-fit-cover" alt="Profile">
                     <img class="position-absolute active-icon" src="<?php echo get_template_directory_uri(); ?>/assets/img/nd/active_icon.png" alt="Active">
                 </div>
             </div>
             <input
                 id="txt"
                 type="text"
                 class="w-100 input"
                 placeholder="What's on your mind?"
                 data-bs-toggle="modal"
                 data-bs-target="#createPostModal">
         </div>

         <!-- Input Modal -->
         <div class="modal fade" id="createPostModal" tabindex="-1" aria-labelledby="modalLabel" aria-hidden="true">
             <div class="modal-dialog modal-dialog-centered">
                 <div class="modal-content">
                     <form id="create-post-form" enctype="multipart/form-data">
                         <div class="modal-header">
                             <div class="d-flex align-items-center gap-3">
                                 <div class="position-relative img44">
                                     <img src="<?php echo esc_url(get_user_meta(get_current_user_id(), 'profile_photo', true) ?: get_template_directory_uri() . '/assets/img/nd/profile.png'); ?>" class="rounded-circle w-100 h-100 object-fit-cover" alt="Profile">
                                     <img class="position-absolute active-icon" src="<?php echo get_template_directory_uri(); ?>/assets/img/nd/active_icon.png" alt="Active">
                                 </div>
                                 <div class="d-flex flex-column post-user">
                                     <?php $profile = wp_get_current_user(); ?>
                                     <span class="p_name"><?php echo esc_html($profile->first_name . ' ' . $profile->last_name); ?></span>
                                     <select name="post_privacy" class="bg-neutral-color mt-2 p-size border-0 w-auto custom-select">
                                         <option value="only_me" selected>Only Me</option>
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
                             <div class="position-relative mb-3">
                                 <textarea name="post_content" class="input" rows="8" placeholder="What's on your mind?"></textarea>
                                 <img class="modal-emoji" src="<?php echo get_template_directory_uri(); ?>/assets/img/nd/imogi.png" alt="Emoji">
                             </div>

                             <div class="d-flex flex-md-wrap gap-2 gap-md-4 mb-3">
                                 <label for="photoUpload" class="d-flex align-items-center gap-2" style="cursor:pointer;">
                                     <img src="<?php echo get_template_directory_uri(); ?>/assets/img/nd/gallery.png" alt="Image/Video">
                                     Image/Video
                                 </label>
                                 <input type="file" name="post_image" id="photoUpload" class="d-none">
                                 <!-- <button class="d-flex align-items-center gap-2">
                                     <img src="<?php echo get_template_directory_uri(); ?>/assets/img/nd/location.png" alt="Location">Location
                                 </button>
                                 <button class="d-md-block d-flex align-items-center gap-2 d-none">
                                     <img src="<?php echo get_template_directory_uri(); ?>/assets/img/nd/partner.png" alt="Partner">Tag partner
                                 </button> -->
                             </div>

                             <div class="mb-3 image-preview-container">
                                 <img id="image-preview" src="" alt="Preview" style="display:none; max-width:100%; border-radius:8px;">
                             </div>
                         </div>

                         <div class="mt-0 custom-card">
                             <button type="submit" class="btn-bg-color w-100 custom-btn">Share</button>
                         </div>
                     </form>
                 </div>
             </div>
         </div>
         
         <!-- <div class="d-flex flex-md-wrap gap-2 gap-md-4 mt-3">
             <button class="d-flex align-items-center gap-2">
                 <img src="<?php echo get_template_directory_uri(); ?>/assets/img/nd/gallery.png" alt="Image/Video">
                 Image/Video
             </button>
             <button class="d-flex align-items-center gap-2">
                 <img src="<?php echo get_template_directory_uri(); ?>/assets/img/nd/location.png" alt="Location">Location
             </button>
             <button class="d-md-block d-flex align-items-center gap-2 d-none">
                 <img src="<?php echo get_template_directory_uri(); ?>/assets/img/nd/partner.png" alt="Partner">Tag partner
             </button>
         </div> -->
     </div>