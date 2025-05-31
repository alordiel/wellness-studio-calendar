<template>
  <v-container class="pa-4">
    <h1 class="text-h4 font-weight-bold text-center mb-8">Admin Dashboard</h1>

    <v-row>
      <v-col
        v-for="block in dashboardBlocks"
        :key="block.name"
        cols="12"
        sm="6"
        md="4"
      >
        <v-card
          :to="block.route"
          class="h-100"
          elevation="2"
          hover
          link
        >
          <v-card-item>
            <v-card-title class="text-h5">
              {{ block.title }}
              <v-badge
                v-if="block.name === 'reservations' && reservationsCount"
                :content="reservationsCount.toString()"
                color="error"
                inline
                class="ml-2"
              ></v-badge>
            </v-card-title>
            <v-card-text>
              {{ block.description }}
            </v-card-text>
          </v-card-item>
          <v-card-actions>
            <v-btn
              variant="text"
              color="primary"
            >
              View {{ block.title }}
            </v-btn>
          </v-card-actions>
        </v-card>
      </v-col>
    </v-row>
  </v-container>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { useReservationStore } from '../store/reservation';

interface DashboardBlock {
  name: string;
  title: string;
  route: string;
  ariaLabel: string;
  description: string;
}

// Access the reservation store
const reservationStore = useReservationStore();

// Compute the number of reservations from the getter
const reservationsCount = computed(() => reservationStore.getNumberOfReservations);

const dashboardBlocks: DashboardBlock[] = [
  {
    name: 'reservations',
    title: 'Reservations',
    route: '/reservations',
    ariaLabel: 'Navigate to Reservations page',
    description: 'Manage all bookings and customer reservations in one place.'
  },
  {
    name: 'activities',
    title: 'Activities',
    route: '/activities',
    ariaLabel: 'Navigate to Activities page',
    description: 'Configure and manage all available activities for scheduling.'
  },
  {
    name: 'events',
    title: 'Events',
    route: '/events',
    ariaLabel: 'Navigate to Events page',
    description: 'Create and manage special events and seasonal promotions.'
  },
  {
    name: 'instructors',
    title: 'Instructors',
    route: '/instructors',
    ariaLabel: 'Navigate to Instructors page',
    description: 'Manage your team of instructors and their availability.'
  },
  {
    name: 'locations',
    title: 'Locations',
    route: '/locations',
    ariaLabel: 'Navigate to Locations page',
    description: 'Set up and manage multiple venues and locations.'
  },
  {
    name: 'settings',
    title: 'Settings',
    route: '/settings',
    ariaLabel: 'Navigate to Settings page',
    description: 'Configure system settings and preferences.'
  }
];
</script>