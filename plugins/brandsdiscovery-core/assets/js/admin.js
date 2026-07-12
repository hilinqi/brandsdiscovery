/**
 * BrandsDiscovery Core Admin Scripts.
 *
 * @package BrandsDiscovery_Core
 */

(function($) {
    'use strict';

    var apiUrl = bdcAdmin.apiUrl;
    var nonce = bdcAdmin.nonce;

    /**
     * Approve a brand.
     */
    $(document).on('click', '.bdc-approve', function(e) {
        e.preventDefault();
        var id = $(this).data('id');
        if (confirm(bdcAdmin.messages.confirmDelete ? 'Approve this brand? / 确定通过此品牌？' : 'Approve this brand?')) {
            $.ajax({
                url: apiUrl + 'admin/brands/' + id + '/status',
                method: 'PUT',
                data: { status: 'published' },
                beforeSend: function(xhr) {
                    xhr.setRequestHeader('X-WP-Nonce', nonce);
                },
                success: function() {
                    alert(bdcAdmin.messages.saved);
                    location.reload();
                },
                error: function() {
                    alert(bdcAdmin.messages.error);
                }
            });
        }
    });

    /**
     * Reject a brand.
     */
    $(document).on('click', '.bdc-reject', function(e) {
        e.preventDefault();
        var id = $(this).data('id');
        if (confirm('Reject this brand? / 确定拒绝此品牌？')) {
            $.ajax({
                url: apiUrl + 'admin/brands/' + id + '/status',
                method: 'PUT',
                data: { status: 'rejected' },
                beforeSend: function(xhr) {
                    xhr.setRequestHeader('X-WP-Nonce', nonce);
                },
                success: function() {
                    alert(bdcAdmin.messages.saved);
                    location.reload();
                },
                error: function() {
                    alert(bdcAdmin.messages.error);
                }
            });
        }
    });

    /**
     * Approve a claim.
     */
    $(document).on('click', '.bdc-approve-claim', function() {
        var id = $(this).data('id');
        if (confirm('Approve this claim? / 确定通过此认领？')) {
            $.ajax({
                url: apiUrl + 'admin/claims/' + id,
                method: 'PUT',
                data: { status: 'approved' },
                beforeSend: function(xhr) {
                    xhr.setRequestHeader('X-WP-Nonce', nonce);
                },
                success: function() {
                    alert(bdcAdmin.messages.saved);
                    location.reload();
                },
                error: function() {
                    alert(bdcAdmin.messages.error);
                }
            });
        }
    });

    /**
     * Reject a claim.
     */
    $(document).on('click', '.bdc-reject-claim', function() {
        var id = $(this).data('id');
        var notes = prompt('Reason for rejection / 拒绝原因:');
        if (notes !== null) {
            $.ajax({
                url: apiUrl + 'admin/claims/' + id,
                method: 'PUT',
                data: { status: 'rejected', reviewer_notes: notes },
                beforeSend: function(xhr) {
                    xhr.setRequestHeader('X-WP-Nonce', nonce);
                },
                success: function() {
                    alert(bdcAdmin.messages.saved);
                    location.reload();
                },
                error: function() {
                    alert(bdcAdmin.messages.error);
                }
            });
        }
    });

    /**
     * Revoke a claim.
     */
    $(document).on('click', '.bdc-revoke-claim', function() {
        var id = $(this).data('id');
        if (confirm('Revoke this claim? / 确定撤销此认领？')) {
            $.ajax({
                url: apiUrl + 'admin/claims/' + id,
                method: 'PUT',
                data: { status: 'revoked' },
                beforeSend: function(xhr) {
                    xhr.setRequestHeader('X-WP-Nonce', nonce);
                },
                success: function() {
                    alert(bdcAdmin.messages.saved);
                    location.reload();
                },
                error: function() {
                    alert(bdcAdmin.messages.error);
                }
            });
        }
    });

})(jQuery);
