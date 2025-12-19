<?php

/**
 * MVP hardcoded steps
 */
function mm_spg_get_steps()
{
    return [
        [
            'title'   => 'Welcome 👋',
            'message' => 'Welcome to the Sweet Portal. We will guide you step by step.',
        ],
        [
            'title'     => 'Update Your Profile',
            'message'   => 'This area is important to complete your profile.',
            'highlight' => '#profile-form', // CSS selector
        ],
        [
            'title'   => 'Your Digital Card',
            'message' => 'Create and share your digital business card anywhere.',
            'video'   => 'https://www.youtube.com/embed/dQw4w9WgXcQ',
        ],
        [
            'title'    => 'Next Step',
            'message'  => 'Let’s update your profile now.',
            'redirect' => '/profile',
        ],
        [
            'title'   => 'Welcome to Phase 2',
            'message' => 'We will guide you through the platform.',
        ],
        [
            'title'   => 'Social Management',
            'message' => 'Manage all your social links in one place.',
        ],
    ];
}


