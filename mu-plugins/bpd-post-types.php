<?php
/**
 * Plugin Name: BPD Post Types & Taxonomies
 * Description: Registers all custom post types and taxonomies for BPD Healthcare.
 *              Must-use plugin — loads automatically, cannot be deactivated.
 *              Built for WP 7 block editor + Phase 2 headless (WPGraphQL) compatibility.
 *
 * Drop this file in wp-content/mu-plugins/
 * No activation needed.
 */

defined( 'ABSPATH' ) || exit;

add_action( 'init', 'bpd_register_post_types', 0 );
add_action( 'init', 'bpd_register_taxonomies', 0 );


// ---------------------------------------------------------------------------
// TAXONOMIES
// Register before post types so they can be passed in the 'taxonomies' arg.
// ---------------------------------------------------------------------------

function bpd_register_taxonomies() {

    // icu_category — used by icu-blog
    // Terms: 2025 Budget, Affordable Care Act, Medicaid, DOGE, etc.
    register_taxonomy( 'icu_category', 'icu-blog', [
        'label'              => 'ICU Categories',
        'labels'             => [
            'name'          => 'ICU Categories',
            'singular_name' => 'ICU Category',
            'menu_name'     => 'ICU Categories',
        ],
        'hierarchical'       => true,   // category-style (not tag-style)
        'show_ui'            => true,
        'show_in_rest'       => true,   // required for block editor
        'show_in_graphql'    => true,   // WPGraphQL Phase 2
        'graphql_single_name' => 'icuCategory',
        'graphql_plural_name' => 'icuCategories',
        'rewrite'            => [ 'slug' => 'icu-category' ],
    ] );

    // work_type — used by work (case studies)
    // Terms: Brand, Brand Strategy, Communications, Media Planning, etc.
    register_taxonomy( 'work_type', 'work', [
        'label'              => 'Work Types',
        'labels'             => [
            'name'          => 'Work Types',
            'singular_name' => 'Work Type',
            'menu_name'     => 'Work Types',
        ],
        'hierarchical'       => false,  // tag-style
        'show_ui'            => true,
        'show_in_rest'       => true,
        'show_in_graphql'    => true,
        'graphql_single_name' => 'workType',
        'graphql_plural_name' => 'workTypes',
        'rewrite'            => [ 'slug' => 'work-type' ],
    ] );

    // Standard 'category' taxonomy is already registered by WP core.
    // work and guides both use it — connect them here.
    register_taxonomy_for_object_type( 'category', 'work' );
    register_taxonomy_for_object_type( 'category', 'guides' );
}


// ---------------------------------------------------------------------------
// POST TYPES
// ---------------------------------------------------------------------------

function bpd_register_post_types() {

    // ---
    // podcasts
    // Custom fields: podcast_id, apple_podcast_url, spotify_podcast_url,
    //                rss_feed_url, episode_details, upper_label, lower_label,
    //                accent_color
    // ---
    register_post_type( 'podcasts', [
        'label'               => 'Podcasts',
        'labels'              => bpd_labels( 'Podcast', 'Podcasts' ),
        'public'              => true,
        'show_in_rest'        => true,
        'show_in_graphql'     => true,
        'graphql_single_name' => 'podcast',
        'graphql_plural_name' => 'podcasts',
        'supports'            => [ 'title', 'editor', 'thumbnail', 'excerpt', 'custom-fields' ],
        'has_archive'         => true,
        'rewrite'             => [ 'slug' => 'podcasts' ],
        'menu_icon'           => 'dashicons-microphone',
    ] );

    // ---
    // staff
    // Custom fields: role, bio, staff_type, image, pet_name, pet_label, pet_image
    // ---
    register_post_type( 'staff', [
        'label'               => 'Staff',
        'labels'              => bpd_labels( 'Staff Member', 'Staff' ),
        'public'              => true,
        'show_in_rest'        => true,
        'show_in_graphql'     => true,
        'graphql_single_name' => 'staffMember',
        'graphql_plural_name' => 'staffMembers',
        'supports'            => [ 'title', 'editor', 'thumbnail', 'custom-fields' ],
        'has_archive'         => true,
        'rewrite'             => [ 'slug' => 'team' ],
        'menu_icon'           => 'dashicons-groups',
    ] );

    // ---
    // icu-blog — "In Case You Missed It" policy/news blog
    // Taxonomy: icu_category
    // ---
    register_post_type( 'icu-blog', [
        'label'               => 'ICU Blog',
        'labels'              => bpd_labels( 'ICU Post', 'ICU Blog' ),
        'public'              => true,
        'show_in_rest'        => true,
        'show_in_graphql'     => true,
        'graphql_single_name' => 'icuPost',
        'graphql_plural_name' => 'icuPosts',
        'supports'            => [ 'title', 'editor', 'thumbnail', 'excerpt', 'custom-fields' ],
        'taxonomies'          => [ 'icu_category' ],
        'has_archive'         => true,
        'rewrite'             => [ 'slug' => 'icu-blog' ],
        'menu_icon'           => 'dashicons-welcome-learn-more',
    ] );

    // ---
    // icu-updates — shorter policy update items (different from full icu-blog posts)
    // Custom fields: headline_text, headline_link_label, headline_link_url,
    //                headline_date, update_type
    // ---
    register_post_type( 'icu-updates', [
        'label'               => 'ICU Updates',
        'labels'              => bpd_labels( 'ICU Update', 'ICU Updates' ),
        'public'              => true,
        'show_in_rest'        => true,
        'show_in_graphql'     => true,
        'graphql_single_name' => 'icuUpdate',
        'graphql_plural_name' => 'icuUpdates',
        'supports'            => [ 'title', 'editor', 'thumbnail', 'excerpt', 'custom-fields' ],
        'has_archive'         => true,
        'rewrite'             => [ 'slug' => 'icu-updates' ],
        'menu_icon'           => 'dashicons-megaphone',
    ] );

    // ---
    // bpd-news — agency news / press
    // ---
    register_post_type( 'bpd-news', [
        'label'               => 'BPD News',
        'labels'              => bpd_labels( 'News Item', 'BPD News' ),
        'public'              => true,
        'show_in_rest'        => true,
        'show_in_graphql'     => true,
        'graphql_single_name' => 'bpdNewsItem',
        'graphql_plural_name' => 'bpdNewsItems',
        'supports'            => [ 'title', 'editor', 'thumbnail', 'excerpt', 'custom-fields' ],
        'has_archive'         => true,
        'rewrite'             => [ 'slug' => 'news' ],
        'menu_icon'           => 'dashicons-pressthis',
    ] );

    // ---
    // work — case studies / portfolio
    // Custom fields: excerpt, related_posts
    // Taxonomies: work_type, category
    // ---
    register_post_type( 'work', [
        'label'               => 'Work',
        'labels'              => bpd_labels( 'Case Study', 'Work' ),
        'public'              => true,
        'show_in_rest'        => true,
        'show_in_graphql'     => true,
        'graphql_single_name' => 'caseStudy',
        'graphql_plural_name' => 'caseStudies',
        'supports'            => [ 'title', 'editor', 'thumbnail', 'excerpt', 'custom-fields' ],
        'taxonomies'          => [ 'work_type', 'category' ],
        'has_archive'         => true,
        'rewrite'             => [ 'slug' => 'work' ],
        'menu_icon'           => 'dashicons-portfolio',
    ] );

    // ---
    // events
    // Custom fields: event_date, event_date_display, event_location,
    //                event_excerpt, event_registration_url,
    //                custom_hero, hero_background_color
    // ---
    register_post_type( 'events', [
        'label'               => 'Events',
        'labels'              => bpd_labels( 'Event', 'Events' ),
        'public'              => true,
        'show_in_rest'        => true,
        'show_in_graphql'     => true,
        'graphql_single_name' => 'event',
        'graphql_plural_name' => 'events',
        'supports'            => [ 'title', 'editor', 'thumbnail', 'excerpt', 'custom-fields' ],
        'has_archive'         => true,
        'rewrite'             => [ 'slug' => 'events' ],
        'menu_icon'           => 'dashicons-calendar-alt',
    ] );

    // ---
    // guides
    // Custom fields: hero_background_color, hero_button_text, hero_button_link,
    //                hero_button_text_color, hero_button_bg_color
    // Taxonomies: category
    // ---
    register_post_type( 'guides', [
        'label'               => 'Guides',
        'labels'              => bpd_labels( 'Guide', 'Guides' ),
        'public'              => true,
        'show_in_rest'        => true,
        'show_in_graphql'     => true,
        'graphql_single_name' => 'guide',
        'graphql_plural_name' => 'guides',
        'supports'            => [ 'title', 'editor', 'thumbnail', 'excerpt', 'custom-fields' ],
        'taxonomies'          => [ 'category' ],
        'has_archive'         => true,
        'rewrite'             => [ 'slug' => 'guides' ],
        'menu_icon'           => 'dashicons-book-alt',
    ] );
}


// ---------------------------------------------------------------------------
// HELPER
// Generates a standard WP labels array from singular/plural names.
// ---------------------------------------------------------------------------

function bpd_labels( string $singular, string $plural ): array {
    return [
        'name'               => $plural,
        'singular_name'      => $singular,
        'add_new'            => "Add New {$singular}",
        'add_new_item'       => "Add New {$singular}",
        'edit_item'          => "Edit {$singular}",
        'new_item'           => "New {$singular}",
        'view_item'          => "View {$singular}",
        'search_items'       => "Search {$plural}",
        'not_found'          => "No {$plural} found",
        'not_found_in_trash' => "No {$plural} found in trash",
        'menu_name'          => $plural,
    ];
}
