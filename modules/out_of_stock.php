<div class="module-container">
    <h2>Out of Stock Medicines</h2>
    
    <div class="module-header">
        <p>View all medicines that are currently out of stock.</p>
    </div>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Medicine Name</th>
                    <th>Batch #</th>
                    <th>Expiry Date</th>
                    <th>Quantity on Hand</th>
                    <th>Selling Price</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="out-of-stock-table-body">
                <!-- Data will be loaded by app.js -->
                <tr><td colspan="5" style="text-align: center;">Loading out of stock data...</td></tr>
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
</style>