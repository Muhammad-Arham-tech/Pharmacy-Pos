<div class="module-container">
    <h2>Sales History</h2>
    <p>A log of all completed transactions.</p>
</div>

<div class="table-container">
    <table>
        <thead>
            <tr>
                <th>Transaction ID</th>
                <th>Date</th>
                <th>Cashier</th>
                <th>Total Amount</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody id="sales-table-body">
            <!-- Rows will be populated dynamically by JavaScript -->
            <tr>
                <td colspan="5" style="text-align: center;">Loading sales data...</td>
            </tr>
        </tbody>
    </table>
</div>

<!-- =================================================================
     SALE DETAILS MODAL
     ================================================================= -->
<div id="sale-details-modal" class="modal-backdrop" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Sale Details</h3>
            <button id="modal-close-btn" class="modal-close">&times;</button>
        </div>
        <div class="modal-body">
            <div id="modal-loader" style="display: none;">Loading details...</div>
            <div id="modal-details-content">
                <!-- Details will be populated by JavaScript -->
            </div>
        </div>
    </div>
</div>


<style>
.module-container h2 {
    margin-bottom: 2rem;
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
.view-details-btn {
    cursor: pointer;
}
</style>