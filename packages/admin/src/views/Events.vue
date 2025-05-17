<template>
  <div class="p-6">
    <!-- Header with Add Button -->
    <div class="flex justify-between items-center mb-6">
      <h1 class="text-2xl font-bold text-gray-800">Events Management</h1>
      <router-link to="/" class="text-blue-600 hover:text-blue-800">← Back to Dashboard</router-link>
      <button
          @click="showEditModal = true"
          class="bg-blue-500 hover:bg-blue-600 text-white font-medium py-2 px-4 rounded-lg transition duration-200"
      >
        Add New Entry
      </button>
    </div>

    <!-- Events Table -->
    <div v-if="events.length > 0" class="overflow-x-auto shadow-md rounded-lg">
      <table class="w-full bg-white">
        <thead class="bg-gray-50">
        <tr>
          <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Event Name</th>
          <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Instructor</th>
          <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Location</th>
          <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Color</th>
          <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Week Day</th>
          <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Start Time</th>
          <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">End Time</th>
          <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Places</th>
          <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
        </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
        <tr v-for="(event, index) in events" :key="index" class="hover:bg-gray-50">
          <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ event.event_name }}</td>
          <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ getInstructor(event.instructor) }}</td>
          <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><p v-html="getLocation(event.location)"></p></td>
          <td class="px-6 py-4 whitespace-nowrap">
            <div class="flex items-center">
              <div
                  class="w-6 h-6 rounded border border-gray-300"
                  :style="{ backgroundColor: event.color }"
              ></div>
              <span class="ml-2 text-sm text-gray-900">{{ event.color }}</span>
            </div>
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
import {ref} from 'vue'
import 'vue3-colorpicker/style.css'
import DeleteConfirmation from "../components/events/DeleteConfirmation.vue";
import ExceptionsModal from "../components/events/ExceptionsModal.vue";
import EditModal from "../components/events/EditModal.vue";
import { storeToRefs } from 'pinia'
import { useEventsStore } from '../store/event.js'
import {useInstructorStore} from "../store/instructor.js";
import {useLocationStore} from "../store/location.js";


const eventsStore = useEventsStore()
const { events } = storeToRefs(eventsStore)
const instructorStore = useInstructorStore()
const locationStore = useLocationStore()

const showEditModal = ref(false)
const showDeleteConfirm = ref(false)
const showExceptionsModal = ref(false)

const deleteEventIndex = ref(null);
const editEventIndex = ref(null);
const exceptionEventIndex = ref(null);
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

const getInstructor = (id) => {
  const user = instructorStore.getInstructorById(id)
  return user.name
}

const getLocation = (id) => {
  const location = locationStore.getLocationById(id)
  return location.address + ' <br> <strong>' + location.hall + '</strong>'
}

</script>