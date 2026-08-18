<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Search</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>
</head>
<body class="bg-gray-50">
    <div id="app" class="min-h-screen">
        <!-- Header -->
        <header class="bg-white shadow">
            <div class="max-w-6xl mx-auto px-4 py-6">
                <h1 class="text-3xl font-bold text-gray-900">User Search</h1>
                <p class="text-gray-600 mt-1">Search across users</p>
            </div>
        </header>

        <!-- Main Content -->
        <main class="max-w-6xl mx-auto px-4 py-8">
            <!-- Search Form -->
            <div class="bg-white rounded-lg shadow p-6 mb-8">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                    <!-- Query Input -->
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Query</label>
                        <input
                            v-model="search.query"
                            @keyup.enter="performSearch"
                            type="text"
                            placeholder="Enter email, phone, user ID, or name..."
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            :disabled="loading"
                        >
                    </div>

                    <!-- Search Type Dropdown -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Search Type</label>
                        <select
                            v-model="search.type"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            :disabled="loading"
                        >
                            <option value="email">Email</option>
                            <option value="phone">Phone</option>
                            <option value="user_id">User ID</option>
                            <option value="name">Name</option>
                        </select>
                    </div>
                </div>

                <!-- Additional Options -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Limit</label>
                        <input
                            v-model.number="search.limit"
                            type="number"
                            min="1"
                            max="100"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            :disabled="loading"
                        >
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Offset</label>
                        <input
                            v-model.number="search.offset"
                            type="number"
                            min="0"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            :disabled="loading"
                        >
                    </div>

                    <div class="flex items-end">
                        <button
                            @click="performSearch"
                            :disabled="loading || !search.query"
                            class="w-full px-6 py-2 bg-blue-600 hover:bg-blue-700 disabled:bg-gray-400 text-white font-medium rounded-lg transition"
                        >
                            @{{ loading ? 'Searching...' : 'Search' }}
                        </button>
                    </div>
                </div>

                <!-- Error Message -->
                <div v-if="error" class="mt-4 p-4 bg-red-50 border border-red-200 rounded-lg text-red-700">
                    @{{ error }}
                </div>
            </div>

            <!-- Results Section -->
            <div v-if="results.length > 0 || searched" class="bg-white rounded-lg shadow p-6">
                <!-- Stats -->
                <div class="mb-6 flex justify-between items-center">
                    <div>
                        <h2 class="text-xl font-bold text-gray-900">
                            Results
                            <span v-if="results.length > 0" class="text-gray-600 font-normal text-lg">
                                (@{{ results.length }} of @{{ pagination.total }} total)
                            </span>
                        </h2>
                        <p v-if="results.length > 0" class="text-sm text-gray-600 mt-1">
                            Found in @{{ lastResponse.took_ms }}ms
                        </p>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-if="results.length === 0" class="text-center py-12">
                    <h3 class="text-sm font-medium text-gray-900">No results</h3>
                    <p class="mt-1 text-sm text-gray-600">
                        @{{ searched ? 'No users found matching your search.' : 'Enter a search query to get started.' }}
                    </p>
                </div>

                <!-- Results Table -->
                <div v-if="results.length > 0" class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User ID</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Phone</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="user in results" :key="user.user_id" class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">@{{ user.user_id }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">@{{ user.full_name }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">@{{ user.user_email }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">@{{ user.msisdn || '-' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <span :class="getStatusClass(user.status)" class="px-2 py-1 rounded text-xs font-medium">
                                        @{{ getStatusLabel(user.status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                    @{{ formatDate(user.created_at) }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div v-if="results.length > 0" class="mt-6 flex justify-between items-center">
                    <div class="text-sm text-gray-600">
                        Showing @{{ pagination.offset + 1 }} to @{{ Math.min(pagination.offset + pagination.limit, pagination.total) }} of @{{ pagination.total }}
                    </div>
                    <div class="flex gap-2">
                        <button
                            @click="previousPage"
                            :disabled="pagination.offset === 0 || loading"
                            class="px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            Previous
                        </button>
                        <button
                            @click="nextPage"
                            :disabled="pagination.offset + pagination.limit >= pagination.total || loading"
                            class="px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            Next
                        </button>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        const { createApp } = Vue;

        createApp({
            data() {
                return {
                    search: {
                        query: '',
                        type: 'name',
                        limit: 10,
                        offset: 0,
                    },
                    results: [],
                    pagination: {
                        total: 0,
                        limit: 10,
                        offset: 0,
                    },
                    loading: false,
                    error: null,
                    searched: false,
                    lastResponse: {},
                };
            },
            methods: {
                async performSearch() {
                    this.error = null;
                    this.loading = true;

                    try {
                        const params = new URLSearchParams({
                            q: this.search.query,
                            type: this.search.type,
                            limit: this.search.limit,
                            offset: this.search.offset,
                        });

                        const response = await fetch(`/api/search?${params}`);

                        if (!response.ok) {
                            const errorData = await response.json();
                            this.error = errorData.error || 'Search failed';
                            this.results = [];
                            this.searched = true;
                            return;
                        }

                        const data = await response.json();
                        this.lastResponse = data;
                        this.results = data.results;
                        this.pagination = {
                            total: data.total,
                            limit: data.limit,
                            offset: data.offset,
                        };
                        this.searched = true;
                    } catch (err) {
                        this.error = 'Network error. Please try again.';
                        this.results = [];
                        this.searched = true;
                    } finally {
                        this.loading = false;
                    }
                },

                nextPage() {
                    this.search.offset += this.search.limit;
                    this.performSearch();
                },

                previousPage() {
                    this.search.offset = Math.max(0, this.search.offset - this.search.limit);
                    this.performSearch();
                },

                getStatusLabel(status) {
                    const statuses = {
                        0: 'Inactive',
                        1: 'Active',
                        2: 'Suspended',
                    };
                    return statuses[status] || 'Unknown';
                },

                getStatusClass(status) {
                    const classes = {
                        0: 'bg-gray-100 text-gray-800',
                        1: 'bg-green-100 text-green-800',
                        2: 'bg-red-100 text-red-800',
                    };
                    return classes[status] || 'bg-gray-100 text-gray-800';
                },

                formatDate(dateString) {
                    if (!dateString) return '-';
                    return new Date(dateString).toLocaleDateString('en-US', {
                        year: 'numeric',
                        month: 'short',
                        day: 'numeric',
                    });
                },
            },
        }).mount('#app');
    </script>
</body>
</html>
