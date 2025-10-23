</div> <!-- Closing main container if opened earlier -->
<footer class="border-top xtext-white xfooter-gradient footer-height" style="border-top-color: #BEC8E4 !important;">
    <div class="pt-0 pb-0 h-100 container custom-card">

        <!-- Site Name -->
        <p class="mb-1 pt-4 fw-bold"><?php echo get_bloginfo('name'); ?></p>

        <div class="align-items-center g-3 g-md-0 row">

            <!-- Site Description / Info -->
            <div class="col-md-5">
                <p class="mt-2 mt-md-0 mb-0 xtext-white fs14">
                    <?php echo get_theme_mod('mm_custom_subline', 'Default subline here'); ?>
                </p>
            </div>

            <!-- Social Links -->
            <div class="col-md-3">
                <ul class="list-inline d-flex mb-2 gap12">
                    <?php
                    // Social media platforms and icon filenames
                    $social_platforms = [
                        'facebook'  => 'fb.png',
                        'twitter'   => 'twitter.png',
                        'instagram' => 'instagram.png',
                        'linkedin'  => 'linkedin.png',
                        'youtube'   => 'youtube.png'
                    ];

                    // Loop through each platform and show only those with URLs set in the Customizer
                    foreach ($social_platforms as $platform => $icon) :
                        $url = get_theme_mod("{$platform}_url"); // Get from Customizer
                        if (!empty($url)) :
                    ?>
                            <li class="list-inline-item">
                                <a href="<?php echo esc_url($url); ?>"
                                    class="text-decoration-none xtext-white fs-3"
                                    target="_blank"
                                    rel="noopener"
                                    aria-label="<?php echo ucfirst($platform); ?>">
                                    <img
                                        src="<?php echo esc_url(get_template_directory_uri() . '/assets/img/nd/' . $icon); ?>"
                                        alt="<?php echo ucfirst($platform); ?>"
                                        width="24"
                                        height="24">
                                </a>
                            </li>
                    <?php
                        endif;
                    endforeach;
                    ?>
                </ul>

            </div>

            <!-- Footer Menu -->
            <div class="text-lg-end text-center col-lg-4">
                <?php
                wp_nav_menu([
                    'theme_location' => 'secondary',
                    'container' => false,
                    'menu_class' => 'list-inline d-flex justify-content-end mb-0 gap-4',
                    'items_wrap' => '<ul id="%1$s" class="%2$s">%3$s</ul>',
                    'walker' => new MM_Footer_Walker_Nav_Menu(),
                    'fallback_cb' => false,
                ]);
                ?>
            </div>

        </div>
    </div>
</footer>

<!-- Bootstrap & Popper -->
<!-- <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script> -->
<!-- <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script> -->

<!-- Custom JS -->
<script>
    document.addEventListener('DOMContentLoaded', function() {

        // ===== Search toggle =====
        const searchIcon = document.querySelector(".search-icon");
        const searchBox = document.querySelector(".search-box");
        const searchInput = document.querySelector("#searchInput");
        if (searchIcon && searchBox && searchInput) {
            searchIcon.addEventListener("click", e => {
                e.stopPropagation();
                searchBox.classList.toggle("active");
                if (searchBox.classList.contains("active")) searchInput.focus();
            });
            document.addEventListener("click", () => searchBox.classList.remove("active"));
            searchBox.addEventListener("click", e => e.stopPropagation());
        }

        // ===== Mobile menu toggle =====
        const menuToggle = document.getElementById("menuToggle");
        const mobileMenu = document.querySelector(".mobile-menu");
        if (menuToggle && mobileMenu) {
            menuToggle.addEventListener("click", () => mobileMenu.classList.toggle("show"));
        }

        // ===== Optional Tab slider (uncomment if needed) =====
        // const tabButtons = document.querySelectorAll('[data-bs-toggle="tab"]');
        // const tabContents = document.querySelectorAll('.tab-content');
        // let currentIndex = 0, delay = 11000, intervalId;
        // function showNextTab() { ... }
        // function startSlider() { ... }
        // function stopSlider() { ... }

    });
</script>

<?php wp_footer(); ?>
</body>

</html>