/**
 * BrandsDiscovery Theme — Main JavaScript.
 *
 * @package BrandsDiscovery
 */

(function() {
    'use strict';

    // Mobile menu toggle.
    var toggle = document.querySelector('.mobile-menu-toggle');
    var nav = document.querySelector('.main-nav');

    if (toggle && nav) {
        toggle.addEventListener('click', function() {
            var expanded = toggle.getAttribute('aria-expanded') === 'true';
            toggle.setAttribute('aria-expanded', !expanded);
            nav.style.display = expanded ? '' : 'flex';
            if (!expanded) {
                nav.style.flexDirection = 'column';
                nav.style.position = 'absolute';
                nav.style.top = '100%';
                nav.style.left = '0';
                nav.style.right = '0';
                nav.style.background = '#1B2A4A';
                nav.style.padding = '16px';
                nav.style.gap = '12px';
            }
        });
    }

    // Visit Store click tracking (delegated).
    document.addEventListener('click', function(e) {
        var link = e.target.closest('[data-track-visit]');
        if (!link) return;

        e.preventDefault();
        var brandId = link.getAttribute('data-track-visit');

        if (window.bdTheme && bdTheme.apiUrl) {
            fetch(bdTheme.apiUrl + 'visit/' + brandId, {
                method: 'POST',
                headers: { 'X-WP-Nonce': bdTheme.nonce || '' }
            })
            .then(function(res) { return res.json(); })
            .then(function(data) {
                if (data.redirect_url) {
                    window.open(data.redirect_url, '_blank', 'noopener,noreferrer');
                }
            })
            .catch(function() {
                window.open(link.href, '_blank', 'noopener,noreferrer');
            });
        } else {
            window.open(link.href, '_blank', 'noopener,noreferrer');
        }
    });

})();
