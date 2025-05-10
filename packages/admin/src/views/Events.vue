<template>
  <div class="p-6">
    <!-- Header with Add Button -->
    <div class="flex justify-between items-center mb-6">
      <h1 class="text-2xl font-bold text-gray-800">Events Management</h1>
      <router-link to="/" class="text-blue-600 hover:text-blue-800">← Back to Dashboard</router-link>
      <button
        @click="openAddModal"
        class="bg-blue-500 hover:bg-blue-600 text-white font-medium py-2 px-4 rounded-lg transition duration-200"
      >
        Add New Entry
      </button>
    </div>

    <!-- Events Table -->
    <div v-if="events.length > 0" class="overflow-x-auto shadow-md rounded-lg">
      <table class="w-full bg-white">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Event Name</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Instructor</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Location</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Color</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Week Day</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Start Time</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">End Time</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Places</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
          <tr v-for="(event, index) in events" :key="index" class="hover:bg-gray-50">
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ event.event_name }}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ event.instructor }}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ event.location }}</td>
            <td class="px-6 py-4 whitespace-nowrap">
              <div class="flex items-center">
                <div
                  class="w-6 h-6 rounded border border-gray-300"
                  :style="{ backgroundColor: event.color }"
                ></div>
                <span class="ml-2 text-sm text-gray-900">{{ event.color }}</span>
              </div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ formatWeekDays(event.week_day) }}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ event.start_time }}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ event.end_time }}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ event.places }}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
              <button
                @click="openEditModal(index)"
                class="text-blue-600 hover:text-blue-900 mr-3"
              >
                Edit
              </button>
              <button
                @click="confirmDelete(index)"
                class="text-red-600 hover:text-red-900"
              >
                Delete
              </button>
              <button
                @click="confirmDelete(index)"
                class="text-red-600 hover:text-red-900"
              >
                Add Exception
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- No Entries Message -->
    <div v-else class="text-center py-12 bg-gray-50 rounded-lg">
      <p class="text-gray-500 text-lg">No event entries found. Add a new entry to get started.</p>
    </div>

    <!-- Modal Dialog -->
    <div v-if="showModal" class="fixed inset-0 bg-black bg-opacity-50 overflow-y-auto h-full w-full flex items-center justify-center z-50">
      <div class="bg-white rounded-lg p-6 w-full max-w-2xl max-h-[90vh] overflow-y-auto">
        <h2 class="text-xl font-bold mb-4">{{ modalTitle }}</h2>

        <form @submit.prevent="handleSubmit">
          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-2">Event Name</label>
            <select
              v-model="formData.event_name"
              required
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              :class="{ 'border-red-500': errors.event_name }"
            >
              <option value="">Select an activity</option>
              <option v-for="activity in activities" :key="activity" :value="activity">
                {{ activity }}
              </option>
            </select>
            <p v-if="errors.event_name" class="text-red-500 text-xs mt-1">{{ errors.event_name }}</p>
          </div>

          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-2">Instructor</label>
            <select
              v-model="formData.instructor"
              required
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              :class="{ 'border-red-500': errors.instructor }"
            >
              <option value="">Select an instructor</option>
              <option v-for="instructor in instructors" :key="instructor" :value="instructor">
                {{ instructor }}
              </option>
            </select>
            <p v-if="errors.instructor" class="text-red-500 text-xs mt-1">{{ errors.instructor }}</p>
          </div>

          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-2">Location</label>
            <select
              v-model="formData.location"
              required
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              :class="{ 'border-red-500': errors.location }"
            >
              <option value="">Select a location</option>
              <option v-for="location in locations" :key="location" :value="location">
                {{ location }}
              </option>
            </select>
            <p v-if="errors.location" class="text-red-500 text-xs mt-1">{{ errors.location }}</p>
          </div>

          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-2">Color</label>
            <color-picker
              v-model:pureColor="formData.color"
              format="hex"
              shape="square"
              :disable-alpha="true"
            />
            <p v-if="errors.color" class="text-red-500 text-xs mt-1">{{ errors.color }}</p>
          </div>

          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-2">Week Days</label>
            <div class="grid grid-cols-3 gap-3">
              <label v-for="day in weekDays" :key="day.value" class="flex items-center">
                <input
                  type="radio"
                  v-model="formData.week_day"
                  :value="day.value"
                  class="mr-2 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                />
                <span class="text-sm text-gray-700">{{ day.label }}</span>
              </label>
            </div>
            <p v-if="errors.week_day" class="text-red-500 text-xs mt-1">{{ errors.week_day }}</p>
          </div>

          <div class="grid grid-cols-2 gap-4 mb-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Start Time</label>
              <input
                v-model="formData.start_time"
                type="time"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                :class="{ 'border-red-500': errors.start_time }"
              />
              <p v-if="errors.start_time" class="text-red-500 text-xs mt-1">{{ errors.start_time }}</p>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">End Time</label>
              <input
                v-model="formData.end_time"
                type="time"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                :class="{ 'border-red-500': errors.end_time }"
              />
              <p v-if="errors.end_time" class="text-red-500 text-xs mt-1">{{ errors.end_time }}</p>
            </div>
          </div>

          <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-2">Places</label>
            <input
              v-model.number="formData.places"
              type="number"
              min="1"
              required
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              :class="{ 'border-red-500': errors.places }"
            />
            <p v-if="errors.places" class="text-red-500 text-xs mt-1">{{ errors.places }}</p>
          </div>

          <div class="flex justify-end space-x-3">
            <button
              type="button"
              @click="closeModal"
              class="px-4 py-2 text-gray-700 bg-gray-200 hover:bg-gray-300 rounded-md transition duration-200"
            >
              Cancel
            </button>
            <button
              type="submit"
              class="px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded-md transition duration-200"
            >
              {{ isEditing ? 'Update' : 'Add' }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Delete Confirmation Dialog -->
    <div v-if="showDeleteConfirm" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg p-6 w-full max-w-sm">
        <h2 class="text-xl font-bold mb-4">Confirm Delete</h2>
        <p class="text-gray-600 mb-6">Are you sure you want to delete this event?</p>
        <div class="flex justify-end space-x-3">
          <button
            @click="cancelDelete"
            class="px-4 py-2 text-gray-700 bg-gray-200 hover:bg-gray-300 rounded-md transition duration-200"
          >
            Cancel
          </button>
          <button
            @click="deleteEvent"
            class="px-4 py-2 bg-red-500 hover:bg-red-600 text-white rounded-md transition duration-200"
          >
            Delete
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed } from 'vue'
import { ColorPicker } from 'vue3-colorpicker'
import 'vue3-colorpicker/style.css'

// Sample data for dropdowns - replace with real data from your store/API
const activities = ref(['Morning Yoga', 'Advanced Yoga', 'Digital Marketing Workshop', 'Web Development Bootcamp'])
const instructors = ref(['John Smith', 'Sarah Johnson', 'Michael Brown', 'Emily Davis'])
const locations = ref(['Grand Ballroom', 'Conference Hall A', 'Training Room 1', 'Outdoor Pavilion'])

const weekDays = [
  { label: 'Monday', value: 'monday' },
  { label: 'Tuesday', value: 'tuesday' },
  { label: 'Wednesday', value: 'wednesday' },
  { label: 'Thursday', value: 'thursday' },
  { label: 'Friday', value: 'friday' },
  { label: 'Saturday', value: 'saturday' },
  { label: 'Sunday', value: 'sunday' }
]

// Reactive data
const events = ref([
  // Sample data - remove this when connecting to real data source
  {
    event_name: 'Morning Yoga',
    instructor: 'John Smith',
    location: 'Grand Ballroom',
    color: '#3B82F6',
    week_day: ['monday', 'wednesday', 'friday'],
    start_time: '08:00',
    end_time: '09:30',
    places: 30
  }
])

const showModal = ref(false)
const showDeleteConfirm = ref(false)
const isEditing = ref(false)
const editingIndex = ref(null)
const deleteIndex = ref(null)

const formData = reactive({
  event_name: '',
  instructor: '',
  location: '',
  color: '#3B82F6',
  week_day: [],
  start_time: '',
  end_time: '',
  places: 1
})

const errors = reactive({
  event_name: '',
  instructor: '',
  location: '',
  color: '',
  week_day: '',
  start_time: '',
  end_time: '',
  places: ''
})

// Computed properties
const modalTitle = computed(() => isEditing.value ? 'Edit Event' : 'Add New Event')

// Methods
const formatWeekDays = (days) => {
  return days.map(day => day.charAt(0).toUpperCase() + day.slice(1)).join(', ')
}

const openAddModal = () => {
  resetForm()
  isEditing.value = false
  editingIndex.value = null
  showModal.value = true
}

const openEditModal = (index) => {
  const event = events.value[index]
  formData.event_name = event.event_name
  formData.instructor = event.instructor
  formData.location = event.location
  formData.color = event.color
  formData.week_day = [...event.week_day]
  formData.start_time = event.start_time
  formData.end_time = event.end_time
  formData.places = event.places
  isEditing.value = true
  editingIndex.value = index
  showModal.value = true
}

const closeModal = () => {
  showModal.value = false
  resetForm()
}

const resetForm = () => {
  formData.event_name = ''
  formData.instructor = ''
  formData.location = ''
  formData.color = '#3B82F6'
  formData.week_day = []
  formData.start_time = ''
  formData.end_time = ''
  formData.places = 1
  Object.keys(errors).forEach(key => errors[key] = '')
}

const validateForm = () => {
  let isValid = true

  // Reset errors
  Object.keys(errors).forEach(key => errors[key] = '')

  // Validate event name
  if (!formData.event_name) {
    errors.event_name = 'Event name is required'
    isValid = false
  }

  // Validate instructor
  if (!formData.instructor) {
    errors.instructor = 'Instructor is required'
    isValid = false
  }

  // Validate location
  if (!formData.location) {
    errors.location = 'Location is required'
    isValid = false
  }

  // Validate color
  if (!formData.color) {
    errors.color = 'Color is required'
    isValid = false
  }

  // Validate week days
  if (formData.week_day.length === 0) {
    errors.week_day = 'Please select at least one day'
    isValid = false
  }

  // Validate start time
  if (!formData.start_time) {
    errors.start_time = 'Start time is required'
    isValid = false
  }

  // Validate end time
  if (!formData.end_time) {
    errors.end_time = 'End time is required'
    isValid = false
  } else if (formData.start_time && formData.end_time <= formData.start_time) {
    errors.end_time = 'End time must be after start time'
    isValid = false
  }

  // Validate places
  if (!formData.places || formData.places < 1) {
    errors.places = 'Number of places must be at least 1'
    isValid = false
  }

  return isValid
}

const handleSubmit = () => {
  if (!validateForm()) return

  const eventData = {
    event_name: formData.event_name,
    instructor: formData.instructor,
    location: formData.location,
    color: formData.color,
    week_day: [...formData.week_day],
    start_time: formData.start_time,
    end_time: formData.end_time,
    places: formData.places
  }

  if (isEditing.value) {
    events.value[editingIndex.value] = eventData
  } else {
    events.value.push(eventData)
  }

  closeModal()
}

const confirmDelete = (index) => {
  deleteIndex.value = index
  showDeleteConfirm.value = true
}

const cancelDelete = () => {
  deleteIndex.value = null
  showDeleteConfirm.value = false
}

const deleteEvent = () => {
  if (deleteIndex.value !== null) {
    events.value.splice(deleteIndex.value, 1)
  }
  cancelDelete()
}
</script>