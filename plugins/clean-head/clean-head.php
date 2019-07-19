<?php
/*
 *  Plugin Name: Clean Head
 *  Description: Remove unnecessary information from &lt;head&gt;.
 *  Author: Pasi Lallinaho
 *  Version: 1.0.1
 *  Author URI: https://open.knome.fi/
 *  Plugin URI: https://wordpress.knome.fi/
 *
 *  License: GNU General Public License v2 or later
 *  License URI: https://www.gnu.org/licenses/gpl-2.0.html
 *
 */

/*
 *  Feed links
 *
 */

remove_action( 'wp_head', 'feed_links_extra', 3 );
remove_action( 'wp_head', 'feed_links', 2 );
remove_action( 'wp_head', 'rsd_link' );

/*
 *  Windows Live Writer manifest file
 *
 */

remove_action( 'wp_head', 'wlwmanifest_link' );

/*
 *  Relative links for other content on the site
 *
 */

remove_action( 'wp_head', 'index_rel_link' );
remove_action( 'wp_head', 'parent_post_rel_link', 10, 0 );
remove_action( 'wp_head', 'start_post_rel_link', 10, 0 );
remove_action( 'wp_head', 'adjacent_posts_rel_link_wp_head', 10, 0 );

/*
 *  Generator meta
 *
 */

remove_action( 'wp_head', 'wp_generator' );

/*
 *  To disable the emoji functionality, use the
 *  "Disable Emojis" plugin
 *  by Ryan Hellyer
 *  https://wordpress.org/plugins/disable-emojis/
 *
 *
 */

?>
