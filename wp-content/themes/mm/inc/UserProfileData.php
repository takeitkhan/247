<?php
class UserProfileData
{
    private static $instance = null;
    private $user;
    private $referred_users = null;

    public function __construct($user = null)
    {
        if ($user instanceof WP_User) {
            $this->user = $user;
        } elseif (is_string($user)) {
            $this->user = get_user_by('slug', $user);
        } elseif (is_user_logged_in()) {
            $this->user = wp_get_current_user();
        }
    }

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    // Get the user profile data
    // This returns an array of user information including ID, username, email, display name,

    public function getProfile()
    {
        if (!$this->user) return null;

        $user_categories = get_user_meta($this->user->ID, 'user_categories', true);
        $user_category_names = [];

        if (!empty($user_categories) && is_array($user_categories)) {
            foreach ($user_categories as $cat_id) {
                $term = get_term($cat_id);
                if ($term && !is_wp_error($term)) {
                    $user_category_names[] = $term->name;
                }
            }
        }

        $social_links = get_user_meta($this->user->ID, 'custom_social_links', true);
        $social_links = is_array($social_links) ? $social_links : [];

        return [
            'id' => $this->user->ID,
            'username' => $this->user->user_login,
            'email' => $this->user->user_email,
            'referrer' => $this->getReferrer(), // ✅ Added here
            'display_name' => $this->user->display_name,
            'first_name' => get_user_meta($this->user->ID, 'first_name', true),
            'last_name' => get_user_meta($this->user->ID, 'last_name', true),
            'dob' => get_user_meta($this->user->ID, 'dob', true),
            'phone' => get_user_meta($this->user->ID, 'phone', true),
            'about_me' => get_user_meta($this->user->ID, 'about_me', true),
            'about_me_short' => get_user_meta($this->user->ID, 'about_me_short', true),
            'location' => get_user_meta($this->user->ID, 'place_address', true),
            'latitude' => get_user_meta($this->user->ID, 'latitude', true),
            'longitude' => get_user_meta($this->user->ID, 'longitude', true),
            'place_display_name' => get_user_meta($this->user->ID, 'place_display_name', true),
            'user_categories' => $user_categories,
            'user_category_names' => $user_category_names,
            'bio' => get_user_meta($this->user->ID, 'description', true),
            'profile_photo' => get_user_meta($this->user->ID, 'profile_photo', true),
            'cover_photo' => get_user_meta($this->user->ID, 'profile_cover_photo', true),
            'roles' => $this->user->roles,
            'profile_url' => $this->getProfileUrl(),
            'social_links' => $social_links, // ✅ NEW: array of platform, label, url
            'referred_users' => $this->getReferredUsers(), // ✅ Added referred users
            'referred_users_count' => count($this->getReferredUsers()), // Count
            'referred_users_html' => $this->render_referred_users($this->getReferredUsers(), $this->user->user_login, 3), // Rendered HTML
        ];
    }

    // Get the profile URL for the user
    // This is the URL to their profile page based on their username
    public function getProfileUrl()
    {
        if (!$this->user) return '';
        return home_url('/' . $this->user->user_login);
    }

    // Get the referrer for the user
    // This is the user who referred them, stored in user meta
    public function getReferrer()
    {
        if (!$this->user) return null;
        return get_user_meta($this->user->ID, 'referrer', true);
    }

    // Get users referred by this user
    public function getReferredUsers()
    {
        if (!$this->user) {
            return [];
        }

        $referrer_id = $this->user->ID;
        $referrer_login = $this->user->user_login;

        $args = [
            'meta_query' => [
                [
                    'key'     => 'referrer',
                    'value'   => [$referrer_id, $referrer_login],
                    'compare' => 'IN'
                ]
            ],
            'orderby' => 'registered',
            'order'   => 'DESC',
            'number' => 7, // Optional limit
        ];

        return get_users($args);
    }


    // public static function getReferredUsersBy($referrer_user)
    // {
    //     if (!$referrer_user instanceof WP_User) {
    //         $referrer_user = get_user_by('id', (int)$referrer_user);
    //     }

    //     if (!$referrer_user) {
    //         return [];
    //     }

    //     $referrer_id = $referrer_user->ID;
    //     $referrer_login = $referrer_user->user_login;

    //     $args = [
    //         'meta_query' => [
    //             [
    //                 'key'     => 'referrer',
    //                 'value'   => [$referrer_id, $referrer_login],
    //                 'compare' => 'IN'
    //             ]
    //         ],
    //         'orderby' => 'registered',
    //         'order'   => 'DESC',
    //         'number' => 7,
    //     ];

    //     return get_users($args);
    // }

    public static function getReferredUsersBy($referrer_user)
    {
        if (!$referrer_user instanceof WP_User) {
            $referrer_user = get_user_by('id', (int)$referrer_user);
        }

        if (!$referrer_user) {
            return [];
        }

        $referrer_id = $referrer_user->ID;
        $referrer_login = $referrer_user->user_login;

        $args = [
            'meta_query' => [
                [
                    'key'     => 'referrer',
                    'value'   => [$referrer_id, $referrer_login],
                    'compare' => 'IN'
                ]
            ],
            'orderby' => 'registered',
            'order'   => 'DESC',
            'number'  => 7,
        ];

        $users = get_users($args);

        $enriched_users = [];

        foreach ($users as $user) {
            $user_id = $user->ID;

            $user_categories = get_user_meta($user_id, 'user_categories', true) ?: [];
            $user_category_names = get_user_meta($user_id, 'user_category_names', true) ?: [];

            $zoom_access_token = get_user_meta($user_id, 'zoom_access_token', true);
            $zoom_refresh_token = get_user_meta($user_id, 'zoom_refresh_token', true);

            $enriched_users[] = [
                'id' => $user_id,
                'username' => $user->user_login,
                'email' => $user->user_email,
                'referrer' => get_user_meta($user_id, 'referrer', true),
                'display_name' => $user->display_name,
                'first_name' => get_user_meta($user_id, 'first_name', true),
                'last_name' => get_user_meta($user_id, 'last_name', true),
                'dob' => get_user_meta($user_id, 'dob', true),
                'phone' => get_user_meta($user_id, 'phone', true),
                'about_me' => get_user_meta($user_id, 'about_me', true),
                'about_me_short' => get_user_meta($user_id, 'about_me_short', true),
                'location' => get_user_meta($user_id, 'place_address', true),
                'latitude' => get_user_meta($user_id, 'latitude', true),
                'longitude' => get_user_meta($user_id, 'longitude', true),
                'place_display_name' => get_user_meta($user_id, 'place_display_name', true),
                'user_categories' => $user_categories,
                'user_category_names' => $user_category_names,
                'bio' => get_user_meta($user_id, 'description', true),
                'profile_photo' => get_user_meta($user_id, 'profile_photo', true),
                'cover_photo' => get_user_meta($user_id, 'profile_cover_photo', true),
                'roles' => $user->roles,
                'social_links' => get_user_meta($user_id, 'social_links', true),
                'zoom_access_token' => $zoom_access_token,
                'zoom_refresh_token' => $zoom_refresh_token,
                'zoom_connected' => !empty($zoom_access_token) ? true : false,
            ];
        }

        return $enriched_users;
    }


    function render_referred_users($referredUsers, $user_login, $maxVisible = 3)
    {
        $referralCount = count($referredUsers);
        $count = 0;
        ob_start();

        echo '<div class="d-flex justify-content-lg-start align-items-start justify-content-center mt-1 overflow-hidden">';

        foreach ($referredUsers as $user) {
            if ($count >= $maxVisible) break;

            $profilePhoto = get_user_meta($user->ID, 'profile_photo', true);
            if (!$profilePhoto) {
                $profilePhoto = 'https://www.gravatar.com/avatar/' . md5(strtolower(trim($user->user_email))) . '?s=64&d=mm';
            }
            $userProfileUrl = site_url('/' . $user->user_login);

            echo '<a href="' . esc_url($userProfileUrl) . '" class="d-inline-block">';
            echo '<img src="' . esc_url($profilePhoto) . '" alt="' . esc_attr($user->display_name) . '" class="profile-img ' . ($count === 0 ? '' : 'profile-img-stacked') . '" />';
            echo '</a>';

            $count++;
        }

        if ($referralCount > $maxVisible) {
            $lastUser = $referredUsers[$maxVisible];
            $lastPhoto = get_user_meta($lastUser->ID, 'profile_photo', true);
            if (!$lastPhoto) {
                $lastPhoto = 'https://www.gravatar.com/avatar/' . md5(strtolower(trim($lastUser->user_email))) . '?s=64&d=mm';
            }
            $referralsLink = site_url('/' . $user_login . '/referrals');

            echo '<div><div class="position-relative">';
            echo '<a href="' . esc_url($referralsLink) . '" class="d-inline-block position-relative" title="See all referral partners">';
            echo '<img src="' . esc_url($lastPhoto) . '" alt="See all referral partners" class="profile-img profile-img-stacked" />';
            echo '<span class="top-0 bottom-0 position-absolute d-flex align-items-center end-0 start-0">';
            echo '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="20" fill="var(--white-color)" viewBox="0 0 16 16">';
            echo '<path d="M3 9.5A1.5 1.5 0 1 0 3 6.5a1.5 1.5 0 0 0 0 3zm5 0a1.5 1.5 0 1 0 0-3 1.5 1.5 0 0 0 0 3zm5 0a1.5 1.5 0 1 0 0-3 1.5 1.5 0 0 0 0 3z"/>';
            echo '</svg></span></a></div></div>';
        }

        echo '</div>';

        return ob_get_clean();
    }

    public function toJSON()
    {
        return wp_json_encode($this->getProfile());
    }
}
