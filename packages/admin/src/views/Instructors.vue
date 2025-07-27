<template>
  <v-container>
    <ViewTitle  title="Instructor's Management" />

    <div class="d-flex justify-end">
      <v-btn
          color="primary"
          @click="openAddModal"
          prepend-icon="mdi-plus"
      >
        Add New Instructor
      </v-btn>
    </div>

    <!-- Instructors Data Table -->
    <v-data-table
      :headers="headers"
      :items="instructors"
      :sort-by="[{ key: 'name', order: 'asc' }]"
      item-key="id"
      class="elevation-1"
    >
      <template v-slot:item.name="{ item }">
        <div class="d-flex align-center">
          <v-avatar v-if="item.avatar" size="40" class="mr-3">
            <v-img
              :src="item.avatar"
              :alt="item.name"
            />
          </v-avatar>
          <v-avatar
              v-else
              color="brown"
              size="40px"
            >
              <span>{{ getInitials(item.name) }}</span>
            </v-avatar>
          <span class="font-weight-medium ml-3">{{ item.name }}</span>
        </div>
      </template>

      <template v-slot:item.biography="{ item }">
        <div class="text-truncate" style="max-width: 300px;">
          {{ getDescriptionExcerpt(item.biography) }}
        </div>
      </template>

      <template v-slot:item.link="{ item }">
        <a
          v-if="item.link"
          :href="item.link"
          target="_blank"
          rel="noopener noreferrer"
          class="text-decoration-none"
        >
          {{ truncateLink(item.link) }}
        </a>
        <span v-else class="text-grey">No URL</span>
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

    <!-- Edit Instructor Modal -->
    <EditInstructor
      v-model:dialog="showModal"
      :instructor="selectedInstructor"
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
          Are you sure you want to delete this instructor?
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
            @click="deleteInstructor"
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
import { useInstructorStore } from '../store/instructor'
import EditInstructor from '../components/instructors/EditInstructor.vue'
import ViewTitle from "../components/View-title.vue";

// Store
const instructorStore = useInstructorStore()

// Reactive data
const showModal = ref(false)
const showDeleteConfirm = ref(false)
const isEditing = ref(false)
const selectedInstructor = ref(null)
const instructorToDelete = ref(null)

// Computed properties
const instructors = computed(() => instructorStore.getAllInstructors)

// Data table headers
const headers = [
  { 
    title: 'Name', 
    key: 'name',
    sortable: true
  },
  { 
    title: 'Biography', 
    key: 'biography',
    sortable: false
  },
  { 
    title: 'URL', 
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

const getInitials = (name) => {
  const names = name.split(' ');
  let initials = name.charAt(0);
  if (names.length > 1) {
    initials += names[1].charAt(0)
  }
  return initials;
}

const openAddModal = () => {
  selectedInstructor.value = null
  isEditing.value = false
  showModal.value = true
}

const openEditModal = (instructor) => {
  selectedInstructor.value = instructor
  isEditing.value = true
  showModal.value = true
}

const closeModal = () => {
  showModal.value = false
  selectedInstructor.value = null
}

const handleSave = async (instructorData) => {
  try {
    if (isEditing.value) {
      await instructorStore.updateInstructor(selectedInstructor.value.id, instructorData)
    } else {
      // Generate new ID for new instructor
      instructorData.id = Date.now()
      await instructorStore.addInstructor(instructorData)
    }
    closeModal()
  } catch (error) {
    console.error('Error saving instructor:', error)
  }
}

const confirmDelete = (instructor) => {
  instructorToDelete.value = instructor
  showDeleteConfirm.value = true
}

const cancelDelete = () => {
  instructorToDelete.value = null
  showDeleteConfirm.value = false
}

const deleteInstructor = async () => {
  try {
    if (instructorToDelete.value) {
      await instructorStore.deleteInstructor(instructorToDelete.value.id)
    }
    cancelDelete()
  } catch (error) {
    console.error('Error deleting instructor:', error)
  }
}
</script>