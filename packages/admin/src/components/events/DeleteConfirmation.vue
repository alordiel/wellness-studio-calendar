<template>
  <v-modal v-model="modalState" width="auto">
    <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg p-6 w-full max-w-sm">
        <h2 class="text-xl font-bold mb-4">Confirm Delete</h2>
        <p class="text-gray-600 mb-6">Are you sure you want to delete this event?</p>
        <div class="flex justify-end space-x-3">
          <button
              @click="closeDeleteDialog"
              class="px-4 py-2 text-gray-700 bg-gray-200 hover:bg-gray-300 rounded-md transition duration-200"
          >
            Cancel
          </button>
          <button
              @click="deleteEvent"
              class="px-4 py-2 bg-red-500 hover:bg-red-600 text-white rounded-md transition duration-200"
          >
            Delete
          </button>
        </div>
      </div>
    </div>
  </v-modal>
</template>

<script setup>
import {ref, onMounted} from "vue";

const emit = defineEmits(['close'])
const props = defineProps(['eventIndex', 'showModal'])
const isDeleting = ref(false);
const modalState = ref(false);

onMounted(() => {
  modalState.value = props.showModal
});
const deleteEvent = () => {
  isDeleting.value = true;
  closeDeleteDialog()
}
const closeDeleteDialog = () => {
  modalState.value = false
  emit('close');
}

</script>