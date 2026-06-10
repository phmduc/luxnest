/**
 * Featured Rooms - Wishlist Functionality
 */

(function ($) {
    'use strict';

    // Storage key for wishlist
    const WISHLIST_KEY = 'luxnest_wishlist';

    /**
     * Get wishlist from localStorage
     */
    function getWishlist() {
        try {
            const wishlist = localStorage.getItem(WISHLIST_KEY);
            return wishlist ? JSON.parse(wishlist) : [];
        } catch (error) {
            console.error('Error reading wishlist:', error);
            return [];
        }
    }

    /**
     * Save wishlist to localStorage
     */
    function saveWishlist(wishlist) {
        try {
            localStorage.setItem(WISHLIST_KEY, JSON.stringify(wishlist));
            return true;
        } catch (error) {
            console.error('Error saving wishlist:', error);
            return false;
        }
    }

    /**
     * Check if room is in wishlist
     */
    function isInWishlist(roomId) {
        const wishlist = getWishlist();
        return wishlist.includes(roomId);
    }

    /**
     * Add room to wishlist
     */
    function addToWishlist(roomId) {
        const wishlist = getWishlist();
        if (!wishlist.includes(roomId)) {
            wishlist.push(roomId);
            saveWishlist(wishlist);
            return true;
        }
        return false;
    }

    /**
     * Remove room from wishlist
     */
    function removeFromWishlist(roomId) {
        let wishlist = getWishlist();
        const index = wishlist.indexOf(roomId);
        if (index > -1) {
            wishlist.splice(index, 1);
            saveWishlist(wishlist);
            return true;
        }
        return false;
    }

    /**
     * Toggle wishlist button state
     */
    function toggleWishlistButton($button, isActive) {
        if (isActive) {
            $button.addClass('active');
            $button.attr('aria-label', 'Remove from wishlist');
        } else {
            $button.removeClass('active');
            $button.attr('aria-label', 'Add to wishlist');
        }
    }

    /**
     * Handle wishlist button click
     */
    function handleWishlistClick(event) {
        event.preventDefault();
        event.stopPropagation();

        const $button = $(this);
        const roomId = $button.data('room-id');

        if (!roomId && roomId !== 0) {
            console.error('Room ID not found');
            return;
        }

        const roomIdStr = String(roomId);

        // If user is logged in, sync with server
        if (typeof luxnest_ajax !== 'undefined' && luxnest_ajax.is_logged_in) {
            $button.addClass('animating');
            
            $.ajax({
                url: luxnest_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'luxnest_toggle_wishlist',
                    room_id: roomIdStr
                },
                success: function(response) {
                    if (response.success) {
                        const isActive = (response.data.status === 'added');
                        toggleWishlistButton($button, isActive);
                        
                        // Sync localStorage too for consistency
                        if (isActive) {
                            addToWishlist(roomIdStr);
                            $(document).trigger('luxnest:wishlist:added', [roomIdStr]);
                        } else {
                            removeFromWishlist(roomIdStr);
                            $(document).trigger('luxnest:wishlist:removed', [roomIdStr]);
                        }
                    } else {
                        console.error('Wishlist error:', response.data);
                    }
                    $button.removeClass('animating');
                },
                error: function() {
                    $button.removeClass('animating');
                }
            });
            return;
        }

        const isCurrentlyInWishlist = isInWishlist(roomIdStr);

        // Add animation class
        $button.addClass('animating');

        if (isCurrentlyInWishlist) {
            // Remove from wishlist
            if (removeFromWishlist(roomIdStr)) {
                toggleWishlistButton($button, false);

                // Trigger custom event
                $(document).trigger('luxnest:wishlist:removed', [roomIdStr]);
            }
        } else {
            // Add to wishlist
            if (addToWishlist(roomIdStr)) {
                toggleWishlistButton($button, true);

                // Trigger custom event
                $(document).trigger('luxnest:wishlist:added', [roomIdStr]);
            }
        }

        // Remove animation class after animation completes
        setTimeout(function () {
            $button.removeClass('animating');
        }, 300);
    }

    /**
     * Initialize wishlist buttons
     */
    function initWishlistButtons() {
        const $wishlistButtons = $('.wishlist-btn');

        if ($wishlistButtons.length === 0) {
            return;
        }

        // If logged in, fetch from server to sync UI
        if (typeof luxnest_ajax !== 'undefined' && luxnest_ajax.is_logged_in) {
            $.ajax({
                url: luxnest_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'luxnest_get_wishlist_ids'
                },
                success: function(response) {
                    if (response.success && response.data.ids) {
                        const serverIds = response.data.ids.map(String);
                        saveWishlist(serverIds);
                        
                        $wishlistButtons.each(function() {
                            const $button = $(this);
                            const roomId = String($button.data('room-id'));
                            toggleWishlistButton($button, serverIds.includes(roomId));
                        });
                    }
                }
            });
        } else {
            // Set initial state based on local saved wishlist (for guests)
            $wishlistButtons.each(function () {
                const $button = $(this);
                const roomId = String($button.data('room-id'));

                if (isInWishlist(roomId)) {
                    toggleWishlistButton($button, true);
                }
            });
        }

        // Attach click handlers
        $wishlistButtons.on('click', handleWishlistClick);
    }

    /**
     * Prevent card click when clicking wishlist button
     */
    function preventCardClickPropagation() {
        $('.room-card').on('click', '.wishlist-btn', function (event) {
            event.stopPropagation();
        });
    }

    /**
     * Optional: Make entire card clickable (navigate to booking link)
     */
    function initCardClick() {
        $('.room-card').on('click', function (event) {
            // Don't trigger if clicking on wishlist button or book button
            if ($(event.target).closest('.wishlist-btn, .book-btn').length > 0) {
                return;
            }

            const $bookBtn = $(this).find('.book-btn');
            if ($bookBtn.length > 0) {
                const href = $bookBtn.attr('href');
                if (href && href !== '#') {
                    window.location.href = href;
                }
            }
        });
    }

    /**
     * Add animation class to wishlist button
     */
    function addWishlistAnimation() {
        const style = document.createElement('style');
        style.textContent = `
            .wishlist-btn.animating {
                animation: wishlist-pulse 0.3s ease;
            }
            
            @keyframes wishlist-pulse {
                0% { transform: scale(1); }
                50% { transform: scale(1.2); }
                100% { transform: scale(1); }
            }
        `;
        document.head.appendChild(style);
    }

    /**
     * Initialize on document ready
     */
    function initAll() {
        // Initialize wishlist functionality
        // Remove existing handlers first if re-initializing
        $('.wishlist-btn').off('click', handleWishlistClick);
        initWishlistButtons();
        preventCardClickPropagation();
        initCardClick();
    }

    $(document).ready(function () {
        // Add animation styles
        addWishlistAnimation();

        initAll();

        // Log wishlist count (for debugging)
        const wishlistCount = getWishlist().length;
        if (wishlistCount > 0) {
            console.log('LuxNest: ' + wishlistCount + ' room(s) in wishlist');
        }
    });
    
    $(document).on('luxnest_ajax_loaded', function() {
        initAll();
    });

    /**
     * Optional: Listen for custom events
     * Other scripts can listen to these events to update UI, analytics, etc.
     */
    $(document).on('luxnest:wishlist:added', function (event, roomId) {
        console.log('Room added to wishlist:', roomId);
        // You can add analytics tracking here
        // Example: gtag('event', 'add_to_wishlist', { room_id: roomId });
    });

    $(document).on('luxnest:wishlist:removed', function (event, roomId) {
        console.log('Room removed from wishlist:', roomId);
        // You can add analytics tracking here
        // Example: gtag('event', 'remove_from_wishlist', { room_id: roomId });
    });

    /**
     * Expose public API (optional)
     */
    window.LuxNestWishlist = {
        get: getWishlist,
        add: addToWishlist,
        remove: removeFromWishlist,
        isInWishlist: isInWishlist,
        count: function () {
            return getWishlist().length;
        },
        clear: function () {
            localStorage.removeItem(WISHLIST_KEY);
            $('.wishlist-btn').each(function () {
                toggleWishlistButton($(this), false);
            });
        }
    };

})(jQuery);
