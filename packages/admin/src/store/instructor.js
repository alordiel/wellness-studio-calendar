import {defineStore} from 'pinia'

export const useInstructorStore = defineStore('instructors', {
    // State
    state: () => ({
        instructors: [
            {
                id: 1,
                name: 'Jane Smith',
                avatar: '/assets/avatars/jane-smith.jpg',
                biography: 'Jane is a certified yoga instructor with over 10 years of experience in various yoga styles including Hatha, Vinyasa, and Yin.',
                link: '/instructors/jane-smith'
            },
            {
                id: 2,
                name: 'Michael Johnson',
                avatar: '/assets/avatars/michael-johnson.jpg',
                biography: 'Michael specializes in pilates and core training. He has worked with professional athletes and rehabilitation patients.',
                link: '/instructors/michael-johnson'
            },
            {
                id: 3,
                name: 'Sarah Rodriguez',
                avatar: '',
                biography: 'Former professional boxer Sarah brings her expertise to boxing classes for all levels, focusing on technique and fitness.',
                link: '/instructors/sarah-rodriguez'
            },
            {
                id: 4,
                name: 'David Chen',
                avatar: '/assets/avatars/david-chen.jpg',
                biography: 'David is an internationally certified Zumba instructor who brings energy and fun to every class.',
                link: '/instructors/david-chen'
            }
        ]
    }),

    getters: {
        getAllInstructors: (state) => {
            return [...state.instructors].sort((a, b) =>
                a.name.localeCompare(b.name)
            )
        },
        getInstructorById: (state) => {
            return (id) => {
                return state.instructors.find(instructor => instructor.id === id) || null
            }
        },
        getInstructorByIndex: (state) => {
            return (index) => {
                if (index >= 0 && index < state.instructors.length) {
                    return state.instructors[index]
                }
                return null
            }
        }
    },

    actions: {
        async addInstructor(instructor) {
            this.instructors.push(instructor)
            // Example: await saveInstructorToWordPress(instructor)
            return true
        },
        async updateInstructor(id, updatedInstructor) {
            const index = this.instructors.findIndex(instructor => instructor.id === id)
            if (index >= 0) {
                this.instructors[index] = updatedInstructor
                // Example: await updateInstructorInWordPress(updatedInstructor)
                return true
            }
            return false
        },
        async deleteInstructor(id) {
            const index = this.instructors.findIndex(instructor => instructor.id === id)
            if (index >= 0) {
                this.instructors.splice(index, 1)
                // Example: await deleteInstructorFromWordPress(id)
                return true
            }
            return false
        }
    }
})