<template>
  <v-dialog v-model="localDialog" max-width="600px" persistent>
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
                  v-model="formData.name"
                  label="Activity Name"
                  required
                  :rules="nameRules"
                  maxlength="255"
                  counter="255"
                  :error-messages="errors.name"
                />
              </v-col>
              
              <v-col cols="12">
                <v-textarea
                  v-model="formData.description"
                  label="Description"
                  required
                  :rules="descriptionRules"
                  maxlength="500"
                  counter="500"
                  :error-messages="errors.description"
                  rows="4"
                />
              </v-col>
              
              <v-col cols="12">
                <v-text-field
                  v-model="formData.link"
                  label="Link (URL)"
                  required
                  type="url"
                  :rules="linkRules"
                  maxlength="255"
                  counter="255"
                  :error-messages="errors.link"
                  placeholder="https://example.com"
                />
              </v-col>
              
              <v-col cols="12">
                <v-row>
                  <v-col cols="8">
                    <v-text-field
                      v-model="formData.color"
                      label="Color"
                      required
                      :rules="colorRules"
                      :error-messages="errors.color"
                      readonly
                      @click="showColorPicker = true"
                      prepend-icon="mdi-palette"
                    />
                  </v-col>
                  
                  <v-col cols="4">
                    <div class="d-flex align-center">
                      <v-btn
                        :color="formData.color || '#1976D2'"
                        @click="showColorPicker = true"
                        width="60"
                        height="40"
                        class="mr-2"
                      >
                        <v-icon>mdi-palette</v-icon>
                      </v-btn>
                      <span class="text-caption">Preview</span>
                    </div>
                  </v-col>
                </v-row>
                
                <!-- Color Picker Dialog -->
                <v-dialog v-model="showColorPicker" max-width="400">
                  <v-card>
                    <v-card-title>Choose Color</v-card-title>
                    <v-card-text>
                      <v-color-picker
                        v-model="tempColor"
                        show-swatches
                        hide-mode-switch
                        mode="hex"
                        swatches-max-height="200"
                      />
                    </v-card-text>
                    <v-card-actions>
                      <v-spacer />
                      <v-btn
                        color="grey darken-1"
                        variant="text"
                        @click="cancelColorPicker"
                      >
                        Cancel
                      </v-btn>
                      <v-btn
                        color="blue darken-1"
                        variant="text"
                        @click="confirmColorPicker"
                      >
                        OK
                      </v-btn>
                    </v-card-actions>
                  </v-card>
                </v-dialog>
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
  activity: {
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
const showColorPicker = ref(false)
const tempColor = ref('#1976D2')

const formData = reactive({
  name: '',
  description: '',
  link: '',
  color: '#1976D2'
})

const errors = reactive({
  name: '',
  description: '',
  link: '',
  color: ''
})

const modalTitle = computed(() => props.isEditing ? 'Edit Activity' : 'Add New Activity')

const nameRules = [
  v => !!v || 'Activity name is required',
  v => v.length <= 255 || 'Activity name must be less than 255 characters'
]

const descriptionRules = [
  v => !!v || 'Description is required',
  v => v.length <= 500 || 'Description must be less than 500 characters'
]

const linkRules = [
  v => !!v || 'Link is required',
  v => v.length <= 255 || 'Link must be less than 255 characters',
  v => isValidUrl(v) || 'Please enter a valid URL'
]

const colorRules = [
  v => !!v || 'Color is required',
  v => /^#[0-9A-F]{6}$/i.test(v) || 'Please select a valid color'
]

const isValidUrl = (string) => {
  try {
    new URL(string)
    return true
  } catch (_) {
    return false
  }
}

const resetForm = () => {
  formData.name = ''
  formData.description = ''
  formData.link = ''
  formData.color = '#1976D2'
  errors.name = ''
  errors.description = ''
  errors.link = ''
  errors.color = ''
}

const populateForm = () => {
  if (props.activity) {
    formData.name = props.activity.name || ''
    formData.description = props.activity.description || ''
    formData.link = props.activity.link || ''
    formData.color = props.activity.color || '#1976D2'
  }
}

const confirmColorPicker = () => {
  formData.color = tempColor.value
  showColorPicker.value = false
}

const cancelColorPicker = () => {
  tempColor.value = formData.color
  showColorPicker.value = false
}

const handleSubmit = async () => {
  const { valid } = await formRef.value.validate()
  if (!valid) return

  const activityData = {
    name: formData.name,
    description: formData.description,
    link: formData.link,
    color: formData.color
  }

  if (props.isEditing && props.activity) {
    activityData.id = props.activity.id
  }

  emit('save', activityData)
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
    tempColor.value = formData.color
  }
})

watch(localDialog, (newVal) => {
  if (!newVal) {
    emit('update:dialog', false)
  }
})
</script>