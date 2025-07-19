<template>
  <v-container>
    <ViewTitle  title="Activities Management" />
    <!-- Header with Add Button -->
    <div class="d-flex justify-end">
      <v-btn
        color="primary"
        @click="openAddModal"
        prepend-icon="mdi-plus"
      >
        Add New Activity
      </v-btn>
    </div>
    <!-- Activities Data Table -->
    <v-data-table
      :headers="headers"
      :items="activities"
      :sort-by="[{ key: 'name', order: 'asc' }]"
      item-key="id"
      class="elevation-1"
    >
      <template v-slot:item.name="{ item }">
        <div class="d-flex align-center">
          <v-avatar size="30" class="mr-3" :color="item.color">
            <v-icon color="white">mdi-fitness</v-icon>
          </v-avatar>
          <span class="font-weight-medium">{{ item.name }}</span>
        </div>
      </template>

      <template v-slot:item.description="{ item }">
        <div class="text-truncate" style="max-width: 300px;">
          {{ getDescriptionExcerpt(item.description) }}
        </div>
      </template>

      <template v-slot:item.color="{ item }">
        <v-chip :color="item.color" size="small">
          {{ item.color }}
        </v-chip>
      </template>

      <template v-slot:item.link="{ item }">
        <a
          :href="item.link"
          target="_blank"
          rel="noopener noreferrer"
          class="text-decoration-none"
        >
          {{ truncateLink(item.link) }}
        </a>
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

    <!-- Edit Activity Modal -->
    <EditModal
      v-model:dialog="showModal"
      :activity="selectedActivity"
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
          Are you sure you want to delete this activity?
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
            @click="deleteActivity"
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
import { useActivityStore } from '../store/activity'
import EditModal from '../components/activities/EditModal.vue'
import ViewTitle from "../components/View-title.vue";

// Store
const activityStore = useActivityStore()

// Reactive data
const showModal = ref(false)
const showDeleteConfirm = ref(false)
const isEditing = ref(false)
const selectedActivity = ref(null)
const activityToDelete = ref(null)

// Computed properties
const activities = computed(() => activityStore.getAllActivities)

// Data table headers
const headers = [
  { 
    title: 'Activity Name', 
    key: 'name',
    sortable: true
  },
  { 
    title: 'Description', 
    key: 'description',
    sortable: false
  },
  { 
    title: 'Color', 
    key: 'color',
    sortable: false
  },
  { 
    title: 'Link', 
    key: 'link',
    sortable: false
  },
  { 
    title: 'Actions', 
    key: 'actions',
    sortable: false
  }
]

// Methods
const getDescriptionExcerpt = (description) => {
  if (!description) return ''
  if (description.length <= 250) {
    return description
  }
  return description.substring(0, 247) + '...'
}

const truncateLink = (link) => {
  if (!link) return ''
  if (link.length > 50) {
    return link.substring(0, 47) + '...'
  }
  return link
}

const openAddModal = () => {
  selectedActivity.value = null
  isEditing.value = false
  showModal.value = true
}

const openEditModal = (activity) => {
  selectedActivity.value = activity
  isEditing.value = true
  showModal.value = true
}

const closeModal = () => {
  showModal.value = false
  selectedActivity.value = null
}

const handleSave = async (activityData) => {
  try {
    if (isEditing.value) {
      await activityStore.updateActivity(selectedActivity.value.id, activityData)
    } else {
      // Generate new ID for new activity
      activityData.id = Date.now()
      await activityStore.addActivity(activityData)
    }
    closeModal()
  } catch (error) {
    console.error('Error saving activity:', error)
  }
}

const confirmDelete = (activity) => {
  activityToDelete.value = activity
  showDeleteConfirm.value = true
}

const cancelDelete = () => {
  activityToDelete.value = null
  showDeleteConfirm.value = false
}

const deleteActivity = async () => {
  try {
    if (activityToDelete.value) {
      await activityStore.deleteActivity(activityToDelete.value.id)
    }
    cancelDelete()
  } catch (error) {
    console.error('Error deleting activity:', error)
  }
}
</script>