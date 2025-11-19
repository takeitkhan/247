<?php
/* Template Name: Modify Profile */
if (is_user_logged_in()) {
    get_header('portal');
} else {
    get_header('main');
}

if (!is_user_logged_in()) {
    wp_redirect(wp_login_url());
    exit;
}

$current_user = wp_get_current_user();

// Get current logged-in user ID (used as a fallback if no slug is provided)
$current_user_id = get_current_user_id();

// 1. Get the user slug from the query variable
$user_slug = get_query_var('user_profile');

// 2. Determine the target user
if ($user_slug) {
    // If a slug is present, try to get the user by their slug (login or nicename)
    $user = get_user_by('slug', $user_slug);
} else {
    // If no slug, fall back to the currently logged-in user
    $user = get_user_by('ID', $current_user_id);
}

// 3. Instantiate the UserProfileData class and get the profile array
if ($user) {
    // We pass the WP_User object to the class constructor, or the ID/slug depending on the class's constructor.
    // Given your original line: $profile = (new UserProfileData($user_slug))->getProfile();
    // We'll update it to pass the $user object for better data handling, assuming the class supports it.
    // If the class REQUIRES a slug, use $user_slug or $user->user_login.

    // Option A: If UserProfileData takes a WP_User object (Recommended)
    $profile_data_instance = new UserProfileData($user);

    // Option B: If UserProfileData only takes the slug (Sticking closer to your original code)
    // Use the slug if present, otherwise use the current user's login.
    $target_identifier = $user_slug ? $user_slug : $user->user_login;
    $profile_data_instance = new UserProfileData($target_identifier);

    // Get the profile array
    $profile = $profile_data_instance->getProfile();
} else {
    // Set variables to null if no user could be determined
    $user = null;
    $profile = null;
}

$dob   = get_user_meta($current_user->ID, 'dob', true);
$phone = get_user_meta($current_user->ID, 'phone', true);
$referrer = get_user_meta($current_user->ID, 'referrer', true);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    if (!isset($_POST['frontend_profile_update_nonce']) || !wp_verify_nonce($_POST['frontend_profile_update_nonce'], 'frontend_profile_update')) {
        echo '<div class="alert alert-danger">Security check failed.</div>';
    } else {
        $user_id = $current_user->ID;

        wp_update_user([
            'ID'         => $user_id,
            'first_name' => sanitize_text_field($_POST['first_name']),
            'last_name'  => sanitize_text_field($_POST['last_name']),
            'user_email' => sanitize_email($_POST['email']),
        ]);

        update_user_meta($user_id, 'dob', sanitize_text_field($_POST['dob']));
        update_user_meta($user_id, 'phone', sanitize_text_field($_POST['phone']));
        update_user_meta($user_id, 'about_me', sanitize_textarea_field($_POST['about_me']));
        update_user_meta($user_id, 'about_me_short', sanitize_text_field($_POST['about_me_short']));
        // update_user_meta($user_id, 'location', sanitize_text_field($_POST['location']));
        if (!empty($_POST['latitude']) && !empty($_POST['longitude'])) {
            update_user_meta($user_id, 'latitude', sanitize_text_field($_POST['latitude']));
            update_user_meta($user_id, 'longitude', sanitize_text_field($_POST['longitude']));
        }

        if (!empty($_POST['place_display_name'])) {
            update_user_meta($user_id, 'place_display_name', sanitize_text_field($_POST['place_display_name']));
        }

        if (!empty($_POST['place_address'])) {
            update_user_meta($user_id, 'place_address', sanitize_text_field($_POST['place_address']));
        }

        update_user_meta($user_id, 'user_categories', array_map('intval', $_POST['user_categories'] ?? []));

        update_user_meta($user_id, 'show_email', isset($_POST['show_email']) ? '1' : '0');
        update_user_meta($user_id, 'show_phone', isset($_POST['show_phone']) ? '1' : '0');
        update_user_meta($user_id, 'show_dob', isset($_POST['show_dob']) ? '1' : '0');
        update_user_meta($user_id, 'show_full_address', isset($_POST['show_full_address']) ? '1' : '0');


        echo '
        <script>
        document.addEventListener("DOMContentLoaded", function() {
            Toastify({
            text: "Profile updated successfully.",
            duration: 4000,
            gravity: "bottom", // `top` or `bottom`
            position: "left", // `left`, `center` or `right`
            backgroundColor: "#28a745",
            close: true,
            stopOnFocus: true,
            }).showToast();
        });
        </script>
        ';


        $current_user = wp_get_current_user();
        $dob   = get_user_meta($current_user->ID, 'dob', true);
        $phone = get_user_meta($current_user->ID, 'phone', true);
    }
}

?>

<div class="container profile-page pt20">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-lg-3">
            <?php get_template_part('template-custom/auth/common-parts/editprofilemenu', null, ['profile' => $profile]); ?>
            <?php get_template_part('template-custom/auth/profile-parts/navlink', null, ['profile' => $profile]); ?>
        </div>
        <div class="mb-0 rounded-end-0 col-lg-6">
            <div class="bg-white custom-card post-search">
                <div class="gap-3 post-row">
                    <div>
                        <h5 class="pb-4 text-start portal-title">Update Profile</h5>
                    </div>

                    <div>
                        <form method="post" class="row g-3">
                            <?php wp_nonce_field('frontend_profile_update', 'frontend_profile_update_nonce'); ?>

                            <div class="col-12 col-md-6">
                                <label class="form-label">First Name:</label>
                                <input type="text" name="first_name" class="form-control input"
                                    value="<?php echo esc_attr($current_user->first_name); ?>" placeholder="Enter your first name" required>
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="form-label">Last Name:</label>
                                <input type="text" name="last_name" class="form-control input"
                                    value="<?php echo esc_attr($current_user->last_name); ?>" placeholder="Enter your last name" required>
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="form-label">Email:</label>
                                <input type="email" name="email" class="form-control input"
                                    value="<?php echo esc_attr($current_user->user_email); ?>" placeholder="Enter your email" required>
                                <div class="mt-2 form-check">
                                    <input type="checkbox" class="form-check-input" id="show_email" name="show_email" value="1"
                                        <?php checked(get_user_meta($current_user->ID, 'show_email', true), '1'); ?>>
                                    <label class="form-check-label" for="show_email">Show Email on profile</label>
                                </div>
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="form-label">Phone number:</label>
                                <input type="text" name="phone" class="form-control input"
                                    value="<?php echo esc_attr($phone); ?>" placeholder="Enter your phone number" required>
                                <div class="mt-2 form-check">
                                    <input type="checkbox" class="form-check-input" id="show_phone" name="show_phone" value="1"
                                        <?php checked(get_user_meta($current_user->ID, 'show_phone', true), '1'); ?>>
                                    <label class="form-check-label" for="show_phone">Show phone number on profile</label>
                                </div>
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="form-label">Date of birth:</label>
                                <input type="date" name="dob" class="form-control input"
                                    value="<?php echo esc_attr($dob); ?>">
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="form-label">About me (one liner):</label>
                                <input type="text" name="about_me_short" class="form-control input"
                                    value="<?php echo esc_attr(get_user_meta($current_user->ID, 'about_me_short', true)); ?>" placeholder="About me" required>
                            </div>

                            <div class="col-12">
                                <label class="form-label">About me (full description):</label>
                                <div class="position-relative">
                                    <textarea name="about_me" class="form-control input" rows="5"
                                        placeholder="What's on your mind?"><?php echo esc_textarea(get_user_meta($current_user->ID, 'about_me', true)); ?></textarea>
                                    <img class="modal-emoji" src="<?php echo get_template_directory_uri(); ?>/assets/img/imogi.png" alt="">
                                </div>
                            </div>

                            <div class="col-12">
                                <label class="form-label">Latitude:</label>
                                <input type="text" name="latitude" id="latitude" class="form-control input"
                                    value="<?php echo esc_attr(get_user_meta($current_user->ID, 'latitude', true)); ?>" placeholder="Enter latitude">
                            </div>

                            <div class="col-12">
                                <label class="form-label">Longitude:</label>
                                <input type="text" name="longitude" id="longitude" class="form-control input"
                                    value="<?php echo esc_attr(get_user_meta($current_user->ID, 'longitude', true)); ?>" placeholder="Enter longitude">
                            </div>

                            <div class="col-12">
                                <label class="form-label">Location Name:</label>
                                <input type="text" name="place_display_name" id="place_display_name" class="form-control input"
                                    value="<?php echo esc_attr(get_user_meta($current_user->ID, 'place_display_name', true)); ?>" placeholder="Enter your location name">
                            </div>

                            <div class="col-12">
                                <label class="form-label">Full Address:</label>
                                <input type="text" name="place_address" id="place_address" class="form-control input"
                                    value="<?php echo esc_attr(get_user_meta($current_user->ID, 'place_address', true)); ?>" placeholder="Enter your full address information">
                                <div class="mt-2 form-check">
                                    <input class="form-check-input" type="checkbox" id="show_full_address" name="show_full_address" value="1"
                                        <?php checked(get_user_meta($current_user->ID, 'show_full_address', true), '1'); ?>>
                                    <label class="form-check-label" for="show_full_address">Show full address on profile</label>
                                </div>
                            </div>

                            <div class="col-12">
                                <label class="form-label">Referrer:</label>
                                <input type="text" class="form-control input"
                                    value="<?php echo esc_attr($referrer); ?>" disabled>
                            </div>

                            <div class="col-12">
                                <label class="form-label">Choose Focus Categories:</label>
                                <div class="row">
                                    <?php
                                    $categories = get_categories(['hide_empty' => false]);
                                    $selected_cats = get_user_meta($current_user->ID, 'user_categories', true);
                                    $selected_cats = is_array($selected_cats) ? $selected_cats : [];

                                    $half = ceil(count($categories) / 2);
                                    $chunks = array_chunk($categories, $half);

                                    foreach ($chunks as $chunk) {
                                        echo '<div class="col-12 col-md-6">';
                                        foreach ($chunk as $cat) {
                                            echo '<div class="mb-2 form-check">';
                                            echo '<input class="form-check-input" type="checkbox" name="user_categories[]" value="' . esc_attr($cat->term_id) . '" ' . checked(in_array($cat->term_id, $selected_cats), true, false) . '>';
                                            echo '<label class="form-check-label">' . esc_html($cat->name) . '</label>';
                                            echo '</div>';
                                        }
                                        echo '</div>';
                                    }
                                    ?>
                                </div>
                            </div>

                            <div class="d-flex justify-content-end gap-3 mt-3 col-12">
                                <button type="button" class="w-auto text-blue-color custom-btn-size" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" name="update_profile" class="w-auto custom-btn">Update</button>
                            </div>

                        </form>
                    </div>
                </div>
            </div>
        </div>
        <div class="rounded-start-0 col-lg-3">
            <?php get_template_part('template-custom/auth/editprofile-parts/profile-photo-form', null, ['profile' => $profile]); ?>
        </div>

    </div>

</div>


<main>
    <div class="main-container" style="padding-top: 80px">

        <div class="row g-3">
            <?php include get_template_directory() . '/template-custom/auth/profile-parts/edit-profile-left-sidebar.php'; ?>

            <div class="ms-md-auto col-12 col-md-8 col-lg-9 col-xl-9">
                <div class="bg-white custom-box-shadow mb-3 p-3 custom-border-radius">
                    <div class="row">
                        <div class="col-6">
                            <h5 class="mb-5">👤 My Profile</h5>
                        </div>
                    </div>
                    <form method="post">
                        <?php wp_nonce_field('frontend_profile_update', 'frontend_profile_update_nonce'); ?>

                        <div class="row">
                            <div class="col">
                                <div class="mb-3">
                                    <label class="form-label">First Name</label>
                                    <input type="text" name="first_name" class="form-control" value="<?php echo esc_attr($current_user->first_name); ?>">
                                </div>
                            </div>
                            <div class="col">
                                <div class="mb-3">
                                    <label class="form-label">Last Name</label>
                                    <input type="text" name="last_name" class="form-control" value="<?php echo esc_attr($current_user->last_name); ?>">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col">
                                <div class="mb-3">
                                    <label class="form-label">Email</label>
                                    <input type="email" name="email" class="form-control" value="<?php echo esc_attr($current_user->user_email); ?>" required>
                                </div>
                                <div class="mb-3 form-check">
                                    <input type="checkbox" class="form-check-input" id="show_email" name="show_email" value="1" <?php checked(get_user_meta($current_user->ID, 'show_email', true), '1'); ?>>
                                    <label class="form-check-label" for="show_email">Show Email on profile</label>
                                </div>
                            </div>


                            <div class="col">
                                <div class="mb-3">
                                    <label class="form-label">Phone</label>
                                    <input type="text" name="phone" class="form-control" value="<?php echo esc_attr($phone); ?>">
                                </div>
                                <div class="mb-3 form-check">
                                    <input type="checkbox" class="form-check-input" id="show_phone" name="show_phone" value="1" <?php checked(get_user_meta($current_user->ID, 'show_phone', true), '1'); ?>>
                                    <label class="form-check-label" for="show_phone">Show Phone on profile</label>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col">
                                <div class="mb-3">
                                    <label class="form-label">Date of Birth</label>
                                    <input type="date" name="dob" class="form-control" value="<?php echo esc_attr($dob); ?>">
                                </div>
                                <div class="mb-3 form-check">
                                    <input type="checkbox" class="form-check-input" id="show_dob" name="show_dob" value="1" <?php checked(get_user_meta($current_user->ID, 'show_dob', true), '1'); ?>>
                                    <label class="form-check-label" for="show_dob">Show Phone on profile</label>
                                </div>
                            </div>
                            <div class="col">
                                <div class="mb-3">
                                    <label class="form-label">About Me (One Liner)</label>
                                    <input type="text" name="about_me_short" class="form-control" value="<?php echo esc_attr(get_user_meta($current_user->ID, 'about_me_short', true)); ?>">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col">
                                <div class="mb-3">
                                    <label class="form-label">About Me (Full Description)</label>
                                    <textarea name="about_me" class="form-control"><?php echo esc_textarea(get_user_meta($current_user->ID, 'about_me', true)); ?></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col">
                                <div class="place-autocomplete-card" id="place-autocomplete-card">
                                    <p>Search for a place here:</p>
                                </div>
                                <div id="map"></div>

                                <label class="form-label">Latitude</label>
                                <input type="text" name="latitude" id="latitude" class="form-control"
                                    value="<?php echo esc_attr(get_user_meta($current_user->ID, 'latitude', true)); ?>">

                                <label class="form-label">Longitude</label>
                                <input type="text" name="longitude" id="longitude" class="form-control"
                                    value="<?php echo esc_attr(get_user_meta($current_user->ID, 'longitude', true)); ?>">

                                <label class="form-label">Place Display Name</label>
                                <input type="text" name="place_display_name" id="place_display_name" class="form-control"
                                    value="<?php echo esc_attr(get_user_meta($current_user->ID, 'place_display_name', true)); ?>">

                                <label class="form-label">Full Address</label>
                                <input type="text" name="place_address" id="place_address" class="form-control"
                                    value="<?php echo esc_attr(get_user_meta($current_user->ID, 'place_address', true)); ?>">

                                <div class="mb-3 form-check">
                                    <input type="checkbox" class="form-check-input" id="show_full_address" name="show_full_address" value="1" <?php checked(get_user_meta($current_user->ID, 'show_full_address', true), '1'); ?>>
                                    <label class="form-check-label" for="show_full_address">Show full address on profile</label>
                                </div>


                                <!-- prettier-ignore -->
                                <script>
                                    (g => {
                                        var h, a, k, p = "The Google Maps JavaScript API",
                                            c = "google",
                                            l = "importLibrary",
                                            q = "__ib__",
                                            m = document,
                                            b = window;
                                        b = b[c] || (b[c] = {});
                                        var d = b.maps || (b.maps = {}),
                                            r = new Set,
                                            e = new URLSearchParams,
                                            u = () => h || (h = new Promise(async (f, n) => {
                                                await (a = m.createElement("script"));
                                                e.set("libraries", [...r] + "");
                                                for (k in g) e.set(k.replace(/[A-Z]/g, t => "_" + t[0].toLowerCase()), g[k]);
                                                e.set("callback", c + ".maps." + q);
                                                a.src = `https://maps.${c}apis.com/maps/api/js?` + e;
                                                d[q] = f;
                                                a.onerror = () => h = n(Error(p + " could not load."));
                                                a.nonce = m.querySelector("script[nonce]")?.nonce || "";
                                                m.head.append(a)
                                            }));
                                        d[l] ? console.warn(p + " only loads once. Ignoring:", g) : d[l] = (f, ...n) => r.add(f) && u().then(() => d[l](f, ...n))
                                    })
                                    ({
                                        key: "AIzaSyBwhYFTy_B0-NBs7jGXxIsACCBo0c2W9s0",
                                        v: "weekly"
                                    });
                                </script>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col">
                                <div class="mb-3">
                                    <label class="form-label">Referrer</label>
                                    <input type="text" class="form-control" value="<?php echo esc_attr($referrer); ?>" disabled>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Choose Categories</label>
                            <?php
                            $categories = get_categories(['hide_empty' => false]);
                            $selected_cats = get_user_meta($current_user->ID, 'user_categories', true);
                            $selected_cats = is_array($selected_cats) ? $selected_cats : [];

                            foreach ($categories as $cat) :
                            ?>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="user_categories[]" value="<?php echo esc_attr($cat->term_id); ?>"
                                        <?php checked(in_array($cat->term_id, $selected_cats)); ?>>
                                    <label class="form-check-label"><?php echo esc_html($cat->name); ?></label>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <button type="submit" name="update_profile" class="btn btn-primary">Update Profile</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</main>
<script>
    "use strict";
    let map;
    let marker;
    let infoWindow;
    let center = {
        lat: 40.749933,
        lng: -73.98633
    }; // New York City
    async function initMap() {
        // Request needed libraries.
        //@ts-ignore
        const [{
            Map
        }, {
            AdvancedMarkerElement
        }] = await Promise.all([
            google.maps.importLibrary("marker"),
            google.maps.importLibrary("places")
        ]);
        // Initialize the map.
        map = new google.maps.Map(document.getElementById('map'), {
            center,
            zoom: 13,
            mapId: '4504f8b37365c3d0',
            mapTypeControl: false,
        });
        //@ts-ignore
        const placeAutocomplete = new google.maps.places.PlaceAutocompleteElement();
        //@ts-ignore
        placeAutocomplete.id = 'place-autocomplete-input';
        placeAutocomplete.locationBias = center;
        const card = document.getElementById('place-autocomplete-card');
        //@ts-ignore
        card.appendChild(placeAutocomplete);
        map.controls[google.maps.ControlPosition.TOP_LEFT].push(card);
        // Create the marker and infowindow.
        marker = new google.maps.marker.AdvancedMarkerElement({
            map,
        });
        infoWindow = new google.maps.InfoWindow({});

        // ✅ Pin saved location if latitude and longitude are available
        const lat = parseFloat(document.getElementById('latitude').value);
        const lng = parseFloat(document.getElementById('longitude').value);
        const displayName = document.getElementById('place_display_name').value || 'Saved Location';
        const address = document.getElementById('place_address').value;

        if (!isNaN(lat) && !isNaN(lng)) {
            const savedLocation = {
                lat,
                lng
            };
            map.setCenter(savedLocation);
            map.setZoom(17);
            marker.position = savedLocation;

            const content = `
        <div id="infowindow-content">
            <span class="title">${displayName}</span><br/>
            <span>${address}</span>
        </div>`;
            updateInfoWindow(content, savedLocation);
        }


        // Add the gmp-placeselect listener, and display the results on the map.
        //@ts-ignore
        placeAutocomplete.addEventListener('gmp-select', async ({
            placePrediction
        }) => {
            const place = placePrediction.toPlace();
            await place.fetchFields({
                fields: ['displayName', 'formattedAddress', 'location']
            });

            const lat = place.location.lat();
            const lng = place.location.lng();

            // Fill hidden inputs
            document.getElementById('latitude').value = lat;
            document.getElementById('longitude').value = lng;
            document.getElementById('place_display_name').value = place.displayName;
            document.getElementById('place_address').value = place.formattedAddress;
            // If the place has a geometry, then present it on a map.
            if (place.viewport) {
                map.fitBounds(place.viewport);
            } else {
                map.setCenter(place.location);
                map.setZoom(17);
            }
            let content = '<div id="infowindow-content">' +
                '<span id="place-displayname" class="title">' + place.displayName + '</span><br />' +
                '<span id="place-address">' + place.formattedAddress + '</span>' +
                '</div>';
            updateInfoWindow(content, place.location);
            marker.position = place.location;
        });
    }
    // Helper function to create an info window.
    function updateInfoWindow(content, center) {
        infoWindow.setContent(content);
        infoWindow.setPosition(center);
        infoWindow.open({
            map,
            anchor: marker,
            shouldFocus: false,
        });
    }
    initMap();
</script>
<style>
    html,
    body {
        height: 100%;
        margin: 0;
        padding: 0;
    }
</style>
<?php
if (is_user_logged_in()) {
    get_footer('portal');
} else {
    get_footer('main');
}
?>