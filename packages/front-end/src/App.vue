<template>
  <div class="wp-vue-app bg-white p-6 rounded-lg shadow-md">
    <div class="max-w-6xl mx-auto p-6 bg-gray-50 min-h-screen">
      <div class="text-center mb-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-2">Календар на събитията</h1>
        <p class="text-gray-600">Следващите 7 дни</p>
      </div>

      <div v-if="store.loading" class="text-center py-12">
        <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-purple-600"></div>
        <p class="mt-2 text-gray-600">Зареждане...</p>
      </div>

      <div v-else-if="Object.keys(store.events).length === 0" class="text-center py-12">
        <div class="text-gray-500">
          <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
          </svg>
          <h3 class="mt-2 text-sm font-medium text-gray-900">Няма събития</h3>
          <p class="mt-1 text-sm text-gray-500">Няма планирани събития за следващите 7 дни.</p>
        </div>
      </div>

      <div v-else>
        <DaySection
            v-for="(events, date) in store.events"
            :key="date"
            :date="date"
            :events="events"
        />
      </div>

      <BookingModal/>
    </div>
  </div>
</template>
<script setup lang="ts">
import {onMounted} from 'vue';
import DaySection from "./components/DaySection.vue";
import BookingModal from "./components/BookingModal.vue";
import {useCalendarStore} from './store.js';

const store = useCalendarStore();

// Fetch events on component mount
onMounted(() => {
  store.fetchEvents();
});
</script>