<script setup>
import Toaster from './components/ui/toast/Toaster.vue';

</script>

<template>
  <Toaster />
  <div class="min-h-screen bg-gray-50">
    <header class="bg-white shadow-sm">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <nav class="flex items-center justify-start h-16 space-x-8">
          <RouterLink to="/"
            class="text-gray-900 hover:text-blue-600 px-3 py-2 rounded-md text-sm font-medium transition-colors"
            active-class="text-blue-600 font-semibold">
            Home
          </RouterLink>
          <RouterLink to="/testers/laravel"
            class="text-gray-900 hover:text-blue-600 px-3 py-2 rounded-md text-sm font-medium transition-colors"
            active-class="text-blue-600 font-semibold">
            Laravel Tester
          </RouterLink>
          <RouterLink to="/testers/websocket"
            class="text-gray-900 hover:text-blue-600 px-3 py-2 rounded-md text-sm font-medium transition-colors"
            active-class="text-blue-600 font-semibold">
            WebSockets Tester
          </RouterLink>
        </nav>
      </div>
    </header>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <RouterView />
    </main>
  </div>
</template>