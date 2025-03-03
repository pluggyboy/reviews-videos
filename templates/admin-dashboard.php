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
    
    <div class="rvg-dashboard">
        <div class="rvg-dashboard-header">
            <h2><?php _e('Welcome to Review Showcase', 'reviews-video-generator'); ?></h2>
            <p><?php _e('Transform your reviews into engaging social videos with just a few clicks.', 'reviews-video-generator'); ?></p>
        </div>
        
        <div class="rvg-dashboard-cards">
            <div class="rvg-card">
                <div class="rvg-card-icon dashicons dashicons-star-filled"></div>
                <h3><?php _e('Add Reviews', 'reviews-video-generator'); ?></h3>
                <p><?php _e('Manually add your best customer reviews to showcase in videos.', 'reviews-video-generator'); ?></p>
                <a href="<?php echo admin_url('admin.php?page=' . $this->plugin_name . '-reviews'); ?>" class="button"><?php _e('Manage Reviews', 'reviews-video-generator'); ?></a>
            </div>
            
            <div class="rvg-card">
                <div class="rvg-card-icon dashicons dashicons-video-alt3"></div>
                <h3><?php _e('Create Videos', 'reviews-video-generator'); ?></h3>
                <p><?php _e('Turn your reviews into professional videos for social media.', 'reviews-video-generator'); ?></p>
                <a href="<?php echo admin_url('admin.php?page=' . $this->plugin_name . '-create-video'); ?>" class="button"><?php _e('Create Video', 'reviews-video-generator'); ?></a>
            </div>
            
            <div class="rvg-card">
                <div class="rvg-card-icon dashicons dashicons-format-gallery"></div>
                <h3><?php _e('Your Videos', 'reviews-video-generator'); ?></h3>
                <p><?php _e('View, download, and manage your generated videos.', 'reviews-video-generator'); ?></p>
                <a href="<?php echo admin_url('admin.php?page=' . $this->plugin_name . '-videos'); ?>" class="button"><?php _e('View Videos', 'reviews-video-generator'); ?></a>
            </div>
        </div>
        
        <div class="rvg-dashboard-getting-started">
            <h2><?php _e('Getting Started', 'reviews-video-generator'); ?></h2>
            <ol>
                <li><?php _e('Configure your Shotstack API key in the Settings page', 'reviews-video-generator'); ?></li>
                <li><?php _e('Add your customer reviews manually', 'reviews-video-generator'); ?></li>
                <li><?php _e('Select a review and create a video', 'reviews-video-generator'); ?></li>
                <li><?php _e('Customize the video appearance', 'reviews-video-generator'); ?></li>
                <li><?php _e('Generate and download your video', 'reviews-video-generator'); ?></li>
            </ol>
        </div>
    </div>
</div>

<style>
    .rvg-dashboard {
        margin-top: 20px;
    }
    
    .rvg-dashboard-header {
        background: #fff;
        padding: 20px;
        border-radius: 5px;
        border: 1px solid #ddd;
        margin-bottom: 20px;
    }
    
    .rvg-dashboard-header h2 {
        margin-top: 0;
    }
    
    .rvg-dashboard-cards {
        display: flex;
        gap: 20px;
        margin-bottom: 20px;
    }
    
    .rvg-card {
        background: #fff;
        padding: 20px;
        border-radius: 5px;
        border: 1px solid #ddd;
        flex: 1;
        display: flex;
        flex-direction: column;
    }
    
    .rvg-card-icon {
        font-size: 2.5em;
        margin-bottom: 10px;
        color: #0073aa;
    }
    
    .rvg-card h3 {
        margin-top: 0;
    }
    
    .rvg-card p {
        flex-grow: 1;
    }
    
    .rvg-dashboard-getting-started {
        background: #fff;
        padding: 20px;
        border-radius: 5px;
        border: 1px solid #ddd;
    }
    
    .rvg-dashboard-getting-started h2 {
        margin-top: 0;
    }
    
    .rvg-dashboard-getting-started ol {
        margin-left: 20px;
    }
    
    .rvg-dashboard-getting-started li {
        margin-bottom: 10px;
    }
</style>
