<?php
if (! defined('ABSPATH')) exit;
/**
 * Returns a list of predefined actions/events
 * key => label
 */
class MM_Gamification_Admin
{

    public function __construct()
    {
        add_action('admin_menu', [$this, 'register_admin_menus']);
    }

    public function register_admin_menus()
    {
        // Main page: Actions List
        add_menu_page(
            'Gamification',
            'Gamification',
            'manage_options',
            'mm-gamification',
            [$this, 'actions_list_page'],
            'dashicons-awards',
            30
        );

        // Subpage: Add New Action
        add_submenu_page(
            'mm-gamification',
            'Add New Action',
            'Add New Action',
            'manage_options',
            'mm-gamification-add',
            [$this, 'add_action_page']
        );
    }

    public function actions_list_page()
    {
        global $wpdb;
        $table = $wpdb->prefix . 'gamification_actions';
        $actions = $wpdb->get_results("SELECT * FROM $table ORDER BY id DESC");
?>
        <div class="wrap">
            <h1 class="wp-heading-inline">Gamification Actions</h1>
            <a href="<?php echo admin_url('admin.php?page=mm-gamification-add'); ?>" class="page-title-action">Add New</a>
            <hr class="wp-header-end">

            <table class="fixed widefat striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Action Key</th>
                        <th>Message</th>
                        <th>Points</th>
                        <th>Created</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($actions) : ?>
                        <?php foreach ($actions as $a) : ?>
                            <tr>
                                <td><?php echo $a->id; ?></td>
                                <td><?php echo esc_html($a->action_key); ?></td>
                                <td><?php echo esc_html($a->custom_message); ?></td>
                                <td><?php echo $a->points; ?></td>
                                <td><?php echo $a->created_at; ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <tr>
                            <td colspan="5">No actions found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    <?php
    }

    public function add_action_page()
    {
        global $wpdb;
        $table = $wpdb->prefix . 'gamification_actions';

        // Predefined actions
        $available_actions = mm_get_available_actions();

        // Handle form submit
        if (isset($_POST['mm_add_action'])) {
            check_admin_referer('mm_add_action_nonce');

            // Determine selected action
            if (!empty($_POST['action_key_custom'])) {
                $action_key = sanitize_text_field($_POST['action_key_custom']);
            } elseif (!empty($_POST['action_key_dropdown'])) {
                $action_key = sanitize_text_field($_POST['action_key_dropdown']);
            } else {
                $action_key = '';
            }

            // Validate
            if (empty($action_key)) {
                echo '<div class="notice notice-error is-dismissible"><p>Please select or enter an action.</p></div>';
            } else {
                $custom_message = sanitize_textarea_field($_POST['custom_message']);
                $points         = intval($_POST['points']);

                $wpdb->insert($table, [
                    'action_key'     => $action_key,
                    'custom_message' => $custom_message,
                    'points'         => $points
                ]);

                echo '<div class="notice notice-success is-dismissible"><p>Action Added Successfully!</p></div>';
            }
        }
    ?>
        <div class="wrap">
            <h1>Add New Action</h1>
            <form method="post">
                <?php wp_nonce_field('mm_add_action_nonce'); ?>
                <table class="form-table">
                    <tr>
                        <th><label for="action_key_dropdown">Select Predefined Action</label></th>
                        <td>
                            <select name="action_key_dropdown" id="action_key_dropdown">
                                <option value="">-- Select an Action --</option>
                                <?php foreach ($available_actions as $key => $label) : ?>
                                    <option value="<?php echo esc_attr($key); ?>"><?php echo esc_html($label); ?></option>
                                <?php endforeach; ?>
                            </select>
                            <br>
                            <small>Or enter a custom action below:</small>
                            <br>
                            <input type="text" name="action_key_custom" placeholder="Custom action key" class="regular-text">
                        </td>
                    </tr>
                    <tr>
                        <th><label for="custom_message">Custom Message</label></th>
                        <td><textarea name="custom_message" rows="4" class="large-text" required></textarea></td>
                    </tr>
                    <tr>
                        <th><label for="points">Points</label></th>
                        <td><input type="number" name="points" required class="small-text"></td>
                    </tr>
                </table>
                <?php submit_button('Save Action', 'primary', 'mm_add_action'); ?>
            </form>
        </div>
<?php
    }
}
