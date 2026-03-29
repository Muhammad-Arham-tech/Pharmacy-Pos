<div class="module-container">
    <h2>Manage Suppliers</h2>
    <p>Add a new supplier or edit an existing one.</p>
</div>

<div class="main-grid">
    <div class="form-container">
        <form id="supplier-form" class="content-form">
            <h4 id="supplier-form-title">Add New Supplier</h4>
            <input type="hidden" id="supplier_id" name="id">
            
            <div class="form-group">
                <label for="supplier_name">Supplier Name</label>
                <input type="text" id="supplier_name" name="name" required>
            </div>
            <div class="form-group">
                <label for="supplier_contact">Contact Person</label>
                <input type="text" id="supplier_contact" name="contact_person">
                <small>This field will be encrypted.</small>
            </div>
            <div class="form-group">
                <label for="supplier_phone">Phone</label>
                <input type="text" id="supplier_phone" name="phone">
                 <small>This field will be encrypted.</small>
            </div>
             <div class="form-group">
                <label for="supplier_email">Email</label>
                <input type="email" id="supplier_email" name="email">
                 <small>This field will be encrypted.</small>
            </div>

            <div class="form-buttons">
                <button type="submit" id="supplier-submit-btn" class="btn btn-primary">Add Supplier</button>
                <button type="button" id="supplier-cancel-btn" class="btn" style="display: none;">Cancel</button>
            </div>
        </form>
         <div id="form-message-container" style="margin-top: 1rem;"></div>
    </div>

    <div class="table-container">
         <h4>Existing Suppliers</h4>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="suppliers-table-body">
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
.form-group input { width: 100%; padding: 0.75rem; background-color: var(--bg-dark); border: 1px solid var(--border-color); border-radius: 6px; color: var(--text-primary); }
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
</style>
