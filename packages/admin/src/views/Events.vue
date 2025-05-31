<template>
  <div>
    <!-- Header with Add Button -->
    <div class="flex justify-between items-center mb-6">
      <h1 >Events Management</h1>
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

    <!-- Events Table -->
    <div v-if="formattedEvents.length > 0" class="overflow-x-auto shadow-md rounded-lg">
      <table class="w-full bg-white">
        <thead class="bg-gray-50">
        <tr>
          <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Event Name</th>
          <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Instructor</th>
          <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Location</th>
          <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Week Day</th>
          <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Start Time</th>
          <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">End Time</th>
          <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Places</th>
          <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
        </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
        <tr v-for="(event, index) in formattedEvents" :key="index" class="hover:bg-gray-50">
          <td class="px-6 py-4 whitespace-nowrap text-sm activity-cell text-gray-900"
              :style="{borderColor: event.color}">
            {{ event.activity_name }}
          </td>
          <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ event.instructor }}</td>
          <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><p v-html="event.location"></p>
          </td>
          <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ event.week_day }}</td>
          <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ event.start_time }}</td>
          <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ event.end_time }}</td>
          <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ event.places }}</td>
          <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
            <button
                @click="openEditModal(index)"
                class="text-blue-600 hover:text-blue-900 mr-3"
            >
              Edit
            </button>
            <button
                @click="openConfirmDelete(index)"
                class="text-red-600 hover:text-red-900"
            >
              Delete
            </button>
            <button
                @click="openManageException(index)"
                class="text-red-600 hover:text-red-900"
            >
              Add Exception
            </button>
          </td>
        </tr>
        </tbody>
      </table>
    </div>

    <!-- No Entries Message -->
    <div v-else class="text-center py-12 bg-gray-50 rounded-lg">
      <p class="text-gray-500 text-lg">No event entries found. Add a new entry to get started.</p>
    </div>

    <EditModal
        :show-modal="showEditModal"
        :event-index="editEventIndex"
        @close="closeModal()"
    ></EditModal>

    <DeleteConfirmation
        :show-modal="showDeleteConfirm"
        :event-index="deleteEventIndex"
        @close="closeModal()"
    ></DeleteConfirmation>

    <ExceptionsModal
        :show-modal="showExceptionsModal"
        :event-index="exceptionEventIndex"
        @close="closeModal()"
    ></ExceptionsModal>
  </div>
</template>

<script setup>
import {onMounted, ref} from 'vue'
import DeleteConfirmation from "../components/events/DeleteConfirmation.vue";
import ExceptionsModal from "../components/events/ExceptionsModal.vue";
import EditModal from "../components/events/EditModal.vue";
import {storeToRefs} from 'pinia'

import {useEventsStore} from '../store/event.js'
import {useInstructorStore} from "../store/instructor.js";
import {useLocationStore} from "../store/location.js";
import {useActivityStore} from "../store/activity.js";

const eventsStore = useEventsStore()
const {events} = storeToRefs(eventsStore)
const instructorStore = useInstructorStore()
const locationStore = useLocationStore()
const activityStore = useActivityStore()

const formattedEvents = ref([]);

const showEditModal = ref(false)
const showDeleteConfirm = ref(false)
const showExceptionsModal = ref(false)

const deleteEventIndex = ref(null);
const editEventIndex = ref(null);
const exceptionEventIndex = ref(null);

onMounted(() => {
  events.value.forEach((event) => {
    const activity = activityStore.getActivityById(event.activity_id)
    const location = locationStore.getLocationById(event.location_id)
    const user = instructorStore.getInstructorById(event.instructor_id)
    formattedEvents.value.push({
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
    })
  })
})

const openEditModal = (index) => {
  showEditModal.value = true;
  editEventIndex.value = index;
}
const openConfirmDelete = (index) => {
  showDeleteConfirm.value = true;
  deleteEventIndex.value = index;
}
const openManageException = (index) => {
  showExceptionsModal.value = true;
  exceptionEventIndex.value = index;
}

const closeModal = () => {
  showEditModal.value = false;
  showDeleteConfirm.value = false;
  showExceptionsModal.value = false;
  editEventIndex.value = null;
  deleteEventIndex.value = null;
  exceptionEventIndex.value = null;
}

</script>

<style scoped>
.activity-cell {
  border-left-width: 10px;
}
</style>