<template>
  <div class="p-6">
    <!-- Header with Add Button -->
    <div class="flex justify-between items-center mb-6">
      <h1 class="text-2xl font-bold text-gray-800">Instructor Management</h1>
      <router-link to="/" class="text-blue-600 hover:text-blue-800">← Back to Dashboard</router-link>
      <button
        @click="openAddModal"
        class="bg-blue-500 hover:bg-blue-600 text-white font-medium py-2 px-4 rounded-lg transition duration-200"
      >
        Add New Entry
      </button>
    </div>

    <!-- Instructors Table -->
    <div v-if="instructors.length > 0" class="overflow-x-auto shadow-md rounded-lg">
      <table class="w-full bg-white">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Biography</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">URL</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Events</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
          <tr v-for="(instructor, index) in instructors" :key="index" class="hover:bg-gray-50">
            <td class="px-6 py-4 whitespace-nowrap">
              <div class="flex items-center">
                <div class="h-10 w-10 flex-shrink-0">
                  <img
                    v-if="instructor.avatar"
                    :src="instructor.avatar"
                    :alt="instructor.name"
                    class="h-10 w-10 rounded-full object-cover"
                  />
                  <div v-else class="h-10 w-10 rounded-full bg-gray-300 flex items-center justify-center">
                    <span class="text-gray-600 font-medium">{{ getInitials(instructor.name) }}</span>
                  </div>
                </div>
                <div class="ml-4">
                  <div class="text-sm font-medium text-gray-900">{{ instructor.name }}</div>
                </div>
              </div>
            </td>
            <td class="px-6 py-4 text-sm text-gray-900">
              <div class="max-w-md">{{ getDescriptionExcerpt(instructor.biography) }}</div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm">
              <a
                v-if="instructor.url"
                :href="instructor.url"
                target="_blank"
                rel="noopener noreferrer"
                class="text-blue-600 hover:text-blue-900 underline"
              >
                {{ truncateLink(instructor.url) }}
              </a>
              <span v-else class="text-gray-400">No URL</span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
              {{ instructor.events ? instructor.events.length : 0 }} events
            </td>
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
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- No Entries Message -->
    <div v-else class="text-center py-12 bg-gray-50 rounded-lg">
      <p class="text-gray-500 text-lg">No instructor entries found. Add a new entry to get started.</p>
    </div>

    <!-- Modal Dialog -->
    <div v-if="showModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg p-6 w-full max-w-2xl">
        <h2 class="text-xl font-bold mb-4">{{ modalTitle }}</h2>

        <form @submit.prevent="handleSubmit">
          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-2">Name</label>
            <input
              v-model="formData.name"
              type="text"
              maxlength="255"
              required
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              :class="{ 'border-red-500': errors.name }"
            />
            <p v-if="errors.name" class="text-red-500 text-xs mt-1">{{ errors.name }}</p>
          </div>

          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-2">Avatar</label>
            <div class="flex items-center space-x-4">
              <div>
                <img
                  v-if="avatarPreview"
                  :src="avatarPreview"
                  alt="Avatar preview"
                  class="h-20 w-20 rounded-full object-cover"
                />
                <div v-else class="h-20 w-20 rounded-full bg-gray-300 flex items-center justify-center">
                  <span class="text-gray-600 text-2xl">{{ formData.name ? getInitials(formData.name) : '?' }}</span>
                </div>
              </div>
              <div>
                <input
                  type="file"
                  accept="image/*"
                  @change="handleFileUpload"
                  class="block w-full text-sm text-gray-500
                    file:mr-4 file:py-2 file:px-4
                    file:rounded-full file:border-0
                    file:text-sm file:font-semibold
                    file:bg-blue-50 file:text-blue-700
                    hover:file:bg-blue-100"
                />
                <p class="text-xs text-gray-500 mt-1">Max size: 2MB</p>
                <p v-if="errors.avatar" class="text-red-500 text-xs mt-1">{{ errors.avatar }}</p>
              </div>
            </div>
          </div>

          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-2">URL</label>
            <input
              v-model="formData.url"
              type="url"
              maxlength="255"
              placeholder="https://example.com"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              :class="{ 'border-red-500': errors.url }"
            />
            <p v-if="errors.url" class="text-red-500 text-xs mt-1">{{ errors.url }}</p>
          </div>

          <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-2">Biography</label>
            <textarea
              v-model="formData.biography"
              rows="4"
              maxlength="500"
              required
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              :class="{ 'border-red-500': errors.biography }"
            ></textarea>
            <p class="text-gray-500 text-xs mt-1">{{ formData.biography.length }}/500 characters</p>
            <p v-if="errors.biography" class="text-red-500 text-xs mt-1">{{ errors.biography }}</p>
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
        <p class="text-gray-600 mb-6">Are you sure you want to delete this instructor?</p>
        <div class="flex justify-end space-x-3">
          <button
            @click="cancelDelete"
            class="px-4 py-2 text-gray-700 bg-gray-200 hover:bg-gray-300 rounded-md transition duration-200"
          >
            Cancel
          </button>
          <button
            @click="deleteInstructor"
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

// Reactive data
const instructors = ref([
  // Sample data - remove this when connecting to real data source
  {
    name: 'John Smith',
    avatar: null,
    biography: 'Professional yoga instructor with over 10 years of experience. Specializes in Hatha and Vinyasa yoga styles. Passionate about helping students achieve mind-body balance.',
    url: 'https://example.com/instructors/john-smith',
    events: ['Morning Yoga', 'Evening Flow']
  },
  {
    name: 'Sarah Johnson',
    avatar: null,
    biography: 'Digital marketing expert with a focus on social media strategy and content creation. Has worked with Fortune 500 companies and startups alike.',
    url: 'https://example.com/instructors/sarah-johnson',
    events: ['Digital Marketing 101', 'Social Media Workshop']
  }
])

const showModal = ref(false)
const showDeleteConfirm = ref(false)
const isEditing = ref(false)
const editingIndex = ref(null)
const deleteIndex = ref(null)
const avatarPreview = ref(null)
const avatarFile = ref(null)

const formData = reactive({
  name: '',
  biography: '',
  url: '',
  avatar: null
})

const errors = reactive({
  name: '',
  biography: '',
  url: '',
  avatar: ''
})

// Computed properties
const modalTitle = computed(() => isEditing.value ? 'Edit Instructor' : 'Add New Instructor')

// Methods
const getInitials = (name) => {
  if (!name) return ''
  return name.split(' ').map(n => n[0]).join('').toUpperCase().substring(0, 2)
}

const getDescriptionExcerpt = (description) => {
  if (description.length <= 250) {
    return description
  }
  return description.substring(0, 247) + '...'
}

const truncateLink = (link) => {
  if (link.length > 50) {
    return link.substring(0, 47) + '...'
  }
  return link
}

const handleFileUpload = (event) => {
  const file = event.target.files[0]

  if (!file) return

  // Validate file size (2MB limit)
  if (file.size > 2 * 1024 * 1024) {
    errors.avatar = 'File size must be less than 2MB'
    event.target.value = ''
    return
  }

  // Validate file type
  if (!file.type.startsWith('image/')) {
    errors.avatar = 'Please upload an image file'
    event.target.value = ''
    return
  }

  avatarFile.value = file
  errors.avatar = ''

  // Create preview
  const reader = new FileReader()
  reader.onload = (e) => {
    avatarPreview.value = e.target.result
  }
  reader.readAsDataURL(file)
}

const openAddModal = () => {
  resetForm()
  isEditing.value = false
  editingIndex.value = null
  showModal.value = true
}

const openEditModal = (index) => {
  const instructor = instructors.value[index]
  formData.name = instructor.name
  formData.biography = instructor.biography
  formData.url = instructor.url || ''
  formData.avatar = instructor.avatar
  avatarPreview.value = instructor.avatar
  isEditing.value = true
  editingIndex.value = index
  showModal.value = true
}

const closeModal = () => {
  showModal.value = false
  resetForm()
}

const resetForm = () => {
  formData.name = ''
  formData.biography = ''
  formData.url = ''
  formData.avatar = null
  avatarPreview.value = null
  avatarFile.value = null
  errors.name = ''
  errors.biography = ''
  errors.url = ''
  errors.avatar = ''
}

const validateForm = () => {
  let isValid = true

  // Reset errors
  errors.name = ''
  errors.biography = ''
  errors.url = ''
  errors.avatar = ''

  // Validate name
  if (!formData.name.trim()) {
    errors.name = 'Name is required'
    isValid = false
  } else if (formData.name.length > 255) {
    errors.name = 'Name must be less than 255 characters'
    isValid = false
  }

  // Validate biography
  if (!formData.biography.trim()) {
    errors.biography = 'Biography is required'
    isValid = false
  } else if (formData.biography.length > 500) {
    errors.biography = 'Biography must be less than 500 characters'
    isValid = false
  }

  // Validate URL (optional)
  if (formData.url && formData.url.trim()) {
    if (formData.url.length > 255) {
      errors.url = 'URL must be less than 255 characters'
      isValid = false
    } else if (!isValidUrl(formData.url)) {
      errors.url = 'Please enter a valid URL'
      isValid = false
    }
  }

  return isValid
}

const isValidUrl = (string) => {
  try {
    new URL(string)
    return true
  } catch (_) {
    return false
  }
}

const handleSubmit = () => {
  if (!validateForm()) return

  const instructorData = {
    name: formData.name,
    biography: formData.biography,
    url: formData.url || null,
    avatar: avatarPreview.value,
    events: isEditing.value ? instructors.value[editingIndex.value].events : []
  }

  if (isEditing.value) {
    instructors.value[editingIndex.value] = instructorData
  } else {
    instructors.value.push(instructorData)
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

const deleteInstructor = () => {
  if (deleteIndex.value !== null) {
    instructors.value.splice(deleteIndex.value, 1)
  }
  cancelDelete()
}
</script>