<template>
  <div class="mb-8">
    <div class="bg-gradient-to-r from-purple-600 to-pink-600 text-white px-6 py-4 rounded-t-lg">
      <h3 class="text-lg font-semibold">{{ formatDate(date) }}</h3>
    </div>
    <div class="bg-white shadow-lg rounded-b-lg overflow-hidden">
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
        <tr>
          <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Събитие</th>
          <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Инструктор</th>
          <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Места</th>
          <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Начало</th>
          <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Край</th>
          <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Зала</th>
        </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
        <EventRow
            v-for="event in events"
            :key="event.id"
            :event="event"
        />
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup>
import EventRow from "./EventRow.vue";
const formatDate = (dateString) => {
  const date = new Date(dateString);
  return date.toLocaleDateString('bg-BG', {
    weekday: 'long',
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  });
};

</script>