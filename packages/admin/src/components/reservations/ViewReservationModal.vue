<template>
  <v-dialog v-model="localDialog" max-width="600px">
    <v-card>
      <v-card-title>
        <span class="text-h5">Reservation Details</span>
      </v-card-title>
      <v-card-text>
        <v-container>
          <v-row>
            <v-col cols="12" sm="6">
              <div class="mb-4">
                <v-label class="text-body-2 font-weight-medium mb-1">User Name</v-label>
                <div class="text-body-1 pa-3 bg-grey-lighten-5 rounded">
                  {{ reservation?.userName || 'N/A' }}
                </div>
              </div>
            </v-col>
            <v-col cols="12" sm="6">
              <div class="mb-4">
                <v-label class="text-body-2 font-weight-medium mb-1">Event Name</v-label>
                <div class="text-body-1 pa-3 bg-grey-lighten-5 rounded">
                  {{ reservation?.eventName || 'N/A' }}
                </div>
              </div>
            </v-col>
            <v-col cols="12" sm="6">
              <div class="mb-4">
                <v-label class="text-body-2 font-weight-medium mb-1">Reservation Date</v-label>
                <div class="text-body-1 pa-3 bg-grey-lighten-5 rounded">
                  {{ formatDate(reservation?.dateTimeReservation) }}
                </div>
              </div>
            </v-col>
            <v-col cols="12" sm="6">
              <div class="mb-4">
                <v-label class="text-body-2 font-weight-medium mb-1">Creation Date</v-label>
                <div class="text-body-1 pa-3 bg-grey-lighten-5 rounded">
                  {{ formatDate(reservation?.dateCreation) }}
                </div>
              </div>
            </v-col>
            <v-col cols="12" sm="6">
              <div class="mb-4">
                <v-label class="text-body-2 font-weight-medium mb-1">User Email</v-label>
                <div class="text-body-1 pa-3 bg-grey-lighten-5 rounded">
                  {{ reservation?.userEmail || 'N/A' }}
                </div>
              </div>
            </v-col>
            <v-col cols="12" sm="6">
              <div class="mb-4">
                <v-label class="text-body-2 font-weight-medium mb-1">User Phone</v-label>
                <div class="text-body-1 pa-3 bg-grey-lighten-5 rounded">
                  {{ reservation?.userPhone || 'N/A' }}
                </div>
              </div>
            </v-col>
            <v-col cols="12" v-if="reservation?.userNotes ">
              <div class="mb-4">
                <v-label class="text-body-2 font-weight-medium mb-1">User Notes</v-label>
                <div class="text-body-1 pa-3 bg-grey-lighten-5 rounded" style="min-height: 60px;">
                  {{ reservation.userNotes  }}
                </div>
              </div>
            </v-col>
            <v-col cols="12">
              <h4 class="mb-2">Admin Notes</h4>
              <div v-if="reservation?.adminNotes?.length" class="mb-4">
                <v-card
                  v-for="(note, index) in reservation.adminNotes"
                  :key="index"
                  class="mb-2"
                  variant="outlined"
                >
                  <v-card-text>
                    <div class="d-flex justify-space-between mb-1">
                      <span class="font-weight-medium">by <strong>{{ note.author }}</strong></span>
                      <span class="text-caption">{{ note.created_at }}</span>
                    </div>
                    <p class="mb-0">{{ note.content }}</p>
                  </v-card-text>
                </v-card>
              </div>
              <div v-else class="text-body-2 text-grey pa-3 bg-grey-lighten-5 rounded mb-4">
                No admin notes yet
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
        <v-btn color="error" @click="handleDelete">
          Cancel Reservation
        </v-btn>
        <v-spacer></v-spacer>
        <v-btn color="grey" @click="handleClose">Close</v-btn>
        <v-btn color="primary" @click="handleSaveNote">Save Note</v-btn>
      </v-card-actions>
    </v-card>
  </v-dialog>
</template>

<script setup>
import { ref, watch } from 'vue'

const props = defineProps({
  modelValue: {
    type: Boolean,
    default: false
  },
  reservation: {
    type: Object,
    default: null
  }
})

const emit = defineEmits(['update:modelValue', 'delete', 'saveNote', 'close'])

const localDialog = ref(false)
const newAdminNote = ref('')

// Watch for changes to the prop and update local state
watch(() => props.modelValue, (newVal) => {
  localDialog.value = newVal
  if (!newVal) {
    newAdminNote.value = ''
  }
})

// Watch local dialog changes and emit to parent
watch(localDialog, (newVal) => {
  if (newVal !== props.modelValue) {
    emit('update:modelValue', newVal)
  }
})

const formatDate = (dateString) => {
  if (!dateString) return 'N/A'
  const date = new Date(dateString)
  return date.toLocaleString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

const handleClose = () => {
  localDialog.value = false
  emit('close')
}

const handleDelete = () => {
  emit('delete', props.reservation)
}

const handleSaveNote = () => {
  if (!newAdminNote.value.trim()) return

  const note = {
    date: new Date().toISOString(),
    adminName: 'Current Admin', // In real app, get from user session
    content: newAdminNote.value
  }

  emit('saveNote', { reservation: props.reservation, note })
  newAdminNote.value = ''
}
</script>