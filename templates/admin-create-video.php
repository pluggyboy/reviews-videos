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
                        
                        <div class="rvg-form-field">
                            <label for="rvg-text-color"><?php _e('Text Color', 'reviews-video-generator'); ?></label>
                            <div class="rvg-color-options">
                                <div class="rvg-color-option selected" data-color="#FFFFFF" style="background-color: #FFFFFF; color: #000000;">Aa</div>
                                <div class="rvg-color-option" data-color="#000000" style="background-color: #000000; color: #FFFFFF;">Aa</div>
                                <div class="rvg-color-option" data-color="#0073AA" style="background-color: #0073AA; color: #FFFFFF;">Aa</div>
                                <div class="rvg-color-option" data-color="#F5C518" style="background-color: #F5C518; color: #000000;">Aa</div>
                            </div>
                            <input type="hidden" name="text_color" id="rvg-text-color" value="#FFFFFF">
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
                                <div class="rvg-preview-stars">
                                    <?php echo str_repeat('★', $review['rating']) . str_repeat('☆', 5 - $review['rating']); ?>
                                </div>
                                <div class="rvg-preview-text">
                                    "<?php echo esc_html($review['review_text']); ?>"
                                </div>
                                <div class="rvg-preview-author">
                                    - <?php echo esc_html($review['author_name']); ?>
                                </div>
                            <?php else: ?>
                                <div class="rvg-preview-stars">★★★★★</div>
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

<style>
    .rvg-create-video {
        margin-top: 20px;
    }
    
    .rvg-create-video-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
    }
    
    .rvg-create-video-container {
        display: flex;
        gap: 30px;
    }
    
    .rvg-create-video-controls {
        flex: 1;
        background: #fff;
        padding: 20px;
        border-radius: 5px;
        border: 1px solid #ddd;
    }
    
    .rvg-create-video-preview {
        flex: 1;
        display: flex;
        flex-direction: column;
        align-items: center;
    }
    
    .rvg-section {
        margin-bottom: 25px;
        padding-bottom: 20px;
        border-bottom: 1px solid #eee;
    }
    
    .rvg-section h3 {
        margin-top: 0;
        margin-bottom: 15px;
    }
    
    .rvg-form-field {
        margin-bottom: 15px;
    }
    
    .rvg-form-field label {
        display: block;
        margin-bottom: 5px;
        font-weight: 600;
    }
    
    .rvg-form-field select,
    .rvg-form-field input {
        width: 100%;
    }
    
    .rvg-review-preview {
        background: #f9f9f9;
        padding: 15px;
        border-radius: 5px;
        margin-bottom: 10px;
    }
    
    .rvg-star-rating {
        color: #FFD700;
        margin-bottom: 5px;
    }
    
    .rvg-review-text {
        margin-bottom: 10px;
    }
    
    .rvg-review-author {
        font-style: italic;
        text-align: right;
    }
    
    .rvg-background-video-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 10px;
        margin-bottom: 10px;
    }
    
    .rvg-background-video-item {
        cursor: pointer;
        text-align: center;
        border: 2px solid transparent;
        border-radius: 5px;
        padding: 5px;
    }
    
    .rvg-background-video-item.selected {
        border-color: #0073aa;
        background: #f0f6fc;
    }
    
    .rvg-background-video-thumbnail {
        height: 60px;
        background-size: cover;
        background-position: center;
        border-radius: 3px;
        margin-bottom: 5px;
    }
    
    .rvg-upload-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        background-color: #f0f0f0;
    }
    
    .rvg-upload-icon .dashicons {
        font-size: 24px;
        width: 24px;
        height: 24px;
        color: #0073aa;
    }
    
    .rvg-upload-new-video {
        border: 2px dashed #ddd;
    }
    
    .rvg-upload-new-video:hover {
        border-color: #0073aa;
        background-color: #f0f6fc;
    }
    
    .rvg-color-options {
        display: flex;
        gap: 10px;
        margin-top: 5px;
    }
    
    .rvg-color-option {
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 5px;
        cursor: pointer;
        border: 2px solid transparent;
        font-weight: bold;
    }
    
    .rvg-color-option.selected {
        border-color: #0073aa;
    }
    
    .rvg-aspect-ratio-options {
        display: flex;
        gap: 15px;
    }
    
    .rvg-aspect-ratio-option {
        text-align: center;
        cursor: pointer;
    }
    
    .rvg-aspect-ratio-option.selected .rvg-aspect-ratio-preview {
        border-color: #0073aa;
        background-color: #f0f6fc;
    }
    
    .rvg-aspect-ratio-preview {
        width: 60px;
        height: 60px;
        border: 2px solid #ddd;
        margin-bottom: 5px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .ratio-16-9 {
        position: relative;
        width: 100%;
    }
    
    .ratio-16-9::before {
        content: "";
        display: block;
        padding-top: 56.25%; /* 9/16 = 0.5625 */
    }
    
    .ratio-1-1 {
        position: relative;
        width: 100%;
    }
    
    .ratio-1-1::before {
        content: "";
        display: block;
        padding-top: 100%; /* 1/1 = 1 */
    }
    
    .ratio-9-16 {
        position: relative;
        width: 100%;
    }
    
    .ratio-9-16::before {
        content: "";
        display: block;
        padding-top: 177.78%; /* 16/9 = 1.7778 */
    }
    
    .rvg-aspect-ratio-preview.ratio-16-9 {
        width: 60px;
        height: 34px;
    }
    
    .rvg-aspect-ratio-preview.ratio-1-1 {
        width: 45px;
        height: 45px;
    }
    
    .rvg-aspect-ratio-preview.ratio-9-16 {
        width: 34px;
        height: 60px;
    }
    
    .rvg-video-preview-container {
        width: 100%;
        max-width: 500px;
        position: relative;
        margin-bottom: 10px;
        background-color: #000;
        border-radius: 5px;
        overflow: hidden;
    }
    
    .rvg-video-preview-placeholder {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-image: url('https://shotstack-assets.s3.ap-southeast-2.amazonaws.com/thumbnails/cafe-owner.jpg');
        background-size: cover;
        background-position: center;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .rvg-video-preview-content {
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        padding: 20px;
        color: #fff;
        text-align: center;
    }
    
    .rvg-preview-stars {
        color: #FFD700;
        font-size: 24px;
        margin-bottom: 15px;
    }
    
    .rvg-preview-text {
        font-size: 20px;
        font-weight: 600;
        margin-bottom: 15px;
        line-height: 1.4;
    }
    
    .rvg-preview-author {
        font-style: italic;
        align-self: flex-end;
        margin-right: 20px;
    }
    
    .rvg-preview-note {
        color: #666;
        font-style: italic;
        text-align: center;
    }
    
    .rvg-form-actions {
        margin-top: 30px;
        text-align: center;
    }
    
    /* Modal Styles */
    .rvg-modal {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.7);
        z-index: 9999;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .rvg-modal-content {
        background-color: #fff;
        padding: 30px;
        border-radius: 5px;
        max-width: 600px;
        width: 90%;
        text-align: center;
    }
    
    .rvg-progress {
        height: 10px;
        background-color: #f0f0f0;
        border-radius: 5px;
        margin: 20px 0;
        overflow: hidden;
    }
    
    .rvg-progress-bar {
        height: 100%;
        background-color: #0073aa;
        width: 0;
        transition: width 0.3s;
    }
    
    .rvg-progress-status {
        font-weight: 600;
    }
    
    .rvg-progress-note {
        color: #666;
        margin-top: 20px;
    }
    
    .rvg-video-container {
        margin: 20px 0;
        max-height: 500px;
        overflow: hidden;
    }
    
    .rvg-video-container video {
        width: 100%;
        max-height: 500px;
        border-radius: 5px;
        object-fit: contain;
    }
    
    .rvg-modal-actions {
        display: flex;
        justify-content: center;
        gap: 10px;
        margin-top: 20px;
    }
    
    @media (max-width: 782px) {
        .rvg-create-video-container {
            flex-direction: column;
        }
        
        .rvg-background-video-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }
</style>

<script>
jQuery(document).ready(function($) {
    console.log('Create Video page script loaded');
    
    // Handle "Upload New" button click
    $(document).on('click', '.rvg-upload-new-video', function(e) {
        e.preventDefault();
        e.stopPropagation();
        console.log('Upload New button clicked');
        
        // Redirect to the background videos page
        window.location.href = '<?php echo admin_url('admin.php?page=' . $this->plugin_name . '-background-videos'); ?>';
    });
    
    // Handle background video selection
    $(document).on('click', '.rvg-background-video-item:not(.rvg-upload-new-video)', function() {
        console.log('Background video selected:', $(this).find('span').text());
        
        $('.rvg-background-video-item').removeClass('selected');
        $(this).addClass('selected');
        
        const videoUrl = $(this).data('video');
        console.log('Selected video URL:', videoUrl);
        $('#rvg-background-video').val(videoUrl);
        
        // Update preview background
        const thumbnailUrl = $(this).find('.rvg-background-video-thumbnail').css('background-image');
        $('.rvg-video-preview-placeholder').css('background-image', thumbnailUrl);
    });
    
    // Handle text color selection
    $('.rvg-color-option').on('click', function() {
        $('.rvg-color-option').removeClass('selected');
        $(this).addClass('selected');
        
        const color = $(this).data('color');
        $('#rvg-text-color').val(color);
        
        // Update preview text color
        $('.rvg-preview-text, .rvg-preview-author').css('color', color);
    });
    
    // Handle aspect ratio selection
    $('.rvg-aspect-ratio-option').on('click', function() {
        $('.rvg-aspect-ratio-option').removeClass('selected');
        $(this).addClass('selected');
        
        const ratio = $(this).data('ratio');
        $('#rvg-aspect-ratio').val(ratio);
        
        // Update preview aspect ratio
        $('.rvg-video-preview-container').removeClass('ratio-16-9 ratio-1-1 ratio-9-16');
        
        if (ratio === '16:9') {
            $('.rvg-video-preview-container').addClass('ratio-16-9');
        } else if (ratio === '1:1') {
            $('.rvg-video-preview-container').addClass('ratio-1-1');
        } else if (ratio === '9:16') {
            $('.rvg-video-preview-container').addClass('ratio-9-16');
        }
    });
    
    // Handle font selection
    $('#rvg-font').on('change', function() {
        const font = $(this).val();
        // In a real implementation, we would update the preview font
    });
    
    // Handle review selection (when no review was pre-selected)
    $('#rvg-select-review').on('change', function() {
        const reviewId = $(this).val();
        
        if (reviewId) {
            // In a real implementation, we would fetch the review data from the server
            // For now, we'll use sample data
            const sampleReviews = {
                '1': {
                    id: 1,
                    author_name: 'John Smith',
                    rating: 5,
                    review_text: 'Absolutely amazing service! The staff went above and beyond to help me. Would definitely recommend to anyone looking for quality service.',
                    review_date: '2025-02-28'
                },
                '2': {
                    id: 2,
                    author_name: 'Jane Doe',
                    rating: 4,
                    review_text: 'Great experience overall. The product exceeded my expectations and customer service was very helpful.',
                    review_date: '2025-02-25'
                },
                '3': {
                    id: 3,
                    author_name: 'Mike Johnson',
                    rating: 5,
                    review_text: 'Top notch quality and service. I\'ve been a customer for years and they never disappoint.',
                    review_date: '2025-02-20'
                }
            };
            
            const review = sampleReviews[reviewId];
            
            if (review) {
                const stars = '★'.repeat(review.rating) + '☆'.repeat(5 - review.rating);
                
                // Update the preview
                $('#rvg-selected-review-preview').html(`
                    <div class="rvg-review-preview">
                        <div class="rvg-star-rating">${stars}</div>
                        <p class="rvg-review-text">${review.review_text}</p>
                        <p class="rvg-review-author">- ${review.author_name}</p>
                    </div>
                `).show();
                
                // Update the video preview
                $('.rvg-preview-stars').html(stars);
                $('.rvg-preview-text').text(`"${review.review_text}"`);
                $('.rvg-preview-author').text(`- ${review.author_name}`);
                
                // Add hidden inputs for form submission
                $('#rvg-create-video-form').append(`
                    <input type="hidden" name="author_name" value="${review.author_name}">
                    <input type="hidden" name="rating" value="${review.rating}">
                    <input type="hidden" name="review_text" value="${review.review_text}">
                `);
            }
        }
    });
    
    // Handle edit text button
    $('#rvg-edit-review-text').on('click', function() {
        $('#rvg-edit-text-form').slideDown();
    });
    
    $('#rvg-cancel-edit-text').on('click', function() {
        $('#rvg-edit-text-form').slideUp();
    });
    
    $('#rvg-save-edited-text').on('click', function() {
        const editedText = $('textarea[name="edited_review_text"]').val();
        
        // Update the displayed text
        $('.rvg-review-text').text(editedText);
        $('.rvg-preview-text').text(`"${editedText}"`);
        
        // Update the hidden input
        $('input[name="review_text"]').val(editedText);
        
        $('#rvg-edit-text-form').slideUp();
    });
    
    // Handle form submission
    $('#rvg-create-video-form').on('submit', function(e) {
        e.preventDefault();
        
        // Validate form
        const reviewId = $('select[name="review_id"]').val() || $('input[name="review_id"]').val();
        const reviewText = $('input[name="review_text"]').val();
        const backgroundVideo = $('#rvg-background-video').val();
        
        if (!reviewId || !reviewText || !backgroundVideo) {
            alert('Please complete all required fields.');
            return;
        }
        
        // Get form data
        const formData = {
            review_id: reviewId,
            author_name: $('input[name="author_name"]').val(),
            rating: $('input[name="rating"]').val(),
            review_text: reviewText,
            background_video: backgroundVideo,
            text_color: $('#rvg-text-color').val(),
            font: $('#rvg-font').val(),
            aspect_ratio: $('#rvg-aspect-ratio').val()
        };
        
        // Check if the background video URL is local
        if (isLocalUrl(formData.background_video)) {
            console.warn('Local video URL detected:', formData.background_video);
            
            // Show a warning message
            const confirmMessage = 'You are using a local video URL which may not be accessible by the Shotstack API. The system will use a sample video instead. Continue?';
            if (!confirm(confirmMessage)) {
                return;
            }
        }
        
        // Log the form data for debugging
        console.log('Form data for video creation:', formData);
        console.log('Background video URL being sent:', formData.background_video);
        
        // Send AJAX request to create video
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'rvg_create_video',
                nonce: '<?php echo wp_create_nonce('rvg_nonce'); ?>',
                review_id: formData.review_id,
                author_name: formData.author_name,
                rating: formData.rating,
                review_text: formData.review_text,
                background_video: formData.background_video,
                text_color: formData.text_color,
                font: formData.font,
                aspect_ratio: formData.aspect_ratio
            },
            beforeSend: function() {
                // Show processing modal
                $('#rvg-processing-modal').show();
                $('.rvg-progress-bar').css('width', '0%');
                $('.rvg-progress-status').text('<?php _e('Please wait...', 'reviews-video-generator'); ?>');
            },
            success: function(response) {
                console.log('Create video response:', response);
                
                if (response.success) {
                    // Start polling for video status
                    pollVideoStatus(response.data.render_id);
                } else {
                    // Show error message
                    handleApiError(response.data.message || '<?php _e('Error creating video. Please try again.', 'reviews-video-generator'); ?>');
                    $('#rvg-processing-modal').hide();
                }
            },
            error: function(xhr, status, error) {
                console.error('Error creating video:', error);
                alert('<?php _e('Error creating video. Please try again.', 'reviews-video-generator'); ?>');
                $('#rvg-processing-modal').hide();
            }
        });
    });
    
    /**
     * Check if a URL is a local URL
     */
    function isLocalUrl(url) {
        const localPatterns = [
            /^https?:\/\/localhost/,
            /^https?:\/\/127\.0\.0\.1/,
            /^https?:\/\/192\.168\./,
            /^https?:\/\/10\./,
            /^https?:\/\/172\.(1[6-9]|2[0-9]|3[0-1])\./
        ];
        
        return localPatterns.some(pattern => pattern.test(url));
    }
    
    /**
     * Handle API error with user-friendly message
     */
    function handleApiError(errorMessage) {
        console.error('API Error:', errorMessage);
        
        // Check for common error patterns and provide more helpful messages
        if (errorMessage.includes('style must be a string')) {
            errorMessage = '<?php _e('API Error: The style format is incorrect. Please contact the plugin developer.', 'reviews-video-generator'); ?>';
        } else if (errorMessage.includes('fails to match the required pattern')) {
            errorMessage = '<?php _e('API Error: The video URL is not in a format that Shotstack can access. Please use a publicly accessible video URL or one of the sample videos.', 'reviews-video-generator'); ?>';
        } else if (errorMessage.includes('API key')) {
            errorMessage = '<?php _e('API Error: There is an issue with your Shotstack API key. Please check your settings.', 'reviews-video-generator'); ?>';
        }
        
        // Show the error message
        alert(errorMessage);
    }
    
    /**
     * Poll for video status
     */
    function pollVideoStatus(renderId) {
        console.log('Polling video status for render ID:', renderId);
        
        // Set initial progress
        $('.rvg-progress-bar').css('width', '10%');
        $('.rvg-progress-status').text('<?php _e('Preparing video assets...', 'reviews-video-generator'); ?>');
        
        // Poll for status every 5 seconds
        const statusInterval = setInterval(function() {
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'rvg_get_video_status',
                    nonce: '<?php echo wp_create_nonce('rvg_nonce'); ?>',
                    render_id: renderId
                },
                success: function(response) {
                    console.log('Video status response:', response);
                    
                    if (response.success) {
                        // Update progress bar
                        $('.rvg-progress-bar').css('width', response.data.progress + '%');
                        
                        // Update status text based on status
                        switch (response.data.status) {
                            case 'queued':
                                $('.rvg-progress-status').text('<?php _e('Preparing video assets...', 'reviews-video-generator'); ?>');
                                break;
                            case 'fetching':
                                $('.rvg-progress-status').text('<?php _e('Preparing video assets...', 'reviews-video-generator'); ?>');
                                break;
                            case 'rendering':
                                $('.rvg-progress-status').text('<?php _e('Rendering video...', 'reviews-video-generator'); ?>');
                                break;
                            case 'saving':
                                $('.rvg-progress-status').text('<?php _e('Finalizing video...', 'reviews-video-generator'); ?>');
                                break;
                            case 'done':
                                $('.rvg-progress-status').text('<?php _e('Video ready!', 'reviews-video-generator'); ?>');
                                
                                // Clear the interval
                                clearInterval(statusInterval);
                                
                                // Hide processing modal and show completed modal
                                setTimeout(function() {
                                    $('#rvg-processing-modal').hide();
                                    
                                    // Set the video source
                                    $('#rvg-completed-video').attr('src', response.data.url);
                                    $('#rvg-download-video').attr('href', response.data.url);
                                    
                                    // Show completed modal
                                    $('#rvg-completed-modal').show();
                                    
                                    // Load the video
                                    $('#rvg-completed-video')[0].load();
                                }, 500);
                                break;
                            case 'failed':
                                $('.rvg-progress-status').text(response.data.message || '<?php _e('Error creating video', 'reviews-video-generator'); ?>');
                                
                                // Clear the interval
                                clearInterval(statusInterval);
                                
                                // Show error message
                                setTimeout(function() {
                                    $('#rvg-processing-modal').hide();
                                    alert(response.data.message || '<?php _e('Error creating video. Please try again.', 'reviews-video-generator'); ?>');
                                }, 500);
                                break;
                        }
                    } else {
                        // Show error message
                        clearInterval(statusInterval);
                        $('#rvg-processing-modal').hide();
                        alert(response.data.message || '<?php _e('Error getting video status. Please try again.', 'reviews-video-generator'); ?>');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error getting video status:', error);
                    clearInterval(statusInterval);
                    $('#rvg-processing-modal').hide();
                    alert('<?php _e('Error getting video status. Please try again.', 'reviews-video-generator'); ?>');
                }
            });
        }, 5000); // Poll every 5 seconds
    }
});
