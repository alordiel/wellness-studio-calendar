<template>
  <v-dialog v-model="modalState" width="auto">
    <v-card
        max-width="400"
        prepend-icon="mdi-delete"
        text="Are you sure you want to delete this event?"
        title="Confirm Delete"
    >
      <template v-slot:actions>
        <div>
          <v-btn
              class="ms-auto"
              text="Cancel"
              @click="closeDeleteDialog"
          ></v-btn>
          <v-btn
            class="ms-auto"
            text="Delete"
            @click="deleteEvent"
          ></v-btn>
        </div>
      </template>
    </v-card>
  </v-dialog>
</template>

<script setup>
import {ref, onMounted, watch} from "vue";
import {useEventsStore} from "../../store/event.js";

const store = useEventsStore();
const emit = defineEmits(['close'])
const props = defineProps(['activityId', 'showModal'])
const isDeleting = ref(false);
const modalState = ref(false);

// Watch for changes to the prop and update local state
watch(() => props.showModal, (newVal) => {
  modalState.value = newVal
})

const deleteEvent = () => {
  isDeleting.value = true;
  store.deleteEvent(props.activityId)
      .then(() => {
        isDeleting.value = false
      });
  closeDeleteDialog()
}
const closeDeleteDialog = () => {
  modalState.value = false
  emit('close');
}

</script>