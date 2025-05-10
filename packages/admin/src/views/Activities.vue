<template>
  <div class="p-6">
    <!-- Header with Add Button -->
    <div class="flex justify-between items-center mb-6">
      <h1 class="text-2xl font-bold text-gray-800">Activities Management</h1>
      <router-link to="/" class="text-blue-600 hover:text-blue-800">← Back to Dashboard</router-link>
      <button
        @click="openAddModal"
        class="bg-blue-500 hover:bg-blue-600 text-white font-medium py-2 px-4 rounded-lg transition duration-200"
      >
        Add New Entry
      </button>
    </div>

    <!-- Activities Table -->
    <div v-if="activities.length > 0" class="overflow-x-auto shadow-md rounded-lg">
      <table class="w-full bg-white">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Activity Name</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Link</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
          <tr v-for="(activity, index) in activities" :key="index" class="hover:bg-gray-50">
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ activity.activity_name }}</td>
            <td class="px-6 py-4 text-sm text-gray-900">
              <div class="max-w-md">{{ getDescriptionExcerpt(activity.description) }}</div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm">
              <a
                :href="activity.link"
                target="_blank"
                rel="noopener noreferrer"
                class="text-blue-600 hover:text-blue-900 underline"
              >
                {{ truncateLink(activity.link) }}
              </a>
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
      <p class="text-gray-500 text-lg">No activity entries found. Add a new entry to get started.</p>
    </div>

    <!-- Modal Dialog -->
    <div v-if="showModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg p-6 w-full max-w-2xl">
        <h2 class="text-xl font-bold mb-4">{{ modalTitle }}</h2>

        <form @submit.prevent="handleSubmit">
          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-2">Activity Name</label>
            <input
              v-model="formData.activity_name"
              type="text"
              maxlength="255"
              required
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              :class="{ 'border-red-500': errors.activity_name }"
            />
            <p v-if="errors.activity_name" class="text-red-500 text-xs mt-1">{{ errors.activity_name }}</p>
          </div>

          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
            <textarea
              v-model="formData.description"
              rows="4"
              maxlength="500"
              required
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              :class="{ 'border-red-500': errors.description }"
            ></textarea>
            <p class="text-gray-500 text-xs mt-1">{{ formData.description.length }}/500 characters</p>
            <p v-if="errors.description" class="text-red-500 text-xs mt-1">{{ errors.description }}</p>
          </div>

          <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-2">Link (URL)</label>
            <input
              v-model="formData.link"
              type="url"
              maxlength="255"
              required
              placeholder="https://example.com"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              :class="{ 'border-red-500': errors.link }"
            />
            <p v-if="errors.link" class="text-red-500 text-xs mt-1">{{ errors.link }}</p>
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
        <p class="text-gray-600 mb-6">Are you sure you want to delete this activity?</p>
        <div class="flex justify-end space-x-3">
          <button
            @click="cancelDelete"
            class="px-4 py-2 text-gray-700 bg-gray-200 hover:bg-gray-300 rounded-md transition duration-200"
          >
            Cancel
          </button>
          <button
            @click="deleteActivity"
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
const activities = ref([
  // Sample data - remove this when connecting to real data source
  {
    activity_name: 'Morning Yoga Session',
    description: 'Start your day with a refreshing yoga session designed for all skill levels. Our experienced instructors will guide you through breathing exercises and gentle stretches to improve flexibility and mental clarity.',
    link: 'https://example.com/activities/morning-yoga'
  },
  {
    activity_name: 'Digital Marketing Workshop',
    description: 'Learn the fundamentals of digital marketing including SEO, social media marketing, and content strategy. This hands-on workshop covers practical techniques you can apply immediately to grow your online presence.',
    link: 'https://example.com/activities/digital-marketing-workshop'
  }
])

const showModal = ref(false)
const showDeleteConfirm = ref(false)
const isEditing = ref(false)
const editingIndex = ref(null)
const deleteIndex = ref(null)

const formData = reactive({
  activity_name: '',
  description: '',
  link: ''
})

const errors = reactive({
  activity_name: '',
  description: '',
  link: ''
})

// Computed properties
const modalTitle = computed(() => isEditing.value ? 'Edit Activity' : 'Add New Activity')

// Methods
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

const openAddModal = () => {
  resetForm()
  isEditing.value = false
  editingIndex.value = null
  showModal.value = true
}

const openEditModal = (index) => {
  const activity = activities.value[index]
  formData.activity_name = activity.activity_name
  formData.description = activity.description
  formData.link = activity.link
  isEditing.value = true
  editingIndex.value = index
  showModal.value = true
}

const closeModal = () => {
  showModal.value = false
  resetForm()
}

const resetForm = () => {
  formData.activity_name = ''
  formData.description = ''
  formData.link = ''
  errors.activity_name = ''
  errors.description = ''
  errors.link = ''
}

const validateForm = () => {
  let isValid = true

  // Reset errors
  errors.activity_name = ''
  errors.description = ''
  errors.link = ''

  // Validate activity name
  if (!formData.activity_name.trim()) {
    errors.activity_name = 'Activity name is required'
    isValid = false
  } else if (formData.activity_name.length > 255) {
    errors.activity_name = 'Activity name must be less than 255 characters'
    isValid = false
  }

  // Validate description
  if (!formData.description.trim()) {
    errors.description = 'Description is required'
    isValid = false
  } else if (formData.description.length > 500) {
    errors.description = 'Description must be less than 500 characters'
    isValid = false
  }

  // Validate link
  if (!formData.link.trim()) {
    errors.link = 'Link is required'
    isValid = false
  } else if (formData.link.length > 255) {
    errors.link = 'Link must be less than 255 characters'
    isValid = false
  } else if (!isValidUrl(formData.link)) {
    errors.link = 'Please enter a valid URL'
    isValid = false
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

  const activityData = {
    activity_name: formData.activity_name,
    description: formData.description,
    link: formData.link
  }

  if (isEditing.value) {
    activities.value[editingIndex.value] = activityData
  } else {
    activities.value.push(activityData)
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

const deleteActivity = () => {
  if (deleteIndex.value !== null) {
    activities.value.splice(deleteIndex.value, 1)
  }
  cancelDelete()
}
</script>