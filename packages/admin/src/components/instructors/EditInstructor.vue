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
                  label="Name"
                  required
                  :rules="nameRules"
                  maxlength="255"
                  counter="255"
                  :error-messages="errors.name"
                />
              </v-col>
              
              <v-col cols="12">
                <v-row>
                  <v-col cols="4">
                    <v-avatar size="80" class="mb-4">
                      <v-img
                        v-if="avatarPreview"
                        :src="avatarPreview"
                        :alt="formData.name"
                      />
                      <v-icon
                        v-else
                        size="40"
                        icon="mdi-account"
                      />
                    </v-avatar>
                  </v-col>
                  
                  <v-col cols="8">
                    <v-file-input
                      v-model="avatarFile"
                      label="Avatar"
                      accept="image/*"
                      prepend-icon="mdi-camera"
                      @change="handleFileUpload"
                      :error-messages="errors.avatar"
                      hint="Max size: 2MB"
                      persistent-hint
                    />
                  </v-col>
                </v-row>
              </v-col>
              
              <v-col cols="12">
                <v-text-field
                  v-model="formData.link"
                  label="URL"
                  type="url"
                  maxlength="255"
                  counter="255"
                  :rules="urlRules"
                  :error-messages="errors.link"
                  placeholder="https://example.com"
                />
              </v-col>
              
              <v-col cols="12">
                <v-textarea
                  v-model="formData.biography"
                  label="Biography"
                  required
                  :rules="biographyRules"
                  maxlength="500"
                  counter="500"
                  :error-messages="errors.biography"
                  rows="4"
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
  instructor: {
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
const avatarPreview = ref(null)
const avatarFile = ref(null)

const formData = reactive({
  name: '',
  biography: '',
  link: '',
  avatar: null
})

const errors = reactive({
  name: '',
  biography: '',
  link: '',
  avatar: ''
})

const modalTitle = computed(() => props.isEditing ? 'Edit Instructor' : 'Add New Instructor')

const nameRules = [
  v => !!v || 'Name is required',
  v => v.length <= 255 || 'Name must be less than 255 characters'
]

const biographyRules = [
  v => !!v || 'Biography is required',
  v => v.length <= 500 || 'Biography must be less than 500 characters'
]

const urlRules = [
  v => !v || isValidUrl(v) || 'Please enter a valid URL',
  v => !v || v.length <= 255 || 'URL must be less than 255 characters'
]

const isValidUrl = (string) => {
  try {
    new URL(string)
    return true
  } catch (_) {
    return false
  }
}

const handleFileUpload = () => {
  if (!avatarFile.value || !avatarFile.value.length) {
    avatarPreview.value = null
    return
  }

  const file = avatarFile.value[0]

  if (file.size > 2 * 1024 * 1024) {
    errors.avatar = 'File size must be less than 2MB'
    avatarFile.value = null
    return
  }

  if (!file.type.startsWith('image/')) {
    errors.avatar = 'Please upload an image file'
    avatarFile.value = null
    return
  }

  errors.avatar = ''
  
  const reader = new FileReader()
  reader.onload = (e) => {
    avatarPreview.value = e.target.result
  }
  reader.readAsDataURL(file)
}

const resetForm = () => {
  formData.name = ''
  formData.biography = ''
  formData.link = ''
  formData.avatar = null
  avatarPreview.value = null
  avatarFile.value = null
  errors.name = ''
  errors.biography = ''
  errors.link = ''
  errors.avatar = ''
}

const populateForm = () => {
  if (props.instructor) {
    formData.name = props.instructor.name || ''
    formData.biography = props.instructor.biography || ''
    formData.link = props.instructor.link || ''
    formData.avatar = props.instructor.avatar || null
    avatarPreview.value = props.instructor.avatar || null
  }
}

const handleSubmit = async () => {
  const { valid } = await formRef.value.validate()
  if (!valid) return

  const instructorData = {
    name: formData.name,
    biography: formData.biography,
    link: formData.link || null,
    avatar: avatarPreview.value
  }

  if (props.isEditing && props.instructor) {
    instructorData.id = props.instructor.id
  }

  emit('save', instructorData)
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