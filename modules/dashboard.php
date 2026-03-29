<div class="dashboard-container">
    <h2>Dashboard</h2>
    <p>A summary of today's activity and current inventory status.</p>
    
    <div class="stats-grid">
        <div class="stat-card">
            <h4>Today's Sales</h4>
            <p id="stats-todays-sales">Loading...</p>
        </div>
        <div class="stat-card">
            <h4>Items Sold Today</h4>
            <p id="stats-items-sold">Loading...</p>
        </div>
        <div class="stat-card">
            <h4>Expiring Soon</h4>
            <p id="stats-expiring-soon">Loading...</p>
        </div>
         <div class="stat-card">
            <h4>Out of Stock</h4>
            <p id="stats-out-of-stock">Loading...</p>
        </div>
    </div>

    <div class="charts-section">
        <h3>Sales Overview</h3>
        <div class="sales-period-selector">
            <label for="sales-period">View Sales By:</label>
            <select id="sales-period">
                <option value="daily">Daily</option>
                <option value="weekly">Weekly</option>
                <option value="monthly" selected>Monthly</option>
                <option value="yearly">Yearly</option>
            </select>
        </div>
        <div class="chart-container" style="position: relative; height:40vh; width:80vw; max-width: 900px;">
            <canvas id="salesChart"></canvas>
        </div>
    </div>
</div>

<style>
.dashboard-container h2 {
    margin-bottom: 1rem;
}
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
    gap: 1.5rem;
    margin-top: 2rem;
}
.stat-card {
    background-color: var(--card-dark);
    padding: 1.5rem;
    border-radius: 8px;
    border: 1px solid var(--border-color);
}
.stat-card h4 {
    color: var(--accent-green);
    margin-bottom: 0.5rem;
}
.stat-card p {
    font-size: 1.8rem;
    font-weight: bold;
}
.charts-section {
    margin-top: 3rem;
    background-color: var(--card-dark);
    padding: 1.5rem;
    border-radius: 8px;
    border: 1px solid var(--border-color);
}
.charts-section h3 {
    margin-bottom: 1.5rem;
    color: var(--text-color);
}
.sales-period-selector {
    margin-bottom: 1rem;
}
.sales-period-selector label {
    margin-right: 0.5rem;
    color: var(--text-primary);
}
.sales-period-selector select {
    padding: 0.5rem 0.8rem;
    border-radius: 4px;
    border: 1px solid var(--border-color);
    background-color: var(--bg-dark);
    color: var(--text-primary);
}
</style>
