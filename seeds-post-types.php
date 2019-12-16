<?php

/**
Plugin Name: Seeds Post Types
Description: Custom post types for content creation and management.
Version: 1.0.0
Author: Seeds Creative Services, LLC.
Author URI: https://seedscreativeservices.com
Text Domain: seeds_post_types
*/

namespace SeedsPostTypes;

class SeedsPostTypes {

  public static $postTypes = array();

  public function __construct() {

    /**
     * Exit if this file is accessed directly.
     */

    defined('ABSPATH') || exit;

    $pluginDIR = dirname(__FILE__);
    $postTypesDIR = get_stylesheet_directory() . "/theme/post-types";

    foreach(scandir($postTypesDIR) as $file) {

      if(!in_array($file, array('.', '..'))) {

        if(is_file($postTypesDIR.'/'.$file)) {

          $postType = include_once($postTypesDIR . '/' . $file);
          
          self::$postTypes[$postType['slug']] = $postType;

        }

      }

    }

    $this->RegisterPostTypes();
    $this->RegisterPostGlances();
    $this->RegisterPostIcons();

  }

  public function RegisterPostTypes() {

    add_action('init', function() {

      foreach(self::$postTypes as $slug => $postType) {

        $postTypeLabels = array(
          'name' => $postType['name']['multiple'],
          'singular_name' => $postType['name']['singular'],
          'menu_name' => $postType['name']['multiple'],
          'name_admin_bar' => $postType['name']['singular'],
          'add_new' => "Add New",
          'add_new_item' => "New " . $postType['name']['singular'],
          'new_item' => "New " . strtolower($postType['name']['singular']),
          'edit_item' => "Edit " . strtolower($postType['name']['singular']),
          'view_item' => "View " . strtolower($postType['name']['singular']),
          'all_items' => "All " . $postType['name']['multiple'],
          'not_found' => "No " . strtolower($postType['name']['multiple']) . " found"
        );
    
        $postTypeArgs = array(
          'labels' => $postTypeLabels,
          'public' => $postType['public'],
          'show_ui' => $postType['show']['ui'],
          'show_in_menu' => $postType['show']['menu'],
          'supports' => $postType['supports']
        );
  
        register_post_type($postType['slug'], $postTypeArgs);

        if(array_key_exists('fields', $postType)) {

          call_user_func($postType['fields'], $postType);

        }

      }

    });

  }

  public function RegisterPostIcons() {

    add_action('admin_head', function() {

      foreach(self::$postTypes as $slug => $postType) {

        echo "<style type='text/css' media='all'>";
        echo ".menu-icon-" . $postType['slug'] . " .wp-menu-image::before { content: '\\" . $postType['icon'] . "' !important; }";
        echo "#dashboard_right_now a." . $postType['slug'] . "-count:before { content: '\\" . $postType['icon'] . "' !important; }";
        echo "</style>";

      }

    });

  }

  public function RegisterPostGlances() {

    add_filter('dashboard_glance_items', function($items = array()) {

      foreach(self::$postTypes as $slug => $postType) {

        if($postType['glance']) {

          $postCount = wp_count_posts($slug);
  
          if($postCount) {

            $published = intval($postCount->publish);
            $postType = get_post_type_object($slug);

            $text = _n('%s ' . $postType->labels->singular_name, '%s ' . $postType->labels->name, $published, 'seeds');
            $text = sprintf($text, number_format_i18n($published));

            if(current_user_can($postType->cap->edit_posts)) {

              $items[] = sprintf('<a class="%1$s-count" href="edit.php?post_type=%1$s">%2$s</a>', $slug, $text) . "\n";

            }

          }

        }

      }

      return $items;

    });

  }

}

global $SeedsPostTypes;
$SeedsPostTypes = new SeedsPostTypes;