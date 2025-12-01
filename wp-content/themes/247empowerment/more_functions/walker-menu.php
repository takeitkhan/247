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
        'portalmegamenu' => __('Portal Mega Menu', 'mm'),
        'megarightmenu' => __('Mega Right Menu', 'mm'),
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
    private function relative_url($url) {
        $path = wp_make_link_relative($url);
        return untrailingslashit($path) ?: '/';
    }

    function start_lvl(&$output, $depth = 0, $args = null)
    {
        $indent = str_repeat("\t", $depth);
        $submenu_class = ($depth > 0) ? ' dropdown-submenu' : '';
        $output .= "\n$indent<ul class=\"dropdown-menu$submenu_class\">\n";
    }

    function start_el(&$output, $item, $depth = 0, $args = null, $id = 0)
    {
        $classes = empty($item->classes) ? [] : (array) $item->classes;
        $classes = array_filter($classes);

        $has_children = in_array('menu-item-has-children', $classes);

        // Current URL
        $current_url = home_url(add_query_arg([], $GLOBALS['wp']->request));
        $menu_path = $this->relative_url($item->url);
        $current_path = $this->relative_url($current_url);

        // Check if this menu item is active
        $is_active = ($menu_path === $current_path) || in_array('current-menu-item', $classes);

        // --- Check if any child is active for parent highlight ---
        if ($has_children && isset($args->walker) && $args->walker instanceof Walker_Nav_Menu) {
            $child_active = false;
            foreach ($item->classes as $c) {
                if (strpos($c, 'current-menu-ancestor') !== false) {
                    $child_active = true;
                    break;
                }
            }
            if ($child_active) {
                $is_active = true;
            }
        }

        // --- Classes for <li> ---
        $li_classes = ['nav-item'];
        if ($has_children && $depth === 0) $li_classes[] = 'dropdown';
        if ($depth > 0 && $has_children) $li_classes[] = 'dropdown-submenu';
        if ($is_active) $li_classes[] = 'active-menu';

        // --- Classes for <a> ---
        $link_classes = ['nav-link'];
        if ($depth === 0 && $has_children) $link_classes[] = 'dropdown-toggle';
        if ($depth > 0) $link_classes = ['dropdown-item'];

        $attrs = '';
        if ($has_children && $depth === 0) $attrs .= ' data-bs-toggle="dropdown" aria-expanded="false"';

        // Output
        $output .= '<li class="' . esc_attr(implode(' ', $li_classes)) . '">';
        $output .= '<a href="' . esc_url($item->url) . '" class="' . esc_attr(implode(' ', $link_classes)) . '"' . $attrs . '>';
        $output .= esc_html($item->title);
        $output .= '</a>';
    }

    function end_el(&$output, $item, $depth = 0, $args = null)
    {
        $output .= "</li>\n";
    }

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

function mm_relative_url($url) {
    $path = parse_url($url, PHP_URL_PATH) ?? '';
    $path = trim($path, '/');

    return '/' . $path;
}

class Image_Icon_Walker_Nav_Menu extends Walker_Nav_Menu
{
    function start_el(&$output, $item, $depth = 0, $args = [], $id = 0)
    {
        $icon_url   = get_field('menu_icon_image', $item);
        $icon_class = get_field('menu_icon_class', $item) ?: 'img24';

        $current_user = wp_get_current_user();
        $username = $current_user->user_nicename ?: 'guest';
        $url = str_replace('$username', $username, $item->url);

        // Create comparable paths
        $current_url = home_url(add_query_arg([], $GLOBALS['wp']->request));

        $menu_path    = mm_relative_url($url);
        $current_path = mm_relative_url($current_url);

        // Check active
        $active_class = ($menu_path === $current_path) ? 'active-menu' : '';

        // Output
        $output .= '<li class="nav-item ' . esc_attr($active_class) . '">';
        $output .= '<a href="' . esc_url($url) . '" class="d-flex align-items-baseline gap-2 nav-link">';

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
        // 🔥 Detect active item
        $active_class = '';
        if (
            in_array('current-menu-item', $item->classes) ||
            in_array('current_page_item', $item->classes) ||
            in_array('current-menu-ancestor', $item->classes) ||
            in_array('current_page_ancestor', $item->classes) ||
            in_array('current-menu-parent', $item->classes)
        ) {
            $active_class = ' active-menu';
        }

        // ACF icon fields
        $icon_url   = get_field('menu_icon_image', $item);
        $icon_class = get_field('menu_icon_class', $item) ?: 'img24';

        if (!$icon_url) {
            $icon_url = get_template_directory_uri() . '/images/default-icon.png';
        }

        // Output LI with active class
        $output .= '<li class="d-flex align-items-center nav-item gap10' . $active_class . '">';

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
        // Check if this menu item is active
        $active_class = '';
        if (in_array('current-menu-item', $item->classes) ||
            in_array('current_page_item', $item->classes) ||
            in_array('current-menu-ancestor', $item->classes) ||
            in_array('current_page_ancestor', $item->classes)) {
            $active_class = ' active-menu';
        }

        $icon = get_field('menu_icon_image', $item); // ACF icon image

        $output .= '<li class="d-flex align-items-center nav-item gap10' . $active_class . '">';

        if ($icon) {
            $output .= '<img src="' . esc_url($icon) . '" alt="' . esc_attr($item->title) . '" class="icon-img">';
        }

        $output .= '<a href="' . esc_url($item->url) . '" class="p-0 text">' . esc_html($item->title) . '</a>';
        $output .= '</li>';
    }
}


class Portal_Mega_FB_Style_Walker extends Walker_Nav_Menu {

    function start_lvl(&$output, $depth = 0, $args = null) {
        $output .= '<ul class="ps-0 list-unstyled mega-subitems">';
    }

    function end_lvl(&$output, $depth = 0, $args = null) {
        $output .= '</ul>';
    }

    function start_el(&$output, $item, $depth = 0, $args = null, $id = 0)
    {
        // Unified icon system
        // $icon_url   = get_field('menu_icon_image', $item);
        // $icon_class = get_field('menu_icon_class', $item) ?: 'img24';        

        // Text fields
        $title = esc_html($item->title);
        $desc  = esc_html($item->description);
        $url   = esc_url($item->url);

        // ---- 0: Section Title ----
        if ($depth === 0) {
            $output .= "<h6 class='mb-2 mega-section-title'>{$title}</h6>";
            return;
        }

        // ---- 1+: Sub Item with icon ----
        $output .= "<li class='d-flex align-items-start gap-3 mega-item'>";
        
        // <div class='mega-icon'></div>";
        // {$icon_class}
        // Icon only if exists
        // if ($icon_url) {
        //     $output .= "<img src='" . esc_url($icon_url) . "' class='w-100 h-100 object-fit-contain' alt=''>";
        // }

        $output .= "
            <a href='{$url}' class='d-flex flex-column mega-link'>
                <span class='mega-title'>{$title}</span>
                <small class='text-muted mega-desc'>{$desc}</small>
            </a>
        </li>";
    }

    function end_el(&$output, $item, $depth = 0, $args = null) {
        // no closing tag needed
    }
}

class Mega_Right_Walker extends Walker_Nav_Menu {

    function start_el(&$output, $item, $depth = 0, $args = null, $id = 0) {
        // Get custom fields for the menu item
        $icon_url   = get_field('menu_icon_image', 'menu_item_' . $item->ID);
        $icon_class = get_field('menu_icon_class', 'menu_item_' . $item->ID) ?: '';

        // Replace $username in URL with current user nicename
        $current_user = wp_get_current_user();
        $username = $current_user->user_nicename ?: 'guest';
        $url = str_replace('$username', $username, $item->url);

        // Start menu item
        $output .= '<li>';
        $output .= '<a href="' . esc_url($url) . '">';

        // Output icon or image
        if ($icon_url) {
            $output .= '<div class="' . esc_attr($icon_class ?: 'img24') . '">';
            $output .= '<img class="w-100 h-100 object-fit-contain" src="' . esc_url($icon_url) . '" alt="">';
            $output .= '</div>';
        } elseif ($icon_class) {
            $output .= '<i class="icon ' . esc_attr($icon_class) . '"></i> ';
        }

        // Menu title
        $output .= esc_html($item->title);
        $output .= '</a></li>';
    }

    function start_lvl(&$output, $depth = 0, $args = null) {
        $output .= '<ul class="list-unstyled mega-create-list">';
    }

    function end_lvl(&$output, $depth = 0, $args = null) {
        $output .= '</ul>';
    }
}
