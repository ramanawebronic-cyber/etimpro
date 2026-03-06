/**
 * ETIM Admin Settings JavaScript
 * 
 * @package ETIM_For_WooCommerce
 */

(function ($) {
    'use strict';

    /**
     * Toggle password visibility
     */
    function initToggleSecret() {
        $('.etim-toggle-secret').on('click', function () {
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
        $('#etim-test-connection').on('click', function () {
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
                success: function (response) {
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
                error: function (xhr, status, error) {
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
     * Fallback for missing images to show alert instead of broken icon
     */
    function initImageFallback() {
        $('img').on('error', function () {
            var src = $(this).attr('src') || '';
            var filename = src.split('/').pop();
            var altText = $(this).attr('alt') || filename;
            var isIcon = $(this).hasClass('etim-icon') || $(this).hasClass('etim-stat-ico');

            // Only add the alert UI if we haven't already replaced it to prevent loops
            if (!$(this).hasClass('etim-img-failed')) {
                $(this).addClass('etim-img-failed').hide();
                $('<span class="etim-img-alert" title="' + src + '">Missing: ' + altText + '</span>').insertAfter(this);
            }
        });

        // Trigger error for already broken images that failed before JS loaded
        $('img').each(function () {
            if (!this.complete || (typeof this.naturalWidth !== "undefined" && this.naturalWidth === 0)) {
                $(this).trigger('error');
            }
        });
    }

    /**
     * Handle ETIM Sync functionality
     */
    function initSyncData() {
        $('#etim-sync-btn').on('click', function (e) {
            e.preventDefault();
            var $btn = $(this);
            var $status = $('#etim-sync-status');
            var $icon = $btn.find('img');
            var $text = $btn.find('span');

            // Show loading
            $btn.prop('disabled', true);
            $icon.css('animation', 'spin 1s linear infinite');
            $text.text('Syncing...');
            $status.hide();

            $.ajax({
                url: etimSettings.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'etim_sync_data',
                    nonce: etimSettings.nonce
                },
                success: function (response) {
                    $btn.prop('disabled', false);
                    $icon.css('animation', '');
                    $text.text('Sync Now');

                    if (response.success) {
                        $status.css('color', '#22c55e').text(response.data.message).fadeIn();
                        setTimeout(function () {
                            $status.fadeOut();
                        }, 5000);

                        // Possibly update the "Last Synced" text if it exists
                        // e.g., location.reload();
                    } else {
                        var errMsg = response.data && response.data.message ? response.data.message : 'Sync Failed';
                        $status.css('color', '#ef4444').text(errMsg).fadeIn();
                    }
                },
                error: function () {
                    $btn.prop('disabled', false);
                    $icon.css('animation', '');
                    $text.text('Sync Now');
                    $status.css('color', '#ef4444').text('An error occurred. Please try again.').fadeIn();
                }
            });
        });
    }

    /**
     * Initialize on document ready
     */
    $(document).ready(function () {
        initToggleSecret();
        initTestConnection();
        initImageFallback();
        initSyncData();
    });

})(jQuery);
