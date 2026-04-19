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

          <!-- Date -->
          <v-text-field
              v-model="formData.date"
              label="Date"
              type="date"
              variant="outlined"
              density="comfortable"
              :error-messages="errors.date"
              class="mb-4"
          ></v-text-field>

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
          <v-btn variant="text" @click="closeModal" text="Cancel"></v-btn>
          <v-btn color="primary" variant="flat" @click="handleSubmit">
            {{ isEditing ? 'Update' : 'Add' }}
          </v-btn>
        </div>
      </v-card-actions>
    </v-card>
  </v-dialog>
</template>

<script setup>
import { ref, reactive, computed, watch } from 'vue'
import { VTimePicker } from 'vuetify/labs/VTimePicker'
import { useEventsStore } from '../../store/event.js'
import { useActivityStore } from '../../store/activity.js'
import { useInstructorStore } from '../../store/instructor.js'

const emit = defineEmits(['close'])
const props = defineProps(['eventId', 'showModal'])

const eventsStore = useEventsStore()
const activityStore = useActivityStore()
const instructorStore = useInstructorStore()

const modalState = ref(false)

const formData = reactive({
  activity_id: null,
  instructor_id: null,
  date: '',
  start_time: null,
  end_time: null,
  places: 1,
})

const errors = reactive({
  activity_id: '',
  instructor_id: '',
  date: '',
  start_time: '',
  end_time: '',
  places: '',
})

const isEditing = computed(() => props.eventId !== null)
const modalTitle = computed(() => isEditing.value ? 'Edit Single Event' : 'Add Single Event')

const activityOptions = computed(() => activityStore.getAllActivities)
const instructorOptions = computed(() => instructorStore.getAllInstructors)

watch(() => props.showModal, (newVal) => {
  modalState.value = newVal
  if (newVal && isEditing.value) {
    populateFormData()
  }
})

const populateFormData = () => {
  const event = eventsStore.getEventByEventId(props.eventId)
  if (event) {
    formData.activity_id = event.activity_id
    formData.instructor_id = event.instructor_id
    formData.date = event.date ?? ''
    formData.start_time = event.start_time
    formData.end_time = event.end_time
    formData.places = event.places
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
  formData.date = ''
  formData.start_time = null
  formData.end_time = null
  formData.places = 1
  Object.keys(errors).forEach(key => errors[key] = '')
}

const validateForm = () => {
  let isValid = true
  Object.keys(errors).forEach(key => errors[key] = '')

  if (!formData.activity_id) {
    errors.activity_id = 'Activity is required'
    isValid = false
  }
  if (!formData.instructor_id) {
    errors.instructor_id = 'Instructor is required'
    isValid = false
  }
  if (!formData.date) {
    errors.date = 'Date is required'
    isValid = false
  }
  if (!formData.start_time) {
    errors.start_time = 'Start time is required'
    isValid = false
  }
  if (!formData.end_time) {
    errors.end_time = 'End time is required'
    isValid = false
  } else if (formData.start_time && formData.end_time <= formData.start_time) {
    errors.end_time = 'End time must be after start time'
    isValid = false
  }
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
    date: formData.date,
    is_recurring: 0,
    start_time: formData.start_time,
    end_time: formData.end_time,
    places: formData.places,
  }

  if (isEditing.value) {
    eventsStore.updateEvent(props.eventId, eventData)
  } else {
    eventsStore.addEvent(eventData)
  }

  closeModal()
}
</script>
