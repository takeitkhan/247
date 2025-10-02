</div> <!-- Closing main container if opened earlier -->

<footer class="py-2 text-white footer-gradient">
    <div class="container-fluid">
        <div class="align-items-center px-4 overflow-hidden row">
            <div class="mb-3 col-md-5">
                <h5><?php echo get_bloginfo('name'); ?></h5>
                <p class="mb-0"><?php echo get_bloginfo('description'); ?></p>
            </div>

            <div class="mb-3 col-md-3">
                <ul class="list-inline mb-0">
                    <!-- Social links -->
                    <li class="list-inline-item"><a href="#" class="text-white fs-3" aria-label="X / Twitter"><i class="fab fa-x-twitter"></i></a></li>
                    <li class="list-inline-item"><a href="#" class="text-white fs-3" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a></li>
                    <li class="list-inline-item"><a href="#" class="text-white fs-3" aria-label="Instagram"><i class="fab fa-instagram"></i></a></li>
                    <li class="list-inline-item"><a href="#" class="text-white fs-3" aria-label="LinkedIn"><i class="fab fa-linkedin-in"></i></a></li>
                    <li class="list-inline-item"><a href="#" class="text-white fs-3" aria-label="YouTube"><i class="fab fa-youtube"></i></a></li>
                </ul>
            </div>

            <div class="mb-3 text-end col-md-4">
                <?php
                wp_nav_menu([
                    'theme_location' => 'secondary',
                    'container' => false,
                    'menu_class' => 'list-inline mb-0',
                    'items_wrap' => '<ul id="%1$s" class="%2$s">%3$s</ul>',
                    'walker' => new MM_Footer_Walker_Nav_Menu(),
                    'fallback_cb' => false,
                ]);
                ?>
            </div>
        </div>
    </div>
</footer>
<!-- Custom JS for tab slider -->
<script>
    // document.addEventListener('DOMContentLoaded', function () {
    //     const tabButtons = document.querySelectorAll('[data-bs-toggle="tab"]');
    //     const tabContents = document.querySelectorAll('.tab-content');
    //     let currentIndex = 0;
    //     const delay = 11000;
    //     let intervalId;

    //     function showNextTab() {
    //         tabButtons[currentIndex].classList.remove('active');
    //         document.querySelector(tabButtons[currentIndex].dataset.bsTarget).classList.remove('show', 'active');
    //         currentIndex = (currentIndex + 1) % tabButtons.length;
    //         const nextTab = new bootstrap.Tab(tabButtons[currentIndex]);
    //         nextTab.show();
    //     }

    //     function startSlider() {
    //         intervalId = setInterval(showNextTab, delay);
    //     }

    //     function stopSlider() {
    //         clearInterval(intervalId);
    //     }

    //     startSlider();
    //     tabButtons.forEach(btn => {
    //         btn.addEventListener('mouseenter', stopSlider);
    //         btn.addEventListener('mouseleave', startSlider);
    //     });
    //     tabContents.forEach(content => {
    //         content.addEventListener('mouseenter', stopSlider);
    //         content.addEventListener('mouseleave', startSlider);
    //     });
    // });
</script>

<?php wp_footer(); ?>
</body>
</html>