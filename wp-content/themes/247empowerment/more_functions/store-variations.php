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

if (!defined('ABSPATH')) exit;

// -----------------------------------------------------------
// 1. Register meta box
// -----------------------------------------------------------
add_action('add_meta_boxes', function () {
    add_meta_box(
        'mm_course_variations',
        'Product / Course Variations',
        'mm_course_variations_meta_box_html',
        'course',
        'normal',
        'high'
    );
});

function mm_course_variations_meta_box_html($post)
{
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
                });
            });
        }

        $('#mm-var-add').on('click', function(){
            list.find('[data-placeholder]').remove();
            var tpl = $('#mm-var-template').html().replace(/__INDEX__/g, list.find('.mm-var-row').length);
            list.append(tpl);
        });

        list.on('click', '.mm-var-remove', function(){
            if (!confirm('Remove this variation?')) return;
            $(this).closest('.mm-var-row').remove();
            reindex();
            if (!list.find('.mm-var-row').length) {
                list.append('<div class="mm-var-empty" data-placeholder>No variations yet. Click "Add variation" below.</div>');
            }
        });
    })(jQuery);
    </script>
    <?php
}

function mm_render_variation_row($index, $v)
{
    $label = esc_attr($v['label'] ?? '');
    $desc  = esc_textarea($v['desc']  ?? '');
    $price = esc_attr($v['price'] ?? '');
    $sku   = esc_attr($v['sku']   ?? '');
    $billing = $v['billing'] ?? 'onetime';
    $plan_id = $v['plan_id'] ?? '';
    ?>
    <div class="mm-var-row">
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
                    <option value="monthly" <?= selected($billing, 'monthly', false) ?>>Monthly</option>
                    <option value="yearly"  <?= selected($billing, 'yearly',  false) ?>>Yearly</option>
                </select>
                <?php if ($plan_id): ?>
                    <small style="color:#46b450;display:block;margin-top:4px;" title="<?= esc_attr($plan_id) ?>">✓ Plan linked</small>
                <?php elseif (in_array($billing, ['monthly', 'yearly'], true)): ?>
                    <small style="color:#d63638;display:block;margin-top:4px;">⚠ No PayPal plan yet — save to create</small>
                <?php endif; ?>
            </div>
            <div>
                <label>SKU (optional)</label>
                <input type="text" name="mm_variations[<?= $index ?>][sku]" value="<?= $sku ?>" placeholder="BASIC-01">
            </div>
            <div>
                <label>Description</label>
                <textarea name="mm_variations[<?= $index ?>][desc]" placeholder="Short description shown to buyer"><?= $desc ?></textarea>
            </div>
            <div>
                <button type="button" class="mm-var-remove" title="Remove">✕</button>
            </div>
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

            $billing = in_array($row['billing'] ?? 'onetime', ['onetime', 'monthly', 'yearly'], true)
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
                            $interval = $billing === 'yearly' ? 'YEAR' : 'MONTH';
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
                'desc'    => isset($row['desc']) ? sanitize_textarea_field($row['desc']) : '',
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
