<template>
  <v-container>
    <ViewTitle  title="Settings" />
    <!-- Vuetify Tabs -->
    <v-tabs v-model="activeTab" class="mb-6">
      <v-tab value="settings">Settings</v-tab>
      <v-tab value="email">Email</v-tab>
      <v-tab value="coming-next">Coming Next</v-tab>
      <v-tab value="about">About</v-tab>
    </v-tabs>

    <v-tabs-window v-model="activeTab">
      <!-- Settings Tab -->
      <v-tabs-window-item value="settings">
        <v-card class="pa-6">
          <div>Payment methods: add and remove payment methods</div>
          <h2 class="text-h5 mb-4">Captcha</h2>
          <p class="text-body-1 mb-4">
            Allowing captcha will reduce the chance of bots registering for your events
          </p>

          <v-checkbox
            v-model="settings.captcha.enabled"
            label="Add hCaptcha"
            color="primary"
            class="mb-4"
          ></v-checkbox>


          <v-expand-transition>
            <div v-show="settings.captcha.enabled">
              <v-text-field
                v-model="settings.captcha.siteKey"
                label="hCaptcha Site Key"
                variant="outlined"
                placeholder="Enter your hCaptcha site key"
                class="mb-4"
              ></v-text-field>
            </div>
          </v-expand-transition>

          <v-alert
            type="info"
            variant="tonal"
            class="mb-4"
          >
            <template v-slot:text>
              You can get your free hCaptcha key by following these instructions
              <a
                href="https://captcha4wp.com/docs/how-to-get-hcaptcha-keys/"
                target="_blank"
                class="text-blue-600 hover:text-blue-800"
              >
                here
              </a>
            </template>
          </v-alert>
        </v-card>
      </v-tabs-window-item>

      <!-- Email Tab -->
      <v-tabs-window-item value="email">
        <v-card class="pa-6">
          <h2 class="text-h5 mb-6">Email Templates</h2>

          <div class="mb-6">
            <v-label class="text-subtitle-1 mb-2 d-block">
              Email template for confirming the reservation
            </v-label>
            <QuillEditor
              v-model:content="settings.email.confirmationTemplate"
              content-type="html"
              :options="quillOptions"
              class="mb-4"
            />
          </div>

          <div class="mb-6">
            <v-label class="text-subtitle-1 mb-2 d-block">
              Email template for confirming cancellation
            </v-label>
            <QuillEditor
              v-model:content="settings.email.cancellationTemplate"
              content-type="html"
              :options="quillOptions"
              class="mb-4"
            />
          </div>
        </v-card>
      </v-tabs-window-item>

      <!-- Coming Next Tab -->
      <v-tabs-window-item value="coming-next">
        <v-card class="pa-6">
          <h2 class="text-h5 mb-4">Coming Next</h2>
          <p class="text-body-1 mb-4">
            Planned for some day in the future Pro version:
          </p>

          <v-list class="bg-transparent">
            <v-list-item class="px-0">
              <template v-slot:prepend>
                <v-icon color="primary">mdi-clock-outline</v-icon>
              </template>
              <v-list-item-title>Option for waiting list</v-list-item-title>
            </v-list-item>

            <v-list-item class="px-0">
              <template v-slot:prepend>
                <v-icon color="primary">mdi-calendar-clock</v-icon>
              </template>
              <v-list-item-title>
                Option to choose the max cancelling hour before the start of the event
              </v-list-item-title>
            </v-list-item>

            <v-list-item class="px-0">
              <template v-slot:prepend>
                <v-icon color="primary">mdi-calendar-multiple</v-icon>
              </template>
              <v-list-item-title>
                Option to show more than a week of activities
              </v-list-item-title>
            </v-list-item>

            <v-list-item class="px-0">
              <template v-slot:prepend>
                <v-icon color="primary">mdi-calendar-multiple</v-icon>
              </template>
              <v-list-item-title>
                Functional calendar with showing the activities per date/time and location<br>
                <small>check also if adding a new event doesn't overlap with old one in space and time</small>
              </v-list-item-title>
            </v-list-item>
          </v-list>
        </v-card>
      </v-tabs-window-item>

      <!-- About Tab -->
      <v-tabs-window-item value="about">
        <v-card class="pa-6">
          <h2 class="text-h5 mb-4">About</h2>

          <v-card variant="outlined" class="pa-4">
            <v-card-text>
              <p class="text-body-1 mb-4">
                Some information about the plugin plus contact details.
              </p>
              <p class="text-body-1 mb-4">
                What happens on uninstall
              </p>

              <v-divider class="my-4"></v-divider>

              <div class="d-flex flex-column gap-2">
                <div class="d-flex align-center gap-2">
                  <v-icon color="primary">mdi-email</v-icon>
                  <span class="text-body-2">
                    Feedback:
                    <a href="mailto:support@timelinedev.com" class="text-blue-600 hover:text-blue-800">
                      support@timelinedev.com
                    </a>
                  </span>
                </div>

                <div class="d-flex align-center gap-2">
                  <v-icon color="primary">mdi-github</v-icon>
                  <span class="text-body-2">
                    Github:
                    <a
                      href="https://github.com/alordiel/wellness-studio-calendar"
                      target="_blank"
                      class="text-blue-600 hover:text-blue-800"
                    >
                      https://github.com/alordiel/wellness-studio-calendar
                    </a>
                  </span>
                </div>

                <div class="d-flex align-center gap-2">
                  <v-icon color="primary">mdi-web</v-icon>
                  <span class="text-body-2">
                    Company site:
                    <a
                      href="https://timeline-dev.com"
                      target="_blank"
                      class="text-blue-600 hover:text-blue-800"
                    >
                      https://timeline-dev.com
                    </a>
                  </span>
                </div>

                <div class="d-flex align-center gap-2">
                  <v-icon color="primary">mdi-wordpress</v-icon>
                  <span class="text-body-2">
                    WordPress: Please review in WordPress
                  </span>
                </div>
              </div>
            </v-card-text>
          </v-card>
        </v-card>
      </v-tabs-window-item>
    </v-tabs-window>

    <!-- Save Settings Button -->
    <div class="d-flex justify-center mt-6">
      <v-btn
        color="primary"
        size="large"
        :loading="saving"
        @click="saveSettings"
        prepend-icon="mdi-content-save"
      >
        Save Settings
      </v-btn>
    </div>

    <!-- Success/Error Snackbars -->
    <v-snackbar
      v-model="snackbar.show"
      :color="snackbar.color"
      :timeout="4000"
      location="top"
    >
      {{ snackbar.message }}
      <template v-slot:actions>
        <v-btn
          variant="text"
          @click="snackbar.show = false"
        >
          Close
        </v-btn>
      </template>
    </v-snackbar>
  </v-container>
</template>

<script setup lang="ts">
import { ref, reactive } from 'vue'
import { QuillEditor } from '@vueup/vue-quill'
import '@vueup/vue-quill/dist/vue-quill.snow.css'
import ViewTitle from "../components/View-title.vue";

// Types
interface CaptchaSettings {
  enabled: boolean
  siteKey: string
}

interface EmailSettings {
  confirmationTemplate: string
  cancellationTemplate: string
}

interface Settings {
  captcha: CaptchaSettings
  email: EmailSettings
}

interface Snackbar {
  show: boolean
  message: string
  color: 'success' | 'error' | 'warning' | 'info'
}

// Reactive state
const activeTab = ref<string>('settings')
const saving = ref<boolean>(false)

const settings = reactive<Settings>({
  captcha: {
    enabled: false,
    siteKey: '1b18ff62-eba2-4f8e-bda8-a6465ce28da8'
  },
  email: {
    confirmationTemplate: '<p>Thank you for your reservation! We look forward to seeing you.</p>',
    cancellationTemplate: '<p>Your reservation has been cancelled successfully.</p>'
  }
})

const snackbar = reactive<Snackbar>({
  show: false,
  message: '',
  color: 'success'
})

// Quill editor options
const quillOptions = {
  theme: 'snow',
  modules: {
    toolbar: [
      ['bold', 'italic', 'underline', 'strike'],
      ['blockquote', 'code-block'],
      [{ 'header': 1 }, { 'header': 2 }],
      [{ 'list': 'ordered'}, { 'list': 'bullet' }],
      [{ 'script': 'sub'}, { 'script': 'super' }],
      [{ 'indent': '-1'}, { 'indent': '+1' }],
      [{ 'direction': 'rtl' }],
      [{ 'size': ['small', false, 'large', 'huge'] }],
      [{ 'header': [1, 2, 3, 4, 5, 6, false] }],
      [{ 'color': [] }, { 'background': [] }],
      [{ 'font': [] }],
      [{ 'align': [] }],
      ['clean'],
      ['link', 'image']
    ]
  },
  placeholder: 'Enter your email template...'
}

// Methods
const saveSettings = async (): Promise<void> => {
  saving.value = true

  try {
    // Prepare data for WordPress ajax call
    const formData = new FormData()
    formData.append('action', 'store_settings')
    formData.append('settings', JSON.stringify(settings))
    // Note: nonce will be added later as mentioned

    const response = await fetch('/wp-admin/admin-ajax.php', {
      method: 'POST',
      body: formData
    })

    if (!response.ok) {
      throw new Error(`HTTP error! status: ${response.status}`)
    }

    const result = await response.json()

    if (result.success) {
      showSnackbar('Settings saved successfully!', 'success')
    } else {
      throw new Error(result.data?.message || 'Failed to save settings')
    }

  } catch (error) {
    console.error('Error saving settings:', error)
    showSnackbar(
      error instanceof Error ? error.message : 'Failed to save settings. Please try again.',
      'error'
    )
  } finally {
    saving.value = false
  }
}

const showSnackbar = (message: string, color: Snackbar['color']): void => {
  snackbar.message = message
  snackbar.color = color
  snackbar.show = true
}

// TODO: Add method to load existing settings from WordPress on component mount
// const loadSettings = async (): Promise<void> => {
//   // Implementation will be added when backend is ready
// }

// TODO: Add validation for required fields before saving
// const validateSettings = (): boolean => {
//   // Implementation for form validation
//   return true
// }
</script>

<style scoped>
/* Custom styles for Quill editor integration with Vuetify */
:deep(.ql-editor) {
  min-height: 150px;
  font-family: inherit;
}

:deep(.ql-toolbar) {
  border-top: 1px solid rgba(var(--v-border-color), var(--v-border-opacity));
  border-left: 1px solid rgba(var(--v-border-color), var(--v-border-opacity));
  border-right: 1px solid rgba(var(--v-border-color), var(--v-border-opacity));
}

:deep(.ql-container) {
  border-bottom: 1px solid rgba(var(--v-border-color), var(--v-border-opacity));
  border-left: 1px solid rgba(var(--v-border-color), var(--v-border-opacity));
  border-right: 1px solid rgba(var(--v-border-color), var(--v-border-opacity));
}

/* Ensure proper spacing in tab content */
.v-tabs-window-item {
  padding-top: 0;
}

/* Custom link styles */
a {
  text-decoration: none;
}

a:hover {
  text-decoration: underline;
}
</style>