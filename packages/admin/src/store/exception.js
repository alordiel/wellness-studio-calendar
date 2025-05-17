import {defineStore} from 'pinia'

export const useExceptionStore = defineStore('exceptions', {
    // State
    state: () => ({
        exceptions: [
            {
                id: 1,
                event_id: 1, // Morning Yoga
                type: 'schedule_change',
                exception_date: '2025-05-26', // Changed date for Memorial Day
                new_date: '2025-05-27',
                new_start_time: '07:00',
                new_end_time: '08:30'
            },
            {
                id: 2,
                event_id: 2, // Boxing Class
                type: 'cancellation',
                exception_date: '2025-05-20', // Cancelled for this date
                new_date: null,
                new_start_time: null,
                new_end_time: null
            },
            {
                id: 3,
                event_id: 3, // Pilates
                type: 'time_change',
                exception_date: '2025-05-21',
                new_date: '2025-05-21', // Same date, different time
                new_start_time: '13:00',
                new_end_time: '14:00'
            },
            {
                id: 4,
                event_id: 4, // Zumba
                type: 'location_change',
                exception_date: '2025-05-23',
                new_date: '2025-05-23', // Same date, different location (would be handled elsewhere)
                new_start_time: '17:30',
                new_end_time: '18:30'
            }
        ]
    }),

    getters: {
        getAllExceptions: (state) => {
            return [...state.exceptions]
        },
        getExceptionById: (state) => {
            return (id) => {
                return state.exceptions.find(exception => exception.id === id) || null
            }
        },
        getExceptionsByEventId: (state) => {
            return (eventId) => {
                return state.exceptions.filter(exception => exception.event_id === eventId)
            }
        },
        getExceptionsByDate: (state) => {
            return (date) => {
                return state.exceptions.filter(exception =>
                    exception.exception_date === date || exception.new_date === date
                )
            }
        },
        getExceptionsByType: (state) => {
            return (type) => {
                return state.exceptions.filter(exception => exception.type === type)
            }
        }
    },

    actions: {
        async addException(exception) {
            this.exceptions.push(exception)
            // Example: await saveExceptionToWordPress(exception)
            return true
        },
        async updateException(id, updatedException) {
            const index = this.exceptions.findIndex(exception => exception.id === id)
            if (index >= 0) {
                this.exceptions[index] = updatedException
                // Example: await updateExceptionInWordPress(updatedException)
                return true
            }
            return false
        },
        async deleteException(id) {
            const index = this.exceptions.findIndex(exception => exception.id === id)
            if (index >= 0) {
                this.exceptions.splice(index, 1)
                // Example: await deleteExceptionFromWordPress(id)
                return true
            }
            return false
        },
        async deleteExceptionsByEventId(eventId) {
            const initialLength = this.exceptions.length
            this.exceptions = this.exceptions.filter(exception => exception.event_id !== eventId)
            // Example: await deleteExceptionsByEventFromWordPress(eventId)
            return initialLength !== this.exceptions.length
        }
    }
})