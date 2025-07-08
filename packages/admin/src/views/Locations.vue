<template>
  <v-container>
    <!-- Header with Add Button -->
    <v-row class="mb-6">
      <v-col cols="12" md="6">
        <h1 class="text-h4 font-weight-bold">Location Management</h1>
      </v-col>
      <v-col cols="12" md="6" class="text-right">
        <v-btn
          color="primary"
          @click="openAddModal"
          prepend-icon="mdi-plus"
        >
          Add New Location
        </v-btn>
      </v-col>
    </v-row>

    <!-- Locations Data Table -->
    <v-data-table
      :headers="headers"
      :items="locations"
      :sort-by="[{ key: 'hall', order: 'asc' }]"
      item-key="id"
      class="elevation-1"
    >
      <template v-slot:item.address="{ item }">
        <div class="d-flex align-center">
          <v-icon class="mr-2" color="blue">mdi-map-marker</v-icon>
          <span>{{ item.address }}</span>
        </div>
      </template>

      <template v-slot:item.hall="{ item }">
        <div class="d-flex align-center">
          <v-icon class="mr-2" color="green">mdi-door</v-icon>
          <span class="font-weight-medium">{{ item.hall }}</span>
        </div>
      </template>

      <template v-slot:item.max_participants="{ item }">
        <v-chip color="primary" size="small">
          {{ item.max_participants }}
        </v-chip>
      </template>

      <template v-slot:item.actions="{ item }">
        <v-btn
          icon="mdi-pencil"
          size="small"
          color="blue"
          variant="text"
          @click="openEditModal(item)"
        />
        <v-btn
          icon="mdi-delete"
          size="small"
          color="red"
          variant="text"
          @click="confirmDelete(item)"
        />
      </template>
    </v-data-table>

    <!-- Edit Location Modal -->
    <EditModal
      v-model:dialog="showModal"
      :location="selectedLocation"
      :is-editing="isEditing"
      @save="handleSave"
      @cancel="closeModal"
    />

    <!-- Delete Confirmation Dialog -->
    <v-dialog v-model="showDeleteConfirm" max-width="400px">
      <v-card>
        <v-card-title class="text-h5">
          Confirm Delete
        </v-card-title>
        <v-card-text>
          Are you sure you want to delete this location?
        </v-card-text>
        <v-card-actions>
          <v-spacer />
          <v-btn
            color="grey darken-1"
            variant="text"
            @click="cancelDelete"
          >
            Cancel
          </v-btn>
          <v-btn
            color="red darken-1"
            variant="text"
            @click="deleteLocation"
          >
            Delete
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </v-container>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useLocationStore } from '../store/location'
import EditModal from '../components/locations/EditModal.vue'

// Store
const locationStore = useLocationStore()

// Reactive data
const showModal = ref(false)
const showDeleteConfirm = ref(false)
const isEditing = ref(false)
const selectedLocation = ref(null)
const locationToDelete = ref(null)

// Computed properties
const locations = computed(() => locationStore.getAllLocations)

// Data table headers
const headers = [
  { 
    title: 'Address', 
    key: 'address',
    sortable: false
  },
  { 
    title: 'Hall Name', 
    key: 'hall',
    sortable: true
  },
  { 
    title: 'Max Participants', 
    key: 'max_participants',
    sortable: false
  },
  { 
    title: 'Actions', 
    key: 'actions',
    sortable: false
  }
]

// Methods
const openAddModal = () => {
  selectedLocation.value = null
  isEditing.value = false
  showModal.value = true
}

const openEditModal = (location) => {
  selectedLocation.value = location
  isEditing.value = true
  showModal.value = true
}

const closeModal = () => {
  showModal.value = false
  selectedLocation.value = null
}

const handleSave = async (locationData) => {
  try {
    if (isEditing.value) {
      await locationStore.updateLocation(selectedLocation.value.id, locationData)
    } else {
      // Generate new ID for new location
      locationData.id = Date.now()
      await locationStore.addLocation(locationData)
    }
    closeModal()
  } catch (error) {
    console.error('Error saving location:', error)
  }
}

const confirmDelete = (location) => {
  locationToDelete.value = location
  showDeleteConfirm.value = true
}

const cancelDelete = () => {
  locationToDelete.value = null
  showDeleteConfirm.value = false
}

const deleteLocation = async () => {
  try {
    if (locationToDelete.value) {
      await locationStore.deleteLocation(locationToDelete.value.id)
    }
    cancelDelete()
  } catch (error) {
    console.error('Error deleting location:', error)
  }
}
</script>