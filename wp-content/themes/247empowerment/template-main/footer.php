</div>
<footer class="py-4 text-white footer-gradient">
    <div class="container">
        <div class="align-items-center row">            
            <div class="mb-3 col-md-5">
                <h5><?php echo get_bloginfo('name'); ?></h5>
                <p class="mb-0"><?php echo get_bloginfo(show: 'tagline'); ?></p>
            </div>
            <div class="mb-3 col-md-3">
                <ul class="list-inline mb-0">
                    <li class="list-inline-item">
                        <a href="#" class="text-white text-decoration-none fs-3" aria-label="X / Twitter">
                            <i class="fab fa-x-twitter"></i>
                        </a>
                    </li>
                    <li class="list-inline-item">
                        <a href="#" class="text-white text-decoration-none fs-3" aria-label="Facebook">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                    </li>
                    <li class="list-inline-item">
                        <a href="#" class="text-white text-decoration-none fs-3" aria-label="Instagram">
                            <i class="fab fa-instagram"></i>
                        </a>
                    </li>
                    <li class="list-inline-item">
                        <a href="#" class="text-white text-decoration-none fs-3" aria-label="LinkedIn">
                            <i class="fab fa-linkedin-in"></i>
                        </a>
                    </li>
                    <li class="list-inline-item">
                        <a href="#" class="text-white text-decoration-none fs-3" aria-label="YouTube">
                            <i class="fab fa-youtube"></i>
                        </a>
                    </li>
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