<script setup>
import { ref } from 'vue'
import { useAuthStore } from '@/stores/auth';
import { Button } from '@/components/ui/button'


const authStore = useAuthStore()

const email = ref('a1@mail.pt')
const password = ref('123')
const responseData = ref('')

const submit = async () => {

    const user = await authStore.login({
        email: email.value,
        password: password.value
    })
    responseData.value = user.name

}
</script>

<template>
    <div class="max-w-2xl mx-auto py-8">
        <h2 class="text-2xl font-bold text-gray-900 mb-6">Laravel Tester</h2>

        <form class="space-y-6">
            <div class="space-y-2">
                <label for="email" class="block text-sm font-medium text-gray-700">
                    Email:
                </label>
                <input type="text" id="email" v-model="email"
                    class="mt-1 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
            </div>

            <div class="space-y-2">
                <label for="password" class="block text-sm font-medium text-gray-700">
                    Password:
                </label>
                <input type="password" id="password" v-model="password"
                    class="mt-1 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
            </div>

            <Button @click.prevent="submit" type="submit">Submit </Button>

            <div v-if="responseData" class="space-y-2 mt-8">
                <label for="response" class="block text-sm font-medium text-gray-700">
                    Response
                </label>
                <textarea :value="responseData" id="response" rows="3"
                    class="mt-1 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                    readonly></textarea>
            </div>
        </form>
    </div>
</template>