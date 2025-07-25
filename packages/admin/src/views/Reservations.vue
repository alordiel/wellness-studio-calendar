<template>
  <v-container>
    <h1 class="text-h4 mb-6">Bookings</h1>
    <router-link to="/" class="text-blue-600 hover:text-blue-800">← Back to Dashboard</router-link>

    <!-- Add New Reservation Button -->
    <v-tabs v-model="activeTab" class="mb-6">
      <v-tab value="reservations">Reservations</v-tab>
      <v-tab value="events-preview">Events Preview</v-tab>
    </v-tabs>

    <v-tabs-window v-model="activeTab">
      <v-tabs-window-item value="reservations">
        <v-row class="mb-6">
          <v-col cols="12" class="text-right">
            <v-btn
                color="primary"
                prepend-icon="mdi-plus"
                @click="openNewReservation = true"
            >
              Add New Reservation
            </v-btn>
          </v-col>
        </v-row>
        <!-- Reservations Data Table -->
        <v-sheet border rounded>
          <v-data-table
              :headers="headers"
              :items="reservations"
              :sort-by="[{ key: 'eventName', order: 'asc' }]"
              class="elevation-1"
          >
            <template v-slot:item.actions="{ item }">
              <v-btn
                  icon="mdi-eye"
                  size="small"
                  variant="text"
                  @click="viewReservation(item)"
              ></v-btn>
              <v-btn
                  icon="mdi-delete"
                  size="small"
                  variant="text"
                  color="error"
                  @click="confirmDelete(item)"
              ></v-btn>
            </template>
          </v-data-table>
        </v-sheet>
      </v-tabs-window-item>

      <v-tabs-window-item value="events-preview">
        <!-- Events Preview content will be added later -->
        <div class="text-center py-6">
          <Calendar></Calendar>
        </div>
      </v-tabs-window-item>
    </v-tabs-window>

    <!-- Modal Components -->
    <ViewReservationModal
        v-model="openManageReservation"
        :reservation="selectedReservation"
        @delete="handleDeleteFromView"
        @saveNote="handleSaveNote"
        @close="closeViewModal"
    />

    <DeleteReservationModal
        v-model="deleteDialog"
        :reservation="reservationToDelete"
        @confirm="handleDeleteConfirm"
        @cancel="closeDeleteModal"
    />

    <AddReservationModal
        v-model="openNewReservation"
        @success="handleAddSuccess"
        @cancel="closeAddModal"
    />
  </v-container>
</template>

<script setup>
import {ref} from 'vue'
import Calendar from "../components/reservations/Calendar.vue"
import ViewReservationModal from "../components/reservations/ViewReservationModal.vue"
import DeleteReservationModal from "../components/reservations/DeleteReservationModal.vue"
import AddReservationModal from "../components/reservations/AddReservationModal.vue"

// Reactive data
const activeTab = ref('reservations')
const openManageReservation = ref(false)
const deleteDialog = ref(false)
const openNewReservation = ref(false)
const selectedReservation = ref(null)
const reservationToDelete = ref(null)

// Mock data
const reservations = ref([
  {
    id: 1,
    eventName: 'Morning Yoga Class',
    dateTimeReservation: '2024-11-15T08:00:00',
    userName: 'John Doe',
    userEmail: 'john.doe@example.com',
    userPhone: '+1234567890',
    userNotes: 'First time attending, excited! Please arrive 10 minutes early.',
    dateCreation: '2024-11-10T14:30:00',
    adminNotes: [
      {
        date: '2024-11-11T09:00:00',
        adminName: 'Sarah Admin',
        content: 'New member, offered intro package. Please arrive 10 minutes early.'
      }
    ]
  },
  {
    id: 2,
    eventName: 'Evening Meditation',
    dateTimeReservation: '2024-11-16T18:00:00',
    userName: 'Jane Smith',
    userEmail: 'jane.smith@example.com',
    userPhone: '+1987654321',
    userNotes: 'Need a quiet corner if possible',
    dateCreation: '2024-11-12T09:15:00',
    adminNotes: []
  },
  {
    id: 3,
    eventName: 'Power Yoga Session',
    dateTimeReservation: '2024-11-17T10:00:00',
    userName: 'Mike Johnson',
    userEmail: 'mike.j@example.com',
    userPhone: '+1122334455',
    userNotes: '',
    dateCreation: '2024-11-13T16:45:00',
    adminNotes: [
      {
        date: '2024-11-13T17:00:00',
        adminName: 'Admin User',
        content: 'Regular participant, advanced level'
      },
      {
        date: '2024-11-14T09:00:00',
        adminName: 'Another Admin',
        content: 'Requested specific mat placement near window'
      }
    ]
  }
])

// Table headers
const headers = [
  {title: 'Event Name', key: 'eventName', sortable: true},
  {
    title: 'Date & Time Reservation',
    key: 'dateTimeReservation',
    sortable: true,
    value: (item) => formatDate(item.dateTimeReservation)
  },
  {title: 'User Name', key: 'userName', sortable: false},
  {title: 'Actions', key: 'actions', sortable: false}
]

// Methods
const formatDate = (dateString) => {
  if (!dateString) return ''
  const date = new Date(dateString)
  return date.toLocaleString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

const viewReservation = (item) => {
  selectedReservation.value = {...item}
  openManageReservation.value = true
}

const confirmDelete = (item) => {
  reservationToDelete.value = item
  deleteDialog.value = true
}

const handleDeleteFromView = (reservation) => {
  reservationToDelete.value = reservation
  openManageReservation.value = false
  deleteDialog.value = true
}

const handleDeleteConfirm = (reservation) => {
  // In real app, this would call an API
  const index = reservations.value.findIndex(r => r.id === reservation.id)
  if (index > -1) {
    reservations.value.splice(index, 1)
  }
  deleteDialog.value = false
  reservationToDelete.value = null
}

const handleSaveNote = ({reservation, note}) => {
  // Add note to selected reservation
  if (!reservation.adminNotes) {
    reservation.adminNotes = []
  }
  reservation.adminNotes.push(note)

  // Update the original reservation in the list
  const index = reservations.value.findIndex(r => r.id === reservation.id)
  if (index > -1) {
    reservations.value[index] = {...reservation}
  }

  // Update selectedReservation to reflect the change
  selectedReservation.value = {...reservation}
}

const handleAddSuccess = (newReservation) => {
  // Add the new reservation to the list (in real app, this would be handled by the store)
  const reservationForTable = {
    id: newReservation.id,
    eventName: 'Manual Reservation', // In real app, this would be from selected event
    dateTimeReservation: newReservation.dateTimeReservation,
    userName: newReservation.user_name,
    userEmail: newReservation.email,
    userPhone: newReservation.phone,
    userNotes: newReservation.user_notes || '',
    dateCreation: newReservation.dateCreation,
    adminNotes: newReservation.admin_notes ? [
      {
        date: newReservation.dateCreation,
        adminName: 'Current Admin',
        content: newReservation.admin_notes
      }
    ] : []
  }

  reservations.value.push(reservationForTable)
  console.log('New reservation added:', newReservation)
}

// Modal close handlers
const closeViewModal = () => {
  openManageReservation.value = false
  selectedReservation.value = null
}

const closeDeleteModal = () => {
  deleteDialog.value = false
  reservationToDelete.value = null
}

const closeAddModal = () => {
  openNewReservation.value = false
}
</script>
