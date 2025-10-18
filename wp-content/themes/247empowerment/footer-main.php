</div> <!-- Closing main container if opened earlier -->
<footer class="text-white footer-gradient footer-height">
    <div class="pt-0 pb-0 h-100 container custom-card">

        <!-- Site Name -->
        <p class="mb-1 pt-4 fw-bold"><?php echo get_bloginfo('name'); ?></p>

        <div class="align-items-center g-3 g-md-0 row">

            <!-- Site Description / Info -->
            <div class="col-md-5">
                <p class="mt-2 mt-md-0 mb-0 text-white fs14">
                    <?php echo get_bloginfo('description'); ?>
                </p>
            </div>

            <!-- Social Links -->
            <div class="col-md-3">
                <ul class="list-inline d-flex mb-0 gap12">
                    <?php
                    // Example dynamic social links (replace with ACF/theme options if needed)
                    $social_links = [
                        'twitter' => 'https://twitter.com/yourhandle',
                        'facebook' => 'https://facebook.com/yourpage',
                        'instagram' => 'https://instagram.com/yourhandle',
                        'linkedin' => 'https://linkedin.com/yourpage',
                        'youtube' => 'https://youtube.com/yourchannel'
                    ];
                    $social_icons = [
                        'twitter' => 'twitter.png',
                        'facebook' => 'fb.png',
                        'instagram' => 'instagram.png',
                        'linkedin' => 'linkedin.png',
                        'youtube' => 'youtube.png'
                    ];

                    foreach ($social_links as $key => $url) :
                    ?>
                        <li class="list-inline-item">
                            <a href="<?php echo esc_url($url); ?>" class="text-white text-decoration-none fs-3" aria-label="<?php echo ucfirst($key); ?>">
                                <img src="<?php echo get_template_directory_uri() . '/assets/img/nd/' . $social_icons[$key]; ?>" alt="<?php echo ucfirst($key); ?>">
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <!-- Footer Menu -->
            <div class="text-lg-end text-center col-lg-4">
                <?php
                wp_nav_menu([
                    'theme_location' => 'secondary',
                    'container' => false,
                    'menu_class' => 'list-inline d-flex justify-content-between mb-0',
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
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>

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