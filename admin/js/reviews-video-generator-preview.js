/**
 * JavaScript for the video preview functionality
 */
jQuery(document).ready(function($) {
    console.log('Video preview script loaded');
    
    // Initialize the preview (no sticky JS needed, handled by CSS)
    initializePreview();
    
    // Handle range sliders for position
    $('.rvg-range').on('input', function() {
        const value = $(this).val();
        const id = $(this).attr('id');
        
        // Update the displayed value
        $(`#${id}-value`).text(value);
        
        // Update the preview position
        updatePreviewPositions();
    });
    
    // Handle font size inputs
    $('#rvg-rating-font-size, #rvg-review-font-size, #rvg-reviewer-font-size').on('input', function() {
        updatePreviewFontSizes();
    });
    
    // Handle text color selection for each text element
    $('.rvg-color-options').each(function() {
        const target = $(this).data('target');
        
        $(this).find('.rvg-color-option').on('click', function() {
            // Only remove selected class from siblings
            $(this).siblings().removeClass('selected');
            $(this).addClass('selected');
            
            const color = $(this).data('color');
            $(`#rvg-${target}-text-color`).val(color);
            
            // Update preview based on target
            if (target === 'rating') {
                $('.rvg-preview-rating').css('color', color);
                console.log('Updated rating color to:', color);
            } else if (target === 'review') {
                $('.rvg-preview-text').css('color', color);
                console.log('Updated review text color to:', color);
            } else if (target === 'reviewer') {
                $('.rvg-preview-author').css('color', color);
                console.log('Updated reviewer color to:', color);
            }
        });
    });
    
    // Function to make the preview sticky
    function makePreviewSticky() {
        // Wrap the preview container in a sticky div
        $('.rvg-video-preview-container').wrap('<div class="rvg-sticky-preview"></div>');
        
        // Also move the preview note inside the sticky container
        $('.rvg-preview-note').appendTo('.rvg-sticky-preview');
        
        console.log('Made preview sticky');
    }
    
    // Set all text elements to the same initial font size
    function initializePreview() {
        // Set default font size for all text elements
        const defaultFontSize = 24;
        $('.rvg-preview-rating, .rvg-preview-text, .rvg-preview-author').css('font-size', `${defaultFontSize}px`);
        
        // Center all text elements
        $('.rvg-preview-rating, .rvg-preview-text, .rvg-preview-author').css({
            'position': 'absolute',
            'left': '50%',
            'transform': 'translateX(-50%)'
        });
        
        // Position elements vertically
        $('.rvg-preview-rating').css('top', '30%');
        $('.rvg-preview-text').css('top', '50%');
        $('.rvg-preview-author').css('top', '70%');
        
        // Update the preview content layout
        $('.rvg-video-preview-content').css({
            'position': 'relative',
            'justify-content': 'unset',
            'align-items': 'unset'
        });
        
        // Now apply the current control values
        updatePreviewPositions();
        updatePreviewFontSizes();
    }
    
    // Function to update preview positions based on range inputs
    function updatePreviewPositions() {
        const ratingX = $('#rvg-rating-position-x').val();
        const ratingY = $('#rvg-rating-position-y').val();
        const reviewX = $('#rvg-review-position-x').val();
        const reviewY = $('#rvg-review-position-y').val();
        const reviewerX = $('#rvg-reviewer-position-x').val();
        const reviewerY = $('#rvg-reviewer-position-y').val();
        
        // Calculate percentage values for CSS
        const ratingXPercent = (parseFloat(ratingX) * 50) + 50; // Convert -1 to 1 range to 0% to 100%
        const reviewXPercent = (parseFloat(reviewX) * 50) + 50;
        const reviewerXPercent = (parseFloat(reviewerX) * 50) + 50;
        
        console.log('Updating positions:', {
            rating: { x: ratingXPercent, y: (1 - parseFloat(ratingY)) * 50 },
            review: { x: reviewXPercent, y: (1 - parseFloat(reviewY)) * 50 },
            reviewer: { x: reviewerXPercent, y: (1 - parseFloat(reviewerY)) * 50 }
        });
        
        // Apply positions to preview elements
        $('.rvg-preview-rating').css({
            'position': 'absolute',
            'left': `${ratingXPercent}%`,
            'top': `${(1 - parseFloat(ratingY)) * 50}%`, // Convert Y position to top percentage
            'transform': 'translate(-50%, -50%)'
        });
        
        $('.rvg-preview-text').css({
            'position': 'absolute',
            'left': `${reviewXPercent}%`,
            'top': `${(1 - parseFloat(reviewY)) * 50}%`,
            'transform': 'translate(-50%, -50%)'
        });
        
        $('.rvg-preview-author').css({
            'position': 'absolute',
            'left': `${reviewerXPercent}%`,
            'top': `${(1 - parseFloat(reviewerY)) * 50}%`,
            'transform': 'translate(-50%, -50%)',
            'align-self': 'unset', // Remove the default alignment
            'margin-right': '0'
        });
        
        // Update the preview content layout
        $('.rvg-video-preview-content').css({
            'position': 'relative',
            'justify-content': 'unset', // Remove default flex alignment
            'align-items': 'unset'
        });
    }
    
    // Function to update preview font sizes
    function updatePreviewFontSizes() {
        const ratingFontSize = $('#rvg-rating-font-size').val();
        const reviewFontSize = $('#rvg-review-font-size').val();
        const reviewerFontSize = $('#rvg-reviewer-font-size').val();
        
        console.log('Updating font sizes:', {
            rating: ratingFontSize,
            review: reviewFontSize,
            reviewer: reviewerFontSize
        });
        
        // Apply font sizes to preview elements
        $('.rvg-preview-rating').css('font-size', `${ratingFontSize}px`);
        $('.rvg-preview-text').css('font-size', `${reviewFontSize}px`);
        $('.rvg-preview-author').css('font-size', `${reviewerFontSize}px`);
    }
});
