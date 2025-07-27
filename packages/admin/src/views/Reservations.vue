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
              :items="formattedReservations"
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
import {computed, ref} from 'vue'
import Calendar from "../components/reservations/Calendar.vue"
import ViewReservationModal from "../components/reservations/ViewReservationModal.vue"
import DeleteReservationModal from "../components/reservations/DeleteReservationModal.vue"
import AddReservationModal from "../components/reservations/AddReservationModal.vue"
import {useReservationStore} from '../store/reservation.js'
import {useEventsStore} from "../store/event.js";
import {useActivityStore} from "../store/activity.js";
import {storeToRefs} from "pinia";

// Reactive data
const activeTab = ref('reservations');
const openManageReservation = ref(false);
const deleteDialog = ref(false);
const openNewReservation = ref(false);
const selectedReservation = ref(null);
const reservationToDelete = ref(null);
const reservationStore = useReservationStore();
const activityStore = useActivityStore();
const eventStore = useEventsStore();
const {reservations} = storeToRefs(reservationStore);

// Table headers
const headers = [
  {title: 'Event Name', key: 'eventName', sortable: true},
  {
    title: 'Date & Time Reservation',
    key: 'dateTimeReservation',
    sortable: true,
    value: (item) => formatDate(item.dateTimeReservation),
  },
  {title: 'User Name', key: 'userName', sortable: false,},
  {title: 'Actions', key: 'actions', sortable: false,}
];

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
  });
};

const viewReservation = (item) => {
  selectedReservation.value = {...item};
  openManageReservation.value = true;
};

const confirmDelete = (item) => {
  reservationToDelete.value = item;
  deleteDialog.value = true;
};

const handleDeleteFromView = (reservation) => {
  reservationToDelete.value = reservation;
  openManageReservation.value = false;
  deleteDialog.value = true;
};

const handleDeleteConfirm = (reservation) => {
  // In real app, this would call an API
  const index = reservations.value.findIndex(r => r.id === reservation.id)
  if (index > -1) {
    reservations.value.splice(index, 1);
  }
  deleteDialog.value = false;
  reservationToDelete.value = null;
};

const handleAddSuccess = (newReservation) => {
  // Add the new reservation to the list (in real app, this would be handled by the store)
  const reservationForTable = {
    id: newReservation.id,
    eventName: newReservation.event_id, // In real app, this would be from selected event
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

const formattedReservations = computed(() => {
  return reservations.value.map((reservation) => {
    const event = eventStore.getEventByEventId(reservation.event_id);
    const activity = activityStore.getActivityById(event.activity_id);

    return {
      id:reservation.id,
      eventName: activity.name,
      dateTimeReservation: '2024-11-15T08:00:00',
      userName: reservation.user_name,
      userEmail: reservation.email,
      userPhone: reservation.phone,
      userNotes:reservation.user_notes || '',
      dateCreation: reservation.created_at,
      adminNotes: reservation.admin_notes ?reservation.admin_notes : [],
    }
  })
})

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
