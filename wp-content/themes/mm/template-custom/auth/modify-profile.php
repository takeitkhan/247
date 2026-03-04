<?php
/* Template Name: Modify Profile */
get_header();

if (!is_user_logged_in()) {
    wp_redirect(wp_login_url());
    exit;
}

$current_user = wp_get_current_user();
$dob   = get_user_meta($current_user->ID, 'dob', true);
$phone = get_user_meta($current_user->ID, 'phone', true);
$referrer = get_user_meta($current_user->ID, 'referrer', true);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    if (!isset($_POST['frontend_profile_update_nonce']) || !wp_verify_nonce($_POST['frontend_profile_update_nonce'], 'frontend_profile_update')) {
        echo '<div class="alert alert-danger">Security check failed.</div>';
    } else {
        $user_id = $current_user->ID;

        wp_update_user([
            'ID'         => $user_id,
            'first_name' => sanitize_text_field($_POST['first_name']),
            'last_name'  => sanitize_text_field($_POST['last_name']),
            'user_email' => sanitize_email($_POST['email']),
        ]);

        update_user_meta($user_id, 'dob', sanitize_text_field($_POST['dob']));
        update_user_meta($user_id, 'phone', sanitize_text_field($_POST['phone']));
        update_user_meta($user_id, 'about_me', sanitize_textarea_field($_POST['about_me']));
        update_user_meta($user_id, 'about_me_short', sanitize_text_field($_POST['about_me_short']));
        // update_user_meta($user_id, 'location', sanitize_text_field($_POST['location']));
        update_user_meta($user_id, 'show_email', isset($_POST['show_email']) ? '1' : '0');
        update_user_meta($user_id, 'show_phone', isset($_POST['show_phone']) ? '1' : '0');
        update_user_meta($user_id, 'show_dob', isset($_POST['show_dob']) ? '1' : '0');


        echo '
        <script>
        document.addEventListener("DOMContentLoaded", function() {
                            // Clear auto-saved draft after successful update
                            localStorage.removeItem("profile_draft");
                            localStorage.removeItem("profile_draft_timestamp");
                            
            gravity: "bottom", // `top` or `bottom`
            position: "left", // `left`, `center` or `right`
            backgroundColor: "#28a745",
            close: true,
            stopOnFocus: true,
            }).showToast();
        });
        </script>
        ';


        $current_user = wp_get_current_user();
        $dob   = get_user_meta($current_user->ID, 'dob', true);
        $phone = get_user_meta($current_user->ID, 'phone', true);
    }
}

?>

<main>
    <div class="main-container" style="padding-top: 80px">

        <div class="row g-3">
            <?php include get_template_directory() . '/template-custom/auth/profile-parts/edit-profile-left-sidebar.php'; ?>

            <div class="ms-md-auto col-12 col-md-8 col-lg-9 col-xl-9">
                <div class="bg-white custom-box-shadow mb-3 p-3 custom-border-radius">
                    <div class="row">
                        <div class="col-6">
                            <h5 class="mb-5">👤 My Profile</h5>
                        </div>
                    </div>
                    
                    <!-- Auto Save Status -->
                    <div id="autosave-status" class="alert alert-info d-none mb-3" role="alert">
                        <small>
                            <i class="bi bi-hourglass-split"></i> <span id="autosave-message">Draft auto-saving...</span>
                            <span id="autosave-last-saved" class="float-end"></span>
                        </small>
                    </div>
                    
                    <form method="post" id="profile-form">
                        <?php wp_nonce_field('frontend_profile_update', 'frontend_profile_update_nonce'); ?>

                        <div class="row">
                            <div class="col">
                                <div class="mb-3">
                                    <label class="form-label">First Name</label>
                                    <input type="text" name="first_name" class="form-control" value="<?php echo esc_attr($current_user->first_name); ?>">
                                </div>
                            </div>
                            <div class="col">
                                <div class="mb-3">
                                    <label class="form-label">Last Name</label>
                                    <input type="text" name="last_name" class="form-control" value="<?php echo esc_attr($current_user->last_name); ?>">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col">
                                <div class="mb-3">
                                    <label class="form-label">Email</label>
                                    <input type="email" name="email" class="form-control" value="<?php echo esc_attr($current_user->user_email); ?>" required>
                                </div>
                                <div class="mb-3 form-check">
                                    <input type="checkbox" class="form-check-input" id="show_email" name="show_email" value="1" <?php checked(get_user_meta($current_user->ID, 'show_email', true), '1'); ?>>
                                    <label class="form-check-label" for="show_email">Show Email on profile</label>
                                </div>
                            </div>


                            <div class="col">
                                <div class="mb-3">
                                    <label class="form-label">Phone</label>
                                    <input type="text" name="phone" class="form-control" value="<?php echo esc_attr($phone); ?>">
                                </div>
                                <div class="mb-3 form-check">
                                    <input type="checkbox" class="form-check-input" id="show_phone" name="show_phone" value="1" <?php checked(get_user_meta($current_user->ID, 'show_phone', true), '1'); ?>>
                                    <label class="form-check-label" for="show_phone">Show Phone on profile</label>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col">
                                <div class="mb-3">
                                    <label class="form-label">Date of Birth</label>
                                    <input type="date" name="dob" class="form-control" value="<?php echo esc_attr($dob); ?>">
                                </div>
                                <div class="mb-3 form-check">
                                    <input type="checkbox" class="form-check-input" id="show_dob" name="show_dob" value="1" <?php checked(get_user_meta($current_user->ID, 'show_dob', true), '1'); ?>>
                                    <label class="form-check-label" for="show_dob">Show Phone on profile</label>
                                </div>
                            </div>
                            <div class="col">
                                <div class="mb-3">
                                    <label class="form-label">About Me (One Liner)</label>
                                    <input type="text" name="about_me_short" class="form-control" value="<?php echo esc_attr(get_user_meta($current_user->ID, 'about_me_short', true)); ?>">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col">
                                <div class="mb-3">
                                    <label class="form-label">About Me (Full Description)</label>
                                    <textarea name="about_me" class="form-control"><?php echo esc_textarea(get_user_meta($current_user->ID, 'about_me', true)); ?></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col">
                                <div class="mb-3">
                                    <label class="form-label">Referrer</label>
                                    <input type="text" class="form-control" value="<?php echo esc_attr($referrer); ?>" disabled>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Choose Categories</label>
                            <?php
                            $categories = get_categories(['hide_empty' => false]);
                            $selected_cats = get_user_meta($current_user->ID, 'user_categories', true);
                            $selected_cats = is_array($selected_cats) ? $selected_cats : [];

                            foreach ($categories as $cat) :
                            ?>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="user_categories[]" value="<?php echo esc_attr($cat->term_id); ?>"
                                        <?php checked(in_array($cat->term_id, $selected_cats)); ?>>
                                    <label class="form-check-label"><?php echo esc_html($cat->name); ?></label>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" name="update_profile" class="btn btn-primary">Update Profile</button>
                            <button type="button" id="restore-draft-btn" class="btn btn-outline-secondary d-none">Restore Draft</button>
                        </div>
                    </form>
                </div>
            </div>
            
            <script>
            // Dummy function for Google Maps callback (suppress error)
            window.initProfileMap = function() {
                console.log("📍 Google Maps callback called (no map on this page)");
            };
            
            // Initialize auto-save immediately (no waiting for DOMContentLoaded)
            console.log("🚀 Auto-save script loaded!");
            
            const formId = "profile-form";
            const storageKey = "profile_draft";
            const timestampKey = "profile_draft_timestamp";
            const autoSaveInterval = 30000; // 30 seconds
            
            let form = null;
            let statusDiv = null;
            let statusMessage = null;
            let lastSavedSpan = null;
            let restoreDraftBtn = null;
            
            // Function to initialize when elements are ready
            function initializeAutoSave() {
                form = document.getElementById(formId);
                statusDiv = document.getElementById("autosave-status");
                statusMessage = document.getElementById("autosave-message");
                lastSavedSpan = document.getElementById("autosave-last-saved");
                restoreDraftBtn = document.getElementById("restore-draft-btn");
                
                console.log("📋 Form found:", form ? "YES ✅" : "NO ❌");
                console.log("🎨 Status div found:", statusDiv ? "YES ✅" : "NO ❌");
                
                if (!form) {
                    console.error("❌ Form not found! Retrying...");
                    setTimeout(initializeAutoSave, 500);
                    return;
                }
                
                console.log("✅ All elements found, setting up listeners...");
                setupAutoSave();
            }
            
            function setupAutoSave() {
                // Save draft to localStorage
                function saveDraft() {
                    const inputs = {};
                    
                    // Get all input values
                    form.querySelectorAll("input[type='text'], input[type='email'], input[type='date']").forEach(input => {
                        if (input.name) inputs[input.name] = input.value;
                    });
                    
                    // Get textarea values
                    form.querySelectorAll("textarea").forEach(textarea => {
                        if (textarea.name) inputs[textarea.name] = textarea.value;
                    });
                    
                    // Get checkbox values
                    form.querySelectorAll("input[type='checkbox']").forEach(checkbox => {
                        if (checkbox.name) {
                            if (!inputs[checkbox.name]) inputs[checkbox.name] = [];
                            if (checkbox.checked) {
                                if (Array.isArray(inputs[checkbox.name])) {
                                    inputs[checkbox.name].push(checkbox.value);
                                }
                            }
                        }
                    });
                    
                    localStorage.setItem(storageKey, JSON.stringify(inputs));
                    localStorage.setItem(timestampKey, new Date().toLocaleTimeString());
                    
                    console.log("💾 SAVED to localStorage:", new Date().toLocaleTimeString());
                    console.log("📦 Data:", inputs);
                    
                    // Show green alert
                    if (statusDiv) {
                        statusDiv.classList.remove("d-none");
                        statusDiv.classList.add("alert-success");
                        statusDiv.classList.remove("alert-info", "alert-warning");
                        statusMessage.innerHTML = '<i class="bi bi-check-circle"></i> Draft saved';
                        lastSavedSpan.textContent = 'Last saved: ' + new Date().toLocaleTimeString();
                        
                        setTimeout(() => {
                            statusDiv.classList.add("d-none");
                        }, 2000);
                    }
                }
                
                // Attach blur listeners to all input fields
                const allInputs = form.querySelectorAll("input[type='text'], input[type='email'], input[type='date'], textarea");
                console.log("📝 Attaching blur listeners to " + allInputs.length + " fields...");
                
                allInputs.forEach((field, index) => {
                    field.addEventListener("blur", function(e) {
                        console.log("👉 BLUR EVENT on field:", this.name, "| Value:", this.value.substring(0, 50));
                        saveDraft();
                    });
                });
                
                // Checkbox changes
                form.addEventListener("change", function(e) {
                    if (e.target.type === "checkbox") {
                        console.log("☑️  CHECKBOX changed:", e.target.name, "=", e.target.checked);
                        saveDraft();
                    }
                });
                
                // Form submit - clear draft
                form.addEventListener("submit", function() {
                    console.log("📤 Form submitted - clearing draft");
                    localStorage.removeItem(storageKey);
                    localStorage.removeItem(timestampKey);
                });
                
                // Every 30 seconds, auto-save
                setInterval(saveDraft, autoSaveInterval);
                
                console.log("✅✅✅ AUTO-SAVE FULLY INITIALIZED ✅✅✅");
                console.log("👉 Move between fields to see auto-save in action!");
            }
            
            // Start initialization
            if (document.readyState === "loading") {
                document.addEventListener("DOMContentLoaded", initializeAutoSave);
            } else {
                initializeAutoSave();
            }
            </script>
        </div>
    </div>
</main>
<?php get_footer(); ?>