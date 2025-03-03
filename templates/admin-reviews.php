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
    
    <div class="rvg-reviews">
        <div class="rvg-reviews-header">
            <h2><?php _e('Manage Reviews', 'reviews-video-generator'); ?></h2>
            <button id="rvg-add-review" class="button button-primary"><?php _e('Add New Review', 'reviews-video-generator'); ?></button>
        </div>
        
        <div id="rvg-add-review-form" class="rvg-form" style="display: none;">
            <h3><?php _e('Add New Review', 'reviews-video-generator'); ?></h3>
            <form id="rvg-review-form">
                <div class="rvg-form-field">
                    <label for="rvg-review-author"><?php _e('Author Name', 'reviews-video-generator'); ?></label>
                    <input type="text" id="rvg-review-author" name="author_name" required>
                </div>
                
                <div class="rvg-form-field">
                    <label for="rvg-review-rating"><?php _e('Rating', 'reviews-video-generator'); ?></label>
                    <select id="rvg-review-rating" name="rating" required>
                        <option value="5">★★★★★ (5 stars)</option>
                        <option value="4">★★★★☆ (4 stars)</option>
                        <option value="3">★★★☆☆ (3 stars)</option>
                        <option value="2">★★☆☆☆ (2 stars)</option>
                        <option value="1">★☆☆☆☆ (1 star)</option>
                    </select>
                </div>
                
                <div class="rvg-form-field">
                    <label for="rvg-review-text"><?php _e('Review Text', 'reviews-video-generator'); ?></label>
                    <textarea id="rvg-review-text" name="review_text" rows="5" required></textarea>
                    <p class="description"><?php _e('Enter the review text. Keep it concise for better video results.', 'reviews-video-generator'); ?></p>
                </div>
                
                <div class="rvg-form-field">
                    <label for="rvg-review-date"><?php _e('Review Date', 'reviews-video-generator'); ?></label>
                    <input type="date" id="rvg-review-date" name="review_date">
                </div>
                
                <div class="rvg-form-actions">
                    <button type="submit" class="button button-primary"><?php _e('Save Review', 'reviews-video-generator'); ?></button>
                    <button type="button" id="rvg-cancel-add" class="button"><?php _e('Cancel', 'reviews-video-generator'); ?></button>
                </div>
            </form>
        </div>
        
        <div class="rvg-reviews-list">
            <div class="rvg-reviews-filters">
                <select id="rvg-filter-rating">
                    <option value=""><?php _e('All Ratings', 'reviews-video-generator'); ?></option>
                    <option value="5">★★★★★ (5 stars)</option>
                    <option value="4">★★★★☆ (4 stars)</option>
                    <option value="3">★★★☆☆ (3 stars)</option>
                    <option value="2">★★☆☆☆ (2 stars)</option>
                    <option value="1">★☆☆☆☆ (1 star)</option>
                </select>
                
                <input type="text" id="rvg-search-reviews" placeholder="<?php _e('Search reviews...', 'reviews-video-generator'); ?>">
            </div>
            
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th><?php _e('Author', 'reviews-video-generator'); ?></th>
                        <th><?php _e('Rating', 'reviews-video-generator'); ?></th>
                        <th><?php _e('Review', 'reviews-video-generator'); ?></th>
                        <th><?php _e('Date', 'reviews-video-generator'); ?></th>
                        <th><?php _e('Actions', 'reviews-video-generator'); ?></th>
                    </tr>
                </thead>
                <tbody id="rvg-reviews-tbody">
                    <!-- Reviews will be loaded here via JavaScript -->
                    <tr class="rvg-no-reviews">
                        <td colspan="5"><?php _e('No reviews found. Add your first review using the button above.', 'reviews-video-generator'); ?></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<style>
    .rvg-reviews {
        margin-top: 20px;
    }
    
    .rvg-reviews-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
    }
    
    .rvg-form {
        background: #fff;
        padding: 20px;
        border-radius: 5px;
        border: 1px solid #ddd;
        margin-bottom: 20px;
    }
    
    .rvg-form h3 {
        margin-top: 0;
        margin-bottom: 20px;
    }
    
    .rvg-form-field {
        margin-bottom: 15px;
    }
    
    .rvg-form-field label {
        display: block;
        margin-bottom: 5px;
        font-weight: 600;
    }
    
    .rvg-form-field input[type="text"],
    .rvg-form-field input[type="date"],
    .rvg-form-field select,
    .rvg-form-field textarea {
        width: 100%;
    }
    
    .rvg-form-actions {
        margin-top: 20px;
    }
    
    .rvg-reviews-filters {
        display: flex;
        gap: 10px;
        margin-bottom: 10px;
    }
    
    .rvg-reviews-filters select,
    .rvg-reviews-filters input {
        max-width: 200px;
    }
    
    .rvg-star-rating {
        color: #FFD700;
    }
    
    .rvg-review-actions {
        display: flex;
        gap: 5px;
    }
</style>

<script>
jQuery(document).ready(function($) {
    // Show/hide the add review form
    $('#rvg-add-review').on('click', function() {
        $('#rvg-add-review-form').slideDown();
    });
    
    $('#rvg-cancel-add').on('click', function() {
        $('#rvg-add-review-form').slideUp();
        $('#rvg-review-form')[0].reset();
    });
    
    // Handle form submission
    $('#rvg-review-form').on('submit', function(e) {
        e.preventDefault();
        
        // In a real implementation, this would save the review to the database
        // For now, we'll just show a success message and reset the form
        alert('Review saved successfully!');
        $('#rvg-review-form')[0].reset();
        $('#rvg-add-review-form').slideUp();
        
        // In a real implementation, we would reload the reviews list here
    });
    
    // For demonstration purposes, let's add some sample reviews
    function loadSampleReviews() {
        const sampleReviews = [
            {
                id: 1,
                author_name: 'John Smith',
                rating: 5,
                review_text: 'Absolutely amazing service! The staff went above and beyond to help me. Would definitely recommend to anyone looking for quality service.',
                review_date: '2025-02-28'
            },
            {
                id: 2,
                author_name: 'Jane Doe',
                rating: 4,
                review_text: 'Great experience overall. The product exceeded my expectations and customer service was very helpful.',
                review_date: '2025-02-25'
            },
            {
                id: 3,
                author_name: 'Mike Johnson',
                rating: 5,
                review_text: 'Top notch quality and service. I\'ve been a customer for years and they never disappoint.',
                review_date: '2025-02-20'
            }
        ];
        
        // Clear the table
        $('#rvg-reviews-tbody').empty();
        
        // Add the sample reviews
        sampleReviews.forEach(function(review) {
            const stars = '★'.repeat(review.rating) + '☆'.repeat(5 - review.rating);
            
            const row = `
                <tr data-id="${review.id}">
                    <td>${review.author_name}</td>
                    <td><span class="rvg-star-rating">${stars}</span></td>
                    <td>${review.review_text}</td>
                    <td>${review.review_date}</td>
                    <td class="rvg-review-actions">
                        <button class="button rvg-edit-review"><?php _e('Edit', 'reviews-video-generator'); ?></button>
                        <button class="button rvg-delete-review"><?php _e('Delete', 'reviews-video-generator'); ?></button>
                        <a href="<?php echo admin_url('admin.php?page=' . $this->plugin_name . '-create-video'); ?>&review_id=${review.id}" class="button button-primary"><?php _e('Create Video', 'reviews-video-generator'); ?></a>
                    </td>
                </tr>
            `;
            
            $('#rvg-reviews-tbody').append(row);
        });
    }
    
    // Load sample reviews
    loadSampleReviews();
    
    // Handle edit and delete buttons
    $(document).on('click', '.rvg-edit-review', function() {
        alert('Edit functionality would be implemented here.');
    });
    
    $(document).on('click', '.rvg-delete-review', function() {
        if (confirm('Are you sure you want to delete this review?')) {
            alert('Delete functionality would be implemented here.');
        }
    });
    
    // Handle filtering and searching
    $('#rvg-filter-rating, #rvg-search-reviews').on('change keyup', function() {
        alert('Filter/search functionality would be implemented here.');
    });
});
</script>
