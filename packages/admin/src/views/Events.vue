<template>
  <v-container>
    <ViewTitle title="Events" />

    <v-row class="mt-4">
      <v-col cols="12" md="6">
        <v-card
          class="hub-card pa-8 text-center"
          :elevation="recurringHovered ? 8 : 2"
          @mouseenter="recurringHovered = true"
          @mouseleave="recurringHovered = false"
          @click="router.push('/events/recurring')"
        >
          <v-icon size="64" color="primary" class="mb-4">mdi-repeat</v-icon>
          <h2 class="text-h5 mb-2">Recurring Events</h2>
          <p class="text-body-2 text-medium-emphasis">
            Manage weekly recurring classes and sessions
          </p>
        </v-card>
      </v-col>

      <v-col cols="12" md="6">
        <v-card
          class="hub-card pa-8 text-center"
          :elevation="singleHovered ? 8 : 2"
          @mouseenter="singleHovered = true"
          @mouseleave="singleHovered = false"
          @click="router.push('/events/single')"
        >
          <v-icon size="64" color="primary" class="mb-4">mdi-calendar-check</v-icon>
          <h2 class="text-h5 mb-2">Single Time Events</h2>
          <p class="text-body-2 text-medium-emphasis">
            Manage one-off events on a specific date
          </p>
        </v-card>
      </v-col>
    </v-row>
  </v-container>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import ViewTitle from '../components/View-title.vue'

const router = useRouter()
const recurringHovered = ref(false)
const singleHovered = ref(false)
</script>

<style scoped>
.hub-card {
  cursor: pointer;
  transition: elevation 0.2s ease;
}
</style>
