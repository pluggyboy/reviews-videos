<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    
    <?php
    // Process video upload if form was submitted
    $this->handle_video_upload();
    
    // Display settings errors
    settings_errors('rvg_video_upload');
    
    // Process video deletion if requested
    if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['video_id'])) {
        // Check nonce for security
        if (isset($_GET['_wpnonce']) && wp_verify_nonce($_GET['_wpnonce'], 'delete_video_' . $_GET['video_id'])) {
            // Delete the video
            $deleted = $this->delete_background_video($_GET['video_id']);
            
            if ($deleted) {
                ?>
                <div class="notice notice-success">
                    <p><?php _e('Video deleted successfully.', 'reviews-video-generator'); ?></p>
                </div>
                <?php
            } else {
                ?>
                <div class="notice notice-error">
                    <p><?php _e('Error deleting video. Please try again.', 'reviews-video-generator'); ?></p>
                </div>
                <?php
            }
        } else {
            ?>
            <div class="notice notice-error">
                <p><?php _e('Invalid security token. Please try again.', 'reviews-video-generator'); ?></p>
            </div>
            <?php
        }
    }
    
    // Get all videos
    $videos = $this->get_background_videos();
    ?>
    
    <div class="rvg-background-videos">
        <div class="rvg-background-videos-header">
            <h2><?php _e('Background Videos', 'reviews-video-generator'); ?></h2>
            <button id="rvg-add-video" class="button button-primary"><?php _e('Upload New Video', 'reviews-video-generator'); ?></button>
        </div>
        
        <div id="rvg-upload-video-form" class="rvg-form" style="display: none;">
            <h3><?php _e('Upload New Background Video', 'reviews-video-generator'); ?></h3>
            <form method="post" enctype="multipart/form-data">
                <?php wp_nonce_field('rvg_upload_video', 'rvg_upload_video_nonce'); ?>
                
                <div class="rvg-form-field">
                    <label for="rvg-video-title"><?php _e('Video Title', 'reviews-video-generator'); ?></label>
                    <input type="text" id="rvg-video-title" name="rvg_video_title" required>
                    <p class="description"><?php _e('Enter a title for the video.', 'reviews-video-generator'); ?></p>
                </div>
                
                <div class="rvg-form-field">
                    <label for="rvg-video-file"><?php _e('Video File', 'reviews-video-generator'); ?></label>
                    <input type="file" id="rvg-video-file" name="rvg_video_file" accept="video/*" required>
                    <p class="description"><?php _e('Select a video file to upload. Supported formats: MP4, MOV, AVI, FLV, WEBM.', 'reviews-video-generator'); ?></p>
                </div>
                
                <div class="rvg-form-actions">
                    <button type="submit" name="rvg_upload_video_submit" class="button button-primary"><?php _e('Upload Video', 'reviews-video-generator'); ?></button>
                    <button type="button" id="rvg-cancel-upload" class="button"><?php _e('Cancel', 'reviews-video-generator'); ?></button>
                </div>
            </form>
        </div>
        
        <div class="rvg-background-videos-grid">
            <?php if (empty($videos)): ?>
                <div class="rvg-no-videos">
                    <p><?php _e('No background videos found. Upload your first video using the button above.', 'reviews-video-generator'); ?></p>
                </div>
            <?php else: ?>
                <?php foreach ($videos as $video): ?>
                    <div class="rvg-video-card" data-id="<?php echo esc_attr($video['id']); ?>">
                        <div class="rvg-video-thumbnail">
                            <?php 
                            // Check for different thumbnail sources in order of preference
                            if (isset($video['thumbnail'])) {
                                // Use the provided thumbnail (for default videos)
                                $thumbnail_url = $video['thumbnail'];
                            } elseif (isset($video['attachment_id'])) {
                                // Get the WordPress-generated thumbnail for media library videos
                                $thumbnail_url = wp_get_attachment_image_url($video['attachment_id'], 'medium');
                                
                                // If no thumbnail is available, use the video poster frame if available
                                if (!$thumbnail_url) {
                                    $metadata = wp_get_attachment_metadata($video['attachment_id']);
                                    if (isset($metadata['image']['full'])) {
                                        $thumbnail_url = $metadata['image']['full']['file'];
                                    }
                                }
                            }
                            
                            // If no thumbnail is available, use a placeholder
                            if (empty($thumbnail_url)) {
                                $thumbnail_url = 'https://shotstack-assets.s3.ap-southeast-2.amazonaws.com/thumbnails/placeholder.jpg';
                            }
                            ?>
                            <img src="<?php echo esc_url($thumbnail_url); ?>" alt="<?php echo esc_attr($video['title']); ?>">
                            <div class="rvg-video-play" data-video="<?php echo esc_url($video['url']); ?>">
                                <span class="dashicons dashicons-controls-play"></span>
                            </div>
                        </div>
                        <div class="rvg-video-info">
                            <h3><?php echo esc_html($video['title']); ?></h3>
                            <div class="rvg-video-meta">
                                <span class="rvg-video-filename"><?php echo esc_html($video['filename']); ?></span>
                                <span class="rvg-video-date"><?php echo date_i18n(get_option('date_format'), strtotime($video['uploaded'])); ?></span>
                            </div>
                        </div>
                        <div class="rvg-video-actions">
                            <?php
                            // Only show delete button for user-uploaded videos (not default ones)
                            if (!isset($video['thumbnail'])):
                                $delete_url = add_query_arg(
                                    array(
                                        'action' => 'delete',
                                        'video_id' => $video['id'],
                                        '_wpnonce' => wp_create_nonce('delete_video_' . $video['id'])
                                    ),
                                    admin_url('admin.php?page=' . $this->plugin_name . '-background-videos')
                                );
                            ?>
                                <a href="<?php echo esc_url($delete_url); ?>" class="button rvg-delete-video" onclick="return confirm('<?php _e('Are you sure you want to delete this video?', 'reviews-video-generator'); ?>');"><?php _e('Delete', 'reviews-video-generator'); ?></a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
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
        </div>
    </div>
</div>

<style>
    .rvg-background-videos {
        margin-top: 20px;
    }
    
    .rvg-background-videos-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
    }
    
    .rvg-background-videos-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 20px;
        margin-top: 20px;
    }
    
    .rvg-video-card {
        background: #fff;
        border-radius: 5px;
        border: 1px solid #ddd;
        overflow: hidden;
    }
    
    .rvg-video-thumbnail {
        position: relative;
        height: 0;
        padding-top: 56.25%; /* 16:9 aspect ratio */
        background-color: #f0f0f0;
        overflow: hidden;
    }
    
    .rvg-video-thumbnail img {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    
    .rvg-video-thumbnail-placeholder {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        background-color: #f0f0f0;
    }
    
    .rvg-video-thumbnail-placeholder .dashicons {
        font-size: 48px;
        width: 48px;
        height: 48px;
        color: #999;
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
    }
    
    .rvg-video-actions {
        padding: 0 15px 15px;
        text-align: right;
    }
    
    .rvg-no-videos {
        grid-column: 1 / -1;
        text-align: center;
        padding: 50px 20px;
        background: #fff;
        border-radius: 5px;
        border: 1px solid #ddd;
    }
</style>

<script>
jQuery(document).ready(function($) {
    // Show/hide the upload form
    $('#rvg-add-video').on('click', function() {
        $('#rvg-upload-video-form').slideDown();
    });
    
    $('#rvg-cancel-upload').on('click', function() {
        $('#rvg-upload-video-form').slideUp();
        $('#rvg-upload-video-form form')[0].reset();
    });
    
    // Make sure the form has the correct enctype
    $('#rvg-upload-video-form form').attr('enctype', 'multipart/form-data');
    
    // Handle video play button click
    $('.rvg-video-play').on('click', function() {
        const videoUrl = $(this).data('video');
        
        // Set the video source
        $('#rvg-preview-video').attr('src', videoUrl);
        
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
});
</script>
