<div class="module-container">
    <h2>Stock / Inventory</h2>
    
    <div class="module-header">
        <p>View and manage current stock levels, batches, and expiry dates.</p>
        <div class="controls-container">
            <div class="search-bar" style="width: 300px; margin-right: 1rem;">
                <input type="text" id="stock-search" placeholder="Search by name or batch number...">
            </div>
            <div class="form-check">
                <input type="checkbox" id="show-out-of-stock" style="margin-right: 0.5rem;">
                <label for="show-out-of-stock">Show Out of Stock Only</label>
            </div>
        </div>
    </div>
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Medicine Name</th>
                    <th>Type</th>
                    <th>Batch #</th>
                    <th>Expiry Date</th>
                    <th>Quantity on Hand</th>
                    <th>Selling Price</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="stock-table-body">
                <!-- Data will be loaded by app.js -->
                <tr><td colspan="6" style="text-align: center;">Loading stock data...</td></tr>
            </tbody>
        </table>
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
.status-ok { color: #28a745; }
.status-warning { color: #ffc107; }
.status-danger { color: #dc3545; }
</style>
