import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

// Global Drawer Handlers
window.openGlobalDrawer = function(drawerId, overlayId) {
    const $drawer = $(`#${drawerId}`);
    const $overlay = $(`#${overlayId}`);

    const mode = $drawer.data('mode') || 'add';
    
    $overlay.removeClass('opacity-0 pointer-events-none').addClass('opacity-100');
    $drawer.removeClass('translate-x-full');
    $('body').addClass('overflow-hidden');
};

window.closeGlobalDrawer = function(drawerId, overlayId) {
    const $drawer = drawerId ? $(`#${drawerId}`) : $('[id$="-drawer"]');
    const $overlay = overlayId ? $(`#${overlayId}`) : $('[id$="-overlay"], #drawer-overlay');

    $drawer.addClass('translate-x-full');
    $overlay.removeClass('opacity-100').addClass('opacity-0 pointer-events-none');
    $('body').removeClass('overflow-hidden');
};


$(document).ready(function() {
 
    $(document).on('click', '[id$="-overlay"], #drawer-overlay', function() {
        closeGlobalDrawer();
    });

    $(document).keydown(function(e) {
        if (e.key === 'Escape') closeGlobalDrawer();
    });
});