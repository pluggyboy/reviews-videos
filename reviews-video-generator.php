<?php
/**
 * Plugin Name: Reviews Video Generator
 * Plugin URI: https://example.com/reviews-video-generator
 * Description: Transform reviews into engaging social videos with customizable backgrounds and text overlays.
 * Version: 0.1.0
 * Author: Your Name
 * Author URI: https://example.com
 * Text Domain: reviews-video-generator
 * Domain Path: /languages
 * License: GPL v2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

// Define plugin constants
define('RVG_VERSION', '0.1.0');
define('RVG_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('RVG_PLUGIN_URL', plugin_dir_url(__FILE__));
define('RVG_PLUGIN_BASENAME', plugin_basename(__FILE__));

// Include required files
require_once RVG_PLUGIN_DIR . 'includes/class-reviews-video-generator-debug.php';
require_once RVG_PLUGIN_DIR . 'includes/class-reviews-video-generator.php';

/**
 * Begins execution of the plugin.
 */
function run_reviews_video_generator() {
    $plugin = new Reviews_Video_Generator();
    $plugin->run();
}

// Start the plugin
run_reviews_video_generator();
