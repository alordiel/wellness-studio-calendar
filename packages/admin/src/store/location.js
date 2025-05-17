import {defineStore} from 'pinia'

export const useLocationStore = defineStore('locations', {
    // State
    state: () => ({
        locations: [
            {
                id: 1,
                address: '123 Fitness Street, Downtown',
                hall: 'Main Gym',
                max_participants: 25
            },
            {
                id: 2,
                address: '123 Fitness Street, Downtown',
                hall: 'Studio A',
                max_participants: 15
            },
            {
                id: 3,
                address: '123 Fitness Street, Downtown',
                hall: 'Studio B',
                max_participants: 20
            },
            {
                id: 4,
                address: '456 Wellness Avenue, Uptown',
                hall: 'Aquatic Center',
                max_participants: 30
            }
        ]
    }),

    getters: {
        getAllLocations: (state) => {
            return [...state.locations].sort((a, b) =>
                a.hall.localeCompare(b.hall)
            )
        },
        getLocationById: (state) => {
            return (id) => {
                return state.locations.find(location => location.id === id) || null
            }
        },
        getLocationByIndex: (state) => {
            return (index) => {
                if (index >= 0 && index < state.locations.length) {
                    return state.locations[index]
                }
                return null
            }
        }
    },

    actions: {
        async addLocation(location) {
            this.locations.push(location)
            // Example: await saveLocationToWordPress(location)
            return true
        },
        async updateLocation(id, updatedLocation) {
            const index = this.locations.findIndex(location => location.id === id)
            if (index >= 0) {
                this.locations[index] = updatedLocation
                // Example: await updateLocationInWordPress(updatedLocation)
                return true
            }
            return false
        },
        async deleteLocation(id) {
            const index = this.locations.findIndex(location => location.id === id)
            if (index >= 0) {
                this.locations.splice(index, 1)
                // Example: await deleteLocationFromWordPress(id)
                return true
            }
            return false
        }
    }
})