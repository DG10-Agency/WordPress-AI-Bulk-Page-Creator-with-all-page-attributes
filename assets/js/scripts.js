jQuery(document).ready(function($) {
    // Handle AI provider change to enable/disable image generation checkbox
    function updateImageGenerationCheckbox() {
        var provider = $('select[name="abpcwa_ai_provider"]').val();
        var generateImagesCheckbox = $('#abpcwa_generate_images');
        
        if (provider === 'deepseek') {
            generateImagesCheckbox.prop('disabled', true);
            generateImagesCheckbox.prop('checked', false);
        } else {
            generateImagesCheckbox.prop('disabled', false);
        }
    }
    
    // Update on page load
    updateImageGenerationCheckbox();
    
    // Update when provider changes
    $('select[name="abpcwa_ai_provider"]').on('change', function() {
        updateImageGenerationCheckbox();
    });
    
    // Show loading state when generating images
    $('form').on('submit', function() {
        if ($('#abpcwa_generate_images').is(':checked') && !$('#abpcwa_generate_images').is(':disabled')) {
            $('.submit .spinner').css('visibility', 'visible');
            $('input[type="submit"]').prop('disabled', true).val('Generating Images...');
        }
    });
});
