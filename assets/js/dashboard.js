// Tridah Budget Tracker - Dashboard JavaScript

// Global state
let currentUser = null;
let dashboardData = {};
let charts = {};

// Initialize dashboard
document.addEventListener('DOMContentLoaded', async () => {
    await checkAuthentication();
    await loadUserProfile();
    await loadCurrentTracker();
    await loadDashboardData();
    initializeCharts();
    loadCategories();
    loadIncomeSources();
    loadRecentTransactions();
    
    // Close tracker menu when clicking outside
    document.addEventListener('click', (e) => {
        const trackerSelector = document.querySelector('.tracker-selector');
        const trackerMenu = document.getElementById('trackerMenu');
        
        if (trackerMenu && !trackerSelector.contains(e.target) && !trackerMenu.contains(e.target)) {
            trackerMenu.style.display = 'none';
            trackerSelector.classList.remove('active');
        }
    });
});

// Check if user is authenticated
async function checkAuthentication() {
    try {
        const response = await fetch(`${API_URL}/api/auth/check.php`);
        const result = await response.json();
        
        if (!result.success || !result.logged_in) {
            window.location.href = 'index.html';
            return;
        }
        
        currentUser = result.user;
    } catch (error) {
        console.error('Auth check error:', error);
        window.location.href = 'index.html';
    }
}

// Load user profile
async function loadUserProfile() {
    if (currentUser) {
        document.getElementById('userName').textContent = currentUser.full_name;
        document.getElementById('userEmail').textContent = currentUser.email;
    }
}

// Handle logout
async function handleLogout() {
    try {
        await fetch(`${API_URL}/api/auth/logout.php`);
        window.location.href = 'index.html';
    } catch (error) {
        console.error('Logout error:', error);
    }
}

// Section navigation
function showSection(sectionName) {
    // Hide all sections
    document.querySelectorAll('.content-section').forEach(section => {
        section.classList.remove('active');
    });
    
    // Remove active from all nav items
    document.querySelectorAll('.nav-item').forEach(item => {
        item.classList.remove('active');
    });
    
    // Show selected section
    const section = document.getElementById(`${sectionName}-section`);
    if (section) {
        section.classList.add('active');
    }
    
    // Activate nav item
    const navItem = document.querySelector(`.nav-item[onclick*="${sectionName}"]`);
    if (navItem) {
        navItem.classList.add('active');
    }
    
    // Load section-specific data
    if (sectionName === 'transactions') {
        loadTransactions();
    } else if (sectionName === 'budgets') {
        loadBudgets();
    } else if (sectionName === 'income-sources') {
        loadIncomeSourcesList();
    } else if (sectionName === 'reports') {
        // Initialize charts if not already done
        if (!charts.monthlyTrendChart) {
            initializeReportsCharts();
        } else {
            // Just reload data
            loadReportData();
        }
    }
}

// Load dashboard data
async function loadDashboardData() {
    try {
        const periodFilter = document.getElementById('periodFilter').value;
        const dates = getDateRange(periodFilter);
        
        const response = await fetch(
            `${API_URL}/api/transactions/get-summary.php?start_date=${dates.start}&end_date=${dates.end}`
        );
        const result = await response.json();
        
        if (result.success) {
            dashboardData = result.summary;
            updateDashboardUI();
        }
    } catch (error) {
        console.error('Error loading dashboard data:', error);
    }
}

// Update dashboard UI
function updateDashboardUI() {
    document.getElementById('totalIncome').textContent = formatCurrency(dashboardData.total_income || 0);
    document.getElementById('totalExpenses').textContent = formatCurrency(dashboardData.total_expenses || 0);
    document.getElementById('netSavings').textContent = formatCurrency(dashboardData.net_savings || 0);
    
    // Update budget status with smart insights
    updateBudgetStatus();
    
    // Update charts
    if (charts.expenseChart) {
        updateExpenseChart();
    }
    if (charts.incomeExpenseChart) {
        updateIncomeExpenseChart();
    }
}

// Update budget status card with insights
async function updateBudgetStatus() {
    try {
        const response = await fetch(`${API_URL}/api/budgets/get.php`);
        const result = await response.json();
        
        
        if (result.success && result.budgets && result.budgets.length > 0) {
            // Calculate overall budget health
            let totalBudget = 0;
            let totalSpent = 0;
            let overBudgetCount = 0;
            
            result.budgets.forEach(budget => {
                const budgetAmount = parseFloat(budget.amount) || 0;
                const budgetSpent = parseFloat(budget.spent) || 0;
                
                totalBudget += budgetAmount;
                totalSpent += budgetSpent;
                
                // Only count as over if actually over (with proper number comparison)
                if (budgetSpent > budgetAmount) {
                    overBudgetCount++;
                }
            });
            
            
            const overallPercentage = totalBudget > 0 ? (totalSpent / totalBudget) * 100 : 0;
            document.getElementById('budgetProgress').style.width = Math.min(overallPercentage, 100) + '%';
            
            // Set status message
            const statusElement = document.getElementById('budgetStatus');
            if (overBudgetCount > 0) {
                statusElement.textContent = `${overBudgetCount} Over Budget`;
                statusElement.style.color = 'var(--danger-color)';
            } else if (overallPercentage > 75) {
                statusElement.textContent = 'Watch Spending';
                statusElement.style.color = 'var(--warning-color)';
            } else if (totalBudget > 0) {
                statusElement.textContent = `${overallPercentage.toFixed(0)}% Used`;
                statusElement.style.color = 'var(--success-color)';
            } else {
                statusElement.textContent = 'On Track';
                statusElement.style.color = 'var(--success-color)';
            }
            
            // Update progress bar color
            const progressBar = document.getElementById('budgetProgress');
            if (overallPercentage > 90) {
                progressBar.style.background = 'linear-gradient(to right, var(--danger-color), #dc2626)';
            } else if (overallPercentage > 75) {
                progressBar.style.background = 'linear-gradient(to right, var(--warning-color), #d97706)';
            } else {
                progressBar.style.background = 'linear-gradient(to right, var(--primary-color), var(--primary-light))';
            }
        } else {
            // No budgets set
            document.getElementById('budgetStatus').textContent = 'No Budgets';
            document.getElementById('budgetStatus').style.color = 'var(--gray-500)';
        }
    } catch (error) {
        console.error('Error updating budget status:', error);
    }
}

// Get date range based on period
function getDateRange(period) {
    const today = new Date();
    let start, end;
    
    switch (period) {
        case 'this-month':
            start = new Date(today.getFullYear(), today.getMonth(), 1);
            end = new Date(today.getFullYear(), today.getMonth() + 1, 0);
            break;
        case 'last-month':
            start = new Date(today.getFullYear(), today.getMonth() - 1, 1);
            end = new Date(today.getFullYear(), today.getMonth(), 0);
            break;
        case 'this-year':
            start = new Date(today.getFullYear(), 0, 1);
            end = new Date(today.getFullYear(), 11, 31);
            break;
        case 'last-year':
            start = new Date(today.getFullYear() - 1, 0, 1);
            end = new Date(today.getFullYear() - 1, 11, 31);
            break;
        default:
            start = new Date(today.getFullYear(), today.getMonth(), 1);
            end = today;
    }
    
    return {
        start: start.toISOString().split('T')[0],
        end: end.toISOString().split('T')[0]
    };
}

// Initialize charts
function initializeCharts() {
    // Expense Breakdown Chart
    const expenseCtx = document.getElementById('expenseChart');
    if (expenseCtx) {
        charts.expenseChart = new Chart(expenseCtx, {
            type: 'doughnut',
            data: {
                labels: [],
                datasets: [{
                    data: [],
                    backgroundColor: [
                        '#6366f1', '#10b981', '#f59e0b', '#ef4444',
                        '#8b5cf6', '#ec4899', '#14b8a6', '#f97316'
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    }
    
    // Income vs Expense Chart
    const incomeExpenseCtx = document.getElementById('incomeExpenseChart');
    if (incomeExpenseCtx) {
        charts.incomeExpenseChart = new Chart(incomeExpenseCtx, {
            type: 'bar',
            data: {
                labels: ['Income', 'Expenses'],
                datasets: [{
                    label: 'Amount',
                    data: [0, 0],
                    backgroundColor: ['#10b981', '#ef4444']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return '$' + value.toLocaleString();
                            }
                        }
                    }
                }
            }
        });
    }
}

// Update expense chart
function updateExpenseChart() {
    if (!charts.expenseChart || !dashboardData.expenses_by_category) return;
    
    const labels = dashboardData.expenses_by_category.map(cat => cat.category_name || 'Uncategorized');
    const data = dashboardData.expenses_by_category.map(cat => parseFloat(cat.total));
    
    charts.expenseChart.data.labels = labels;
    charts.expenseChart.data.datasets[0].data = data;
    charts.expenseChart.update();
}

// Update income vs expense chart
function updateIncomeExpenseChart() {
    if (!charts.incomeExpenseChart) return;
    
    charts.incomeExpenseChart.data.datasets[0].data = [
        dashboardData.total_income || 0,
        dashboardData.total_expenses || 0
    ];
    charts.incomeExpenseChart.update();
}

// Update dashboard (called when period changes)
function updateDashboard() {
    loadDashboardData();
}

// Transaction management
function showAddTransactionModal() {
    showModal('addTransactionModal');
}

function toggleTransactionFields() {
    const type = document.querySelector('input[name="transaction_type"]:checked').value;
    const categoryField = document.getElementById('categoryField');
    const sourceField = document.getElementById('sourceField');
    
    if (type === 'income') {
        categoryField.style.display = 'none';
        sourceField.style.display = 'block';
    } else {
        categoryField.style.display = 'block';
        sourceField.style.display = 'none';
    }
}

async function handleAddTransaction(event) {
    event.preventDefault();
    
    const form = event.target;
    const formData = new FormData(form);
    const data = Object.fromEntries(formData.entries());
    const type = data.transaction_type;
    
    delete data.transaction_type;
    
    try {
        const endpoint = type === 'income' ? 'add-income.php' : 'add-expense.php';
        const response = await fetch(`${API_URL}/api/transactions/${endpoint}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(data)
        });
        
        const result = await response.json();
        
        if (result.success) {
            showNotification('Transaction added successfully!', 'success');
            closeModal('addTransactionModal');
            form.reset();
            
            // Reload all transaction-related data
            await loadDashboardData();
            await loadRecentTransactions();
            
            // If on transactions page, reload the list
            const transactionsList = document.getElementById('transactionsList');
            if (transactionsList && transactionsList.offsetParent !== null) {
                await loadTransactions();
            }
        } else {
            showNotification(result.message || 'Failed to add transaction', 'error');
        }
    } catch (error) {
        console.error('Error adding transaction:', error);
        showNotification('An error occurred. Please try again.', 'error');
    }
}

// Load categories
async function loadCategories() {
    try {
        const response = await fetch(`${API_URL}/api/categories/get.php`);
        const result = await response.json();
        
        if (result.success) {
            const select = document.getElementById('transaction-category');
            select.innerHTML = '<option value="">Select category</option>';
            
            result.categories.forEach(cat => {
                const option = document.createElement('option');
                option.value = cat.category_id;
                option.textContent = cat.category_name;
                select.appendChild(option);
            });
        }
    } catch (error) {
        console.error('Error loading categories:', error);
    }
}

// Load income sources
async function loadIncomeSources() {
    try {
        const response = await fetch(`${API_URL}/api/income-sources/get.php`);
        const result = await response.json();
        
        if (result.success) {
            const select = document.getElementById('transaction-source');
            select.innerHTML = '<option value="">Select source</option>';
            
            result.sources.forEach(source => {
                const option = document.createElement('option');
                option.value = source.source_id;
                option.textContent = source.source_name;
                select.appendChild(option);
            });
        }
    } catch (error) {
        console.error('Error loading income sources:', error);
    }
}

// Load transactions list
async function loadTransactions(type = 'all') {
    try {
        const list = document.getElementById('transactionsList');
        if (!list) return;
        
        // Show loading
        list.innerHTML = '<div class="empty-state"><i class="fas fa-spinner fa-spin"></i><p>Loading...</p></div>';
        
        let allTransactions = [];
        
        // Fetch based on type
        if (type === 'all' || type === 'income') {
            const incomeResponse = await fetch(`${API_URL}/api/transactions/get-income.php`);
            const incomeResult = await incomeResponse.json();
            
            if (incomeResult.success && incomeResult.transactions) {
                allTransactions = allTransactions.concat(
                    incomeResult.transactions.map(t => ({ ...t, type: 'income' }))
                );
            }
        }
        
        if (type === 'all' || type === 'expense') {
            const expenseResponse = await fetch(`${API_URL}/api/transactions/get-expenses.php`);
            const expenseResult = await expenseResponse.json();
            
            if (expenseResult.success && expenseResult.transactions) {
                allTransactions = allTransactions.concat(
                    expenseResult.transactions.map(t => ({ ...t, type: 'expense' }))
                );
            }
        }
        
        // Sort by date (newest first)
        allTransactions.sort((a, b) => new Date(b.transaction_date) - new Date(a.transaction_date));
        
        // Display transactions
        if (allTransactions.length > 0) {
            list.innerHTML = allTransactions.map(transaction => `
                <div class="transaction-item">
                    <div class="transaction-icon ${transaction.type}">
                        <i class="fas fa-${transaction.type === 'income' ? 'arrow-down' : 'arrow-up'}"></i>
                    </div>
                    <div class="transaction-details">
                        <div class="transaction-title">
                            ${transaction.description || (transaction.type === 'income' ? 'Income' : 'Expense')}
                        </div>
                        <div class="transaction-meta">
                            ${formatDate(transaction.transaction_date)}
                            ${transaction.category_name ? `• ${transaction.category_name}` : ''}
                            ${transaction.source_name ? `• ${transaction.source_name}` : ''}
                        </div>
                    </div>
                    <div class="transaction-amount ${transaction.type}">
                        ${transaction.type === 'income' ? '+' : '-'}${formatCurrency(transaction.amount)}
                    </div>
                    <div class="item-actions">
                        <button class="btn-icon" onclick="editTransaction(${transaction.transaction_id}, '${transaction.type}')" title="Edit">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn-icon btn-danger" onclick="deleteTransaction(${transaction.transaction_id}, '${transaction.type}')" title="Delete">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
            `).join('');
        } else {
            list.innerHTML = `
                <div class="empty-state">
                    <i class="fas fa-receipt"></i>
                    <p>No transactions yet</p>
                    <button class="btn btn-primary" onclick="showAddTransactionModal()">Add Your First Transaction</button>
                </div>
            `;
        }
        
        // Also update recent transactions on dashboard
        loadRecentTransactions();
        
    } catch (error) {
        console.error('Error loading transactions:', error);
        const list = document.getElementById('transactionsList');
        if (list) {
            list.innerHTML = '<div class="empty-state"><i class="fas fa-exclamation-circle"></i><p>Error loading transactions</p></div>';
        }
    }
}

// Load recent transactions for dashboard
async function loadRecentTransactions() {
    try {
        const list = document.getElementById('recentTransactionsList');
        if (!list) return;
        
        let allTransactions = [];
        
        // Fetch income and expenses with limit
        const incomeResponse = await fetch(`${API_URL}/api/transactions/get-income.php?limit=5`);
        const incomeResult = await incomeResponse.json();
        
        if (incomeResult.success && incomeResult.transactions) {
            allTransactions = allTransactions.concat(
                incomeResult.transactions.map(t => ({ ...t, type: 'income' }))
            );
        }
        
        const expenseResponse = await fetch(`${API_URL}/api/transactions/get-expenses.php?limit=5`);
        const expenseResult = await expenseResponse.json();
        
        if (expenseResult.success && expenseResult.transactions) {
            allTransactions = allTransactions.concat(
                expenseResult.transactions.map(t => ({ ...t, type: 'expense' }))
            );
        }
        
        // Sort by date and take top 5
        allTransactions.sort((a, b) => new Date(b.transaction_date) - new Date(a.transaction_date));
        allTransactions = allTransactions.slice(0, 5);
        
        if (allTransactions.length > 0) {
            list.innerHTML = allTransactions.map(transaction => `
                <div class="transaction-item">
                    <div class="transaction-icon ${transaction.type}">
                        <i class="fas fa-${transaction.type === 'income' ? 'arrow-down' : 'arrow-up'}"></i>
                    </div>
                    <div class="transaction-details">
                        <div class="transaction-title">
                            ${transaction.description || (transaction.type === 'income' ? 'Income' : 'Expense')}
                        </div>
                        <div class="transaction-meta">
                            ${formatDate(transaction.transaction_date)}
                            ${transaction.category_name ? `• ${transaction.category_name}` : ''}
                            ${transaction.source_name ? `• ${transaction.source_name}` : ''}
                        </div>
                    </div>
                    <div class="transaction-amount ${transaction.type}">
                        ${transaction.type === 'income' ? '+' : '-'}${formatCurrency(transaction.amount)}
                    </div>
                </div>
            `).join('');
        } else {
            list.innerHTML = `
                <div class="empty-state">
                    <i class="fas fa-receipt"></i>
                    <p>No transactions yet</p>
                    <button class="btn btn-primary" onclick="showAddTransactionModal()">Add Transaction</button>
                </div>
            `;
        }
    } catch (error) {
        console.error('Error loading recent transactions:', error);
    }
}

// Delete Transaction
async function deleteTransaction(transactionId, type) {
    if (!confirm(`Are you sure you want to delete this ${type} transaction?`)) {
        return;
    }
    
    try {
        const response = await fetch(`${API_URL}/api/transactions/delete.php`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ 
                transaction_id: transactionId,
                type: type 
            })
        });
        
        const result = await response.json();
        
        if (result.success) {
            showNotification('Transaction deleted successfully!', 'success');
            loadTransactions();
            loadDashboardData();
        } else {
            showNotification(result.message || 'Failed to delete transaction', 'error');
        }
    } catch (error) {
        console.error('Error deleting transaction:', error);
        showNotification('An error occurred', 'error');
    }
}

// Edit Transaction (placeholder for future implementation)
function editTransaction(transactionId, type) {
    showNotification('Edit transaction feature coming soon!', 'info');
    // TODO: Implement edit transaction similar to income source edit
}

// Budget management
function showAddBudgetModal() {
    showModal('addBudgetModal');
}

async function handleAddBudget(event) {
    event.preventDefault();
    
    const form = event.target;
    const formData = new FormData(form);
    const data = Object.fromEntries(formData.entries());
    
    try {
        const response = await fetch(`${API_URL}/api/budgets/create.php`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(data)
        });
        
        const result = await response.json();
        
        if (result.success) {
            showNotification('Budget created successfully!', 'success');
            closeModal('addBudgetModal');
            form.reset();
            loadBudgets();
        } else {
            showNotification(result.message || 'Failed to create budget', 'error');
        }
    } catch (error) {
        console.error('Error creating budget:', error);
        showNotification('An error occurred. Please try again.', 'error');
    }
}

async function loadBudgets() {
    try {
        const response = await fetch(`${API_URL}/api/budgets/get.php`);
        const result = await response.json();
        
        const list = document.getElementById('budgetsList');
        if (!list) return;
        
        if (result.success && result.budgets && result.budgets.length > 0) {
            list.innerHTML = result.budgets.map(budget => {
                const percentage = (budget.spent / budget.amount) * 100;
                const displayPercentage = Math.min(percentage, 100);
                const statusClass = percentage > 100 ? 'danger' : percentage > 90 ? 'danger' : percentage > 75 ? 'warning' : 'success';
                
                // Calculate days in period
                const daysInPeriod = calculateDaysInPeriod(budget.period, budget.start_date);
                const daysPassed = calculateDaysPassed(budget.start_date);
                const daysRemaining = Math.max(0, daysInPeriod - daysPassed);
                
                // Calculate daily spending rate
                const dailySpent = daysPassed > 0 ? budget.spent / daysPassed : 0;
                const projectedTotal = dailySpent * daysInPeriod;
                const onTrack = projectedTotal <= budget.amount;
                
                // Generate status message
                let statusMessage = '';
                if (percentage > 100) {
                    statusMessage = `<span class="budget-alert danger"><i class="fas fa-exclamation-circle"></i> Over budget by ${formatCurrency(budget.spent - budget.amount)}</span>`;
                } else if (percentage > 90) {
                    statusMessage = `<span class="budget-alert warning"><i class="fas fa-exclamation-triangle"></i> ${(100 - percentage).toFixed(0)}% remaining</span>`;
                } else if (!onTrack && daysRemaining > 0) {
                    statusMessage = `<span class="budget-alert warning"><i class="fas fa-chart-line"></i> Pace: ${formatCurrency(projectedTotal)} projected</span>`;
                } else {
                    statusMessage = `<span class="budget-alert success"><i class="fas fa-check-circle"></i> On track</span>`;
                }
                
                return `
                    <div class="budget-item ${statusClass}">
                        <div class="budget-header">
                            <div>
                                <div class="budget-name">${budget.budget_name}</div>
                                <div class="budget-period">
                                    ${budget.period}
                                    ${daysRemaining > 0 ? `• ${daysRemaining} days left` : ''}
                                    ${budget.category_name ? `• ${budget.category_name}` : ''}
                                </div>
                            </div>
                            <div class="item-actions">
                                <button class="btn-icon" onclick="viewBudgetDetails(${budget.budget_id})" title="View Details">
                                    <i class="fas fa-chart-bar"></i>
                                </button>
                                <button class="btn-icon" onclick="editBudget(${budget.budget_id})" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn-icon btn-danger" onclick="deleteBudget(${budget.budget_id}, '${budget.budget_name}')" title="Delete">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                        <div class="budget-stats">
                            <div>
                                <div class="budget-stat-label">Spent</div>
                                <div class="budget-stat-value">${formatCurrency(budget.spent)}</div>
                            </div>
                            <div>
                                <div class="budget-stat-label">Budget</div>
                                <div class="budget-stat-value">${formatCurrency(budget.amount)}</div>
                            </div>
                            <div>
                                <div class="budget-stat-label">Remaining</div>
                                <div class="budget-stat-value ${budget.remaining < 0 ? 'negative' : ''}">${formatCurrency(Math.abs(budget.remaining))}</div>
                            </div>
                        </div>
                        <div class="budget-progress">
                            <div class="progress-bar">
                                <div class="progress-fill ${statusClass}" style="width: ${displayPercentage}%"></div>
                            </div>
                            <div class="budget-progress-text">${percentage.toFixed(1)}% used</div>
                        </div>
                        <div class="budget-status">
                            ${statusMessage}
                        </div>
                        ${percentage > 75 ? `
                        <div class="budget-actions">
                            <button class="btn btn-sm btn-outline" onclick="viewBudgetTransactions(${budget.budget_id}, ${budget.category_id || 'null'})">
                                <i class="fas fa-list"></i> View Expenses
                            </button>
                            ${percentage > 90 ? `
                            <button class="btn btn-sm btn-primary" onclick="adjustBudget(${budget.budget_id})">
                                <i class="fas fa-arrow-up"></i> Increase Budget
                            </button>
                            ` : ''}
                        </div>
                        ` : ''}
                    </div>
                `;
            }).join('');
            
            // Check for budget alerts
            checkBudgetAlerts(result.budgets);
        } else {
            list.innerHTML = '<div class="empty-state"><i class="fas fa-chart-pie"></i><p>No budgets yet</p><button class="btn btn-primary" onclick="showAddBudgetModal()">Create Your First Budget</button></div>';
        }
    } catch (error) {
        console.error('Error loading budgets:', error);
    }
}

// Helper functions for budget calculations
function calculateDaysInPeriod(period, startDate) {
    const start = new Date(startDate);
    let days = 30; // default monthly
    
    switch(period) {
        case 'weekly': days = 7; break;
        case 'monthly': days = 30; break;
        case 'quarterly': days = 90; break;
        case 'annually': days = 365; break;
    }
    
    return days;
}

function calculateDaysPassed(startDate) {
    const start = new Date(startDate);
    const now = new Date();
    const diffTime = Math.abs(now - start);
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
    return diffDays;
}

// Check for budget alerts
function checkBudgetAlerts(budgets) {
    budgets.forEach(budget => {
        const percentage = (budget.spent / budget.amount) * 100;
        
        if (percentage >= 100 && !sessionStorage.getItem(`alert_shown_${budget.budget_id}_100`)) {
            showNotification(`⚠️ Budget Alert: "${budget.budget_name}" is over budget!`, 'error');
            sessionStorage.setItem(`alert_shown_${budget.budget_id}_100`, 'true');
        } else if (percentage >= 90 && percentage < 100 && !sessionStorage.getItem(`alert_shown_${budget.budget_id}_90`)) {
            showNotification(`⚠️ Budget Warning: "${budget.budget_name}" is at ${percentage.toFixed(0)}%`, 'warning');
            sessionStorage.setItem(`alert_shown_${budget.budget_id}_90`, 'true');
        }
    });
}

// View budget details
function viewBudgetDetails(budgetId) {
    showNotification('Budget analytics coming soon! This will show detailed spending breakdown.', 'info');
    // TODO: Show modal with detailed budget analytics
}

// View transactions for a budget
function viewBudgetTransactions(budgetId, categoryId) {
    // Switch to transactions page
    showSection('transactions');
    
    // TODO: Filter transactions by category if budget is category-specific
    showNotification('Showing transactions for this budget period', 'info');
}

// Quick adjust budget amount
async function adjustBudget(budgetId) {
    const newAmount = prompt('Enter new budget amount:');
    if (!newAmount || isNaN(newAmount)) return;
    
    try {
        const response = await fetch(`${API_URL}/api/budgets/update.php`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ 
                budget_id: budgetId,
                amount: parseFloat(newAmount)
            })
        });
        
        const result = await response.json();
        
        if (result.success) {
            showNotification('Budget amount updated!', 'success');
            loadBudgets();
        } else {
            showNotification(result.message || 'Failed to update budget', 'error');
        }
    } catch (error) {
        console.error('Error updating budget:', error);
        showNotification('An error occurred', 'error');
    }
}

// Edit Budget
async function editBudget(budgetId) {
    try {
        const response = await fetch(`${API_URL}/api/budgets/get.php`);
        const result = await response.json();
        
        if (result.success) {
            const budget = result.budgets.find(b => b.budget_id == budgetId);
            if (!budget) {
                showNotification('Budget not found', 'error');
                return;
            }
            
            // Populate form
            document.getElementById('budget-name').value = budget.budget_name;
            document.getElementById('budget-amount').value = budget.amount;
            document.getElementById('budget-period').value = budget.period;
            document.getElementById('budget-start').value = budget.start_date;
            document.getElementById('budget-type').value = budget.budget_type;
            
            // Change form handler
            const form = document.getElementById('addBudgetForm');
            form.onsubmit = async (e) => {
                e.preventDefault();
                await handleUpdateBudget(budgetId);
            };
            
            // Update modal
            document.querySelector('#addBudgetModal .modal-header h2').textContent = 'Edit Budget';
            const submitBtn = form.querySelector('button[type="submit"]');
            submitBtn.innerHTML = '<i class="fas fa-save"></i> Update Budget';
            
            showModal('addBudgetModal');
        }
    } catch (error) {
        console.error('Error loading budget:', error);
        showNotification('Failed to load budget', 'error');
    }
}

// Update Budget
async function handleUpdateBudget(budgetId) {
    const form = document.getElementById('addBudgetForm');
    const formData = new FormData(form);
    const data = Object.fromEntries(formData.entries());
    data.budget_id = budgetId;
    
    try {
        const response = await fetch(`${API_URL}/api/budgets/update.php`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(data)
        });
        
        const result = await response.json();
        
        if (result.success) {
            showNotification('Budget updated successfully!', 'success');
            closeModal('addBudgetModal');
            resetBudgetForm();
            loadBudgets();
        } else {
            showNotification(result.message || 'Failed to update budget', 'error');
        }
    } catch (error) {
        console.error('Error updating budget:', error);
        showNotification('An error occurred', 'error');
    }
}

// Delete Budget
async function deleteBudget(budgetId, budgetName) {
    if (!confirm(`Are you sure you want to delete budget "${budgetName}"?`)) {
        return;
    }
    
    try {
        const response = await fetch(`${API_URL}/api/budgets/delete.php`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ budget_id: budgetId })
        });
        
        const result = await response.json();
        
        if (result.success) {
            showNotification('Budget deleted successfully!', 'success');
            loadBudgets();
        } else {
            showNotification(result.message || 'Failed to delete budget', 'error');
        }
    } catch (error) {
        console.error('Error deleting budget:', error);
        showNotification('An error occurred', 'error');
    }
}

// Reset Budget Form
function resetBudgetForm() {
    const form = document.getElementById('addBudgetForm');
    form.reset();
    form.onsubmit = handleAddBudget;
    
    document.querySelector('#addBudgetModal .modal-header h2').textContent = 'Create Budget';
    const submitBtn = form.querySelector('button[type="submit"]');
    submitBtn.innerHTML = '<i class="fas fa-plus"></i> Create Budget';
}

// Income source management
function showAddIncomeSourceModal() {
    resetIncomeSourceForm(); // Reset form before showing
    showModal('addIncomeSourceModal');
}

function showAddBudgetModal() {
    resetBudgetForm(); // Reset form before showing
    showModal('addBudgetModal');
}

async function handleAddIncomeSource(event) {
    event.preventDefault();
    
    const form = event.target;
    const formData = new FormData(form);
    const data = Object.fromEntries(formData.entries());
    
    try {
        const response = await fetch(`${API_URL}/api/income-sources/create.php`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(data)
        });
        
        const result = await response.json();
        
        if (result.success) {
            showNotification('Income source added successfully!', 'success');
            closeModal('addIncomeSourceModal');
            form.reset();
            loadIncomeSourcesList();
            loadIncomeSources(); // Reload for dropdown
        } else {
            showNotification(result.message || 'Failed to add income source', 'error');
        }
    } catch (error) {
        console.error('Error adding income source:', error);
        showNotification('An error occurred. Please try again.', 'error');
    }
}

async function loadIncomeSourcesList() {
    try {
        const response = await fetch(`${API_URL}/api/income-sources/get.php`);
        const result = await response.json();
        
        const list = document.getElementById('incomeSourcesList');
        if (!list) return;
        
        if (result.success && result.sources.length > 0) {
            list.innerHTML = result.sources.map(source => `
                <div class="income-source-item">
                    <div class="source-header">
                        <div class="source-icon">
                            <i class="fas fa-wallet"></i>
                        </div>
                        <div class="source-info">
                            <h3>${source.source_name}</h3>
                            <div class="source-type">${source.source_type.replace('_', ' ')}</div>
                        </div>
                        <div class="item-actions">
                            <button class="btn-icon" onclick="editIncomeSource(${source.source_id})" title="Edit">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn-icon btn-danger" onclick="deleteIncomeSource(${source.source_id}, '${source.source_name}')" title="Delete">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                    ${source.expected_amount ? `<div class="source-amount">${formatCurrency(source.expected_amount)}/month</div>` : ''}
                    ${source.description ? `<p>${source.description}</p>` : ''}
                </div>
            `).join('');
        } else {
            list.innerHTML = '<div class="empty-state"><i class="fas fa-wallet"></i><p>No income sources yet</p></div>';
        }
    } catch (error) {
        console.error('Error loading income sources:', error);
    }
}

// Edit Income Source
async function editIncomeSource(sourceId) {
    try {
        const response = await fetch(`${API_URL}/api/income-sources/get.php`);
        const result = await response.json();
        
        if (result.success) {
            const source = result.sources.find(s => s.source_id == sourceId);
            if (!source) {
                showNotification('Income source not found', 'error');
                return;
            }
            
            // Populate the form with existing data
            document.getElementById('source-name').value = source.source_name;
            document.getElementById('source-type').value = source.source_type;
            document.getElementById('source-description').value = source.description || '';
            document.getElementById('source-recurring').checked = source.is_recurring == 1;
            document.getElementById('source-expected').value = source.expected_amount || '';
            
            // Change form submit handler to update instead of create
            const form = document.getElementById('addIncomeSourceForm');
            form.onsubmit = async (e) => {
                e.preventDefault();
                await handleUpdateIncomeSource(sourceId);
            };
            
            // Update modal title
            document.querySelector('#addIncomeSourceModal .modal-header h2').textContent = 'Edit Income Source';
            
            // Change button text
            const submitBtn = form.querySelector('button[type="submit"]');
            submitBtn.innerHTML = '<i class="fas fa-save"></i> Update Income Source';
            
            showModal('addIncomeSourceModal');
        }
    } catch (error) {
        console.error('Error loading income source:', error);
        showNotification('Failed to load income source', 'error');
    }
}

// Update Income Source
async function handleUpdateIncomeSource(sourceId) {
    const form = document.getElementById('addIncomeSourceForm');
    const formData = new FormData(form);
    const data = Object.fromEntries(formData.entries());
    data.source_id = sourceId;
    
    try {
        const response = await fetch(`${API_URL}/api/income-sources/update.php`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(data)
        });
        
        const result = await response.json();
        
        if (result.success) {
            showNotification('Income source updated successfully!', 'success');
            closeModal('addIncomeSourceModal');
            resetIncomeSourceForm();
            loadIncomeSourcesList();
            loadIncomeSources(); // Reload dropdown
        } else {
            showNotification(result.message || 'Failed to update income source', 'error');
        }
    } catch (error) {
        console.error('Error updating income source:', error);
        showNotification('An error occurred', 'error');
    }
}

// Delete Income Source
async function deleteIncomeSource(sourceId, sourceName) {
    if (!confirm(`Are you sure you want to delete "${sourceName}"? This action cannot be undone.`)) {
        return;
    }
    
    try {
        const response = await fetch(`${API_URL}/api/income-sources/delete.php`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ source_id: sourceId })
        });
        
        const result = await response.json();
        
        if (result.success) {
            showNotification('Income source deleted successfully!', 'success');
            loadIncomeSourcesList();
            loadIncomeSources(); // Reload dropdown
        } else {
            showNotification(result.message || 'Failed to delete income source', 'error');
        }
    } catch (error) {
        console.error('Error deleting income source:', error);
        showNotification('An error occurred', 'error');
    }
}

// Reset Income Source Form
function resetIncomeSourceForm() {
    const form = document.getElementById('addIncomeSourceForm');
    form.reset();
    form.onsubmit = handleAddIncomeSource;
    
    // Reset modal title and button
    document.querySelector('#addIncomeSourceModal .modal-header h2').textContent = 'Add Income Source';
    const submitBtn = form.querySelector('button[type="submit"]');
    submitBtn.innerHTML = '<i class="fas fa-plus"></i> Add Income Source';
}

// Transaction tabs
function switchTransactionTab(tab) {
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.classList.remove('active');
    });
    event.target.classList.add('active');
    
    // Load transactions based on tab
    loadTransactions(tab);
}

// Goals management
function showAddGoalModal() {
    showNotification('Goals feature coming soon!', 'info');
}

// ===================================
// TRACKER MANAGEMENT
// ===================================

// Load and display all trackers in sidebar
async function loadCurrentTracker() {
    
    try {
        const response = await fetch(`${API_URL}/api/trackers/get.php`);
        
        if (!response.ok) {
            console.error('Tracker API returned:', response.status);
            return;
        }
        
        const result = await response.json();
        
        if (result.success && result.trackers && result.trackers.length > 0) {
            // Find the active tracker (the one marked as default)
            const activeTracker = result.trackers.find(t => t.is_default) || result.trackers[0];
            window.activeTrackerId = activeTracker.tracker_id;
            window.userTrackers = result.trackers;
            
            // Render tracker list in sidebar
            renderTrackerList(result.trackers, activeTracker.tracker_id);
        } else {
            console.warn('No trackers found');
        }
    } catch (error) {
        console.error('Error loading tracker:', error);
    }
}

// Render tracker list in sidebar
function renderTrackerList(trackers, activeId) {
    const container = document.getElementById('trackerListSidebar');
    if (!container) {
        console.error('trackerListSidebar not found!');
        return;
    }
    
    
    container.innerHTML = trackers.map(tracker => {
        const isActive = tracker.tracker_id == activeId;
        return `
            <div class="tracker-item-sidebar ${isActive ? 'active' : ''}" 
                 onclick="switchToTracker(${tracker.tracker_id})">
                <div class="tracker-icon" style="background: ${tracker.color || '#6366f1'}">
                    ${tracker.icon || '💰'}
                </div>
                <div class="tracker-name">${tracker.tracker_name}</div>
                ${isActive ? '<i class="fas fa-check"></i>' : ''}
            </div>
        `;
    }).join('');
}

// Switch to different tracker
async function switchToTracker(trackerId) {
    
    // Don't switch if already active
    if (trackerId == window.activeTrackerId) {
        return;
    }
    
    try {
        const response = await fetch(`${API_URL}/api/trackers/switch.php`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ tracker_id: trackerId })
        });
        
        const result = await response.json();
        
        if (result.success) {
            showNotification('Switched to ' + result.tracker.tracker_name, 'success');
            
            // Update active tracker ID
            window.activeTrackerId = trackerId;
            
            // Re-render tracker list with new active
            if (window.userTrackers) {
                renderTrackerList(window.userTrackers, trackerId);
            }
            
            // Reload all data for new tracker
            await Promise.all([
                loadDashboardData(),
                loadRecentTransactions(),
                loadIncomeSources()
            ]);
            
            // Reload section-specific data if on those pages
            const budgetsList = document.getElementById('budgetsList');
            if (budgetsList && budgetsList.offsetParent !== null) {
                await loadBudgets();
            }
            
            const transactionsList = document.getElementById('transactionsList');
            if (transactionsList && transactionsList.offsetParent !== null) {
                await loadTransactions();
            }
            
            const incomeSourcesList = document.getElementById('incomeSourcesList');
            if (incomeSourcesList && incomeSourcesList.offsetParent !== null) {
                await loadIncomeSourcesList();
            }
            
            const reportsSection = document.getElementById('reports-section');
            if (reportsSection && reportsSection.classList.contains('active')) {
                await loadReportData();
            }
        } else {
            showNotification(result.message || 'Failed to switch tracker', 'error');
        }
    } catch (error) {
        console.error('Error switching tracker:', error);
        showNotification('An error occurred while switching trackers', 'error');
    }
}

// Show create tracker modal
function showCreateTrackerModal(event) {
    if (event) {
        event.preventDefault();
        event.stopPropagation();
    }
    
    showModal('createTrackerModal');
}

// Handle create tracker form submission
async function handleCreateTracker(event) {
    event.preventDefault();
    
    const form = event.target;
    const formData = new FormData(form);
    const data = Object.fromEntries(formData.entries());
    
    try {
        const response = await fetch(`${API_URL}/api/trackers/create.php`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(data)
        });
        
        const result = await response.json();
        
        if (result.success) {
            showNotification('Tracker created successfully!', 'success');
            closeModal('createTrackerModal');
            form.reset();
            
            // Reload trackers
            await loadCurrentTracker();
            
            // Optionally switch to the new tracker
            if (confirm('Switch to the new tracker now?')) {
                await switchToTracker(result.tracker_id);
            }
        } else {
            showNotification(result.message || 'Failed to create tracker', 'error');
        }
    } catch (error) {
        console.error('Error creating tracker:', error);
        showNotification('An error occurred. Please try again.', 'error');
    }
}

// Set tracker icon (helper for icon selection buttons)
function setTrackerIcon(icon) {
    document.getElementById('tracker-icon').value = icon;
}

// Reports
async function initializeReportsCharts() {
    // Monthly Trend Chart
    const monthlyTrendCtx = document.getElementById('monthlyTrendChart');
    if (monthlyTrendCtx && !charts.monthlyTrendChart) {
        charts.monthlyTrendChart = new Chart(monthlyTrendCtx, {
            type: 'line',
            data: {
                labels: [],
                datasets: [
                    {
                        label: 'Income',
                        data: [],
                        borderColor: '#10b981',
                        backgroundColor: 'rgba(16, 185, 129, 0.1)',
                        borderWidth: 3,
                        tension: 0.4,
                        fill: true
                    },
                    {
                        label: 'Expenses',
                        data: [],
                        borderColor: '#ef4444',
                        backgroundColor: 'rgba(239, 68, 68, 0.1)',
                        borderWidth: 3,
                        tension: 0.4,
                        fill: true
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            usePointStyle: true,
                            padding: 15
                        }
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false,
                        callbacks: {
                            label: function(context) {
                                return context.dataset.label + ': ' + formatCurrency(context.parsed.y);
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return '$' + value.toLocaleString();
                            }
                        }
                    }
                },
                interaction: {
                    mode: 'nearest',
                    axis: 'x',
                    intersect: false
                }
            }
        });
    }
    
    // Category Pie Chart
    const categoryPieCtx = document.getElementById('categoryPieChart');
    if (categoryPieCtx && !charts.categoryPieChart) {
        charts.categoryPieChart = new Chart(categoryPieCtx, {
            type: 'doughnut',
            data: {
                labels: [],
                datasets: [{
                    data: [],
                    backgroundColor: [
                        '#6366f1', '#10b981', '#f59e0b', '#ef4444',
                        '#8b5cf6', '#ec4899', '#14b8a6', '#f97316',
                        '#06b6d4', '#84cc16'
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const label = context.label || '';
                                const value = formatCurrency(context.parsed);
                                const percentage = ((context.parsed / context.dataset.data.reduce((a, b) => a + b, 0)) * 100).toFixed(1);
                                return label + ': ' + value + ' (' + percentage + '%)';
                            }
                        }
                    }
                }
            }
        });
    }
    
    // Income Source Chart
    const incomeSourceCtx = document.getElementById('incomeSourceChart');
    if (incomeSourceCtx && !charts.incomeSourceChart) {
        charts.incomeSourceChart = new Chart(incomeSourceCtx, {
            type: 'doughnut',
            data: {
                labels: [],
                datasets: [{
                    data: [],
                    backgroundColor: [
                        '#10b981', '#059669', '#34d399', '#6ee7b7',
                        '#a7f3d0', '#d1fae5'
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const label = context.label || '';
                                const value = formatCurrency(context.parsed);
                                const percentage = ((context.parsed / context.dataset.data.reduce((a, b) => a + b, 0)) * 100).toFixed(1);
                                return label + ': ' + value + ' (' + percentage + '%)';
                            }
                        }
                    }
                }
            }
        });
    }
    
    // Savings Trend Chart
    const savingsTrendCtx = document.getElementById('savingsTrendChart');
    if (savingsTrendCtx && !charts.savingsTrendChart) {
        charts.savingsTrendChart = new Chart(savingsTrendCtx, {
            type: 'bar',
            data: {
                labels: [],
                datasets: [{
                    label: 'Net Savings',
                    data: [],
                    backgroundColor: function(context) {
                        // Check if data exists
                        if (!context.parsed || context.parsed.y === undefined) {
                            return '#10b981'; // Default green
                        }
                        const value = context.parsed.y;
                        return value >= 0 ? '#10b981' : '#ef4444';
                    },
                    borderColor: function(context) {
                        // Check if data exists
                        if (!context.parsed || context.parsed.y === undefined) {
                            return '#059669'; // Default green border
                        }
                        const value = context.parsed.y;
                        return value >= 0 ? '#059669' : '#dc2626';
                    },
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return 'Savings: ' + formatCurrency(context.parsed.y);
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return '$' + value.toLocaleString();
                            }
                        }
                    }
                }
            }
        });
    }
    
    // Load report data
    await loadReportData();
}

// Load all report data
async function loadReportData() {
    const months = document.getElementById('reportPeriod')?.value || 12;
    
    await Promise.all([
        loadMonthlyTrend(months),
        loadCategoryBreakdown(),
        loadIncomeBreakdown()
    ]);
}

// Load monthly trend data
async function loadMonthlyTrend(months = 12) {
    try {
        const response = await fetch(`${API_URL}/api/reports/get-monthly-trend.php?months=${months}`);
        const result = await response.json();
        
        if (result.success && result.data && result.data.length > 0) {
            const labels = result.data.map(d => d.month_name);
            const incomeData = result.data.map(d => parseFloat(d.income) || 0);
            const expenseData = result.data.map(d => parseFloat(d.expenses) || 0);
            const savingsData = result.data.map(d => (parseFloat(d.income) || 0) - (parseFloat(d.expenses) || 0));
            
            // Update monthly trend chart
            if (charts.monthlyTrendChart) {
                charts.monthlyTrendChart.data.labels = labels;
                charts.monthlyTrendChart.data.datasets[0].data = incomeData;
                charts.monthlyTrendChart.data.datasets[1].data = expenseData;
                charts.monthlyTrendChart.update();
            }
            
            // Update savings trend chart with proper data
            if (charts.savingsTrendChart) {
                charts.savingsTrendChart.data.labels = labels;
                charts.savingsTrendChart.data.datasets[0].data = savingsData;
                
                // Update colors based on actual data
                const colors = savingsData.map(val => val >= 0 ? '#10b981' : '#ef4444');
                const borderColors = savingsData.map(val => val >= 0 ? '#059669' : '#dc2626');
                
                charts.savingsTrendChart.data.datasets[0].backgroundColor = colors;
                charts.savingsTrendChart.data.datasets[0].borderColor = borderColors;
                charts.savingsTrendChart.update();
            }
            
            // Calculate and display averages
            const totalIncome = incomeData.reduce((a, b) => a + b, 0);
            const totalExpenses = expenseData.reduce((a, b) => a + b, 0);
            const monthsWithData = incomeData.filter(v => v > 0).length || incomeData.length;
            
            const avgIncome = totalIncome / monthsWithData;
            const avgExpenses = totalExpenses / monthsWithData;
            const avgSavings = avgIncome - avgExpenses;
            const savingsRate = avgIncome > 0 ? (avgSavings / avgIncome) * 100 : 0;
            
            const avgIncomeEl = document.getElementById('avgIncome');
            const avgExpensesEl = document.getElementById('avgExpenses');
            const avgSavingsEl = document.getElementById('avgSavings');
            const savingsRateEl = document.getElementById('savingsRate');
            
            if (avgIncomeEl) avgIncomeEl.textContent = formatCurrency(avgIncome);
            if (avgExpensesEl) avgExpensesEl.textContent = formatCurrency(avgExpenses);
            if (avgSavingsEl) avgSavingsEl.textContent = formatCurrency(avgSavings);
            if (savingsRateEl) savingsRateEl.textContent = savingsRate.toFixed(1) + '%';
            
            // Color code savings rate
            if (savingsRateEl) {
                if (savingsRate >= 20) {
                    savingsRateEl.style.color = 'var(--success-color)';
                } else if (savingsRate >= 10) {
                    savingsRateEl.style.color = 'var(--warning-color)';
                } else if (savingsRate < 0) {
                    savingsRateEl.style.color = 'var(--danger-color)';
                } else {
                    savingsRateEl.style.color = 'var(--gray-900)';
                }
            }
        } else {
        }
    } catch (error) {
        console.error('Error loading monthly trend:', error);
    }
}

// Load category breakdown
async function loadCategoryBreakdown() {
    try {
        const response = await fetch(`${API_URL}/api/reports/get-category-breakdown.php`);
        const result = await response.json();
        
        if (result.success && result.categories && result.categories.length > 0) {
            const labels = result.categories.map(c => c.category_name || 'Uncategorized');
            const data = result.categories.map(c => parseFloat(c.total));
            const colors = result.categories.map(c => c.color || '#6366f1');
            
            // Update chart
            if (charts.categoryPieChart) {
                charts.categoryPieChart.data.labels = labels;
                charts.categoryPieChart.data.datasets[0].data = data;
                charts.categoryPieChart.data.datasets[0].backgroundColor = colors;
                charts.categoryPieChart.update();
            }
            
            // Update breakdown list
            const list = document.getElementById('categoryBreakdownList');
            if (list) {
                list.innerHTML = result.categories.map(cat => `
                    <div class="breakdown-item">
                        <div class="breakdown-color" style="background: ${cat.color || '#6366f1'}"></div>
                        <div class="breakdown-icon">${cat.icon || '📦'}</div>
                        <div class="breakdown-info">
                            <div class="breakdown-name">${cat.category_name || 'Uncategorized'}</div>
                            <div class="breakdown-count">${cat.transaction_count} transactions</div>
                        </div>
                        <div style="text-align: right;">
                            <div class="breakdown-amount">${formatCurrency(cat.total)}</div>
                            <div class="breakdown-percentage">${cat.percentage.toFixed(1)}%</div>
                        </div>
                    </div>
                `).join('');
            }
        } else {
            // No data - clear charts and lists
            if (charts.categoryPieChart) {
                charts.categoryPieChart.data.labels = ['No Data'];
                charts.categoryPieChart.data.datasets[0].data = [1];
                charts.categoryPieChart.data.datasets[0].backgroundColor = ['#e5e7eb'];
                charts.categoryPieChart.update();
            }
            
            const list = document.getElementById('categoryBreakdownList');
            if (list) {
                list.innerHTML = '<div style="padding: 1rem; text-align: center; color: var(--gray-500);">No expense data for this period</div>';
            }
        }
    } catch (error) {
        console.error('Error loading category breakdown:', error);
    }
}

// Load income breakdown
async function loadIncomeBreakdown() {
    try {
        const response = await fetch(`${API_URL}/api/reports/get-income-breakdown.php`);
        const result = await response.json();
        
        if (result.success && result.sources && result.sources.length > 0) {
            const labels = result.sources.map(s => s.source_name || 'Uncategorized');
            const data = result.sources.map(s => parseFloat(s.total));
            
            // Update chart
            if (charts.incomeSourceChart) {
                charts.incomeSourceChart.data.labels = labels;
                charts.incomeSourceChart.data.datasets[0].data = data;
                charts.incomeSourceChart.update();
            }
            
            // Update breakdown list
            const list = document.getElementById('incomeBreakdownList');
            if (list) {
                list.innerHTML = result.sources.map(source => `
                    <div class="breakdown-item">
                        <div class="breakdown-icon">💰</div>
                        <div class="breakdown-info">
                            <div class="breakdown-name">${source.source_name || 'Uncategorized'}</div>
                            <div class="breakdown-count">${source.transaction_count} transactions • ${source.source_type || 'other'}</div>
                        </div>
                        <div style="text-align: right;">
                            <div class="breakdown-amount">${formatCurrency(source.total)}</div>
                            <div class="breakdown-percentage">${source.percentage.toFixed(1)}%</div>
                        </div>
                    </div>
                `).join('');
            }
        } else {
            // No data - clear charts and lists
            if (charts.incomeSourceChart) {
                charts.incomeSourceChart.data.labels = ['No Data'];
                charts.incomeSourceChart.data.datasets[0].data = [1];
                charts.incomeSourceChart.data.datasets[0].backgroundColor = ['#e5e7eb'];
                charts.incomeSourceChart.update();
            }
            
            const list = document.getElementById('incomeBreakdownList');
            if (list) {
                list.innerHTML = '<div style="padding: 1rem; text-align: center; color: var(--gray-500);">No income data for this period</div>';
            }
        }
    } catch (error) {
        console.error('Error loading income breakdown:', error);
    }
}

// Update reports when period changes
async function updateReports() {
    await loadReportData();
}

// Export reports (placeholder)
function exportReports() {
    showNotification('Export feature coming soon! Will export as PDF/Excel.', 'info');
    // TODO: Implement PDF/CSV export
}

