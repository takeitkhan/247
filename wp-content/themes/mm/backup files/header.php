<?php if (is_user_logged_in()) : ?>
    <?php
    $custom_header = get_template_directory() . '/template-custom/header.php';
    if (file_exists($custom_header)) {
        include $custom_header;
    } else {
        echo "<p>Missing: $custom_header</p>";
    }
    ?>
<?php else : ?>
    <?php
    $main_header = get_template_directory() . '/template-main/header.php';
    if (file_exists($main_header)) {
        include $main_header;
    } else {
        echo "<p>Missing: $main_header</p>";
    }
    ?>
<?php endif; ?>