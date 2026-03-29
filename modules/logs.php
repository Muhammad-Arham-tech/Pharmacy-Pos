<div class="module-container">
    <h2>Security Logs</h2>
    
    <div class="module-header">
        <p>Review important security events across the application.</p>
        <div class="search-bar">
            <input type="text" id="log-search" placeholder="Filter by user or event type...">
        </div>
    </div>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Timestamp</th>
                    <th>User</th>
                    <th>IP Address</th>
                    <th>Event Type</th>
                    <th>Description</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>2025-12-22 06:50:10 PM</td>
                    <td>admin</td>
                    <td>127.0.0.1</td>
                    <td><span class="event-success">LOGIN_SUCCESS</span></td>
                    <td>User 'admin' logged in successfully.</td>
                </tr>
                 <tr>
                    <td>2025-12-22 06:49:55 PM</td>
                    <td>someone</td>
                    <td>192.168.1.10</td>
                    <td><span class="event-fail">LOGIN_FAIL</span></td>
                    <td>Failed login attempt for user 'someone'.</td>
                </tr>
                 <tr>
                    <td>2025-12-22 07:15:00 PM</td>
                    <td>admin</td>
                    <td>127.0.0.1</td>
                    <td><span class="event-info">DATA_ACCESS</span></td>
                    <td>User accessed the user management page.</td>
                </tr>
                <!-- More rows would be populated from the database -->
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
[class^="event-"] {
    padding: 3px 8px;
    border-radius: 5px;
    font-size: 0.8rem;
    font-weight: 500;
}
.event-success { background-color: rgba(40, 167, 69, 0.2); color: #28a745; }
.event-fail { background-color: rgba(220, 53, 69, 0.2); color: #dc3545; }
.event-info { background-color: rgba(23, 162, 184, 0.2); color: #17a2b8; }
</style>