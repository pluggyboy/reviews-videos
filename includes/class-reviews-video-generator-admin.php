<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and hooks for
 * the admin area of the site.
 *
 * @since      0.1.0
 */
class Reviews_Video_Generator_Admin {

    /**
     * The ID of this plugin.
     *
     * @since    0.1.0
     * @access   private
     * @var      string    $plugin_name    The ID of this plugin.
     */
    private $plugin_name;

    /**
     * The version of this plugin.
     *
     * @since    0.1.0
     * @access   private
     * @var      string    $version    The current version of this plugin.
     */
    private $version;

    /**
     * Initialize the class and set its properties.
     *
     * @since    0.1.0
     * @param    string    $plugin_name       The name of this plugin.
     * @param    string    $version           The version of this plugin.
     */
    public function __construct($plugin_name, $version) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
        
        // Register AJAX handlers
        add_action('wp_ajax_rvg_create_video', array($this, 'ajax_create_video'));
        add_action('wp_ajax_rvg_get_video_status', array($this, 'ajax_get_video_status'));
    }
    
    /**
     * AJAX handler for creating a video.
     *
     * @since    0.1.0
     */
    public function ajax_create_video() {
        // Check nonce for security
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'rvg_nonce')) {
            wp_send_json_error(array('message' => __('Security check failed.', 'reviews-video-generator')));
        }
        
        Reviews_Video_Generator_Debug::info('AJAX request received for creating video', [
            'post_data' => $_POST
        ]);
        
        // Get video data from POST
        $video_data = array(
            'review_id' => isset($_POST['review_id']) ? sanitize_text_field($_POST['review_id']) : '',
            'author_name' => isset($_POST['author_name']) ? sanitize_text_field($_POST['author_name']) : '',
            'rating' => isset($_POST['rating']) ? intval($_POST['rating']) : 5,
            'review_text' => isset($_POST['review_text']) ? sanitize_textarea_field($_POST['review_text']) : '',
            'background_video' => isset($_POST['background_video']) ? esc_url_raw($_POST['background_video']) : '',
            'text_color' => isset($_POST['text_color']) ? sanitize_hex_color($_POST['text_color']) : '#FFFFFF',
            'font' => isset($_POST['font']) ? sanitize_text_field($_POST['font']) : 'Open Sans',
            'font_size' => isset($_POST['font_size']) ? intval($_POST['font_size']) : 30,
            'aspect_ratio' => isset($_POST['aspect_ratio']) ? sanitize_text_field($_POST['aspect_ratio']) : '16:9'
        );
        
        // Log the font size and background video URL for debugging
        Reviews_Video_Generator_Debug::debug('Video creation data received', [
            'font_size' => $video_data['font_size'],
            'font_size_raw' => isset($_POST['font_size']) ? $_POST['font_size'] : 'not set',
            'font_size_type' => isset($_POST['font_size']) ? gettype($_POST['font_size']) : 'not set',
            'url' => $video_data['background_video']
        ]);
        
        // Validate required fields
        if (empty($video_data['review_text']) || empty($video_data['author_name']) || empty($video_data['background_video'])) {
            Reviews_Video_Generator_Debug::error('Missing required fields for video creation', [
                'video_data' => $video_data
            ]);
            
            wp_send_json_error(array('message' => __('Please fill in all required fields.', 'reviews-video-generator')));
        }
        
        // Create the video using the Shotstack API
        $shotstack_api = new Reviews_Video_Generator_Shotstack_API();
        $response = $shotstack_api->create_video($video_data);
        
        if (is_wp_error($response)) {
            Reviews_Video_Generator_Debug::error('WP_Error when creating video', [
                'error' => $response->get_error_message()
            ]);
            
            wp_send_json_error(array('message' => $response->get_error_message()));
        }
        
        // Check if the response contains the render ID
        if (!isset($response['response']['id'])) {
            Reviews_Video_Generator_Debug::error('Invalid response from Shotstack API', [
                'response' => $response
            ]);
            
            wp_send_json_error(array('message' => __('Invalid response from Shotstack API.', 'reviews-video-generator')));
        }
        
        // Store the render ID in the database
        $render_id = $response['response']['id'];
        $videos = get_option('rvg_videos', array());
        $videos[] = array(
            'id' => uniqid(),
            'render_id' => $render_id,
            'review_id' => $video_data['review_id'],
            'author_name' => $video_data['author_name'],
            'rating' => $video_data['rating'],
            'review_text' => $video_data['review_text'],
            'aspect_ratio' => $video_data['aspect_ratio'],
            'status' => 'queued',
            'url' => '',
            'created' => current_time('mysql')
        );
        update_option('rvg_videos', $videos);
        
        Reviews_Video_Generator_Debug::info('Video creation request sent to Shotstack API', [
            'render_id' => $render_id
        ]);
        
        // Return success response with render ID
        wp_send_json_success(array(
            'render_id' => $render_id,
            'message' => __('Video creation started.', 'reviews-video-generator')
        ));
    }
    
    /**
     * AJAX handler for getting video status.
     *
     * @since    0.1.0
     */
    public function ajax_get_video_status() {
        // Check nonce for security
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'rvg_nonce')) {
            wp_send_json_error(array('message' => __('Security check failed.', 'reviews-video-generator')));
        }
        
        // Get render ID from POST
        $render_id = isset($_POST['render_id']) ? sanitize_text_field($_POST['render_id']) : '';
        
        if (empty($render_id)) {
            Reviews_Video_Generator_Debug::error('Missing render ID for video status check');
            wp_send_json_error(array('message' => __('Missing render ID.', 'reviews-video-generator')));
        }
        
        Reviews_Video_Generator_Debug::info('AJAX request received for video status', [
            'render_id' => $render_id
        ]);
        
        // Get the status from the Shotstack API
        $shotstack_api = new Reviews_Video_Generator_Shotstack_API();
        $response = $shotstack_api->get_render_status($render_id);
        
        if (is_wp_error($response)) {
            Reviews_Video_Generator_Debug::error('WP_Error when getting video status', [
                'error' => $response->get_error_message()
            ]);
            
            wp_send_json_error(array('message' => $response->get_error_message()));
        }
        
        // Check if the response contains the status
        if (!isset($response['response']['status'])) {
            Reviews_Video_Generator_Debug::error('Invalid response from Shotstack API for status check', [
                'response' => $response
            ]);
            
            wp_send_json_error(array('message' => __('Invalid response from Shotstack API.', 'reviews-video-generator')));
        }
        
        $status = $response['response']['status'];
        $progress = 0;
        
        // Calculate progress based on status
        switch ($status) {
            case 'queued':
                $progress = 10;
                break;
            case 'fetching':
                $progress = 20;
                break;
            case 'rendering':
                $progress = 50;
                break;
            case 'saving':
                $progress = 80;
                break;
            case 'done':
                $progress = 100;
                
                // Update the video URL in the database
                if (isset($response['response']['url'])) {
                    $videos = get_option('rvg_videos', array());
                    foreach ($videos as $key => $video) {
                        if ($video['render_id'] === $render_id) {
                            $videos[$key]['status'] = 'done';
                            $videos[$key]['url'] = $response['response']['url'];
                            break;
                        }
                    }
                    update_option('rvg_videos', $videos);
                }
                break;
            case 'failed':
                $progress = 0;
                break;
        }
        
        Reviews_Video_Generator_Debug::info('Video status check completed', [
            'render_id' => $render_id,
            'status' => $status,
            'progress' => $progress,
            'url' => isset($response['response']['url']) ? $response['response']['url'] : ''
        ]);
        
        // Return success response with status and progress
        wp_send_json_success(array(
            'status' => $status,
            'progress' => $progress,
            'url' => isset($response['response']['url']) ? $response['response']['url'] : '',
            'message' => __('Video status: ' . $status, 'reviews-video-generator')
        ));
    }

    /**
     * Register the stylesheets for the admin area.
     *
     * @since    0.1.0
     */
    public function enqueue_styles() {
        wp_enqueue_style($this->plugin_name, plugin_dir_url(dirname(__FILE__)) . 'admin/css/reviews-video-generator-admin.css', array(), $this->version, 'all');
    }

    /**
     * Register the JavaScript for the admin area.
     *
     * @since    0.1.0
     */
    public function enqueue_scripts() {
        // Enqueue WordPress media scripts
        wp_enqueue_media();
        
        // Enqueue WordPress dashicons
        wp_enqueue_style('dashicons');
        
        // Enqueue the plugin's admin JavaScript
        wp_enqueue_script($this->plugin_name, plugin_dir_url(dirname(__FILE__)) . 'admin/js/reviews-video-generator-admin.js', array('jquery'), $this->version, true);
        
        // Localize the script with data for the admin JS
        wp_localize_script($this->plugin_name, 'rvg_admin_data', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('rvg_nonce'),
            'plugin_url' => RVG_PLUGIN_URL,
            'i18n' => array(
                'error_creating_video' => __('Error creating video. Please try again.', 'reviews-video-generator'),
                'error_getting_status' => __('Error getting video status. Please try again.', 'reviews-video-generator'),
                'video_ready' => __('Video ready!', 'reviews-video-generator'),
                'preparing_assets' => __('Preparing video assets...', 'reviews-video-generator'),
                'rendering_video' => __('Rendering video...', 'reviews-video-generator'),
                'finalizing_video' => __('Finalizing video...', 'reviews-video-generator'),
                'please_wait' => __('Please wait...', 'reviews-video-generator')
            )
        ));
    }

    /**
     * Add menu items to the admin menu.
     *
     * @since    0.1.0
     */
    public function add_plugin_admin_menu() {
        // Main menu item
        add_menu_page(
            __('Review Showcase', 'reviews-video-generator'),
            __('Review Showcase', 'reviews-video-generator'),
            'manage_options',
            $this->plugin_name,
            array($this, 'display_plugin_admin_dashboard'),
            'dashicons-video-alt2',
            30
        );

        // Dashboard submenu
        add_submenu_page(
            $this->plugin_name,
            __('Dashboard', 'reviews-video-generator'),
            __('Dashboard', 'reviews-video-generator'),
            'manage_options',
            $this->plugin_name,
            array($this, 'display_plugin_admin_dashboard')
        );

        // Reviews submenu
        add_submenu_page(
            $this->plugin_name,
            __('Reviews', 'reviews-video-generator'),
            __('Reviews', 'reviews-video-generator'),
            'manage_options',
            $this->plugin_name . '-reviews',
            array($this, 'display_plugin_admin_reviews')
        );

        // Create Video submenu
        add_submenu_page(
            $this->plugin_name,
            __('Create Video', 'reviews-video-generator'),
            __('Create Video', 'reviews-video-generator'),
            'manage_options',
            $this->plugin_name . '-create-video',
            array($this, 'display_plugin_admin_create_video')
        );

        // Videos submenu
        add_submenu_page(
            $this->plugin_name,
            __('Videos', 'reviews-video-generator'),
            __('Videos', 'reviews-video-generator'),
            'manage_options',
            $this->plugin_name . '-videos',
            array($this, 'display_plugin_admin_videos')
        );
        
        // Background Videos submenu
        add_submenu_page(
            $this->plugin_name,
            __('Background Videos', 'reviews-video-generator'),
            __('Background Videos', 'reviews-video-generator'),
            'manage_options',
            $this->plugin_name . '-background-videos',
            array($this, 'display_plugin_admin_background_videos')
        );

        // Settings submenu
        add_submenu_page(
            $this->plugin_name,
            __('Settings', 'reviews-video-generator'),
            __('Settings', 'reviews-video-generator'),
            'manage_options',
            $this->plugin_name . '-settings',
            array($this, 'display_plugin_admin_settings')
        );
    }

    /**
     * Register plugin settings.
     *
     * @since    0.1.0
     */
    public function register_settings() {
        // Register a setting for the Shotstack API key
        register_setting(
            'rvg_settings',
            'rvg_shotstack_api_key',
            array(
                'type' => 'string',
                'sanitize_callback' => 'sanitize_text_field',
                'default' => ''
            )
        );

        // Register a setting for the Shotstack API environment (sandbox/production)
        register_setting(
            'rvg_settings',
            'rvg_shotstack_environment',
            array(
                'type' => 'string',
                'sanitize_callback' => 'sanitize_text_field',
                'default' => 'sandbox'
            )
        );

        // Add a settings section
        add_settings_section(
            'rvg_settings_section',
            __('API Settings', 'reviews-video-generator'),
            array($this, 'settings_section_callback'),
            'rvg_settings'
        );

        // Add settings fields
        add_settings_field(
            'rvg_shotstack_api_key',
            __('Shotstack API Key', 'reviews-video-generator'),
            array($this, 'api_key_field_callback'),
            'rvg_settings',
            'rvg_settings_section'
        );

        add_settings_field(
            'rvg_shotstack_environment',
            __('Shotstack Environment', 'reviews-video-generator'),
            array($this, 'environment_field_callback'),
            'rvg_settings',
            'rvg_settings_section'
        );
    }

    /**
     * Settings section callback.
     *
     * @since    0.1.0
     */
    public function settings_section_callback() {
        echo '<p>' . __('Configure your Shotstack API settings below. You need a valid API key to generate videos.', 'reviews-video-generator') . '</p>';
    }

    /**
     * API key field callback.
     *
     * @since    0.1.0
     */
    public function api_key_field_callback() {
        $api_key = get_option('rvg_shotstack_api_key');
        echo '<input type="text" id="rvg_shotstack_api_key" name="rvg_shotstack_api_key" value="' . esc_attr($api_key) . '" class="regular-text">';
    }

    /**
     * Environment field callback.
     *
     * @since    0.1.0
     */
    public function environment_field_callback() {
        $environment = get_option('rvg_shotstack_environment', 'sandbox');
        ?>
        <select id="rvg_shotstack_environment" name="rvg_shotstack_environment">
            <option value="sandbox" <?php selected($environment, 'sandbox'); ?>><?php _e('Sandbox', 'reviews-video-generator'); ?></option>
            <option value="production" <?php selected($environment, 'production'); ?>><?php _e('Production', 'reviews-video-generator'); ?></option>
        </select>
        <p class="description"><?php _e('Use Sandbox for testing, Production for live videos.', 'reviews-video-generator'); ?></p>
        <?php
    }

    /**
     * Render the dashboard page.
     *
     * @since    0.1.0
     */
    public function display_plugin_admin_dashboard() {
        include_once RVG_PLUGIN_DIR . 'templates/admin-dashboard.php';
    }

    /**
     * Render the reviews page.
     *
     * @since    0.1.0
     */
    public function display_plugin_admin_reviews() {
        include_once RVG_PLUGIN_DIR . 'templates/admin-reviews.php';
    }

    /**
     * Render the create video page.
     *
     * @since    0.1.0
     */
    public function display_plugin_admin_create_video() {
        include_once RVG_PLUGIN_DIR . 'templates/admin-create-video.php';
    }

    /**
     * Render the videos page.
     *
     * @since    0.1.0
     */
    public function display_plugin_admin_videos() {
        include_once RVG_PLUGIN_DIR . 'templates/admin-videos.php';
    }

    /**
     * Render the settings page.
     *
     * @since    0.1.0
     */
    public function display_plugin_admin_settings() {
        include_once RVG_PLUGIN_DIR . 'templates/admin-settings.php';
    }
    
    /**
     * Render the background videos page.
     *
     * @since    0.1.0
     */
    public function display_plugin_admin_background_videos() {
        include_once RVG_PLUGIN_DIR . 'templates/admin-background-videos.php';
    }
    
    /**
     * Handle video upload.
     *
     * @since    0.1.0
     */
    public function handle_video_upload() {
        // Check if the form was submitted
        if (isset($_POST['rvg_upload_video_submit']) && isset($_FILES['rvg_video_file'])) {
            // Check nonce for security
            check_admin_referer('rvg_upload_video', 'rvg_upload_video_nonce');
            
            // Get the file
            $file = $_FILES['rvg_video_file'];
            
            // Debug information
            Reviews_Video_Generator_Debug::info('Video upload attempt', [
                'file' => $file['name'],
                'type' => $file['type'],
                'size' => $file['size']
            ]);
            
            // Check for errors
            if ($file['error'] !== UPLOAD_ERR_OK) {
                $error_message = 'Unknown error';
                switch ($file['error']) {
                    case UPLOAD_ERR_INI_SIZE:
                        $error_message = 'The uploaded file exceeds the upload_max_filesize directive in php.ini';
                        break;
                    case UPLOAD_ERR_FORM_SIZE:
                        $error_message = 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form';
                        break;
                    case UPLOAD_ERR_PARTIAL:
                        $error_message = 'The uploaded file was only partially uploaded';
                        break;
                    case UPLOAD_ERR_NO_FILE:
                        $error_message = 'No file was uploaded';
                        break;
                    case UPLOAD_ERR_NO_TMP_DIR:
                        $error_message = 'Missing a temporary folder';
                        break;
                    case UPLOAD_ERR_CANT_WRITE:
                        $error_message = 'Failed to write file to disk';
                        break;
                    case UPLOAD_ERR_EXTENSION:
                        $error_message = 'A PHP extension stopped the file upload';
                        break;
                }
                
                Reviews_Video_Generator_Debug::error('Video upload error', [
                    'error_code' => $file['error'],
                    'error_message' => $error_message
                ]);
                
                add_settings_error(
                    'rvg_video_upload',
                    'rvg_video_upload_error',
                    __('Error uploading file: ' . $error_message, 'reviews-video-generator'),
                    'error'
                );
                return;
            }
            
            // Check file type
            $allowed_types = array('video/mp4', 'video/quicktime', 'video/x-msvideo', 'video/x-flv', 'video/webm');
            if (!in_array($file['type'], $allowed_types)) {
                Reviews_Video_Generator_Debug::error('Invalid file type', [
                    'file_type' => $file['type'],
                    'allowed_types' => $allowed_types
                ]);
                
                add_settings_error(
                    'rvg_video_upload',
                    'rvg_video_upload_error',
                    __('Invalid file type: ' . $file['type'] . '. Please upload a video file (MP4, MOV, AVI, FLV, WEBM).', 'reviews-video-generator'),
                    'error'
                );
                return;
            }
            
            // Get video title
            $video_title = isset($_POST['rvg_video_title']) ? sanitize_text_field($_POST['rvg_video_title']) : pathinfo($file['name'], PATHINFO_FILENAME);
            
            // Prepare file data for WordPress media library
            $upload = wp_upload_bits($file['name'], null, file_get_contents($file['tmp_name']));
            
            if ($upload['error']) {
                Reviews_Video_Generator_Debug::error('Error uploading to WordPress media library', [
                    'error' => $upload['error']
                ]);
                
                add_settings_error(
                    'rvg_video_upload',
                    'rvg_video_upload_error',
                    __('Error uploading to WordPress media library: ' . $upload['error'], 'reviews-video-generator'),
                    'error'
                );
                return;
            }
            
            // Get file type
            $wp_filetype = wp_check_filetype(basename($upload['file']), null);
            
            // Prepare attachment data
            $attachment = array(
                'post_mime_type' => $wp_filetype['type'],
                'post_title' => $video_title,
                'post_content' => '',
                'post_status' => 'inherit'
            );
            
            // Insert the attachment
            $attachment_id = wp_insert_attachment($attachment, $upload['file']);
            
            if (is_wp_error($attachment_id)) {
                Reviews_Video_Generator_Debug::error('Error creating attachment', [
                    'error' => $attachment_id->get_error_message()
                ]);
                
                add_settings_error(
                    'rvg_video_upload',
                    'rvg_video_upload_error',
                    __('Error creating attachment: ' . $attachment_id->get_error_message(), 'reviews-video-generator'),
                    'error'
                );
                return;
            }
            
            // Generate metadata for the attachment
            $attachment_data = wp_generate_attachment_metadata($attachment_id, $upload['file']);
            wp_update_attachment_metadata($attachment_id, $attachment_data);
            
            // Get the attachment URL
            $attachment_url = wp_get_attachment_url($attachment_id);
            
            Reviews_Video_Generator_Debug::info('Video uploaded to WordPress media library', [
                'attachment_id' => $attachment_id,
                'url' => $attachment_url
            ]);
            
            // Get existing videos
            $videos = get_option('rvg_background_videos', array());
            
            // Add the new video
            $videos[] = array(
                'id' => uniqid(),
                'filename' => basename($upload['file']),
                'url' => $attachment_url,
                'attachment_id' => $attachment_id,
                'title' => $video_title,
                'uploaded' => current_time('mysql')
            );
            
            // Save the videos
            update_option('rvg_background_videos', $videos);
            
            // Add success message
            add_settings_error(
                'rvg_video_upload',
                'rvg_video_upload_success',
                __('Video uploaded successfully to WordPress media library.', 'reviews-video-generator'),
                'success'
            );
            
            // Redirect to avoid form resubmission
            wp_redirect(add_query_arg('settings-updated', 'true', admin_url('admin.php?page=' . $this->plugin_name . '-background-videos')));
            exit;
        }
    }
    
    /**
     * Get all uploaded background videos.
     *
     * @since    0.1.0
     * @return   array    The uploaded videos.
     */
    public function get_background_videos() {
        // Get videos from options
        $videos = get_option('rvg_background_videos', array());
        
        // Add default videos using Mixkit videos that are known to work with Shotstack
        $default_videos = array(
            array(
                'id' => 'waves',
                'filename' => 'mixkit-waves-in-the-water-1164-large.mp4',
                'url' => 'https://assets.mixkit.co/videos/preview/mixkit-waves-in-the-water-1164-large.mp4',
                'thumbnail' => 'https://assets.mixkit.co/videos/preview/mixkit-waves-in-the-water-1164-large.jpg',
                'title' => 'Ocean Waves',
                'uploaded' => '2025-01-01 00:00:00'
            ),
            array(
                'id' => 'yellow-flowers',
                'filename' => 'mixkit-tree-with-yellow-flowers-1173-large.mp4',
                'url' => 'https://assets.mixkit.co/videos/preview/mixkit-tree-with-yellow-flowers-1173-large.mp4',
                'thumbnail' => 'https://assets.mixkit.co/videos/preview/mixkit-tree-with-yellow-flowers-1173-large.jpg',
                'title' => 'Yellow Flowers',
                'uploaded' => '2025-01-01 00:00:00'
            ),
            array(
                'id' => 'forest-stream',
                'filename' => 'mixkit-forest-stream-in-the-sunlight-529-large.mp4',
                'url' => 'https://assets.mixkit.co/videos/preview/mixkit-forest-stream-in-the-sunlight-529-large.mp4',
                'thumbnail' => 'https://assets.mixkit.co/videos/preview/mixkit-forest-stream-in-the-sunlight-529-large.jpg',
                'title' => 'Forest Stream',
                'uploaded' => '2025-01-01 00:00:00'
            ),
            array(
                'id' => 'blue-sky',
                'filename' => 'mixkit-clouds-and-blue-sky-2408-large.mp4',
                'url' => 'https://assets.mixkit.co/videos/preview/mixkit-clouds-and-blue-sky-2408-large.mp4',
                'thumbnail' => 'https://assets.mixkit.co/videos/preview/mixkit-clouds-and-blue-sky-2408-large.jpg',
                'title' => 'Blue Sky',
                'uploaded' => '2025-01-01 00:00:00'
            ),
            array(
                'id' => 'beach',
                'filename' => 'mixkit-white-sand-beach-and-palm-trees-1564-large.mp4',
                'url' => 'https://assets.mixkit.co/videos/preview/mixkit-white-sand-beach-and-palm-trees-1564-large.mp4',
                'thumbnail' => 'https://assets.mixkit.co/videos/preview/mixkit-white-sand-beach-and-palm-trees-1564-large.jpg',
                'title' => 'Beach',
                'uploaded' => '2025-01-01 00:00:00'
            ),
            array(
                'id' => 'city-traffic',
                'filename' => 'mixkit-aerial-view-of-city-traffic-at-night-11-large.mp4',
                'url' => 'https://assets.mixkit.co/videos/preview/mixkit-aerial-view-of-city-traffic-at-night-11-large.mp4',
                'thumbnail' => 'https://assets.mixkit.co/videos/preview/mixkit-aerial-view-of-city-traffic-at-night-11-large.jpg',
                'title' => 'City Traffic',
                'uploaded' => '2025-01-01 00:00:00'
            )
        );
        
        // Combine videos
        return array_merge($default_videos, $videos);
    }
    
    /**
     * Delete a background video.
     *
     * @since    0.1.0
     * @param    string    $video_id    The ID of the video to delete.
     * @return   boolean                True if the video was deleted, false otherwise.
     */
    public function delete_background_video($video_id) {
        // Get videos from options
        $videos = get_option('rvg_background_videos', array());
        
        // Find the video
        foreach ($videos as $key => $video) {
            if ($video['id'] === $video_id) {
                // Check if it's a default video
                if (in_array($video['id'], array('waves', 'yellow-flowers', 'forest-stream', 'blue-sky', 'beach', 'city-traffic'))) {
                    Reviews_Video_Generator_Debug::warning('Attempted to delete a default video', [
                        'video_id' => $video_id
                    ]);
                    
                    return false;
                }
                
                // Check if it has an attachment ID
                if (isset($video['attachment_id'])) {
                    // Delete the attachment from the media library
                    $result = wp_delete_attachment($video['attachment_id'], true);
                    
                    if (!$result) {
                        Reviews_Video_Generator_Debug::error('Failed to delete attachment from media library', [
                            'attachment_id' => $video['attachment_id']
                        ]);
                        
                        return false;
                    }
                    
                    Reviews_Video_Generator_Debug::info('Deleted attachment from media library', [
                        'attachment_id' => $video['attachment_id']
                    ]);
                } else {
                    // For backward compatibility, try to delete the file from the plugin directory
                    $file = RVG_PLUGIN_DIR . 'assets/uploads/' . $video['filename'];
                    if (file_exists($file)) {
                        if (!unlink($file)) {
                            Reviews_Video_Generator_Debug::error('Failed to delete file from plugin directory', [
                                'file' => $file
                            ]);
                            
                            return false;
                        }
                        
                        Reviews_Video_Generator_Debug::info('Deleted file from plugin directory', [
                            'file' => $file
                        ]);
                    }
                }
                
                // Remove from array
                unset($videos[$key]);
                
                // Save the videos
                update_option('rvg_background_videos', array_values($videos));
                
                Reviews_Video_Generator_Debug::info('Deleted background video', [
                    'video_id' => $video_id
                ]);
                
                return true;
            }
        }
        
        Reviews_Video_Generator_Debug::warning('Video not found for deletion', [
            'video_id' => $video_id
        ]);
        
        return false;
    }
}
