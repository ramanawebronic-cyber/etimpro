/**
 * ETIM Admin Settings JavaScript
 * 
 * @package ETIM_For_WooCommerce
 */

(function($) {
    'use strict';
    
    /**
     * Toggle password visibility
     */
    function initToggleSecret() {
        $('.etim-toggle-secret').on('click', function() {
            var targetId = $(this).data('target');
            var $input = $('#' + targetId);
            var $icon = $(this).find('.dashicons');
            
            if ($input.attr('type') === 'password') {
                $input.attr('type', 'text');
                $icon.removeClass('dashicons-visibility').addClass('dashicons-hidden');
            } else {
                $input.attr('type', 'password');
                $icon.removeClass('dashicons-hidden').addClass('dashicons-visibility');
            }
        });
    }
    
    /**
     * Test API connection
     */
    function initTestConnection() {
        $('#etim-test-connection').on('click', function() {
            var $button = $(this);
            var $result = $('#etim-test-result');
            
            // Check if credentials are filled
            var clientId = $('#etim_client_id').val();
            var clientSecret = $('#etim_client_secret').val();
            
            if (!clientId || !clientSecret) {
                $result
                    .removeClass('success loading')
                    .addClass('error')
                    .text(etimSettings.strings.saveFirst)
                    .show();
                return;
            }
            
            // Show loading state
            $button.prop('disabled', true);
            $result
                .removeClass('success error')
                .addClass('loading')
                .text(etimSettings.strings.testing)
                .show();
            
            // Make AJAX request
            $.ajax({
                url: etimSettings.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'etim_test_connection',
                    nonce: etimSettings.nonce
                },
                success: function(response) {
                    $button.prop('disabled', false);
                    
                    if (response.success) {
                        $result
                            .removeClass('loading error')
                            .addClass('success')
                            .text(etimSettings.strings.success);
                    } else {
                        $result
                            .removeClass('loading success')
                            .addClass('error')
                            .text(etimSettings.strings.error + ' ' + (response.data.message || 'Unknown error'));
                    }
                },
                error: function(xhr, status, error) {
                    $button.prop('disabled', false);
                    $result
                        .removeClass('loading success')
                        .addClass('error')
                        .text(etimSettings.strings.error + ' ' + error);
                }
            });
        });
    }
    
    /**
     * Initialize on document ready
     */
    $(document).ready(function() {
        initToggleSecret();
        initTestConnection();
    });
    
})(jQuery);
