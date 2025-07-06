<template>
  <v-container fluid>
    <!-- Header with Add Button -->
    <h1 class="text-center">Events Management</h1>
    <div class="d-flex justify-space-between mb-6">
      <router-link to="/" >← Back to Dashboard</router-link>
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

    <!-- Search Bar -->
    <v-text-field
      v-model="search"
      prepend-inner-icon="mdi-magnify"
      label="Search events..."
      variant="outlined"
      hide-details
      clearable
      class="mb-4"
    ></v-text-field>

    <!-- Events Data Table -->
    <v-data-table
      v-if="formattedEvents.length > 0"
      :headers="headers"
      :items="formattedEvents"
      :search="search"
      :sort-by="[{ key: 'activity_name', order: 'asc' }]"
      class="elevation-2"
    >
      <!-- Event Name Column with Color Border -->
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

      <!-- Location Column with HTML Content -->
      <template v-slot:item.location="{ item }">
        <div v-html="item.location"></div>
      </template>

      <!-- Week Day Column with Chip -->
      <template v-slot:item.week_day="{ item }">
        <v-chip
          size="small"
          variant="outlined"
          :color="getWeekDayColor(item.week_day)"
        >
          {{ item.week_day }}
        </v-chip>
      </template>

      <!-- Time Columns with Icons -->
      <template v-slot:item.start_time="{ item }">
        <div class="d-flex align-center">
          <v-icon size="small" class="mr-1">mdi-play</v-icon>
          {{ item.start_time }}
        </div>
      </template>

      <template v-slot:item.end_time="{ item }">
        <div class="d-flex align-center">
          <v-icon size="small" class="mr-1">mdi-stop</v-icon>
          {{ item.end_time }}
        </div>
      </template>

      <!-- Places Column with Capacity Indicator -->
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
      <template v-slot:item.actions="{ item, index }">
        <div class="d-flex ga-1">
          <v-tooltip text="Edit Event">
            <template v-slot:activator="{ props }">
              <v-btn
                v-bind="props"
                icon="mdi-pencil"
                size="small"
                variant="text"
                color="primary"
                @click="openEditModal(index)"
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

          <v-tooltip text="Add Exception">
            <template v-slot:activator="{ props }">
              <v-btn
                v-bind="props"
                icon="mdi-calendar-alert"
                size="small"
                variant="text"
                color="warning"
                @click="openManageException(index)"
              ></v-btn>
            </template>
          </v-tooltip>
        </div>
      </template>

      <!-- No Data Slot -->
      <template v-slot:no-data>
        <div class="text-center py-8">
          <v-icon size="64" color="grey-lighten-1" class="mb-4">
            mdi-calendar-blank
          </v-icon>
          <h3 class="text-h6 text-grey-darken-1 mb-2">No Events Found</h3>
          <p class="text-body-2 text-grey-darken-1">
            {{ search ? 'No events match your search criteria.' : 'No event entries found.' }}
          </p>
          <v-btn
            v-if="!search"
            color="primary"
            variant="flat"
            prepend-icon="mdi-plus"
            @click="showEditModal = true"
            class="mt-4"
          >
            Add Your First Event
          </v-btn>
        </div>
      </template>

      <!-- Loading Slot -->
      <template v-slot:loading>
        <v-skeleton-loader type="table-row@6"></v-skeleton-loader>
      </template>
    </v-data-table>

    <!-- No Events Message (Fallback) -->
    <v-card v-else class="text-center py-12" variant="outlined">
      <v-card-text>
        <v-icon size="64" color="grey-lighten-1" class="mb-4">
          mdi-calendar-blank
        </v-icon>
        <h3 class="text-h6 text-grey-darken-1 mb-2">No Events Available</h3>
        <p class="text-body-2 text-grey-darken-1 mb-4">
          Get started by adding your first event entry.
        </p>
        <v-btn
          color="primary"
          variant="flat"
          prepend-icon="mdi-plus"
          @click="showEditModal = true"
        >
          Add New Event
        </v-btn>
      </v-card-text>
    </v-card>

    <!-- Modals -->
    <EditModal
      :show-modal="showEditModal"
      :event-index="editEventIndex"
      @close="closeModal()"
    />

    <DeleteConfirmation
      :show-modal="showDeleteConfirm"
      :activity-id="activityIdForDeleting"
      @close="closeModal()"
    />

    <ExceptionsModal
      :show-modal="showExceptionsModal"
      :event-index="exceptionEventIndex"
      @close="closeModal()"
    />
  </v-container>
</template>

<script setup>
import {computed, onMounted, ref, watchEffect} from 'vue'
import { storeToRefs } from 'pinia'
import DeleteConfirmation from "../components/events/DeleteConfirmation.vue"
import ExceptionsModal from "../components/events/ExceptionsModal.vue"
import EditModal from "../components/events/EditModal.vue"
import { useEventsStore } from '../store/event.js'
import { useInstructorStore } from "../store/instructor.js"
import { useLocationStore } from "../store/location.js"
import { useActivityStore } from "../store/activity.js"

// Stores
const eventsStore = useEventsStore()
const { events } = storeToRefs(eventsStore)
const instructorStore = useInstructorStore()
const locationStore = useLocationStore()
const activityStore = useActivityStore()

// Reactive data
const search = ref('')

// Modal states
const showEditModal = ref(false)
const showDeleteConfirm = ref(false)
const showExceptionsModal = ref(false)

// Modal indices
const activityIdForDeleting = ref(null)
const editEventIndex = ref(null)
const exceptionEventIndex = ref(null)

// Data table headers
const headers = ref([
  {
    title: 'Event Name',
    align: 'start',
    sortable: true,
    key: 'activity_name',
    width: '200px'
  },
  {
    title: 'Instructor',
    align: 'start',
    sortable: true,
    key: 'instructor',
    width: '150px'
  },
  {
    title: 'Location',
    align: 'start',
    sortable: false,
    key: 'location',
    width: '200px'
  },
  {
    title: 'Week Day',
    align: 'center',
    sortable: true,
    key: 'week_day',
    width: '120px'
  },
  {
    title: 'Start Time',
    align: 'center',
    sortable: false,
    key: 'start_time',
    width: '120px'
  },
  {
    title: 'End Time',
    align: 'center',
    sortable: false,
    key: 'end_time',
    width: '120px'
  },
  {
    title: 'Places',
    align: 'center',
    sortable: false,
    key: 'places',
    width: '100px'
  },
  {
    title: 'Actions',
    align: 'center',
    sortable: false,
    key: 'actions',
    width: '150px'
  }
])

// Utility functions
const getWeekDayColor = (weekDay) => {
  const colors = {
    'Monday': 'blue',
    'Tuesday': 'green',
    'Wednesday': 'orange',
    'Thursday': 'purple',
    'Friday': 'red',
    'Saturday': 'cyan',
    'Sunday': 'pink'
  }
  return colors[weekDay] || 'grey'
}

// Modal functions
const openEditModal = (index) => {
  showEditModal.value = true
  editEventIndex.value = index
}

const openConfirmDelete = (activityId) => {
  showDeleteConfirm.value = true
  activityIdForDeleting.value = activityId
}

const openManageException = (index) => {
  showExceptionsModal.value = true
  exceptionEventIndex.value = index
}

const closeModal = () => {
  showEditModal.value = false
  showDeleteConfirm.value = false
  showExceptionsModal.value = false
  editEventIndex.value = null
  activityIdForDeleting.value = null
  exceptionEventIndex.value = null
}


// Data initialization
const formattedEvents = computed(() => {
  return events.value.map((event) => {
    const activity = activityStore.getActivityById(event.activity_id)
    const location = locationStore.getLocationById(event.location_id)
    const user = instructorStore.getInstructorById(event.instructor_id)

    return {
      id: event.id, // Add unique ID for proper tracking
      activity_name: activity.name,
      activity_id: event.activity_id,
      color: activity.color,
      instructor: user.name,
      instructor_id: event.instructor_id,
      location: location.address + ' <br> <strong>' + location.hall + '</strong>',
      location_id: event.location_id,
      week_day: event.week_day,
      start_time: event.start_time,
      end_time: event.end_time,
      places: event.places
    }
  })
})
</script>

<style scoped>
/* Custom styles for enhanced visual appeal */
.v-data-table {
  border-radius: 8px;
}

.v-data-table ::v-deep(.v-data-table__td) {
  padding: 12px 16px;
}

.v-data-table ::v-deep(.v-data-table__th) {
  background-color: #f5f5f5;
  font-weight: 600;
}

/* Custom hover effect for action buttons */
.v-btn:hover {
  transform: scale(1.05);
  transition: transform 0.2s ease;
}
</style>