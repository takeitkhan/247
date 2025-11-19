<?php
$points_logs    = $args['points_logs'] ?? [];
$current_points = $args['current_points'] ?? 0;
$page           = $args['page'] ?? 1;
$total_pages    = $args['total_pages'] ?? 1;
?>

<?php if (empty($points_logs)): ?>
    <div class="bg-white mt-4 custom-card">
        <p>You haven't earned any rewards points yet.</p>
    </div>
<?php else: ?>
    <div class="bg-white mt-4 custom-card">
        <div class="post-search">
            <div class="gap-3 post-row">
                <div>
                    <h5 class="pb-4 text-start portal-title"><?php esc_html_e( 'Rewards Points', '247empowerment' ); ?></h5>
                    <p><?php esc_html_e( 'Your referral earning history showing below.', '247empowerment' ); ?></p>
                </div>
            </div>
            <div class="mt-3">
                <div class="d-flex flex-column gap-3">
                    <?php
                    foreach ( $points_logs as $log ) :

                        // Safely get referred user ID
                        $referred_user_id = $log['referred_user_id'] ?? null;
                        $course_post      = ! empty( $log['earned_for_id'] ) ? get_post( $log['earned_for_id'] ) : null;

                        $referred_user = $referred_user_id ? get_user_by( 'ID', $referred_user_id ) : null;
                    ?>
                        <div class="d-flex justify-content-between bg-light mt-1 p-2 rounded">
                            <div class="d-inline-flex align-items-center gap-2">
                                <span class="gap-5">
                                    <?php esc_html_e( 'Your referred user', '247empowerment' ); ?>

                                    <?php if ( $referred_user ) : ?>
                                        <a href="<?php echo esc_url( get_author_posts_url( $referred_user->ID, $referred_user->user_nicename ) ); ?>">
                                            <?php echo esc_html( $referred_user->display_name ); ?>
                                        </a>
                                    <?php else : ?>
                                        <?php echo esc_html( $referred_user_id ? 'User #' . $referred_user_id : 'Unknown User' ); ?>
                                    <?php endif; ?>

                                    <?php esc_html_e( 'purchased a course', '247empowerment' ); ?> (

                                    <?php if ( $course_post ) : ?>
                                        <a href="<?php echo esc_url( get_permalink( $course_post ) ); ?>">
                                            <?php echo esc_html( $log['earned_for'] ?? 'Unknown Course' ); ?>
                                        </a>
                                    <?php else : ?>
                                        <span><?php echo esc_html( $log['earned_for'] ?? 'Unknown Course' ); ?></span>
                                    <?php endif; ?>

                                    ) <?php esc_html_e( 'from 24/7 Empowerment\'s store at', '247empowerment' ); ?>
                                    <?php echo esc_html( date( 'F j, Y H:i', strtotime( $log['date'] ?? 'now' ) ) ); ?>
                                </span>
                            </div>

                            <span class="ml-1 text-primary-color text-end fs18 fw-bold">
                                +$<?= number_format( (float) ( $log['amount'] ?? 0 ), 2 ); ?>
                            </span>
                        </div>
                    <?php endforeach; ?>

                </div>
            </div>
        </div>
    </div>
<?php endif; ?>
<?php if ($total_pages > 1): ?>
    <div class="d-flex justify-content-center gap-2 mt-3">
        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
            <a href="?ref_page=<?= $i ?>"
                class="px-3 py-1 border rounded <?= $i == $page ? 'bg-primary text-white' : 'bg-white' ?>">
                <?= $i ?>
            </a>
        <?php endfor; ?>
    </div>
<?php endif; ?>