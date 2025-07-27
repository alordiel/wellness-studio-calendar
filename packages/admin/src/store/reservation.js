import {defineStore} from 'pinia'

export const useReservationStore = defineStore('reservations', {
    // State
    state: () => ({
        reservations: [
            {
                id: 1,
                event: 1, // Morning Yoga
                user_name: 'Alice Johnson',
                email: 'alice@example.com',
                phone: '555-123-4567',
                payment_method: 'credit_card',
                cancelled_by: null,
                user_notes: 'I have some lower back issues, please advise.',
                created_at: '2024-12-23',
                admin_notes: [{author: 'Semana',created_at: '14:22:00 23-01-2999', content: 'First-time participant, offer intro package.'}],
            },
            {
                id: 2,
                event: 2, // Boxing Class
                user_name: 'Bob Williams',
                email: 'bob@example.com',
                phone: '555-234-5678',
                payment_method: 'paypal',
                cancelled_by: null,
                user_notes: '',
                created_at: '2024-12-23',
                admin_notes: [{author: 'Semana',created_at: '14:22:00 23-01-2999', content: 'Regular attendee, membership expires in 2 weeks.'}],
            },
            {
                id: 3,
                event: 3, // Pilates
                user_name: 'Carol Martinez',
                email: 'carol@example.com',
                phone: '555-345-6789',
                payment_method: 'cash',
                cancelled_by: 'user',
                user_notes: 'May be 5 minutes late.',
                created_at: '2024-12-23',
                admin_notes: [{author: 'Semana',created_at: '14:22:00 23-01-2999', content: ''}],
            },
            {
                id: 4,
                event: 4, // Zumba
                user_name: 'David Lee',
                email: 'david@example.com',
                phone: '555-456-7890',
                payment_method: 'credit_card',
                cancelled_by: null,
                user_notes: '',
                created_at: '2024-12-23',
                admin_notes: [{author: 'Semana',created_at: '14:22:00 23-01-2999', content: 'New member discount applied.'}],
            }
        ]
    }),

    getters: {
        getAllReservations: (state) => {
            return [...state.reservations]
        },
        getReservationById: (state) => {
            return (id) => {
                return state.reservations.find(reservation => reservation.id === id) || null
            }
        },
        getReservationsByEvent: (state) => {
            return (eventId) => {
                return state.reservations.filter(reservation => reservation.event === eventId)
            }
        },
        getActiveReservations: (state) => {
            return state.reservations.filter(reservation => reservation.cancelled_by === null)
        },
        getNumberOfReservations: (state) => {
            return state.reservations.filter(reservation => reservation.cancelled_by === null).length;
        }
    },

    actions: {
        async addReservation(reservation) {
            this.reservations.push(reservation)
            // Example: await saveReservationToWordPress(reservation)
            return true
        },
        async updateReservation(id, updatedReservation) {
            const index = this.reservations.findIndex(reservation => reservation.id === id)
            if (index >= 0) {
                this.reservations[index] = updatedReservation
                // Example: await updateReservationInWordPress(updatedReservation)
                return true
            }
            return false
        },
        async cancelReservation(id, cancelledBy) {
            const index = this.reservations.findIndex(reservation => reservation.id === id)
            if (index >= 0) {
                this.reservations[index] = {
                    ...this.reservations[index],
                    cancelled_by: cancelledBy || 'user'
                }
                // Example: await cancelReservationInWordPress(id, cancelledBy)
                return true
            }
            return false
        },
        async deleteReservation(id) {
            const index = this.reservations.findIndex(reservation => reservation.id === id)
            if (index >= 0) {
                this.reservations.splice(index, 1)
                // Example: await deleteReservationFromWordPress(id)
                return true
            }
            return false
        }
    }
})