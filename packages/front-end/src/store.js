// Pinia Store
const { defineStore } = Pinia;

export const useCalendarStore = defineStore('calendar', {
    state: () => ({
        events: {},
        loading: false,
        showBookingModal: false,
        selectedEvent: null,
        bookingForm: {
            name: '',
            email: '',
            phone: '',
            payment_type: ''
        },
        bookingLoading: false
    }),

    actions: {
        async fetchEvents() {
            this.loading = true;
            try {
                const formData = new FormData();
                formData.append('action', 'get_calendar_events');
                formData.append('nonce', wellness_ajax.nonce);

                const response = await fetch(wellness_ajax.ajax_url, {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();
                if (data.success) {
                    this.events = data.data;
                } else {
                    console.error('Failed to fetch events:', data.data);
                }
            } catch (error) {
                console.error('Error fetching events:', error);
            } finally {
                this.loading = false;
            }
        },

        openBookingModal(event) {
            this.selectedEvent = event;
            this.showBookingModal = true;
            this.resetBookingForm();
        },

        closeBookingModal() {
            this.showBookingModal = false;
            this.selectedEvent = null;
            this.resetBookingForm();
        },

        resetBookingForm() {
            this.bookingForm = {
                name: '',
                email: '',
                phone: '',
                payment_type: ''
            };
        },

        async submitBooking() {
            this.bookingLoading = true;
            try {
                const formData = new FormData();
                formData.append('action', 'book_event');
                formData.append('nonce', wellness_ajax.nonce);
                formData.append('event_id', this.selectedEvent.id);
                formData.append('name', this.bookingForm.name);
                formData.append('email', this.bookingForm.email);
                formData.append('phone', this.bookingForm.phone);
                formData.append('payment_type', this.bookingForm.payment_type);

                const response = await fetch(wellness_ajax.ajax_url, {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();
                if (data.success) {
                    alert(data.data);
                    this.closeBookingModal();
                    this.fetchEvents(); // Refresh events
                } else {
                    alert(data.data);
                }
            } catch (error) {
                console.error('Error submitting booking:', error);
                alert('Възникна грешка при записването. Моля опитайте отново.');
            } finally {
                this.bookingLoading = false;
            }
        }
    }
});