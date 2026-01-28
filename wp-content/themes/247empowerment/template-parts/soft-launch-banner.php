<?php
/**
 * Soft Launch Banner
 * 
 * This template displays a welcome banner for the soft launch campaign
 * with buttons for reporting bugs and making suggestions
 */
?>

<div class="soft-launch-banner mt-2 mb-3" style="background-color: #EBC1DE; padding: 15px;">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h5 class="mb-2" style="margin-bottom: 8px; color: #333; font-weight: 600;">
                    Welcome to our soft launch; Earn points by Reporting Bugs & Making Suggestions
                </h5>
                <p class="mb-0" style="margin: 0; color: #555; font-size: 0.95rem;">
                    Help us improve the platform by sharing your feedback and reporting any issues you encounter.
                </p>
            </div>
            <div class="col-md-4">
                <div class="d-flex gap-2 justify-content-end flex-wrap">
                    <a class="custom-btn text-center text-decoration-none" style="width: auto; white-space: nowrap;" href="<?php echo esc_url( home_url( '/report' ) ); ?>">
                        <i class="bi bi-bug"></i> Report a Bug
                    </a>
                    <a class="custom-btn-outline-none text-center text-decoration-none" style="width: auto; white-space: nowrap;" href="<?php echo esc_url( home_url( '/suggestion' ) ); ?>">
                        <i class="bi bi-lightbulb"></i> Make a Suggestion
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>