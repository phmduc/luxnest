/**
 * LuxNest Branches Swiper Initialization - Simple Version
 */

document.addEventListener('DOMContentLoaded', function () {
    // Check if branches swiper exists
    const branchesSlider = document.querySelector('.branches-swiper');

    if (!branchesSlider) {
        return; // Exit if slider doesn't exist on this page
    }

    // Initialize Branches Swiper - Simple configuration
    const branchesSwiper = new Swiper('.branches-swiper', {
        // Slides per view
        slidesPerView: 1,
        spaceBetween: 32,

        // Speed
        speed: 600,

        // No loop - simpler and more reliable
        loop: false,

        // Slide one at a time
        slidesPerGroup: 1,

        // Navigation arrows
        navigation: {
            nextEl: '.branches-nav-next',
            prevEl: '.branches-nav-prev',
        },

        // Pagination
        pagination: {
            el: '.branches-pagination',
            clickable: true,
        },

        // Keyboard control
        keyboard: {
            enabled: true,
        },

        // Touch/Swipe
        grabCursor: true,

        // Responsive breakpoints
        breakpoints: {
            // Mobile: 1 slide
            320: {
                slidesPerView: 1,
                spaceBetween: 16
            },
            // Tablet: 2 slides
            768: {
                slidesPerView: 2,
                spaceBetween: 24
            },
            // Desktop: 3 slides
            1024: {
                slidesPerView: 3,
                spaceBetween: 32
            }
        }
    });

    console.log('Branches slider initialized (simple mode)');
});
