<footer class="bg-white mt-3 py-4">
    <div class="main-container">
        <div>
            <div class="d-flex align-items-center justify-content-center mt-3">
                <p class="text-center"><?php echo get_bloginfo('name'); ?></p>
            </div>
        </div>
</footer>

<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"
    integrity="sha384-j1CDi7MgGQ12Z7Qab0qlWQ/Qqz24Gc6BM0thvEMVjHnfYGF0rmFCozFSxQBxwHKO"
    crossorigin="anonymous"></script>
<?php
$user_id = get_current_user_id();
$notifications_instance = Notifications::getInstance();
$all_notifications = $notifications_instance->getNotifications($user_id);
$unread_notifications = $notifications_instance->getNotifications($user_id, true);
$unread_count = count($unread_notifications);

// Show latest 6
$notifications = array_slice($all_notifications, 0, 6);
?>

<!-- Floating badge -->
<button id="notificationBadge"
    class="position-fixed m-3 rounded-circle btn btn-primary"
    style="width:50px;height:50px; z-index:1050; bottom:20px; left:20px;">
    🔔
    <?php if ($unread_count > 0) : ?>
        <span class="top-0 position-absolute bg-danger rounded-pill translate-middle start-100 badge">
            <?php echo $unread_count; ?>
        </span>
    <?php endif; ?>
</button>

<!-- Notification Box -->
<div id="toastContainer"
    class="shadow card"
    style="width:350px; max-height:400px; display:none; z-index:1060; position:absolute;">
    <!-- Header -->
    <div class="d-flex align-items-center justify-content-between card-header">
        <strong>Notifications</strong>
        <?php if (!empty($notifications)) : ?>
            <div>
                <a href="#" class="me-2 text-primary small mark-all-read">Mark All</a>
                <a href="#" class="clear-notifications text-danger small">Clear</a>
            </div>
        <?php endif; ?>
    </div>

    <!-- Body -->
    <div class="p-2 card-body" style="overflow-y:auto; max-height:300px;">
        <?php if (!empty($notifications)) : ?>
            <?php foreach ($notifications as $notification) :
                $message    = esc_html($notification['message']);
                $type       = esc_attr($notification['type']);
                $created_at = $notification['created_at'];
                $time_ago   = human_time_diff(strtotime($created_at), current_time('timestamp')) . ' ago';
                $read       = !empty($notification['read_at']);

                $icon_url = get_template_directory_uri() . '/assets/img/icons/info.png';
                if ($type === 'success') $icon_url = get_template_directory_uri() . '/assets/img/icons/success.png';
                elseif ($type === 'error') $icon_url = get_template_directory_uri() . '/assets/img/icons/error.png';
            ?>
                <div class="d-flex align-items-start py-2 border-bottom <?php echo $read ? 'read' : 'unread'; ?>" data-id="<?php echo esc_attr($notification['id']); ?>">
                    <div class="flex-grow-1">
                        <div class="text-muted small"><?php echo $time_ago; ?></div>
                        <div><?php echo $message; ?></div>
                        <?php if (!$read) : ?>
                            <a href="#" class="text-decoration-underline mark-read small" data-id="<?php echo esc_attr($notification['id']); ?>">Mark Read</a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else : ?>
            <div class="p-3 text-muted text-center">No notifications found.</div>
        <?php endif; ?>
    </div>
</div>
<?php wp_footer(); ?>
</body>

</html>