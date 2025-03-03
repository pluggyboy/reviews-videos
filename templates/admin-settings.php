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
            <p><?php _e('Please enter your Shotstack API key to enable video generation.', 'reviews-video-generator'); ?></p>
        </div>
        <?php
    } else {
        ?>
        <div class="notice notice-success">
            <p><?php _e('Shotstack API key is set. You can now generate videos.', 'reviews-video-generator'); ?></p>
        </div>
        <?php
    }
    ?>
    
    <form method="post" action="options.php">
        <?php
        // Output security fields
        settings_fields('rvg_settings');
        
        // Output setting sections and their fields
        do_settings_sections('rvg_settings');
        
        // Output save settings button
        submit_button(__('Save Settings', 'reviews-video-generator'));
        ?>
    </form>
    
    <div class="rvg-settings-info">
        <h2><?php _e('About Shotstack', 'reviews-video-generator'); ?></h2>
        <p><?php _e('Shotstack is a cloud-based video editing API that allows you to programmatically create videos. This plugin uses Shotstack to generate videos from your reviews.', 'reviews-video-generator'); ?></p>
        
        <h3><?php _e('API Key', 'reviews-video-generator'); ?></h3>
        <p><?php _e('You need a Shotstack API key to use this plugin. If you don\'t have one, you can sign up for a free account at <a href="https://shotstack.io" target="_blank">shotstack.io</a>.', 'reviews-video-generator'); ?></p>
        
        <h3><?php _e('Environment', 'reviews-video-generator'); ?></h3>
        <p><?php _e('The Sandbox environment is for testing and has limitations. Use Production for final videos.', 'reviews-video-generator'); ?></p>
    </div>
</div>

<style>
    .rvg-settings-info {
        margin-top: 30px;
        background: #fff;
        padding: 20px;
        border-radius: 5px;
        border: 1px solid #ddd;
    }
    
    .rvg-settings-info h2 {
        margin-top: 0;
    }
    
    .rvg-settings-info h3 {
        margin-top: 20px;
    }
</style>
