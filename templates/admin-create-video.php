<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    
    <?php
    // Check if the API key is set
    $api_key = get_option('rvg_shotstack_api_key', '');
    $is_api_key_set = !empty($api_key);
    
    // Show a notice if the API key is not set
    if (!$is_api_key_set) {
        ?>
        <div class="notice notice-error">
            <p>
                <?php _e('You need to set up your Shotstack API key before you can create videos.', 'reviews-video-generator'); ?>
                <a href="<?php echo admin_url('admin.php?page=' . $this->plugin_name . '-settings'); ?>" class="button button-primary"><?php _e('Go to Settings', 'reviews-video-generator'); ?></a>
            </p>
        </div>
        <?php
        return; // Stop execution if API key is not set
    }
    
    // Get review ID from URL if available
    $review_id = isset($_GET['review_id']) ? intval($_GET['review_id']) : 0;
    
    // In a real implementation, we would fetch the review data from the database
    // For now, we'll use sample data
    $review = null;
    if ($review_id > 0) {
        // Sample reviews data
        $sample_reviews = array(
            1 => array(
                'id' => 1,
                'author_name' => 'John Smith',
                'rating' => 5,
                'review_text' => 'Absolutely amazing service! The staff went above and beyond to help me. Would definitely recommend to anyone looking for quality service.',
                'review_date' => '2025-02-28'
            ),
            2 => array(
                'id' => 2,
                'author_name' => 'Jane Doe',
                'rating' => 4,
                'review_text' => 'Great experience overall. The product exceeded my expectations and customer service was very helpful.',
                'review_date' => '2025-02-25'
            ),
            3 => array(
                'id' => 3,
                'author_name' => 'Mike Johnson',
                'rating' => 5,
                'review_text' => 'Top notch quality and service. I\'ve been a customer for years and they never disappoint.',
                'review_date' => '2025-02-20'
            )
        );
        
        if (isset($sample_reviews[$review_id])) {
            $review = $sample_reviews[$review_id];
        }
    }
    ?>
    
    <div class="rvg-create-video">
        <div class="rvg-create-video-header">
            <h2><?php _e('Create Video from Review', 'reviews-video-generator'); ?></h2>
            <a href="<?php echo admin_url('admin.php?page=' . $this->plugin_name . '-reviews'); ?>" class="button"><?php _e('Back to Reviews', 'reviews-video-generator'); ?></a>
        </div>
        
        <div class="notice notice-info">
            <p>
                <?php _e('Note: The Shotstack API requires videos to be hosted on publicly accessible URLs. If you\'re using local videos, the system will automatically use sample videos instead.', 'reviews-video-generator'); ?>
            </p>
        </div>
        
        <div class="rvg-create-video-container">
            <!-- Left Panel - Controls -->
            <div class="rvg-create-video-controls">
                <form id="rvg-create-video-form">
                    <div class="rvg-section">
                        <h3><?php _e('Review Content', 'reviews-video-generator'); ?></h3>
                        
                        <?php if ($review): ?>
                            <!-- If a review was selected, show its details -->
                            <div class="rvg-review-preview">
                                <div class="rvg-star-rating">
                                    <?php echo str_repeat('★', $review['rating']) . str_repeat('☆', 5 - $review['rating']); ?>
                                </div>
                                <p class="rvg-review-text"><?php echo esc_html($review['review_text']); ?></p>
                                <p class="rvg-review-author">- <?php echo esc_html($review['author_name']); ?></p>
                                
                                <input type="hidden" name="review_id" value="<?php echo esc_attr($review['id']); ?>">
                                <input type="hidden" name="author_name" value="<?php echo esc_attr($review['author_name']); ?>">
                                <input type="hidden" name="rating" value="<?php echo esc_attr($review['rating']); ?>">
                                <input type="hidden" name="review_text" value="<?php echo esc_attr($review['review_text']); ?>">
                            </div>
                            
                            <button type="button" id="rvg-edit-review-text" class="button"><?php _e('Edit Text', 'reviews-video-generator'); ?></button>
                            
                            <div id="rvg-edit-text-form" style="display: none; margin-top: 10px;">
                                <textarea name="edited_review_text" rows="4" style="width: 100%;"><?php echo esc_textarea($review['review_text']); ?></textarea>
                                <button type="button" id="rvg-save-edited-text" class="button button-small"><?php _e('Save', 'reviews-video-generator'); ?></button>
                                <button type="button" id="rvg-cancel-edit-text" class="button button-small"><?php _e('Cancel', 'reviews-video-generator'); ?></button>
                            </div>
                        <?php else: ?>
                            <!-- If no review was selected, show a dropdown to select one -->
                            <div class="rvg-form-field">
                                <label for="rvg-select-review"><?php _e('Select a Review', 'reviews-video-generator'); ?></label>
                                <select id="rvg-select-review" name="review_id" required>
                                    <option value=""><?php _e('-- Select a Review --', 'reviews-video-generator'); ?></option>
                                    <option value="1">John Smith - ★★★★★</option>
                                    <option value="2">Jane Doe - ★★★★☆</option>
                                    <option value="3">Mike Johnson - ★★★★★</option>
                                </select>
                            </div>
                            
                            <div id="rvg-selected-review-preview" style="display: none; margin-top: 10px;"></div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="rvg-section">
                        <h3><?php _e('Background Video', 'reviews-video-generator'); ?></h3>
                        <div class="rvg-background-videos">
                            <div class="rvg-background-video-grid">
                                <?php
                                // Get all background videos
                                $videos = $this->get_background_videos();
                                $first_video = !empty($videos) ? $videos[0]['url'] : '';
                                
                                foreach ($videos as $index => $video):
                                    $is_selected = $index === 0;
                                    $thumbnail = isset($video['thumbnail']) ? $video['thumbnail'] : 'https://shotstack-assets.s3.ap-southeast-2.amazonaws.com/thumbnails/placeholder.jpg';
                                ?>
                                    <div class="rvg-background-video-item <?php echo $is_selected ? 'selected' : ''; ?>" data-video="<?php echo esc_url($video['url']); ?>">
                                        <div class="rvg-background-video-thumbnail" style="background-image: url('<?php echo esc_url($thumbnail); ?>');"></div>
                                        <span><?php echo esc_html($video['title']); ?></span>
                                    </div>
                                <?php endforeach; ?>
                                
                                <div class="rvg-background-video-item rvg-upload-new-video">
                                    <div class="rvg-background-video-thumbnail rvg-upload-icon">
                                        <span class="dashicons dashicons-plus"></span>
                                    </div>
                                    <span><?php _e('Upload New', 'reviews-video-generator'); ?></span>
                                </div>
                            </div>
                            <input type="hidden" name="background_video" id="rvg-background-video" value="<?php echo esc_url($first_video); ?>">
                            <p class="description">
                                <?php _e('Select a background video or', 'reviews-video-generator'); ?>
                                <a href="<?php echo admin_url('admin.php?page=' . $this->plugin_name . '-background-videos'); ?>"><?php _e('manage your videos', 'reviews-video-generator'); ?></a>.
                            </p>
                        </div>
                    </div>
                    
                    <div class="rvg-section">
                        <h3><?php _e('Text Style', 'reviews-video-generator'); ?></h3>
                        
                        <div class="rvg-form-field">
                            <label for="rvg-font"><?php _e('Font', 'reviews-video-generator'); ?></label>
                            <select id="rvg-font" name="font">
                                <option value="Open Sans">Open Sans</option>
                                <option value="Roboto">Roboto</option>
                                <option value="Montserrat">Montserrat</option>
                                <option value="Lato">Lato</option>
                                <option value="Oswald">Oswald</option>
                            </select>
                        </div>
                        
                        <!-- Rating Text Style -->
                        <div class="rvg-text-style-section">
                            <h4><?php _e('Rating Text Style', 'reviews-video-generator'); ?></h4>
                            
                            <div class="rvg-form-field">
                                <label for="rvg-rating-font-size"><?php _e('Font Size (px)', 'reviews-video-generator'); ?></label>
                                <input type="number" id="rvg-rating-font-size" name="rating_font_size" min="10" max="100" value="24" class="regular-text">
                            </div>
                            
                            <div class="rvg-form-field">
                                <label for="rvg-rating-text-color"><?php _e('Text Color', 'reviews-video-generator'); ?></label>
                                <div class="rvg-color-options" data-target="rating">
                                    <div class="rvg-color-option selected" data-color="#FFD700" style="background-color: #FFD700; color: #000000;">Aa</div>
                                    <div class="rvg-color-option" data-color="#FFFFFF" style="background-color: #FFFFFF; color: #000000;">Aa</div>
                                    <div class="rvg-color-option" data-color="#000000" style="background-color: #000000; color: #FFFFFF;">Aa</div>
                                    <div class="rvg-color-option" data-color="#0073AA" style="background-color: #0073AA; color: #FFFFFF;">Aa</div>
                                </div>
                                <input type="hidden" name="rating_text_color" id="rvg-rating-text-color" value="#FFD700">
                            </div>
                            
                            <div class="rvg-form-field">
                                <label for="rvg-rating-position-x"><?php _e('X Position', 'reviews-video-generator'); ?></label>
                                <input type="range" id="rvg-rating-position-x" name="rating_position_x" min="-1" max="1" step="0.1" value="0" class="rvg-range">
                                <div class="rvg-range-value"><span id="rvg-rating-position-x-value">0</span></div>
                            </div>
                            
                            <div class="rvg-form-field">
                                <label for="rvg-rating-position-y"><?php _e('Y Position', 'reviews-video-generator'); ?></label>
                                <input type="range" id="rvg-rating-position-y" name="rating_position_y" min="-1" max="1" step="0.1" value="0.3" class="rvg-range">
                                <div class="rvg-range-value"><span id="rvg-rating-position-y-value">0.3</span></div>
                            </div>
                        </div>
                        
                        <!-- Review Text Style -->
                        <div class="rvg-text-style-section">
                            <h4><?php _e('Review Text Style', 'reviews-video-generator'); ?></h4>
                            
                            <div class="rvg-form-field">
                                <label for="rvg-review-font-size"><?php _e('Font Size (px)', 'reviews-video-generator'); ?></label>
                                <input type="number" id="rvg-review-font-size" name="review_font_size" min="10" max="100" value="24" class="regular-text">
                            </div>
                            
                            <div class="rvg-form-field">
                                <label for="rvg-review-text-color"><?php _e('Text Color', 'reviews-video-generator'); ?></label>
                                <div class="rvg-color-options" data-target="review">
                                    <div class="rvg-color-option" data-color="#FFD700" style="background-color: #FFD700; color: #000000;">Aa</div>
                                    <div class="rvg-color-option selected" data-color="#FFFFFF" style="background-color: #FFFFFF; color: #000000;">Aa</div>
                                    <div class="rvg-color-option" data-color="#000000" style="background-color: #000000; color: #FFFFFF;">Aa</div>
                                    <div class="rvg-color-option" data-color="#0073AA" style="background-color: #0073AA; color: #FFFFFF;">Aa</div>
                                </div>
                                <input type="hidden" name="review_text_color" id="rvg-review-text-color" value="#FFFFFF">
                            </div>
                            
                            <div class="rvg-form-field">
                                <label for="rvg-review-position-x"><?php _e('X Position', 'reviews-video-generator'); ?></label>
                                <input type="range" id="rvg-review-position-x" name="review_position_x" min="-1" max="1" step="0.1" value="0" class="rvg-range">
                                <div class="rvg-range-value"><span id="rvg-review-position-x-value">0</span></div>
                            </div>
                            
                            <div class="rvg-form-field">
                                <label for="rvg-review-position-y"><?php _e('Y Position', 'reviews-video-generator'); ?></label>
                                <input type="range" id="rvg-review-position-y" name="review_position_y" min="-1" max="1" step="0.1" value="0" class="rvg-range">
                                <div class="rvg-range-value"><span id="rvg-review-position-y-value">0</span></div>
                            </div>
                        </div>
                        
                        <!-- Reviewer Text Style -->
                        <div class="rvg-text-style-section">
                            <h4><?php _e('Reviewer Text Style', 'reviews-video-generator'); ?></h4>
                            
                            <div class="rvg-form-field">
                                <label for="rvg-reviewer-font-size"><?php _e('Font Size (px)', 'reviews-video-generator'); ?></label>
                                <input type="number" id="rvg-reviewer-font-size" name="reviewer_font_size" min="10" max="100" value="24" class="regular-text">
                            </div>
                            
                            <div class="rvg-form-field">
                                <label for="rvg-reviewer-text-color"><?php _e('Text Color', 'reviews-video-generator'); ?></label>
                                <div class="rvg-color-options" data-target="reviewer">
                                    <div class="rvg-color-option" data-color="#FFD700" style="background-color: #FFD700; color: #000000;">Aa</div>
                                    <div class="rvg-color-option selected" data-color="#FFFFFF" style="background-color: #FFFFFF; color: #000000;">Aa</div>
                                    <div class="rvg-color-option" data-color="#000000" style="background-color: #000000; color: #FFFFFF;">Aa</div>
                                    <div class="rvg-color-option" data-color="#0073AA" style="background-color: #0073AA; color: #FFFFFF;">Aa</div>
                                </div>
                                <input type="hidden" name="reviewer_text_color" id="rvg-reviewer-text-color" value="#FFFFFF">
                            </div>
                            
                            <div class="rvg-form-field">
                                <label for="rvg-reviewer-position-x"><?php _e('X Position', 'reviews-video-generator'); ?></label>
                                <input type="range" id="rvg-reviewer-position-x" name="reviewer_position_x" min="-1" max="1" step="0.1" value="0" class="rvg-range">
                                <div class="rvg-range-value"><span id="rvg-reviewer-position-x-value">0</span></div>
                            </div>
                            
                            <div class="rvg-form-field">
                                <label for="rvg-reviewer-position-y"><?php _e('Y Position', 'reviews-video-generator'); ?></label>
                                <input type="range" id="rvg-reviewer-position-y" name="reviewer_position_y" min="-1" max="1" step="0.1" value="-0.3" class="rvg-range">
                                <div class="rvg-range-value"><span id="rvg-reviewer-position-y-value">-0.3</span></div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="rvg-section">
                        <h3><?php _e('Video Format', 'reviews-video-generator'); ?></h3>
                        <div class="rvg-aspect-ratio-options">
                            <div class="rvg-aspect-ratio-option selected" data-ratio="16:9">
                                <div class="rvg-aspect-ratio-preview ratio-16-9"></div>
                                <span>16:9</span>
                            </div>
                            <div class="rvg-aspect-ratio-option" data-ratio="1:1">
                                <div class="rvg-aspect-ratio-preview ratio-1-1"></div>
                                <span>1:1</span>
                            </div>
                            <div class="rvg-aspect-ratio-option" data-ratio="9:16">
                                <div class="rvg-aspect-ratio-preview ratio-9-16"></div>
                                <span>9:16</span>
                            </div>
                        </div>
                        <input type="hidden" name="aspect_ratio" id="rvg-aspect-ratio" value="16:9">
                    </div>
                    
                    <div class="rvg-form-actions">
                        <button type="submit" id="rvg-generate-video" class="button button-primary button-large"><?php _e('Generate Video', 'reviews-video-generator'); ?></button>
                    </div>
                </form>
            </div>
            
            <!-- Right Panel - Preview -->
            <div class="rvg-create-video-preview">
                <div class="rvg-video-preview-container ratio-16-9">
                    <div class="rvg-video-preview-placeholder">
                        <div class="rvg-video-preview-content">
            <?php if ($review): ?>
                <div class="rvg-preview-rating">
                    <?php echo esc_html($review['rating']); ?>/5 Rating
                </div>
                <div class="rvg-preview-text">
                    "<?php echo esc_html($review['review_text']); ?>"
                </div>
                <div class="rvg-preview-author">
                    - <?php echo esc_html($review['author_name']); ?>
                </div>
            <?php else: ?>
                <div class="rvg-preview-rating">5/5 Rating</div>
                <div class="rvg-preview-text">
                    "<?php _e('Select a review to preview how it will look in the video.', 'reviews-video-generator'); ?>"
                </div>
                <div class="rvg-preview-author">
                    - <?php _e('Customer Name', 'reviews-video-generator'); ?>
                </div>
            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <p class="rvg-preview-note"><?php _e('Preview - This is a representation of how your video will look.', 'reviews-video-generator'); ?></p>
            </div>
        </div>
    </div>
    
    <!-- Processing Modal -->
    <div id="rvg-processing-modal" class="rvg-modal" style="display: none;">
        <div class="rvg-modal-content">
            <h2><?php _e('Processing Your Video', 'reviews-video-generator'); ?></h2>
            <div class="rvg-progress">
                <div class="rvg-progress-bar"></div>
            </div>
            <p class="rvg-progress-status"><?php _e('Initializing...', 'reviews-video-generator'); ?></p>
            <p class="rvg-progress-note"><?php _e('This may take a few minutes. Please don\'t close this window.', 'reviews-video-generator'); ?></p>
        </div>
    </div>
    
    <!-- Completed Modal -->
    <div id="rvg-completed-modal" class="rvg-modal" style="display: none;">
        <div class="rvg-modal-content">
            <span class="rvg-modal-close">&times;</span>
            <h2><?php _e('Your Video is Ready!', 'reviews-video-generator'); ?></h2>
            <div class="rvg-video-container">
                <video id="rvg-completed-video" controls>
                    <source src="" type="video/mp4">
                    <?php _e('Your browser does not support the video tag.', 'reviews-video-generator'); ?>
                </video>
            </div>
            <div class="rvg-modal-actions">
                <a href="#" id="rvg-download-video" class="button button-primary" download><?php _e('Download Video', 'reviews-video-generator'); ?></a>
                <a href="<?php echo admin_url('admin.php?page=' . $this->plugin_name . '-create-video'); ?>" class="button"><?php _e('Create Another', 'reviews-video-generator'); ?></a>
            </div>
        </div>
    </div>
</div>
