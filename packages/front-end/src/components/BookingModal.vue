<template>
  <div v-if="store.showBookingModal"
       class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-lg shadow-xl max-w-md w-full max-h-96 overflow-y-auto">
      <div class="p-6">
        <div class="flex justify-between items-center mb-6">
          <h3 class="text-xl font-semibold text-gray-900">Записване за събитие</h3>
          <button @click="store.closeBookingModal" class="text-gray-400 hover:text-gray-600">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
          </button>
        </div>

        <div v-if="store.selectedEvent" class="mb-6 p-4 bg-purple-50 rounded-lg">
          <h4 class="font-semibold text-purple-900">{{ store.selectedEvent.name }}</h4>
          <p class="text-sm text-purple-700">{{ store.selectedEvent.instructor }}</p>
          <p class="text-sm text-purple-700">{{ formatDateTime(store.selectedEvent.start_time) }}</p>
          <p class="text-sm text-purple-700">{{ store.selectedEvent.location }}</p>
        </div>

        <form @submit.prevent="store.submitBooking" class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Име *</label>
            <input
                v-model="store.bookingForm.name"
                type="text"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
            >
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Имейл *</label>
            <input
                v-model="store.bookingForm.email"
                type="email"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
            >
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Телефон *</label>
            <input
                v-model="store.bookingForm.phone"
                type="tel"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
            >
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Начин на плащане *</label>
            <select
                v-model="store.bookingForm.payment_type"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
            >
              <option value="">Изберете начин на плащане</option>
              <option value="multisport_card">Мултиспорт карта</option>
              <option value="club_card">Клубна карта</option>
              <option value="cash">В брой</option>
            </select>
          </div>

          <div class="flex gap-3 pt-4">
            <button
                type="button"
                @click="store.closeBookingModal"
                class="flex-1 px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-500"
            >
              Отказ
            </button>
            <button
                type="submit"
                :disabled="store.bookingLoading"
                class="flex-1 px-4 py-2 bg-purple-600 text-white rounded-md hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-purple-500 disabled:opacity-50"
            >
              <span v-if="store.bookingLoading">Записване...</span>
              <span v-else>Запиши се</span>
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import {useCalendarStore} from "../store.js";

const store = useCalendarStore();

const formatDateTime = (dateTime) => {
  const date = new Date(dateTime);
  return date.toLocaleString('bg-BG', {
    weekday: 'long',
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  });
};
</script>
