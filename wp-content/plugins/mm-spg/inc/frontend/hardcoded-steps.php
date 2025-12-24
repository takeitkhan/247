<?php

/**
 * MVP hardcoded steps
 */
function mm_spg_get_steps()
{
    return [

        // Phase 2 – Welcome & Guide Orientation
        [
            'phase' => 2,
            'title' => 'Welcome to 247 Empowerment Platform 👋',
            'blocks' => [
                [
                    'type'    => 'text',
                    'content' => 'Welcome to the 247 Empowerment Platform! 🎉 This guide will walk you through your profile, features, and how to get the most value. You can always revisit these anytime.',
                ],
            ],
        ],

        // Interests Management
        [
            'phase' => 2,
            'title' => 'Interests Management',
            'blocks' => [
                [
                    'type'    => 'text',
                    'content' => 'Choose and prioritize your interests so we can guide you on the most relevant path.',
                ],                
                [
                    'type'      => 'shortcode',
                    'shortcode' => '[mm_spg_interest_form]',
                ],
            ],
        ],

        // Social Management
        [
            'phase' => 2,
            'title' => 'Social Management',
            'blocks' => [
                [
                    'type'    => 'text',
                    'content' => 'Keep all your social links in one place and simplify how you share them. Some platforms even allow concurrent posting to multiple channels.',
                ],
                [
                    'type'      => 'shortcode',
                    'shortcode' => '[mm_spg_social_links_form]',
                ],
            ],
        ],

        // Digital Business Card (with read time)
        [
            'phase' => 2,
            'title' => 'Digital Business Card',
            'blocks' => [
                [
                    'type'    => 'text',
                    'content' => 'Your digital business card represents your personal or business brand — share it anywhere instantly.',
                ],
                [
                    'type'      => 'shortcode',
                    'shortcode' => '[mm_spg_additional_profile_details]',
                ],
            ],
        ],

        // Wallet
        [
            'phase' => 2,
            'title' => 'Your Wallet',
            'blocks' => [
                [
                    'type'    => 'text',
                    'content' => 'Your wallet shows your earnings, rewards, and opportunities. Keep track of your progress here.',
                ],
                [
                    'type' => 'redirect',
                    'url'  => 'https://personalempowermentteams.me/wallet/',
                ],
            ],
        ],

        // Collaboration
        [
            'phase' => 2,
            'title' => 'Collaboration',
            'blocks' => [
                [
                    'type'    => 'text',
                    'content' => 'Collaboration helps you connect and build with others. Teamwork boosts opportunity and shared success.',
                ],
                [
                    'type' => 'redirect',
                    'url'  => 'https://personalempowermentteams.me/collaboration/',
                ],
            ],
        ],

        // Platform Training Videos
        [
            'phase' => 2,
            'title' => 'Platform Training Videos',
            'blocks' => [
                [
                    'type'    => 'text',
                    'content' => 'Watch these tutorials to learn more about how the platform works.',
                ],
                [
                    'type' => 'video',
                    'src'  => 'https://www.youtube.com/embed/Lx5kkc5lMFc?list=PLI38LwhCUSlZKUTB9tRRNAfYCnBD8bwVO',
                ],
            ],
        ],

        // Reputation Marketing
        [
            'phase' => 2,
            'title' => 'Reputation Marketing',
            'blocks' => [
                [
                    'type'    => 'text',
                    'content' => 'Understand how powerful reputation and reviews help you grow your visibility and credibility.',
                ],
                [
                    'type' => 'redirect',
                    'url'  => 'https://personalempowermentteams.me/reputation/',
                ],
            ],
        ],

        // FAQs
        [
            'phase' => 2,
            'title' => 'FAQs',
            'blocks' => [
                [
                    'type'    => 'text',
                    'content' => 'Got questions? This FAQ page has answers to many common questions.',
                ],
                [
                    'type' => 'redirect',
                    'url'  => 'https://personalempowermentteams.me/faqs/',
                ],
            ],
        ],
    ];
}

function mm_spg_interest_paths() {
    return [
        'communication-business-marketing' => [
            ['type' => 'video', 'src' => 'https://youtu.be/Lx5kkc5lMFc?...'],
            ['type' => 'redirect', 'url' => 'https://personalempowermentteams.me/tools/'],
            ['type' => 'redirect', 'url' => 'https://personalempowermentteams.me/artificial-intelligence/'],
            ['type' => 'redirect', 'url' => 'https://personalempowermentteams.me/empowerment-teams/'],
            ['type' => 'redirect', 'url' => 'https://personalempowermentteams.me/faqs/'],
        ],

        'diabetes-health-fitness' => [
            ['type' => 'redirect', 'url' => 'https://personalempowermentteams.me/empowerment-teams/'],
            ['type' => 'video', 'src' => 'https://youtu.be/IkdAV35bdfk?...'],
            ['type' => 'redirect', 'url' => 'https://personalempowermentteams.me/faqs/'],
        ],

        // more paths...
    ];
}


function mm_spg_build_phase_3_steps($interest_slug) {

    $paths = mm_spg_interest_paths();
    if (!isset($paths[$interest_slug])) {
        return [];
    }

    $term = get_term_by('slug', $interest_slug, 'category');
    $title = $term ? 'Your ' . $term->name . ' Path' : 'Your Personalized Path';

    $steps = [];

    foreach ($paths[$interest_slug] as $item) {
        $steps[] = [
            'phase'  => 3,
            'title'  => $title,
            'blocks' => [$item],
        ];
    }

    return $steps;
}

