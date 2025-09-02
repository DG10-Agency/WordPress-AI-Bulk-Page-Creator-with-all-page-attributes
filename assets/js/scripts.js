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

    // AI Generation Loading Animation - for the initial form
    $('form').on('submit', function(e) {
        var $form = $(this);
        var submitButton = $form.find('input[type="submit"]');
        var buttonText = submitButton.val();
        
        // Check if this is the AI generation form (has business_type field)
        if ($form.find('input[name="abpcwa_business_type"]').length > 0 && buttonText === 'Generate Page Suggestions') {
            // Create loading overlay if it doesn't exist
            if ($('#abpcwa-loading-overlay').length === 0) {
                $('body').append(`
                    <div id="abpcwa-loading-overlay" style="
                        position: fixed;
                        top: 0;
                        left: 0;
                        width: 100%;
                        height: 100%;
                        background: rgba(255, 255, 255, 0.9);
                        z-index: 9999;
                        display: flex;
                        flex-direction: column;
                        justify-content: center;
                        align-items: center;
                        backdrop-filter: blur(5px);
                    ">
                        <div style="text-align: center;">
                            <img src="${abpcwa_plugin_data.plugin_url}assets/images/loader.png" 
                                 alt="Loading..." 
                                 style="
                                     width: 80px;
                                     height: 80px;
                                     animation: spin 1.5s linear infinite;
                                     margin-bottom: 20px;
                                 ">
                            <h3 style="color: #2271b1; margin: 0 0 10px 0; font-size: 18px;">
                                Analyzing Your Business with AI
                            </h3>
                            <p style="color: #666; margin: 0; font-size: 14px;">
                                Crafting the perfect page structure for your needs...
                            </p>
                        </div>
                    </div>
                `);
                
                // Add CSS animation
                $('<style>')
                    .prop('type', 'text/css')
                    .html(`
                        @keyframes spin {
                            0% { transform: rotate(0deg); }
                            100% { transform: rotate(360deg); }
                        }
                    `)
                    .appendTo('head');
            }
            
            // Show loading overlay
            $('#abpcwa-loading-overlay').fadeIn(300);
            
            // Disable submit button to prevent multiple submissions
            submitButton.prop('disabled', true).val('Analyzing with AI...');
        }
        
        // Check if this is the page creation form (has selected_pages field)
        if ($form.find('input[name="abpcwa_selected_pages[]"]').length > 0 && buttonText === 'Create Selected Pages') {
            // Create loading overlay if it doesn't exist
            if ($('#abpcwa-loading-overlay').length === 0) {
                $('body').append(`
                    <div id="abpcwa-loading-overlay" style="
                        position: fixed;
                        top: 0;
                        left: 0;
                        width: 100%;
                        height: 100%;
                        background: rgba(255, 255, 255, 0.9);
                        z-index: 9999;
                        display: flex;
                        flex-direction: column;
                        justify-content: center;
                        align-items: center;
                        backdrop-filter: blur(5px);
                    ">
                        <div style="text-align: center;">
                            <img src="${abpcwa_plugin_data.plugin_url}assets/images/loader.png" 
                                 alt="Loading..." 
                                 style="
                                     width: 80px;
                                     height: 80px;
                                     animation: spin 1.5s linear infinite;
                                     margin-bottom: 20px;
                                 ">
                            <h3 style="color: #2271b1; margin: 0 0 10px 0; font-size: 18px;">
                                Generating Awesome Pages with Context-Aware AI
                            </h3>
                            <p style="color: #666; margin: 0; font-size: 14px;">
                                This may take a few moments. Please don't close this window...
                            </p>
                        </div>
                    </div>
                `);
                
                // Add CSS animation
                $('<style>')
                    .prop('type', 'text/css')
                    .html(`
                        @keyframes spin {
                            0% { transform: rotate(0deg); }
                            100% { transform: rotate(360deg); }
                        }
                    `)
                    .appendTo('head');
            }
            
            // Show loading overlay
            $('#abpcwa-loading-overlay').fadeIn(300);
            
            // Disable submit button to prevent multiple submissions
            submitButton.prop('disabled', true).val('Creating Pages...');
        }
    });

    // Remove loading overlay when page reloads (after form submission)
    if (window.history && window.history.replaceState) {
        window.history.replaceState(null, null, window.location.href);
    }
});
