<template>
  <v-dialog v-model="localDialog" max-width="400px">
    <v-card>
      <v-card-title class="text-h5">Confirm Cancellation</v-card-title>
      <v-card-text>
        Are you sure you want to cancel this reservation? An email notification will be sent to the user.
      </v-card-text>
      <v-card-actions>
        <v-spacer></v-spacer>
        <v-btn color="grey" @click="handleCancel">Cancel</v-btn>
        <v-btn color="error" @click="handleConfirm">Confirm</v-btn>
      </v-card-actions>
    </v-card>
  </v-dialog>
</template>

<script setup>
import { ref, watch } from 'vue'

const props = defineProps({
  modelValue: {
    type: Boolean,
    default: false
  },
  reservation: {
    type: Object,
    default: null
  }
})

const emit = defineEmits(['update:modelValue', 'confirm', 'cancel'])

const localDialog = ref(false)

// Watch for changes to the prop and update local state
watch(() => props.modelValue, (newVal) => {
  localDialog.value = newVal
})

// Watch local dialog changes and emit to parent
watch(localDialog, (newVal) => {
  if (newVal !== props.modelValue) {
    emit('update:modelValue', newVal)
  }
})

const handleCancel = () => {
  localDialog.value = false
  emit('cancel')
}

const handleConfirm = () => {
  emit('confirm', props.reservation)
  localDialog.value = false
}
</script>