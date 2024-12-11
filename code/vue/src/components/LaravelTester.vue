<script setup>
import { ref, computed } from 'vue'
import { useAuthStore } from '@/stores/auth';
import { Button } from '@/components/ui/button'
import { useGamesStore } from '@/stores/games'
import { format } from 'date-fns'
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow
} from '@/components/ui/table'
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue
} from '@/components/ui/select'
import { Badge } from '@/components/ui/badge'
import { ArrowUpDown, Loader2 } from 'lucide-vue-next'

const store = useGamesStore()
const authStore = useAuthStore()

const loading = ref(false)
const selectedType = ref('')
const selectedStatus = ref('')
const sortField = ref('created_at')
const sortDirection = ref('desc')
const email = ref('a1@mail.pt')
const password = ref('123')
const responseData = ref('')


const games = computed(() => store.games)

const fetchData = async (resetPagination = false) => {
    loading.value = true

    store.filters = {
        type: selectedType.value,
        status: selectedStatus.value,
        sort_by: sortField.value,
        sort_direction: sortDirection.value
    }

    await store.fetchGames(resetPagination)
    loading.value = false
}

const handleFiltersChange = async () => {
    await fetchData(true)
}

const toggleSort = (field) => {
    if (sortField.value === field) {
        sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc'
    } else {
        sortField.value = field
        sortDirection.value = 'desc'
    }
    handleFiltersChange()
}

const loadMore = async () => {
    loading.value = true
    await store.fetchGamesNextPage()
    loading.value = false
}

const getStatusVariant = (status) => {
    const variants = {
        PE: 'secondary',
        PL: 'default',
        E: 'success',
        I: 'destructive'
    }
    return variants[status] || 'default'
}

const getStatusLabel = (status) => {
    const labels = {
        PE: 'Pending',
        PL: 'Playing',
        E: 'Ended',
        I: 'Interrupted'
    }
    return labels[status] || status
}

const formatDate = (date) => {
    return format(new Date(date), 'PPp')
}


const showGames = ref(false)
const submit = async () => {

    const user = await authStore.login({
        email: email.value,
        password: password.value
    })
    responseData.value = user.name
    showGames.value = true
    await fetchData()

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

        <div class="space-y-4 mt-10" v-if="showGames">
            <div class="flex gap-4">
                <Select v-model="selectedType" @update:modelValue="handleFiltersChange">
                    <SelectTrigger class="w-[180px]">
                        <SelectValue placeholder="Select type" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem value="S">Single Player</SelectItem>
                        <SelectItem value="M">Multiplayer</SelectItem>
                    </SelectContent>
                </Select>

                <Select v-model="selectedStatus" @update:modelValue="handleFiltersChange">
                    <SelectTrigger class="w-[180px]">
                        <SelectValue placeholder="Select status" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem value="PE">Pending</SelectItem>
                        <SelectItem value="PL">Playing</SelectItem>
                        <SelectItem value="E">Ended</SelectItem>
                        <SelectItem value="I">Interrupted</SelectItem>
                    </SelectContent>
                </Select>
            </div>

            <div class="rounded-md border">
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>ID</TableHead>
                            <TableHead>
                                <div class="flex items-center gap-2 cursor-pointer" @click="toggleSort('type')">
                                    Type
                                    <ArrowUpDown class="h-4 w-4" />
                                </div>
                            </TableHead>
                            <TableHead>
                                <div class="flex items-center gap-2 cursor-pointer" @click="toggleSort('status')">
                                    Status
                                    <ArrowUpDown class="h-4 w-4" />
                                </div>
                            </TableHead>
                            <TableHead>Created By</TableHead>
                            <TableHead>Winner</TableHead>
                            <TableHead>
                                <div class="flex items-center gap-2 cursor-pointer" @click="toggleSort('total_time')">
                                    Total Time
                                    <ArrowUpDown class="h-4 w-4" />
                                </div>
                            </TableHead>
                            <TableHead>
                                <div class="flex items-center gap-2 cursor-pointer" @click="toggleSort('created_at')">
                                    Created At
                                    <ArrowUpDown class="h-4 w-4" />
                                </div>
                            </TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-for="game in games" :key="game.id">
                            <TableCell>{{ game.id }}</TableCell>
                            <TableCell>
                                <Badge :variant="game.type === 'S' ? 'default' : 'secondary'">
                                    {{ game.type === 'S' ? 'Single' : 'Multi' }}
                                </Badge>
                            </TableCell>
                            <TableCell>
                                <Badge :variant="getStatusVariant(game.status)">
                                    {{ getStatusLabel(game.status) }}
                                </Badge>
                            </TableCell>
                            <TableCell>{{ game.created_by?.name }}</TableCell>
                            <TableCell>{{ game.winner?.name || '-' }}</TableCell>
                            <TableCell>{{ game.total_time ? `${game.total_time}s` : '-' }}</TableCell>
                            <TableCell>{{ formatDate(game.created_at) }}</TableCell>
                        </TableRow>
                        <TableRow v-if="!games?.length">
                            <TableCell colspan="7" class="text-center h-24">No games found</TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>

            <div class="flex justify-center">
                <Button variant="outline" @click="loadMore" :disabled="loading">
                    <Loader2 v-if="loading" class="mr-2 h-4 w-4 animate-spin" />
                    Load More
                </Button>
            </div>
        </div>
    </div>
</template>