/**
 * Hero Banner Swiper Initialization
 */

document.addEventListener('DOMContentLoaded', function () {
    // Initialize Hero Swiper
    const heroSwiper = new Swiper('.hero-swiper', {
        // Effect
        effect: 'fade',
        fadeEffect: {
            crossFade: true
        },

        // Autoplay
        autoplay: {
            delay: 5000,
            disableOnInteraction: false,
            pauseOnMouseEnter: true
        },

        // Speed
        speed: 800,

        // Loop
        loop: true,

        // Lazy Loading
        lazy: {
            loadPrevNext: true,
            loadPrevNextAmount: 1
        },

        // Navigation arrows
        navigation: {
            nextEl: '.hero-nav-next',
            prevEl: '.hero-nav-prev',
        },

        // Pagination
        pagination: {
            el: '.hero-pagination',
            clickable: true,
            dynamicBullets: false
        },

        // Keyboard control
        keyboard: {
            enabled: true,
            onlyInViewport: true
        },

        // Accessibility
        a11y: {
            prevSlideMessage: 'Previous slide',
            nextSlideMessage: 'Next slide',
            paginationBulletMessage: 'Go to slide {{index}}'
        },

        // Callbacks
        on: {
            init: function () {
                console.log('Hero slider initialized');

                // Trigger animations on first slide immediately
                setTimeout(() => {
                    const activeSlide = document.querySelector('.swiper-slide-active');
                    if (activeSlide) {
                        const heading = activeSlide.querySelector('.hero-heading');
                        const subheading = activeSlide.querySelector('.hero-subheading');
                        const buttons = activeSlide.querySelector('.hero-buttons');

                        setTimeout(() => {
                            if (heading) heading.style.opacity = '1';
                        }, 100);

                        setTimeout(() => {
                            if (subheading) subheading.style.opacity = '1';
                        }, 300);

                        setTimeout(() => {
                            if (buttons) buttons.style.opacity = '1';
                        }, 500);
                    }
                }, 100);
            },
            slideChange: function () {
                // Reset animations on slide change
                const slides = document.querySelectorAll('.hero-swiper .swiper-slide');
                slides.forEach(slide => {
                    const heading = slide.querySelector('.hero-heading');
                    const subheading = slide.querySelector('.hero-subheading');
                    const buttons = slide.querySelector('.hero-buttons');

                    if (heading) heading.style.opacity = '0';
                    if (subheading) subheading.style.opacity = '0';
                    if (buttons) buttons.style.opacity = '0';
                });
            },
            slideChangeTransitionEnd: function () {
                // Trigger animations on active slide
                const activeSlide = document.querySelector('.swiper-slide-active');
                if (activeSlide) {
                    const heading = activeSlide.querySelector('.hero-heading');
                    const subheading = activeSlide.querySelector('.hero-subheading');
                    const buttons = activeSlide.querySelector('.hero-buttons');

                    setTimeout(() => {
                        if (heading) heading.style.opacity = '1';
                    }, 100);

                    setTimeout(() => {
                        if (subheading) subheading.style.opacity = '1';
                    }, 300);

                    setTimeout(() => {
                        if (buttons) buttons.style.opacity = '1';
                    }, 500);
                }
            }
        }
    });

    // Pause autoplay when user interacts with navigation
    const navButtons = document.querySelectorAll('.hero-nav-prev, .hero-nav-next');
    navButtons.forEach(button => {
        button.addEventListener('click', function () {
            heroSwiper.autoplay.stop();
            setTimeout(() => {
                heroSwiper.autoplay.start();
            }, 10000); // Resume after 10 seconds
        });
    });
});
