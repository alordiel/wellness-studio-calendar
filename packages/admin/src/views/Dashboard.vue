<template>
  <div class="min-h-screen bg-gray-100 p-6">
    <h1 class="text-3xl font-bold text-center mb-8">Admin Dashboard</h1>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 max-w-6xl mx-auto">
      <router-link
          v-for="block in dashboardBlocks"
          :key="block.name"
          :to="block.route"
          class="group"
          :aria-label="block.ariaLabel"
      >
        <div
            class="relative overflow-hidden rounded-lg transition-all duration-300 ease-in-out transform hover:scale-105"
            :class="[
            'p-6 h-32 border-2 shadow-md hover:shadow-lg',
            block.borderColor,
            block.bgColor,
            block.hoverColor
          ]"
        >
          <div class="absolute inset-0 flex items-center justify-center">
            <h2 class="text-2xl font-semibold text-center" :class="block.textColor">
              {{ block.title }}
            </h2>
          </div>
        </div>
      </router-link>
    </div>


  </div>
</template>

<script setup lang="ts">
interface DashboardBlock {
  name: string;
  title: string;
  route: string;
  ariaLabel: string;
  borderColor: string;
  bgColor: string;
  hoverColor: string;
  textColor: string;
}

const dashboardBlocks: DashboardBlock[] = [
  {
    name: 'reservations',
    title: 'Reservations',
    route: '/reservations',
    ariaLabel: 'Navigate to Reservations page',
    borderColor: 'border-blue-300',
    bgColor: 'bg-blue-50',
    hoverColor: 'hover:bg-blue-100',
    textColor: 'text-blue-800'
  },
  {
    name: 'activities',
    title: 'Activities',
    route: '/activities',
    ariaLabel: 'Navigate to Activities page',
    borderColor: 'border-green-300',
    bgColor: 'bg-green-50',
    hoverColor: 'hover:bg-green-100',
    textColor: 'text-green-800'
  },
  {
    name: 'events',
    title: 'Events',
    route: '/events',
    ariaLabel: 'Navigate to Events page',
    borderColor: 'border-purple-300',
    bgColor: 'bg-purple-50',
    hoverColor: 'hover:bg-purple-100',
    textColor: 'text-purple-800'
  },
  {
    name: 'instructors',
    title: 'Instructors',
    route: '/instructors',
    ariaLabel: 'Navigate to Instructors page',
    borderColor: 'border-red-300',
    bgColor: 'bg-red-50',
    hoverColor: 'hover:bg-red-100',
    textColor: 'text-red-800'
  },
  {
    name: 'locations',
    title: 'Locations',
    route: '/locations',
    ariaLabel: 'Navigate to Locations page',
    borderColor: 'border-indigo-300',
    bgColor: 'bg-indigo-50',
    hoverColor: 'hover:bg-indigo-100',
    textColor: 'text-indigo-800'
  },
  {
    name: 'settings',
    title: 'Settings',
    route: '/settings',
    ariaLabel: 'Navigate toSettings page',
    borderColor: 'border-yellow-300',
    bgColor: 'bg-yellow-50',
    hoverColor: 'hover:bg-yellow-100',
    textColor: 'text-yellow-800'
  }
];
</script>
