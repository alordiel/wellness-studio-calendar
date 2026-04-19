<template>
  <v-container>
    <ViewTitle title="Settings"/>
    <!-- Vuetify Tabs -->
    <v-tabs v-model="activeTab" class="mb-6">
      <v-tab value="settings">Settings</v-tab>
      <v-tab value="email">Email</v-tab>
      <v-tab value="coming-next">Coming Next</v-tab>
      <v-tab value="about">About</v-tab>
      <v-tab value="faq">FAQ</v-tab>
    </v-tabs>

    <v-tabs-window v-model="activeTab">
      <!-- Settings Tab -->
      <v-tabs-window-item value="settings">
        <v-card class="pa-6">
          <v-expansion-panels variant="accordion" :model-value="null">

            <!-- Payment Methods -->
            <v-expansion-panel>
              <v-expansion-panel-title>
                <v-icon color="primary" class="mr-2">mdi-account-credit-card-outline</v-icon>
                Payment methods
              </v-expansion-panel-title>
              <v-expansion-panel-text>
                <div class="mt-2">
                  <div
                      v-for="(_, index) in settings.paymentMethods"
                      :key="index"
                      class="d-flex align-center gap-2 mb-3"
                  >
                    <v-text-field
                        v-model="settings.paymentMethods[index]"
                        variant="outlined"
                        density="compact"
                        hide-details
                        class="flex-grow-1"
                    ></v-text-field>
                    <v-btn
                        icon="mdi-minus"
                        variant="text"
                        color="error"
                        size="small"
                        :disabled="settings.paymentMethods.length === 1"
                        @click="removePaymentMethod(index)"
                    ></v-btn>
                  </div>
                  <v-btn
                      prepend-icon="mdi-plus"
                      variant="tonal"
                      color="primary"
                      size="small"
                      class="mt-1"
                      @click="addPaymentMethod"
                  >Add method</v-btn>
                </div>
                <div class="d-flex mt-6">
                  <v-btn
                      color="primary"
                      size="large"
                      :loading="saving"
                      @click="saveSettings('payment-method')"
                      prepend-icon="mdi-content-save"
                  >Save</v-btn>
                </div>
              </v-expansion-panel-text>
            </v-expansion-panel>

            <!-- Location -->
            <v-expansion-panel>
              <v-expansion-panel-title>
                <v-icon color="primary" class="mr-2">mdi-map-marker</v-icon>
                Location
              </v-expansion-panel-title>
              <v-expansion-panel-text>
                <v-text-field
                    v-model="settings.locationName"
                    label="Location name"
                    variant="outlined"
                    placeholder="Enter your location name"
                    class="mb-4 mt-2"
                ></v-text-field>
                <div class="d-flex justify-center mt-2">
                  <v-btn
                      color="primary"
                      size="large"
                      :loading="saving"
                      @click="saveSettings('location')"
                      prepend-icon="mdi-content-save"
                  >Save</v-btn>
                </div>
              </v-expansion-panel-text>
            </v-expansion-panel>

            <!-- Captcha -->
            <v-expansion-panel>
              <v-expansion-panel-title>
                <v-icon color="primary" class="mr-2">mdi-robot-outline</v-icon>
                Captcha
              </v-expansion-panel-title>
              <v-expansion-panel-text>
                <p class="text-body-1 mb-4 mt-2">
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
                    v-show="settings.captcha.enabled"
                >
                  <template v-slot:text>
                    You can get your free hCaptcha key by following these instructions
                    <a
                        href="https://captcha4wp.com/docs/how-to-get-hcaptcha-keys/"
                        target="_blank"
                        class="text-blue-600 hover:text-blue-800"
                    >here</a>
                  </template>
                </v-alert>
                <div class="d-flex gap-3 mt-6" v-show="settings.captcha.enabled">
                  <v-btn
                      v-show="!settings.captcha.isSaved"
                      color="primary"
                      size="large"
                      :loading="saving"
                      @click="saveSettings('captcha')"
                      prepend-icon="mdi-content-save"
                  >Save captcha</v-btn>
                  <v-btn
                      v-if="settings.captcha.isSaved && settings.captcha.siteKey"
                      color="error"
                      size="large"
                      variant="tonal"
                      :loading="saving"
                      @click="removeCaptcha"
                      prepend-icon="mdi-delete-outline"
                  >Remove captcha</v-btn>
                </div>
              </v-expansion-panel-text>
            </v-expansion-panel>

          </v-expansion-panels>
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

          <div>
            <h2>Instructions and placeholders</h2>

          </div>

          <!-- Save Settings Button -->
          <div class="d-flex mt-6">
            <v-btn
                color="primary"
                size="large"
                :loading="saving"
                @click="saveSettings('email-templates')"
                prepend-icon="mdi-content-save"
            >
              Save email templates
            </v-btn>
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
                <v-icon color="primary">mdi-map-marker</v-icon>
              </template>
              <v-list-item-title>Support for multiple locations</v-list-item-title>
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
              <h2 class="text-body-1 mb-4">
                Plugin's information:
              </h2>

              <ul>
                <li>Plugin version number: 1.0.0</li>
                <li>Plugin version type: <em>Light</em></li>
                <li>Update date: 27 April 2026</li>
              </ul>

              <v-divider class="my-4"></v-divider>

              <div class="d-flex flex-column gap-2 mb-3">
                <div class="d-flex align-center gap-2  mb-3">
                  <v-icon color="primary">mdi-email</v-icon>
                  <span class="text-body-2">
                    Feedback:
                    <a href="mailto:support@timelinedev.com" class="text-blue-600 hover:text-blue-800">
                      support@timelinedev.com
                    </a>
                  </span>
                </div>

                <div class="d-flex align-center gap-2 mb-3">
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

                <div class="d-flex align-center gap-2 mb-3">
                  <v-icon color="primary">mdi-web</v-icon>
                  <span class="text-body-2">
                    Website:
                    <a
                        href="https://timeline-dev.com"
                        target="_blank"
                        class="text-blue-600 hover:text-blue-800"
                    >
                      https://timeline-dev.com/plugins/wellness-studio-calendar
                    </a>
                  </span>
                </div>

                <div class="d-flex align-center gap-2 mb-3">
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

      <!-- FAQ Tab -->
      <v-tabs-window-item value="faq">
        <v-card class="pa-6">
          <h2 class="text-h5 mb-4">FAQ</h2>

          <v-card variant="outlined" class="pa-4">
            <v-card-text>
              <h1 class="text-body-1 mb-4">
                Frequently asked questions
              </h1>

              <h2 class="text-body-1 mb-4">
                # What happens on uninstall?
              </h2>

              <p>You can uninstall the plugin from the WordPress Plugins section by clicking "Uninstall". This will
                delete all the plugin's data, erase any database tables that were created and while out any user data
                that the plugin has generated. if you prefer to keep that data you can deactivate the plugin, which will
                not activate the "uninstall" procedure. </p>

            </v-card-text>
          </v-card>
        </v-card>
      </v-tabs-window-item>
    </v-tabs-window>


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
import {ref, reactive} from 'vue';
import {QuillEditor} from '@vueup/vue-quill';
import '@vueup/vue-quill/dist/vue-quill.snow.css';
import ViewTitle from "../components/View-title.vue";

// Types
interface CaptchaSettings {
  enabled: boolean
  siteKey: string
  isSaved: boolean
}

interface EmailSettings {
  confirmationTemplate: string
  cancellationTemplate: string
}

interface Settings {
  captcha: CaptchaSettings
  locationName: string,
  email: EmailSettings
  paymentMethods: string[]
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
    enabled: true,
    isSaved: true,
    siteKey: '1b18ff62-eba2-4f8e-bda8-a6465ce28da8'
  },
  email: {
    confirmationTemplate: '<p>Thank you for your reservation! We look forward to seeing you.</p>',
    cancellationTemplate: '<p>Your reservation has been canceled successfully.</p>'
  },
  locationName: '',
  paymentMethods: ['Cash on visit', 'Card payment on visit']
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
      [{'header': 1}, {'header': 2}],
      [{'list': 'ordered'}, {'list': 'bullet'}],
      [{'script': 'sub'}, {'script': 'super'}],
      [{'indent': '-1'}, {'indent': '+1'}],
      [{'direction': 'rtl'}],
      [{'size': ['small', false, 'large', 'huge']}],
      [{'header': [1, 2, 3, 4, 5, 6, false]}],
      [{'color': []}, {'background': []}],
      [{'font': []}],
      [{'align': []}],
      ['clean'],
      ['link', 'image']
    ]
  },
  placeholder: 'Enter your email template...'
}

// Methods
const addPaymentMethod = (): void => {
  settings.paymentMethods.push('')
}

const removePaymentMethod = (index: number): void => {
  settings.paymentMethods.splice(index, 1)
}

const removeCaptcha = async (): Promise<void> => {
  saving.value = true
  try {
    const formData = new FormData()
    formData.append('action', 'remove_captcha')

    const response = await fetch('/wp-admin/admin-ajax.php', {
      method: 'POST',
      body: formData
    })

    if (!response.ok) {
      showSnackbar(`HTTP error! status: ${response.status}`, 'error')
      return
    }

    const result = await response.json()

    if (result.success) {
      settings.captcha.enabled = false
      settings.captcha.siteKey = ''
      settings.captcha.isSaved = false
      showSnackbar('Captcha removed successfully.', 'success')
    } else {
      showSnackbar(result.data?.message || 'Failed to remove captcha.', 'error')
    }
  } catch (err) {
    showSnackbar(
        err instanceof Error ? err.message : 'Failed to remove captcha. Please try again.',
        'error'
    )
  } finally {
    saving.value = false
  }
}

type SettingsType = 'payment-method' | 'location' | 'captcha' | 'email-templates'

const payloadForType = (type: SettingsType): Record<string, unknown> => {
  switch (type) {
    case 'payment-method': return { paymentMethods: settings.paymentMethods }
    case 'location':       return { locationName: settings.locationName }
    case 'captcha':        return { captcha: settings.captcha }
    case 'email-templates':return { email: settings.email }
  }
}

const validateSettings = (type: SettingsType): string | null => {
  switch (type) {
    case 'payment-method':
      if (settings.paymentMethods.some(m => !m.trim()))
        return 'Payment method names cannot be empty.'
      return null
    case 'location':
      if (!settings.locationName.trim())
        return 'Location name cannot be empty.'
      return null
    case 'captcha':
      if (settings.captcha.enabled && !settings.captcha.siteKey.trim())
        return 'hCaptcha site key is required when captcha is enabled.'
      return null
    case 'email-templates':
      if (!settings.email.confirmationTemplate.trim() || !settings.email.cancellationTemplate.trim())
        return 'Both email templates must have content.'
      return null
  }
}

const saveSettings = async (type: SettingsType) => {
  const error = validateSettings(type);
  if (error) {
    showSnackbar(error, 'error');
    return;
  }

  saving.value = true;

  try {
    const formData = new FormData();
    formData.append('action', 'store_settings');
    formData.append('type', type);
    formData.append('settings', JSON.stringify(payloadForType(type)));

    const response = await fetch('/wp-admin/admin-ajax.php', {
      method: 'POST',
      body: formData
    });

    if (!response.ok) {
      showSnackbar(`HTTP error! status: ${response.status}`, 'error');
      return;
    }

    const result = await response.json();

    if (result.success) {
      showSnackbar('Settings saved successfully!', 'success');
      if (type === 'captcha') {
        settings.captcha.isSaved = true;
      }
    } else {
      showSnackbar(result.data?.message || 'Failed to save settings.', 'error');
    }
  } catch (err) {
    showSnackbar(
        err instanceof Error ? err.message : 'Failed to save settings. Please try again.',
        'error'
    );
  } finally {
    saving.value = false;
  }
}

const showSnackbar = (message: string, color: Snackbar['color']): void => {
  snackbar.message = message;
  snackbar.color = color;
  snackbar.show = true;
}
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