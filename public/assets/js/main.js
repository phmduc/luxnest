
document.addEventListener('DOMContentLoaded', function () {
    console.log('DucPham theme loaded.');

    // Smooth scroll for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const targetId = this.getAttribute('href').substring(1);
            const targetElement = document.getElementById(targetId);

            if (targetElement) {
                targetElement.scrollIntoView({
                    behavior: 'smooth'
                });
            }
        });
    });

    // Mobile menu toggle
    const mobileToggle = document.querySelector('.klook-mobile-toggle');
    const mobileSidebar = document.querySelector('.klook-mobile-sidebar');
    const mobileOverlay = document.querySelector('.klook-mobile-overlay');

    if (mobileToggle && mobileSidebar && mobileOverlay) {
        // Toggle menu
        mobileToggle.addEventListener('click', function () {
            this.classList.toggle('active');
            mobileSidebar.classList.toggle('active');
            mobileOverlay.classList.toggle('active');
            document.body.style.overflow = mobileSidebar.classList.contains('active') ? 'hidden' : '';
        });

        // Close menu when clicking overlay
        mobileOverlay.addEventListener('click', function () {
            mobileToggle.classList.remove('active');
            mobileSidebar.classList.remove('active');
            mobileOverlay.classList.remove('active');
            document.body.style.overflow = '';
        });
    }

    // Mobile search dropdown toggle
    const searchButton = document.querySelector('.klook-search-button');
    const mobileSearchDropdown = document.querySelector('.klook-mobile-search-dropdown');
    const mobileSearchClose = document.querySelector('.klook-mobile-search-close');

    if (searchButton && mobileSearchDropdown && mobileSearchClose) {
        // Open search dropdown on mobile (prevent form submission)
        searchButton.addEventListener('click', function (e) {
            if (window.innerWidth <= 768) {
                e.preventDefault();
                mobileSearchDropdown.classList.add('active');
            }
        });

        // Close search dropdown
        mobileSearchClose.addEventListener('click', function () {
            mobileSearchDropdown.classList.remove('active');
        });
    }

    // ── Popup Chain Logic (Promo Loop) ───────────────────
    const consultingPopup = document.getElementById('luxnest-consulting-popup');
    const consultingClose = document.getElementById('luxnest-consulting-close');
    const consultingContactBtn = document.getElementById('luxnest-consulting-contact-btn');
    
    const promoPopup = document.getElementById('luxnest-promo-popup');
    const promoClose = document.getElementById('luxnest-promo-close');
    const promoCheckbox = document.getElementById('luxnest-promo-dont-show');
    
    let promoTimer = null;

    const showPromo = () => {
        if (promoPopup && !sessionStorage.getItem('luxnest_promo_never_show')) {
            promoPopup.classList.remove('promo-hidden');
        }
    };

    const closePromo = () => {
        if (promoPopup) {
            promoPopup.classList.add('promo-hidden');
            if (promoCheckbox && promoCheckbox.checked) {
                sessionStorage.setItem('luxnest_promo_never_show', 'true');
            }
            
            // Loop back to Promo popup after 15s
            schedulePromo();
        }
    };

    const schedulePromo = () => {
        if (!sessionStorage.getItem('luxnest_promo_never_show')) {
            clearTimeout(promoTimer);
            promoTimer = setTimeout(showPromo, 30000); // 30 seconds
        }
    };

    const closeConsulting = (openChat = false) => {
        if (consultingPopup) {
            consultingPopup.classList.add('consulting-hidden');
            
            // Trigger 15s timer for promo popup AFTER consulting closes
            schedulePromo();

            if (openChat) {
                const chatToggle = document.getElementById('luxnest-ai-toggle');
                if (chatToggle && chatToggle.classList.contains('ai-closed')) {
                    chatToggle.click();
                }
            }
        }
    };

    // Start the sequence!
    if (consultingPopup || promoPopup) {
        
        if (consultingPopup) {
            // Consulting popup only shows ONCE after 10s, if chat is not already open
            setTimeout(() => {
                if (consultingPopup) {
                    const aiWindow = document.getElementById('luxnest-ai-window');
                    // Check if chat window is visible (doesn't have 'hidden' class)
                    const isChatActive = aiWindow && !aiWindow.classList.contains('hidden');

                    if (isChatActive) {
                        // Skip consulting popup and start promo loop
                        console.log('Chat is active, skipping consulting popup.');
                        schedulePromo();
                    } else {
                        consultingPopup.classList.remove('consulting-hidden');
                    }
                }
            }, 10000);
            
            if (consultingClose) consultingClose.addEventListener('click', () => closeConsulting(false));
            if (consultingContactBtn) consultingContactBtn.addEventListener('click', () => closeConsulting(true));
            const cOverlay = consultingPopup.querySelector('.consulting-overlay');
            if (cOverlay) cOverlay.addEventListener('click', () => closeConsulting(false));
        } else {
            // If consulting doesn't exist, start promo loop immediately
            schedulePromo();
        }

        if (promoPopup && promoClose) {
            promoClose.addEventListener('click', closePromo);
            const pOverlay = promoPopup.querySelector('.promo-overlay');
            if (pOverlay) pOverlay.addEventListener('click', closePromo);
        }
    }
});
