<template>
  <v-container>
    <v-data-table
      :headers="headers"
      :items="events"
      :group-by="[{ key: 'date', order: 'asc' }]"
      class="elevation-1"
    >
      <!-- Group Header Slot -->
      <template v-slot:group-header="{ item, columns, toggleGroup, isGroupOpen }">
        <template :ref="(el) => { groupHeaders[item.value] = { item, toggleGroup, isGroupOpen } }"/>
        <tr class="bg-grey-lighten-3">
          <td :colspan="columns.length">
            <v-btn
              :icon="isGroupOpen(item) ? 'mdi-chevron-down' : 'mdi-chevron-right'"
              size="small"
              variant="text"
              @click="toggleGroup(item)"
            ></v-btn>
            <span class="font-weight-bold">
              {{ formatDateWithWeekday(item.value) }}
            </span>
          </td>
        </tr>
      </template>

      <!-- Start Time Column -->
      <template v-slot:item.startTime="{ item }">
        {{ formatTime(item.startTime) }}
      </template>

      <!-- Participants Column -->
      <template v-slot:item.participants="{ item }">
        <v-chip
          :color="item.booked >= item.available ? 'error' : 'success'"
          size="small"
        >
          {{ item.booked }} / {{ item.available }}
        </v-chip>
      </template>

      <!-- Actions Column -->
      <template v-slot:item.actions="{ item }">
        <v-btn
          icon="mdi-eye"
          size="small"
          variant="text"
          @click="viewEvent(item)"
        ></v-btn>
      </template>
    </v-data-table>

    <!-- Event Participants Modal -->
    <v-dialog v-model="eventDialog" max-width="800px">
      <v-card>
        <v-card-title>
          <span class="text-h5">{{ selectedEvent?.eventName }} - Participants</span>
        </v-card-title>
        <v-card-text>
          <v-container>
            <v-row class="mb-4">
              <v-col cols="12" sm="6">
                <v-text-field
                  label="Event"
                  :model-value="selectedEvent?.eventName"
                  readonly
                  variant="outlined"
                ></v-text-field>
              </v-col>
              <v-col cols="12" sm="6">
                <v-text-field
                  label="Instructor"
                  :model-value="selectedEvent?.instructor"
                  readonly
                  variant="outlined"
                ></v-text-field>
              </v-col>
              <v-col cols="12" sm="6">
                <v-text-field
                  label="Date & Time"
                  :model-value="formatDateTime(selectedEvent?.date, selectedEvent?.startTime)"
                  readonly
                  variant="outlined"
                ></v-text-field>
              </v-col>
              <v-col cols="12" sm="6">
                <v-text-field
                  label="Location"
                  :model-value="selectedEvent?.location"
                  readonly
                  variant="outlined"
                ></v-text-field>
              </v-col>
            </v-row>

            <h4 class="mb-3">Participants ({{ selectedEvent?.participants?.length || 0 }})</h4>
            <v-data-table
              :headers="participantHeaders"
              :items="selectedEvent?.participants || []"
              density="compact"
              class="elevation-1"
            >
              <template v-slot:item.actions="{ item }">
                <v-btn
                  icon="mdi-eye"
                  size="x-small"
                  variant="text"
                  @click="viewParticipant(item)"
                ></v-btn>
                <v-btn
                  icon="mdi-delete"
                  size="x-small"
                  variant="text"
                  color="error"
                  @click="confirmCancelBooking(item)"
                ></v-btn>
              </template>
            </v-data-table>
          </v-container>
        </v-card-text>
        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn color="grey" @click="eventDialog = false">Close</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- Participant Details Modal (reusing structure from previous component) -->
    <v-dialog v-model="participantDialog" max-width="600px">
      <v-card>
        <v-card-title>
          <span class="text-h5">Participant Details</span>
        </v-card-title>
        <v-card-text>
          <v-container>
            <v-row>
              <v-col cols="12" sm="6">
                <v-text-field
                  label="User Name"
                  :model-value="selectedParticipant?.userName"
                  readonly
                  variant="outlined"
                ></v-text-field>
              </v-col>
              <v-col cols="12" sm="6">
                <v-text-field
                  label="Event Name"
                  :model-value="selectedEvent?.eventName"
                  readonly
                  variant="outlined"
                ></v-text-field>
              </v-col>
              <v-col cols="12" sm="6">
                <v-text-field
                  label="Reservation Date"
                  :model-value="formatDate(selectedParticipant?.bookingDate)"
                  readonly
                  variant="outlined"
                ></v-text-field>
              </v-col>
              <v-col cols="12" sm="6">
                <v-text-field
                  label="Creation Date"
                  :model-value="formatDate(selectedParticipant?.createdAt)"
                  readonly
                  variant="outlined"
                ></v-text-field>
              </v-col>
              <v-col cols="12" sm="6">
                <v-text-field
                  label="User Email"
                  :model-value="selectedParticipant?.email"
                  readonly
                  variant="outlined"
                ></v-text-field>
              </v-col>
              <v-col cols="12" sm="6">
                <v-text-field
                  label="User Phone"
                  :model-value="selectedParticipant?.phone"
                  readonly
                  variant="outlined"
                ></v-text-field>
              </v-col>
              <v-col cols="12">
                <v-textarea
                  label="User Notes"
                  :model-value="selectedParticipant?.userNotes"
                  readonly
                  variant="outlined"
                  rows="2"
                ></v-textarea>
              </v-col>
              <v-col cols="12">
                <h4 class="mb-2">Admin Notes</h4>
                <div v-if="selectedParticipant?.adminNotes?.length" class="mb-4">
                  <v-card
                    v-for="(note, index) in selectedParticipant.adminNotes"
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
          <v-btn color="error" @click="confirmCancelBooking(selectedParticipant)">
            Cancel Booking
          </v-btn>
          <v-spacer></v-spacer>
          <v-btn color="grey" @click="participantDialog = false">Close</v-btn>
          <v-btn color="primary" @click="saveAdminNote">Save Note</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- Cancel Booking Confirmation Modal -->
    <v-dialog v-model="cancelDialog" max-width="400px">
      <v-card>
        <v-card-title class="text-h5">Confirm Cancellation</v-card-title>
        <v-card-text>
          Are you sure you want to cancel this booking? An email notification will be sent to the user.
        </v-card-text>
        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn color="grey" @click="cancelDialog = false">Cancel</v-btn>
          <v-btn color="error" @click="cancelBooking">Confirm</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </v-container>
</template>

<script setup>
import {onMounted, ref} from 'vue'
const groupHeaders = ref([])

 onMounted(() => {
    Object.values(groupHeaders.value).forEach(header => {
      header.toggleGroup(header.item)
    })
  })

// Reactive data
const eventDialog = ref(false)
const participantDialog = ref(false)
const cancelDialog = ref(false)
const selectedEvent = ref(null)
const selectedParticipant = ref(null)
const participantToCancel = ref(null)
const newAdminNote = ref('')

// Mock events data
const events = ref([
  {
    id: 1,
    date: '2024-11-15',
    startTime: '08:00',
    eventName: 'Morning Yoga Class',
    instructor: 'Sarah Johnson',
    location: 'Studio A',
    booked: 12,
    available: 15,
    participants: [
      {
        id: 1,
        userName: 'John Doe',
        email: 'john.doe@example.com',
        phone: '+1234567890',
        bookingDate: '2024-11-10T14:30:00',
        createdAt: '2024-11-10T14:30:00',
        userNotes: 'First time attending',
        adminNotes: []
      },
      {
        id: 2,
        userName: 'Jane Smith',
        email: 'jane.smith@example.com',
        phone: '+1987654321',
        bookingDate: '2024-11-11T09:15:00',
        createdAt: '2024-11-11T09:15:00',
        userNotes: '',
        adminNotes: [
          {
            date: '2024-11-12T10:00:00',
            adminName: 'Admin User',
            content: 'Regular member'
          }
        ]
      }
    ]
  },
  {
    id: 2,
    date: '2024-11-15',
    startTime: '10:00',
    eventName: 'Power Yoga Session',
    instructor: 'Mike Wilson',
    location: 'Studio B',
    booked: 15,
    available: 15,
    participants: []
  },
  {
    id: 3,
    date: '2024-11-16',
    startTime: '18:00',
    eventName: 'Evening Meditation',
    instructor: 'Lisa Chen',
    location: 'Meditation Room',
    booked: 8,
    available: 10,
    participants: []
  },
  {
    id: 4,
    date: '2024-11-16',
    startTime: '19:30',
    eventName: 'Yoga Nidra',
    instructor: 'Sarah Johnson',
    location: 'Studio A',
    booked: 5,
    available: 12,
    participants: []
  }
])

// Table headers
const headers = [
  { title: 'Start Time', key: 'startTime', sortable: true },
  { title: 'Event Name', key: 'eventName', sortable: true },
  { title: 'Instructor', key: 'instructor', sortable: true },
  { title: 'Location', key: 'location', sortable: true },
  { title: 'Participants', key: 'participants', sortable: false },
  { title: 'Actions', key: 'actions', sortable: false }
]

const participantHeaders = [
  { title: 'Name', key: 'userName' },
  { title: 'Email', key: 'email' },
  { title: 'Phone', key: 'phone' },
  { title: 'Actions', key: 'actions', sortable: false }
]

// Methods
const formatDateWithWeekday = (dateString) => {
  const date = new Date(dateString)
  const weekdays = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday']
  const weekday = weekdays[date.getDay()]

  return `${weekday}, ${date.toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  })}`
}

const formatTime = (timeString) => {
  const [hours, minutes] = timeString.split(':')
  const date = new Date()
  date.setHours(parseInt(hours), parseInt(minutes))

  return date.toLocaleTimeString('en-US', {
    hour: '2-digit',
    minute: '2-digit',
    hour12: true
  })
}

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

const formatDateTime = (dateString, timeString) => {
  if (!dateString || !timeString) return ''
  const [year, month, day] = dateString.split('-')
  const [hours, minutes] = timeString.split(':')
  const date = new Date(year, month - 1, day, hours, minutes)

  return date.toLocaleString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

const viewEvent = (event) => {
  selectedEvent.value = { ...event }
  eventDialog.value = true
}

const viewParticipant = (participant) => {
  selectedParticipant.value = { ...participant }
  participantDialog.value = true
}

const confirmCancelBooking = (participant) => {
  participantToCancel.value = participant
  cancelDialog.value = true
}

const cancelBooking = () => {
  // In real app, this would call an API
  if (selectedEvent.value && participantToCancel.value) {
    const index = selectedEvent.value.participants.findIndex(p => p.id === participantToCancel.value.id)
    if (index > -1) {
      selectedEvent.value.participants.splice(index, 1)
      selectedEvent.value.booked--
    }
  }

  cancelDialog.value = false
  participantDialog.value = false
  participantToCancel.value = null
}

const saveAdminNote = () => {
  if (!newAdminNote.value.trim()) return

  const note = {
    date: new Date().toISOString(),
    adminName: 'Current Admin', // In real app, get from user session
    content: newAdminNote.value
  }

  // Add note to selected participant
  if (!selectedParticipant.value.adminNotes) {
    selectedParticipant.value.adminNotes = []
  }
  selectedParticipant.value.adminNotes.push(note)

  // Update the participant in the event list
  if (selectedEvent.value && selectedParticipant.value) {
    const index = selectedEvent.value.participants.findIndex(p => p.id === selectedParticipant.value.id)
    if (index > -1) {
      selectedEvent.value.participants[index] = { ...selectedParticipant.value }
    }
  }

  newAdminNote.value = ''
}
</script>

<style scoped>
/* Add any custom styles here */
</style>