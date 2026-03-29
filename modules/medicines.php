<div class="add-medicine-container">
    <h2>Add New Medicine</h2>
    <p>Add medicine details and its initial stock batch information.</p>

    <!-- This container will show success or error messages -->
    <div id="form-message-container"></div>

    <form id="add-medicine-form" class="content-form">
        <div class="form-section">
            <h3>Medicine Details</h3>
            <div class="form-grid">
                <div class="form-group">
                    <label for="name">Medicine Name</label>
                    <input type="text" id="name" name="name" required>
                </div>
                <div class="form-group">
                    <label for="medicine_type">Medicine Type</label>
                    <select id="medicine_type" name="medicine_type" required>
                        <option value="">Select Type</option>
                        <option value="Tablet">Tablet</option>
                        <option value="Syrup">Syrup</option>
                        <option value="Injection">Injection</option>
                        <option value="Capsule">Capsule</option>
                        <option value="Drops">Drops</option>
                        <option value="Ointment">Ointment</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="barcode">Barcode</label>
                    <input type="text" id="barcode" name="barcode">
                </div>
                <div class="form-group">
                    <label for="strength">Strength (e.g., 500mg)</label>
                    <input type="text" id="strength" name="strength">
                </div>
                <div class="form-group">
                    <label for="category_id">Category</label>
                    <select id="category_id" name="category_id">
                        <option value="">Loading...</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="manufacturer_id">Manufacturer</label>
                    <select id="manufacturer_id" name="manufacturer_id">
                         <option value="">Loading...</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="generic_salt_id">Generic Salt</label>
                    <select id="generic_salt_id" name="generic_salt_id">
                         <option value="">Loading...</option>
                    </select>
                </div>
                 <div class="form-group">
                    <label for="requires_prescription">Requires Prescription?</label>
                    <select id="requires_prescription" name="requires_prescription">
                        <option value="0">No</option>
                        <option value="1">Yes</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="form-section">
            <h3>Initial Stock Batch</h3>
             <div class="form-grid">
                <div class="form-group">
                    <label for="batch_number">Batch Number</label>
                    <input type="text" id="batch_number" name="batch_number" required>
                </div>
                <div class="form-group">
                    <label for="expiry_date">Expiry Date</label>
                    <input type="date" id="expiry_date" name="expiry_date" required>
                </div>
                <div class="form-group">
                    <label for="quantity">Quantity</label>
                    <input type="number" id="quantity" name="quantity" required min="0">
                </div>
                <div class="form-group">
                    <label for="mrp">MRP (Max Retail Price)</label>
                    <input type="number" id="mrp" name="mrp" step="0.01" required>
                </div>
                 <div class="form-group">
                    <label for="cost_price">Cost Price (per unit)</label>
                    <input type="number" id="cost_price" name="cost_price" step="0.01" required>
                </div>
                <div class="form-group">
                    <label for="selling_price">Selling Price (per unit)</label>
                    <input type="number" id="selling_price" name="selling_price" step="0.01" required>
                </div>
                 <div class="form-group">
                    <label for="tax_rate">Tax Rate (%)</label>
                    <input type="number" id="tax_rate" name="tax_rate" step="0.01" value="5.00" required>
                </div>
            </div>
        </div>
        
        <!-- MODIFIED: Button now has a unique ID and is type="button" -->
        <button type="button" id="add-medicine-btn" class="btn btn-primary">Add Medicine to Stock</button>
    </form>
</div>

<style>
.content-form { margin-top: 2rem; background-color: var(--card-dark); padding: 1.5rem; border-radius: 8px; border: 1px solid var(--border-color); }
.form-section { margin-bottom: 2rem; }
.form-section:last-child { margin-bottom: 0; }
.form-section h3 { color: var(--accent-green); margin-bottom: 1.5rem; border-bottom: 1px solid var(--border-color); padding-bottom: 0.75rem; }
.form-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 1.5rem; }
.form-group label { display: block; margin-bottom: 0.5rem; color: var(--text-secondary); }
.form-group input, .form-group select { width: 100%; padding: 0.75rem; background-color: var(--bg-dark); border: 1px solid var(--border-color); border-radius: 6px; color: var(--text-primary); font-size: 1rem; }
.form-message { padding: 1rem; border-radius: 6px; margin-bottom: 1.5rem; border: 1px solid; }
.form-message.success { background-color: rgba(0, 200, 81, 0.1); border-color: var(--accent-green); color: var(--accent-green); }
.form-message.error { background-color: rgba(255, 82, 82, 0.1); border-color: #ff5252; color: #ff5252; }
</style>