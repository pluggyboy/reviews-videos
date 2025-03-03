<?php
/**
 * Admin Videos Template
 *
 * @since      0.1.0
 */
?>
<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    
    <?php
    // Check if the API key is set
    $api_key = get_option('rvg_shotstack_api_key', '');
    $is_api_key_set = !empty($api_key);
    
    // Show a notice if the API key is not set
    if (!$is_api_key_set) {
        ?>
        <div class="notice notice-warning">
            <p>
                <?php _e('Please set up your Shotstack API key in the settings to enable video generation.', 'reviews-video-generator'); ?>
                <a href="<?php echo admin_url('admin.php?page=' . $this->plugin_name . '-settings'); ?>" class="button button-primary"><?php _e('Go to Settings', 'reviews-video-generator'); ?></a>
            </p>
        </div>
        <?php
    }
    ?>
    
    <div class="rvg-videos">
        <div class="rvg-videos-header">
            <h2><?php _e('Your Generated Videos', 'reviews-video-generator'); ?></h2>
            <a href="<?php echo admin_url('admin.php?page=' . $this->plugin_name . '-create-video'); ?>" class="button button-primary"><?php _e('Create New Video', 'reviews-video-generator'); ?></a>
        </div>
        
        <div class="rvg-videos-filters">
            <select id="rvg-filter-aspect-ratio">
                <option value=""><?php _e('All Formats', 'reviews-video-generator'); ?></option>
                <option value="16:9"><?php _e('Landscape (16:9)', 'reviews-video-generator'); ?></option>
                <option value="1:1"><?php _e('Square (1:1)', 'reviews-video-generator'); ?></option>
                <option value="9:16"><?php _e('Portrait (9:16)', 'reviews-video-generator'); ?></option>
            </select>
            
            <input type="text" id="rvg-search-videos" placeholder="<?php _e('Search videos...', 'reviews-video-generator'); ?>">
        </div>
        
        <div class="rvg-videos-grid">
            <?php
            // Get videos from the database
            $videos = get_option('rvg_videos', array());
            
            // Sort videos by creation date (newest first)
            usort($videos, function($a, $b) {
                return strtotime($b['created']) - strtotime($a['created']);
            });
            
            if (empty($videos)) {
                ?>
                <div class="rvg-no-videos">
                    <p><?php _e('No videos found. Create your first video!', 'reviews-video-generator'); ?></p>
                    <a href="<?php echo admin_url('admin.php?page=' . $this->plugin_name . '-create-video'); ?>" class="button button-primary"><?php _e('Create Video', 'reviews-video-generator'); ?></a>
                </div>
                <?php
            } else {
                foreach ($videos as $video) {
                    // Skip videos that don't have a URL (still processing or failed)
                    if (empty($video['url'])) {
                        continue;
                    }
                    
                    $aspect_ratio_class = 'ratio-' . str_replace(':', '-', $video['aspect_ratio']);
                    $created_date = date('M j, Y', strtotime($video['created']));
                    $title = !empty($video['author_name']) ? $video['author_name'] . ' Review' : 'Review Video';
                    
                    // Generate a thumbnail from the video URL
                    $thumbnail = 'https://shotstack-assets.s3.ap-southeast-2.amazonaws.com/thumbnails/cafe-owner.jpg';
                    if (strpos($video['url'], 'shotstack-api') !== false) {
                        // If it's a Shotstack URL, we can try to get the thumbnail
                        $thumbnail = str_replace('.mp4', '.jpg', $video['url']);
                    }
                    ?>
                    <div class="rvg-video-card" data-id="<?php echo esc_attr($video['id']); ?>" data-aspect-ratio="<?php echo esc_attr($video['aspect_ratio']); ?>">
                        <div class="rvg-video-thumbnail <?php echo esc_attr($aspect_ratio_class); ?>">
                            <img src="<?php echo esc_url($thumbnail); ?>" alt="<?php echo esc_attr($title); ?>">
                            <div class="rvg-video-play" data-video="<?php echo esc_url($video['url']); ?>">
                                <span class="dashicons dashicons-controls-play"></span>
                            </div>
                        </div>
                        <div class="rvg-video-info">
                            <h3><?php echo esc_html($title); ?></h3>
                            <div class="rvg-video-meta">
                                <span class="rvg-video-date"><?php echo esc_html($created_date); ?></span>
                                <span class="rvg-video-format"><?php echo esc_html($video['aspect_ratio']); ?></span>
                            </div>
                            <p class="rvg-video-review">"<?php echo esc_html($video['review_text']); ?>"</p>
                            <p class="rvg-video-author">- <?php echo esc_html($video['author_name']); ?></p>
                        </div>
                        <div class="rvg-video-actions">
                            <a href="<?php echo esc_url($video['url']); ?>" class="button" download><?php _e('Download', 'reviews-video-generator'); ?></a>
                            <button class="button rvg-delete-video" data-id="<?php echo esc_attr($video['id']); ?>"><?php _e('Delete', 'reviews-video-generator'); ?></button>
                        </div>
                    </div>
                    <?php
                }
            }
            ?>
        </div>
    </div>
    
    <!-- Video Preview Modal -->
    <div id="rvg-video-preview-modal" class="rvg-modal" style="display: none;">
        <div class="rvg-modal-content">
            <div class="rvg-modal-header">
                <h2><?php _e('Video Preview', 'reviews-video-generator'); ?></h2>
                <button class="rvg-close-modal">&times;</button>
            </div>
            <div class="rvg-video-container">
                <video id="rvg-preview-video" controls>
                    <source src="" type="video/mp4">
                    <?php _e('Your browser does not support the video tag.', 'reviews-video-generator'); ?>
                </video>
            </div>
            <div class="rvg-modal-actions">
                <a href="#" id="rvg-modal-download" class="button button-primary" download><?php _e('Download Video', 'reviews-video-generator'); ?></a>
            </div>
        </div>
    </div>
</div>

<style>
    .rvg-videos {
        margin-top: 20px;
    }
    
    .rvg-videos-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
    }
    
    .rvg-videos-filters {
        display: flex;
        gap: 10px;
        margin-bottom: 20px;
    }
    
    .rvg-videos-filters select,
    .rvg-videos-filters input {
        max-width: 200px;
    }
    
    .rvg-videos-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 20px;
    }
    
    .rvg-video-card {
        background: #fff;
        border-radius: 5px;
        border: 1px solid #ddd;
        overflow: hidden;
    }
    
    .rvg-video-thumbnail {
        position: relative;
        overflow: hidden;
        background-color: #000;
    }
    
    .rvg-video-thumbnail img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }
    
    .ratio-16-9 {
        position: relative;
        padding-top: 56.25%; /* 9/16 = 0.5625 */
    }
    
    .ratio-1-1 {
        position: relative;
        padding-top: 100%; /* 1/1 = 1 */
    }
    
    .ratio-9-16 {
        position: relative;
        padding-top: 177.78%; /* 16/9 = 1.7778 */
    }
    
    .rvg-video-thumbnail img {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
    }
    
    .rvg-video-play {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        background-color: rgba(0, 0, 0, 0.3);
        opacity: 0;
        transition: opacity 0.3s;
        cursor: pointer;
    }
    
    .rvg-video-play .dashicons {
        color: #fff;
        font-size: 48px;
        width: 48px;
        height: 48px;
        background-color: rgba(0, 0, 0, 0.5);
        border-radius: 50%;
        padding: 10px;
    }
    
    .rvg-video-thumbnail:hover .rvg-video-play {
        opacity: 1;
    }
    
    .rvg-video-info {
        padding: 15px;
    }
    
    .rvg-video-info h3 {
        margin-top: 0;
        margin-bottom: 10px;
    }
    
    .rvg-video-meta {
        display: flex;
        justify-content: space-between;
        color: #666;
        font-size: 0.9em;
        margin-bottom: 10px;
    }
    
    .rvg-video-review {
        font-style: italic;
        margin-bottom: 5px;
    }
    
    .rvg-video-author {
        text-align: right;
        font-weight: 600;
    }
    
    .rvg-video-actions {
        display: flex;
        justify-content: space-between;
        padding: 0 15px 15px;
    }
    
    .rvg-no-videos {
        grid-column: 1 / -1;
        text-align: center;
        padding: 50px 20px;
        background: #fff;
        border-radius: 5px;
        border: 1px solid #ddd;
    }
    
    .rvg-no-videos p {
        margin-bottom: 20px;
        font-size: 1.1em;
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
        border-radius: 5px;
        max-width: 800px;
        width: 90%;
    }
    
    .rvg-modal-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 15px 20px;
        border-bottom: 1px solid #ddd;
    }
    
    .rvg-modal-header h2 {
        margin: 0;
    }
    
    .rvg-close-modal {
        background: none;
        border: none;
        font-size: 24px;
        cursor: pointer;
        color: #666;
    }
    
    .rvg-video-container {
        padding: 20px;
    }
    
    .rvg-video-container video {
        width: 100%;
        display: block;
    }
    
    .rvg-modal-actions {
        padding: 0 20px 20px;
        text-align: center;
    }
    
    @media (max-width: 782px) {
        .rvg-videos-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<script>
jQuery(document).ready(function($) {
    // Handle video play button click
    $('.rvg-video-play').on('click', function() {
        const videoUrl = $(this).data('video');
        
        // Set the video source
        $('#rvg-preview-video').attr('src', videoUrl);
        $('#rvg-modal-download').attr('href', videoUrl);
        
        // Show the modal
        $('#rvg-video-preview-modal').show();
        
        // Load the video
        $('#rvg-preview-video')[0].load();
        $('#rvg-preview-video')[0].play();
    });
    
    // Handle modal close button
    $('.rvg-close-modal').on('click', function() {
        $('#rvg-video-preview-modal').hide();
        $('#rvg-preview-video')[0].pause();
    });
    
    // Close modal when clicking outside
    $(window).on('click', function(e) {
        if ($(e.target).is('.rvg-modal')) {
            $('.rvg-modal').hide();
            $('#rvg-preview-video')[0].pause();
        }
    });
    
    // Handle delete button
    $('.rvg-delete-video').on('click', function() {
        if (confirm('Are you sure you want to delete this video?')) {
            // In a real implementation, we would send an AJAX request to delete the video
            alert('Delete functionality would be implemented here.');
        }
    });
    
    // Handle filtering by aspect ratio
    $('#rvg-filter-aspect-ratio').on('change', function() {
        const aspectRatio = $(this).val();
        
        if (aspectRatio) {
            $('.rvg-video-card').hide();
            $('.rvg-video-card[data-aspect-ratio="' + aspectRatio + '"]').show();
        } else {
            $('.rvg-video-card').show();
        }
    });
    
    // Handle search
    $('#rvg-search-videos').on('keyup', function() {
        const searchTerm = $(this).val().toLowerCase();
        
        $('.rvg-video-card').each(function() {
            const title = $(this).find('h3').text().toLowerCase();
            const review = $(this).find('.rvg-video-review').text().toLowerCase();
            const author = $(this).find('.rvg-video-author').text().toLowerCase();
            
            if (title.includes(searchTerm) || review.includes(searchTerm) || author.includes(searchTerm)) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    });
});
</script>
