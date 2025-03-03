/**
 * Reviews Video Generator Admin JavaScript
 */
(function($) {
    'use strict';

    /**
     * Initialize the admin functionality
     */
    function initAdmin() {
        // Initialize the page-specific functionality based on the current page
        const currentPage = getCurrentPage();
        
        switch (currentPage) {
            case 'reviews-video-generator':
                initDashboard();
                break;
            case 'reviews-video-generator-reviews':
                initReviews();
                break;
            case 'reviews-video-generator-create-video':
                initCreateVideo();
                break;
            case 'reviews-video-generator-videos':
                initVideos();
                break;
            case 'reviews-video-generator-settings':
                initSettings();
                break;
        }
        
        // Initialize common functionality
        initCommon();
    }

    /**
     * Get the current admin page
     */
    function getCurrentPage() {
        const urlParams = new URLSearchParams(window.location.search);
        return urlParams.get('page') || '';
    }

    /**
     * Initialize common functionality across all admin pages
     */
    function initCommon() {
        // Handle notices dismissal
        $('.notice-dismiss').on('click', function() {
            $(this).closest('.notice').slideUp();
        });
    }

    /**
     * Initialize the dashboard page
     */
    function initDashboard() {
        console.log('Dashboard page initialized');
        
        // Add any dashboard-specific functionality here
    }

    /**
     * Initialize the reviews page
     */
    function initReviews() {
        console.log('Reviews page initialized');
        
        // Show/hide the add review form
        $('#rvg-add-review').on('click', function() {
            $('#rvg-add-review-form').slideDown();
        });
        
        $('#rvg-cancel-add').on('click', function() {
            $('#rvg-add-review-form').slideUp();
            $('#rvg-review-form')[0].reset();
        });
        
        // Handle review form submission
        $('#rvg-review-form').on('submit', function(e) {
            e.preventDefault();
            
            // Get form data
            const formData = {
                author_name: $('#rvg-review-author').val(),
                rating: $('#rvg-review-rating').val(),
                review_text: $('#rvg-review-text').val(),
                review_date: $('#rvg-review-date').val() || formatDate(new Date())
            };
            
            // In a real implementation, we would send an AJAX request to save the review
            // For now, we'll just show a success message and reset the form
            saveReview(formData);
        });
        
        // Handle edit and delete buttons
        $(document).on('click', '.rvg-edit-review', function() {
            const reviewId = $(this).closest('tr').data('id');
            editReview(reviewId);
        });
        
        $(document).on('click', '.rvg-delete-review', function() {
            const reviewId = $(this).closest('tr').data('id');
            if (confirm('Are you sure you want to delete this review?')) {
                deleteReview(reviewId);
            }
        });
        
        // Handle filtering and searching
        $('#rvg-filter-rating, #rvg-search-reviews').on('change keyup', function() {
            filterReviews();
        });
    }

    /**
     * Save a review (mock implementation)
     */
    function saveReview(formData) {
        // In a real implementation, this would send an AJAX request to save the review
        console.log('Saving review:', formData);
        
        // Show success message
        alert('Review saved successfully!');
        
        // Reset the form and hide it
        $('#rvg-review-form')[0].reset();
        $('#rvg-add-review-form').slideUp();
        
        // Reload the reviews list
        // In a real implementation, we would reload the reviews from the server
        // For now, we'll just reload the page
        // window.location.reload();
    }

    /**
     * Edit a review (mock implementation)
     */
    function editReview(reviewId) {
        // In a real implementation, this would fetch the review data and show an edit form
        console.log('Editing review:', reviewId);
        alert('Edit functionality would be implemented here.');
    }

    /**
     * Delete a review (mock implementation)
     */
    function deleteReview(reviewId) {
        // In a real implementation, this would send an AJAX request to delete the review
        console.log('Deleting review:', reviewId);
        alert('Delete functionality would be implemented here.');
    }

    /**
     * Filter reviews (mock implementation)
     */
    function filterReviews() {
        // In a real implementation, this would filter the reviews based on the selected criteria
        console.log('Filtering reviews');
        alert('Filter/search functionality would be implemented here.');
    }

    /**
     * Initialize the create video page
     */
    function initCreateVideo() {
        console.log('Create Video page initialized');
        
        // Handle background video selection
        $('.rvg-background-video-item').on('click', function() {
            $('.rvg-background-video-item').removeClass('selected');
            $(this).addClass('selected');
            
            const videoUrl = $(this).data('video');
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
            
            // In a real implementation, we would send an AJAX request to create the video
            createVideo(formData);
        });
    }

    /**
     * Create a video using AJAX
     */
    function createVideo(formData) {
        console.log('Creating video:', formData);
        
        // Check if the background video URL is local
        if (isLocalUrl(formData.background_video)) {
            console.warn('Local video URL detected:', formData.background_video);
            
            // Show a warning message
            const confirmMessage = 'You are using a local video URL which may not be accessible by the Shotstack API. The system will use a sample video instead. Continue?';
            if (!confirm(confirmMessage)) {
                return;
            }
        }
        
        // Show processing modal
        $('#rvg-processing-modal').show();
        $('.rvg-progress-bar').css('width', '0%');
        $('.rvg-progress-status').text(rvg_admin_data.i18n.please_wait);
        
        // Add nonce to form data
        formData.nonce = rvg_admin_data.nonce;
        
        // Send AJAX request to create video
        $.ajax({
            url: rvg_admin_data.ajax_url,
            type: 'POST',
            data: {
                action: 'rvg_create_video',
                nonce: rvg_admin_data.nonce,
                review_id: formData.review_id,
                author_name: formData.author_name,
                rating: formData.rating,
                review_text: formData.review_text,
                background_video: formData.background_video,
                text_color: formData.text_color,
                font: formData.font,
                aspect_ratio: formData.aspect_ratio
            },
            success: function(response) {
                console.log('Create video response:', response);
                
                if (response.success) {
                    // Start polling for video status
                    pollVideoStatus(response.data.render_id);
                } else {
                    // Show error message
                    handleApiError(response.data.message || rvg_admin_data.i18n.error_creating_video);
                    $('#rvg-processing-modal').hide();
                }
            },
            error: function(xhr, status, error) {
                console.error('Error creating video:', error);
                handleApiError(rvg_admin_data.i18n.error_creating_video);
                $('#rvg-processing-modal').hide();
            }
        });
    }
    
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
            errorMessage = 'API Error: The style format is incorrect. Please contact the plugin developer.';
        } else if (errorMessage.includes('fails to match the required pattern')) {
            errorMessage = 'API Error: The video URL is not in a format that Shotstack can access. Please use a publicly accessible video URL or one of the sample videos.';
        } else if (errorMessage.includes('API key')) {
            errorMessage = 'API Error: There is an issue with your Shotstack API key. Please check your settings.';
        } else if (errorMessage.includes('must be one of')) {
            errorMessage = 'API Error: One of the parameters has an invalid value. Please contact the plugin developer.';
        } else if (errorMessage.includes('ValidationError')) {
            errorMessage = 'API Error: The video request contains invalid parameters. Please contact the plugin developer.';
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
        $('.rvg-progress-status').text(rvg_admin_data.i18n.preparing_assets);
        
        // Poll for status every 5 seconds
        const statusInterval = setInterval(function() {
            $.ajax({
                url: rvg_admin_data.ajax_url,
                type: 'POST',
                data: {
                    action: 'rvg_get_video_status',
                    nonce: rvg_admin_data.nonce,
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
                                $('.rvg-progress-status').text(rvg_admin_data.i18n.preparing_assets);
                                break;
                            case 'fetching':
                                $('.rvg-progress-status').text(rvg_admin_data.i18n.preparing_assets);
                                break;
                            case 'rendering':
                                $('.rvg-progress-status').text(rvg_admin_data.i18n.rendering_video);
                                break;
                            case 'saving':
                                $('.rvg-progress-status').text(rvg_admin_data.i18n.finalizing_video);
                                break;
                            case 'done':
                                $('.rvg-progress-status').text(rvg_admin_data.i18n.video_ready);
                                
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
                                $('.rvg-progress-status').text(response.data.message || 'Error creating video');
                                
                                // Clear the interval
                                clearInterval(statusInterval);
                                
                                // Show error message
                                setTimeout(function() {
                                    $('#rvg-processing-modal').hide();
                                    alert(response.data.message || rvg_admin_data.i18n.error_creating_video);
                                }, 500);
                                break;
                        }
                    } else {
                        // Show error message
                        clearInterval(statusInterval);
                        $('#rvg-processing-modal').hide();
                        alert(response.data.message || rvg_admin_data.i18n.error_getting_status);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error getting video status:', error);
                    clearInterval(statusInterval);
                    $('#rvg-processing-modal').hide();
                    alert(rvg_admin_data.i18n.error_getting_status);
                }
            });
        }, 5000); // Poll every 5 seconds
    }

    /**
     * Initialize the videos page
     */
    function initVideos() {
        console.log('Videos page initialized');
        
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
            const videoId = $(this).data('id');
            if (confirm('Are you sure you want to delete this video?')) {
                deleteVideo(videoId);
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
    }

    /**
     * Delete a video (mock implementation)
     */
    function deleteVideo(videoId) {
        // In a real implementation, this would send an AJAX request to delete the video
        console.log('Deleting video:', videoId);
        alert('Delete functionality would be implemented here.');
    }

    /**
     * Initialize the settings page
     */
    function initSettings() {
        console.log('Settings page initialized');
        
        // Add any settings-specific functionality here
    }

    /**
     * Format a date as YYYY-MM-DD
     */
    function formatDate(date) {
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        return `${year}-${month}-${day}`;
    }

    // Initialize when the DOM is ready
    $(document).ready(function() {
        initAdmin();
    });

})(jQuery);
