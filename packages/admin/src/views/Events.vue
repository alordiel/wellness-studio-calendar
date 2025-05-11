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
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ event.instructor }}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ event.location }}</td>
            <td class="px-6 py-4 whitespace-nowrap">
              <div class="flex items-center">
                <div
                  class="w-6 h-6 rounded border border-gray-300"
                  :style="{ backgroundColor: event.color }"
                ></div>
                <span class="ml-2 text-sm text-gray-900">{{ event.color }}</span>
              </div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ formatWeekDays(event.week_day) }}</td>
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

    <EditModal v-if="showEditModal"></EditModal>
    <DeleteConfirmation v-if="showDeleteConfirm"></DeleteConfirmation>
    <ExceptionsModal v-if="showExceptionsModal"></ExceptionsModal>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import 'vue3-colorpicker/style.css'
import DeleteConfirmation from "../components/events/DeleteConfirmation.vue";
import ExceptionsModal from "../components/events/ExceptionsModal.vue";
import EditModal from "../components/events/EditModal.vue";

// Reactive data
const events = ref([
  // Sample data - remove this when connecting to real data source
  {
    event_name: 'Morning Yoga',
    instructor: 'John Smith',
    location: 'Grand Ballroom',
    color: '#3B82F6',
    week_day: ['monday', 'wednesday', 'friday'],
    start_time: '08:00',
    end_time: '09:30',
    places: 30
  }
])

const showEditModal = ref(false)
const showDeleteConfirm = ref(false)
const showExceptionsModal = ref(false)

const deleteEventIndex = ref(null);
const editEvent = ref(null);
const exceptionEvent = ref(null);
const openEditModal= (index) => {
  showEditModal.value = true;
  editEvent.value = events[index];
}
const openConfirmDelete= (index) => {
  showDeleteConfirm.value = true;
  deleteEventIndex.value = index;
}
const openManageException = (index) => {
  showExceptionsModal.value = true;
  exceptionEvent.value = events[index];
}


</script>