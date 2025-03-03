<?php
/**
 * The Shotstack API integration functionality of the plugin.
 *
 * @since      0.1.0
 */
class Reviews_Video_Generator_Shotstack_API {

    /**
     * The API key for Shotstack.
     *
     * @since    0.1.0
     * @access   private
     * @var      string    $api_key    The API key for Shotstack.
     */
    private $api_key;

    /**
     * The environment for Shotstack (sandbox or production).
     *
     * @since    0.1.0
     * @access   private
     * @var      string    $environment    The environment for Shotstack.
     */
    private $environment;

    /**
     * The base URL for the Shotstack API.
     *
     * @since    0.1.0
     * @access   private
     * @var      string    $api_base_url    The base URL for the Shotstack API.
     */
    private $api_base_url;

    /**
     * Initialize the class and set its properties.
     *
     * @since    0.1.0
     */
    public function __construct() {
        $this->api_key = get_option('rvg_shotstack_api_key', '');
        $this->environment = get_option('rvg_shotstack_environment', 'sandbox');
        
        // Set the API base URL based on the environment
        if ($this->environment === 'production') {
            $this->api_base_url = 'https://api.shotstack.io/v1';
        } else {
            $this->api_base_url = 'https://api.shotstack.io/stage';
        }
        
        Reviews_Video_Generator_Debug::info('Shotstack API initialized', [
            'environment' => $this->environment,
            'api_base_url' => $this->api_base_url,
            'api_key_set' => !empty($this->api_key)
        ]);
    }

    /**
     * Check if the API key is set.
     *
     * @since    0.1.0
     * @return   boolean    True if the API key is set, false otherwise.
     */
    public function is_api_key_set() {
        return !empty($this->api_key);
    }

    /**
     * Create a video using the Shotstack API.
     *
     * @since    0.1.0
     * @param    array    $video_data    The data for the video.
     * @return   array|WP_Error          The response from the API or a WP_Error.
     */
    public function create_video($video_data) {
        Reviews_Video_Generator_Debug::info('Creating video with Shotstack API', [
            'video_data' => $video_data
        ]);
        
        if (!$this->is_api_key_set()) {
            Reviews_Video_Generator_Debug::error('Shotstack API key is not set');
            return new WP_Error('api_key_missing', __('Shotstack API key is not set.', 'reviews-video-generator'));
        }

        try {
            // Prepare the video edit
            $edit = $this->prepare_video_edit($video_data);
            
            Reviews_Video_Generator_Debug::debug('Prepared video edit for Shotstack API', [
                'edit' => $edit
            ]);

            // Make the API request
            $request_args = array(
                'headers' => array(
                    'Content-Type' => 'application/json',
                    'x-api-key' => $this->api_key
                ),
                'body' => json_encode($edit),
                'timeout' => 30
            );
            
            Reviews_Video_Generator_Debug::debug('Making API request to Shotstack', [
                'url' => $this->api_base_url . '/render',
                'args' => array_merge($request_args, ['headers' => ['x-api-key' => '***REDACTED***']])
            ]);
            
            $response = wp_remote_post(
                $this->api_base_url . '/render',
                $request_args
            );

            // Check for errors
            if (is_wp_error($response)) {
                Reviews_Video_Generator_Debug::error('WP_Error when making API request to Shotstack', [
                    'error' => $response->get_error_message()
                ]);
                return $response;
            }

            // Parse the response
            $body = json_decode(wp_remote_retrieve_body($response), true);
            $status_code = wp_remote_retrieve_response_code($response);
            
            Reviews_Video_Generator_Debug::debug('Received response from Shotstack API', [
                'status_code' => $status_code,
                'body' => $body
            ]);

            if ($status_code !== 201) {
                Reviews_Video_Generator_Debug::error('Shotstack API error', [
                    'status_code' => $status_code,
                    'message' => isset($body['message']) ? $body['message'] : 'Unknown API error'
                ]);
                
                return new WP_Error(
                    'api_error',
                    isset($body['message']) ? $body['message'] : __('Unknown API error.', 'reviews-video-generator'),
                    array('status' => $status_code)
                );
            }

            Reviews_Video_Generator_Debug::info('Successfully created video with Shotstack API', [
                'render_id' => isset($body['response']['id']) ? $body['response']['id'] : 'unknown'
            ]);
            
            return $body;
        } catch (Exception $e) {
            Reviews_Video_Generator_Debug::exception($e, 'Exception when creating video with Shotstack API');
            
            return new WP_Error(
                'api_exception',
                $e->getMessage(),
                array('exception' => $e)
            );
        }
    }

    /**
     * Get the status of a video render.
     *
     * @since    0.1.0
     * @param    string    $render_id    The ID of the render.
     * @return   array|WP_Error          The response from the API or a WP_Error.
     */
    public function get_render_status($render_id) {
        Reviews_Video_Generator_Debug::info('Getting render status from Shotstack API', [
            'render_id' => $render_id
        ]);
        
        if (!$this->is_api_key_set()) {
            Reviews_Video_Generator_Debug::error('Shotstack API key is not set');
            return new WP_Error('api_key_missing', __('Shotstack API key is not set.', 'reviews-video-generator'));
        }

        try {
            // Make the API request
            $request_args = array(
                'headers' => array(
                    'x-api-key' => $this->api_key
                ),
                'timeout' => 30
            );
            
            Reviews_Video_Generator_Debug::debug('Making API request to Shotstack for render status', [
                'url' => $this->api_base_url . '/render/' . $render_id,
                'args' => array_merge($request_args, ['headers' => ['x-api-key' => '***REDACTED***']])
            ]);
            
            $response = wp_remote_get(
                $this->api_base_url . '/render/' . $render_id,
                $request_args
            );

            // Check for errors
            if (is_wp_error($response)) {
                Reviews_Video_Generator_Debug::error('WP_Error when getting render status from Shotstack API', [
                    'error' => $response->get_error_message()
                ]);
                return $response;
            }

            // Parse the response
            $body = json_decode(wp_remote_retrieve_body($response), true);
            $status_code = wp_remote_retrieve_response_code($response);
            
            Reviews_Video_Generator_Debug::debug('Received render status response from Shotstack API', [
                'status_code' => $status_code,
                'body' => $body
            ]);

            if ($status_code !== 200) {
                Reviews_Video_Generator_Debug::error('Shotstack API error when getting render status', [
                    'status_code' => $status_code,
                    'message' => isset($body['message']) ? $body['message'] : 'Unknown API error'
                ]);
                
                return new WP_Error(
                    'api_error',
                    isset($body['message']) ? $body['message'] : __('Unknown API error.', 'reviews-video-generator'),
                    array('status' => $status_code)
                );
            }

            Reviews_Video_Generator_Debug::info('Successfully got render status from Shotstack API', [
                'render_id' => $render_id,
                'status' => isset($body['response']['status']) ? $body['response']['status'] : 'unknown'
            ]);
            
            return $body;
        } catch (Exception $e) {
            Reviews_Video_Generator_Debug::exception($e, 'Exception when getting render status from Shotstack API');
            
            return new WP_Error(
                'api_exception',
                $e->getMessage(),
                array('exception' => $e)
            );
        }
    }

    /**
     * Check if a URL is a local URL.
     *
     * @since    0.1.0
     * @param    string    $url    The URL to check.
     * @return   boolean           True if the URL is local, false otherwise.
     */
    private function is_local_url($url) {
        $local_patterns = array(
            '/^https?:\/\/localhost/',
            '/^https?:\/\/127\.0\.0\.1/',
            '/^https?:\/\/192\.168\./',
            '/^https?:\/\/10\./',
            '/^https?:\/\/172\.(1[6-9]|2[0-9]|3[0-1])\./'
        );
        
        foreach ($local_patterns as $pattern) {
            if (preg_match($pattern, $url)) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Get a fallback video URL.
     *
     * @since    0.1.0
     * @return   string    A fallback video URL.
     */
    private function get_fallback_video_url() {
        // Sample videos from publicly accessible sources known to work with Shotstack
        $sample_videos = array(
            'https://assets.mixkit.co/videos/preview/mixkit-waves-in-the-water-1164-large.mp4',
            'https://assets.mixkit.co/videos/preview/mixkit-tree-with-yellow-flowers-1173-large.mp4',
            'https://assets.mixkit.co/videos/preview/mixkit-forest-stream-in-the-sunlight-529-large.mp4',
            'https://assets.mixkit.co/videos/preview/mixkit-clouds-and-blue-sky-2408-large.mp4',
            'https://assets.mixkit.co/videos/preview/mixkit-white-sand-beach-and-palm-trees-1564-large.mp4',
            'https://assets.mixkit.co/videos/preview/mixkit-aerial-view-of-city-traffic-at-night-11-large.mp4'
        );
        
        // Return a random sample video
        return $sample_videos[array_rand($sample_videos)];
    }

    /**
     * Prepare the video edit data for the Shotstack API.
     *
     * @since    0.1.0
     * @param    array    $video_data    The data for the video.
     * @return   array                   The prepared edit data.
     */
    private function prepare_video_edit($video_data) {
        // Extract video data
        $review_text = isset($video_data['review_text']) ? $video_data['review_text'] : '';
        $author_name = isset($video_data['author_name']) ? $video_data['author_name'] : '';
        $rating = isset($video_data['rating']) ? intval($video_data['rating']) : 5;
        $background_video = isset($video_data['background_video']) ? $video_data['background_video'] : '';
        $text_color = isset($video_data['text_color']) ? $video_data['text_color'] : '#FFFFFF';
        $font = isset($video_data['font']) ? $video_data['font'] : 'Open Sans';
        $aspect_ratio = isset($video_data['aspect_ratio']) ? $video_data['aspect_ratio'] : '16:9';
        
        // Check if the background video URL is local or not from a trusted domain
        $use_fallback = false;
        
        // Log the original video URL
        Reviews_Video_Generator_Debug::debug('Processing background video URL', [
            'original_url' => $background_video
        ]);
        
        // Check if it's a local URL
        if ($this->is_local_url($background_video)) {
            Reviews_Video_Generator_Debug::warning('Local video URL detected, using fallback video', [
                'local_url' => $background_video
            ]);
            $use_fallback = true;
        }
        
        // Get the current site domain
        $site_url = site_url();
        $site_domain = parse_url($site_url, PHP_URL_HOST);
        
        // Check if it's from a trusted domain
        $trusted_domains = array(
            'shotstack-assets.s3.ap-southeast-2.amazonaws.com',
            'assets.mixkit.co',
            'storage.googleapis.com',
            'vod-progressive.akamaized.net',
            's3.amazonaws.com',
            $site_domain, // Add the current site domain
            'localhost', // Add localhost for testing
            'brandytesting.com' // Add brandytesting.com for testing
        );
        
        Reviews_Video_Generator_Debug::debug('Checking if video URL is from a trusted domain', [
            'video_url' => $background_video,
            'site_domain' => $site_domain,
            'trusted_domains' => $trusted_domains
        ]);
        
        $is_trusted = false;
        foreach ($trusted_domains as $domain) {
            if (strpos($background_video, $domain) !== false) {
                $is_trusted = true;
                Reviews_Video_Generator_Debug::debug('Video URL is from a trusted domain', [
                    'domain' => $domain
                ]);
                break;
            }
        }
        
        if (!$is_trusted && !empty($background_video)) {
            Reviews_Video_Generator_Debug::warning('Non-trusted domain for video URL, using fallback video', [
                'video_url' => $background_video
            ]);
            $use_fallback = true;
        }
        
        // Use a fallback video URL if needed
        if ($use_fallback || empty($background_video)) {
            $background_video = $this->get_fallback_video_url();
            
            Reviews_Video_Generator_Debug::info('Using fallback video URL', [
                'fallback_url' => $background_video
            ]);
        }
        
        // Set dimensions based on aspect ratio
        $dimensions = $this->get_dimensions_from_aspect_ratio($aspect_ratio);
        
        // Prepare the star rating text
        $star_rating = str_repeat('★', $rating) . str_repeat('☆', 5 - $rating);
        
        // Create the edit object using the structure from the working example
        $edit = array(
            'timeline' => array(
                'fonts' => array(
                    array(
                        'src' => 'https://templates.shotstack.io/hello-world-title-video/f60f6f75-38b6-4aa8-8db0-28741aa34ec8/MovieLetters.ttf'
                    )
                ),
                'background' => '#000000',
                'tracks' => array(
                    // Review text track (first track for proper z-index)
                    array(
                        'clips' => array(
                            array(
                                'asset' => array(
                                    'type' => 'text',
                                    'text' => '"' . $review_text . '"',
                                    'font' => array(
                                        'family' => 'Clear Sans',
                                        'color' => $text_color,
                                        'size' => 70
                                    ),
                                    'alignment' => array(
                                        'horizontal' => 'center'
                                    ),
                                    'width' => 720,
                                    'height' => 212
                                ),
                                'start' => 2,
                                'length' => 7,
                                'transition' => array(
                                    'in' => 'fade',
                                    'out' => 'fade'
                                ),
                                'fit' => 'none',
                                'scale' => 1,
                                'offset' => array(
                                    'x' => 0,
                                    'y' => 0
                                ),
                                'position' => 'center',
                                'effect' => 'zoomIn'
                            )
                        )
                    ),
                    // Star rating track
                    array(
                        'clips' => array(
                            array(
                                'asset' => array(
                                    'type' => 'text',
                                    'text' => $star_rating,
                                    'font' => array(
                                        'family' => 'Clear Sans',
                                        'color' => '#FFD700',
                                        'size' => 60
                                    ),
                                    'alignment' => array(
                                        'horizontal' => 'left'
                                    ),
                                    'width' => 300,
                                    'height' => 100
                                ),
                                'start' => 1,
                                'length' => 9,
                                'transition' => array(
                                    'in' => 'fade',
                                    'out' => 'fade'
                                ),
                                'fit' => 'none',
                                'scale' => 1,
                                'offset' => array(
                                    'x' => 0.1,
                                    'y' => 0.3
                                ),
                                'position' => 'bottomLeft',
                                'effect' => 'zoomIn'
                            )
                        )
                    ),
                    // Author name track
                    array(
                        'clips' => array(
                            array(
                                'asset' => array(
                                    'type' => 'text',
                                    'text' => '- ' . $author_name,
                                    'font' => array(
                                        'family' => 'Clear Sans',
                                        'color' => $text_color,
                                        'size' => 50
                                    ),
                                    'alignment' => array(
                                        'horizontal' => 'right'
                                    ),
                                    'width' => 400,
                                    'height' => 100
                                ),
                                'start' => 3,
                                'length' => 6,
                                'transition' => array(
                                    'in' => 'fade',
                                    'out' => 'fade'
                                ),
                                'fit' => 'none',
                                'scale' => 1,
                                'offset' => array(
                                    'x' => 0.1,
                                    'y' => 0.1
                                ),
                                'position' => 'bottomRight',
                                'effect' => 'zoomIn'
                            )
                        )
                    ),
                    // Background video track (after text tracks for proper z-index)
                    array(
                        'clips' => array(
                            array(
                                'asset' => array(
                                    'type' => 'video',
                                    'src' => $background_video,
                                    'volume' => 1
                                ),
                                'start' => 0,
                                'length' => 10,
                                'transition' => array(
                                    'in' => 'fade',
                                    'out' => 'fade'
                                ),
                                'position' => 'center',
                                'scale' => 1,
                                'effect' => 'zoomIn',
                                'filter' => 'darken'
                            )
                        )
                    )
                )
            ),
            'output' => array(
                'format' => 'mp4',
                'fps' => 30,
                'size' => array(
                    'width' => 1280,
                    'height' => 720
                )
            )
        );
        
        return $edit;
    }
    
    /**
     * Get dimensions from aspect ratio.
     *
     * @since    0.1.0
     * @param    string    $aspect_ratio    The aspect ratio (16:9, 1:1, 9:16).
     * @return   array                      The dimensions.
     */
    private function get_dimensions_from_aspect_ratio($aspect_ratio) {
        switch ($aspect_ratio) {
            case '1:1':
                return array(
                    'resolution' => 'sd', // Valid values: preview, mobile, sd, hd, 1080, 4k
                    'ratio' => '1:1'
                );
            case '9:16':
                return array(
                    'resolution' => 'hd', // Valid values: preview, mobile, sd, hd, 1080, 4k
                    'ratio' => '9:16'
                );
            case '16:9':
            default:
                return array(
                    'resolution' => 'hd', // Valid values: preview, mobile, sd, hd, 1080, 4k
                    'ratio' => '16:9'
                );
        }
    }
}
