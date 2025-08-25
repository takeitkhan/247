<?php

/**
 * Template Name: Login Router
 */
if (is_user_logged_in()) {
    include get_template_directory() . '/template-custom/auth/home.php';
} else {
    include get_template_directory() . '/page-welcome.php';
}
