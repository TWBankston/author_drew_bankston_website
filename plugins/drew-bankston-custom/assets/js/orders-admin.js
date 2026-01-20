/**
 * Orders Admin Panel JavaScript
 * Drew Bankston Custom Plugin
 */

(function($) {
    'use strict';

    // Initialize on document ready
    $(document).ready(function() {
        initStatusSelects();
        initViewButtons();
        initModal();
    });

    /**
     * Initialize status select dropdowns
     */
    function initStatusSelects() {
        $('.order-status-select').on('change', function() {
            var $select = $(this);
            var orderId = $select.data('order-id');
            var newStatus = $select.val();
            var $row = $select.closest('tr');
            
            // Show loading state
            $select.prop('disabled', true);
            
            $.ajax({
                url: dbcOrders.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'dbc_update_order_status',
                    nonce: dbcOrders.nonce,
                    order_id: orderId,
                    status: newStatus
                },
                success: function(response) {
                    if (response.success) {
                        // Update status badge
                        var $statusCell = $row.find('.column-status');
                        $statusCell.find('.order-status')
                            .removeClass('order-status--pending order-status--processing order-status--shipped order-status--completed order-status--cancelled')
                            .addClass('order-status--' + newStatus)
                            .text(newStatus.charAt(0).toUpperCase() + newStatus.slice(1));
                        
                        // Highlight row
                        $row.addClass('updated');
                        setTimeout(function() {
                            $row.removeClass('updated');
                        }, 2000);
                        
                        // Show success notice
                        showNotice('Order #' + orderId + ' updated to ' + newStatus, 'success');
                    } else {
                        showNotice(response.data.message || 'Failed to update order', 'error');
                    }
                },
                error: function() {
                    showNotice('Connection error. Please try again.', 'error');
                },
                complete: function() {
                    $select.prop('disabled', false);
                }
            });
        });
    }

    /**
     * Initialize view order buttons
     */
    function initViewButtons() {
        $('.view-order').on('click', function(e) {
            e.preventDefault();
            var orderId = $(this).data('order-id');
            openOrderModal(orderId);
        });
    }

    /**
     * Initialize modal functionality
     */
    function initModal() {
        var $modal = $('#order-modal');
        var $body = $('#order-modal-body');
        
        // Close modal on backdrop click
        $modal.find('.dbc-modal__backdrop').on('click', function() {
            closeModal();
        });
        
        // Close modal on close button click
        $modal.find('.dbc-modal__close').on('click', function() {
            closeModal();
        });
        
        // Close modal on escape key
        $(document).on('keydown', function(e) {
            if (e.key === 'Escape' && $modal.is(':visible')) {
                closeModal();
            }
        });
    }

    /**
     * Open order details modal
     */
    function openOrderModal(orderId) {
        var $modal = $('#order-modal');
        var $body = $('#order-modal-body');
        
        // Show modal with loading state
        $body.html('<div class="dbc-loading"></div>');
        $modal.fadeIn(200);
        $('body').css('overflow', 'hidden');
        
        // Fetch order details
        $.ajax({
            url: dbcOrders.ajaxUrl,
            type: 'GET',
            data: {
                action: 'dbc_get_order_details',
                nonce: dbcOrders.nonce,
                order_id: orderId
            },
            success: function(response) {
                if (response.success) {
                    $body.html(response.data.html);
                    initModalForm();
                } else {
                    $body.html('<div class="notice notice-error"><p>' + (response.data.message || 'Failed to load order') + '</p></div>');
                }
            },
            error: function() {
                $body.html('<div class="notice notice-error"><p>Connection error. Please try again.</p></div>');
            }
        });
    }

    /**
     * Close modal
     */
    function closeModal() {
        var $modal = $('#order-modal');
        $modal.fadeOut(200);
        $('body').css('overflow', '');
    }

    /**
     * Initialize modal form functionality
     */
    function initModalForm() {
        var $form = $('#update-order-form');
        var $statusSelect = $form.find('#modal-status');
        var $shippingFields = $form.find('.shipping-fields');
        
        // Toggle shipping fields based on status
        $statusSelect.on('change', function() {
            var status = $(this).val();
            if (status === 'shipped' || status === 'completed') {
                $shippingFields.slideDown(200);
            } else {
                $shippingFields.slideUp(200);
            }
        });
        
        // Handle form submission
        $form.on('submit', function(e) {
            e.preventDefault();
            
            var $submitBtn = $form.find('button[type="submit"]');
            var originalText = $submitBtn.text();
            
            // Show loading state
            $submitBtn.prop('disabled', true).text('Updating...');
            
            var formData = {
                action: 'dbc_update_order_status',
                nonce: dbcOrders.nonce,
                order_id: $form.find('input[name="order_id"]').val(),
                status: $form.find('select[name="status"]').val(),
                tracking_number: $form.find('input[name="tracking_number"]').val(),
                tracking_carrier: $form.find('select[name="tracking_carrier"]').val(),
                admin_notes: $form.find('textarea[name="admin_notes"]').val()
            };
            
            $.ajax({
                url: dbcOrders.ajaxUrl,
                type: 'POST',
                data: formData,
                success: function(response) {
                    if (response.success) {
                        showNotice('Order updated successfully!', 'success');
                        
                        // Update the table row
                        var $row = $('tr[data-order-id="' + formData.order_id + '"]');
                        if ($row.length) {
                            var newStatus = formData.status;
                            
                            // Update status badge
                            $row.find('.column-status .order-status')
                                .removeClass('order-status--pending order-status--processing order-status--shipped order-status--completed order-status--cancelled')
                                .addClass('order-status--' + newStatus)
                                .text(newStatus.charAt(0).toUpperCase() + newStatus.slice(1));
                            
                            // Update dropdown
                            $row.find('.order-status-select').val(newStatus);
                            
                            // Highlight row
                            $row.addClass('updated');
                        }
                        
                        // Update modal status badge
                        var $modalStatus = $('.order-details__header .order-status');
                        $modalStatus
                            .removeClass('order-status--pending order-status--processing order-status--shipped order-status--completed order-status--cancelled')
                            .addClass('order-status--' + formData.status)
                            .text(formData.status.charAt(0).toUpperCase() + formData.status.slice(1));
                        
                    } else {
                        showNotice(response.data.message || 'Failed to update order', 'error');
                    }
                },
                error: function() {
                    showNotice('Connection error. Please try again.', 'error');
                },
                complete: function() {
                    $submitBtn.prop('disabled', false).text(originalText);
                }
            });
        });
    }

    /**
     * Show admin notice
     */
    function showNotice(message, type) {
        var $notice = $('<div class="notice notice-' + type + ' is-dismissible"><p>' + message + '</p></div>');
        
        // Remove existing notices
        $('.dbc-orders-wrap > .notice').remove();
        
        // Add new notice
        $('.dbc-orders-wrap h1').after($notice);
        
        // Auto dismiss after 5 seconds
        setTimeout(function() {
            $notice.fadeOut(300, function() {
                $(this).remove();
            });
        }, 5000);
        
        // Make dismissible
        $notice.on('click', '.notice-dismiss', function() {
            $notice.fadeOut(300, function() {
                $(this).remove();
            });
        });
    }

})(jQuery);
