  <body>
      <header
          class="custom-z-index position-fixed bg-white custom-box-shadow header-navbar end-0 start-0">
          <nav
              class="d-flex flex-wrap w-100 h-100 navbar main-container navbar-expand-lg">
              <div
                  class="d-flex align-items-center justify-content-between w-100 h-full">
                  <div class="d-flex align-items-center">
                      <div class="me-2">
                          <?php if (is_user_logged_in()): ?>
                              <?php $user = UserProfileData::getInstance(); ?>
                              <a
                                  class="position-relative d-flex align-items-center justify-content-center logo-box"
                                  href="<?php echo esc_url($user->getProfileUrl()); ?>">
                                  <img class="bottom-0 position-absolute w-100 h-100 object-fit-cover" src="<?php echo esc_url(get_theme_mod('large_logo')); ?>" alt="<?php echo esc_attr(get_bloginfo('name')); ?>">
                              </a>
                          <?php endif; ?>
                      </div>

                      <?php
                        $current_user = wp_get_current_user();
                        $username = $current_user->user_nicename;
                        $referrals_url = site_url("/{$username}/referrals/");
                        ?>

                      <form method="get" action="<?php echo esc_url($referrals_url); ?>">
                          <div class="input-group d-lg-block d-none">
                              <div class="position-relative">
                                  <img class="img-icon" src="<?php echo get_template_directory_uri(); ?>/assets/img/loggedin_images/search.png" alt="" />
                                  <input
                                      type="text"
                                      name="search"
                                      value="<?php echo esc_attr($_GET['search'] ?? ''); ?>"
                                      placeholder="Search referral partners"
                                      style="border-radius: 100px; width: 230px; padding-left: 2rem;"
                                      aria-label="Search referrals"
                                      class="form-control" />
                              </div>
                          </div>
                      </form>


                  </div>
                  <?php $current_url = home_url(add_query_arg(array(), $wp->request)); ?>

                  <div class="d-lg-block w-50 d-none">
                      <div class="d-flex justify-content-evenly middle-col">

                          <div>
                              <a href="/" class="<?php echo (untrailingslashit(home_url('/')) === untrailingslashit($current_url)) ? 'active-menu' : ''; ?>">
                                  <i class="bi bi-house fs-4"></i>
                              </a>
                          </div>

                          <div>
                              <a href="<?php echo esc_url(home_url("/$username/store")); ?>" class="<?php echo (home_url("/$username/store") === $current_url) ? 'active-menu' : ''; ?>">
                                  <i class="bi bi-shop fs-4"></i>
                              </a>
                          </div>

                          <div>
                              <a href="<?php echo esc_url(home_url("/$username/referrals")); ?>" class="<?php echo (home_url("/$username/referrals") === $current_url) ? 'active-menu' : ''; ?>">
                                  <i class="bi bi-people fs-4"></i>
                              </a>
                          </div>

                          <div>
                              <a href="<?php echo esc_url(home_url("/$username/events")); ?>" class="<?php echo (home_url("/$username/events") === $current_url) ? 'active-menu' : ''; ?>">
                                  <i class="bi bi-calendar-event fs-4"></i>
                              </a>
                          </div>


                      </div>
                  </div>


                  <div>
                      <div class="d-flex">
                          <ul
                              class="right-navbar-gap flex-row align-items-center navbar-nav">
                              <li
                                  class="right-navbar-li position-relative rounded-circle text-center nav-item">
                                  <a
                                      class="d-flex align-items-center justify-content-center w-100 h-100 nav-link"
                                      href="#">
                                      <img class="dropdown-icon" src="<?php echo get_template_directory_uri(); ?>/assets/img/loggedin_images/union.png" alt="" />
                                  </a>
                              </li>

                              <li class="position-relative nav-item">
                                  <?php
                                    $user_id = get_current_user_id();
                                    $notifications = Notifications::getInstance();
                                    $unread_notifications = $notifications->getNotifications($user_id, true); // true = only unread
                                    $unread_count = count($unread_notifications);
                                    ?>
                                  <a
                                      href="#"
                                      class="right-navbar-li position-relative d-flex align-items-center justify-content-center p-0 rounded-circle nav-link"
                                      id="notificationDropdown"
                                      role="button"
                                      data-bs-toggle="dropdown"
                                      aria-expanded="false">
                                      <svg
                                          viewBox="0 0 24 24"
                                          width="16"
                                          height="16"
                                          fill="#66676B"
                                          aria-hidden="true">
                                          <path
                                              d="M3 9.5a9 9 0 1 1 18 0v2.927c0 1.69.475 3.345 1.37 4.778a1.5 1.5 0 0 1-1.272 2.295h-4.625a4.5 4.5 0 0 1-8.946 0H2.902a1.5 1.5 0 0 1-1.272-2.295A9.01 9.01 0 0 0 3 12.43V9.5zm6.55 10a2.5 2.5 0 0 0 4.9 0h-4.9z"></path>
                                      </svg>
                                      <?php if ($unread_count > 0) : ?>
                                          <span
                                              class="notification-float-box position-absolute rounded-pill translate-middle badge"
                                              aria-label="<?php echo $unread_count; ?> unread notifications">
                                              <?php echo $unread_count; ?>
                                          </span>
                                      <?php endif; ?>
                                  </a>


                                  <?php
                                    // Get current user ID
                                    $user_id = get_current_user_id();
                                    $notifications_instance = Notifications::getInstance();
                                    $notifications = $notifications_instance->getNotifications($user_id);

                                    // Function to get human-readable relative time (e.g., '2 minutes ago')
                                    function get_relative_time($datetime)
                                    {
                                        $timestamp = strtotime($datetime);
                                        $diff = time() - $timestamp;

                                        if ($diff < 60) {
                                            return $diff . ' seconds ago';
                                        } elseif ($diff < 3600) {
                                            return floor($diff / 60) . ' minutes ago';
                                        } elseif ($diff < 86400) {
                                            return floor($diff / 3600) . ' hours ago';
                                        } else {
                                            return floor($diff / 86400) . ' days ago';
                                        }
                                    }
                                    ?>
                                  <ul
                                      class="position-absolute mt-2 dropdown-menu notification-dropdown-width dropdown-menu-end dropdown"
                                      aria-labelledby="notificationDropdown">

                                      <li class="px-3 pt-3 pb-3 border-bottom">
                                          <h6 class="mb-0 text-dark fw-semibold">Notifications</h6>
                                      </li>

                                      <?php if (!empty($notifications)) : ?>
                                          <?php foreach ($notifications as $notification) :
                                                // Customize these variables as you want:
                                                $message = esc_html($notification['message']);
                                                $type = esc_attr($notification['type']);
                                                $created_at = $notification['created_at'];
                                                $time_ago = get_relative_time($created_at);

                                                // Example: You can customize icon or image based on notification type
                                                $icon_url = get_template_directory_uri() . '/assets/img/loggedin_images/banner.jpg'; // default image

                                                // Optionally change icon per type
                                                if ($type === 'success') {
                                                    $icon_url = get_template_directory_uri() . '/assets/img/icons/success.png';
                                                } elseif ($type === 'error') {
                                                    $icon_url = get_template_directory_uri() . '/assets/img/icons/error.png';
                                                } elseif ($type === 'info') {
                                                    $icon_url = get_template_directory_uri() . '/assets/img/icons/info.png';
                                                }
                                            ?>
                                              <li>
                                                  <a class="d-flex align-items-start gap-3 px-3 pt-3 dropdown-item" href="#" style="white-space: normal;">
                                                      <!-- Icon div removed/commented out -->

                                                      <div>
                                                          <div class="fw-light small"><?php echo $message; ?></div>
                                                          <small class="text-muted"><?php echo $time_ago; ?></small>
                                                      </div>
                                                  </a>
                                              </li>

                                          <?php endforeach; ?>
                                      <?php else: ?>
                                          <li class="px-3 py-3 text-muted text-center">No notifications found.</li>
                                      <?php endif; ?>

                                  </ul>
                              </li>

                              <li class="rounded-circle list-style-none nav-item dropdown">
                                  <a
                                      href="#"
                                      class="right-navbar-li position-relative d-flex align-items-center justify-content-center p-0 rounded-circle nav-link"
                                      id="iconDropdown"
                                      role="button"
                                      data-bs-toggle="dropdown"
                                      aria-expanded="false">
                                      <img
                                          class="z-0 rounded-circle w-100 h-100 dropdown-icon"
                                          src="<?php echo esc_url(get_user_meta(get_current_user_id(), 'profile_photo', true) ?: get_template_directory_uri() . '/assets/img/loggedin_images/banner.jpg'); ?>"
                                          alt="Dropdown Icon" />
                                      <div class="bottom-0 z-2 position-absolute d-flex align-content-center justify-content-center border-2 border-white rounded-circle text-center end-0 avatar-size">

                                          <i class="rounded-circle w-100 h-100 fa-caret-down fa-solid"></i>

                                      </div>
                                  </a>
                                  <ul
                                      class="position-absolute dropdown-menu navbar-ul-width dropdown-menu-end"
                                      aria-labelledby="iconDropdown">
                                      <?php
                                        if (is_user_logged_in()) :
                                            $current_user = wp_get_current_user();
                                            $first_name = $current_user->first_name;
                                            $last_name = $current_user->last_name;

                                            // Get the current user's username (slug)
                                            $user_slug = $current_user->user_login;

                                            // Create the profile URL using the username
                                            $profile_url = home_url('/' . $user_slug);  // This should link to the user's profile page

                                        ?>
                                          <li>
                                              <a class="dropdown-item" href="<?php echo esc_url($profile_url); ?>">
                                                  <?php echo esc_html($first_name . ' ' . $last_name); ?>
                                              </a>
                                          </li>
                                      <?php endif; ?>
                                      <li>
                                          <a class="dropdown-item" href="<?php echo esc_url('/modify-profile'); ?>">Update Profile</a>
                                      </li>
                                      <li>
                                          <?php if (current_user_can('administrator')) : ?>
                                              <a class="dropdown-item" href="<?php echo esc_url(admin_url()); ?>">Dashboard</a>
                                          <?php endif; ?>
                                      </li>
                                      <?php if (is_user_logged_in()) : ?>
                                          <li>
                                              <a class="dropdown-item" href="<?php echo esc_url(home_url('/report')); ?>">Report an issue</a>
                                          </li>
                                      <?php endif; ?>
                                      <?php if (is_user_logged_in()) : ?>
                                          <li>
                                              <a class="dropdown-item" href="<?php echo esc_url(home_url('/suggestion')); ?>">Make a suggestion</a>
                                          </li>
                                      <?php endif; ?>

                                      <li><a class="dropdown-item" href="<?php echo wp_logout_url(home_url('/')); ?>">Logout</a></li>
                                  </ul>
                              </li>
                          </ul>
                      </div>
                  </div>
              </div>
          </nav>
      </header>