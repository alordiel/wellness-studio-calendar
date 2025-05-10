<template>
  <div class="p-6">
    <!-- Header with Add Button -->
    <div class="flex justify-between items-center mb-6">
      <h1 class="text-2xl font-bold text-gray-800">Location Management</h1>
      <button
          @click="openAddModal"
          class="bg-blue-500 hover:bg-blue-600 text-white font-medium py-2 px-4 rounded-lg transition duration-200"
      >
        Add New Entry
      </button>
    </div>

    <!-- Locations Table -->
    <div v-if="locations.length > 0" class="overflow-x-auto shadow-md rounded-lg">
      <table class="w-full bg-white">
        <thead class="bg-gray-50">
        <tr>
          <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Address</th>
          <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Hall Name</th>
          <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Max Participants
          </th>
          <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
        </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
        <tr v-for="(location, index) in locations" :key="index" class="hover:bg-gray-50">
          <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ location.address }}</td>
          <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ location.hall_name }}</td>
          <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ location.max_participants }}</td>
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
      <p class="text-gray-500 text-lg">No location entries found. Add a new entry to get started.</p>
    </div>

    <!-- Modal Dialog -->
    <div v-if="showModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg p-6 w-full max-w-md">
        <h2 class="text-xl font-bold mb-4">{{ modalTitle }}</h2>

        <form @submit.prevent="handleSubmit">
          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-2">Address</label>
            <input
                v-model="formData.address"
                type="text"
                maxlength="255"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                :class="{ 'border-red-500': errors.address }"
            />
            <p v-if="errors.address" class="text-red-500 text-xs mt-1">{{ errors.address }}</p>
          </div>

          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-2">Hall Name</label>
            <input
                v-model="formData.hall_name"
                type="text"
                maxlength="255"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                :class="{ 'border-red-500': errors.hall_name }"
            />
            <p v-if="errors.hall_name" class="text-red-500 text-xs mt-1">{{ errors.hall_name }}</p>
          </div>

          <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-2">Max Participants</label>
            <input
                v-model="formData.max_participants"
                type="text"
                required
                @input="validateNumber"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                :class="{ 'border-red-500': errors.max_participants }"
            />
            <p v-if="errors.max_participants" class="text-red-500 text-xs mt-1">{{ errors.max_participants }}</p>
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
        <p class="text-gray-600 mb-6">Are you sure you want to delete this location?</p>
        <div class="flex justify-end space-x-3">
          <button
              @click="cancelDelete"
              class="px-4 py-2 text-gray-700 bg-gray-200 hover:bg-gray-300 rounded-md transition duration-200"
          >
            Cancel
          </button>
          <button
              @click="deleteLocation"
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
import {ref, reactive, computed} from 'vue';

// Reactive data
const locations = ref([
  // Sample data - remove this when connecting to real data source
  {
    id: 1,
    address: '123 Main Street, City Center',
    hall_name: 'Grand Ballroom',
    max_participants: 500
  },
  {
    id: 2,
    address: '456 Park Avenue, Downtown',
    hall_name: 'Conference Hall A',
    max_participants: 150
  }
])

const showModal = ref(false)
const showDeleteConfirm = ref(false)
const isEditing = ref(false)
const editingIndex = ref(null)
const deleteIndex = ref(null)

const formData = reactive({
  id: 0,
  address: '',
  hall_name: '',
  max_participants: ''
})

const errors = reactive({
  address: '',
  hall_name: '',
  max_participants: ''
})

// Computed properties
const modalTitle = computed(() => isEditing.value ? 'Edit Location' : 'Add New Location')

// Methods
const openAddModal = () => {
  resetForm()
  resetErrors()
  isEditing.value = false
  editingIndex.value = null
  showModal.value = true
}

const openEditModal = (index) => {
  const location = locations.value[index];
  formData.id = location.id;
  formData.address = location.address;
  formData.hall_name = location.hall_name;
  formData.max_participants = location.max_participants;
  isEditing.value = true;
  editingIndex.value = index;
  showModal.value = true;
}

const closeModal = () => {
  showModal.value = false;
  resetForm();
  resetErrors();
}

const resetForm = () => {
  formData.id = 0;
  formData.address = '';
  formData.hall_name = '';
  formData.max_participants = '';

}

const resetErrors = () => {
  errors.address = '';
  errors.hall_name = '';
  errors.max_participants = '';
}

const validateNumber = (event) => {
  const value = event.target.value
  // Remove non-numeric characters
  formData.max_participants = value.replace(/\D/g, '')
}

const validateForm = () => {
  let isValid = true

  resetErrors();

  // Validate address
  if (!formData.address.trim()) {
    errors.address = 'Address is required'
    isValid = false
  } else if (formData.address.length > 255) {
    errors.address = 'Address must be less than 255 characters'
    isValid = false
  }

  // Validate hall name
  if (!formData.hall_name.trim()) {
    errors.hall_name = 'Hall name is required'
    isValid = false
  } else if (formData.hall_name.length > 255) {
    errors.hall_name = 'Hall name must be less than 255 characters'
    isValid = false
  }

  // Validate max participants
  if (!formData.max_participants) {
    errors.max_participants = 'Max participants is required'
    isValid = false
  } else if (!/^\d+$/.test(formData.max_participants)) {
    errors.max_participants = 'Only numbers are allowed'
    isValid = false
  }

  return isValid
}

const handleSubmit = () => {
  if (!validateForm()) return

  const locationData = {
    id: formData.id,
    address: formData.address,
    hall_name: formData.hall_name,
    max_participants: parseInt(formData.max_participants)
  }

  if (isEditing.value) {
    locations.value[editingIndex.value] = locationData
  } else {
    locations.value.push(locationData)
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

const deleteLocation = () => {
  if (deleteIndex.value !== null) {
    locations.value.splice(deleteIndex.value, 1)
  }
  cancelDelete()
}
</script>