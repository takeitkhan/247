<?php
defined('ABSPATH') || exit;

/**
 * Get ALL steps dynamically
 * Replaces hardcoded mm_spg_get_steps()
 */
function mm_spg_get_steps()
{
    $query = new WP_Query([
        'post_type'      => 'spg_step',
        'posts_per_page' => -1,
        'post_status'    => 'publish',

        // 🔑 ordering
        'orderby'        => 'menu_order ID',
        'order'          => 'ASC',

        'meta_query'     => [
            [
                'key'     => '_spg_phase',
                'compare' => 'EXISTS',
            ],
        ],
    ]);

    $steps = [];

    while ($query->have_posts()) {
        $query->the_post();

        $steps[] = [
            'phase'    => (int) get_post_meta(get_the_ID(), '_spg_phase', true),
            'title'    => get_the_title(),
            'interest' => get_post_meta(get_the_ID(), '_spg_interest', true) ?: null,
            'blocks'   => get_post_meta(get_the_ID(), '_spg_blocks', true) ?: [],
        ];
    }

    wp_reset_postdata();

    return $steps;
}


/**
 * Build Phase 3 steps based on interest slug
 */
function mm_spg_build_phase_3_steps($interest_slug)
{
    if (empty($interest_slug)) {
        return [];
    }

    $query = new WP_Query([
        'post_type'      => 'spg_step',
        'posts_per_page' => -1,
        'post_status'    => 'publish',
        'orderby'        => 'menu_order',
        'order'          => 'ASC',
        'meta_query'     => [
            [
                'key'   => '_spg_phase',
                'value' => 3,
                'type'  => 'NUMERIC',
            ],
            [
                'key'   => '_spg_interest',
                'value' => $interest_slug,
            ],
        ],
    ]);

    $steps = [];

    while ($query->have_posts()) {
        $query->the_post();

        $steps[] = [
            'phase'    => 3,
            'title'    => get_the_title(),
            'interest' => $interest_slug,
            'blocks'   => get_post_meta(get_the_ID(), '_spg_blocks', true) ?: [],
        ];
    }

    wp_reset_postdata();

    return $steps;
}