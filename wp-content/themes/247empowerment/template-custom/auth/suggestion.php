<?php
/**
 * Template Name: Suggestion Template
 * Custom Suggestion Template
 */
get_header();
?>

<main>
    <div class="main-container" style="padding-top: 80px;">
        <div class="bg-white custom-box-shadow mb-3 p-3 custom-border-radius">
            <h5 class="mb-3">Submit a Suggestion</h5>

            <?php
            if (isset($_POST['submit_suggestion'])) {
                $name         = sanitize_text_field($_POST['name']);
                $email        = sanitize_email($_POST['email']);
                $phone        = sanitize_text_field($_POST['phone']);
                $subject      = sanitize_text_field($_POST['subject']);
                $suggestion_type = sanitize_text_field($_POST['suggestion_type']);
                $description  = sanitize_textarea_field($_POST['description']);
                $datetime     = sanitize_text_field($_POST['datetime']);
                $page_url     = esc_url_raw($_POST['page_url']);
                $consent      = isset($_POST['consent']) ? 'Yes' : 'No';

                $attachment_url = '';
                if (!empty($_FILES['attachment']['name'])) {
                    require_once ABSPATH . 'wp-admin/includes/image.php';
                    require_once ABSPATH . 'wp-admin/includes/file.php';
                    require_once ABSPATH . 'wp-admin/includes/media.php';

                    $uploaded = media_handle_upload('attachment', 0);
                    if (!is_wp_error($uploaded)) {
                        $attachment_url = wp_get_attachment_url($uploaded);
                    }
                }

                // Insert into WP
                $post_id = wp_insert_post([
                    'post_type'   => 'suggestion_report', // Or create custom post type like 'suggestion'
                    'post_title'  => $subject . ' (' . $name . ')',
                    'post_status' => 'publish',
                ]);

                if ($post_id && !is_wp_error($post_id)) {
                    update_post_meta($post_id, 'type', 'suggestion');
                    update_post_meta($post_id, 'name', $name);
                    update_post_meta($post_id, 'email', $email);
                    update_post_meta($post_id, 'phone', $phone);
                    update_post_meta($post_id, 'suggestion_type', $suggestion_type);
                    update_post_meta($post_id, 'description', $description);
                    update_post_meta($post_id, 'datetime', $datetime);
                    update_post_meta($post_id, 'page_url', $page_url);
                    update_post_meta($post_id, 'consent', $consent);
                    update_post_meta($post_id, 'attachment_url', $attachment_url);
                }

                echo '<div class="alert alert-success">Thank you for your suggestion!</div>';
            }
            ?>

            <form method="post" enctype="multipart/form-data" class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Your Name (optional)</label>
                    <input type="text" name="name" class="form-control">
                </div>

                <div class="col-md-6">
                    <label class="form-label">Email Address *</label>
                    <input type="email" name="email" class="form-control" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Phone Number (optional)</label>
                    <input type="text" name="phone" class="form-control">
                </div>

                <div class="col-md-6">
                    <label class="form-label">Subject / Title *</label>
                    <input type="text" name="subject" class="form-control" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Suggestion Type *</label>
                    <select name="suggestion_type" class="form-select" required>
                        <option value="">Select Type</option>
                        <option value="Feature Request">Feature Request</option>
                        <option value="Improvement Idea">Improvement Idea</option>
                        <option value="Content Suggestion">Content Suggestion</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Date & Time (optional)</label>
                    <input type="datetime-local" name="datetime" class="form-control">
                </div>

                <div class="col-12">
                    <label class="form-label">Suggestion Details *</label>
                    <textarea name="description" class="form-control" rows="4" required></textarea>
                </div>

                

                <div class="col-12">
                    <label class="form-label">Page URL (optional)</label>
                    <input type="url" name="page_url" class="form-control" value="<?php echo esc_url(home_url($_SERVER['REQUEST_URI'])); ?>">
                </div>

                <div class="col-12">
                    <label class="form-label">Upload File / Screenshot (optional)</label>
                    <input type="file" name="attachment" class="form-control" accept="image/*,.pdf,.txt">
                </div>

                <div class="col-12">
                    <input class="form-check-input" type="checkbox" name="consent" id="consentCheckbox" required>
                    <label class="form-check-label" for="consentCheckbox">
                        I agree to the terms and allow you to contact me regarding this suggestion.
                    </label>
                </div>

                <div class="col-12">
                    <button type="submit" name="submit_suggestion" class="btn btn-primary">Submit Suggestion</button>
                </div>
            </form>
        </div>
    </div>
</main>
<?php get_footer(); ?>