<template>
  <v-dialog v-model="localDialog" max-width="600px" persistent>
    <v-card>
      <v-card-title>
        <span class="text-h5">Add New Reservation</span>
      </v-card-title>
      <v-card-text>
        <v-container>
          <v-form ref="formRef" @submit.prevent="handleSubmit">
            <v-row>
              <v-col cols="12" sm="6">
                <v-text-field
                  v-model="formData.user_name"
                  label="User Name"
                  required
                  :rules="nameRules"
                  variant="outlined"
                  :error-messages="errors.user_name"
                  maxlength="255"
                ></v-text-field>
              </v-col>
              <v-col cols="12" sm="6">
                <v-text-field
                  v-model="formData.email"
                  label="Email"
                  required
                  :rules="emailRules"
                  variant="outlined"
                  :error-messages="errors.email"
                  type="email"
                ></v-text-field>
              </v-col>
              <v-col cols="12">
                <v-select>
                  :item-props="itemProps"
                  item-value="value
                  :items="listOfEvents"
                  label="Select Event"
                </v-select>
              </v-col>
              <v-col cols="12" sm="6">
                <v-text-field
                  v-model="formData.phone"
                  label="Phone"
                  required
                  :rules="phoneRules"
                  variant="outlined"
                  :error-messages="errors.phone"
                ></v-text-field>
              </v-col>
              <v-col cols="12" sm="6">
                <v-select
                  v-model="formData.payment_method"
                  label="Payment Method"
                  :items="paymentMethods"
                  required
                  :rules="paymentRules"
                  variant="outlined"
                  :error-messages="errors.payment_method"
                ></v-select>
              </v-col>
              <v-col cols="12">
                <v-textarea
                  v-model="formData.admin_notes"
                  label="Admin Notes"
                  variant="outlined"
                  rows="3"
                  placeholder="Enter any admin notes for this reservation..."
                  :error-messages="errors.admin_notes"
                ></v-textarea>
              </v-col>
            </v-row>
          </v-form>
        </v-container>
      </v-card-text>
      <v-card-actions>
        <v-spacer></v-spacer>
        <v-btn color="grey" @click="handleCancel">Cancel</v-btn>
        <v-btn
          color="primary"
          @click="handleSubmit"
          :loading="isSubmitting"
        >
          Add Reservation
        </v-btn>
      </v-card-actions>
    </v-card>
  </v-dialog>
</template>

<script setup>
import { ref, reactive, watch } from 'vue'
import { useReservationStore } from '../../store/reservation.js'
import {useEventsStore} from "../../store/event.js";

const props = defineProps({
  modelValue: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['update:modelValue', 'success', 'cancel'])

const reservationStore = useReservationStore()
const eventStore = useEventsStore();
const formRef = ref(null)
const localDialog = ref(false)
const isSubmitting = ref(false)
const listOfEvents = ref(eventStore.getAllEventsAsList());

const formData = reactive({
  user_name: '',
  email: '',
  phone: '',
  payment_method: '',
  admin_notes: ''
})

const errors = reactive({
  user_name: '',
  email: '',
  phone: '',
  payment_method: '',
  admin_notes: ''
})

const paymentMethods = [
  { title: 'Card Payment', value: 'card payment' },
  { title: 'Cash', value: 'cash' },
  { title: 'Club Card', value: 'club card' },
  { title: 'Multisport Card', value: 'multisport card' }
]

// Validation rules
const nameRules = [
  v => !!v || 'User name is required',
  v => v.length <= 255 || 'Name must be less than 255 characters'
]

const emailRules = [
  v => !!v || 'Email is required',
  v => /.+@.+\..+/.test(v) || 'Email must be valid'
]

const phoneRules = [
  v => !!v || 'Phone number is required',
  v => /^[\+]?[0-9\s\-\(\)]{10,}$/.test(v) || 'Phone number must be valid'
]

const paymentRules = [
  v => !!v || 'Payment method is required'
]

// Watch for changes to the prop and update local state
watch(() => props.modelValue, (newVal) => {
  localDialog.value = newVal
  if (!newVal) {
    resetForm()
  }
})

// Watch local dialog changes and emit to parent
watch(localDialog, (newVal) => {
  if (newVal !== props.modelValue) {
    emit('update:modelValue', newVal)
  }
})

function itemProps (item) {
    return {
      title: item.name,
      subtitle: item.date,
    }
  }

const resetForm = () => {
  formData.user_name = ''
  formData.email = ''
  formData.phone = ''
  formData.payment_method = ''
  formData.admin_notes = ''

  // Clear errors
  Object.keys(errors).forEach(key => {
    errors[key] = ''
  })

  // Reset form validation
  if (formRef.value) {
    formRef.value.resetValidation()
  }
}

const validateForm = async () => {
  if (!formRef.value) return false

  const { valid } = await formRef.value.validate()
  return valid
}

const handleSubmit = async () => {
  const isValid = await validateForm()
  if (!isValid) return

  isSubmitting.value = true

  try {
    // Generate new ID
    const existingReservations = reservationStore.getAllReservations
    const newId = existingReservations.length > 0
      ? Math.max(...existingReservations.map(r => r.id)) + 1
      : 1

    // Create new reservation object
    const newReservation = {
      id: newId,
      event: null, // This would be set based on event selection in a real app
      user_name: formData.user_name,
      email: formData.email,
      phone: formData.phone,
      payment_method: formData.payment_method,
      cancelled_by: null,
      user_notes: '',
      admin_notes: formData.admin_notes,
      dateCreation: new Date().toISOString(),
      dateTimeReservation: new Date().toISOString() // In real app, this would be the event date
    }

    // Add to store
    await reservationStore.addReservation(newReservation)

    // Emit success and close modal
    emit('success', newReservation)
    localDialog.value = false

  } catch (error) {
    console.error('Error adding reservation:', error)
    // You could add error handling here, like showing a toast notification
  } finally {
    isSubmitting.value = false
  }
}

const handleCancel = () => {
  localDialog.value = false
  emit('cancel')
}
</script>