document.addEventListener("DOMContentLoaded", function() {
    const checkinInput = document.getElementById("checkin-date");
    const checkoutInput = document.getElementById("checkout-date");

    if (checkinInput && checkoutInput) {
        // Initialize Check-In Date Picker
        const checkinPicker = flatpickr(checkinInput, {
            dateFormat: "Y-m-d",
            minDate: "today",
            disableMobile: true, // Force the beautiful flatpickr UI on mobile instead of native
            onChange: function(selectedDates, dateStr, instance) {
                // When Check-In is selected, update Check-Out minimum date
                if (selectedDates.length > 0) {
                    // Set checkout minDate to the day after check-in
                    const nextDay = new Date(selectedDates[0]);
                    nextDay.setDate(nextDay.getDate() + 1);
                    checkoutPicker.set("minDate", nextDay);
                    
                    // If current checkout is before new minDate, clear it
                    if (checkoutPicker.selectedDates.length > 0 && checkoutPicker.selectedDates[0] <= selectedDates[0]) {
                        checkoutPicker.clear();
                    }
                    
                    // Automatically open checkout picker for convenience
                    setTimeout(() => checkoutPicker.open(), 100);
                }
            }
        });

        // Initialize Check-Out Date Picker
        const checkoutPicker = flatpickr(checkoutInput, {
            dateFormat: "Y-m-d",
            minDate: new Date().fp_incr(1), // Tomorrow
            disableMobile: true,
        });
    }
});
