<template>
  <tr
      @click="store.openBookingModal(event)"
      class="border-b border-gray-200 hover:bg-purple-50 cursor-pointer transition-colors duration-200"
      :class="{ 'opacity-50': event.free_places === 0 }"
  >
    <td class="px-6 py-4">
      <div class="text-sm font-medium text-gray-900">{{ event.name }}</div>
    </td>
    <td class="px-6 py-4">
      <div class="text-sm text-gray-600">{{ event.instructor }}</div>
    </td>
    <td class="px-6 py-4">
      <div class="text-sm text-gray-900">
        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
              :class="event.free_places > 0 ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'">
            {{ event.free_places }} свободни места
        </span>
      </div>
    </td>
    <td class="px-6 py-4">
      <div class="text-sm text-gray-900">{{ formatTime(event.start_time) }}</div>
    </td>
    <td class="px-6 py-4">
      <div class="text-sm text-gray-900">{{ formatTime(event.end_time) }}</div>
    </td>
    <td class="px-6 py-4">
      <div class="text-sm text-gray-600">{{ event.location }}</div>
    </td>
  </tr>
</template>

<script setup>
import {useCalendarStore} from '../store.js';

const props = defineProps(['event']);

const store = useCalendarStore();

const formatTime = (dateTime) => {
  const date = new Date(dateTime);
  return date.toLocaleTimeString('bg-BG', {
    hour: '2-digit',
    minute: '2-digit'
  });
};


</script>
