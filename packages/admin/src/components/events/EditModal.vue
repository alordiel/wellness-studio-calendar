<template>
  <v-dialog v-model="modalState" width="auto">
    <v-card>
      <h2 class="text-xl font-bold mb-4">{{ modalTitle }}</h2>

      <form @submit.prevent="handleSubmit">
        <div class="mb-4">
          <label>Event Name</label>
          <select
              v-model="formData.event_name"
              required
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
          <label>Instructor</label>
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
          <label>Location</label>
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
          <label>Color</label>
          <v-color-picker
              v-model="formData.color"
              mode="hex"
          ></v-color-picker>
          <p v-if="errors.color" class="text-red-500 text-xs mt-1">{{ errors.color }}</p>
        </div>

        <div class="mb-4">
          <label class="">Week Days</label>
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
            <label>Start Time</label>
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
            <label>End Time</label>
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
          <label>Places</label>
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

        <v-card-actions>
          <v-btn
              variant="plain"
              @click="closeModal"
              text="Close"
          ></v-btn>
          <v-btn
              color="primary"
              @click="handleSubmit"
          >
            {{ isEditing ? 'Update' : 'Add' }}
          </v-btn>
        </v-card-actions>
      </form>
    </v-card>
  </v-dialog>
</template>

<script setup>
import {ref, reactive, computed, onMounted, watch} from 'vue'

const emit = defineEmits(['close'])
const props = defineProps(['eventIndex', 'showModal'])

const modalState = ref(false);
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

import {useEventsStore} from "../../store/event.js";
// Sample data for dropdowns - replace with real data from your store/API
const activities = ref(['Morning Yoga', 'Advanced Yoga', 'Digital Marketing Workshop', 'Web Development Bootcamp'])
const instructors = ref(['John Smith', 'Sarah Johnson', 'Michael Brown', 'Emily Davis'])
const locations = ref(['Grand Ballroom', 'Conference Hall A', 'Training Room 1', 'Outdoor Pavilion'])
const isEditing = props.eventIndex !== null;
const store = useEventsStore();

const weekDays = [
  {label: 'Monday', value: 'monday'},
  {label: 'Tuesday', value: 'tuesday'},
  {label: 'Wednesday', value: 'wednesday'},
  {label: 'Thursday', value: 'thursday'},
  {label: 'Friday', value: 'friday'},
  {label: 'Saturday', value: 'saturday'},
  {label: 'Sunday', value: 'sunday'}
]

watch(() => props.showModal, (newVal) => {
  modalState.value = newVal
})

// Computed properties
const modalTitle = computed(() => isEditing ? 'Edit Event' : 'Add New Event')

// Methods
const formatWeekDays = (days) => {
  return days.map(day => day.charAt(0).toUpperCase() + day.slice(1)).join(', ')
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
}

const closeModal = () => {
  resetForm();
  modalState.value = false
  emit('close');
}

/**
 * Resets the event form to its default values.
 *
 * This function is called when the user either submits the form successfully,
 * or when the user clicks the "Cancel" button to close the modal.
 */
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

  if (props.eventIndex !== null) {
    store.updateEvent(props.eventIndex, eventData);
  } else {
    store.addEvent(eventData)
  }

  closeModal()
}

</script>