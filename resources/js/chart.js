import Chart from 'chart.js/auto';

// Global chart instances
window.dashboardCharts = {
    systemOverview: null,
    userTrend: null,
    todayTransaction: null,
    weeklyTransaction: null
};

// Initialize charts function
function initializeDashboardCharts() {
    let initialized = false;
    
    // Initialize admin charts if they exist
    const systemChartEl = document.getElementById('systemOverviewChart');
    const trendChartEl = document.getElementById('userTrendChart');
    
    if (systemChartEl && trendChartEl) {
        initialized = initializeAdminCharts() || initialized;
    }
    
    // Initialize transaction charts if they exist
    const todayTransactionEl = document.getElementById('todayTransactionChart');
    const weeklyTransactionEl = document.getElementById('weeklyTransactionChart');
    
    if (todayTransactionEl || weeklyTransactionEl) {
        initialized = initializeTransactionCharts() || initialized;
    }
    
    return initialized;
}

// Initialize admin charts function
function initializeAdminCharts() {
    const systemChartEl = document.getElementById('systemOverviewChart');
    const trendChartEl = document.getElementById('userTrendChart');
    
    if (!systemChartEl || !trendChartEl) {
        return false;
    }
    
    try {
        // Destroy existing charts
        if (window.dashboardCharts.systemOverview) {
            window.dashboardCharts.systemOverview.destroy();
        }
        if (window.dashboardCharts.userTrend) {
            window.dashboardCharts.userTrend.destroy();
        }
        
        // Get data for system overview chart
        const usersEl = document.querySelector('[data-users-count]');
        const rolesEl = document.querySelector('[data-roles-count]');
        const permissionsEl = document.querySelector('[data-permissions-count]');
        
        const usersCount = parseInt(usersEl?.dataset.usersCount || usersEl?.textContent || 0);
        const rolesCount = parseInt(rolesEl?.dataset.rolesCount || rolesEl?.textContent || 0);
        const permissionsCount = parseInt(permissionsEl?.dataset.permissionsCount || permissionsEl?.textContent || 0);
        
        // Create System Overview Chart
        const ctx1 = systemChartEl.getContext('2d');
        window.dashboardCharts.systemOverview = new Chart(ctx1, {
            type: 'doughnut',
            data: {
                labels: ['Users', 'Roles', 'Permissions'],
                datasets: [{
                    data: [usersCount, rolesCount, permissionsCount],
                    backgroundColor: ['#2563EB', '#16A34A', '#9333EA'],
                    borderColor: ['#1D4ED8', '#15803D', '#7C3AED'],
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 20,
                            usePointStyle: true,
                            color: getThemeColor()
                        }
                    }
                }
            }
        });
        
        // Get trend data
        const trendDataRaw = trendChartEl.dataset.trendData;
        const trendData = JSON.parse(trendDataRaw || '{"labels":[],"data":[]}');
        
        // Create User Trend Chart
        const ctx2 = trendChartEl.getContext('2d');
        window.dashboardCharts.userTrend = new Chart(ctx2, {
            type: 'line',
            data: {
                labels: trendData.labels || [],
                datasets: [{
                    label: 'New Users',
                    data: trendData.data || [],
                    borderColor: '#2563EB',
                    backgroundColor: 'rgba(37, 99, 235, 0.1)',
                    tension: 0.4,
                    fill: true,
                    pointBackgroundColor: '#2563EB',
                    pointBorderColor: '#FFFFFF',
                    pointBorderWidth: 2,
                    pointRadius: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { color: getTickColor() },
                        grid: { color: getGridColor() }
                    },
                    x: {
                        ticks: { color: getTickColor() },
                        grid: { color: getGridColor() }
                    }
                }
            }
        });
        
        return true;
        
    } catch (error) {
        console.error('Error initializing admin charts:', error);
        return false;
    }
}

// Initialize transaction charts function
function initializeTransactionCharts() {
    const todayChartEl = document.getElementById('todayTransactionChart');
    const weeklyChartEl = document.getElementById('weeklyTransactionChart');
    
    if (!todayChartEl && !weeklyChartEl) {
        return false;
    }
    
    try {
        let initialized = false;
        
        // Initialize Today Transaction Chart
        if (todayChartEl) {
            // Destroy existing chart
            if (window.dashboardCharts.todayTransaction) {
                window.dashboardCharts.todayTransaction.destroy();
            }
            
            // Get chart data
            const todayDataRaw = todayChartEl.dataset.chartData;
            const todayData = JSON.parse(todayDataRaw || '{"labels":[],"revenue":[],"transactions":[]}');
            
            // Create Today Transaction Chart
            const ctx1 = todayChartEl.getContext('2d');
            window.dashboardCharts.todayTransaction = new Chart(ctx1, {
                type: 'line',
                data: {
                    labels: todayData.labels || [],
                    datasets: [
                        {
                            label: 'Pendapatan (Rp)',
                            data: todayData.revenue || [],
                            borderColor: '#16A34A',
                            backgroundColor: 'rgba(22, 163, 74, 0.1)',
                            tension: 0.4,
                            fill: true,
                            yAxisID: 'y',
                            pointBackgroundColor: '#16A34A',
                            pointBorderColor: '#FFFFFF',
                            pointBorderWidth: 2,
                            pointRadius: 4
                        },
                        {
                            label: 'Jumlah Transaksi',
                            data: todayData.transactions || [],
                            borderColor: '#2563EB',
                            backgroundColor: 'rgba(37, 99, 235, 0.1)',
                            tension: 0.4,
                            fill: false,
                            yAxisID: 'y1',
                            pointBackgroundColor: '#2563EB',
                            pointBorderColor: '#FFFFFF',
                            pointBorderWidth: 2,
                            pointRadius: 4
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: {
                        mode: 'index',
                        intersect: false,
                    },
                    plugins: {
                        legend: {
                            position: 'top',
                            labels: {
                                padding: 20,
                                usePointStyle: true,
                                color: getThemeColor()
                            }
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    if (context.datasetIndex === 0) {
                                        return 'Pendapatan: Rp ' + new Intl.NumberFormat('id-ID').format(context.parsed.y);
                                    } else {
                                        return 'Transaksi: ' + context.parsed.y;
                                    }
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            ticks: { color: getTickColor() },
                            grid: { color: getGridColor() }
                        },
                        y: {
                            type: 'linear',
                            display: true,
                            position: 'left',
                            beginAtZero: true,
                            ticks: { 
                                color: getTickColor(),
                                callback: function(value) {
                                    return 'Rp ' + new Intl.NumberFormat('id-ID').format(value);
                                }
                            },
                            grid: { color: getGridColor() }
                        },
                        y1: {
                            type: 'linear',
                            display: true,
                            position: 'right',
                            beginAtZero: true,
                            ticks: { color: getTickColor() },
                            grid: { drawOnChartArea: false }
                        }
                    }
                }
            });
            initialized = true;
        }
        
        // Initialize Weekly Transaction Chart
        if (weeklyChartEl) {
            // Destroy existing chart
            if (window.dashboardCharts.weeklyTransaction) {
                window.dashboardCharts.weeklyTransaction.destroy();
            }
            
            // Get chart data
            const weeklyDataRaw = weeklyChartEl.dataset.chartData;
            const weeklyData = JSON.parse(weeklyDataRaw || '{"labels":[],"revenue":[],"transactions":[]}');
            
            // Create Weekly Transaction Chart
            const ctx2 = weeklyChartEl.getContext('2d');
            window.dashboardCharts.weeklyTransaction = new Chart(ctx2, {
                type: 'bar',
                data: {
                    labels: weeklyData.labels || [],
                    datasets: [
                        {
                            label: 'Pendapatan (Rp)',
                            data: weeklyData.revenue || [],
                            backgroundColor: 'rgba(22, 163, 74, 0.8)',
                            borderColor: '#16A34A',
                            borderWidth: 1,
                            yAxisID: 'y'
                        },
                        {
                            label: 'Jumlah Transaksi',
                            data: weeklyData.transactions || [],
                            type: 'line',
                            borderColor: '#2563EB',
                            backgroundColor: 'rgba(37, 99, 235, 0.1)',
                            tension: 0.4,
                            fill: false,
                            yAxisID: 'y1',
                            pointBackgroundColor: '#2563EB',
                            pointBorderColor: '#FFFFFF',
                            pointBorderWidth: 2,
                            pointRadius: 4
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: {
                        mode: 'index',
                        intersect: false,
                    },
                    plugins: {
                        legend: {
                            position: 'top',
                            labels: {
                                padding: 20,
                                usePointStyle: true,
                                color: getThemeColor()
                            }
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    if (context.datasetIndex === 0) {
                                        return 'Pendapatan: Rp ' + new Intl.NumberFormat('id-ID').format(context.parsed.y);
                                    } else {
                                        return 'Transaksi: ' + context.parsed.y;
                                    }
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            ticks: { color: getTickColor() },
                            grid: { color: getGridColor() }
                        },
                        y: {
                            type: 'linear',
                            display: true,
                            position: 'left',
                            beginAtZero: true,
                            ticks: { 
                                color: getTickColor(),
                                callback: function(value) {
                                    return 'Rp ' + new Intl.NumberFormat('id-ID').format(value);
                                }
                            },
                            grid: { color: getGridColor() }
                        },
                        y1: {
                            type: 'linear',
                            display: true,
                            position: 'right',
                            beginAtZero: true,
                            ticks: { color: getTickColor() },
                            grid: { drawOnChartArea: false }
                        }
                    }
                }
            });
            initialized = true;
        }
        
        return initialized;
        
    } catch (error) {
        console.error('Error initializing transaction charts:', error);
        return false;
    }
}

// Theme color functions
function getThemeColor() {
    return document.documentElement.classList.contains('dark') ? '#F4F4F5' : '#18181B';
}

function getTickColor() {
    return document.documentElement.classList.contains('dark') ? '#A1A1AA' : '#71717A';
}

function getGridColor() {
    return document.documentElement.classList.contains('dark') ? '#27272A' : '#E4E4E7';
}

// Event listeners
document.addEventListener('DOMContentLoaded', function() {
    setTimeout(initializeDashboardCharts, 100);
});

document.addEventListener('livewire:navigated', function() {
    setTimeout(initializeDashboardCharts, 300);
});

// Listen for Livewire updates to refresh charts
document.addEventListener('livewire:updated', function() {
    console.log('Livewire updated - refreshing charts');
    setTimeout(initializeDashboardCharts, 200);
});

// Listen for Livewire component updates (when properties change)
document.addEventListener('livewire:component-updated', function() {
    console.log('Livewire component updated - refreshing charts');
    setTimeout(initializeDashboardCharts, 300);
});

// Listen for custom charts refresh event
window.addEventListener('charts-refresh', function() {
    console.log('Charts refresh event received');
    setTimeout(initializeDashboardCharts, 100);
});

// Fallback: Try multiple times with increasing delays
function tryInitializeWithRetry() {
    const delays = [500, 1000, 2000];
    
    delays.forEach(delay => {
        setTimeout(() => {
            // Check if admin charts need initialization
            const needsAdminCharts = document.getElementById('systemOverviewChart') && 
                                   document.getElementById('userTrendChart') &&
                                   (!window.dashboardCharts.systemOverview || !window.dashboardCharts.userTrend);
            
            // Check if transaction charts need initialization  
            const needsTransactionCharts = (document.getElementById('todayTransactionChart') && !window.dashboardCharts.todayTransaction) ||
                                         (document.getElementById('weeklyTransactionChart') && !window.dashboardCharts.weeklyTransaction);
            
            if (needsAdminCharts || needsTransactionCharts) {
                initializeDashboardCharts();
            }
        }, delay);
    });
}

// Start retry attempts after DOM is ready
document.addEventListener('DOMContentLoaded', tryInitializeWithRetry);
document.addEventListener('livewire:navigated', tryInitializeWithRetry);
document.addEventListener('livewire:updated', tryInitializeWithRetry);