<template>
  <v-dialog v-model="modalState" class="pa-15" width="700" persistent>
    <v-card>
      <v-card-title class="text-h5">
        {{ modalTitle }}
      </v-card-title>

      <v-card-text>
        <v-form @submit.prevent="handleSubmit">
          <!-- Activity Selection -->
          <v-select
              v-model="formData.activity_id"
              :items="activityOptions"
              item-title="name"
              item-value="id"
              label="Activity"
              variant="outlined"
              density="comfortable"
              :error-messages="errors.activity_id"
              class="mb-4"
          ></v-select>

          <!-- Instructor Selection -->
          <v-select
              v-model="formData.instructor_id"
              :items="instructorOptions"
              item-title="name"
              item-value="id"
              label="Instructor"
              variant="outlined"
              density="comfortable"
              :error-messages="errors.instructor_id"
              class="mb-4"
          ></v-select>

          <!-- Location Selection -->
          <v-select
              v-model="formData.location_id"
              :items="locationOptions"
              item-title="displayName"
              item-value="id"
              label="Location"
              variant="outlined"
              density="comfortable"
              :error-messages="errors.location_id"
              class="mb-4"
          ></v-select>

          <!-- Week Day Selection (Radio Buttons) -->
          <div class="mb-4">
            <v-label class="text-body-1 font-weight-medium mb-2">Week Day</v-label>
            <v-radio-group
                v-model="formData.week_day"
                :error-messages="errors.week_day"
                inline
                density="comfortable"
            >
              <v-radio
                  v-for="day in weekDays"
                  :key="day.value"
                  :label="day.label"
                  :value="day.value"
                  color="primary"
              ></v-radio>
            </v-radio-group>
          </div>

          <!-- Time Pickers -->
          <v-row class="mb-4">
            <v-col cols="6">
              <v-label class="text-body-1 font-weight-medium mb-2">Start Time</v-label>
              <v-time-picker
                  v-model="formData.start_time"
                  format="24hr"
                  :error-messages="errors.start_time"
                  elevation="1"
                  width="100%"
              ></v-time-picker>
            </v-col>
            <v-col cols="6">
              <v-label class="text-body-1 font-weight-medium mb-2">End Time</v-label>
              <v-time-picker
                  v-model="formData.end_time"
                  format="24hr"
                  :error-messages="errors.end_time"
                  elevation="1"
                  width="100%"
              ></v-time-picker>
            </v-col>
          </v-row>

          <!-- Places Input -->
          <v-text-field
              v-model.number="formData.places"
              label="Number of Places"
              type="number"
              min="1"
              variant="outlined"
              density="comfortable"
              :error-messages="errors.places"
              class="mb-4"
          ></v-text-field>
        </v-form>
      </v-card-text>

      <v-card-actions>
        <div class="px-10">
          <v-spacer></v-spacer>
          <v-btn
              variant="text"
              @click="closeModal"
              text="Cancel"
          ></v-btn>
          <v-btn
              color="primary"
              variant="flat"
              @click="handleSubmit"
          >
            {{ isEditing ? 'Update' : 'Add' }}
          </v-btn>
        </div>
      </v-card-actions>
    </v-card>
  </v-dialog>
</template>

<script setup>
import {ref, reactive, computed, watch, onMounted} from 'vue'
import {storeToRefs} from 'pinia'
import {VTimePicker} from 'vuetify/labs/VTimePicker'
import {useEventsStore} from "../../store/event.js"
import {useActivityStore} from "../../store/activity.js"
import {useInstructorStore} from "../../store/instructor.js"
import {useLocationStore} from "../../store/location.js"

const emit = defineEmits(['close'])
const props = defineProps(['eventIndex', 'showModal'])

// Stores
const eventsStore = useEventsStore()
const activityStore = useActivityStore()
const instructorStore = useInstructorStore()
const locationStore = useLocationStore()

// Store refs
const {events} = storeToRefs(eventsStore)

const modalState = ref(false)

// Form data matching the actual event model
const formData = reactive({
  activity_id: null,
  instructor_id: null,
  location_id: null,
  week_day: '',
  start_time: null,
  end_time: null,
  places: 1
})

const errors = reactive({
  activity_id: '',
  instructor_id: '',
  location_id: '',
  week_day: '',
  start_time: '',
  end_time: '',
  places: ''
})

const weekDays = [
  {label: 'Monday', value: 'Monday'},
  {label: 'Tuesday', value: 'Tuesday'},
  {label: 'Wednesday', value: 'Wednesday'},
  {label: 'Thursday', value: 'Thursday'},
  {label: 'Friday', value: 'Friday'},
  {label: 'Saturday', value: 'Saturday'},
  {label: 'Sunday', value: 'Sunday'}
]

// Computed properties
const isEditing = computed(() => props.eventIndex !== null)
const modalTitle = computed(() => isEditing.value ? 'Edit Event' : 'Add New Event')

// Options for dropdowns
const activityOptions = computed(() => activityStore.getAllActivities)
const instructorOptions = computed(() => instructorStore.getAllInstructors)
const locationOptions = computed(() => {
  return locationStore.getAllLocations.map(location => ({
    ...location,
    displayName: `${location.hall} - ${location.address}`
  }))
})

// Watchers
watch(() => props.showModal, (newVal) => {
  modalState.value = newVal
  if (newVal && isEditing.value) {
    populateFormData()
  }
})

// Methods
const populateFormData = () => {
  if (props.eventIndex !== null) {
    const event = eventsStore.getEventByIndex(props.eventIndex)
    if (event) {
      formData.activity_id = event.activity_id
      formData.instructor_id = event.instructor_id
      formData.location_id = event.location_id
      formData.week_day = event.week_day
      formData.start_time = event.start_time
      formData.end_time = event.end_time
      formData.places = event.places
    }
  }
}

const closeModal = () => {
  resetForm()
  modalState.value = false
  emit('close')
}

const resetForm = () => {
  formData.activity_id = null
  formData.instructor_id = null
  formData.location_id = null
  formData.week_day = ''
  formData.start_time = null
  formData.end_time = null
  formData.places = 1
  Object.keys(errors).forEach(key => errors[key] = '')
}

const validateForm = () => {
  let isValid = true

  // Reset errors
  Object.keys(errors).forEach(key => errors[key] = '')

  // Validate activity
  if (!formData.activity_id) {
    errors.activity_id = 'Activity is required'
    isValid = false
  }

  // Validate instructor
  if (!formData.instructor_id) {
    errors.instructor_id = 'Instructor is required'
    isValid = false
  }

  // Validate location
  if (!formData.location_id) {
    errors.location_id = 'Location is required'
    isValid = false
  }

  // Validate week day
  if (!formData.week_day) {
    errors.week_day = 'Please select a day'
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
    activity_id: formData.activity_id,
    instructor_id: formData.instructor_id,
    location_id: formData.location_id,
    week_day: formData.week_day,
    start_time: formData.start_time,
    end_time: formData.end_time,
    places: formData.places
  }

  if (isEditing.value) {
    eventsStore.updateEvent(props.eventIndex, eventData)
  } else {
    eventsStore.addEvent(eventData)
  }

  closeModal()
}

// Initialize stores on mount
onMounted(() => {
  // Ensure all stores are populated
  // You might want to call fetch methods here if needed
})
</script>