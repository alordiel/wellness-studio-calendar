import {defineStore} from 'pinia'

export const useEventsStore = defineStore('events', {
    // State
    state: () => ({
        events: [
            {
                event_name: 'Morning Yoga',
                instructor: 1,
                location: 2,
                color: '#4CAF50',
                week_day: 'Monday',
                start_time: '07:00',
                end_time: '08:30',
                places: 15
            },
            {
                event_name: 'Boxing Class',
                instructor: 3,
                location: 1,
                color: '#F44336',
                week_day: 'Tuesday',
                start_time: '18:00',
                end_time: '19:30',
                places: 10
            },
            {
                event_name: 'Pilates',
                instructor: 2,
                location: 2,
                color: '#2196F3',
                week_day: 'Wednesday',
                start_time: '12:00',
                end_time: '13:00',
                places: 12
            },
            {
                event_name: 'Zumba',
                instructor: 4,
                location: 3,
                color: '#FF9800',
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
                a.event_name.localeCompare(b.event_name)
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
        addEvent(event)  {
            this.events.value.push(event)
            // Example: await saveEventToWordPress(event)
            return true
        },
        updateEvent (index, updatedEvent)  {
            if (index >= 0 && index < this.events.length - 1 ) {
                this.events[index] = updatedEvent
                // Example: await updateEventInWordPress(events.value[index])
                return true
            }
            return false
        },
        deleteEvent (index) {
            if (index >= 0 && index < this.events.length - 1 ) {
                this.events.splice(index, 1)
                // Here you would typically make an AJAX call to delete from WordPress
                return true
            }
            return false
        }
    }

})