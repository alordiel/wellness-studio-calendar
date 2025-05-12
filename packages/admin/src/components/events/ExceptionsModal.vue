<template>
  <v-modal v-model="modalState" width="auto">
    <div class="bg-white rounded-lg p-6 w-full max-w-sm">
      <h2 class="text-xl font-bold mb-4">Add exception</h2>
      <p class="text-gray-600 mb-6">Reschedule or cancel this event for particular date.</p>

      <!-- Radio buttons for exception type -->
      <div class="mb-4">
        <v-radio-group v-model="exceptionType" inline>
          <v-radio label="Reschedule" value="reschedule"></v-radio>
          <v-radio label="Cancel" value="cancel"></v-radio>
        </v-radio-group>
      </div>

      <!-- Date picker -->
      <div class="mb-6">
        <v-date-picker
            v-model="exceptionDate"
            :min="getCurrentDate()"
            class="mb-4"
            elevation="0"
            width="100%"
        ></v-date-picker>
      </div>

      <div class="flex justify-end space-x-3">
        <v-btn
            @click="cancelException"
            class="px-4 py-2 text-gray-700 bg-gray-200 hover:bg-gray-300 rounded-md transition duration-200"
        >
          Cancel
        </v-btn>
        <v-btn
            @click="confirmException"
            class="px-4 py-2 bg-red-500 hover:bg-red-600 text-white rounded-md transition duration-200"
        >
          Add Exception
        </v-btn>
      </div>
    </div>
  </v-modal>
</template>

<script setup>
import {ref, onMounted} from "vue";

const emit = defineEmits(['close'])
const props = defineProps(['eventIndex', 'showModal'])

const exceptionIndex = ref(null)
const exceptionType = ref('reschedule')
const exceptionDate = ref(null)
const modalState = ref(false);
const cancelException = () => {
  exceptionIndex.value = null
  exceptionType.value = 'reschedule'
  exceptionDate.value = null

  modalState.value = false;
  emit('close');
}

onMounted(() => {
  modalState.value = props.showModal
});
const confirmException = () => {
  if (!exceptionDate.value) {
    alert('Please select a date for the exception')
    return
  }

  // Here you would handle the exception based on exceptionType.value
  // and exceptionDate.value for the event at exceptionIndex.value
  console.log('Exception created:', {
    eventIndex: exceptionIndex.value,
    type: exceptionType.value,
    date: exceptionDate.value
  })

  cancelException()
}

const getCurrentDate = () => {
  const today = new Date()
  const year = today.getFullYear()
  const month = String(today.getMonth() + 1).padStart(2, '0')
  const day = String(today.getDate()).padStart(2, '0')
  return `${year}-${month}-${day}`
}
</script>