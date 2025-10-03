// Admin Dashboard JavaScript
class AdminDashboard {
    constructor() {
        // Dynamic API URL configuration
        this.baseURL = this.getApiBaseUrl();
        this.token = localStorage.getItem('adminToken');
        this.currentPage = {
            users: 1,
            orders: 1,
            subscriptions: 1
        };
        this.init();
    }

    getApiBaseUrl() {
        // Detect environment and set appropriate API URL
        const hostname = window.location.hostname;
        
        if (hostname === 'localhost' || hostname === '127.0.0.1') {
            // Local development - use direct PHP files
            return 'http://localhost:8000/api';
        } else if (hostname === 'smartapplypro.com' || hostname === 'www.smartapplypro.com') {
            // Production environment - use HTTPS with the same domain
            return `https://${hostname}/api`;
        } else {
            // Fallback for other environments
            return `${window.location.protocol}//${hostname}/api`;
        }
    }

    init() {
        this.setupEventListeners();
        this.checkAuthentication();
    }

    buildApiUrl(endpoint) {
        const hostname = window.location.hostname;
        
        if (hostname === 'localhost' || hostname === '127.0.0.1') {
            // Local development - use direct PHP files
            if (endpoint.startsWith('/admin/')) {
                return `http://localhost:8000/api/admin.php?action=${endpoint.replace('/admin/', '')}`;
            } else {
                return `http://localhost:8000/api${endpoint}.php`;
            }
        } else {
            // Production environment
            if (endpoint.startsWith('/admin/')) {
                return `${this.baseURL}/admin.php?action=${endpoint.replace('/admin/', '')}`;
            } else {
                return `${this.baseURL}${endpoint}`;
            }
        }
    }

    setupEventListeners() {
        // Login form
        document.getElementById('loginForm').addEventListener('submit', (e) => {
            this.handleLogin(e);
        });

        // Logout button
        document.getElementById('logoutBtn').addEventListener('click', () => {
            this.logout();
        });

        // Navigation
        document.querySelectorAll('.nav-link').forEach(link => {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                const section = e.target.dataset.section || e.target.closest('.nav-link').dataset.section;
                this.showSection(section);
            });
        });

        // Add user button
        document.getElementById('addUserBtn').addEventListener('click', () => {
            this.showModal('addUserModal');
        });

        // Add user form
        document.getElementById('addUserForm').addEventListener('submit', (e) => {
            this.handleAddUser(e);
        });

        // Search buttons
        document.getElementById('searchUsersBtn').addEventListener('click', () => {
            this.searchUsers();
        });

        document.getElementById('searchOrdersBtn').addEventListener('click', () => {
            this.searchOrders();
        });

        document.getElementById('searchSubscriptionsBtn').addEventListener('click', () => {
            this.searchSubscriptions();
        });

        // Modal close buttons
        document.querySelectorAll('.close').forEach(closeBtn => {
            closeBtn.addEventListener('click', (e) => {
                const modal = e.target.closest('.modal');
                this.closeModal(modal.id);
            });
        });

        // Close modal when clicking outside
        document.querySelectorAll('.modal').forEach(modal => {
            modal.addEventListener('click', (e) => {
                if (e.target === modal) {
                    this.closeModal(modal.id);
                }
            });
        });
    }

    checkAuthentication() {
        if (this.token) {
            this.showDashboard();
            this.loadDashboardData();
        } else {
            this.showLoginModal();
        }
    }

    async handleLogin(e) {
        e.preventDefault();
        const formData = new FormData(e.target);
        const loginData = {
            username: formData.get('username'),
            password: formData.get('password')
        };

        try {
            this.showLoading();
            const response = await fetch(this.buildApiUrl('/admin/login'), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(loginData)
            });

            const result = await response.json();

            if (response.ok) {
                this.token = result.token;
                localStorage.setItem('adminToken', this.token);
                localStorage.setItem('adminInfo', JSON.stringify(result.admin));
                
                this.hideLoading();
                this.closeModal('loginModal');
                this.showDashboard();
                this.loadDashboardData();
            } else {
                this.hideLoading();
                this.showError('loginError', result.error || 'Login failed');
            }
        } catch (error) {
            this.hideLoading();
            this.showError('loginError', 'Network error. Please try again.');
            console.error('Login error:', error);
        }
    }

    logout() {
        this.token = null;
        localStorage.removeItem('adminToken');
        localStorage.removeItem('adminInfo');
        document.getElementById('dashboard').style.display = 'none';
        this.showLoginModal();
    }

    showLoginModal() {
        document.getElementById('loginModal').style.display = 'block';
        document.getElementById('loginError').style.display = 'none';
    }

    showDashboard() {
        document.getElementById('loginModal').style.display = 'none';
        document.getElementById('dashboard').style.display = 'block';
        
        // Set admin info
        const adminInfo = JSON.parse(localStorage.getItem('adminInfo') || '{}');
        document.getElementById('adminInfo').textContent = `Welcome, ${adminInfo.fullName || adminInfo.username}`;
    }

    showSection(sectionName) {
        // Update navigation
        document.querySelectorAll('.nav-link').forEach(link => {
            link.classList.remove('active');
        });
        document.querySelector(`[data-section="${sectionName}"]`).classList.add('active');

        // Show section
        document.querySelectorAll('.content-section').forEach(section => {
            section.classList.remove('active');
        });
        document.getElementById(`${sectionName}Section`).classList.add('active');

        // Load section data
        switch (sectionName) {
            case 'dashboard':
                this.loadDashboardStats();
                break;
            case 'users':
                this.loadUsers();
                break;
            case 'orders':
                this.loadOrders();
                break;
            case 'subscriptions':
                this.loadSubscriptions();
                break;
        }
    }

    async loadDashboardData() {
        this.loadDashboardStats();
    }

    async loadDashboardStats() {
        try {
            const response = await fetch(this.buildApiUrl('/admin/dashboard'), {
                headers: {
                    'Authorization': `Bearer ${this.token}`
                }
            });

            if (response.ok) {
                const stats = await response.json();
                this.updateDashboardStats(stats);
            } else {
                console.error('Failed to load dashboard stats');
            }
        } catch (error) {
            console.error('Dashboard stats error:', error);
        }
    }

    updateDashboardStats(stats) {
        document.getElementById('totalOrders').textContent = stats.totalOrders || 0;
        document.getElementById('pendingOrders').textContent = stats.pendingOrders || 0;
        document.getElementById('totalUsers').textContent = stats.totalUsers || 0;
        document.getElementById('activeLicenses').textContent = stats.activeLicenses || 0;

        // Update charts (simplified for now)
        this.updateOrderStatusChart(stats.ordersByStatus || []);
        this.updateRevenueChart(stats.revenueByPaymentMethod || []);
    }

    updateOrderStatusChart(data) {
        const container = document.getElementById('orderStatusChart');
        if (data.length === 0) {
            container.innerHTML = '<div class="loading">No data available</div>';
            return;
        }

        let chartHTML = '<div class="simple-chart">';
        data.forEach(item => {
            const percentage = data.reduce((sum, d) => sum + d.count, 0) > 0 
                ? (item.count / data.reduce((sum, d) => sum + d.count, 0)) * 100 
                : 0;
            chartHTML += `
                <div class="chart-item">
                    <span class="chart-label">${item.order_status}: ${item.count}</span>
                    <div class="chart-bar">
                        <div class="chart-fill" style="width: ${percentage}%"></div>
                    </div>
                </div>
            `;
        });
        chartHTML += '</div>';

        container.innerHTML = chartHTML;
    }

    updateRevenueChart(data) {
        const container = document.getElementById('revenueChart');
        if (data.length === 0) {
            container.innerHTML = '<div class="loading">No data available</div>';
            return;
        }

        let chartHTML = '<div class="simple-chart">';
        const maxRevenue = Math.max(...data.map(d => d.revenue || 0));
        
        data.forEach(item => {
            const percentage = maxRevenue > 0 ? (item.revenue / maxRevenue) * 100 : 0;
            chartHTML += `
                <div class="chart-item">
                    <span class="chart-label">${item.payment_method}: $${item.revenue || 0}</span>
                    <div class="chart-bar">
                        <div class="chart-fill" style="width: ${percentage}%"></div>
                    </div>
                </div>
            `;
        });
        chartHTML += '</div>';

        container.innerHTML = chartHTML;
    }

    async loadUsers(page = 1, search = '') {
        try {
            const params = new URLSearchParams({
                page: page.toString(),
                limit: '20'
            });

            if (search) {
                params.append('search', search);
            }

            const response = await fetch(this.buildApiUrl('/admin/users') + '?' + params, {
                headers: {
                    'Authorization': `Bearer ${this.token}`
                }
            });

            if (response.ok) {
                const result = await response.json();
                this.renderUsers(result.users);
                this.renderPagination('usersPagination', result.pagination, 'users');
                this.currentPage.users = page;
            } else {
                console.error('Failed to load users');
            }
        } catch (error) {
            console.error('Load users error:', error);
        }
    }

    renderUsers(users) {
        const tbody = document.querySelector('#usersTable tbody');
        
        if (users.length === 0) {
            tbody.innerHTML = '<tr><td colspan="7" class="loading">No users found</td></tr>';
            return;
        }

        tbody.innerHTML = users.map(user => `
            <tr>
                <td>${user.id}</td>
                <td>${user.username}</td>
                <td>${user.email}</td>
                <td>${user.full_name}</td>
                <td><span class="status-badge status-${user.status}">${user.status}</span></td>
                <td>${new Date(user.created_at).toLocaleDateString()}</td>
                <td>
                    <button class="btn btn-warning btn-sm" onclick="adminDashboard.editUser(${user.id})">
                        <i class="fas fa-edit"></i> Edit
                    </button>
                    <button class="btn btn-danger btn-sm" onclick="adminDashboard.deleteUser(${user.id})" 
                            ${user.status === 'active' ? '' : 'disabled'}>
                        <i class="fas fa-trash"></i> Delete
                    </button>
                </td>
            </tr>
        `).join('');
    }

    async loadOrders(page = 1, search = '', status = '', paymentMethod = '') {
        try {
            const params = new URLSearchParams({
                page: page.toString(),
                limit: '20'
            });

            if (search) params.append('search', search);
            if (status) params.append('status', status);
            if (paymentMethod) params.append('paymentMethod', paymentMethod);

            const response = await fetch(this.buildApiUrl('/admin/orders') + '?' + params, {
                headers: {
                    'Authorization': `Bearer ${this.token}`
                }
            });

            if (response.ok) {
                const result = await response.json();
                this.renderOrders(result.orders);
                this.renderPagination('ordersPagination', result.pagination, 'orders');
                this.currentPage.orders = page;
            } else {
                console.error('Failed to load orders');
            }
        } catch (error) {
            console.error('Load orders error:', error);
        }
    }

    renderOrders(orders) {
        const tbody = document.querySelector('#ordersTable tbody');
        
        if (orders.length === 0) {
            tbody.innerHTML = '<tr><td colspan="8" class="loading">No orders found</td></tr>';
            return;
        }

        tbody.innerHTML = orders.map(order => `
            <tr>
                <td>${order.order_number}</td>
                <td>
                    <div>${order.full_name}</div>
                    <div style="font-size: 0.8em; color: #718096;">${order.email}</div>
                </td>
                <td>${order.plan_type}</td>
                <td>$${order.amount} ${order.currency}</td>
                <td>
                    <span class="status-badge status-${order.payment_method}">
                        ${order.payment_method}
                    </span>
                </td>
                <td><span class="status-badge status-${order.order_status}">${order.order_status}</span></td>
                <td>${new Date(order.created_at).toLocaleDateString()}</td>
                <td>
                    <button class="btn btn-secondary btn-sm" onclick="adminDashboard.viewOrderDetails('${order.order_number}')">
                        <i class="fas fa-eye"></i> View
                    </button>
                    ${order.payment_method === 'bkash' && order.order_status === 'pending' ? `
                        <button class="btn btn-success btn-sm" onclick="adminDashboard.approveOrder(${order.id})">
                            <i class="fas fa-check"></i> Approve
                        </button>
                        <button class="btn btn-danger btn-sm" onclick="adminDashboard.rejectOrder(${order.id})">
                            <i class="fas fa-times"></i> Reject
                        </button>
                    ` : ''}
                </td>
            </tr>
        `).join('');
    }

    async loadSubscriptions(page = 1, search = '', status = '') {
        try {
            const params = new URLSearchParams({
                page: page.toString(),
                limit: '20'
            });

            if (search) params.append('search', search);
            if (status) params.append('status', status);

            const response = await fetch(`${this.baseURL}/admin/subscriptions?${params}`, {
                headers: {
                    'Authorization': `Bearer ${this.token}`
                }
            });

            if (response.ok) {
                const result = await response.json();
                this.renderSubscriptions(result.subscriptions);
                this.renderPagination('subscriptionsPagination', result.pagination, 'subscriptions');
                this.currentPage.subscriptions = page;
            } else {
                console.error('Failed to load subscriptions');
            }
        } catch (error) {
            console.error('Load subscriptions error:', error);
        }
    }

    renderSubscriptions(subscriptions) {
        const tbody = document.querySelector('#subscriptionsTable tbody');
        
        if (subscriptions.length === 0) {
            tbody.innerHTML = '<tr><td colspan="8" class="loading">No subscriptions found</td></tr>';
            return;
        }

        tbody.innerHTML = subscriptions.map(sub => `
            <tr>
                <td style="font-family: monospace; font-size: 0.8em;">${sub.license_key}</td>
                <td>${sub.full_name}</td>
                <td>${sub.email}</td>
                <td>${sub.plan_type}</td>
                <td><span class="status-badge status-${sub.status}">${sub.status}</span></td>
                <td>${sub.expiry_date ? new Date(sub.expiry_date).toLocaleDateString() : 'Lifetime'}</td>
                <td>$${sub.amount_paid} ${sub.currency}</td>
                <td>
                    <button class="btn btn-secondary btn-sm" onclick="adminDashboard.viewSubscriptionDetails('${sub.license_key}')">
                        <i class="fas fa-eye"></i> View
                    </button>
                </td>
            </tr>
        `).join('');
    }

    renderPagination(containerId, pagination, type) {
        const container = document.getElementById(containerId);
        const { page, totalPages, total } = pagination;

        if (totalPages <= 1) {
            container.innerHTML = '';
            return;
        }

        let paginationHTML = `
            <button ${page <= 1 ? 'disabled' : ''} onclick="adminDashboard.changePage('${type}', ${page - 1})">
                <i class="fas fa-chevron-left"></i>
            </button>
        `;

        // Show page numbers
        const startPage = Math.max(1, page - 2);
        const endPage = Math.min(totalPages, page + 2);

        if (startPage > 1) {
            paginationHTML += `<button onclick="adminDashboard.changePage('${type}', 1)">1</button>`;
            if (startPage > 2) {
                paginationHTML += '<span>...</span>';
            }
        }

        for (let i = startPage; i <= endPage; i++) {
            paginationHTML += `
                <button ${i === page ? 'class="active"' : ''} onclick="adminDashboard.changePage('${type}', ${i})">
                    ${i}
                </button>
            `;
        }

        if (endPage < totalPages) {
            if (endPage < totalPages - 1) {
                paginationHTML += '<span>...</span>';
            }
            paginationHTML += `<button onclick="adminDashboard.changePage('${type}', ${totalPages})">${totalPages}</button>`;
        }

        paginationHTML += `
            <button ${page >= totalPages ? 'disabled' : ''} onclick="adminDashboard.changePage('${type}', ${page + 1})">
                <i class="fas fa-chevron-right"></i>
            </button>
            <div class="page-info">
                Showing ${((page - 1) * 20) + 1}-${Math.min(page * 20, total)} of ${total}
            </div>
        `;

        container.innerHTML = paginationHTML;
    }

    changePage(type, page) {
        switch (type) {
            case 'users':
                this.loadUsers(page, document.getElementById('userSearch').value);
                break;
            case 'orders':
                this.loadOrders(
                    page,
                    document.getElementById('orderSearch').value,
                    document.getElementById('orderStatusFilter').value,
                    document.getElementById('paymentMethodFilter').value
                );
                break;
            case 'subscriptions':
                this.loadSubscriptions(
                    page,
                    document.getElementById('subscriptionSearch').value,
                    document.getElementById('subscriptionStatusFilter').value
                );
                break;
        }
    }

    searchUsers() {
        const search = document.getElementById('userSearch').value;
        this.loadUsers(1, search);
    }

    searchOrders() {
        const search = document.getElementById('orderSearch').value;
        const status = document.getElementById('orderStatusFilter').value;
        const paymentMethod = document.getElementById('paymentMethodFilter').value;
        this.loadOrders(1, search, status, paymentMethod);
    }

    searchSubscriptions() {
        const search = document.getElementById('subscriptionSearch').value;
        const status = document.getElementById('subscriptionStatusFilter').value;
        this.loadSubscriptions(1, search, status);
    }

    async handleAddUser(e) {
        e.preventDefault();
        const formData = new FormData(e.target);
        const userData = {
            username: formData.get('username'),
            email: formData.get('email'),
            fullName: formData.get('fullName'),
            password: formData.get('password')
        };

        try {
            this.showLoading();
            const response = await fetch(`${this.baseURL}/admin/users`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${this.token}`
                },
                body: JSON.stringify(userData)
            });

            const result = await response.json();

            if (response.ok) {
                this.hideLoading();
                this.closeModal('addUserModal');
                this.showSuccess('User created successfully');
                this.loadUsers();
                e.target.reset();
            } else {
                this.hideLoading();
                this.showError('addUserError', result.error || 'Failed to create user');
            }
        } catch (error) {
            this.hideLoading();
            this.showError('addUserError', 'Network error. Please try again.');
            console.error('Add user error:', error);
        }
    }

    async approveOrder(orderId) {
        if (!confirm('Are you sure you want to approve this order?')) return;

        try {
            this.showLoading();
            const response = await fetch(`${this.baseURL}/admin/orders/${orderId}/approve`, {
                method: 'POST',
                headers: {
                    'Authorization': `Bearer ${this.token}`
                }
            });

            const result = await response.json();

            if (response.ok) {
                this.hideLoading();
                this.showSuccess('Order approved successfully');
                this.loadOrders();
            } else {
                this.hideLoading();
                this.showError('orderError', result.error || 'Failed to approve order');
            }
        } catch (error) {
            this.hideLoading();
            this.showError('orderError', 'Network error. Please try again.');
            console.error('Approve order error:', error);
        }
    }

    async rejectOrder(orderId) {
        const reason = prompt('Please provide a reason for rejection:');
        if (!reason) return;

        try {
            this.showLoading();
            const response = await fetch(`${this.baseURL}/admin/orders/${orderId}/reject`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${this.token}`
                },
                body: JSON.stringify({ reason })
            });

            const result = await response.json();

            if (response.ok) {
                this.hideLoading();
                this.showSuccess('Order rejected successfully');
                this.loadOrders();
            } else {
                this.hideLoading();
                this.showError('orderError', result.error || 'Failed to reject order');
            }
        } catch (error) {
            this.hideLoading();
            this.showError('orderError', 'Network error. Please try again.');
            console.error('Reject order error:', error);
        }
    }

    // Utility methods
    showModal(modalId) {
        document.getElementById(modalId).style.display = 'block';
    }

    closeModal(modalId) {
        document.getElementById(modalId).style.display = 'none';
    }

    showLoading() {
        document.getElementById('loadingOverlay').style.display = 'flex';
    }

    hideLoading() {
        document.getElementById('loadingOverlay').style.display = 'none';
    }

    showError(elementId, message) {
        const element = document.getElementById(elementId);
        if (element) {
            element.textContent = message;
            element.style.display = 'block';
        } else {
            alert('Error: ' + message);
        }
    }

    showSuccess(message) {
        // Simple success notification
        const notification = document.createElement('div');
        notification.className = 'success-message';
        notification.textContent = message;
        notification.style.position = 'fixed';
        notification.style.top = '20px';
        notification.style.right = '20px';
        notification.style.zIndex = '10000';
        
        document.body.appendChild(notification);
        
        setTimeout(() => {
            notification.remove();
        }, 3000);
    }

    // Placeholder methods for future implementation
    editUser(userId) {
        console.log('Edit user:', userId);
        // TODO: Implement edit user functionality
    }

    deleteUser(userId) {
        if (confirm('Are you sure you want to delete this user?')) {
            console.log('Delete user:', userId);
            // TODO: Implement delete user functionality
        }
    }

    viewOrderDetails(orderNumber) {
        console.log('View order details:', orderNumber);
        // TODO: Implement order details view
    }

    viewSubscriptionDetails(licenseKey) {
        console.log('View subscription details:', licenseKey);
        // TODO: Implement subscription details view
    }
}

// Add simple chart styles
const chartStyles = `
<style>
.simple-chart {
    padding: 10px 0;
}

.chart-item {
    margin-bottom: 12px;
}

.chart-label {
    display: block;
    font-size: 0.875rem;
    color: #4a5568;
    margin-bottom: 4px;
}

.chart-bar {
    height: 20px;
    background: #e2e8f0;
    border-radius: 10px;
    overflow: hidden;
}

.chart-fill {
    height: 100%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 10px;
    transition: width 0.3s ease;
}
</style>
`;

document.head.insertAdjacentHTML('beforeend', chartStyles);

// Global functions for button onclick handlers
window.closeModal = function(modalId) {
    document.getElementById(modalId).style.display = 'none';
};

// Initialize dashboard when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    window.adminDashboard = new AdminDashboard();
});