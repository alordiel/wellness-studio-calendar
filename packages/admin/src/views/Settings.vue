<template>
  <div class="min-h-screen bg-gray-100 p-6">
    <h1 class="text-3xl font-bold mb-6">Settings</h1>
    <router-link to="/" class="text-blue-600 hover:text-blue-800">← Back to Dashboard</router-link>
    <!-- Tabs -->
    <tab>Settings</tab>
    <tab>Email</tab>
    <tab>Coming next</tab>
    <tab>About</tab>
    <!-- Start: Content for Settings tab -->
    <h2>Captcha</h2>
    <p>Allowing captcha will reduce the chance of bots registering for yor events</p>
    <label for="captcha">
      <input type="checkbox" name="captcha" id="captcha"/> Add hCaptcha
    </label>
    <div class="hidden-hcaptcha-api-key"></div>
    <small>You can get your free hCaptcha key following these instructions <a href="https://captcha4wp.com/docs/how-to-get-hcaptcha-keys/" target="_blank">here</a></small>
    <!-- End: Content for Settings tab -->
    <!-- Start: Content for Email tab -->
    <h1>Email templates</h1>
    <label>Email template for confirming the reservation</label>
    <textarea></textarea>
    <label>Email template for confirming cancellationSS</label>
    <textarea></textarea>
    <!-- End: Content for Settings tab -->
    <!-- Start: Content for "Coming next" tab -->
    <p>Planned for some day in the Pro version:</p>
    <ul>
      <li>Option for waiting list</li>
      <li>Option to chose the max cancelling hour before the start of the event</li>
      <li>Option to show more then a week  of activities</li>
    </ul>
    <!-- End: Content for Settings tab -->
    <!-- Start: Content for About tab -->
    <div class="card">
      Some information about the plugin plus contact details.
      What happens on uninstall
      Feedback: support@timelinedev.com
      Github: https://github.com/alordiel/wellness-studio-calendar
      Company site: https://timeline-dev.com
      WodrPress: Please review in WordPress
    </div>
    <!-- End: Content for About tab -->
  </div>
</template>

<script setup lang="ts">
// Placeholder for reservations page
</script>