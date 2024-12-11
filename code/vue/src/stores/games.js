import { ref, computed } from 'vue'
import { defineStore } from 'pinia'
import axios from 'axios'

export const useGamesStore = defineStore('games', () => {
  const games = ref(null)
  const page = ref(1)
  const filters = ref({
    type: '',
    status: '',
    sort_by: 'created_at',
    sort_direction: 'desc'
  })

  const totalGames = computed(() => games.value?.length)

  const resetPage = () => {
    page.value = 1
    games.value = null
  }

  const fetchGames = async (resetPagination = false) => {
    if (resetPagination) {
      resetPage()
    }

    const queryParams = new URLSearchParams({
      page: page.value,
      ...(filters.value.type && { type: filters.value.type }),
      ...(filters.value.status && { status: filters.value.status }),
      sort_by: filters.value.sort_by,
      sort_direction: filters.value.sort_direction
    }).toString()

    const response = await axios.get(`/games?${queryParams}`)

    if (page.value === 1 || resetPagination) {
      games.value = response.data.data
    } else {
      games.value = [...(games.value || []), ...response.data.data]
    }

    return response.data
  }

  const fetchGamesNextPage = async () => {
    page.value++
    await fetchGames()
  }

  return {
    games,
    totalGames,
    filters,
    page,
    fetchGames,
    fetchGamesNextPage,
    resetPage
  }
})
