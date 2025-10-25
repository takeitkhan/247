<?php

function register_my_menus()
{
    register_nav_menus([
        'primary' => __('Primary Menu', 'mm'),
        'portalmenu' => __('Portal Menu', 'mm'),
        'portalmobilemenu' => __('Portal Mobile Menu', 'mm'),
        'secondary' => __('Footer Menu', 'mm'),
        'authentication' => __('Authentication Menu', 'mm'),
        'profilemenu' => __('Profile Menu', 'mm'),
        'editprofilemenu' => __('Edit Profile Menu', 'mm'),
    ]);
}
add_action('after_setup_theme', 'register_my_menus');


acf_add_local_field_group([
    'key' => 'menu_icons_group',
    'title' => 'Menu Icon',
    'fields' => [
        [
            'key' => 'field_menu_icon_image',
            'label' => 'Icon Image',
            'name' => 'menu_icon_image',
            'type' => 'image',
            'return_format' => 'url',
        ],
        [
            'key' => 'field_menu_icon_class',
            'label' => 'Icon Size Class (optional)',
            'name' => 'menu_icon_class',
            'type' => 'text',
            'placeholder' => 'e.g., img24 or img20',
        ],
    ],
    'location' => [
        [
            [
                'param' => 'nav_menu_item',
                'operator' => '==',
                'value' => 'all',
            ],
        ],
    ],
]);


class MM_Walker_Nav_Menu extends Walker_Nav_Menu
{

    // Start level (sub-menu)
    function start_lvl(&$output, $depth = 0, $args = null)
    {
        $indent = str_repeat("\t", $depth);
        $submenu_class = ($depth > 0) ? ' dropdown-submenu' : '';
        $output .= "\n$indent<ul class=\"dropdown-menu$submenu_class\">\n";
    }

    // Start element
    function start_el(&$output, $item, $depth = 0, $args = null, $id = 0)
    {
        $classes = empty($item->classes) ? [] : (array) $item->classes;
        $classes = array_filter($classes); // remove empty

        $has_children = in_array('menu-item-has-children', $classes);

        // Classes for <li>
        $li_classes = ['nav-item'];
        if ($has_children && $depth === 0) {
            $li_classes[] = 'dropdown';
        } elseif ($depth > 0 && $has_children) {
            $li_classes[] = 'dropdown-submenu';
        }

        // Classes for <a>
        $link_classes = ['nav-link'];
        if ($depth === 0 && $has_children) {
            $link_classes[] = 'dropdown-toggle';
        } elseif ($depth > 0) {
            $link_classes = ['dropdown-item'];
        }

        // Attributes for <a>
        $attrs = '';
        if ($has_children && $depth === 0) {
            $attrs .= ' data-bs-toggle="dropdown" aria-expanded="false"';
        }

        $output .= '<li class="' . esc_attr(implode(' ', $li_classes)) . '">';
        $output .= '<a href="' . esc_url($item->url) . '" class="' . esc_attr(implode(' ', $link_classes)) . '"' . $attrs . '>';
        $output .= esc_html($item->title);
        $output .= '</a>';
    }

    // End element
    function end_el(&$output, $item, $depth = 0, $args = null)
    {
        $output .= "</li>\n";
    }

    // End level
    function end_lvl(&$output, $depth = 0, $args = null)
    {
        $output .= "</ul>\n";
    }
}



class MM_Footer_Walker_Nav_Menu extends Walker_Nav_Menu
{
    function start_lvl(&$output, $depth = 0, $args = null)
    {
        // No submenus for footer — skip
    }

    function end_lvl(&$output, $depth = 0, $args = null)
    {
        // No submenus for footer — skip
    }

    function start_el(&$output, $item, $depth = 0, $args = null, $id = 0)
    {
        $output .= '<li class="footer-list-inline-item list-inline-item">';

        $atts = [
            'href'  => !empty($item->url) ? esc_url($item->url) : '#',
            'class' => 'text-white text-decoration-none',
        ];

        $attributes = '';
        foreach ($atts as $attr => $value) {
            $attributes .= ' ' . $attr . '="' . esc_attr($value) . '"';
        }

        $title = apply_filters('the_title', $item->title, $item->ID);
        $output .= '<a' . $attributes . '>' . esc_html($title) . '</a>';
    }

    function end_el(&$output, $item, $depth = 0, $args = null)
    {
        $output .= "</li>\n";
    }
}

class MM_Auth_Walker_Nav_Menu extends Walker_Nav_Menu
{
    function start_lvl(&$output, $depth = 0, $args = null) {}
    function end_lvl(&$output, $depth = 0, $args = null) {}

    function start_el(&$output, $item, $depth = 0, $args = null, $id = 0)
    {
        // Detect "Sign In" or "Sign Up"
        $title_lower = strtolower($item->title);
        $is_sign_in = strpos($title_lower, 'sign in') !== false;
        $is_sign_up = strpos($title_lower, 'sign up') !== false;

        // Assign classes
        $classes = '';
        if ($is_sign_in) {
            $classes = 'btn btn-outline-primary btn-sm px-4';
        } elseif ($is_sign_up) {
            $classes = 'me-2 text-decoration-none';
        } else {
            $classes = 'text-decoration-none';
        }

        $output .= '<li class="list-inline-item">';
        $output .= '<a href="' . esc_url($item->url) . '" class="' . esc_attr($classes) . '">'
            . esc_html($item->title) . '</a>';
    }

    function end_el(&$output, $item, $depth = 0, $args = null)
    {
        $output .= '</li>';
    }
}

class Image_Icon_Walker_Nav_Menu extends Walker_Nav_Menu
{
    function start_el(&$output, $item, $depth = 0, $args = [], $id = 0)
    {
        // Get ACF fields
        $icon_url   = get_field('menu_icon_image', $item);
        $icon_class = get_field('menu_icon_class', $item) ?: 'img24';

        // Replace $username placeholder with logged-in username
        $current_user = wp_get_current_user();
        $username = $current_user->user_nicename ?: 'guest'; // fallback if not logged in
        $url = str_replace('$username', $username, $item->url);

        // Active class
        $current_url = home_url(add_query_arg([], $GLOBALS['wp']->request));
        $active_class = (untrailingslashit($url) === untrailingslashit($current_url)) ? 'active-menu' : '';

        $output .= '<li class="nav-item">';
        $output .= '<a href="' . esc_url($url) . '" class="d-flex align-items-baseline gap-2 nav-link ' . esc_attr($active_class) . '">';

        if ($icon_url) {
            $output .= '<div class="' . esc_attr($icon_class) . '">';
            $output .= '<img class="w-100 h-100 object-fit-contain" src="' . esc_url($icon_url) . '" alt="">';
            $output .= '</div>';
        }

        $output .= esc_html($item->title);
        $output .= '</a></li>';
    }
}

class Profile_Menu_Walker extends Walker_Nav_Menu
{

    function start_lvl(&$output, $depth = 0, $args = null)
    {
        $output .= "\n<ul class=\"nav d-flex flex-column gap-2\">\n";
    }

    function end_lvl(&$output, $depth = 0, $args = null)
    {
        $output .= "</ul>\n";
    }

    function start_el(&$output, $item, $depth = 0, $args = null, $id = 0)
    {
        // Correct ACF call
        $icon_url   = get_field('menu_icon_image', $item);
        $icon_class = get_field('menu_icon_class', $item) ?: 'img24';

        // Optional fallback
        if (!$icon_url) {
            $icon_url = get_template_directory_uri() . '/images/default-icon.png';
        }

        $output .= '<li class="d-flex align-items-center nav-item gap10">';
        $output .= '<img src="' . esc_url($icon_url) . '" alt="' . esc_attr($item->title) . '" class="icon-img ' . esc_attr($icon_class) . '">';
        $output .= '<a href="' . esc_url($item->url) . '" class="p-0 text">' . esc_html($item->title) . '</a>';
    }

    function end_el(&$output, $item, $depth = 0, $args = null)
    {
        $output .= "</li>\n";
    }
}

class Edit_Profile_Walker extends Walker_Nav_Menu
{
    function start_el(&$output, $item, $depth = 0, $args = array(), $id = 0)
    {
        $icon = get_field('menu_icon_image', $item); // ACF icon field (optional)
        $output .= '<li class="d-flex align-items-center nav-item gap10">';
        if ($icon) {
            $output .= '<img src="' . esc_url($icon) . '" alt="' . esc_attr($item->title) . '" class="icon-img">';
        }
        $output .= '<a href="' . esc_url($item->url) . '" class="p-0 text">' . esc_html($item->title) . '</a>';
        $output .= '</li>';
    }
}
