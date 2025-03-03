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
            
            // Log the font size in the edit object
            $font_sizes = [];
            if (isset($edit['timeline']) && isset($edit['timeline']['tracks'])) {
                foreach ($edit['timeline']['tracks'] as $track_index => $track) {
                    if (isset($track['clips'])) {
                        foreach ($track['clips'] as $clip_index => $clip) {
                            if (isset($clip['asset']) && isset($clip['asset']['type']) && $clip['asset']['type'] === 'text' && isset($clip['asset']['font']) && isset($clip['asset']['font']['size'])) {
                                $font_sizes[] = [
                                    'track_index' => $track_index,
                                    'clip_index' => $clip_index,
                                    'font_size' => $clip['asset']['font']['size'],
                                    'text' => $clip['asset']['text']
                                ];
                            }
                        }
                    }
                }
            }
            
            Reviews_Video_Generator_Debug::debug('Prepared video edit for Shotstack API', [
                'edit' => $edit,
                'font_sizes_in_edit' => $font_sizes
            ]);

            // Make the API request
            $json_body = json_encode($edit);
            
            // Log the actual JSON being sent to the API
            Reviews_Video_Generator_Debug::debug('JSON payload being sent to Shotstack API', [
                'json_body' => $json_body
            ]);
            
            $request_args = array(
                'headers' => array(
                    'Content-Type' => 'application/json',
                    'x-api-key' => $this->api_key
                ),
                'body' => $json_body,
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
     * Get text element positions and styling for the video.
     *
     * @since    0.1.0
     * @param    array    $video_data    The data for the video.
     * @param    array    $dimensions    The video dimensions.
     * @return   array                   Array of clip configurations for text elements.
     */
    private function get_text_element_configs($video_data, $dimensions) {
        // Extract relevant data
        $review_text = isset($video_data['review_text']) ? $video_data['review_text'] : '';
        $author_name = isset($video_data['author_name']) ? $video_data['author_name'] : '';
        $rating = isset($video_data['rating']) ? intval($video_data['rating']) : 5;
        $font = isset($video_data['font']) ? $video_data['font'] : 'Open Sans';
        
        // Extract individual text element settings
        // Rating text style
        $rating_font_size = isset($video_data['rating_font_size']) ? intval($video_data['rating_font_size']) : 30;
        $rating_text_color = isset($video_data['rating_text_color']) ? $video_data['rating_text_color'] : '#FFD700';
        $rating_position_x = isset($video_data['rating_position_x']) ? floatval($video_data['rating_position_x']) : 0;
        $rating_position_y = isset($video_data['rating_position_y']) ? floatval($video_data['rating_position_y']) : 0.3;
        
        // Review text style
        $review_font_size = isset($video_data['review_font_size']) ? intval($video_data['review_font_size']) : 30;
        $review_text_color = isset($video_data['review_text_color']) ? $video_data['review_text_color'] : '#FFFFFF';
        $review_position_x = isset($video_data['review_position_x']) ? floatval($video_data['review_position_x']) : 0;
        $review_position_y = isset($video_data['review_position_y']) ? floatval($video_data['review_position_y']) : 0;
        
        // Reviewer text style
        $reviewer_font_size = isset($video_data['reviewer_font_size']) ? intval($video_data['reviewer_font_size']) : 30;
        $reviewer_text_color = isset($video_data['reviewer_text_color']) ? $video_data['reviewer_text_color'] : '#FFFFFF';
        $reviewer_position_x = isset($video_data['reviewer_position_x']) ? floatval($video_data['reviewer_position_x']) : 0;
        $reviewer_position_y = isset($video_data['reviewer_position_y']) ? floatval($video_data['reviewer_position_y']) : -0.3;
        
        // For backward compatibility
        if (isset($video_data['text_color']) && !isset($video_data['review_text_color'])) {
            $review_text_color = $video_data['text_color'];
            $reviewer_text_color = $video_data['text_color'];
        }
        
        if (isset($video_data['font_size']) && !isset($video_data['rating_font_size'])) {
            $rating_font_size = intval($video_data['font_size']);
            $review_font_size = intval($video_data['font_size']);
            $reviewer_font_size = intval($video_data['font_size']);
        }
        
        // Log the font sizes for debugging
        Reviews_Video_Generator_Debug::debug('Font sizes in get_text_element_configs', [
            'rating_font_size' => $rating_font_size,
            'review_font_size' => $review_font_size,
            'reviewer_font_size' => $reviewer_font_size,
            'video_data' => $video_data
        ]);
        
        // Calculate proportional widths based on video dimensions
        $video_width = $dimensions['width'];
        $video_height = $dimensions['height'];
        
        // Set text widths as a percentage of video width with margins
        $review_text_width = intval($video_width * 0.8); // 80% of video width
        $star_rating_width = intval($video_width * 0.6); // 60% of video width
        $author_name_width = intval($video_width * 0.6); // 60% of video width
        
        // Prepare the star rating text
        $star_rating = $rating . '/5 Rating';
        
        // Create clip configurations for each text element
        $clips = array(
            // Star rating
            array(
                'asset' => array(
                    'type' => 'text',
                    'text' => $star_rating,
                    'font' => array(
                        'family' => 'Arial',
                        'color' => $rating_text_color,
                        'size' => $rating_font_size
                    ),
                    'alignment' => array(
                        'horizontal' => 'center'
                    ),
                    'width' => $star_rating_width,
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
                    'x' => $rating_position_x,
                    'y' => $rating_position_y
                ),
                'position' => 'center',
                'effect' => 'zoomIn'
            ),
            
            // Review text
            array(
                'asset' => array(
                    'type' => 'text',
                    'text' => '"' . $review_text . '"',
                    'font' => array(
                        'family' => 'Arial',
                        'color' => $review_text_color,
                        'size' => $review_font_size
                    ),
                    'alignment' => array(
                        'horizontal' => 'center'
                    ),
                    'width' => $review_text_width,
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
                    'x' => $review_position_x,
                    'y' => $review_position_y
                ),
                'position' => 'center',
                'effect' => 'zoomIn'
            ),
            
            // Author name
            array(
                'asset' => array(
                    'type' => 'text',
                    'text' => '- ' . $author_name,
                    'font' => array(
                        'family' => 'Arial',
                        'color' => $reviewer_text_color,
                        'size' => $reviewer_font_size
                    ),
                    'alignment' => array(
                        'horizontal' => 'center'
                    ),
                    'width' => $author_name_width,
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
                    'x' => $reviewer_position_x,
                    'y' => $reviewer_position_y
                ),
                'position' => 'center',
                'effect' => 'zoomIn'
            )
        );
        
        // Log the font sizes in the clips array
        $font_sizes_in_clips = [];
        foreach ($clips as $index => $clip) {
            if (isset($clip['asset']) && isset($clip['asset']['font']) && isset($clip['asset']['font']['size'])) {
                $font_sizes_in_clips[] = [
                    'clip_index' => $index,
                    'text' => $clip['asset']['text'],
                    'font_size' => $clip['asset']['font']['size']
                ];
            }
        }
        
        Reviews_Video_Generator_Debug::debug('Font sizes in clips before returning', [
            'font_sizes_in_clips' => $font_sizes_in_clips
        ]);
        
        // Log the entire clips array for debugging
        Reviews_Video_Generator_Debug::debug('Final clips array in get_text_element_configs', [
            'clips' => $clips
        ]);
        
        return $clips;
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
        $font_size = isset($video_data['font_size']) ? intval($video_data['font_size']) : 30;
        $aspect_ratio = isset($video_data['aspect_ratio']) ? $video_data['aspect_ratio'] : '16:9';
        
        // Log the font size for debugging
        Reviews_Video_Generator_Debug::debug('Font size in prepare_video_edit - INITIAL', [
            'font_size' => $font_size,
            'video_data_font_size' => isset($video_data['font_size']) ? $video_data['font_size'] : 'not set',
            'video_data' => $video_data,
            'raw_font_size_type' => isset($video_data['font_size']) ? gettype($video_data['font_size']) : 'not set',
            'raw_font_size_value' => isset($video_data['font_size']) ? $video_data['font_size'] : 'not set'
        ]);
        
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
        
        // Prepare the star rating text - use a numeric representation instead of Unicode stars
        $star_rating = $rating . '/5 Rating';
        
        // Set output dimensions based on aspect ratio
        $output_dimensions = array();
        switch ($aspect_ratio) {
            case '1:1':
                $output_dimensions = array(
                    'width' => 720,
                    'height' => 720
                );
                break;
            case '9:16':
                $output_dimensions = array(
                    'width' => 720,
                    'height' => 1280
                );
                break;
            case '16:9':
            default:
                $output_dimensions = array(
                    'width' => 1280,
                    'height' => 720
                );
                break;
        }
        
        // Get text element configurations
        $text_clips = $this->get_text_element_configs($video_data, $output_dimensions);
        
        // Log the text clips after getting them from get_text_element_configs
        $font_sizes_after_get_configs = [];
        foreach ($text_clips as $index => $clip) {
            if (isset($clip['asset']) && isset($clip['asset']['font']) && isset($clip['asset']['font']['size'])) {
                $font_sizes_after_get_configs[] = [
                    'clip_index' => $index,
                    'text' => $clip['asset']['text'],
                    'font_size' => $clip['asset']['font']['size']
                ];
            }
        }
        
        Reviews_Video_Generator_Debug::debug('Font sizes after get_text_element_configs', [
            'font_sizes_after_get_configs' => $font_sizes_after_get_configs
        ]);
        
        // Create tracks for each text element
        $text_tracks = array();
        foreach ($text_clips as $clip) {
            $text_tracks[] = array(
                'clips' => array($clip)
            );
        }
        
        // Log the text tracks after creating them
        $font_sizes_in_tracks = [];
        foreach ($text_tracks as $track_index => $track) {
            foreach ($track['clips'] as $clip_index => $clip) {
                if (isset($clip['asset']) && isset($clip['asset']['font']) && isset($clip['asset']['font']['size'])) {
                    $font_sizes_in_tracks[] = [
                        'track_index' => $track_index,
                        'clip_index' => $clip_index,
                        'text' => $clip['asset']['text'],
                        'font_size' => $clip['asset']['font']['size']
                    ];
                }
            }
        }
        
        Reviews_Video_Generator_Debug::debug('Font sizes in tracks after creation', [
            'font_sizes_in_tracks' => $font_sizes_in_tracks
        ]);
        
        // Create the edit object using the structure from the working example
        $edit = array(
            'timeline' => array(
                'fonts' => array(
                    array(
                        'src' => 'https://templates.shotstack.io/hello-world-title-video/f60f6f75-38b6-4aa8-8db0-28741aa34ec8/MovieLetters.ttf'
                    )
                ),
                'background' => '#000000',
                'tracks' => array_merge(
                    $text_tracks,
                    array(
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
                )
            ),
            'output' => array(
                'format' => 'mp4',
                'fps' => 30,
                'size' => $output_dimensions
            )
        );
        
        // Log the final edit object before returning
        $font_sizes_in_final_edit = [];
        if (isset($edit['timeline']) && isset($edit['timeline']['tracks'])) {
            foreach ($edit['timeline']['tracks'] as $track_index => $track) {
                if (isset($track['clips'])) {
                    foreach ($track['clips'] as $clip_index => $clip) {
                        if (isset($clip['asset']) && isset($clip['asset']['type']) && $clip['asset']['type'] === 'text' && isset($clip['asset']['font']) && isset($clip['asset']['font']['size'])) {
                            $font_sizes_in_final_edit[] = [
                                'track_index' => $track_index,
                                'clip_index' => $clip_index,
                                'text' => $clip['asset']['text'],
                                'font_size' => $clip['asset']['font']['size']
                            ];
                        }
                    }
                }
            }
        }
        
        Reviews_Video_Generator_Debug::debug('Font sizes in final edit object', [
            'font_sizes_in_final_edit' => $font_sizes_in_final_edit
        ]);
        
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
