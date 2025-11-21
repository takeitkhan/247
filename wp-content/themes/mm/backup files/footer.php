<?php wp_footer(); ?>
<?php if (is_user_logged_in()) : ?>
    <?php include 'template-custom/footer.php'; ?>
<?php else : ?>
    <?php include 'template-main/footer.php'; ?>
<?php endif; ?>

</body>

</html>