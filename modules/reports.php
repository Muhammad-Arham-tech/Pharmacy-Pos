<div class="module-container">
    <h2>Reports</h2>
    <p>Generate sales, stock, and financial reports.</p>
</div>

<div class="reports-grid">
    <div class="report-card">
        <h4>Sales Reports</h4>
        <form id="daily-sales-form" class="report-form">
            <div class="form-group">
                <label>Report Type</label>
                <select name="report_type">
                    <option value="daily_sales">Daily Sales Summary</option>
                </select>
            </div>
            <div class="form-group">
                <label>Select Date</label>
                <input type="date" name="date" value="<?php echo date('Y-m-d'); ?>" required>
            </div>
            <button type="submit" class="btn btn-primary btn-block">Generate</button>
        </form>
    </div>

    <div class="report-card">
        <h4>Inventory Reports</h4>
        <form id="inventory-report-form" class="report-form">
            <div class="form-group">
                <label>Report Type</label>
                <select name="report_type">
                    <option value="stock_levels">Current Stock Levels</option>
                    <option value="expiring_soon">Expiring Soon</option>
                </select>
            </div>
            <div class="form-group" id="expiry-threshold-group">
                <label>Expiry Threshold (Days)</label>
                <input type="number" name="threshold_days" value="30">
            </div>
            <button type="submit" class="btn btn-primary btn-block">Generate</button>
        </form>
    </div>

    <div class="report-card">
        <h4>Financial Reports</h4>
         <form class="report-form">
            <p style="color: var(--text-secondary);">Financial reports are not yet available in this demo.</p>
        </form>
    </div>
</div>

<div id="report-display-area" style="margin-top: 2rem;">
    <!-- Generated reports will be displayed here -->
</div>


<style>
.module-container {
    margin-bottom: 2rem;
}
.reports-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
    gap: 2rem;
}
.report-card {
    background-color: var(--card-dark);
    border: 1px solid var(--border-color);
    border-radius: 8px;
    padding: 1.5rem;
}
.report-card h4 {
    color: var(--accent-green);
    margin-bottom: 1.5rem;
}
.report-form .form-group {
    margin-bottom: 1rem;
}
.report-form .form-group label {
    display: block;
    margin-bottom: 0.5rem;
    color: var(--text-secondary);
}
.report-form .form-group input, .report-form .form-group select {
    width: 100%;
    padding: 0.75rem;
    background-color: var(--bg-dark);
    border: 1px solid var(--border-color);
    border-radius: 6px;
    color: var(--text-primary);
}
</style>
