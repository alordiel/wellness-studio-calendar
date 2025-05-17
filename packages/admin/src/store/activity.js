import {defineStore} from 'pinia'

export const useActivityStore = defineStore('activities', {
    // State
    state: () => ({
        activities: [
            {
                id: 1,
                name: 'Fitness Training',
                description: 'General fitness training to improve overall health and well-being.',
                color: '#4CAF50',
                link: '/activities/fitness-training'
            },
            {
                id: 2,
                name: 'Meditation',
                description: 'Guided meditation sessions for mental clarity and stress reduction.',
                color: '#F44336',
                link: '/activities/meditation'
            },
            {
                id: 3,
                name: 'Swimming',
                description: 'Swimming lessons and free swimming sessions for all levels.',
                color: '#2196F3',
                link: '/activities/swimming'
            },
            {
                id: 4,
                name: 'Dance',
                description: 'Various dance styles including ballet, contemporary, and hip-hop.',
                color: '#FF9800',
                link: '/activities/dance'
            }
        ]
    }),

    getters: {
        getAllActivities: (state) => {
            return [...state.activities].sort((a, b) =>
                a.name.localeCompare(b.name)
            )
        },
        getActivityById: (state) => {
            return (id) => {
                return state.activities.find(activity => activity.id === id) || null
            }
        },
        getActivityByIndex: (state) => {
            return (index) => {
                if (index >= 0 && index < state.activities.length) {
                    return state.activities[index]
                }
                return null
            }
        }
    },

    actions: {
        async addActivity(activity) {
            this.activities.push(activity)
            // Example: await saveActivityToWordPress(activity)
            return true
        },
        async updateActivity(id, updatedActivity) {
            const index = this.activities.findIndex(activity => activity.id === id)
            if (index >= 0) {
                this.activities[index] = updatedActivity
                // Example: await updateActivityInWordPress(updatedActivity)
                return true
            }
            return false
        },
        async deleteActivity(id) {
            const index = this.activities.findIndex(activity => activity.id === id)
            if (index >= 0) {
                this.activities.splice(index, 1)
                // Example: await deleteActivityFromWordPress(id)
                return true
            }
            return false
        }
    }
})