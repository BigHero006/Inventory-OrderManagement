// Enhanced Table Functionality for Admin Dashboard
class TableEnhancer {
    constructor(tableSelector) {
        this.table = document.querySelector(tableSelector);
        this.tbody = this.table?.querySelector('tbody');
        this.headers = this.table?.querySelectorAll('th.sortable');
        this.searchInput = document.querySelector('.search-input');
        this.filterSelect = document.querySelector('.filter-select');
        
        this.init();
    }
    
    init() {
        if (!this.table) return;
        
        this.setupSorting();
        this.setupSearch();
        this.setupFiltering();
        this.setupRowHoverEffects();
        this.setupTableAnimations();
    }
    
    // Enhanced sorting functionality
    setupSorting() {
        this.headers?.forEach((header, index) => {
            header.addEventListener('click', () => this.sortTable(index, header));
        });
    }
    
    sortTable(columnIndex, header) {
        const rows = Array.from(this.tbody.querySelectorAll('tr'));
        const isAscending = !header.classList.contains('sort-asc');
        
        // Remove sort classes from all headers
        this.headers.forEach(h => h.classList.remove('sort-asc', 'sort-desc'));
        
        // Add appropriate sort class
        header.classList.add(isAscending ? 'sort-asc' : 'sort-desc');
        
        rows.sort((a, b) => {
            const aCell = a.cells[columnIndex]?.textContent.trim() || '';
            const bCell = b.cells[columnIndex]?.textContent.trim() || '';
            
            // Check if content is numeric
            const aNum = parseFloat(aCell.replace(/[^0-9.-]/g, ''));
            const bNum = parseFloat(bCell.replace(/[^0-9.-]/g, ''));
            
            if (!isNaN(aNum) && !isNaN(bNum)) {
                return isAscending ? aNum - bNum : bNum - aNum;
            }
            
            // String comparison
            return isAscending ? 
                aCell.localeCompare(bCell) : 
                bCell.localeCompare(aCell);
        });
        
        // Animate row reordering
        this.animateRowReorder(rows);
    }
    
    animateRowReorder(sortedRows) {
        const tbody = this.tbody;
        
        // Fade out current rows
        Array.from(tbody.children).forEach(row => {
            row.style.opacity = '0';
            row.style.transform = 'translateX(-20px)';
        });
        
        setTimeout(() => {
            // Clear and append sorted rows
            tbody.innerHTML = '';
            sortedRows.forEach((row, index) => {
                tbody.appendChild(row);
                
                // Animate row in
                setTimeout(() => {
                    row.style.opacity = '1';
                    row.style.transform = 'translateX(0)';
                }, index * 50);
            });
        }, 200);
    }
    
    // Enhanced search functionality
    setupSearch() {
        if (!this.searchInput) return;
        
        this.searchInput.addEventListener('input', (e) => {
            this.filterTable(e.target.value.toLowerCase());
        });
    }
    
    filterTable(searchTerm) {
        const rows = this.tbody.querySelectorAll('tr');
        
        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            const matches = text.includes(searchTerm);
            
            row.style.display = matches ? '' : 'none';
            
            if (matches && searchTerm) {
                this.highlightSearchTerm(row, searchTerm);
            } else {
                this.removeHighlights(row);
            }
        });
        
        this.updateEmptyState();
    }
    
    highlightSearchTerm(row, term) {
        const cells = row.querySelectorAll('td');
        cells.forEach(cell => {
            const originalText = cell.textContent;
            const regex = new RegExp(`(${term})`, 'gi');
            const highlightedText = originalText.replace(regex, '<mark>$1</mark>');
            
            if (highlightedText !== originalText) {
                cell.innerHTML = highlightedText;
            }
        });
    }
    
    removeHighlights(row) {
        const marks = row.querySelectorAll('mark');
        marks.forEach(mark => {
            mark.outerHTML = mark.textContent;
        });
    }
    
    // Filter functionality
    setupFiltering() {
        if (!this.filterSelect) return;
        
        this.filterSelect.addEventListener('change', (e) => {
            this.applyFilter(e.target.value);
        });
    }
    
    applyFilter(filterValue) {
        const rows = this.tbody.querySelectorAll('tr');
        
        rows.forEach(row => {
            if (!filterValue) {
                row.style.display = '';
                return;
            }
            
            const statusCell = row.querySelector('.status-badge, .role-badge');
            const matches = statusCell?.textContent.toLowerCase().includes(filterValue.toLowerCase());
            
            row.style.display = matches ? '' : 'none';
        });
        
        this.updateEmptyState();
    }
    
    // Row hover effects
    setupRowHoverEffects() {
        const rows = this.tbody.querySelectorAll('tr');
        
        rows.forEach(row => {
            row.addEventListener('mouseenter', () => {
                row.style.transform = 'translateX(5px)';
                row.style.boxShadow = '0 8px 25px rgba(102, 126, 234, 0.2)';
            });
            
            row.addEventListener('mouseleave', () => {
                row.style.transform = '';
                row.style.boxShadow = '';
            });
        });
    }
    
    // Table animations
    setupTableAnimations() {
        // Stagger row animations on load
        const rows = this.tbody.querySelectorAll('tr');
        
        rows.forEach((row, index) => {
            row.style.opacity = '0';
            row.style.transform = 'translateY(20px)';
            
            setTimeout(() => {
                row.style.transition = 'all 0.5s ease-out';
                row.style.opacity = '1';
                row.style.transform = 'translateY(0)';
            }, index * 100);
        });
    }
    
    // Empty state management
    updateEmptyState() {
        const visibleRows = Array.from(this.tbody.querySelectorAll('tr')).filter(
            row => row.style.display !== 'none'
        );
        
        let emptyState = this.table.parentElement.querySelector('.table-empty-state');
        
        if (visibleRows.length === 0) {
            if (!emptyState) {
                emptyState = document.createElement('div');
                emptyState.className = 'table-empty-state';
                emptyState.innerHTML = `
                    <i class="fas fa-search"></i>
                    <h3>No results found</h3>
                    <p>Try adjusting your search criteria</p>
                `;
                this.table.parentElement.appendChild(emptyState);
            }
            emptyState.style.display = 'block';
        } else if (emptyState) {
            emptyState.style.display = 'none';
        }
    }
    
    // Add loading state
    showLoading() {
        const loadingDiv = document.createElement('div');
        loadingDiv.className = 'table-loading';
        loadingDiv.innerHTML = `
            <div class="loading-spinner"></div>
            <p>Loading data...</p>
        `;
        
        this.table.parentElement.appendChild(loadingDiv);
        this.table.style.display = 'none';
    }
    
    hideLoading() {
        const loading = this.table.parentElement.querySelector('.table-loading');
        if (loading) {
            loading.remove();
        }
        this.table.style.display = '';
    }
    
    // Refresh table data
    async refreshData() {
        this.showLoading();
        
        // Simulate API call delay
        await new Promise(resolve => setTimeout(resolve, 1000));
        
        this.hideLoading();
        this.setupTableAnimations();
    }
}

// Enhanced Modal functionality
class ModalEnhancer {
    constructor() {
        this.setupModalAnimations();
        this.setupFormValidation();
    }
    
    setupModalAnimations() {
        const modals = document.querySelectorAll('.modal');
        
        modals.forEach(modal => {
            modal.addEventListener('click', (e) => {
                if (e.target === modal) {
                    this.closeModal(modal);
                }
            });
        });
    }
    
    openModal(modalId) {
        const modal = document.getElementById(modalId);
        if (!modal) return;
        
        modal.style.display = 'flex';
        modal.classList.add('active');
        
        setTimeout(() => {
            modal.classList.remove('modal-opening');
            modal.classList.add('modal-open');
        }, 10);
    }
    
    closeModal(modal) {
        if (typeof modal === 'string') {
            modal = document.getElementById(modal);
        }
        
        modal.classList.add('modal-closing');
        modal.classList.remove('active');
        
        setTimeout(() => {
            modal.style.display = 'none';
            modal.classList.remove('modal-open', 'modal-closing');
        }, 300);
    }
    
    setupFormValidation() {
        const forms = document.querySelectorAll('form');
        
        forms.forEach(form => {
            form.addEventListener('submit', (e) => {
                if (!this.validateForm(form)) {
                    e.preventDefault();
                }
            });
            
            // Real-time validation
            const inputs = form.querySelectorAll('input, select, textarea');
            inputs.forEach(input => {
                input.addEventListener('blur', () => this.validateField(input));
                input.addEventListener('input', () => this.clearFieldError(input));
            });
        });
    }
    
    validateForm(form) {
        const requiredFields = form.querySelectorAll('[required]');
        let isValid = true;
        
        requiredFields.forEach(field => {
            if (!this.validateField(field)) {
                isValid = false;
            }
        });
        
        return isValid;
    }
    
    validateField(field) {
        const value = field.value.trim();
        const fieldGroup = field.closest('.form-group');
        
        // Remove existing error
        this.clearFieldError(field);
        
        // Required field validation
        if (field.hasAttribute('required') && !value) {
            this.showFieldError(field, 'This field is required');
            return false;
        }
        
        // Email validation
        if (field.type === 'email' && value && !this.isValidEmail(value)) {
            this.showFieldError(field, 'Please enter a valid email address');
            return false;
        }
        
        // Number validation
        if (field.type === 'number' && value && isNaN(value)) {
            this.showFieldError(field, 'Please enter a valid number');
            return false;
        }
        
        return true;
    }
    
    showFieldError(field, message) {
        const fieldGroup = field.closest('.form-group');
        const errorDiv = document.createElement('div');
        errorDiv.className = 'field-error';
        errorDiv.textContent = message;
        
        field.classList.add('error');
        fieldGroup.appendChild(errorDiv);
    }
    
    clearFieldError(field) {
        const fieldGroup = field.closest('.form-group');
        const errorDiv = fieldGroup.querySelector('.field-error');
        
        field.classList.remove('error');
        if (errorDiv) {
            errorDiv.remove();
        }
    }
    
    isValidEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }
}

// Notification system
class NotificationSystem {
    constructor() {
        this.container = this.createContainer();
    }
    
    createContainer() {
        const container = document.createElement('div');
        container.className = 'notification-container';
        container.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 10000;
            max-width: 400px;
        `;
        document.body.appendChild(container);
        return container;
    }
    
    show(message, type = 'info', duration = 5000) {
        const notification = document.createElement('div');
        notification.className = `notification notification-${type}`;
        notification.style.cssText = `
            padding: 16px 20px;
            margin-bottom: 10px;
            border-radius: 8px;
            color: white;
            font-weight: 500;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            transform: translateX(100%);
            transition: all 0.3s ease-out;
            cursor: pointer;
        `;
        
        // Set background color based on type
        const colors = {
            success: '#38a169',
            error: '#e53e3e',
            warning: '#ed8936',
            info: '#3182ce'
        };
        
        notification.style.background = colors[type] || colors.info;
        notification.textContent = message;
        
        // Add close button
        const closeBtn = document.createElement('span');
        closeBtn.innerHTML = '&times;';
        closeBtn.style.cssText = `
            float: right;
            font-size: 20px;
            font-weight: bold;
            cursor: pointer;
            margin-left: 10px;
        `;
        
        notification.appendChild(closeBtn);
        this.container.appendChild(notification);
        
        // Animate in
        setTimeout(() => {
            notification.style.transform = 'translateX(0)';
        }, 10);
        
        // Auto-remove after duration
        const removeNotification = () => {
            notification.style.transform = 'translateX(100%)';
            setTimeout(() => {
                if (notification.parentElement) {
                    notification.remove();
                }
            }, 300);
        };
        
        closeBtn.addEventListener('click', removeNotification);
        notification.addEventListener('click', removeNotification);
        
        if (duration > 0) {
            setTimeout(removeNotification, duration);
        }
    }
    
    success(message) {
        this.show(message, 'success');
    }
    
    error(message) {
        this.show(message, 'error');
    }
    
    warning(message) {
        this.show(message, 'warning');
    }
    
    info(message) {
        this.show(message, 'info');
    }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    // Initialize global components
    window.notifications = new NotificationSystem();
    window.modalEnhancer = new ModalEnhancer();
    
    // Initialize table enhancers for each page
    const tableSelectors = [
        '.products-table',
        '.suppliers-table', 
        '.orders-table',
        '.users-table',
        '.employees-table',
        '.financial-table'
    ];
    
    tableSelectors.forEach(selector => {
        const table = document.querySelector(selector);
        if (table) {
            new TableEnhancer(selector);
        }
    });
    
    // Add smooth scrolling to anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth'
                });
            }
        });
    });
    
    // Add loading states to forms
    document.querySelectorAll('form').forEach(form => {
        form.addEventListener('submit', function() {
            const submitBtn = form.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
                submitBtn.disabled = true;
            }
        });
    });
});

// Global helper functions
window.openModal = function(modalId) {
    window.modalEnhancer.openModal(modalId);
};

window.closeModal = function(modalId) {
    window.modalEnhancer.closeModal(modalId);
};

window.showNotification = function(message, type = 'info') {
    window.notifications.show(message, type);
};

// Add CSS for enhanced table features
const additionalStyles = `
    <style>
    .field-error {
        color: #e53e3e;
        font-size: 0.8rem;
        margin-top: 4px;
    }
    
    .form-group input.error,
    .form-group select.error,
    .form-group textarea.error {
        border-color: #e53e3e;
        box-shadow: 0 0 0 3px rgba(229, 62, 62, 0.1);
    }
    
    .modal-opening {
        animation: modalFadeIn 0.3s ease-out;
    }
    
    .modal-closing {
        animation: modalFadeOut 0.3s ease-out;
    }
    
    @keyframes modalFadeIn {
        from { opacity: 0; transform: scale(0.7); }
        to { opacity: 1; transform: scale(1); }
    }
    
    @keyframes modalFadeOut {
        from { opacity: 1; transform: scale(1); }
        to { opacity: 0; transform: scale(0.7); }
    }
    
    .table-container {
        position: relative;
    }
    
    th.sortable {
        position: relative;
        user-select: none;
    }
    
    mark {
        background: #fff3cd;
        color: #856404;
        padding: 2px 4px;
        border-radius: 3px;
    }
    </style>
`;

document.head.insertAdjacentHTML('beforeend', additionalStyles);
