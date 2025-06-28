import Chart from 'chart.js/auto';

// Global chart instances
window.dashboardCharts = {
    systemOverview: null,
    userTrend: null,
    dailySales: null,
    paymentMethod: null,
    salesTrend: null,
    salesPaymentMethod: null,
    hourlyChart: null,
    stockValue: null,
    stockMovement: null
};

// Initialize charts function
function initializeDashboardCharts() {
    let success = false;
    
    // Initialize admin charts if they exist
    const systemChartEl = document.getElementById('systemOverviewChart');
    const trendChartEl = document.getElementById('userTrendChart');
    
    if (systemChartEl && trendChartEl) {
        success = initializeAdminCharts() || success;
    }
    
    // Initialize POS charts if they exist
    const dailySalesEl = document.getElementById('dailySalesChart');
    const paymentMethodEl = document.getElementById('paymentMethodChart');
    
    if (dailySalesEl && paymentMethodEl) {
        success = initializePOSCharts() || success;
    }
    
    // Initialize Sales Reports charts if they exist
    const salesTrendEl = document.getElementById('salesTrendChart');
    const salesPaymentEl = document.getElementById('paymentMethodChart');
    const hourlyEl = document.getElementById('hourlyChart');
    
    if (salesTrendEl || salesPaymentEl || hourlyEl) {
        success = initializeSalesReportsCharts() || success;
    }
    
    // Initialize Inventory Reports charts if they exist
    const stockValueEl = document.getElementById('stockValueChart');
    const stockMovementEl = document.getElementById('stockMovementChart');
    
    if (stockValueEl || stockMovementEl) {
        success = initializeInventoryReportsCharts() || success;
    }
    
    return success;
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
            // Check if any charts need initialization
            const needsAdminCharts = document.getElementById('systemOverviewChart') && 
                                   document.getElementById('userTrendChart') &&
                                   (!window.dashboardCharts.systemOverview || !window.dashboardCharts.userTrend);
                                   
            const needsPOSCharts = document.getElementById('dailySalesChart') && 
                                 document.getElementById('paymentMethodChart') &&
                                 (!window.dashboardCharts.dailySales || !window.dashboardCharts.paymentMethod);
                                 
            const needsSalesReportsCharts = (document.getElementById('salesTrendChart') && !window.dashboardCharts.salesTrend) ||
                                          (document.getElementById('hourlyChart') && !window.dashboardCharts.hourlyChart);
                                          
            const needsInventoryReportsCharts = (document.getElementById('stockValueChart') && !window.dashboardCharts.stockValue) ||
                                               (document.getElementById('stockMovementChart') && !window.dashboardCharts.stockMovement);
            
            if (needsAdminCharts || needsPOSCharts || needsSalesReportsCharts || needsInventoryReportsCharts) {
                initializeDashboardCharts();
            }
        }, delay);
    });
}

// Start retry attempts after DOM is ready
document.addEventListener('DOMContentLoaded', tryInitializeWithRetry);
document.addEventListener('livewire:navigated', tryInitializeWithRetry);
document.addEventListener('livewire:updated', tryInitializeWithRetry);

// Initialize POS charts function
function initializePOSCharts() {
    const dailySalesEl = document.getElementById('dailySalesChart');
    const paymentMethodEl = document.getElementById('paymentMethodChart');
    
    if (!dailySalesEl || !paymentMethodEl) {
        return false;
    }
    
    try {
        // Destroy existing charts
        if (window.dashboardCharts.dailySales) {
            window.dashboardCharts.dailySales.destroy();
        }
        if (window.dashboardCharts.paymentMethod) {
            window.dashboardCharts.paymentMethod.destroy();
        }
        
        // Get daily sales data
        const salesDataRaw = dailySalesEl.dataset.salesData;
        const salesData = JSON.parse(salesDataRaw || '{"labels":[],"data":[]}');
        
        // Create Daily Sales Chart
        const ctx1 = dailySalesEl.getContext('2d');
        window.dashboardCharts.dailySales = new Chart(ctx1, {
            type: 'line',
            data: {
                labels: salesData.labels || [],
                datasets: [{
                    label: 'Penjualan Harian (Rp)',
                    data: salesData.data || [],
                    borderColor: '#16A34A',
                    backgroundColor: 'rgba(22, 163, 74, 0.1)',
                    tension: 0.4,
                    fill: true,
                    pointBackgroundColor: '#16A34A',
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
                        ticks: { 
                            color: getTickColor(),
                            callback: function(value) {
                                return 'Rp ' + value.toLocaleString('id-ID');
                            }
                        },
                        grid: { color: getGridColor() }
                    },
                    x: {
                        ticks: { color: getTickColor() },
                        grid: { color: getGridColor() }
                    }
                }
            }
        });
        
        // Get payment method data
        const paymentDataRaw = paymentMethodEl.dataset.paymentData;
        const paymentData = JSON.parse(paymentDataRaw || '{"labels":[],"data":[]}');
        
        // Create Payment Method Chart
        const ctx2 = paymentMethodEl.getContext('2d');
        window.dashboardCharts.paymentMethod = new Chart(ctx2, {
            type: 'doughnut',
            data: {
                labels: paymentData.labels || [],
                datasets: [{
                    data: paymentData.data || [],
                    backgroundColor: ['#2563EB', '#16A34A', '#F59E0B', '#EF4444'],
                    borderColor: ['#1D4ED8', '#15803D', '#D97706', '#DC2626'],
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
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const label = context.label || '';
                                const value = 'Rp ' + context.parsed.toLocaleString('id-ID');
                                return label + ': ' + value;
                            }
                        }
                    }
                }
            }
        });
        
        return true;
        
    } catch (error) {
        console.error('Error initializing POS charts:', error);
        return false;
    }
}

// Initialize Sales Reports charts function
function initializeSalesReportsCharts() {
    const salesTrendEl = document.getElementById('salesTrendChart');
    const salesPaymentEl = document.getElementById('paymentMethodChart');
    const hourlyEl = document.getElementById('hourlyChart');
    
    console.log('initializeSalesReportsCharts called');
    console.log('Elements found:', {
        salesTrend: !!salesTrendEl,
        paymentMethod: !!salesPaymentEl,
        hourly: !!hourlyEl
    });
    
    let success = false;
    
    try {
        // Sales Trend Chart
        if (salesTrendEl) {
            if (window.dashboardCharts.salesTrend) {
                window.dashboardCharts.salesTrend.destroy();
                window.dashboardCharts.salesTrend = null;
            }
            
            const trendDataRaw = salesTrendEl.dataset.trendData;
            console.log('Sales trend data raw:', trendDataRaw);
            const trendData = JSON.parse(trendDataRaw || '{"labels":[],"data":[]}');
            console.log('Sales trend data parsed:', trendData);
            
            const ctx1 = salesTrendEl.getContext('2d');
            window.dashboardCharts.salesTrend = new Chart(ctx1, {
                type: 'line',
                data: {
                    labels: trendData.labels || [],
                    datasets: [{
                        label: 'Penjualan (Rp)',
                        data: trendData.data || [],
                        borderColor: '#16A34A',
                        backgroundColor: 'rgba(22, 163, 74, 0.1)',
                        tension: 0.4,
                        fill: true,
                        pointBackgroundColor: '#16A34A',
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
                            ticks: { 
                                color: getTickColor(),
                                callback: function(value) {
                                    return 'Rp ' + value.toLocaleString('id-ID');
                                }
                            },
                            grid: { color: getGridColor() }
                        },
                        x: {
                            ticks: { color: getTickColor() },
                            grid: { color: getGridColor() }
                        }
                    }
                }
            });
            console.log('Sales trend chart created successfully');
            success = true;
        }
        
        // Payment Method Chart (only for Sales Reports page, not dashboard)
        if (salesPaymentEl && salesPaymentEl.dataset.paymentData && !document.getElementById('dailySalesChart')) {
            if (window.dashboardCharts.salesPaymentMethod) {
                window.dashboardCharts.salesPaymentMethod.destroy();
                window.dashboardCharts.salesPaymentMethod = null;
            }
            
            const paymentDataRaw = salesPaymentEl.dataset.paymentData;
            console.log('Payment method data raw:', paymentDataRaw);
            const paymentData = JSON.parse(paymentDataRaw || '{"labels":[],"data":[]}');
            console.log('Payment method data parsed:', paymentData);
            
            const ctx2 = salesPaymentEl.getContext('2d');
            window.dashboardCharts.salesPaymentMethod = new Chart(ctx2, {
                type: 'doughnut',
                data: {
                    labels: paymentData.labels || [],
                    datasets: [{
                        data: paymentData.data || [],
                        backgroundColor: ['#2563EB', '#16A34A', '#F59E0B', '#EF4444'],
                        borderColor: ['#1D4ED8', '#15803D', '#D97706', '#DC2626'],
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
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const label = context.label || '';
                                    const value = 'Rp ' + context.parsed.toLocaleString('id-ID');
                                    return label + ': ' + value;
                                }
                            }
                        }
                    }
                }
            });
            console.log('Payment method chart created successfully');
            success = true;
        }
        
        // Hourly Chart
        if (hourlyEl) {
            if (window.dashboardCharts.hourlyChart) {
                window.dashboardCharts.hourlyChart.destroy();
                window.dashboardCharts.hourlyChart = null;
            }
            
            const hourlyDataRaw = hourlyEl.dataset.hourlyData;
            console.log('Hourly data raw:', hourlyDataRaw);
            const hourlyData = JSON.parse(hourlyDataRaw || '{"labels":[],"counts":[],"totals":[]}');
            console.log('Hourly data parsed:', hourlyData);
            
            const ctx3 = hourlyEl.getContext('2d');
            window.dashboardCharts.hourlyChart = new Chart(ctx3, {
                type: 'bar',
                data: {
                    labels: hourlyData.labels || [],
                    datasets: [{
                        label: 'Jumlah Transaksi',
                        data: hourlyData.counts || [],
                        backgroundColor: 'rgba(37, 99, 235, 0.8)',
                        borderColor: '#2563EB',
                        borderWidth: 1,
                        yAxisID: 'y'
                    }, {
                        label: 'Total Penjualan (Rp)',
                        data: hourlyData.totals || [],
                        type: 'line',
                        backgroundColor: 'rgba(22, 163, 74, 0.1)',
                        borderColor: '#16A34A',
                        tension: 0.4,
                        fill: false,
                        yAxisID: 'y1'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'top',
                            labels: {
                                color: getThemeColor()
                            }
                        }
                    },
                    scales: {
                        y: {
                            type: 'linear',
                            display: true,
                            position: 'left',
                            ticks: { color: getTickColor() },
                            grid: { color: getGridColor() }
                        },
                        y1: {
                            type: 'linear',
                            display: true,
                            position: 'right',
                            ticks: { 
                                color: getTickColor(),
                                callback: function(value) {
                                    return 'Rp ' + value.toLocaleString('id-ID');
                                }
                            },
                            grid: { drawOnChartArea: false }
                        },
                        x: {
                            ticks: { color: getTickColor() },
                            grid: { color: getGridColor() }
                        }
                    }
                }
            });
            console.log('Hourly chart created successfully');
            success = true;
        }
        
        return success;
        
    } catch (error) {
        console.error('Error initializing Sales Reports charts:', error);
        return false;
    }
}

// Initialize Inventory Reports charts function
function initializeInventoryReportsCharts() {
    const stockValueEl = document.getElementById('stockValueChart');
    const stockMovementEl = document.getElementById('stockMovementChart');
    
    let success = false;
    
    try {
        // Stock Value Chart
        if (stockValueEl) {
            if (window.dashboardCharts.stockValue) {
                window.dashboardCharts.stockValue.destroy();
            }
            
            const stockDataRaw = stockValueEl.dataset.stockData;
            const stockData = JSON.parse(stockDataRaw || '{"labels":[],"values":[]}');
            
            const ctx1 = stockValueEl.getContext('2d');
            window.dashboardCharts.stockValue = new Chart(ctx1, {
                type: 'doughnut',
                data: {
                    labels: stockData.labels || [],
                    datasets: [{
                        data: stockData.values || [],
                        backgroundColor: ['#2563EB', '#16A34A', '#F59E0B', '#EF4444', '#8B5CF6', '#EC4899'],
                        borderColor: ['#1D4ED8', '#15803D', '#D97706', '#DC2626', '#7C3AED', '#DB2777'],
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
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const label = context.label || '';
                                    const value = 'Rp ' + context.parsed.toLocaleString('id-ID');
                                    return label + ': ' + value;
                                }
                            }
                        }
                    }
                }
            });
            success = true;
        }
        
        // Stock Movement Chart
        if (stockMovementEl) {
            if (window.dashboardCharts.stockMovement) {
                window.dashboardCharts.stockMovement.destroy();
            }
            
            const movementDataRaw = stockMovementEl.dataset.movementData;
            const movementData = JSON.parse(movementDataRaw || '{"labels":[],"data":[]}');
            
            const ctx2 = stockMovementEl.getContext('2d');
            window.dashboardCharts.stockMovement = new Chart(ctx2, {
                type: 'bar',
                data: {
                    labels: movementData.labels || [],
                    datasets: [{
                        label: 'Produk Terjual',
                        data: movementData.data || [],
                        backgroundColor: 'rgba(239, 68, 68, 0.8)',
                        borderColor: '#EF4444',
                        borderWidth: 1
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
            success = true;
        }
        
        return success;
        
    } catch (error) {
        console.error('Error initializing Inventory Reports charts:', error);
        return false;
    }
}