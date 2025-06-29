<div class="flex h-full w-full flex-1 flex-col gap-6 rounded-xl">
    @role('Super Admin')
    <!-- Cards Section -->
    <div class="grid auto-rows-min gap-6 md:grid-cols-3">
        <div class="bg-white dark:bg-zinc-900 p-6 rounded-lg shadow-sm border border-zinc-200 dark:border-zinc-700">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">Total Users</h3>
                    <p class="text-sm text-zinc-600 dark:text-zinc-400">Number of registered users</p>
                </div>
                <div class="rounded-full bg-blue-100 p-3 dark:bg-blue-900/20">
                    <flux:icon.users class="h-6 w-6 text-blue-600" />
                </div>
            </div>
            <div class="mt-4">
                <p class="text-2xl font-bold text-zinc-900 dark:text-zinc-100" data-users-count="{{ $this->totalUsers }}">{{ $this->totalUsers }}</p>
            </div>
        </div>

        <div class="bg-white dark:bg-zinc-900 p-6 rounded-lg shadow-sm border border-zinc-200 dark:border-zinc-700">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">Total Roles</h3>
                    <p class="text-sm text-zinc-600 dark:text-zinc-400">Number of system roles</p>
                </div>
                <div class="rounded-full bg-green-100 p-3 dark:bg-green-900/20">
                    <flux:icon.shield-check class="h-6 w-6 text-green-600" />
                </div>
            </div>
            <div class="mt-4">
                <p class="text-2xl font-bold text-zinc-900 dark:text-zinc-100" data-roles-count="{{ $this->totalRoles }}">{{ $this->totalRoles }}</p>
            </div>
        </div>

        <div class="bg-white dark:bg-zinc-900 p-6 rounded-lg shadow-sm border border-zinc-200 dark:border-zinc-700">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">Total Permissions</h3>
                    <p class="text-sm text-zinc-600 dark:text-zinc-400">Number of system permissions</p>
                </div>
                <div class="rounded-full bg-purple-100 p-3 dark:bg-purple-900/20">
                    <flux:icon.key class="h-6 w-6 text-purple-600" />
                </div>
            </div>
            <div class="mt-4">
                <p class="text-2xl font-bold text-zinc-900 dark:text-zinc-100" data-permissions-count="{{ $this->totalPermissions }}">{{ $this->totalPermissions }}</p>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="grid gap-6 lg:grid-cols-2">
        <div class="bg-white dark:bg-zinc-900 p-6 rounded-lg shadow-sm border border-zinc-200 dark:border-zinc-700">
            <h3 class="mb-4 text-lg font-semibold text-zinc-900 dark:text-zinc-100">System Overview</h3>
            <div class="h-64">
                <canvas id="systemOverviewChart"></canvas>
            </div>
        </div>

        <div class="bg-white dark:bg-zinc-900 p-6 rounded-lg shadow-sm border border-zinc-200 dark:border-zinc-700">
            <h3 class="mb-4 text-lg font-semibold text-zinc-900 dark:text-zinc-100">User Registration Trend</h3>
            <div class="h-64">
                <canvas id="userTrendChart" data-trend-data="{{ json_encode($this->userRegistrationTrend) }}"></canvas>
            </div>
        </div>
    </div>

    <!-- Info Section -->
    <div class="grid gap-6 lg:grid-cols-2">
        <div class="bg-white dark:bg-zinc-900 p-6 rounded-lg shadow-sm border border-zinc-200 dark:border-zinc-700">
            <h3 class="mb-4 text-lg font-semibold text-zinc-900 dark:text-zinc-100">Recent Users</h3>
            <div class="space-y-3">
                @foreach($this->recentUsers as $user)
                    <div class="flex items-center justify-between rounded-lg bg-zinc-50 p-3 dark:bg-zinc-800/50">
                        <div class="flex items-center space-x-3">
                            <flux:avatar circle initials="{{ $user->initials() }}" />
                            <div>
                                <p class="text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ $user->name }}</p>
                                <p class="text-xs text-zinc-600 dark:text-zinc-400">{{ $user->email }}</p>
                            </div>
                        </div>
                        <div class="text-xs text-zinc-500 dark:text-zinc-400">
                            {{ $user->created_at->diffForHumans() }}
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="bg-white dark:bg-zinc-900 p-6 rounded-lg shadow-sm border border-zinc-200 dark:border-zinc-700">
            <h3 class="mb-4 text-lg font-semibold text-zinc-900 dark:text-zinc-100">System Statistics</h3>
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <span class="text-sm text-zinc-600 dark:text-zinc-400">Active Users</span>
                    <span class="text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ $this->activeUsers }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-sm text-zinc-600 dark:text-zinc-400">Available Roles</span>
                    <span class="text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ $this->totalRoles }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-sm text-zinc-600 dark:text-zinc-400">Registered Permissions</span>
                    <span class="text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ $this->totalPermissions }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-sm text-zinc-600 dark:text-zinc-400">New Users (7 days)</span>
                    <span class="text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ $this->newUsersThisWeek }}</span>
                </div>
            </div>
        </div>
    </div>
    @endrole

    @role('Admin|Cashier')
    @endrole
</div>
