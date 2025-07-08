import {defineStore} from 'pinia'

export const useEventsStore = defineStore('events', {
    // State
    state: () => ({
        events: [
            {
                id:1,
                activity_id: 1,
                instructor_id: 1,
                location_id: 2,
                week_day: 'Monday',
                start_time: '07:00',
                end_time: '08:30',
                places: 15
            },
            {
                id:2,
                activity_id: 1,
                instructor_id: 3,
                location_id: 1,
                week_day: 'Tuesday',
                start_time: '18:00',
                end_time: '19:30',
                places: 10
            },
            {
                id:3,
                activity_id: 2,
                instructor_id: 2,
                location_id: 2,
                week_day: 'Wednesday',
                start_time: '12:00',
                end_time: '13:00',
                places: 12
            },
            {
                id:4,
                activity_id: 4,
                instructor_id: 4,
                location_id: 3,
                week_day: 'Friday',
                start_time: '17:30',
                end_time: '18:30',
                places: 20
            }
        ],
    }),

    getters: {
        getAllEvents (state) {
            return [...state.events].sort((a, b) => a.activity_id.localeCompare(b.activity_id));
        },
        getEventByEventId (state) {
            return (eventId) => {
                return state.events.find((event) => event.id === eventId);
            }
        }
    },

    actions: {
        async addEvent(event) {
            // Generate new ID (in real app, this would come from backend)
            const newId = Math.max(...this.events.map(e => e.id)) + 1
            const newEvent = {
                id: newId,
                ...event
            }
            this.events.push(newEvent)
            // Example: await saveEventToWordPress(newEvent)
            return true
        },

        async updateEvent(index, updatedEvent) {
            console.log('update event', index, updatedEvent)
            if (index >= 0 && index < this.events.length) {
                // Keep the original ID
                const originalId = this.events[index].id
                this.events[index] = {
                    id: originalId,
                    ...updatedEvent
                }
                // Example: await updateEventInWordPress(this.events[index])
                return true
            }
            return false
        },

        // Alternative method to update by ID (recommended for future use)
        async updateEventById(id, updatedEvent) {
            const index = this.events.findIndex(event => event.id === id)
            if (index !== -1) {
                this.events[index] = {
                    id,
                    ...updatedEvent
                }
                // Example: await updateEventInWordPress(this.events[index])
                return true
            }
            return false
        },

         async deleteEvent (activityID) {

            const index = this.events.findIndex( event => event.activity_id === activityID)
            this.events.splice(index, 1)
            // Here you would typically make an AJAX call to delete from WordPress
            return true
        },

    }

})