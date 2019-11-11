<?php

/*
Plugin Name: WordPress Boilerplate
Plugin URI: https://www.felipepodesta.com.br/
Description: WordPress base for new projects and jobs.
Author: Felipe Podestá
Version: 3.1
Author URI: https://www.felipepodesta.com.br/
*/

// https://gist.github.com/Alicannklc/64ca75a1e9cff530dce66c5edd2da50b#modify-admin-footer-text

/*
 * Runs on Core Init.
 */
add_action('init', 'wpbp_init');

/*
 * Runs on Admin Init.
 */
add_action('admin_init', 'wpbp_admin_init');

/*
 * Functions.
 */

function wpbp_init() {
  define('RECOVERY_MODE_EMAIL', 'null@null.local');
  define('WP_DISABLE_FATAL_ERROR_HANDLER', true);

  /*
   * Security.
   */
  remove_action('wp_head', 'rsd_link');
  remove_action('wp_head', 'wp_generator');
  remove_action('wp_head', 'index_rel_link');
  remove_action('wp_head', 'wlwmanifest_link');
  remove_action('wp_head', 'start_post_rel_link');
  remove_action('wp_head', 'adjacent_posts_rel_link');
  add_filter('xmlrpc_enabled', '__return_false');
  add_filter('recovery_mode_email_rate_limit', function ($interval) {
    return 100 * YEAR_IN_SECONDS;
  });

  /*
   * Remove emojis.
   */
  remove_action('admin_print_scripts', 'print_emoji_detection_script');
  remove_action('admin_print_styles', 'print_emoji_styles');
  remove_filter('comment_text_rss', 'wp_staticize_emoji');
  remove_filter('the_content_feed', 'wp_staticize_emoji');
  remove_filter('wp_mail', 'wp_staticize_emoji_for_email');
  remove_action('wp_head', 'print_emoji_detection_script', 7);
  remove_action('wp_print_styles', 'print_emoji_styles');
  add_filter('emoji_svg_url', '__return_false');
  add_filter('tiny_mce_plugins', function($plugins) {
    return is_array($plugins) ? array_diff($plugins, ['wpemoji']) : [];
  });

  /*
   * Remove comments.
   */
  remove_post_type_support('post', 'comments');
  remove_post_type_support('page', 'comments');

  add_action('admin_menu', function() {
    remove_menu_page('edit-comments.php');
  });

  add_action('wp_before_admin_bar_render', function() {
    global $wp_admin_bar;
    $wp_admin_bar->remove_menu('comments');
  });

  function remove_cssjs_ver( $src ) {
    if( strpos( $src, '?ver=' ) )
    $src = remove_query_arg( 'ver', $src );
    return $src;
   }

   add_filter( 'style_loader_src', 'remove_cssjs_ver', 10, 2 );
   add_filter( 'script_loader_src', 'remove_cssjs_ver', 10, 2 );

  /*
   * Add Open Graph Meta Tags
   */
  add_action('wp_head', function() {
    global $post;
    if (is_single()) {
      $html = '';

      if ( has_post_thumbnail( $post->ID ) ) {
        $img_src = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), 'thumbnail' );
      }

      $excerpt = strip_tags($post->post_content);
      $excerpt_more = '';

      if ( strlen($excerpt ) > 155) {
        $excerpt = substr($excerpt,0,155);
        $excerpt_more = ' ...';
      }

      $excerpt = str_replace( '"', '', $excerpt );
      $excerpt = str_replace( "'", '', $excerpt );
      $excerptwords = preg_split( '/[\n\r\t ]+/', $excerpt, -1, PREG_SPLIT_NO_EMPTY );
      array_pop( $excerptwords );
      $excerpt = implode( ' ', $excerptwords ) . $excerpt_more;

      // $html .= '<meta name="author" content="Your Name">';
      $html .= '<meta name="description" content="'.$excerpt.'">';
      $html .= '<meta property="og:title" content="'.get_the_title().'">';
      $html .= '<meta property="og:description" content="'.$excerpt.'">';
      $html .= '<meta property="og:type" content="article">';
      $html .= '<meta property="og:url" content="'.get_the_permalink().'">';
      $html .= '<meta property="og:site_name" content="'.get_bloginfo('name').'">';
      $html .= '<meta property="og:image" content="'.$img_src[0].'">';

      echo $html;
    } else {
      return;
    }
  }, 5);
}

function wpbp_admin_init() {
  define('FORCE_SSL_ADMIN', true);
  define('CONCATENATE_SCRIPTS', true);
  define('DISALLOW_FILE_EDIT', true);

  /*
   * Remove the default WordPress widgets.
   */
  remove_action('welcome_panel', 'wp_welcome_panel');
  add_action('wp_dashboard_setup', function() {
    global $wp_meta_boxes;

    unset(
      $wp_meta_boxes['dashboard']['normal']['core']['dashboard_activity'],
      $wp_meta_boxes['dashboard']['normal']['core']['dashboard_incoming_links'],
      $wp_meta_boxes['dashboard']['normal']['core']['dashboard_plugins'],
      $wp_meta_boxes['dashboard']['normal']['core']['dashboard_recent_comments'],
      $wp_meta_boxes['dashboard']['normal']['core']['dashboard_recent_drafts'],
      $wp_meta_boxes['dashboard']['normal']['core']['dashboard_right_now'],
      $wp_meta_boxes['dashboard']['side']['core']['dashboard_primary'],
      $wp_meta_boxes['dashboard']['side']['core']['dashboard_quick_press'],
      $wp_meta_boxes['dashboard']['side']['core']['dashboard_secondary']
   );
  });

  /*
   * Remove help tab.
   */
  add_action('admin_head', function() {
    $screen = get_current_screen();
    $screen->remove_help_tabs();
  });

  /*
   * Remove show options screen.
   */
  add_filter('screen_options_show_screen', '__return_false');

  /*
   * Modify footer text.
   */
  add_filter('admin_footer_text', '__return_empty_string');

  /*
   * Remove footer version.
   */
  remove_filter('update_footer', 'core_update_footer');
}



























// function handylinks_add_dashboard_widgets()
// {
//   // Only Admins and Editors Should See this.
//   if ((current_user_can('activate_plugins')) || current_user_can('delete_pages')) {
//     wp_add_dashboard_widget('handy_dashboard_widget', 'Handy Links', 'handy_dashboard_widget_function');
//   } // end of Admin check.
//   // Globalize the metaboxes array, this holds all the widgets for wp-admin

//   global $wp_meta_boxes;

//   // Get the regular dashboard widgets array
//   // (which has our new widget already but at the end)

//   $normal_dashboard = $wp_meta_boxes['dashboard']['normal']['core'];

//   // Backup and delete our new dashboard widget from the end of the array

//   $csf_widget_backup = array('handy_dashboard_widget' => $normal_dashboard['handy_dashboard_widget']);
//   unset($normal_dashboard['handy_dashboard_widget']);

//   // Merge the two arrays together so our widget is at the beginning

//   $sorted_dashboard = array_merge($csf_widget_backup, $normal_dashboard);

//   // Save the sorted array back into the original metaboxes

//   $wp_meta_boxes['dashboard']['normal']['core'] = $sorted_dashboard;
// }
// add_action('wp_dashboard_setup', 'handylinks_add_dashboard_widgets');

/*
  THE WIDGET
  Now, let's build the widget. You can add or remove links as you wish.
  Each link uses a Dashicon, which you can find at https://developer.wordpress.org/resource/dashicons/
  */
// function handy_dashboard_widget_function()
// {
//   echo '<h2>Handy Links</h2>';
//   echo '<ul class="handy-widget">';
//   echo '<li><span class="dashicons dashicons-admin-site"></span> <a href="' . esc_url(home_url()) . '" target="_blank">Visit the Website</a></li>'; // The front end of the website.
//   echo '<li><span class="dashicons dashicons-admin-page"></span> <a href="' . esc_url(admin_url()) . 'edit.php?post_type=page">Site Pages</a></li>'; // Pages.
//   echo '<li><span class="dashicons dashicons-admin-post"></span> <a href="' . esc_url(admin_url()) . 'edit.php">Blog Posts</a></li>'; // Posts.
//   // echo '<li><span class="dashicons dashicons-admin-post"></span> <a href="' . esc_url(admin_url() ).'custom-link.php">Your Custom Link Goes Here</a></li>'; // Custom Link.
//   echo '</ul>';
// }
