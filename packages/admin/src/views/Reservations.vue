<template>
  <v-container>
    <h1 class="text-h4 mb-6">Bookings</h1>
    <router-link to="/" class="text-blue-600 hover:text-blue-800">← Back to Dashboard</router-link>
    <v-tabs v-model="activeTab" class="mb-6">
      <v-tab value="reservations">Reservations</v-tab>
      <v-tab value="events-preview">Events Preview</v-tab>
    </v-tabs>

    <v-tabs-window v-model="activeTab">
      <v-tabs-window-item value="reservations">
        <!-- Reservations Data Table -->
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
      </v-tabs-window-item>

      <v-tabs-window-item value="events-preview">
        <!-- Events Preview content will be added later -->
        <div class="text-center py-6">
          <Calendar></Calendar>
        </div>
      </v-tabs-window-item>
    </v-tabs-window>

    <!-- View Reservation Modal -->
    <v-dialog v-model="viewDialog" max-width="600px">
      <v-card>
        <v-card-title>
          <span class="text-h5">Reservation Details</span>
        </v-card-title>
        <v-card-text>
          <v-container>
            <v-row>
              <v-col cols="12" sm="6">
                <v-text-field
                  label="User Name"
                  :model-value="selectedReservation?.userName"
                  readonly
                  variant="outlined"
                ></v-text-field>
              </v-col>
              <v-col cols="12" sm="6">
                <v-text-field
                  label="Event Name"
                  :model-value="selectedReservation?.eventName"
                  readonly
                  variant="outlined"
                ></v-text-field>
              </v-col>
              <v-col cols="12" sm="6">
                <v-text-field
                  label="Reservation Date"
                  :model-value="formatDate(selectedReservation?.dateTimeReservation)"
                  readonly
                  variant="outlined"
                ></v-text-field>
              </v-col>
              <v-col cols="12" sm="6">
                <v-text-field
                  label="Creation Date"
                  :model-value="formatDate(selectedReservation?.dateCreation)"
                  readonly
                  variant="outlined"
                ></v-text-field>
              </v-col>
              <v-col cols="12" sm="6">
                <v-text-field
                  label="User Email"
                  :model-value="selectedReservation?.userEmail"
                  readonly
                  variant="outlined"
                ></v-text-field>
              </v-col>
              <v-col cols="12" sm="6">
                <v-text-field
                  label="User Phone"
                  :model-value="selectedReservation?.userPhone"
                  readonly
                  variant="outlined"
                ></v-text-field>
              </v-col>
              <v-col cols="12">
                <v-textarea
                  label="User Notes"
                  :model-value="selectedReservation?.userNotes"
                  readonly
                  variant="outlined"
                  rows="2"
                ></v-textarea>
              </v-col>
              <v-col cols="12">
                <h4 class="mb-2">Admin Notes</h4>
                <div v-if="selectedReservation?.adminNotes?.length" class="mb-4">
                  <v-card
                    v-for="(note, index) in selectedReservation.adminNotes"
                    :key="index"
                    class="mb-2"
                    variant="outlined"
                  >
                    <v-card-text>
                      <div class="d-flex justify-space-between mb-1">
                        <span class="font-weight-medium">{{ note.adminName }}</span>
                        <span class="text-caption">{{ formatDate(note.date) }}</span>
                      </div>
                      <p class="mb-0">{{ note.content }}</p>
                    </v-card-text>
                  </v-card>
                </div>
                <v-textarea
                  v-model="newAdminNote"
                  label="Add Admin Note"
                  variant="outlined"
                  rows="3"
                  placeholder="Enter your note here..."
                ></v-textarea>
              </v-col>
            </v-row>
          </v-container>
        </v-card-text>
        <v-card-actions>
          <v-btn color="error" @click="confirmDelete(selectedReservation)">
            Cancel Reservation
          </v-btn>
          <v-spacer></v-spacer>
          <v-btn color="grey" @click="viewDialog = false">Close</v-btn>
          <v-btn color="primary" @click="saveAdminNote">Save Note</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- Delete Confirmation Modal -->
    <v-dialog v-model="deleteDialog" max-width="400px">
      <v-card>
        <v-card-title class="text-h5">Confirm Cancellation</v-card-title>
        <v-card-text>
          Are you sure you want to cancel this reservation? An email notification will be sent to the user.
        </v-card-text>
        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn color="grey" @click="deleteDialog = false">Cancel</v-btn>
          <v-btn color="error" @click="deleteReservation">Confirm</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </v-container>
</template>

<script setup>
import { ref, computed } from 'vue'
import Calendar from "../components/Calendar.vue";

// Reactive data
const activeTab = ref('reservations')
const viewDialog = ref(false)
const deleteDialog = ref(false)
const selectedReservation = ref(null)
const newAdminNote = ref('')
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
    userNotes: 'First time attending, excited!',
    dateCreation: '2024-11-10T14:30:00',
    adminNotes: [
      {
        date: '2024-11-11T10:00:00',
        adminName: 'Admin User',
        content: 'Welcome to our yoga class! Please arrive 10 minutes early.'
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
  { title: 'Event Name', key: 'eventName', sortable: true },
  {
    title: 'Date & Time Reservation',
    key: 'dateTimeReservation',
    sortable: true,
    value: (item) => formatDate(item.dateTimeReservation)
  },
  { title: 'User Name', key: 'userName', sortable: false },
  { title: 'Actions', key: 'actions', sortable: false }
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
  selectedReservation.value = { ...item }
  viewDialog.value = true
}

const confirmDelete = (item) => {
  reservationToDelete.value = item
  deleteDialog.value = true
}

const deleteReservation = () => {
  // In real app, this would call an API
  const index = reservations.value.findIndex(r => r.id === reservationToDelete.value.id)
  if (index > -1) {
    reservations.value.splice(index, 1)
  }
  deleteDialog.value = false
  viewDialog.value = false
  reservationToDelete.value = null
}

const saveAdminNote = () => {
  if (!newAdminNote.value.trim()) return

  const note = {
    date: new Date().toISOString(),
    adminName: 'Current Admin', // In real app, get from user session
    content: newAdminNote.value
  }

  // Add note to selected reservation
  if (!selectedReservation.value.adminNotes) {
    selectedReservation.value.adminNotes = []
  }
  selectedReservation.value.adminNotes.push(note)

  // Update the original reservation in the list
  const index = reservations.value.findIndex(r => r.id === selectedReservation.value.id)
  if (index > -1) {
    reservations.value[index] = { ...selectedReservation.value }
  }

  newAdminNote.value = ''
}
</script>

<style scoped>
/* Add any custom styles here */
</style>