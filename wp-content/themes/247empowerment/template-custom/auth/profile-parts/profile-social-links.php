<?php
$profile = isset($args['profile']) ? $args['profile'] : [];
$social_links = $profile['social_links'] ?? [];
?>
<div class="profile-left bg-white custom-card">
    <div class="d-flex align-items-center justify-content-between pb-4 u-title">
        <h5 class="portal-title">Social Links</h5>        
    </div>
    <?php if (!empty($social_links)): ?>
        <ul class="d-flex flex-column gap-2 nav">

            <?php
            $iconMap = [
                'facebook' => 'bi-facebook',
                'youtube' => 'bi-youtube',
                'linkedin' => 'bi-linkedin',
                'x' => 'bi-twitter',
                'instagram' => 'bi-instagram',
                'google_business' => 'bi-geo-alt-fill',
                'yelp' => 'bi-star-fill',
                'meetup' => 'bi-people-fill',
                'website' => 'bi-globe',
                'tiktok' => 'bi-tiktok',
                'twitch' => 'bi-twitch',
                'pinterest' => 'bi-pinterest',
                'snapchat' => 'bi-snapchat',
                'whatsapp' => 'bi-whatsapp',
                'whatsapp_business' => 'bi-whatsapp',
                'zoom' => 'bi-camera-video-fill',
                'discord' => 'bi-discord',
                'github' => 'bi-github',
                'google' => 'bi-google',
                'custom' => 'bi-link-45deg',
                'other' => 'bi-question-circle-fill',
                'email' => 'bi-envelope-fill',
                'phone' => 'bi-telephone-fill',
                'telegram' => 'bi-telegram',
                'signal' => 'bi-shield-lock-fill',
                'viber' => 'bi-phone-vibrate-fill',
                'sheet' => 'bi-table',
                'slack' => 'bi-slack',
                'reddit' => 'bi-reddit',
                'messenger' => 'bi-messenger',
                'meet' => 'bi-camera-video',
                'calendar' => 'bi-calendar-event',
                'default' => 'bi-link-45deg',
            ];
            ?>
            <?php foreach ($social_links as $link): ?>
                <?php $iconClass = $iconMap[$link['platform']] ?? 'bi-link-45deg'; ?>
                <li class="d-flex align-items-center gap-2">
                    <a href="<?php echo esc_url($link['url']); ?>" target="_blank" class="d-flex align-items-center gap-2 text-decoration-none">
                        <img src="http://pet.test/wp-content/themes/247empowerment/assets/img/nd/location_p.png" class="icon-img" alt="Location">                        
                        <span class="text-dark"><?php echo esc_html($link['label']); ?></span>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <p>No social links added yet.</p>
    <?php endif; ?>
</div>