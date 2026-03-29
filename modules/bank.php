<div class="module-container">
    <h2>Bank Transactions</h2>
    <p>Track bank deposits, withdrawals, and view the transaction ledger.</p>
</div>

<div class="main-grid">
    <div class="form-container">
        <h4>Add New Transaction</h4>
        <form id="bank-transaction-form" class="content-form">
            <div class="form-group">
                <label for="t_date">Transaction Date</label>
                <input type="datetime-local" id="t_date" name="t_date" required>
            </div>
            <div class="form-group">
                <label for="t_type">Transaction Type</label>
                <select id="t_type" name="t_type">
                    <option value="credit">Credit (Deposit)</option>
                    <option value="debit">Debit (Withdrawal)</option>
                </select>
            </div>
             <div class="form-group">
                <label for="t_amount">Amount</label>
                <input type="number" step="0.01" id="t_amount" name="t_amount" required>
            </div>
            <div class="form-group">
                <label for="t_desc">Description</label>
                <input type="text" id="t_desc" name="t_desc" required>
            </div>
            <button type="submit" class="btn btn-primary">Add Transaction</button>
        </form>
    </div>

    <div class="table-container">
         <h4>Transaction Ledger</h4>
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Description</th>
                    <th>Debit</th>
                    <th>Credit</th>
                    <th>Balance</th>
                </tr>
            </thead>
            <tbody id="bank-ledger-body">
                <!-- Transactions will be loaded here dynamically by app.js -->
            </tbody>
        </table>
    </div>
</div>

<style>
.module-container {
    margin-bottom: 2rem;
}
.main-grid {
    display: grid;
    grid-template-columns: 1fr 2fr;
    gap: 2rem;
}
.form-container h4, .table-container h4 {
    margin-bottom: 1rem;
    color: var(--text-secondary);
}
.content-form {
    background-color: var(--card-dark);
    padding: 1.5rem;
    border-radius: 8px;
    border: 1px solid var(--border-color);
}
.form-group {
    margin-bottom: 1rem;
}
.form-group:last-child {
    margin-bottom: 0;
}
.form-group label {
    display: block;
    margin-bottom: 0.5rem;
}
.form-group input, .form-group select {
    width: 100%;
    padding: 0.75rem;
    background-color: var(--bg-dark);
    border: 1px solid var(--border-color);
    border-radius: 6px;
    color: var(--text-primary);
}
.table-container {
    background-color: var(--card-dark);
    border: 1px solid var(--border-color);
    border-radius: 8px;
    padding: 1.5rem;
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
</style>