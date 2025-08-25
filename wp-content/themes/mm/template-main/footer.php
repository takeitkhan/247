</div>
<footer class="mt-auto py-4">
    <div class="container">
        <div class="d-flex justify-content-center gap-3 mb-3 fs-4">
            <?php if ($facebook_url = get_theme_mod('facebook_url')): ?>
                <a href="<?php echo esc_url($facebook_url); ?>" aria-label="Facebook"
                    class="hover:text-yellow-400 transition-colors">
                    <?php if ($facebook_icon_id = get_theme_mod('facebook_icon')): ?>
                        <?php $facebook_icon_url = wp_get_attachment_url($facebook_icon_id); ?>
                        <svg xmlns="http://www.w3.org/2000/svg" x="0px" y="0px" width="36" height="36" viewBox="0,0,256,256">
                            <g fill="#09090A" fill-rule="nonzero" stroke="none" stroke-width="1" stroke-linecap="butt"
                                stroke-linejoin="miter" stroke-miterlimit="10" stroke-dasharray="" stroke-dashoffset="0"
                                font-family="none" font-weight="none" font-size="none" text-anchor="none"
                                style="mix-blend-mode: normal">
                                <g transform="scale(10.66667,10.66667)">
                                    <path
                                        d="M5,3c-1.103,0 -2,0.897 -2,2v14c0,1.103 0.897,2 2,2h6.62109h2.79297h4.58594c1.103,0 2,-0.897 2,-2v-14c0,-1.103 -0.897,-2 -2,-2zM5,5h14l0.00195,14h-4.58789v-3.96484h2.36524l0.35156,-2.72461h-2.70117v-1.73633c0,-0.788 0.21961,-1.32031 1.34961,-1.32031h1.42773v-2.43164c-0.695,-0.071 -1.3928,-0.10552 -2.0918,-0.10352c-2.074,0 -3.49414,1.26584 -3.49414,3.58984v2.00586h-2.34375v2.72461h2.34375v3.96094h-6.62109z">
                                    </path>
                                </g>
                            </g>
                        </svg>
                    <?php endif; ?>
                </a>
            <?php endif; ?>

            <?php if ($twitter_url = get_theme_mod('twitter_url')): ?>
                <a href="<?php echo esc_url($twitter_url); ?>" aria-label="Twitter (X)"
                    class="hover:text-yellow-400 transition-colors">
                    <?php if ($twitter_icon_id = get_theme_mod('twitter_icon')): ?>
                        <?php $twitter_icon_url = wp_get_attachment_url($twitter_icon_id); ?>
                        <svg xmlns="http://www.w3.org/2000/svg" x="0px" y="0px" width="36" height="36" viewBox="0,0,256,256">
                            <g fill="#09090A" fill-rule="nonzero" stroke="none" stroke-width="1" stroke-linecap="butt"
                                stroke-linejoin="miter" stroke-miterlimit="10" stroke-dasharray="" stroke-dashoffset="0"
                                font-family="none" font-weight="none" font-size="none" text-anchor="none"
                                style="mix-blend-mode: normal">
                                <g transform="scale(10.66667,10.66667)">
                                    <path
                                        d="M2.36719,3l7.0957,10.14063l-6.72266,7.85938h2.64063l5.26367,-6.16992l4.31641,6.16992h6.91016l-7.42187,-10.625l6.29102,-7.375h-2.59961l-4.86914,5.6875l-3.97266,-5.6875zM6.20703,5h2.04883l9.77734,14h-2.03125z">
                                    </path>
                                </g>
                            </g>
                        </svg>
                    <?php endif; ?>
                </a>
            <?php endif; ?>

            <?php if ($instagram_url = get_theme_mod('instagram_url')): ?>
                <a href="<?php echo esc_url($instagram_url); ?>" aria-label="Instagram"
                    class="hover:text-yellow-400 transition-colors">
                    <?php if ($instagram_icon_id = get_theme_mod('instagram_icon')): ?>
                        <?php $instagram_icon_url = wp_get_attachment_url($instagram_icon_id); ?>
                        <svg xmlns="http://www.w3.org/2000/svg" x="0px" y="0px" width="36" height="36" viewBox="0,0,256,256">
                            <g fill="#09090A" fill-rule="nonzero" stroke="none" stroke-width="1" stroke-linecap="butt"
                                stroke-linejoin="miter" stroke-miterlimit="10" stroke-dasharray="" stroke-dashoffset="0"
                                font-family="none" font-weight="none" font-size="none" text-anchor="none"
                                style="mix-blend-mode: normal">
                                <g transform="scale(10.66667,10.66667)">
                                    <path
                                        d="M8,3c-2.757,0 -5,2.243 -5,5v8c0,2.757 2.243,5 5,5h8c2.757,0 5,-2.243 5,-5v-8c0,-2.757 -2.243,-5 -5,-5zM8,5h8c1.654,0 3,1.346 3,3v8c0,1.654 -1.346,3 -3,3h-8c-1.654,0 -3,-1.346 -3,-3v-8c0,-1.654 1.346,-3 3,-3zM17,6c-0.55228,0 -1,0.44772 -1,1c0,0.55228 0.44772,1 1,1c0.55228,0 1,-0.44772 1,-1c0,-0.55228 -0.44772,-1 -1,-1zM12,7c-2.757,0 -5,2.243 -5,5c0,2.757 2.243,5 5,5c2.757,0 5,-2.243 5,-5c0,-2.757 -2.243,-5 -5,-5zM12,9c1.654,0 3,1.346 3,3c0,1.654 -1.346,3 -3,3c-1.654,0 -3,-1.346 -3,-3c0,-1.654 1.346,-3 3,-3z">
                                    </path>
                                </g>
                            </g>
                        </svg>
                    <?php endif; ?>
                </a>
            <?php endif; ?>

            <?php if ($meetup_url = get_theme_mod('meetup_url')): ?>
                <a href="<?php echo esc_url($meetup_url); ?>" aria-label="Meetup"
                    class="hover:text-yellow-400 transition-colors">
                    <?php if ($meetup_icon_id = get_theme_mod('meetup_icon')): ?>
                        <?php $meetup_icon_url = wp_get_attachment_url($meetup_icon_id); ?>
                        <svg xmlns="http://www.w3.org/2000/svg" x="0px" y="0px" width="36" height="36" viewBox="0,0,256,256">
                            <g fill="#09090A" fill-rule="nonzero" stroke="none" stroke-width="1" stroke-linecap="butt"
                                stroke-linejoin="miter" stroke-miterlimit="10" stroke-dasharray="" stroke-dashoffset="0"
                                font-family="none" font-weight="none" font-size="none" text-anchor="none"
                                style="mix-blend-mode: normal">
                                <g transform="scale(5.12,5.12)">
                                    <path
                                        d="M15,1c-0.55228,0 -1,0.44772 -1,1c0,0.55228 0.44772,1 1,1c0.55228,0 1,-0.44772 1,-1c0,-0.55228 -0.44772,-1 -1,-1zM28,2c-1.10457,0 -2,0.89543 -2,2c0,1.10457 0.89543,2 2,2c1.10457,0 2,-0.89543 2,-2c0,-1.10457 -0.89543,-2 -2,-2zM22,6c-4.51765,0 -8.26113,3.04928 -9.49414,7.17773c-3.64685,0.50354 -6.50586,3.54109 -6.50586,7.32227c0,1.26164 0.373,2.42065 0.92578,3.46484c-1.14792,1.38526 -1.92578,3.10039 -1.92578,5.03516c0,3.711 2.59145,6.73168 6.03125,7.62695c-0.00722,0.12707 -0.03125,0.24169 -0.03125,0.37305c0,4.40644 3.59356,8 8,8c1.95236,0 3.66209,-0.81086 5.05078,-1.97656c1.17205,1.15208 2.68005,1.97656 4.44922,1.97656c3.05398,0 5.52208,-2.16845 6.20898,-5.01953c0.09973,0.00407 0.18832,0.01953 0.29102,0.01953c4.95852,0 9,-4.04148 9,-9c0,-1.81675 -0.62756,-3.45794 -1.55664,-4.87305c0.90114,-0.97388 1.55664,-2.19204 1.55664,-3.62695c0,-2.55362 -1.80235,-4.60565 -4.16992,-5.21484c0.06511,-0.42617 0.16992,-0.83654 0.16992,-1.28516c0,-4.95852 -4.04148,-9 -9,-9c-1.22408,0 -2.34273,0.3609 -3.4043,0.80469c-1.60239,-1.097 -3.5064,-1.80469 -5.5957,-1.80469zM9.5,8c-0.82843,0 -1.5,0.67157 -1.5,1.5c0,0.82843 0.67157,1.5 1.5,1.5c0.82843,0 1.5,-0.67157 1.5,-1.5c0,-0.82843 -0.67157,-1.5 -1.5,-1.5zM22,8c1.82163,0 3.48845,0.61291 4.83594,1.63867c0.3028,0.23038 0.71009,0.26899 1.05078,0.09961c0.94238,-0.46987 1.9937,-0.73828 3.11328,-0.73828c3.87748,0 7,3.12252 7,7c0,0.60571 -0.08624,1.19493 -0.23633,1.76953c-0.07616,0.29105 -0.01725,0.60093 0.1604,0.84373c0.17765,0.2428 0.45517,0.39273 0.75562,0.40822c1.85779,0.09622 3.32031,1.59704 3.32031,3.47852c0,1.12026 -0.5291,2.0943 -1.35547,2.74219c-0.20934,0.16432 -0.34455,0.4053 -0.37571,0.66959c-0.03116,0.2643 0.04431,0.53011 0.20969,0.73861c0.95231,1.19784 1.52148,2.70117 1.52148,4.34961c0,3.87748 -3.12252,7 -7,7c-0.28936,0 -0.58643,-0.02545 -0.89844,-0.06445c-0.27142,-0.03389 -0.54488,0.04487 -0.7567,0.21794c-0.21182,0.17307 -0.3435,0.42534 -0.36439,0.69808c-0.17871,2.32567 -2.10251,4.14844 -4.48047,4.14844c-1.48057,0 -2.77877,-0.71244 -3.60156,-1.8125c-0.17703,-0.23658 -0.44961,-0.38276 -0.74462,-0.39934c-0.29501,-0.01658 -0.58226,0.09815 -0.78467,0.3134c-1.09743,1.16791 -2.64011,1.89844 -4.36914,1.89844c-3.32556,0 -6,-2.67444 -6,-6c0,-0.29688 0.02947,-0.59967 0.07813,-0.91406c0.04116,-0.26625 -0.02684,-0.53784 -0.18861,-0.75328c-0.16176,-0.21544 -0.40361,-0.3565 -0.67077,-0.39125c-2.94588,-0.38335 -5.21875,-2.88202 -5.21875,-5.94141c0,-1.66534 0.67198,-3.15893 1.76172,-4.24609c0.33577,-0.33469 0.39042,-0.85914 0.13086,-1.25586c-0.56479,-0.865 -0.89258,-1.88767 -0.89258,-2.99805c0,-2.97103 2.33168,-5.36413 5.26953,-5.48633c0.45153,-0.01899 0.83422,-0.33843 0.93359,-0.7793c0.805,-3.5665 3.9764,-6.23437 7.79688,-6.23437zM44,13c-1.10457,0 -2,0.89543 -2,2c0,1.10457 0.89543,2 2,2c1.10457,0 2,-0.89543 2,-2c0,-1.10457 -0.89543,-2 -2,-2zM26.94141,15c-2,0 -2.74963,2 -3.51562,2c-1,0 -2.23533,-1 -3.86133,-1c-2.25,0 -3.36384,1.04714 -3.83984,2.61914c-0.363,1.198 -3.16264,10.21705 -3.55664,11.37305c-0.534,1.568 0.35337,3.29166 1.98438,3.84766c0.336,0.115 0.67672,0.1552 1.01172,0.1582c1.29,0.012 2.4842,-0.74619 2.9082,-1.99219c0.277,-0.812 3.12809,-8.37536 3.87109,-10.31836c0.151,-0.401 0.53914,-0.6875 0.99414,-0.6875c0.587,0 1.0625,0.4755 1.0625,1.0625c0,0.134 -0.02531,0.26186 -0.07031,0.38086c-0.003,0.008 -2.5872,6.65838 -2.7832,7.10938c-0.096,0.222 -0.14253,0.45169 -0.14453,0.67969c-0.007,0.682 0.38773,1.33405 1.05273,1.62305c0.886,0.385 1.91673,-0.02025 2.30273,-0.90625c0.183,-0.42 4.72852,-10.34961 4.72852,-10.34961c0.222,-0.506 0.81141,-0.73567 1.31641,-0.51367c0.371,0.163 0.59461,0.5243 0.59961,0.9043c0.002,0.138 -0.02498,0.27811 -0.08398,0.41211c-0.031,0.07 -3.54952,7.32161 -3.60352,7.47461c-0.241,0.671 -0.31445,1.25305 -0.31445,2.12305c0,0.75 0.375,4 6.5,4c0.828,0 1.5,-0.672 1.5,-1.5c0,-0.828 -0.672,-1.5 -1.5,-1.5c-2,0 -2.5,-1.125 -2.5,-2c0,-0.696 0.294,-1.528 0.5,-2c2.125,-4.875 3.5,-6.875 3.5,-9.125c0,-2 -1.18459,-2.86914 -2.43359,-2.86914c-1.048,0 -1.99884,0.32684 -2.71484,0.96484c-0.04,0.036 -0.09819,0.0207 -0.11719,-0.0293c-0.429,-1.132 -1.51097,-1.94141 -2.79297,-1.94141zM49,20c-0.55228,0 -1,0.44772 -1,1c0,0.55228 0.44772,1 1,1c0.55228,0 1,-0.44772 1,-1c0,-0.55228 -0.44772,-1 -1,-1zM2,21c-1.10457,0 -2,0.89543 -2,2c0,1.10457 0.89543,2 2,2c1.10457,0 2,-0.89543 2,-2c0,-1.10457 -0.89543,-2 -2,-2zM46.5,25c-0.82843,0 -1.5,0.67157 -1.5,1.5c0,0.82843 0.67157,1.5 1.5,1.5c0.82843,0 1.5,-0.67157 1.5,-1.5c0,-0.82843 -0.67157,-1.5 -1.5,-1.5zM8,39c-0.55228,0 -1,0.44772 -1,1c0,0.55228 0.44772,1 1,1c0.55228,0 1,-0.44772 1,-1c0,-0.55228 -0.44772,-1 -1,-1zM38,42c-1.10457,0 -2,0.89543 -2,2c0,1.10457 0.89543,2 2,2c1.10457,0 2,-0.89543 2,-2c0,-1.10457 -0.89543,-2 -2,-2zM23.5,46c-0.82843,0 -1.5,0.67157 -1.5,1.5c0,0.82843 0.67157,1.5 1.5,1.5c0.82843,0 1.5,-0.67157 1.5,-1.5c0,-0.82843 -0.67157,-1.5 -1.5,-1.5z">
                                    </path>
                                </g>
                            </g>
                        </svg>
                    <?php endif; ?>
                </a>
            <?php endif; ?>

            <?php if ($linkedin_url = get_theme_mod('linkedin_url')): ?>
                <a href="<?php echo esc_url($linkedin_url); ?>" aria-label="LinkedIn"
                    class="hover:text-yellow-400 transition-colors">
                    <?php if ($linkedin_icon_id = get_theme_mod('linkedin_icon')): ?>
                        <?php $linkedin_icon_url = wp_get_attachment_url($linkedin_icon_id); ?>
                        <svg xmlns="http://www.w3.org/2000/svg" x="0px" y="0px" width="36" height="36" viewBox="0,0,256,256">
                            <g fill="#09090A" fill-rule="nonzero" stroke="none" stroke-width="1" stroke-linecap="butt"
                                stroke-linejoin="miter" stroke-miterlimit="10" stroke-dasharray="" stroke-dashoffset="0"
                                font-family="none" font-weight="none" font-size="none" text-anchor="none"
                                style="mix-blend-mode: normal">
                                <g transform="scale(10.66667,10.66667)">
                                    <path
                                        d="M5,3c-1.105,0 -2,0.895 -2,2v14c0,1.105 0.895,2 2,2h14c1.105,0 2,-0.895 2,-2v-14c0,-1.105 -0.895,-2 -2,-2zM5,5h14v14h-14zM7.7793,6.31641c-0.857,0 -1.37109,0.51517 -1.37109,1.20117c0,0.686 0.51416,1.19922 1.28516,1.19922c0.857,0 1.37109,-0.51322 1.37109,-1.19922c0,-0.686 -0.51416,-1.20117 -1.28516,-1.20117zM6.47656,10v7h2.52344v-7zM11.08203,10v7h2.52344v-3.82617c0,-1.139 0.81264,-1.30273 1.05664,-1.30273c0.244,0 0.89649,0.24473 0.89649,1.30273v3.82617h2.44141v-3.82617c0,-2.197 -0.97627,-3.17383 -2.19727,-3.17383c-1.221,0 -1.87226,0.40656 -2.19726,0.97656v-0.97656z">
                                    </path>
                                </g>
                            </g>
                        </svg>
                    <?php endif; ?>
                </a>
            <?php endif; ?>

            <?php if ($youtube_url = get_theme_mod('youtube_url')): ?>
                <a href="<?php echo esc_url($youtube_url); ?>" aria-label="YouTube"
                    class="hover:text-yellow-400 transition-colors">
                    <?php if ($youtube_icon_id = get_theme_mod('youtube_icon')): ?>
                        <?php $youtube_icon_url = wp_get_attachment_url($youtube_icon_id); ?>
                        <svg xmlns="http://www.w3.org/2000/svg" x="0px" y="0px" width="36" height="36" viewBox="0,0,256,256">
                            <g fill="#09090A" fill-rule="nonzero" stroke="none" stroke-width="1" stroke-linecap="butt"
                                stroke-linejoin="miter" stroke-miterlimit="10" stroke-dasharray="" stroke-dashoffset="0"
                                font-family="none" font-weight="none" font-size="none" text-anchor="none"
                                style="mix-blend-mode: normal">
                                <g transform="scale(10.66667,10.66667)">
                                    <path
                                        d="M12,4c0,0 -6.25445,-0.00003 -7.81445,0.41797c-0.861,0.23 -1.53758,0.90758 -1.76758,1.76758c-0.418,1.56 -0.41797,5.81445 -0.41797,5.81445c0,0 -0.00003,4.25445 0.41797,5.81445c0.23,0.861 0.90758,1.53758 1.76758,1.76758c1.56,0.418 7.81445,0.41797 7.81445,0.41797c0,0 6.25445,0.00003 7.81445,-0.41797c0.86,-0.23 1.53758,-0.90758 1.76758,-1.76758c0.418,-1.56 0.41797,-5.81445 0.41797,-5.81445c0,0 0.00003,-4.25445 -0.41797,-5.81445c-0.23,-0.86 -0.90758,-1.53758 -1.76758,-1.76758c-1.56,-0.418 -7.81445,-0.41797 -7.81445,-0.41797zM12,6c2.882,0 6.49087,0.13361 7.29688,0.34961c0.169,0.045 0.30752,0.18352 0.35352,0.35352c0.241,0.898 0.34961,3.63888 0.34961,5.29688c0,1.658 -0.10861,4.39787 -0.34961,5.29688c-0.045,0.169 -0.18352,0.30752 -0.35352,0.35352c-0.805,0.216 -4.41488,0.34961 -7.29687,0.34961c-2.881,0 -6.48987,-0.13361 -7.29687,-0.34961c-0.169,-0.045 -0.30752,-0.18352 -0.35352,-0.35352c-0.241,-0.898 -0.34961,-3.63888 -0.34961,-5.29687c0,-1.658 0.10861,-4.39883 0.34961,-5.29883c0.045,-0.168 0.18352,-0.30656 0.35352,-0.35156c0.805,-0.216 4.41488,-0.34961 7.29688,-0.34961zM10,8.53516v6.92969l6,-3.46484z">
                                    </path>
                                </g>
                            </g>
                        </svg>
                    <?php endif; ?>
                </a>
            <?php endif; ?>
        </div>
        <p class="mb-2 text-center uppercase small" style="letter-spacing: 2px;">
            <?php //echo '&copy; ' . get_bloginfo('name') . ' Copyright ' . date('Y') . ' - All Rights Reserved '; 
            ?>
            <?php echo get_bloginfo('name'); ?>

        </p>
        <div class="text-center small footlinks" style="letter-spacing: 1px;">
            <?php
            wp_nav_menu([
                'theme_location' => 'secondary',
                'container' => false,
                'menu_class' => 'me-3  text-decoration-none',
                'items_wrap' => '%3$s',
                'walker' => new MM_Walker_Nav_Menu(),
                'fallback_cb' => false,
            ]);
            ?>
        </div>
    </div>
</footer>
</main>
</div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.min.js"></script>
<script>    
    document.addEventListener('DOMContentLoaded', function() {
        const tabButtons = document.querySelectorAll('[data-bs-toggle="tab"]');
        const tabContents = document.querySelectorAll('.tab-content'); // Assuming .tab-content wrapper
        let currentIndex = 0;
        const delay = 11000;
        let intervalId;

        function showNextTab() {
            // Remove active from current
            tabButtons[currentIndex].classList.remove('active');
            document.querySelector(tabButtons[currentIndex].dataset.bsTarget).classList.remove('show', 'active');

            // Move to next index
            currentIndex = (currentIndex + 1) % tabButtons.length;

            // Add active to new
            const nextButton = tabButtons[currentIndex];
            const nextTab = new bootstrap.Tab(nextButton);
            nextTab.show();
        }

        function startSlider() {
            intervalId = setInterval(showNextTab, delay);
        }

        function stopSlider() {
            clearInterval(intervalId);
        }
        // Start initially
        startSlider();
        // Pause on hover over tabs or content
        tabButtons.forEach(button => {
            button.addEventListener('mouseenter', stopSlider);
            button.addEventListener('mouseleave', startSlider);
        });

        tabContents.forEach(content => {
            content.addEventListener('mouseenter', stopSlider);
            content.addEventListener('mouseleave', startSlider);
        });
    });
</script>