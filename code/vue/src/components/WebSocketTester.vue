<script setup>
import { ref, inject } from 'vue'
import Button from './ui/button/Button.vue';

const socket = inject('socket')

const message = ref('DAD Intermediate Submission')
const responseData = ref('')

const send = () => {
    socket.emit('echo', message.value)
}

socket.on('echo', (message) => {
    responseData.value = message
})
</script>

<template>
    <div class="max-w-2xl mx-auto py-8">
        <h2 class="text-2xl font-bold text-gray-900 mb-6">WebSocket Tester</h2>

        <div class="mb-6 flex items-center space-x-2">
            <div class="flex items-center">
                <div class="h-2.5 w-2.5 rounded-full  mr-2 animate-pulse"
                    :class="{ 'bg-green-500': socket.connected, 'bg-red-500': !socket.connected, }"></div>
                <span class="text-sm text-gray-600"> {{ (!socket.connected) ? 'Not' : '' }} Connected</span>
            </div>
        </div>

        <form class="space-y-6">
            <div class="space-y-2">
                <label for="message" class="block text-sm font-medium text-gray-700">
                    Message:
                </label>
                <div class="flex space-x-4">
                    <input type="text" id="message" v-model="message"
                        class="flex-1 px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                        placeholder="Type your message here...">
                    <Button @click.prevent="send" type="submit">Send </Button>
                </div>
            </div>

            <div v-if="responseData" class="space-y-2 mt-8">
                <div class="flex justify-between items-center mb-2">
                    <label for="response" class="block text-sm font-medium text-gray-700">
                        Response
                    </label>
                    <span class="text-xs text-gray-500">Real-time updates</span>
                </div>
                <textarea :value="responseData" id="response" rows="5"
                    class="mt-1 block w-full px-3 py-2 bg-gray-50 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 sm:text-sm font-mono"
                    readonly></textarea>
            </div>
        </form>
    </div>
</template>