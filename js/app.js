/**
 * Med-Quick - Secure Pharmacy POS
 * Core Application Logic (Vanilla JS & AJAX)
 *
 * Version: 1.2.0
 * - Centralized module initialization logic.
 * - Added cache-busting for API calls.
 */

/**
 * Global utility function to display messages.
 * @param {string} containerId The ID of the HTML element where the message should be displayed.
 * @param {string} message The message content.
 * @param {boolean} isSuccess True for a success message, false for an error message.
 */
const showMessage = (containerId, message, isSuccess) => {
    const container = document.getElementById(containerId);
    if (container) {
        container.innerHTML = `<div class="form-message ${isSuccess ? 'success' : 'error'}">${message}</div>`;
        setTimeout(() => { container.innerHTML = ''; }, 4000);
    }
};

// --- Purchases Module Logic (Globalized for reusability) ---
const loadPurchases = async () => {
    const purchasesTableBody = document.getElementById('purchases-table-body');
    if (!purchasesTableBody) return;

    try {
        const response = await fetch('api/get_purchases.php?t=' + new Date().getTime());
        const purchases = await response.json();
        if (purchases.error) throw new Error(purchases.error);

        purchasesTableBody.innerHTML = ''; // Clear loading message
        if (purchases.length === 0) {
            purchasesTableBody.innerHTML = '<tr><td colspan="6" style="text-align: center;">No purchase records found.</td></tr>';
            return;
        }

        purchases.forEach(p => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>P-${p.id.toString().padStart(3, '0')}</td>
                <td>${p.supplier_name}</td>
                <td>${p.purchase_date}</td>
                <td>${parseFloat(p.total_amount).toFixed(2)}</td>
                <td>${p.user_name}</td>
                <td><a href="#" class="view-details-btn" data-purchase-id="${p.id}">View Details</a></td>
            `;
            purchasesTableBody.appendChild(row);
        });

    } catch (error) {
        console.error('Error fetching purchases:', error);
        purchasesTableBody.innerHTML = '<tr><td colspan="6" style="color: red; text-align: center;">Error loading purchase data.</td></tr>';
    }
};

document.addEventListener('DOMContentLoaded', () => {

    // --- THEME TOGGLE LOGIC ---
    const themeToggleBtn = document.getElementById('theme-toggle');
    const rootElement = document.documentElement;
    const themeIcon = themeToggleBtn ? themeToggleBtn.querySelector('i') : null;

    // Load saved theme
    const savedTheme = localStorage.getItem('theme') || 'dark';
    rootElement.setAttribute('data-theme', savedTheme);
    if (themeIcon) {
        themeIcon.className = savedTheme === 'dark' ? 'fas fa-moon' : 'fas fa-sun';
    }

    if (themeToggleBtn) {
        themeToggleBtn.addEventListener('click', () => {
            const currentTheme = rootElement.getAttribute('data-theme');
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
            
            rootElement.setAttribute('data-theme', newTheme);
            localStorage.setItem('theme', newTheme);
            
            if (themeIcon) {
                themeIcon.className = newTheme === 'dark' ? 'fas fa-moon' : 'fas fa-sun';
            }
        });
    }

    // --- STATE MANAGEMENT ---
    const appState = {
        cart: [],
        inactivityTimer: null,
        timeOut: 120000 // 2 minutes
    };

    // --- SUPPORT MODAL LOGIC ---
    const supportLink = document.getElementById('support-link');
    const supportModal = document.getElementById('support-modal');
    const closeSupportModal = document.getElementById('close-support-modal');

    if (supportLink && supportModal && closeSupportModal) {
        supportLink.addEventListener('click', (e) => {
            e.preventDefault();
            supportModal.style.display = 'flex';
        });

        closeSupportModal.addEventListener('click', () => {
            supportModal.style.display = 'none';
        });

        supportModal.addEventListener('click', (e) => {
            if (e.target === supportModal) {
                supportModal.style.display = 'none';
            }
        });

        // Fix for "Send Email" button opening blank tabs
        const emailBtn = supportModal.querySelector('.btn-email, .btn-support[href^="mailto:"]');
        if (emailBtn) {
            emailBtn.addEventListener('click', function(e) {
                e.preventDefault();
                window.location.href = this.href;
            });
        }
    }

    // --- CORE MODULE LOADER & INITIALIZATION ---
    const loadModule = async (moduleName) => {
        try {
            mainContent.innerHTML = '<h2>Loading...</h2>';
            const response = await fetch(`modules/${moduleName}.php`);
            if (!response.ok) throw new Error(`Could not load ${moduleName}.php.`);
            
            mainContent.innerHTML = await response.text();

            // After loading, run the specific initializer for the module
            switch (moduleName) {
                case 'pos':
                    initPosModule();
                    break;
                case 'dashboard':
                    initDashboardModule();
                    break;
                case 'sales':
                    initSalesModule();
                    break;
                case 'stock':
                    initStockModule();
                    break;
                case 'purchases':
                    initPurchasesModule();
                    break;
                case 'categories':
                    initCategoriesModule();
                    break;
                case 'medicines':
                    initMedicinesModule();
                    break;
                case 'manufacturers':
                    initManufacturersModule();
                    break;
                case 'suppliers':
                    initSuppliersModule();
                    break;
                case 'users':
                    initUsersModule();
                    break;
                case 'reports':
                    initReportsModule();
                    break;
                case 'bank':
                    initBankModule();
                    break;
                case 'out_of_stock': // New case
                    initOutOfStockModule();
                    break;
                // Add cases for other dynamic modules here if needed
            }
        } catch (error) {
            console.error('Module Loader Error:', error);
            mainContent.innerHTML = `<p style="color:red;">Error loading content.</p>`;
        }
    };

    const initBankModule = () => {
        const form = document.getElementById('bank-transaction-form');
        const ledgerBody = document.getElementById('bank-ledger-body');

        if (!form || !ledgerBody) {
            console.error("Bank module elements not found, cannot initialize.");
            return;
        }

        const loadBankTransactions = async () => {
            ledgerBody.innerHTML = '<tr><td colspan="5" style="text-align: center;">Loading...</td></tr>';
            try {
                const response = await fetch(`api/get_bank_transactions.php?t=${new Date().getTime()}`);
                const transactions = await response.json();
                
                if (transactions.error) throw new Error(transactions.error);

                ledgerBody.innerHTML = ''; // Clear the table body
                if (transactions.length === 0) {
                    ledgerBody.innerHTML = '<tr><td colspan="5" style="text-align: center;">No transactions found.</td></tr>';
                    return;
                }

                transactions.forEach(t => {
                    const row = document.createElement('tr');
                    const debitAmount = t.type === 'debit' ? parseFloat(t.amount).toFixed(2) : '-';
                    const creditAmount = t.type === 'credit' ? parseFloat(t.amount).toFixed(2) : '-';

                    row.innerHTML = `
                        <td>${new Date(t.transaction_time).toLocaleString()}</td>
                        <td>${t.description}</td>
                        <td>${debitAmount}</td>
                        <td>${creditAmount}</td>
                        <td>${parseFloat(t.balance).toFixed(2)}</td>
                    `;
                    ledgerBody.appendChild(row);
                });

            } catch (error) {
                console.error("Error loading bank transactions:", error);
                ledgerBody.innerHTML = `<tr><td colspan="5" style="text-align: center; color: red;">Error loading transactions.</td></tr>`;
            }
        };

        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            const submitBtn = form.querySelector('button[type="submit"]');
            submitBtn.disabled = true;
            submitBtn.textContent = 'Adding...';

            const formData = new FormData(form);
            // Quick validation
            if (!formData.get('t_date') || !formData.get('t_amount') || !formData.get('t_desc')) {
                 alert('Please fill out all fields.');
                 submitBtn.disabled = false;
                 submitBtn.textContent = 'Add Transaction';
                 return;
            }
            
            const data = Object.fromEntries(formData.entries());

            try {
                const response = await fetch('api/add_bank_transaction.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(data)
                });
                const result = await response.json();

                if (result.success) {
                    form.reset(); // Clear the form
                    loadBankTransactions(); // Reload the ledger
                } else {
                    alert(result.error || 'An error occurred while adding the transaction.');
                }
            } catch (error) {
                 console.error("Add transaction error:", error);
                 alert("A network error occurred. Could not add transaction.");
            } finally {
                submitBtn.disabled = false;
                submitBtn.textContent = 'Add Transaction';
            }
        });

        // Initial load of the transaction ledger
        loadBankTransactions();
    };

    const initReportsModule = () => {
        const salesForm = document.getElementById('daily-sales-form');
        const inventoryForm = document.getElementById('inventory-report-form');
        const reportDisplayArea = document.getElementById('report-display-area');
        const inventoryReportTypeSelect = document.querySelector('#inventory-report-form select[name="report_type"]');
        const expiryThresholdGroup = document.getElementById('expiry-threshold-group');

        if (!salesForm || !inventoryForm || !reportDisplayArea) return;

        const renderReport = (data) => {
            reportDisplayArea.innerHTML = ''; // Clear previous report
            if (!data || !data.rows) {
                reportDisplayArea.innerHTML = `<p style="color: red;">Error: Invalid report data received.</p>`;
                return;
            }

            let tableHtml = `
                <div class="table-container">
                    <h3>${data.title || 'Report'}</h3>
                    <table>
                        <thead>
                            <tr>
                                ${data.headers.map(header => `<th>${header}</th>`).join('')}
                            </tr>
                        </thead>
                        <tbody>
                            ${data.rows.length > 0 ? data.rows.map(row => `
                                <tr>
                                    ${row.map(cell => `<td>${cell}</td>`).join('')}
                                </tr>
                            `).join('') : `<tr><td colspan="${data.headers.length}" style="text-align: center;">No data found for this report.</td></tr>`}
                        </tbody>
                    </table>
                </div>
            `;
            reportDisplayArea.innerHTML = tableHtml;
        };

        const generateReport = async (form) => {
            const formData = new FormData(form);
            const data = Object.fromEntries(formData.entries());
            reportDisplayArea.innerHTML = '<p>Generating report...</p>';

            try {
                const response = await fetch('api/generate_report.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(data)
                });
                const result = await response.json();
                if (result.error) throw new Error(result.error);
                renderReport(result);
            } catch (error) {
                console.error("Report Generation Error:", error);
                reportDisplayArea.innerHTML = `<p style="color: red;">Error generating report: ${error.message}</p>`;
            }
        };
        
        salesForm.addEventListener('submit', (e) => {
            e.preventDefault();
            generateReport(salesForm);
        });

        inventoryForm.addEventListener('submit', (e) => {
            e.preventDefault();
            generateReport(inventoryForm);
        });

        // Show/hide threshold field based on selection
        const toggleThresholdField = () => {
            if (inventoryReportTypeSelect.value === 'expiring_soon') {
                expiryThresholdGroup.style.display = 'block';
            } else {
                expiryThresholdGroup.style.display = 'none';
            }
        };
        inventoryReportTypeSelect.addEventListener('change', toggleThresholdField);
        toggleThresholdField(); // Run on init
    };

    const initUsersModule = () => {
        const form = document.getElementById('user-form');
        if (!form) return;

        const formTitle = document.getElementById('user-form-title');
        const idInput = document.getElementById('user_id');
        const usernameInput = document.getElementById('user_username');
        const fullNameInput = document.getElementById('user_full_name');
        const passwordInput = document.getElementById('user_password');
        const passwordHelpText = document.getElementById('password-help-text');
        const roleSelect = document.getElementById('user_role');
        const activeSelect = document.getElementById('user_is_active');
        const submitBtn = document.getElementById('user-submit-btn');
        const cancelBtn = document.getElementById('user-cancel-btn');
        const tableBody = document.getElementById('users-table-body');
        const messageContainer = document.getElementById('form-message-container');

        const resetForm = () => {
            form.reset();
            formTitle.textContent = 'Add New User';
            submitBtn.textContent = 'Add User';
            idInput.value = '';
            passwordInput.required = true;
            passwordHelpText.textContent = 'Required for new users.';
            cancelBtn.style.display = 'none';
        };

        const showMessage = (message, isSuccess) => {
            messageContainer.innerHTML = `<div class="form-message ${isSuccess ? 'success' : 'error'}">${message}</div>`;
            setTimeout(() => { messageContainer.innerHTML = ''; }, 4000);
        };

        const loadUsers = async () => {
            try {
                const response = await fetch(`api/get_users.php?t=${new Date().getTime()}`);
                const users = await response.json();
                if (users.error) throw new Error(users.error);

                tableBody.innerHTML = '';
                users.forEach(user => {
                    const row = document.createElement('tr');
                    const statusHtml = user.is_active ? `<span class="status-ok">Active</span>` : `<span class="status-danger">Inactive</span>`;
                    
                    row.innerHTML = `
                        <td>${user.id}</td>
                        <td>${user.username}</td>
                        <td>${user.full_name || 'N/A'}</td>
                        <td><span class="role-${user.role}">${user.role}</span></td>
                        <td>${statusHtml}</td>
                        <td>
                            <a href="#" class="edit-btn btn-action-edit" data-id="${user.id}">Edit</a>
                            <button class="delete-btn btn-action-delete" data-id="${user.id}">Delete</button>
                        </td>
                    `;
                    tableBody.appendChild(row);
                });
            } catch (error) {
                console.error("Error loading users:", error);
                showMessage("Could not load users.", false);
            }
        };

        tableBody.addEventListener('click', async (e) => {
            console.log('User table click event fired. Target:', e.target); // Diagnostic log
            
            // Edit Handler
            if (e.target.classList.contains('edit-btn')) {
                e.preventDefault();
                const id = e.target.getAttribute('data-id');
                console.log('Edit button clicked for user ID:', id); // Diagnostic log
                
                if (!id) {
                    console.error('Data-id attribute missing for edit button.'); // Diagnostic log
                    showMessage("Could not fetch user details: Missing user ID.", false);
                    return;
                }

                try {
                    console.log(`Attempting to fetch user details for ID: ${id} from api/get_user.php`); // Diagnostic log
                    const response = await fetch(`api/get_user.php?id=${id}&t=${new Date().getTime()}`);
                    console.log('Response received from get_user.php:', response); // Diagnostic log
                    const user = await response.json();
                    console.log('Parsed user details JSON:', user); // Diagnostic log

                    if (user.error) {
                        throw new Error(user.error);
                    }

                    formTitle.textContent = 'Edit User';
                    submitBtn.textContent = 'Update User';
                    cancelBtn.style.display = 'inline-block';
                    passwordInput.required = false;
                    passwordHelpText.textContent = 'Optional: Enter a new password to change it.';
                    
                    idInput.value = user.id;
                    usernameInput.value = user.username;
                    fullNameInput.value = user.full_name;
                    roleSelect.value = user.role;
                    activeSelect.value = user.is_active ? '1' : '0';
                    console.log('User form populated with data for ID:', user.id); // Diagnostic log
                    
                } catch(error) {
                     console.error('Error fetching user details:', error); // Diagnostic log
                     showMessage(`Could not fetch user details: ${error.message || 'A network error occurred.'}`, false);
                }
            } 
            
            // Delete Handler
            else if (e.target.classList.contains('delete-btn')) {
                e.preventDefault();
                const id = e.target.getAttribute('data-id');

                if (confirm("Are you sure you want to delete this user?")) {
                    try {
                        const response = await fetch('./api/delete_user_handler.php', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({ id: id })
                        });

                        const text = await response.text();
                        console.log("Delete Raw Response:", text); // Debugging

                        let result;
                        try {
                             result = JSON.parse(text);
                        } catch (e) {
                             throw new Error(`Invalid JSON response: ${text.substring(0, 50)}...`);
                        }

                        if (result.status === 'success') {
                            showMessage(result.message, true);
                            loadUsers(); // Live update
                        } else {
                            showMessage(result.message || 'Error deleting user.', false);
                        }
                    } catch (error) {
                        console.error('Delete error:', error);
                        showMessage(`Error: ${error.message}`, false);
                    }
                }
            }
        });

        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            const isEditMode = !!idInput.value;
            
            // Use relative path for update handler as requested
            const url = isEditMode ? './api/update_user_handler.php' : 'api/add_user.php';
            
            const formData = new FormData(form);
            let data = Object.fromEntries(formData.entries());

            // If updating, map fields to match the handler's expectation
            if (isEditMode) {
                data = {
                    ...data,
                    user_id: data.id,
                    status: data.is_active
                };
                // Only send password on update if it's not empty
                if (!data.password) {
                    delete data.password;
                }
            }

            try {
                const response = await fetch(url, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(data)
                });
                
                const text = await response.text();
                console.log("Raw Response:", text); // Debugging line
                
                let result;
                try {
                     result = JSON.parse(text);
                } catch (e) {
                     throw new Error(`Invalid JSON response: ${text.substring(0, 50)}...`);
                }

                // Check for 'success' (add_user) or 'status' === 'success' (update_user_handler)
                if(result.success || result.status === 'success') {
                    showMessage(result.message || 'Operation successful.', true);
                    resetForm();
                    loadUsers();
                } else {
                    showMessage(result.message || 'An error occurred.', false);
                }
            } catch (error) {
                 console.log(error); // Enhanced logging as requested
                 showMessage(`Error: ${error.message || 'A network error occurred.'}`, false);
            }
        });

        cancelBtn.addEventListener('click', resetForm);
        loadUsers();
    };
    
    const initSuppliersModule = () => {
        const form = document.getElementById('supplier-form');
        if (!form) return;

        const formTitle = document.getElementById('supplier-form-title');
        const idInput = document.getElementById('supplier_id');
        const nameInput = document.getElementById('supplier_name');
        const contactInput = document.getElementById('supplier_contact');
        const phoneInput = document.getElementById('supplier_phone');
        const emailInput = document.getElementById('supplier_email');
        const submitBtn = document.getElementById('supplier-submit-btn');
        const cancelBtn = document.getElementById('supplier-cancel-btn');
        const tableBody = document.getElementById('suppliers-table-body');
        const messageContainer = document.getElementById('form-message-container');

        const resetForm = () => {
            form.reset();
            formTitle.textContent = 'Add New Supplier';
            submitBtn.textContent = 'Add Supplier';
            idInput.value = '';
            cancelBtn.style.display = 'none';
        };

        const showMessage = (message, isSuccess) => {
            messageContainer.innerHTML = `<div class="form-message ${isSuccess ? 'success' : 'error'}">${message}</div>`;
            setTimeout(() => { messageContainer.innerHTML = ''; }, 4000);
        };

        const loadSuppliers = async () => {
            try {
                const response = await fetch(`api/get_suppliers.php?t=${new Date().getTime()}`);
                const suppliers = await response.json();
                if (suppliers.error) throw new Error(suppliers.error);

                tableBody.innerHTML = '';
                suppliers.forEach(s => {
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td>${s.id}</td>
                        <td>${s.name}</td>
                        <td><a href="#" class="edit-btn" data-id="${s.id}">Edit / View</a></td>
                    `;
                    tableBody.appendChild(row);
                });
            } catch (error) {
                console.error("Error loading suppliers:", error);
                showMessage("Could not load suppliers.", false);
            }
        };

        tableBody.addEventListener('click', async (e) => {
            if (e.target.classList.contains('edit-btn')) {
                e.preventDefault();
                const id = e.target.getAttribute('data-id');
                
                try {
                    const response = await fetch(`api/get_supplier.php?id=${id}&t=${new Date().getTime()}`);
                    const s = await response.json();
                    if (s.error) throw new Error(s.error);

                    formTitle.textContent = 'Edit Supplier';
                    submitBtn.textContent = 'Update Supplier';
                    cancelBtn.style.display = 'inline-block';
                    
                    idInput.value = s.id;
                    nameInput.value = s.name;
                    contactInput.value = '';
                    phoneInput.value = '';
                    emailInput.value = '';
                    contactInput.placeholder = "Enter new contact person (optional)";
                    phoneInput.placeholder = "Enter new phone (optional)";
                    emailInput.placeholder = "Enter new email (optional)";
                    
                } catch(error) {
                     showMessage("Could not fetch supplier details.", false);
                }
            }
        });

        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            const isEditMode = !!idInput.value;
            const url = isEditMode ? 'api/update_supplier.php' : 'api/add_supplier.php';
            
            const formData = new FormData(form);
            const data = Object.fromEntries(formData.entries());

            try {
                const response = await fetch(url, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(data)
                });
                const result = await response.json();

                if(result.success) {
                    showMessage(result.message, true);
                    resetForm();
                    loadSuppliers();
                } else {
                    showMessage(result.message || 'An error occurred.', false);
                }
            } catch (error) {
                 showMessage("A network error occurred.", false);
            }
        });

        cancelBtn.addEventListener('click', resetForm);
        loadSuppliers();
    };

    const initManufacturersModule = () => {
        const form = document.getElementById('manufacturer-form');
        if (!form) return;

        const formTitle = document.getElementById('manufacturer-form-title');
        const idInput = document.getElementById('manufacturer_id');
        const nameInput = document.getElementById('manufacturer_name');
        const contactInput = document.getElementById('manufacturer_contact');
        const phoneInput = document.getElementById('manufacturer_phone');
        const emailInput = document.getElementById('manufacturer_email');
        const submitBtn = document.getElementById('manufacturer-submit-btn');
        const cancelBtn = document.getElementById('manufacturer-cancel-btn');
        const tableBody = document.getElementById('manufacturers-table-body');
        const messageContainer = document.getElementById('form-message-container');

        const resetForm = () => {
            form.reset();
            formTitle.textContent = 'Add New Manufacturer';
            submitBtn.textContent = 'Add Manufacturer';
            idInput.value = '';
            cancelBtn.style.display = 'none';
        };

        const showMessage = (message, isSuccess) => {
            messageContainer.innerHTML = `<div class="form-message ${isSuccess ? 'success' : 'error'}">${message}</div>`;
            setTimeout(() => { messageContainer.innerHTML = ''; }, 4000);
        };

        const loadManufacturers = async () => {
            try {
                const response = await fetch(`api/get_manufacturers.php?t=${new Date().getTime()}`);
                const manufacturers = await response.json();
                if (manufacturers.error) throw new Error(manufacturers.error);

                tableBody.innerHTML = '';
                manufacturers.forEach(m => {
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td>${m.id}</td>
                        <td>${m.name}</td>
                        <td><a href="#" class="edit-btn" data-id="${m.id}">Edit / View</a></td>
                    `;
                    tableBody.appendChild(row);
                });
            } catch (error) {
                console.error("Error loading manufacturers:", error);
                showMessage("Could not load manufacturers.", false);
            }
        };

        tableBody.addEventListener('click', async (e) => {
            if (e.target.classList.contains('edit-btn')) {
                e.preventDefault();
                const id = e.target.getAttribute('data-id');
                
                try {
                    const response = await fetch(`api/get_manufacturer.php?id=${id}&t=${new Date().getTime()}`);
                    const m = await response.json();
                    if (m.error) throw new Error(m.error);

                    formTitle.textContent = 'Edit Manufacturer';
                    submitBtn.textContent = 'Update Manufacturer';
                    cancelBtn.style.display = 'inline-block';
                    
                    idInput.value = m.id;
                    nameInput.value = m.name;
                    // NOTE: Because details are encrypted, we don't receive them here.
                    // The form fields are cleared to allow overwriting with new plain text.
                    contactInput.value = '';
                    phoneInput.value = '';
                    emailInput.value = '';
                    contactInput.placeholder = "Enter new contact person (optional)";
                    phoneInput.placeholder = "Enter new phone (optional)";
                    emailInput.placeholder = "Enter new email (optional)";
                    
                } catch(error) {
                     showMessage("Could not fetch manufacturer details.", false);
                }
            }
        });

        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            const isEditMode = !!idInput.value;
            const url = isEditMode ? 'api/update_manufacturer.php' : 'api/add_manufacturer.php';
            
            const formData = new FormData(form);
            const data = Object.fromEntries(formData.entries());

            try {
                const response = await fetch(url, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(data)
                });
                const result = await response.json();

                if(result.success) {
                    showMessage(result.message, true);
                    resetForm();
                    loadManufacturers();
                } else {
                    showMessage(result.message || 'An error occurred.', false);
                }
            } catch (error) {
                 showMessage("A network error occurred.", false);
            }
        });

        cancelBtn.addEventListener('click', resetForm);
        loadManufacturers();
    };

    const populateDropdown = (selectElement, items, defaultOptionText) => {
        if (!selectElement) return;
        selectElement.innerHTML = `<option value="">${defaultOptionText}</option>`;
        items.forEach(item => {
            const option = document.createElement('option');
            option.value = item.id;
            option.textContent = item.name;
            selectElement.appendChild(option);
        });
    };

    const initMedicinesModule = () => {
        const form = document.getElementById('add-medicine-form');
        const submitBtn = document.getElementById('add-medicine-btn');
        if (!form || !submitBtn) return;

        const messageContainer = document.getElementById('form-message-container');
        const categorySelect = document.getElementById('category_id');
        const manufacturerSelect = document.getElementById('manufacturer_id');
        const saltSelect = document.getElementById('generic_salt_id');
        
        // Fetch all data for dropdowns in parallel
        Promise.all([
            fetch('api/get_categories.php?t=' + new Date().getTime()).then(res => res.json()),
            fetch('api/get_manufacturers.php?t=' + new Date().getTime()).then(res => res.json()),
            fetch('api/get_generic_salts.php?t=' + new Date().getTime()).then(res => res.json())
        ]).then(([categories, manufacturers, salts]) => {
            populateDropdown(categorySelect, categories, 'Select Category');
            populateDropdown(manufacturerSelect, manufacturers, 'Select Manufacturer');
            populateDropdown(saltSelect, salts, 'Select Generic Salt');
        }).catch(error => {
            console.error("Error populating dropdowns:", error);
            showMessage('form-message-container', "Could not load form data. Please refresh.", false);
        });

        // Attach listener directly to the button
        submitBtn.addEventListener('click', async () => {
            const formData = new FormData(form);
            const data = Object.fromEntries(formData.entries());

            // Basic manual validation
            if (!data.name || !data.batch_number || !data.expiry_date || !data.quantity || !data.mrp || !data.cost_price || !data.selling_price) {
                showMessage('form-message-container', "Please fill all required fields.", false);
                return;
            }

            try {
                const response = await fetch('api/add_medicine.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(data)
                });
                const result = await response.json();

                if (result.success) {
                    showMessage('form-message-container', result.message, true);
                    form.reset();
                    // Navigate back to the POS terminal to see the new item
                    loadModule('pos');
                    // Also update the active link in the sidebar
                    document.querySelector('.nav-link.active')?.classList.remove('active');
                    document.querySelector('.nav-link[data-module="pos"]')?.classList.add('active');
                } else {
                    showMessage('form-message-container', result.message || 'An error occurred.', false);
                }
            } catch (error) {
                showMessage('form-message-container', 'A network error occurred.', false);
            }
        });
    };

    const initCategoriesModule = () => {
        const form = document.getElementById('category-form');
        const formTitle = document.getElementById('category-form-title');
        const categoryIdInput = document.getElementById('category_id');
        const categoryNameInput = document.getElementById('category_name');
        const parentIdSelect = document.getElementById('parent_id');
        const submitBtn = document.getElementById('category-submit-btn');
        const cancelBtn = document.getElementById('category-cancel-btn');
        const tableBody = document.getElementById('categories-table-body');
        const messageContainer = document.getElementById('form-message-container');

        const resetForm = () => {
            form.reset();
            formTitle.textContent = 'Add New Category';
            submitBtn.textContent = 'Add Category';
            categoryIdInput.value = '';
            cancelBtn.style.display = 'none';
        };

        const showMessage = (message, isSuccess) => {
            messageContainer.innerHTML = `<div class="form-message ${isSuccess ? 'success' : 'error'}">${message}</div>`;
            setTimeout(() => { messageContainer.innerHTML = ''; }, 4000);
        };

        const loadCategories = async () => {
            try {
                const response = await fetch(`api/get_categories.php?t=${new Date().getTime()}`);
                const categories = await response.json();
                if (categories.error) throw new Error(categories.error);

                tableBody.innerHTML = '';
                parentIdSelect.innerHTML = '<option value="">None</option>';

                categories.forEach(cat => {
                    // Populate table
                    const row = document.createElement('tr');
                    
                    // Styled Edit Button (matches User Management edit-btn look)
                    const editBtnStyle = `
                        background-color: #28a745;
                        color: white;
                        border: none;
                        padding: 5px 10px;
                        border-radius: 4px;
                        cursor: pointer;
                        font-size: 0.9rem;
                        text-decoration: none;
                        display: inline-block;
                        margin-right: 5px;
                    `;

                    // Styled Delete Button
                    const deleteBtnStyle = `
                        background-color: #ff4d4d; 
                        color: white; 
                        border: none; 
                        padding: 5px 10px; 
                        border-radius: 4px; 
                        cursor: pointer; 
                        font-size: 0.9rem;
                    `;

                    row.innerHTML = `
                        <td>${cat.id}</td>
                        <td>${cat.name}</td>
                        <td>${cat.parent_name}</td>
                        <td>
                            <a href="#" class="edit-btn" data-id="${cat.id}" style="${editBtnStyle}">Edit</a>
                            <button class="delete-btn" data-id="${cat.id}" data-name="${cat.name}" style="${deleteBtnStyle}">Delete</button>
                        </td>
                    `;
                    tableBody.appendChild(row);

                    // Populate parent dropdown
                    const option = document.createElement('option');
                    option.value = cat.id;
                    option.textContent = cat.name;
                    parentIdSelect.appendChild(option);
                });
            } catch (error) {
                console.error("Error loading categories:", error);
                showMessage("Could not load categories.", false);
            }
        };

        const deleteCategory = async (id, name) => {
            if (!confirm(`Are you sure you want to delete the category "${name}"? This action cannot be undone.`)) {
                return;
            }

            try {
                const response = await fetch('api/delete_category.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id: id })
                });
                const result = await response.json();

                if (result.success) {
                    showMessage(result.message, true);
                    loadCategories(); // Refresh the list
                } else {
                    showMessage(result.message || 'An error occurred during deletion.', false);
                }
            } catch (error) {
                console.error("Delete category error:", error);
                showMessage("A network error occurred.", false);
            }
        };

        tableBody.addEventListener('click', async (e) => {
            if (e.target.classList.contains('edit-btn')) {
                e.preventDefault();
                const id = e.target.getAttribute('data-id');
                
                try {
                    const response = await fetch(`api/get_category.php?id=${id}&t=${new Date().getTime()}`);
                    const cat = await response.json();
                    if (cat.error) throw new Error(cat.error);

                    formTitle.textContent = 'Edit Category';
                    submitBtn.textContent = 'Update Category';
                    cancelBtn.style.display = 'inline-block';
                    
                    categoryIdInput.value = cat.id;
                    categoryNameInput.value = cat.name;

                    // Exclude self from parent dropdown
                    Array.from(parentIdSelect.options).forEach(opt => {
                        opt.disabled = (opt.value == id);
                    });

                    parentIdSelect.value = cat.parent_id || '';
                    
                } catch(error) {
                     showMessage("Could not fetch category details.", false);
                }
            } else if (e.target.classList.contains('delete-btn')) {
                e.preventDefault();
                const id = e.target.getAttribute('data-id');
                const name = e.target.getAttribute('data-name');
                if (id && name) {
                    deleteCategory(parseInt(id), name);
                }
            }
        });

        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            const isEditMode = !!categoryIdInput.value;
            const url = isEditMode ? 'api/update_category.php' : 'api/add_category.php';
            
            const formData = new FormData(form);
            const data = Object.fromEntries(formData.entries());

            try {
                const response = await fetch(url, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(data)
                });
                const result = await response.json();

                if(result.success) {
                    showMessage(result.message, true);
                    resetForm();
                    loadCategories();
                } else {
                    showMessage(result.message || 'An error occurred.', false);
                }
            } catch (error) {
                 showMessage("A network error occurred.", false);
            }
        });

        cancelBtn.addEventListener('click', resetForm);

        // Initial load
        loadCategories();
    };

    const showMessage = (containerId, message, isSuccess) => {
        const container = document.getElementById(containerId);
        if (container) {
            container.innerHTML = `<div class="form-message ${isSuccess ? 'success' : 'error'}">${message}</div>`;
            setTimeout(() => { container.innerHTML = ''; }, 4000);
        }
    };

    // --- Purchases Module Logic ---
    const loadPurchases = async () => {
        const purchasesTableBody = document.getElementById('purchases-table-body');
        if (!purchasesTableBody) return;

        try {
            const response = await fetch('api/get_purchases.php?t=' + new Date().getTime());
            const purchases = await response.json();
            if (purchases.error) throw new Error(purchases.error);

            purchasesTableBody.innerHTML = ''; // Clear loading message
            if (purchases.length === 0) {
                purchasesTableBody.innerHTML = '<tr><td colspan="6" style="text-align: center;">No purchase records found.</td></tr>';
                return;
            }

            purchases.forEach(p => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>P-${p.id.toString().padStart(3, '0')}</td>
                    <td>${p.supplier_name}</td>
                    <td>${p.purchase_date}</td>
                    <td>${parseFloat(p.total_amount).toFixed(2)}</td>
                    <td>${p.user_name}</td>
                    <td><a href="#" class="view-details-btn" data-purchase-id="${p.id}">View Details</a></td>
                `;
                purchasesTableBody.appendChild(row);
            });

        } catch (error) {
            console.error('Error fetching purchases:', error);
            purchasesTableBody.innerHTML = '<tr><td colspan="6" style="color: red; text-align: center;">Error loading purchase data.</td></tr>';
        }
    };

    const initPurchasesModule = async () => {
        console.log('initPurchasesModule called'); // Diagnostic log
        const modal = document.getElementById('purchase-details-modal');
        const purchasesTableBody = document.getElementById('purchases-table-body'); // Added for the initial loadPurchases call later
        // New Purchase Form Elements
        const addNewPurchaseBtn = document.getElementById('add-new-purchase-btn');
        const newPurchaseFormContainer = document.getElementById('new-purchase-form-container');
        const addPurchaseForm = document.getElementById('add-purchase-form');
        const cancelPurchaseBtn = document.getElementById('cancel-purchase-btn');
        const supplierSelect = document.getElementById('supplier_id');
        const purchaseFormMessageContainer = document.getElementById('form-message-container'); // for the new purchase form
        const addItemBtn = document.getElementById('add-item-btn');
        const purchaseItemsList = document.getElementById('purchase-items-list');

        console.log('addNewPurchaseBtn:', addNewPurchaseBtn); // Diagnostic log
        console.log('newPurchaseFormContainer:', newPurchaseFormContainer); // Diagnostic log

        if (!modal || !addNewPurchaseBtn || !newPurchaseFormContainer || !addPurchaseForm || !cancelPurchaseBtn || !supplierSelect || !purchasesTableBody) {
            console.error("Purchases module elements not found. Skipping initialization."); // Added error log
            // Log missing elements for more specific debugging
            if (!modal) console.error("Missing modal");
            if (!addNewPurchaseBtn) console.error("Missing addNewPurchaseBtn");
            if (!newPurchaseFormContainer) console.error("Missing newPurchaseFormContainer");
            if (!addPurchaseForm) console.error("Missing addPurchaseForm");
            if (!cancelPurchaseBtn) console.error("Missing cancelPurchaseBtn");
            if (!supplierSelect) console.error("Missing supplierSelect");
            if (!purchasesTableBody) console.error("Missing purchasesTableBody");
            return;
        }

        const modalCloseBtn = document.getElementById('purchase-modal-close-btn');
        const modalDetailsContent = document.getElementById('purchase-modal-details-content');
        const modalLoader = document.getElementById('purchase-modal-loader');

        const closeModal = () => { modal.style.display = 'none'; };
        modalCloseBtn.onclick = closeModal;
        modal.onclick = (e) => { if (e.target === modal) closeModal(); };

        const showPurchaseDetails = async (purchaseId) => {
            modal.style.display = 'flex';
            modalLoader.style.display = 'block';
            modalDetailsContent.innerHTML = '';

            try {
                const response = await fetch(`api/get_purchase_details.php?id=${purchaseId}&t=${new Date().getTime()}`);
                const details = await response.json();
                if (details.error) throw new Error(details.error);

                let itemsHtml = `
                    <p><strong>Purchase ID:</strong> ${details.id}</p>
                    <p><strong>Supplier:</strong> ${details.supplier_name}</p>
                    <p><strong>Purchase Date:</strong> ${details.purchase_date}</p>
                    <hr style="margin: 1rem 0; border-color: var(--border-color);">
                    <h4>Items Purchased</h4>
                    <div class="table-container" style="margin-top: 1rem;">
                        <table>
                            <thead>
                                <tr><th>Item Name</th><th>Batch #</th><th>Qty</th><th>Cost Price</th><th>Total</th></tr>
                            </thead>
                            <tbody>
                                ${details.items.map(item => `
                                    <tr>
                                        <td>${item.medicine_name}</td>
                                        <td>${item.batch_number}</td>
                                        <td>${item.quantity}</td>
                                        <td>${parseFloat(item.cost_price).toFixed(2)}</td>
                                        <td>${parseFloat(item.total).toFixed(2)}</td>
                                    </tr>
                                `).join('')}
                            </tbody>
                        </table>
                    </div>
                    <div style="text-align: right; margin-top: 1rem; font-size: 1.2rem; color: var(--accent-green); font-weight: bold;">
                        <p>Grand Total: ${parseFloat(details.total_amount).toFixed(2)}</p>
                    </div>
                `;
                modalDetailsContent.innerHTML = itemsHtml;
            } catch (error) {
                console.error('Error fetching purchase details:', error);
                modalDetailsContent.innerHTML = '<p style="color: red;">Could not load purchase details.</p>';
            } finally {
                modalLoader.style.display = 'none';
            }
        };

        // Event listener for "View Details" buttons (delegated to document for dynamic rows)
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('view-details-btn')) {
                e.preventDefault();
                const purchaseId = e.target.getAttribute('data-purchase-id');
                if (purchaseId) showPurchaseDetails(purchaseId);
            }
        });

        // --- New Purchase Form Logic ---
        const populateSuppliers = async () => {
            try {
                const response = await fetch(`api/get_suppliers.php?t=${new Date().getTime()}`);
                const suppliers = await response.json();
                if (suppliers.error) throw new Error(suppliers.error);

                supplierSelect.innerHTML = '<option value="">Select Supplier</option>';
                suppliers.forEach(s => {
                    const option = document.createElement('option');
                    option.value = s.id;
                    option.textContent = s.name;
                    supplierSelect.appendChild(option);
                });
            } catch (error) {
                console.error("Error populating suppliers:", error);
                showMessage('form-message-container', "Could not load suppliers.", false);
            }
        };

        addNewPurchaseBtn.addEventListener('click', () => {
            console.log('Add New Purchase button clicked!'); // Diagnostic log
            newPurchaseFormContainer.style.display = 'block';
            addNewPurchaseBtn.style.display = 'none';
            populateSuppliers();
            console.log('Form container display set to block:', newPurchaseFormContainer.style.display); // Diagnostic log
        });

        cancelPurchaseBtn.addEventListener('click', () => {
            newPurchaseFormContainer.style.display = 'none';
            addNewPurchaseBtn.style.display = 'block';
            addPurchaseForm.reset();
            purchaseItemsList.innerHTML = `
                <div class="purchase-item-entry mb-3 p-3 border rounded">
                    <div class="form-group">
                        <label for="medicine_name_1">Medicine Name</label>
                        <input type="text" id="medicine_name_1" name="items[0][medicine_name]" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="batch_number_1">Batch Number</label>
                        <input type="text" id="batch_number_1" name="items[0][batch_number]" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="expiry_date_1">Expiry Date</label>
                        <input type="date" id="expiry_date_1" name="items[0][expiry_date]" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="quantity_1">Quantity</label>
                        <input type="number" id="quantity_1" name="items[0][quantity]" class="form-control" min="1" required>
                    </div>
                    <div class="form-group">
                        <label for="cost_price_1">Cost Price</label>
                        <input type="number" id="cost_price_1" name="items[0][cost_price]" class="form-control" step="0.01" min="0" required>
                    </div>
                     <div class="form-group">
                        <label for="selling_price_1">Selling Price</label>
                        <input type="number" id="selling_price_1" name="items[0][selling_price]" class="form-control" step="0.01" min="0" required>
                    </div>
                </div>
            `; // Reset items list to one item
        });

        let itemCounter = 1;
        addItemBtn.addEventListener('click', () => {
            itemCounter++;
            const newItemHtml = `
                <div class="purchase-item-entry mb-3 p-3 border rounded">
                    <div class="form-group">
                        <label for="medicine_name_${itemCounter}">Medicine Name</label>
                        <input type="text" id="medicine_name_${itemCounter}" name="items[${itemCounter-1}][medicine_name]" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="batch_number_${itemCounter}">Batch Number</label>
                        <input type="text" id="batch_number_${itemCounter}" name="items[${itemCounter-1}][batch_number]" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="expiry_date_${itemCounter}">Expiry Date</label>
                        <input type="date" id="expiry_date_${itemCounter}" name="items[${itemCounter-1}][expiry_date]" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="quantity_${itemCounter}">Quantity</label>
                        <input type="number" id="quantity_${itemCounter}" name="items[${itemCounter-1}][quantity]" class="form-control" min="1" required>
                    </div>
                    <div class="form-group">
                        <label for="cost_price_${itemCounter}">Cost Price</label>
                        <input type="number" id="cost_price_${itemCounter}" name="items[${itemCounter-1}][cost_price]" class="form-control" step="0.01" min="0" required>
                    </div>
                     <div class="form-group">
                        <label for="selling_price_${itemCounter}">Selling Price</label>
                        <input type="number" id="selling_price_${itemCounter}" name="items[${itemCounter-1}][selling_price]" class="form-control" step="0.01" min="0" required>
                    </div>
                    <button type="button" class="btn btn-danger btn-sm remove-item-btn">Remove Item</button>
                </div>
            `;
            purchaseItemsList.insertAdjacentHTML('beforeend', newItemHtml);
        });

        purchaseItemsList.addEventListener('click', (e) => {
            if (e.target.classList.contains('remove-item-btn')) {
                e.target.closest('.purchase-item-entry').remove();
            }
        });


        addPurchaseForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const formData = new FormData(addPurchaseForm);
            const data = {
                supplier_id: formData.get('supplier_id'),
                purchase_date: formData.get('purchase_date'),
                items: []
            };

            // Manually collect item data
            const itemEntries = purchaseItemsList.querySelectorAll('.purchase-item-entry');
            itemEntries.forEach(entry => {
                const item = {};
                item.medicine_name = entry.querySelector('[name*="[medicine_name]"]').value;
                item.batch_number = entry.querySelector('[name*="[batch_number]"]').value;
                item.expiry_date = entry.querySelector('[name*="[expiry_date]"]').value;
                item.quantity = entry.querySelector('[name*="[quantity]"]').value;
                item.cost_price = entry.querySelector('[name*="[cost_price]"]').value;
                item.selling_price = entry.querySelector('[name*="[selling_price]"]').value;
                data.items.push(item);
            });

            // Basic validation
            if (!data.supplier_id || !data.purchase_date || data.items.length === 0) {
                showMessage('form-message-container', 'Please fill all required fields and add at least one item.', false);
                return;
            }

            try {
                const response = await fetch('api/add_purchase.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(data)
                });
                const result = await response.json();

                if (result.success) {
                    showMessage('form-message-container', result.message, true);
                    addPurchaseForm.reset();
                    newPurchaseFormContainer.style.display = 'none';
                    addNewPurchaseBtn.style.display = 'block';
                    loadPurchases(); // Refresh the list of purchases
                    // Reset items list to one item after successful submission
                    purchaseItemsList.innerHTML = `
                        <div class="purchase-item-entry mb-3 p-3 border rounded">
                            <div class="form-group">
                                <label for="medicine_name_1">Medicine Name</label>
                                <input type="text" id="medicine_name_1" name="items[0][medicine_name]" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label for="batch_number_1">Batch Number</label>
                                <input type="text" id="batch_number_1" name="items[0][batch_number]" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label for="expiry_date_1">Expiry Date</label>
                                <input type="date" id="expiry_date_1" name="items[0][expiry_date]" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label for="quantity_1">Quantity</label>
                                <input type="number" id="quantity_1" name="items[0][quantity]" class="form-control" min="1" required>
                            </div>
                            <div class="form-group">
                                <label for="cost_price_1">Cost Price</label>
                                <input type="number" id="cost_price_1" name="items[0][cost_price]" class="form-control" step="0.01" min="0" required>
                            </div>
                             <div class="form-group">
                                <label for="selling_price_1">Selling Price</label>
                                <input type="number" id="selling_price_1" name="items[0][selling_price]" class="form-control" step="0.01" min="0" required>
                            </div>
                        </div>
                    `;
                    itemCounter = 1; // Reset counter
                } else {
                    showMessage('form-message-container', result.message || 'Error recording purchase.', false);
                }
            } catch (error) {
                console.error("Add Purchase Error:", error);
                showMessage('form-message-container', 'A network error occurred. Please try again.', false);
            }
        });

        // Initial load of purchases (main table)
        loadPurchases();
    };

    const renderStockTable = (items) => {
        const stockTableBody = document.getElementById('stock-table-body');
        if (!stockTableBody) return;

        stockTableBody.innerHTML = '';
        if (items.length === 0) {
            stockTableBody.innerHTML = '<tr><td colspan="7" style="text-align: center;">No stock records found.</td></tr>';
            return;
        }

        const today = new Date();
        today.setHours(0, 0, 0, 0); 

        const oneDayInMs = 24 * 60 * 60 * 1000;

        items.forEach(item => {
            const row = document.createElement('tr');
            
            const expiryDateStr = item.expiry_date;
            const parts = expiryDateStr.split('-');
            const expiryDate = new Date(parts[0], parts[1] - 1, parts[2]);
            expiryDate.setHours(0, 0, 0, 0);

            const diffTime = expiryDate - today;
            const diffDays = Math.ceil(diffTime / oneDayInMs);

            let statusHtml;

            if (item.quantity <= 0) {
                statusHtml = '<span class="status-danger">Out of Stock</span>';
            } else if (diffDays <= 1) {
                statusHtml = '<span class="status-warning">Expiring Soon</span>';
            } else {
                statusHtml = '<span class="status-ok">In Stock</span>';
            }
            
            row.innerHTML = `
                <td>${item.name}</td>
                <td>${item.medicine_type || 'N/A'}</td>
                <td>${item.batch_number}</td>
                <td>${item.expiry_date}</td>
                <td>${item.quantity}</td>
                <td>${parseFloat(item.selling_price).toFixed(2)}</td>
                <td>${statusHtml}</td>
                <td>
                    <a href="edit_medicine.php?id=${item.batch_id}" class="btn btn-secondary btn-sm">Edit</a>
                    <button class="btn btn-danger btn-sm delete-batch-btn" data-batch-id="${item.batch_id}" data-medicine-name="${item.name}">Delete</button>
                </td>
            `;
            stockTableBody.appendChild(row);
        });
    };

    
    
    const initStockModule = () => {
        const searchInput = document.getElementById('stock-search');
        const stockTableBody = document.getElementById('stock-table-body');
        const outOfStockCheckbox = document.getElementById('show-out-of-stock');
        if (!searchInput || !stockTableBody || !outOfStockCheckbox) return;

        let allStockItems = [];
        let searchTimeout;

        const loadAllStock = async () => {
            try {
                const response = await fetch('api/get_all_medicines.php?t=' + new Date().getTime());
                const items = await response.json();
                if (items.error) throw new Error(items.error);
                allStockItems = items;
                applyFilters();
            } catch (error) {
                console.error("Error loading all stock:", error);
                if (stockTableBody) stockTableBody.innerHTML = '<tr><td colspan="7" style="color: red; text-align: center;">Could not load stock data.</td></tr>';
            }
        };

        const applyFilters = () => {
            let itemsToRender = [...allStockItems];
            const query = searchInput.value.toLowerCase();
            
            if (outOfStockCheckbox.checked) {
                itemsToRender = itemsToRender.filter(item => parseInt(item.quantity) <= 0);
            }

            if (query.length >= 2) {
                 itemsToRender = itemsToRender.filter(item => 
                    item.name.toLowerCase().includes(query) || 
                    item.batch_number.toLowerCase().includes(query)
                );
            }
            
            renderStockTable(itemsToRender);
        };
        
        loadAllStock(); // Load all items initially

        searchInput.addEventListener('keyup', () => {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(applyFilters, 300);
        });

        outOfStockCheckbox.addEventListener('change', applyFilters);

        // Event listener for delete buttons
        stockTableBody.addEventListener('click', async (e) => {
            if (e.target.classList.contains('delete-batch-btn')) {
                const button = e.target;
                const batchId = button.dataset.batchId;
                const medicineName = button.dataset.medicineName;

                if (confirm(`Are you sure you want to delete the batch for "${medicineName}"? This action cannot be undone.`)) {
                    try {
                        const response = await fetch('api/delete_medicine_batch.php', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({ batch_id: batchId })
                        });
                        const result = await response.json();
                        if (result.success) {
                            alert(result.message);
                            loadAllStock(); // Refresh the table
                        } else {
                            throw new Error(result.message || 'An unknown error occurred.');
                        }
                    } catch (error) {
                        console.error("Delete batch error:", error);
                        alert(`Failed to delete batch: ${error.message}`);
                    }
                }
            }
        });
    };
    
    const initOutOfStockModule = () => {
        console.log("initOutOfStockModule called");
        const outOfStockTableBody = document.getElementById('out-of-stock-table-body');
        if (!outOfStockTableBody) {
            console.error("Element with ID 'out-of-stock-table-body' not found.");
            return;
        }

        const loadOutOfStock = async () => {
            outOfStockTableBody.innerHTML = '<tr><td colspan="6" style="text-align: center;">Loading out of stock data...</td></tr>';
            try {
                const response = await fetch('api/get_out_of_stock_medicines.php?t=' + new Date().getTime());
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                const items = await response.json();

                if (items.error) throw new Error(items.error);

                outOfStockTableBody.innerHTML = '';
                if (items.length === 0) {
                    outOfStockTableBody.innerHTML = '<tr><td colspan="6" style="text-align: center;">No out of stock medicines found.</td></tr>';
                    return;
                }

                items.forEach(item => {
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td>${item.name}</td>
                        <td>${item.batch_number}</td>
                        <td>${item.expiry_date}</td>
                        <td>${item.quantity}</td>
                        <td>${parseFloat(item.selling_price).toFixed(2)}</td>
                        <td>
                            <button class="btn btn-success btn-sm restock-btn" data-batch-id="${item.batch_id}" data-name="${item.name}">
                                <i class="fas fa-plus"></i> Restock
                            </button>
                        </td>
                    `;
                    outOfStockTableBody.appendChild(row);
                });

            } catch (error) {
                console.error("Error loading out of stock medicines:", error);
                outOfStockTableBody.innerHTML = `<tr><td colspan="6" style="color: red; text-align: center;">Could not load out of stock data. Error: ${error.message}</td></tr>`;
            }
        };

        // Event listener for restock buttons
        outOfStockTableBody.addEventListener('click', async (e) => {
            // Traverse up in case icon is clicked
            const btn = e.target.closest('.restock-btn');
            if (btn) {
                const batchId = btn.getAttribute('data-batch-id');
                const name = btn.getAttribute('data-name');
                
                const quantityStr = prompt(`Enter quantity to add for ${name}:`, "10");
                if (quantityStr !== null) {
                    const quantity = parseInt(quantityStr);
                    if (isNaN(quantity) || quantity <= 0) {
                        alert("Please enter a valid positive number.");
                        return;
                    }

                    try {
                        const response = await fetch('api/restock_medicine.php', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({ batch_id: batchId, new_quantity: quantity })
                        });
                        const result = await response.json();

                        if (result.success) {
                            alert(result.message);
                            loadOutOfStock(); // Refresh list to see item disappear
                        } else {
                            alert(result.message || "Error restocking medicine.");
                        }
                    } catch (error) {
                        console.error("Restock error:", error);
                        alert("A network error occurred.");
                    }
                }
            }
        });

        loadOutOfStock();
    };

    const initDashboardModule = async () => {
        console.log('initDashboardModule called.'); // Diagnostic log

        try {
            // Add cache-busting parameter
            const response = await fetch('api/get_dashboard_stats.php?t=' + new Date().getTime());
            const stats = await response.json();
            if (stats.error) throw new Error(stats.error);

            document.getElementById('stats-todays-sales').textContent = '$' + stats.todays_sales;
            document.getElementById('stats-items-sold').textContent = stats.items_sold_today;
            document.getElementById('stats-expiring-soon').textContent = stats.expiring_soon + ' items';
            document.getElementById('stats-out-of-stock').textContent = stats.out_of_stock + ' items';

        } catch (error) {
            console.error('Error fetching dashboard stats:', error);
             Array.from(document.querySelectorAll('.stat-card p')).forEach(el => {
                el.textContent = 'Error';
                el.style.color = 'red';
            });
        }

        // Charting Logic
        const salesPeriodSelect = document.getElementById('sales-period');
        const salesChartCanvas = document.getElementById('salesChart');
        let salesChartInstance = null; // To hold the Chart.js instance

        console.log('salesPeriodSelect:', salesPeriodSelect); // Diagnostic log
        console.log('salesChartCanvas:', salesChartCanvas);   // Diagnostic log

        const fetchAndRenderSalesChart = async (period) => {
            console.log(`Fetching sales data for period: ${period}`); // Diagnostic log
            try {
                // Use the new analytics endpoint
                const response = await fetch(`api/get_sales_analytics.php?type=${period}&t=` + new Date().getTime());
                const result = await response.json();
                console.log('API response for sales data:', result); // Diagnostic log

                if (result.success) {
                    const labels = result.labels;
                    const data = result.values;

                    if (salesChartCanvas) { // Check if canvas element exists
                        // Get 2D rendering context
                        const ctx = salesChartCanvas.getContext('2d');
                        if (!ctx) {
                            console.error('Failed to get 2D context for salesChartCanvas.');
                            return;
                        }

                        if (salesChartInstance) {
                            salesChartInstance.destroy(); // Destroy previous chart instance
                        }

                        // Create Gradient
                        const gradient = ctx.createLinearGradient(0, 0, 0, 400);
                        gradient.addColorStop(0, 'rgba(46, 204, 113, 0.5)'); // Green top
                        gradient.addColorStop(1, 'rgba(46, 204, 113, 0.0)'); // Transparent bottom

                        salesChartInstance = new Chart(ctx, { // Pass context to Chart constructor
                            type: 'line', 
                            data: {
                                labels: labels,
                                datasets: [{
                                    label: `Total Sales`,
                                    data: data,
                                    backgroundColor: gradient,
                                    borderColor: '#2ecc71',
                                    borderWidth: 2,
                                    pointBackgroundColor: '#2ecc71',
                                    pointBorderColor: '#fff',
                                    pointHoverBackgroundColor: '#fff',
                                    pointHoverBorderColor: '#2ecc71',
                                    fill: true,
                                    tension: 0.4 // Smooth curve
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                plugins: {
                                    legend: {
                                        labels: { color: '#c9d1d9' }
                                    },
                                    tooltip: {
                                        mode: 'index',
                                        intersect: false,
                                    }
                                },
                                scales: {
                                    x: {
                                        ticks: { color: '#8b949e' },
                                        grid: { color: 'rgba(255, 255, 255, 0.05)' }
                                    },
                                    y: {
                                        beginAtZero: true,
                                        ticks: { color: '#8b949e' },
                                        grid: { color: 'rgba(255, 255, 255, 0.05)' }
                                    }
                                },
                                interaction: {
                                    mode: 'nearest',
                                    axis: 'x',
                                    intersect: false
                                }
                            }
                        });
                        console.log('Chart.js instance created successfully.'); // Diagnostic log
                    } else {
                        console.error('salesChartCanvas element not found when trying to create chart.'); // Diagnostic log
                    }

                } else {
                    console.error('Error fetching sales data:', result.message);
                }
            } catch (error) {
                console.error('Error fetching or rendering sales chart:', error);
            }
        };

        // Initial chart render and event listener
        if (salesPeriodSelect) {
            fetchAndRenderSalesChart(salesPeriodSelect.value);

            salesPeriodSelect.addEventListener('change', (e) => {
                fetchAndRenderSalesChart(e.target.value);
            });
        } else {
            console.error('salesPeriodSelect element not found. Charting functionality might be affected.'); // Diagnostic log
        }
    };

    const initSalesModule = async () => {
        const salesTableBody = document.getElementById('sales-table-body');
        const modal = document.getElementById('sale-details-modal');
        const modalCloseBtn = document.getElementById('modal-close-btn');
        const modalDetailsContent = document.getElementById('modal-details-content');
        const modalLoader = document.getElementById('modal-loader');

        if (!salesTableBody || !modal) return;
        
        // --- Modal close logic ---
        const closeModal = () => { modal.style.display = 'none'; };
        modalCloseBtn.onclick = closeModal;
        modal.onclick = (e) => {
            if (e.target === modal) closeModal();
        };

        // --- Fetch and display sale details ---
        const showSaleDetails = async (saleId) => {
            modal.style.display = 'flex';
            modalLoader.style.display = 'block';
            modalDetailsContent.innerHTML = '';

            try {
                const response = await fetch(`api/get_sale_details.php?id=${saleId}&t=${new Date().getTime()}`);
                const details = await response.json();
                if (details.error) throw new Error(details.error);

                let itemsHtml = `
                    <p><strong>Transaction ID:</strong> ${details.transaction_token}</p>
                    <p><strong>Sold By:</strong> ${details.user_name}</p>
                    <p><strong>Date:</strong> ${new Date(details.created_at).toLocaleString()}</p>
                    <hr style="margin: 1rem 0; border-color: var(--border-color);">
                    <h4>Items Sold</h4>
                    <div class="table-container" style="margin-top: 1rem;">
                        <table>
                            <thead>
                                <tr><th>Item Name</th><th>Qty</th><th>Unit Price</th><th>Total</th></tr>
                            </thead>
                            <tbody>
                                ${details.items.map(item => `
                                    <tr>
                                        <td>${item.medicine_name}</td>
                                        <td>${item.quantity}</td>
                                        <td>${parseFloat(item.unit_price).toFixed(2)}</td>
                                        <td>${parseFloat(item.total).toFixed(2)}</td>
                                    </tr>
                                `).join('')}
                            </tbody>
                        </table>
                    </div>
                    <div style="text-align: right; margin-top: 1rem; font-size: 1.2rem;">
                        <p>Subtotal: ${parseFloat(details.subtotal).toFixed(2)}</p>
                        <p>Tax: ${parseFloat(details.total_tax).toFixed(2)}</p>
                        <p style="color: var(--accent-green); font-weight: bold;">Grand Total: ${parseFloat(details.grand_total).toFixed(2)}</p>
                    </div>
                `;
                modalDetailsContent.innerHTML = itemsHtml;

            } catch (error) {
                console.error('Error fetching sale details:', error);
                modalDetailsContent.innerHTML = '<p style="color: red;">Could not load sale details.</p>';
            } finally {
                modalLoader.style.display = 'none';
            }
        };

        // --- Delegated event listener for view buttons ---
        salesTableBody.addEventListener('click', (e) => {
            if (e.target.classList.contains('view-details-btn')) {
                e.preventDefault();
                const saleId = e.target.getAttribute('data-sale-id');
                if (saleId) showSaleDetails(saleId);
            }
        });
        
        // --- Fetch and render all sales ---
        try {
            const response = await fetch('api/get_sales.php?t=' + new Date().getTime());
            const sales = await response.json();
            if (sales.error) throw new Error(sales.error);

            salesTableBody.innerHTML = ''; // Clear loading message
            if (sales.length === 0) {
                salesTableBody.innerHTML = '<tr><td colspan="5" style="text-align: center;">No sales records found.</td></tr>';
                return;
            }

            sales.forEach(sale => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${sale.transaction_token.substring(0, 12).toUpperCase()}</td>
                    <td>${new Date(sale.created_at).toLocaleString()}</td>
                    <td>${sale.user_name}</td>
                    <td>${parseFloat(sale.grand_total).toFixed(2)}</td>
                    <td><a href="#" class="view-details-btn" data-sale-id="${sale.id}">View Details</a></td>
                `;
                salesTableBody.appendChild(row);
            });

        } catch (error) {
            console.error('Error fetching sales:', error);
            salesTableBody.innerHTML = '<tr><td colspan="5" style="color: red; text-align: center;">Error loading sales data.</td></tr>';
        }
    };
    
    const initPosModule = () => {
        // Load all items into the grid
        loadPosItems();
        
        // Initial render for cart
        renderCart();
        calculateTotals();

        // Search (with debouncing)
        let searchTimeout;
        const medicineSearchInput = document.getElementById('medicine-search');
        if (medicineSearchInput) {
            medicineSearchInput.addEventListener('keyup', () => {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    searchMedicine(medicineSearchInput.value);
                }, 300);
            });
        }

        // Cart Actions (delegated)
        const cartContainer = document.getElementById('cart-items-container');
        if (cartContainer) {
            cartContainer.addEventListener('change', (e) => {
                if (e.target.classList.contains('item-qty')) {
                    const batchId = parseInt(e.target.dataset.batchId);
                    const newQuantity = parseInt(e.target.value);
                    updateQty(batchId, newQuantity);
                }
            });

            cartContainer.addEventListener('click', (e) => {
                if (e.target.classList.contains('remove-from-cart-btn')) {
                    const batchId = parseInt(e.target.dataset.batchId);
                    removeFromCart(batchId);
                }
            });
        }
        
        // Checkout
        const checkoutBtn = document.getElementById('process-checkout');
        if (checkoutBtn) {
            checkoutBtn.addEventListener('click', processCheckout);
        }

        // --- CUSTOMER MANAGEMENT LOGIC ---
        const customerSearchInput = document.getElementById('customer-search');
        const customerResults = document.getElementById('customer-results');
        const selectedCustomerId = document.getElementById('selected-customer-id');
        const currentCustomerName = document.getElementById('current-customer-name');
        const addCustomerBtn = document.getElementById('add-customer-btn');
        const addCustomerModal = document.getElementById('add-customer-modal');
        const closeCustomerModal = document.getElementById('close-customer-modal');
        const newCustomerForm = document.getElementById('new-customer-form');

        let customerSearchTimeout;

        if (customerSearchInput) {
            customerSearchInput.addEventListener('keyup', () => {
                clearTimeout(customerSearchTimeout);
                const query = customerSearchInput.value.trim();
                if (query.length === 0) {
                    customerResults.style.display = 'none';
                    return;
                }
                
                customerSearchTimeout = setTimeout(async () => {
                    try {
                        const response = await fetch(`api/search_customers.php?q=${encodeURIComponent(query)}`);
                        const customers = await response.json();
                        
                        customerResults.innerHTML = '';
                        if (customers.length > 0) {
                            customerResults.style.display = 'block';
                            customers.forEach(c => {
                                const div = document.createElement('div');
                                div.style.padding = '0.5rem';
                                div.style.cursor = 'pointer';
                                div.style.borderBottom = '1px solid var(--border-color)';
                                div.innerHTML = `<strong>${c.name}</strong> <span style='font-size:0.8em; color:var(--text-secondary)'>${c.phone}</span>`;
                                div.addEventListener('click', () => {
                                    selectedCustomerId.value = c.id;
                                    currentCustomerName.textContent = c.name;
                                    customerSearchInput.value = ''; // Clear search
                                    customerResults.style.display = 'none';
                                });
                                div.addEventListener('mouseenter', () => div.style.background = 'var(--bg-dark)');
                                div.addEventListener('mouseleave', () => div.style.background = 'transparent');
                                customerResults.appendChild(div);
                            });
                        } else {
                            customerResults.style.display = 'none';
                        }
                    } catch (e) {
                        console.error("Customer search error:", e);
                    }
                }, 300);
            });

            // Hide results on click outside
            document.addEventListener('click', (e) => {
                if (!customerSearchInput.contains(e.target) && !customerResults.contains(e.target)) {
                    customerResults.style.display = 'none';
                }
            });
        }

        if (addCustomerBtn && addCustomerModal) {
            addCustomerBtn.addEventListener('click', () => {
                addCustomerModal.style.display = 'flex';
            });
            
            closeCustomerModal.addEventListener('click', () => {
                addCustomerModal.style.display = 'none';
            });
            
            newCustomerForm.addEventListener('submit', async (e) => {
                e.preventDefault();
                const formData = new FormData(newCustomerForm);
                const data = Object.fromEntries(formData.entries());
                
                try {
                    const response = await fetch('api/add_customer.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify(data)
                    });
                    const result = await response.json();
                    
                    if (result.success) {
                        alert(result.message);
                        addCustomerModal.style.display = 'none';
                        newCustomerForm.reset();
                        // Auto-select the new customer
                        selectedCustomerId.value = result.customer.id;
                        currentCustomerName.textContent = result.customer.name;
                    } else {
                        alert(result.message);
                    }
                } catch (e) {
                    alert('Error adding customer.');
                }
            });
        }

        // --- DISCOUNT LOGIC REMOVED ---
    };

    // --- POS & CART LOGIC ---
    const renderPosGridItems = (items) => {
        const itemGrid = document.getElementById('item-grid-display');
        if (!itemGrid) return;
        itemGrid.innerHTML = '';
        if (items.length === 0) {
            itemGrid.innerHTML = '<p>No medicine found.</p>';
            return;
        }

        const today = new Date();
        today.setHours(0, 0, 0, 0);

        items.forEach(item => {
            const itemCard = document.createElement('div');
            itemCard.className = 'medicine-card';
            
            // Logic for Color Coding
            const expiryDateStr = item.expiry_date; // YYYY-MM-DD
            const parts = expiryDateStr.split('-');
            const expiryDate = new Date(parts[0], parts[1] - 1, parts[2]); // Month is 0-indexed
            expiryDate.setHours(0, 0, 0, 0);

            let statusLabel = '';
            let cardClass = '';

            if (expiryDate <= today) {
                // A. CRITICAL (Red) - Expired
                cardClass = 'card-expired';
                statusLabel = '<span class="status-label label-expired">EXPIRED</span>';
            } else if (parseInt(item.quantity) <= 10) {
                // B. WARNING (Yellow) - Low Stock
                cardClass = 'card-low-stock';
                statusLabel = '<span class="status-label label-low-stock">LOW STOCK</span>';
            }

            if (cardClass) {
                itemCard.classList.add(cardClass);
            }

            itemCard.innerHTML = `
                ${statusLabel}
                <h4>${item.name}</h4>
                <p class="medicine-type">${item.medicine_type || 'Tablet'}</p>
                <p>Batch: ${item.batch_number}</p>
                <p>Exp: ${item.expiry_date}</p>
                <p class="price">Price: ${item.selling_price.toFixed(2)}</p>
                <p>Stock: ${item.quantity}</p>
            `;
            itemCard.addEventListener('click', () => addToCart(item));
            itemGrid.appendChild(itemCard);
        });
    };

    const loadPosItems = async () => {
        console.log('loadPosItems called'); // Diagnostic log
        try {
            // Add cache-busting parameter
            const response = await fetch('api/get_all_medicines.php?t=' + new Date().getTime());
            const items = await response.json();
            if (items.error) throw new Error(items.error);
            renderPosGridItems(items);
            console.log('loadPosItems: Successfully loaded and rendered items.'); // Diagnostic log
        } catch (error) {
            console.error('Error loading POS items:', error);
            const itemGrid = document.getElementById('item-grid-display');
            if(itemGrid) itemGrid.innerHTML = '<p style="color:red;">Could not load items.</p>';
        }
    };

    const searchMedicine = async (query) => {
        if (query.length < 2) {
            if (query.length === 0) loadPosItems();
            return;
        }
        try {
            // Add cache-busting parameter
            const response = await fetch(`api/search_medicine.php?q=${encodeURIComponent(query)}&t=` + new Date().getTime());
            const results = await response.json();
            renderPosGridItems(results);
        } catch (error) {
            console.error('Search Error:', error);
        }
    };

    const addToCart = (item) => {
        const existingItem = appState.cart.find(cartItem => cartItem.batch_id === item.batch_id);
        if (existingItem) {
            // Check against total stock (item.quantity)
            if (existingItem.qty_in_cart < existingItem.quantity) {
                existingItem.qty_in_cart++;
            } else {
                alert(`Cannot add more of ${item.name}. Max stock available.`);
            }
        } else {
            // Add item with a new `qty_in_cart` property, preserving original `quantity` as max stock
            appState.cart.push({ ...item, qty_in_cart: 1 });
        }
        renderCart();
        calculateTotals();
    };
    
    const updateQty = (batchId, newQuantity) => {
        const itemInCart = appState.cart.find(cartItem => cartItem.batch_id === batchId);
        if (!itemInCart) return;

        // Validate against the total available stock (itemInCart.quantity)
        if (newQuantity > itemInCart.quantity) {
             alert(`Cannot set quantity for ${itemInCart.name} above available stock of ${itemInCart.quantity}.`);
             renderCart(); // Reset input to previous valid value
             return;
        }
        
        if (newQuantity > 0) {
            itemInCart.qty_in_cart = newQuantity;
        } else {
            // Remove if quantity is 0 or less
            appState.cart = appState.cart.filter(item => item.batch_id !== batchId);
        }
        renderCart();
        calculateTotals();
    };

    const calculateTotals = () => {
        const subtotalEl = document.getElementById('subtotal');
        const totalTaxEl = document.getElementById('total-tax');
        const grandTotalEl = document.getElementById('grand-total');

        if (!subtotalEl || !totalTaxEl || !grandTotalEl) return;

        let subtotal = 0, totalTax = 0;
        appState.cart.forEach(item => {
            const itemTotal = item.selling_price * item.qty_in_cart;
            const itemTax = itemTotal * (item.tax_rate / 100);
            subtotal += itemTotal;
            totalTax += itemTax;
        });

        // Simplified Calculation: Subtotal + Tax
        const grandTotal = subtotal + totalTax;

        subtotalEl.textContent = subtotal.toFixed(2);
        totalTaxEl.textContent = totalTax.toFixed(2);
        grandTotalEl.textContent = grandTotal.toFixed(2);
    };

    const removeFromCart = (batchId) => {
        appState.cart = appState.cart.filter(item => item.batch_id !== batchId);
        renderCart();
        calculateTotals();
    };

    const renderCart = () => {
        const cartContainer = document.getElementById('cart-items-container');
        if (!cartContainer) return;
        
        cartContainer.innerHTML = '<p>Cart is empty</p>';
        if (appState.cart.length === 0) return;

        cartContainer.innerHTML = '';
        appState.cart.forEach(item => {
            const cartItemDiv = document.createElement('div');
            cartItemDiv.className = 'cart-item';
            // Use item.qty_in_cart for value and item.quantity for max
            cartItemDiv.innerHTML = `
                <span class="item-name">${item.name} (x${item.qty_in_cart})</span>
                <input type="number" value="${item.qty_in_cart}" min="1" max="${item.quantity}" class="item-qty" data-batch-id="${item.batch_id}">
                <span class="item-price">${(item.selling_price * item.qty_in_cart).toFixed(2)}</span>
                <button class="remove-from-cart-btn" data-batch-id="${item.batch_id}">&times;</button>
            `;
            cartContainer.appendChild(cartItemDiv);
        });
    };

    // --- SECURITY & CHECKOUT ---
    const resetInactivityTimer = () => {
        clearTimeout(appState.inactivityTimer);
        appState.inactivityTimer = setTimeout(() => {
            alert('You have been logged out due to inactivity.');
            window.location.href = 'logout.php';
        }, appState.timeOut);
    };
    
    const processCheckout = async () => {
        if (appState.cart.length === 0) {
            alert('Cannot checkout with an empty cart.');
            return;
        }

        const customerId = document.getElementById('selected-customer-id').value;
        const shouldPrint = document.getElementById('print-receipt-toggle').checked;

        // Map `qty_in_cart` back to `quantity` for the backend API
        const cartForApi = appState.cart.map(item => {
            const apiItem = { ...item };
            apiItem.quantity = apiItem.qty_in_cart;
            delete apiItem.qty_in_cart; 
            return apiItem;
        });

        try {
            const response = await fetch('api/process_checkout.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify({
                    cart: cartForApi,
                    customer_id: customerId,
                    subtotal: document.getElementById('subtotal').textContent,
                    tax: document.getElementById('total-tax').textContent,
                    total: document.getElementById('grand-total').textContent
                })
            });
            const result = await response.json();
            if (result.success) {
                alert(`Checkout successful!`);
                
                if (shouldPrint) {
                    window.open(`api/generate_receipt.php?sale_id=${result.sale_id}`, '_blank'); // Auto-print logic handled in receipt
                } else if (confirm("Do you want to print a receipt?")) {
                    window.open(`api/generate_receipt.php?sale_id=${result.sale_id}`, '_blank');
                }

                // Reset Cart & UI
                appState.cart = [];
                renderCart();
                document.getElementById('selected-customer-id').value = 0;
                document.getElementById('current-customer-name').textContent = 'Walk-in Customer';
                
                calculateTotals();
                loadPosItems(); // Refresh the grid to show updated stock
            } else {
                throw new Error(result.message || 'Checkout failed.');
            }
        } catch (error) {
            console.error('Checkout Error:', error);
            alert(`Error during checkout: ${error.message}`);
        }
    };

    // --- GLOBAL EVENT LISTENERS ---
    const mainContent = document.getElementById('main-content');
    const navLinks = document.querySelectorAll('.nav-link');
    
    navLinks.forEach(link => {
        link.addEventListener('click', (e) => {
            if (link.getAttribute('href') === 'logout.php') return;
            e.preventDefault();
            navLinks.forEach(l => l.classList.remove('active'));
            link.classList.add('active');
            const module = link.getAttribute('data-module');
            if (module) loadModule(module);
        });
    });

    ['mousemove', 'keypress', 'scroll', 'click'].forEach(event => 
        window.addEventListener(event, resetInactivityTimer)
    );

    // --- INITIALIZATION ---
    resetInactivityTimer();
    // Check which page is active on load. Default to 'pos' if none.
    const activeLink = document.querySelector('.sidebar-nav a.active') || document.querySelector('.sidebar-nav a[data-module="pos"]');
    const initialModule = activeLink.getAttribute('data-module');
    
    // Load the initial module. This will also trigger the correct initializer.
    if (initialModule) {
       loadModule(initialModule);
    } else {
       // Fallback if no module is active
       initPosModule();
    }
});