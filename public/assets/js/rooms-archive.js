/**
 * Rooms Archive - Branch Filtering, Sorting, Price Range & Availability Check
 */

function initRoomsArchive() {
    // Elements
    const roomItems         = document.querySelectorAll('.room-item-horizontal');
    const sortSelect        = document.querySelector('.custom-select'); // Updated for new UI
    const emptyState        = document.querySelector('.empty-state');
    const roomsContainer    = document.querySelector('.rooms-list-container');
    
    // Price Filter Elements
    const minRange = document.getElementById('price-min-range');
    const maxRange = document.getElementById('price-max-range');
    const minInput = document.getElementById('price-min-input');
    const maxInput = document.getElementById('price-max-input');
    const resetPriceBtn = document.getElementById('reset-price-filter');

    if (!roomsContainer) return;

    // State
    const areaCheckboxes = document.querySelectorAll('.area-checkbox input[type="checkbox"]');

    let currentMinPrice = minRange ? parseInt(minRange.value) : 0;
    let currentMaxPrice = maxRange ? parseInt(maxRange.value) : 20000000;

    // ───────────────────────────────────────────────
    //  Filtering Logic
    // ───────────────────────────────────────────────
    function updateFilters() {
        let visibleCount = 0;
        
        // Get active area checkboxes
        const activeAreas = Array.from(areaCheckboxes)
                                 .filter(cb => cb.checked)
                                 .map(cb => cb.value);

        roomItems.forEach(function(item) {
            const itemBranch = item.dataset.branch;
            const itemPrice  = parseInt(item.dataset.price || '0');
            
            const matchesArea   = activeAreas.length === 0 || activeAreas.includes(itemBranch);
            const matchesPrice  = (itemPrice >= currentMinPrice && itemPrice <= currentMaxPrice);

            if (matchesArea && matchesPrice) {
                item.style.display = 'flex';
                visibleCount++;
            } else {
                item.style.display = 'none';
            }
        });

        if (visibleCount === 0) {
            if (emptyState) emptyState.style.display = 'flex';
        } else {
            if (emptyState) emptyState.style.display = 'none';
        }
        
        // Update the count display in the header summary if it exists
        const countDisplay = document.querySelector('.result-count');
        if (countDisplay) {
            countDisplay.textContent = visibleCount + ' nơi lưu trú được tìm thấy';
        }
    }

    // Bind area checkboxes
    areaCheckboxes.forEach(function(cb) {
        cb.addEventListener('change', updateFilters);
    });

    // ───────────────────────────────────────────────
    //  Price Slider Interaction
    // ───────────────────────────────────────────────
    if (minRange && maxRange) {
        const sliderTrack = document.querySelector('.slider-track');
        
        function handlePriceChange() {
            let minVal = parseInt(minRange.value);
            let maxVal = parseInt(maxRange.value);
            const totalRange = parseInt(maxRange.max) - parseInt(maxRange.min);

            // Prevent crossing
            if (minVal > maxVal - 100000) {
                if (this === minRange) {
                    minRange.value = maxVal - 100000;
                    minVal = maxVal - 100000;
                } else {
                    maxRange.value = minVal + 100000;
                    maxVal = minVal + 100000;
                }
            }

            // Update Track Visualization
            if (sliderTrack) {
                const minPercent = ((minVal - minRange.min) / totalRange) * 100;
                const maxPercent = ((maxVal - minRange.min) / totalRange) * 100;
                sliderTrack.style.background = `linear-gradient(to right, #e9f0ff ${minPercent}%, #3b71fe ${minPercent}%, #3b71fe ${maxPercent}%, #e9f0ff ${maxPercent}%)`;
            }

            currentMinPrice = minVal;
            currentMaxPrice = maxVal;
            
            if (minInput) minInput.value = minVal;
            if (maxInput) maxInput.value = maxVal;

            updateFilters();
        }

        minRange.addEventListener('input', handlePriceChange);
        maxRange.addEventListener('input', handlePriceChange);

        if (minInput) {
            minInput.addEventListener('change', function() {
                minRange.value = this.value;
                handlePriceChange.call(minRange);
            });
        }
        if (maxInput) {
            maxInput.addEventListener('change', function() {
                maxRange.value = this.value;
                handlePriceChange.call(maxRange);
            });
        }
        
        // Initial setup for tracker
        handlePriceChange.call(minRange);
    }

    if (resetPriceBtn) {
        resetPriceBtn.addEventListener('click', function() {
            if (minRange) {
                minRange.value = minRange.min;
                currentMinPrice = parseInt(minRange.min);
                if (minInput) minInput.value = minRange.min;
            }
            if (maxRange) {
                maxRange.value = maxRange.max;
                currentMaxPrice = parseInt(maxRange.max);
                if (maxInput) maxInput.value = maxRange.max;
            }
            const evt = new Event('input');
            if (minRange) minRange.dispatchEvent(evt);
            updateFilters();
        });
    }

    // ───────────────────────────────────────────────
    //  Sorting
    // ───────────────────────────────────────────────
    function sortRooms(sortBy) {
        const itemsArray = Array.from(roomItems);

        itemsArray.sort(function(a, b) {
            const priceA = parseInt(a.dataset.price || '0');
            const priceB = parseInt(b.dataset.price || '0');
            const nameA = (a.querySelector('.room-name') ? a.querySelector('.room-name').textContent : '').trim();
            const nameB = (b.querySelector('.room-name') ? b.querySelector('.room-name').textContent : '').trim();

            switch (sortBy) {
                case 'Giá thấp nhất':  return priceA - priceB;
                case 'Giá cao nhất': return priceB - priceA;
                case 'Độ phổ biến': return 0; // Simplified
                default: return 0;
            }
        });

        itemsArray.forEach(function(item) { roomsContainer.appendChild(item); });
        updateFilters();
    }

    // ───────────────────────────────────────────────
    //  Event Listeners
    // ───────────────────────────────────────────────
    if (sortSelect) {
        sortSelect.addEventListener('change', function () {
            sortRooms(this.value);
        });
    }

    // ───────────────────────────────────────────────
    //  Wishlist Icon Sync Check
    // ───────────────────────────────────────────────
    if (window.jQuery) {
        setTimeout(() => {
            // Heart states handled by featured-rooms.js
        }, 500);
    }

    updateFilters();
}

document.addEventListener('DOMContentLoaded', initRoomsArchive);
document.addEventListener('luxnest_ajax_loaded', initRoomsArchive);
