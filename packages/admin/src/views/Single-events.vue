<template>
  <v-container>
    <ViewTitle title="Single Time Events" />

    <div class="d-flex justify-end mb-6">
      <v-btn
          rounded="0"
          min-width="200px"
          icon="mdi-plus"
          color="#155dcf"
          variant="flat"
          @click="showEditModal = true"
      >
        Add New Entry
      </v-btn>
    </div>

    <v-tabs v-model="activeTab" class="mb-4">
      <v-tab value="upcoming">Upcoming</v-tab>
      <v-tab value="past">Past</v-tab>
    </v-tabs>

    <v-data-table
        :headers="headers"
        :items="activeTab === 'upcoming' ? upcomingEvents : pastEvents"
        class="elevation-2"
    >
      <!-- Date Column -->
      <template v-slot:item.date_display="{ item }">
        <span class="font-weight-medium">{{ item.date_display }}</span>
      </template>

      <!-- Activity Column with Color Border -->
      <template v-slot:item.activity_name="{ item }">
        <div
            class="py-2 pl-3 rounded-l"
            :style="{
              borderLeft: `4px solid ${item.color}`,
              backgroundColor: `${item.color}10`
            }"
        >
          <span class="font-weight-medium">{{ item.activity_name }}</span>
        </div>
      </template>

      <!-- Places Column -->
      <template v-slot:item.places="{ item }">
        <v-chip
            size="small"
            :color="item.places >= 20 ? 'success' : item.places >= 10 ? 'warning' : 'error'"
            variant="flat"
        >
          <v-icon start size="small">mdi-account-group</v-icon>
          {{ item.places }}
        </v-chip>
      </template>

      <!-- Actions Column -->
      <template v-slot:item.actions="{ item }">
        <div class="d-flex ga-1">
          <v-tooltip text="Edit Event">
            <template v-slot:activator="{ props }">
              <v-btn
                  v-bind="props"
                  icon="mdi-pencil"
                  size="small"
                  variant="text"
                  color="primary"
                  @click="openEditModal(item.id)"
              ></v-btn>
            </template>
          </v-tooltip>

          <v-tooltip text="Delete Event">
            <template v-slot:activator="{ props }">
              <v-btn
                  v-bind="props"
                  icon="mdi-delete"
                  size="small"
                  variant="text"
                  color="error"
                  @click="openConfirmDelete(item.activity_id)"
              ></v-btn>
            </template>
          </v-tooltip>
        </div>
      </template>

      <!-- No Data Slot -->
      <template v-slot:no-data>
        <div class="text-center py-8">
          <v-icon size="64" color="grey-lighten-1" class="mb-4">mdi-calendar-blank</v-icon>
          <h3 class="text-h6 text-grey-darken-1 mb-2">No Events Found</h3>
          <p class="text-body-2 text-grey-darken-1">No single-time events in this period.</p>
        </div>
      </template>

      <template v-slot:loading>
        <v-skeleton-loader type="table-row@6"></v-skeleton-loader>
      </template>
    </v-data-table>

    <SingleEventEditModal
        :show-modal="showEditModal"
        :event-id="editEventId"
        @close="closeModal"
    />

    <DeleteConfirmation
        :show-modal="showDeleteConfirm"
        :activity-id="activityIdForDeleting"
        @close="closeModal"
    />
  </v-container>
</template>

<script setup>
import { computed, ref } from 'vue'
import { storeToRefs } from 'pinia'
import ViewTitle from '../components/View-title.vue'
import DeleteConfirmation from '../components/events/DeleteConfirmation.vue'
import SingleEventEditModal from '../components/events/SingleEventEditModal.vue'
import { useEventsStore } from '../store/event.js'
import { useInstructorStore } from '../store/instructor.js'
import { useActivityStore } from '../store/activity.js'

// Stores
const eventsStore = useEventsStore()
const { getSingleEvents } = storeToRefs(eventsStore)
const instructorStore = useInstructorStore()
const activityStore = useActivityStore()

// UI state
const activeTab = ref('upcoming')
const showEditModal = ref(false)
const showDeleteConfirm = ref(false)
const editEventId = ref(null)
const activityIdForDeleting = ref(null)

const headers = [
  { title: 'Date',       key: 'date_display',  sortable: true,  align: 'start',  width: '130px' },
  { title: 'Activity',   key: 'activity_name', sortable: true,  align: 'start',  width: '180px' },
  { title: 'Instructor', key: 'instructor',    sortable: true,  align: 'start',  width: '150px' },
  { title: 'Location',   key: 'location',      sortable: false, align: 'start',  width: '140px' },
  { title: 'Start Time', key: 'start_time',    sortable: true,  align: 'center', width: '110px' },
  { title: 'End Time',   key: 'end_time',      sortable: true,  align: 'center', width: '110px' },
  { title: 'Places',     key: 'places',        sortable: false, align: 'center', width: '90px'  },
  { title: 'Actions',    key: 'actions',       sortable: false, align: 'center', width: '120px' },
]

const formatDate = (dateStr) => {
  if (!dateStr) return '-'
  const [y, m, d] = dateStr.split('-')
  return `${d}/${m}/${y}`
}

const today = new Date().toISOString().slice(0, 10)

const formattedSingleEvents = computed(() =>
  getSingleEvents.value.map((event) => {
    const activity = activityStore.getActivityById(event.activity_id)
    const instructor = instructorStore.getInstructorById(event.instructor_id)
    return {
      id: event.id,
      date: event.date,
      date_display: formatDate(event.date),
      activity_name: activity?.name ?? '-',
      activity_id: event.activity_id,
      color: activity?.color ?? '#ccc',
      instructor: instructor?.name ?? '-',
      instructor_id: event.instructor_id,
      location: '-',
      start_time: event.start_time,
      end_time: event.end_time,
      places: event.places,
    }
  })
)

const upcomingEvents = computed(() =>
  formattedSingleEvents.value
    .filter(e => e.date >= today)
    .sort((a, b) => a.date.localeCompare(b.date))
)

const pastEvents = computed(() =>
  formattedSingleEvents.value
    .filter(e => e.date < today)
    .sort((a, b) => b.date.localeCompare(a.date))
)

const openEditModal = (eventId) => {
  editEventId.value = eventId
  showEditModal.value = true
}

const openConfirmDelete = (activityId) => {
  activityIdForDeleting.value = activityId
  showDeleteConfirm.value = true
}

const closeModal = () => {
  showEditModal.value = false
  showDeleteConfirm.value = false
  editEventId.value = null
  activityIdForDeleting.value = null
}
</script>

<style scoped>
.v-data-table {
  border-radius: 8px;
}
</style>
