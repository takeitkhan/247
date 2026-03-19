<?php
/**
 * LinkedIn Post Sharing Functions
 * Handles posting to LinkedIn user's profile
 */

if ( ! function_exists( 'share_post_to_linkedin' ) ) {
    /**
     * Post to LinkedIn user profile
     */
    function share_post_to_linkedin( $user_id, $post_id, $post_content ) {
        try {
            // Get LinkedIn token
            if ( ! function_exists( 'get_linkedin_token' ) ) {
                error_log( 'LinkedIn token function not found' );
                return false;
            }

            $token = get_linkedin_token( $user_id );
            if ( ! $token ) {
                error_log( 'No LinkedIn token found for user ' . $user_id );
                return false;
            }

            // Get LinkedIn user ID
            $linkedin_user_id = get_user_meta( $user_id, '_linkedin_user_id', true );
            if ( ! $linkedin_user_id ) {
                error_log( 'No LinkedIn user ID found for user ' . $user_id );
                return false;
            }

            // Get post data
            $post = get_post( $post_id );
            if ( ! $post ) {
                error_log( 'Post not found: ' . $post_id );
                return false;
            }

            // Prepare message
            $message = wp_strip_all_tags( $post->post_content );
            $message = mb_substr( $message, 0, 3000 ) . ( mb_strlen( $message ) > 3000 ? '...' : '' );

            // Build post URL in WordPress
            $post_url = get_permalink( $post_id );

            // Get featured image URL
            $image_url = '';
            if ( has_post_thumbnail( $post_id ) ) {
                $image_url = get_the_post_thumbnail_url( $post_id, 'large' );
            }

            // Prepare LinkedIn post data (UGC Post format)
            $post_data = array(
                'agent'              => 'rest',
                'lifecycleState'     => 'PUBLISHED',
                'specificContent'    => array(
                    'com.linkedin.ugc.ShareContent' => array(
                        'shareCommentary' => array(
                            'text' => $message
                        ),
                        'shareMediaCategory' => 'NONE'
                    )
                ),
                'visibility'         => array(
                    'com.linkedin.ugc.MemberNetworkVisibility' => 'PUBLIC'
                )
            );

            // Add image/media if available
            if ( $image_url ) {
                $post_data['specificContent']['com.linkedin.ugc.ShareContent']['media'] = array(
                    array(
                        'status'        => 'READY',
                        'description'   => array(
                            'text' => get_the_title( $post_id )
                        ),
                        'originalUrl'   => $image_url
                    )
                );
                $post_data['specificContent']['com.linkedin.ugc.ShareContent']['shareMediaCategory'] = 'IMAGE';
            }

            // Make API call to LinkedIn
            $response = wp_remote_post(
                'https://api.linkedin.com/v2/ugcPosts',
                array(
                    'headers' => array(
                        'Authorization'  => 'Bearer ' . $token,
                        'Content-Type'   => 'application/json',
                        'X-Restli-Protocol-Version' => '2.0.0'
                    ),
                    'body'    => wp_json_encode( $post_data ),
                    'timeout' => 30,
                    'sslverify' => true
                )
            );

            if ( is_wp_error( $response ) ) {
                error_log( 'LinkedIn API error: ' . $response->get_error_message() );
                return false;
            }

            $status_code = wp_remote_retrieve_response_code( $response );
            $body = wp_remote_retrieve_body( $response );

            // LinkedIn returns 201 Created on success
            if ( $status_code !== 201 ) {
                error_log( 'LinkedIn post failed. Status: ' . $status_code . ', Response: ' . $body );
                return false;
            }

            $result = json_decode( $body, true );

            if ( ! isset( $result['id'] ) ) {
                error_log( 'LinkedIn post failed. No ID in response: ' . $body );
                return false;
            }

            // Store LinkedIn post ID in post meta
            update_post_meta( $post_id, '_linkedin_post_id', $result['id'] );
            error_log( 'LinkedIn post successful. ID: ' . $result['id'] );

            return true;

        } catch ( Exception $e ) {
            error_log( 'Exception in share_post_to_linkedin: ' . $e->getMessage() );
            return false;
        }
    }
}
