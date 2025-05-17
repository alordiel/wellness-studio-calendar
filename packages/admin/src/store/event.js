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
        ]
    }),

    getters: {
        getAllEvents: (state) => {
            return [...state.events.value].sort((a, b) =>
                a.activity_id.localeCompare(b.activity_id)
            )
        },
        getEventByIndex: (state) => {
            return (index) => {
                if (index >= 0 && index < state.events.length - 1) {
                    return state.events[index]
                }
                return null
            }
        }
    },

    actions: {
        async addEvent(event)  {
            this.events.value.push(event)
            // Example: await saveEventToWordPress(event)
            return true
        },
        async updateEvent (index, updatedEvent)  {
            if (index >= 0 && index < this.events.length - 1 ) {
                this.events[index] = updatedEvent
                // Example: await updateEventInWordPress(events.value[index])
                return true
            }
            return false
        },
        async deleteEvent (index) {
            if (index >= 0 && index < this.events.length - 1 ) {
                this.events.splice(index, 1)
                // Here you would typically make an AJAX call to delete from WordPress
                return true
            }
            return false
        }
    }

})