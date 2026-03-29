<div class="module-container">
    <h2>Purchases</h2>

    <div class="module-header">
        <p>Record new purchases and review purchase history.</p>
        <button id="add-new-purchase-btn" class="btn btn-primary"><i class="fas fa-plus"></i> Add New Purchase</button>
    </div>

    <div id="new-purchase-form-container" class="form-card mb-3" style="display: none; margin-bottom: 2rem;">
        <h3>Add New Purchase</h3>
        <div id="form-message-container" class="mb-3"></div>
        <form id="add-purchase-form">
            <div class="form-group">
                <label for="supplier_id">Supplier</label>
                <select id="supplier_id" name="supplier_id" class="form-control" required>
                    <!-- Options populated by JS -->
                    <option value="">Select Supplier</option>
                </select>
            </div>
            <div class="form-group">
                <label for="purchase_date">Purchase Date</label>
                <input type="date" id="purchase_date" name="purchase_date" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
            </div>

            <h4>Purchase Items</h4>
            <div id="purchase-items-list">
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
            </div>
            <button type="button" id="add-item-btn" class="btn btn-secondary btn-sm mb-3">Add Another Item</button>

            <div class="form-actions">
                <button type="submit" id="submit-purchase-btn" class="btn btn-success">Record Purchase</button>
                <button type="button" id="cancel-purchase-btn" class="btn btn-secondary">Cancel</button>
            </div>
        </form>
    </div>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Purchase ID</th>
                    <th>Supplier</th>
                    <th>Purchase Date</th>
                    <th>Total Amount</th>
                    <th>Added By</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="purchases-table-body">
                <tr><td colspan="6" style="text-align: center;">Loading purchase data...</td></tr>
            </tbody>
        </table>
    </div>
</div>

<!-- =================================================================
     PURCHASE DETAILS MODAL
     ================================================================= -->
<div id="purchase-details-modal" class="modal-backdrop" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Purchase Details</h3>
            <button id="purchase-modal-close-btn" class="modal-close">&times;</button>
        </div>
        <div class="modal-body">
            <div id="purchase-modal-loader" style="display: none;">Loading details...</div>
            <div id="purchase-modal-details-content">
                <!-- Details will be populated by JavaScript -->
            </div>
        </div>
    </div>
</div>

<style>
.module-container h2 {
    margin-bottom: 1rem;
}
.module-header {
    margin-bottom: 2rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.module-header p {
    color: var(--text-secondary);
}
.table-container {
    background-color: var(--card-dark);
    border: 1px solid var(--border-color);
    border-radius: 8px;
    overflow: hidden;
}
table {
    width: 100%;
    border-collapse: collapse;
}
th, td {
    padding: 1rem;
    text-align: left;
    border-bottom: 1px solid var(--border-color);
}
thead {
    background-color: #1c222b;
}
tbody tr:last-child td {
    border-bottom: none;
}
tbody tr:hover {
    background-color: #1c222b;
}
</style>
