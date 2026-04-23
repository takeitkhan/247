<?php
/**
 * Course / Product Variations
 * -----------------------------------------------------------
 * Adds a backend meta box on the `course` CPT that lets admins
 * create multiple variations (label, description, price, sku).
 *
 * Variations are stored as a single post meta `_course_variations`
 * holding a JSON-encoded array:
 *   [
 *     { "label": "Basic",    "desc": "1 month access", "price": "19.00", "sku": "BASIC" },
 *     { "label": "Pro",      "desc": "3 month access", "price": "49.00", "sku": "PRO"   },
 *     { "label": "Lifetime", "desc": "Forever",        "price": "199.00","sku": "LIFE"  }
 *   ]
 *
 * Helper: mm_get_course_variations($course_id) → array
 */

// ======================
// DEBUG: Diagnostic page for troubleshooting
// ======================

add_action('wp_ajax_test_variations_system', 'test_variations_system');
add_action('wp_ajax_nopriv_test_variations_system', 'test_variations_system');

function test_variations_system()
{
    if (!current_user_can('manage_options')) {
        wp_send_json_error(['message' => 'Unauthorized']);
    }

    $checks = [];

    // Check 1: Function exists
    $checks['function_exists'] = function_exists('mm_get_course_variations');

    // Check 2: Meta box action
    global $wp_filter;
    $checks['meta_box_action_registered'] = isset($wp_filter['add_meta_boxes']);

    // Check 3: Course CPT exists
    $cpt = get_post_type_object('course');
    $checks['course_cpt_exists'] = $cpt ? true : false;
    $checks['course_cpt_show_ui'] = $cpt->show_ui ?? false;

    // Check 4: Test course exists
    $course = get_posts(['post_type' => 'course', 'numberposts' => 1]);
    $checks['course_posts_exist'] = count($course) > 0;
    if ($course) {
        $course = $course[0];
        $checks['test_course_id'] = $course->ID;
        
        // Try to get variations
        $variations = mm_get_course_variations($course->ID);
        $checks['variations_readable'] = true;
        $checks['variation_count'] = count($variations);
    }

    // Check 5: Debug log
    $debug_log = wp_upload_dir();
    $debug_file = WP_CONTENT_DIR . '/debug.log';
    $checks['debug_file_exists'] = file_exists($debug_file);
    if (file_exists($debug_file)) {
        $checks['debug_file_size'] = filesize($debug_file);
        $last_lines = shell_exec("tail -20 " . escapeshellarg($debug_file));
        $checks['debug_last_lines'] = $last_lines;
    }

    wp_send_json_success($checks);
}

// ======================
// Admin Notice for Debugging
// ======================

add_action('admin_notices', function() {
    if (!current_user_can('manage_options')) return;
    if (get_current_screen()->post_type !== 'course') return;
    
    $script = "
        <script>
        jQuery(document).ready(function($) {
            $.post(ajaxurl, {action: 'test_variations_system'}, function(r) {
                if (r.success) {
                    console.log('Variations System Check:', r.data);
                    if (!r.data.function_exists) {
                        console.error('❌ mm_get_course_variations() function NOT found');
                    } else {
                        console.log('✅ mm_get_course_variations() function exists');
                    }
                    
                    if (!r.data.course_cpt_exists) {
                        console.error('❌ Course CPT NOT registered');
                    } else {
                        console.log('✅ Course CPT registered, show_ui:', r.data.course_cpt_show_ui);
                    }
                    
                    if (r.data.course_posts_exist) {
                        console.log('✅ Course posts exist (test ID:', r.data.test_course_id + ')');
                    } else {
                        console.log('⚠ No course posts found');
                    }
                } else {
                    console.error('Diagnostic error:', r);
                }
            });
        });
        </script>
    ";
    echo $script;
});

add_action('add_meta_boxes', function () {
    // DEBUG: Log meta box registration
    error_log('DEBUG: Registering meta box for course post type');
    
    add_meta_box(
        'mm_course_variations',
        'Product / Course Variations',
        'mm_course_variations_meta_box_html',
        'course',
        'normal',
        'high'
    );
});

// Force meta box to show (priority 999 to run after screen options filter)
add_action('add_meta_boxes_course', function() {
    global $wp_meta_boxes;
    
    // Check if meta box exists and is hidden by screen options
    if (!isset($wp_meta_boxes['course']['normal']['high']['mm_course_variations'])) {
        error_log('DEBUG: Meta box not found, re-registering forcefully');
        
        add_meta_box(
            'mm_course_variations',
            'Product / Course Variations',
            'mm_course_variations_meta_box_html',
            'course',
            'normal',
            'high'
        );
    }
}, 999);

function mm_course_variations_meta_box_html($post)
{
    error_log('DEBUG: Meta box callback called for post ID: ' . $post->ID);
    
    wp_nonce_field('mm_course_variations_save', 'mm_course_variations_nonce');
    $variations = mm_get_course_variations($post->ID);
    ?>
    <style>
        .mm-var-wrap { margin-top: 10px; }
        .mm-var-row {
            background: #fff; border: 1px solid #ccd0d4; border-radius: 4px;
            padding: 12px; margin-bottom: 10px; position: relative;
        }
        .mm-var-row .mm-var-grid {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr 1fr 3fr auto;
            gap: 10px; align-items: start;
        }
        .mm-var-row input[type="text"],
        .mm-var-row input[type="number"],
        .mm-var-row textarea { width: 100%; }
        .mm-var-row textarea { min-height: 60px; }
        .mm-var-row label {
            display: block; font-weight: 600; margin-bottom: 4px; font-size: 12px;
            text-transform: uppercase; color: #555;
        }
        .mm-var-remove {
            background: #dc3232; color: #fff; border: 0; padding: 6px 10px;
            border-radius: 3px; cursor: pointer; height: fit-content; margin-top: 22px;
        }
        .mm-var-remove:hover { background: #a00; }
        #mm-var-add {
            background: #2271b1; color: #fff; border: 0; padding: 8px 16px;
            border-radius: 3px; cursor: pointer; font-size: 14px;
        }
        #mm-var-add:hover { background: #135e96; }
        .mm-var-empty { color: #888; font-style: italic; padding: 10px 0; }
    </style>

    <div class="mm-var-wrap">
        <p class="description">
            Add one row per variation. Each variation has its own price. When a buyer
            selects a variation on the product page, the PayPal payment uses that
            variation's price.
        </p>

        <div id="mm-var-list">
            <?php if (empty($variations)): ?>
                <div class="mm-var-empty" data-placeholder>No variations yet. Click "Add variation" below.</div>
            <?php else: foreach ($variations as $i => $v): ?>
                <?php mm_render_variation_row($i, $v); ?>
            <?php endforeach; endif; ?>
        </div>

        <p><button type="button" id="mm-var-add">+ Add variation</button></p>
    </div>

    <!-- Template for a new row -->
    <script type="text/template" id="mm-var-template">
        <?php mm_render_variation_row('__INDEX__', ['label' => '', 'desc' => '', 'price' => '', 'sku' => '', 'billing' => 'onetime']); ?>
    </script>

    <script>
    (function($){
        var list = $('#mm-var-list');

        function reindex() {
            list.find('.mm-var-row').each(function(i){
                $(this).find('[name]').each(function(){
                    var name = $(this).attr('name').replace(/mm_variations\[[^\]]+\]/, 'mm_variations[' + i + ']');
                    $(this).attr('name', name);
                    
                    // Also update editor IDs if present
                    if ($(this).attr('id')) {
                        var newId = $(this).attr('id').replace(/mm_var_desc_\d+/, 'mm_var_desc_' + i);
                        $(this).attr('id', newId);
                    }
                });
            });
        }

        function reinitializeEditors() {
            // Reinitialize TinyMCE for newly added editors
            if (window.tinymce) {
                list.find('[id^="mm_var_desc_"]').each(function() {
                    var editorId = $(this).attr('id');
                    
                    // Remove existing editor if present
                    if (window.tinymce.get(editorId)) {
                        window.tinymce.get(editorId).destroy();
                    }
                    
                    // Initialize editor with teeny mode
                    window.tinymce.init({
                        selector: '#' + editorId,
                        mode: 'textareas',
                        theme: 'modern',
                        plugins: 'lists,link,image,paste,textcolor',
                        toolbar: 'formatselect | bold italic underline | bullist numlist | link image | forecolor',
                        menubar: false,
                        statusbar: false,
                        height: 150
                    });
                });
            }
        }

        $('#mm-var-add').on('click', function(){
            list.find('[data-placeholder]').remove();
            var tpl = $('#mm-var-template').html().replace(/__INDEX__/g, list.find('.mm-var-row').length);
            list.append(tpl);
            reindex();
            reinitializeEditors();
        });

        list.on('click', '.mm-var-remove', function(){
            if (!confirm('Remove this variation?')) return;
            var rowId = $(this).closest('.mm-var-row').find('[id^="mm_var_desc_"]').attr('id');
            
            // Destroy editor if present
            if (rowId && window.tinymce && window.tinymce.get(rowId)) {
                window.tinymce.get(rowId).destroy();
            }
            
            $(this).closest('.mm-var-row').remove();
            reindex();
            if (!list.find('.mm-var-row').length) {
                list.append('<div class="mm-var-empty" data-placeholder>No variations yet. Click "Add variation" below.</div>');
            }
        });
        
        // Initialize editors on page load
        $(document).ready(function() {
            reinitializeEditors();
        });
    })(jQuery);
    </script>
    <?php
}

function mm_render_variation_row($index, $v)
{
    $label = esc_attr($v['label'] ?? '');
    $desc  = $v['desc'] ?? '';
    $price = esc_attr($v['price'] ?? '');
    $sku   = esc_attr($v['sku']   ?? '');
    $billing = $v['billing'] ?? 'onetime';
    $plan_id = $v['plan_id'] ?? '';
    
    // Generate unique editor ID
    $editor_id = 'mm_var_desc_' . $index;
    ?>
    <div class="mm-var-row">
        <!-- Top Row: Label, Price, Billing, SKU, Remove Button -->
        <div class="mm-var-grid">
            <div>
                <label>Label</label>
                <input type="text" name="mm_variations[<?= $index ?>][label]" value="<?= $label ?>" placeholder="e.g. Basic Plan" required>
            </div>
            <div>
                <label>Price (USD)</label>
                <input type="number" step="0.01" min="0" name="mm_variations[<?= $index ?>][price]" value="<?= $price ?>" placeholder="19.00" required>
            </div>
            <div>
                <label>Billing</label>
                <select name="mm_variations[<?= $index ?>][billing]">
                    <option value="onetime" <?= selected($billing, 'onetime', false) ?>>One-time</option>
                    <option value="weekly" <?= selected($billing, 'weekly', false) ?>>Weekly</option>
                    <option value="monthly" <?= selected($billing, 'monthly', false) ?>>Monthly</option>
                    <option value="yearly"  <?= selected($billing, 'yearly',  false) ?>>Yearly</option>
                </select>
                <?php if ($plan_id): ?>
                    <small style="color:#46b450;display:block;margin-top:4px;" title="<?= esc_attr($plan_id) ?>">✓ Plan linked</small>
                <?php elseif (in_array($billing, ['monthly', 'yearly', 'weekly'], true)): ?>
                    <small style="color:#d63638;display:block;margin-top:4px;">⚠ No PayPal plan yet — save to create</small>
                <?php endif; ?>
            </div>
            <div>
                <label>SKU (optional)</label>
                <input type="text" name="mm_variations[<?= $index ?>][sku]" value="<?= $sku ?>" placeholder="BASIC-01">
            </div>
            <div>
                <button type="button" class="mm-var-remove" title="Remove">✕</button>
            </div>
        </div>

        <!-- Full-width Description Editor -->
        <div style="margin-top: 15px; padding-top: 15px; border-top: 1px solid #eee;">
            <label style="display: block; font-weight: 600; margin-bottom: 8px; font-size: 12px; text-transform: uppercase; color: #555;">
                Description
            </label>
            <?php
            // Use WordPress built-in editor
            wp_editor(
                $desc,
                $editor_id,
                [
                    'textarea_name' => 'mm_variations[' . $index . '][desc]',
                    'media_buttons' => false,
                    'textarea_rows' => 5,
                    'teeny' => true, // Minimal toolbar
                    'quicktags' => true,
                ]
            );
            ?>
        </div>

        <input type="hidden" name="mm_variations[<?= $index ?>][plan_id]" value="<?= esc_attr($plan_id) ?>">
    </div>
    <?php
}

// -----------------------------------------------------------
// 2. Save handler
// -----------------------------------------------------------
add_action('save_post_course', function ($post_id) {
    if (!isset($_POST['mm_course_variations_nonce']) ||
        !wp_verify_nonce($_POST['mm_course_variations_nonce'], 'mm_course_variations_save')) {
        return;
    }
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!current_user_can('edit_post', $post_id)) return;

    $raw = $_POST['mm_variations'] ?? [];
    $existing = mm_get_course_variations($post_id); // keyed by numeric index
    $clean = [];
    $errors = [];

    if (is_array($raw)) {
        foreach ($raw as $row) {
            $label = isset($row['label']) ? sanitize_text_field($row['label']) : '';
            $price = isset($row['price']) ? floatval($row['price']) : 0;
            if ($label === '' || $price <= 0) continue;

            $billing = in_array($row['billing'] ?? 'onetime', ['onetime', 'weekly', 'monthly', 'yearly'], true)
                ? $row['billing']
                : 'onetime';

            $sku = isset($row['sku']) ? sanitize_text_field($row['sku']) : '';

            // Try to find an existing record that matches this row (by label+billing)
            // so we can reuse its plan_id when price hasn't changed.
            $prev = null;
            foreach ($existing as $e) {
                if (($e['label'] ?? '') === $label && ($e['billing'] ?? 'onetime') === $billing) {
                    $prev = $e;
                    break;
                }
            }

            $plan_id = '';
            if ($billing === 'onetime') {
                $plan_id = ''; // not needed
            } else {
                // Subscription: reuse plan_id only if price unchanged
                $prev_price = $prev['price'] ?? '';
                $prev_plan  = $prev['plan_id'] ?? '';
                if ($prev_plan && $prev_price !== '' && floatval($prev_price) === floatval($price)) {
                    $plan_id = $prev_plan;
                } else {
                    // Create product (once per course) then plan
                    if (function_exists('mm_pp_get_or_create_product')) {
                        $product_id = mm_pp_get_or_create_product($post_id);
                        if (is_wp_error($product_id)) {
                            $errors[] = "Could not create PayPal product for \"$label\": " . $product_id->get_error_message();
                        } else {
                            // Map billing type to PayPal interval
                            $interval_map = [
                                'weekly'  => 'WEEK',
                                'monthly' => 'MONTH',
                                'yearly'  => 'YEAR',
                            ];
                            $interval = $interval_map[$billing] ?? 'MONTH';
                            $plan = mm_pp_create_plan($product_id, $label, $price, $interval);
                            if (is_wp_error($plan)) {
                                $errors[] = "Could not create PayPal plan for \"$label\": " . $plan->get_error_message();
                            } else {
                                $plan_id = $plan;
                            }
                        }
                    }
                }
            }

            $clean[] = [
                'label'   => $label,
                'price'   => number_format($price, 2, '.', ''),
                'sku'     => $sku,
                'desc'    => isset($row['desc']) ? wp_kses_post($row['desc']) : '', // Allow HTML from editor
                'billing' => $billing,
                'plan_id' => $plan_id,
            ];
        }
    }

    if ($clean) {
        update_post_meta($post_id, '_course_variations', wp_json_encode($clean));
    } else {
        delete_post_meta($post_id, '_course_variations');
    }

    // Deactivate PayPal plans that existed before but are no longer present.
    // (This prevents new subscriptions to a deleted variation but keeps
    // existing subscribers billing until they cancel or the plan fully ends.)
    if (function_exists('mm_pp_request')) {
        $still_used_plans = array_filter(array_map(function ($r) { return $r['plan_id'] ?? ''; }, $clean));
        foreach ($existing as $e) {
            $old_plan = $e['plan_id'] ?? '';
            if (!$old_plan) continue;
            if (in_array($old_plan, $still_used_plans, true)) continue;
            $resp = mm_pp_request('POST', '/v1/billing/plans/' . rawurlencode($old_plan) . '/deactivate');
            if (is_wp_error($resp)) {
                $errors[] = "Could not deactivate PayPal plan {$old_plan}: " . $resp->get_error_message();
            }
        }
    }

    if ($errors) {
        // Surface errors as transient so they display as admin notice on next load
        set_transient('mm_var_errors_' . $post_id, $errors, 60);
    }
});

// Display any plan-creation errors as an admin notice after save
add_action('admin_notices', function () {
    global $post;
    if (!$post || $post->post_type !== 'course') return;
    $errors = get_transient('mm_var_errors_' . $post->ID);
    if (!$errors) return;
    delete_transient('mm_var_errors_' . $post->ID);
    echo '<div class="notice notice-error"><p><strong>PayPal variation issues:</strong></p><ul style="list-style:disc;padding-left:20px;">';
    foreach ($errors as $e) echo '<li>' . esc_html($e) . '</li>';
    echo '</ul></div>';
});

// -----------------------------------------------------------
// 3. Helpers
// -----------------------------------------------------------
/**
 * Get all variations for a course.
 *
 * @param int $course_id
 * @return array List of ['label','desc','price','sku'] (possibly empty).
 */
function mm_get_course_variations($course_id)
{
    $raw = get_post_meta($course_id, '_course_variations', true);
    if (!$raw) return [];
    $data = json_decode($raw, true);
    return is_array($data) ? $data : [];
}

/**
 * Get a single variation by index, or null if not found.
 */
function mm_get_course_variation($course_id, $index)
{
    $all = mm_get_course_variations($course_id);
    $index = (int) $index;
    return isset($all[$index]) ? $all[$index] : null;
}
