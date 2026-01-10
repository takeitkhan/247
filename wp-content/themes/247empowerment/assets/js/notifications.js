window.mmUnreadCount = parseInt(jQuery('#notif-unread-count').text() || 0, 10);
window.mmPendingLoginSound = false; // ✅ REQUIRED

jQuery(document).ready(function ($) {
    const $badge = $('#notificationDropdown > button');
    const $container = $('#notificationList');

    /* =========================================================
     * UNIFIED GAMIFICATION ACTION DISPATCHER
     * ======================================================= */

    function triggerGamificationAction($el) {
        const actionKey = $el.data('action-key');

        if (!actionKey) return;

        // Prevent double firing
        if ($el.data('mm-fired')) return;
        $el.data('mm-fired', true);

        $.post(notificationsData.ajaxurl, {
            action: 'mm_gamification_action',
            action_key: actionKey,
            security: notificationsData.nonce
        }, function (response) {

            if (response.success && response.data && response.data.notification) {
                mmPushNotification(response.data.notification);
            }

        }).always(function () {
            // Allow re-fire only if needed later
            setTimeout(() => {
                $el.removeData('mm-fired');
            }, 1000);
        });
    }

    // Click-based actions (buttons, links)
    $(document).on('click', '.mm-action', function (e) {
        e.preventDefault();
        triggerGamificationAction($(this));
    });

    // Change-based actions (inputs, selects)
    $(document).on('change', '.mm-action-input', function () {
        triggerGamificationAction($(this));
    });

    /* =========================================================
     * NOTIFICATION DROPDOWN UI
     * ======================================================= */

    function positionContainer() {
        if (!$badge.length || !$container.length) return;

        const offset = $badge.offset();
        $container.css({
            top: offset.top - $container.outerHeight() - 10,
            left: offset.left
        });
    }


    $(window).on('resize', function () {
        if ($container.is(':visible')) positionContainer();
    });

    /* =========================================================
     * MARK SINGLE NOTIFICATION AS READ
     * ======================================================= */

    $(document).on('click', '.mark-read', function (e) {
        e.preventDefault();

        const notifId = $(this).data('id');
        const $item = $(this).closest('[data-id]');

        $.post(notificationsData.ajaxurl, {
            action: 'mark_notification_read',
            notification_id: notifId,
            security: notificationsData.nonce
        }, function (response) {
            if (response.success) {
                $item.remove();
            }
        });
    });

    /* =========================================================
     * MARK ALL AS READ
     * ======================================================= */

    $(document).on('click', '.mark-all-read', function (e) {
        e.preventDefault();

        const $btn = $(this);
        $btn.text('Marking...');

        $.post(notificationsData.ajaxurl, {
            action: 'mark_all_notifications_read',
            security: notificationsData.nonce
        }, function (response) {

            if (response.success) {

                // reset counter
                window.mmUnreadCount = 0;

                // remove badge
                $('#notif-unread-count').remove();

                // remove unread class
                $('#notificationList .unread').removeClass('unread');

                // remove button
                $('.mark-all-read').remove();
            }

            $btn.text('All read');
        }).fail(function () {
            $btn.text('Mark all as read');
        });
    });


    /* =========================================================
     * CLEAR ALL NOTIFICATIONS
     * ======================================================= */

    $(document).on('click', '.clear-notifications', function (e) {
        e.preventDefault();

        $.post(notificationsData.ajaxurl, {
            action: 'clear_all_notifications',
            security: notificationsData.nonce
        }, function (response) {
            if (response.success) {
                $container.html(
                    '<div class="p-3 text-muted text-center">No notifications found.</div>'
                );
                $('.notif-bubble').remove();
            }
        });
    });

});

/* =========================================================
 * NOTIFICATION SOUND SETUP
 * ======================================================= */

let mmNotificationAudio = null;
let mmAudioUnlocked = false;

// Unlock audio after first user interaction (required by browsers)
jQuery(document).one('click keydown touchstart', function () {

    if (!mmNotificationAudio) {
        mmNotificationAudio = new Audio(notificationsData.sound);
        mmNotificationAudio.volume = 0.5;
    }

    // 🔥 Play delayed login sound
    if (window.mmPendingLoginSound) {
        mmNotificationAudio.currentTime = 0;
        mmNotificationAudio.play().catch(() => {});
        window.mmPendingLoginSound = false;
    }

    mmAudioUnlocked = true;
});

/* =========================================================
 * GLOBAL PUSH NOTIFICATION HANDLER
 * ======================================================= */

window.mmPushNotification = function (notification) {
    if (!notification || !notification.message) return;

     // Mark pending login sound
    if (notification.play_sound_on_load) {
        window.mmPendingLoginSound = true;
    }

    const $list = jQuery('#notificationList');
    const $count = jQuery('#notif-unread-count');

    // 🔊 Play notification sound
    if (mmAudioUnlocked && mmNotificationAudio) {
        mmNotificationAudio.currentTime = 0;
        mmNotificationAudio.play().catch(() => { });
    }

    // ✅ Increment unread counter
    window.mmUnreadCount++;

    if ($count.length) {
        $count.text(window.mmUnreadCount);
    } else {
        jQuery('.notification-png').after(
            `<span class="notif-bubble" id="notif-unread-count">${window.mmUnreadCount}</span>`
        );
    }

    // Build notification HTML
    const html = `
        <div class="d-flex align-items-center border-bottom border-2 border-light gap-3 py-2 unread">
            <div class="d-flex align-items-center gap10">
                <img src="${notificationsData.circleIcon}">
                <div class="position-relative img44">
                    <img src="${notificationsData.userImg}" class="rounded-circle w-100 h-100 object-fit-cover">
                    <img class="position-absolute active-icon" src="${notificationsData.activeIcon}">
                </div>
            </div>
            <div class="d-flex flex-column post-user">
                <span class="p_name fs16">${notification.message}</span>
                <span class="mb-0 text-blue-color fs14">just now</span>
            </div>
        </div>
    `;

    $list.find('.text-muted').remove();
    $list.prepend(html);
};


jQuery(document).ready(function () {

    const $badge = jQuery('#notif-unread-count');

    // If unread exists on load, assume login notification
    if ($badge.length && window.mmUnreadCount > 0) {
        window.mmPendingLoginSound = true;
    }
});
