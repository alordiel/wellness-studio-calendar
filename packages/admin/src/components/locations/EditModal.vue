<template>
  <v-dialog v-model="localDialog" max-width="500px" persistent>
    <v-card>
      <v-card-title>
        <span class="text-h5">{{ modalTitle }}</span>
      </v-card-title>
      
      <v-card-text>
        <v-container>
          <v-form ref="formRef" @submit.prevent="handleSubmit">
            <v-row>
              <v-col cols="12">
                <v-text-field
                  v-model="formData.address"
                  label="Address"
                  required
                  :rules="addressRules"
                  maxlength="255"
                  counter="255"
                  :error-messages="errors.address"
                />
              </v-col>
              
              <v-col cols="12">
                <v-text-field
                  v-model="formData.hall"
                  label="Hall Name"
                  required
                  :rules="hallRules"
                  maxlength="255"
                  counter="255"
                  :error-messages="errors.hall"
                />
              </v-col>
              
              <v-col cols="12">
                <v-text-field
                  v-model="formData.max_participants"
                  label="Max Participants"
                  required
                  type="number"
                  :rules="participantsRules"
                  :error-messages="errors.max_participants"
                  min="1"
                  max="9999"
                />
              </v-col>
            </v-row>
          </v-form>
        </v-container>
      </v-card-text>
      
      <v-card-actions>
        <v-spacer />
        <v-btn
          color="grey darken-1"
          variant="text"
          @click="handleCancel"
        >
          Cancel
        </v-btn>
        <v-btn
          color="blue darken-1"
          variant="text"
          @click="handleSubmit"
        >
          {{ isEditing ? 'Update' : 'Add' }}
        </v-btn>
      </v-card-actions>
    </v-card>
  </v-dialog>
</template>

<script setup>
import { ref, reactive, computed, watch } from 'vue'

const props = defineProps({
  dialog: {
    type: Boolean,
    default: false
  },
  location: {
    type: Object,
    default: null
  },
  isEditing: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['update:dialog', 'save', 'cancel'])

const formRef = ref(null)
const localDialog = ref(false)

const formData = reactive({
  address: '',
  hall: '',
  max_participants: ''
})

const errors = reactive({
  address: '',
  hall: '',
  max_participants: ''
})

const modalTitle = computed(() => props.isEditing ? 'Edit Location' : 'Add New Location')

const addressRules = [
  v => !!v || 'Address is required',
  v => v.length <= 255 || 'Address must be less than 255 characters'
]

const hallRules = [
  v => !!v || 'Hall name is required',
  v => v.length <= 255 || 'Hall name must be less than 255 characters'
]

const participantsRules = [
  v => !!v || 'Max participants is required',
  v => /^\d+$/.test(v) || 'Only numbers are allowed',
  v => parseInt(v) > 0 || 'Must be greater than 0',
  v => parseInt(v) <= 9999 || 'Must be less than 10000'
]

const resetForm = () => {
  formData.address = ''
  formData.hall = ''
  formData.max_participants = ''
  errors.address = ''
  errors.hall = ''
  errors.max_participants = ''
}

const populateForm = () => {
  if (props.location) {
    formData.address = props.location.address || ''
    formData.hall = props.location.hall || ''
    formData.max_participants = props.location.max_participants ? props.location.max_participants.toString() : ''
  }
}

const handleSubmit = async () => {
  const { valid } = await formRef.value.validate()
  if (!valid) return

  const locationData = {
    address: formData.address,
    hall: formData.hall,
    max_participants: parseInt(formData.max_participants)
  }

  if (props.isEditing && props.location) {
    locationData.id = props.location.id
  }

  emit('save', locationData)
  resetForm()
}

const handleCancel = () => {
  emit('cancel')
  resetForm()
}

watch(() => props.dialog, (newVal) => {
  localDialog.value = newVal
  if (newVal) {
    populateForm()
  }
})

watch(localDialog, (newVal) => {
  if (!newVal) {
    emit('update:dialog', false)
  }
})
</script>