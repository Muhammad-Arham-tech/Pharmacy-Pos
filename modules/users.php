<div class="module-container">
    <h2>User Management</h2>
    <p>Add a new user or edit an existing one.</p>
</div>

<div class="main-grid">
    <div class="form-container">
        <form id="user-form" class="content-form">
            <h4 id="user-form-title">Add New User</h4>
            <input type="hidden" id="user_id" name="id">
            
            <div class="form-group">
                <label for="user_username">Username</label>
                <input type="text" id="user_username" name="username" required>
            </div>
            <div class="form-group">
                <label for="user_full_name">Full Name</label>
                <input type="text" id="user_full_name" name="full_name">
                <small>This field will be encrypted.</small>
            </div>
            <div class="form-group">
                <label for="user_password">Password</label>
                <input type="password" id="user_password" name="password">
                <small id="password-help-text">Required for new users. Optional for edits.</small>
            </div>
            <div class="form-group">
                <label for="user_role">Role</label>
                <select id="user_role" name="role" required>
                    <option value="cashier">Cashier</option>
                    <option value="pharmacist">Pharmacist</option>
                    <option value="admin">Admin</option>
                </select>
            </div>
             <div class="form-group">
                <label for="user_is_active">Status</label>
                <select id="user_is_active" name="is_active" required>
                    <option value="1">Active</option>
                    <option value="0">Inactive</option>
                </select>
            </div>

            <div class="form-buttons">
                <button type="submit" id="user-submit-btn" class="btn btn-primary">Add User</button>
                <button type="button" id="user-cancel-btn" class="btn" style="display: none;">Cancel</button>
            </div>
        </form>
         <div id="form-message-container" style="margin-top: 1rem;"></div>
    </div>

    <div class="table-container">
         <h4>Existing Users</h4>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Username</th>
                    <th>Full Name</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="users-table-body">
                <!-- Rows will be populated by JavaScript -->
            </tbody>
        </table>
    </div>
</div>

<style>
.module-container { margin-bottom: 2rem; }
.main-grid { display: grid; grid-template-columns: 1fr 2fr; gap: 2rem; }
.form-container h4, .table-container h4 { margin-bottom: 1rem; color: var(--text-secondary); }
.content-form { background-color: var(--card-dark); padding: 1.5rem; border-radius: 8px; border: 1px solid var(--border-color); }
.form-group { margin-bottom: 1rem; }
.form-group label { display: block; margin-bottom: 0.5rem; }
.form-group input, .form-group select { width: 100%; padding: 0.75rem; background-color: var(--bg-dark); border: 1px solid var(--border-color); border-radius: 6px; color: var(--text-primary); }
.form-group small { font-size: 0.8rem; color: var(--text-secondary); margin-top: 0.25rem; display: block;}
.form-buttons { display: flex; gap: 1rem; margin-top: 1.5rem; }
.form-buttons .btn { flex-grow: 1; }
.table-container { background-color: var(--card-dark); border: 1px solid var(--border-color); border-radius: 8px; padding: 1.5rem; }
table { width: 100%; border-collapse: collapse; }
th, td { padding: 1rem; text-align: left; border-bottom: 1px solid var(--border-color); }
thead { background-color: #1c222b; }
tbody tr:last-child td { border-bottom: none; }
.edit-btn { cursor: pointer; }
.form-message { padding: 0.75rem; border-radius: 6px; border: 1px solid; }
.form-message.success { background-color: rgba(0, 200, 81, 0.1); border-color: var(--accent-green); color: var(--accent-green); }
.form-message.error { background-color: rgba(255, 82, 82, 0.1); border-color: #ff5252; color: #ff5252; }
[class^="role-"] { padding: 3px 8px; border-radius: 5px; font-size: 0.8rem; font-weight: 500; }
.role-admin { background-color: rgba(220, 53, 69, 0.2); color: #dc3545; }
.role-pharmacist { background-color: rgba(255, 193, 7, 0.2); color: #ffc107; }
.role-cashier { background-color: rgba(0, 123, 255, 0.2); color: #007bff; }
.status-ok { color: #28a745; }
.status-danger { color: #dc3545; }
</style>
