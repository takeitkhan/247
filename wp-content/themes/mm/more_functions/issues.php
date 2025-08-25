<?php
add_action('init', function () {
    register_post_type('issue_report', [
        'labels' => [
            'name'               => 'Issues List',
            'singular_name'      => 'Issue',
            'add_new'            => 'Add New Issue',
            'add_new_item'       => 'Add New Issue',
            'edit_item'          => 'Edit Issue',
            'new_item'           => 'New Issue',
            'view_item'          => 'View Issue',
            'search_items'       => 'Search Issues',
            'not_found'          => 'No issues found',
            'not_found_in_trash' => 'No issues found in trash',
        ],
        'public'       => false,
        'show_ui'      => true,
        'menu_icon'    => 'dashicons-warning',
        'supports'     => ['title'],
        'has_archive'  => false,
        'capability_type' => 'post',
    ]);
});


add_filter('manage_issue_report_posts_columns', function ($columns) {
    $columns['email'] = 'Email';
    $columns['problem_type'] = 'Problem Type';
    return $columns;
});

add_action('manage_issue_report_posts_custom_column', function ($column, $post_id) {
    if ($column === 'email') {
        echo esc_html(get_post_meta($post_id, 'email', true));
    }
    if ($column === 'problem_type') {
        echo esc_html(get_post_meta($post_id, 'problem_type', true));
    }
}, 10, 2);


add_action('add_meta_boxes', function () {
    add_meta_box(
        'issue_details_box',             // ID
        'Issue Details',                 // Title
        'render_issue_details_meta_box',// Callback
        'issue_report',                  // Post type
        'normal',                        // Context
        'high'                           // Priority
    );
});

function render_issue_details_meta_box($post) {
    $fields = [
        'name'         => 'Name',
        'email'        => 'Email',
        'phone'        => 'Phone',
        'problem_type' => 'Problem Type',
        'description'  => 'Description',
        'steps'        => 'Steps to Reproduce',
        'expected'     => 'Expected Behavior',
        'actual'       => 'Actual Behavior',
        'datetime'     => 'Date & Time',
        'page_url'     => 'Page URL',
        'consent'      => 'Consent Given',
        'attachment_url' => 'Attachment',
    ];

    echo '<table class="form-table">';
    foreach ($fields as $key => $label) {
        $value = esc_html(get_post_meta($post->ID, $key, true));
        if ($key === 'attachment_url' && $value) {
            $value = "<a href='$value' target='_blank'>View File</a>";
        }
        echo "<tr>
                <th scope='row'><label for='$key'>$label</label></th>
                <td>$value</td>
              </tr>";
    }
    echo '</table>';
}

add_action('init', function () {
    register_post_type('suggestion_report', [
        'labels' => [
            'name'               => 'Suggestions List',
            'singular_name'      => 'Suggestion',
            'add_new'            => 'Add New Suggestion',
            'add_new_item'       => 'Add New Suggestion',
            'edit_item'          => 'Edit Suggestion',
            'new_item'           => 'New Suggestion',
            'view_item'          => 'View Suggestion',
            'search_items'       => 'Search Suggestions',
            'not_found'          => 'No suggestions found',
            'not_found_in_trash' => 'No suggestions found in trash',
        ],
        'public'       => false,
        'show_ui'      => true,
        'menu_icon'    => 'dashicons-lightbulb',
        'supports'     => ['title'],
        'has_archive'  => false,
        'capability_type' => 'post',
    ]);
});

// Admin Columns
add_filter('manage_suggestion_report_posts_columns', function ($columns) {
    $columns['email'] = 'Email';
    $columns['suggestion_type'] = 'Suggestion Type';
    return $columns;
});

add_action('manage_suggestion_report_posts_custom_column', function ($column, $post_id) {
    if ($column === 'email') {
        echo esc_html(get_post_meta($post_id, 'email', true));
    }
    if ($column === 'suggestion_type') {
        echo esc_html(get_post_meta($post_id, 'suggestion_type', true));
    }
}, 10, 2);

// Meta Box
add_action('add_meta_boxes', function () {
    add_meta_box(
        'suggestion_details_box',
        'Suggestion Details',
        'render_suggestion_details_meta_box',
        'suggestion_report',
        'normal',
        'high'
    );
});

function render_suggestion_details_meta_box($post) {
    $fields = [
        'name'             => 'Name',
        'email'            => 'Email',
        'phone'            => 'Phone',
        'subject'          => 'Subject',
        'suggestion_type'  => 'Suggestion Type',
        'description'      => 'Suggestion Description',
        'datetime'         => 'Date & Time',
        'page_url'         => 'Page URL',
        'consent'          => 'Consent Given',
        'attachment_url'   => 'Attachment',
    ];

    echo '<table class="form-table">';
    foreach ($fields as $key => $label) {
        $value = esc_html(get_post_meta($post->ID, $key, true));
        if ($key === 'attachment_url' && $value) {
            $value = "<a href='$value' target='_blank'>View File</a>";
        }
        echo "<tr>
                <th scope='row'><label for='$key'>$label</label></th>
                <td>$value</td>
              </tr>";
    }
    echo '</table>';
}
