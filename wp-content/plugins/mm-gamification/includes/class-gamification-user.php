<?php
if (! defined('ABSPATH')) exit;

class MM_Gamification_User
{
    public function __construct()
    {
        add_shortcode('user_points', [$this, 'shortcode_user_points']);
        add_shortcode('user_notifications', [$this, 'shortcode_user_notifications']);
    }

    public function shortcode_user_points()
    {
        if (!is_user_logged_in()) return "Please log in to see your points.";

        $points = (int) get_user_meta(get_current_user_id(), 'gamification_points', true);
        return "<div class='user-points'>Your Points: <strong>{$points}</strong></div>";
    }

    public function shortcode_user_notifications()
    {
        if (!is_user_logged_in()) return "Please log in to see your notifications.";

        $user_id = get_current_user_id();
        $notifications = Notifications::getInstance()->getNotifications($user_id, true);

        if (empty($notifications)) return "<div>No new notifications.</div>";

        $html = "<ul class='user-notifications'>";
        foreach ($notifications as $notif) {
            $html .= "<li><span class='{$notif['type']}'>{$notif['message']}</span></li>";
        }
        $html .= "</ul>";

        return $html;
    }
}
