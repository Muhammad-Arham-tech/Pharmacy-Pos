<div class="module-container">
    <h2>Settings</h2>
    <p>Manage global application settings.</p>

    <?php if (isset($_GET['status']) && $_GET['status'] === 'success'): ?>
        <div style="background-color: rgba(0, 200, 81, 0.1); border: 1px solid var(--accent-green); color: var(--accent-green); padding: 1rem; border-radius: 6px; margin-bottom: 1.5rem;">
            Settings saved successfully!
        </div>
    <?php endif; ?>
</div>

<!-- 1. Form Action & Method -->
<form class="content-form" action="settings.php" method="POST">
    <div class="form-section">
        <h3>Store Information</h3>
        <div class="form-grid">
            <div class="form-group">
                <label for="store_name">Store Name</label>
                <input type="text" id="store_name" name="store_name" value="<?php echo htmlspecialchars($settings['store_name'] ?? 'Med-Quick'); ?>">
            </div>
            <div class="form-group">
                <label for="store_address">Address</label>
                <input type="text" id="store_address" name="store_address" value="<?php echo htmlspecialchars($settings['store_address'] ?? '24/7, Down town, Health City'); ?>">
            </div>
            <div class="form-group">
                <label for="store_phone">Contact Phone</label>
                <input type="text" id="store_phone" name="store_phone" value="<?php echo htmlspecialchars($settings['store_phone'] ?? '+1 (555) 765-4321'); ?>">
            </div>
            <div class="form-group">
                <label for="store_email">Public Email</label>
                <input type="email" id="store_email" name="store_email" value="<?php echo htmlspecialchars($settings['store_email'] ?? 'contact@medquick.com'); ?>">
            </div>
        </div>
    </div>

    <div class="form-section">
        <h3>Financial Settings</h3>
         <div class="form-grid">
            <div class="form-group">
                <label for="currency_symbol">Currency Symbol</label>
                <input type="text" id="currency_symbol" name="currency_symbol" value="<?php echo htmlspecialchars($settings['currency_symbol'] ?? '$'); ?>">
            </div>
            <div class="form-group">
                <label for="default_tax_rate">Default Tax Rate (%)</label>
                <input type="number" step="0.01" id="default_tax_rate" name="default_tax_rate" value="<?php echo htmlspecialchars($settings['default_tax_rate'] ?? '5.00'); ?>">
            </div>
        </div>
    </div>

     <div class="form-section">
        <h3>Security Settings</h3>
         <div class="form-grid">
            <div class="form-group">
                <label for="session_timeout">Inactivity Logout Time (minutes)</label>
                <input type="number" id="session_timeout" name="session_timeout" value="<?php echo htmlspecialchars($settings['session_timeout'] ?? '30'); ?>">
            </div>
        </div>
    </div>
    
    <!-- Button name set for PHP check -->
    <button type="submit" name="save_settings" class="btn btn-primary">Save Settings</button>
</form>

<style>
.module-container {
    margin-bottom: 2rem;
}
.content-form {
    background-color: var(--card-dark);
    padding: 1.5rem;
    border-radius: 8px;
    border: 1px solid var(--border-color);
}
.form-section {
    margin-bottom: 2rem;
}
.form-section:last-child {
    margin-bottom: 1rem;
}
.form-section h3 {
    color: var(--accent-green);
    margin-bottom: 1.5rem;
    border-bottom: 1px solid var(--border-color);
    padding-bottom: 0.75rem;
}
.form-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 1.5rem;
}
.form-group label {
    display: block;
    margin-bottom: 0.5rem;
    color: var(--text-secondary);
}
.form-group input {
    width: 100%;
    padding: 0.75rem;
    background-color: var(--bg-dark);
    border: 1px solid var(--border-color);
    border-radius: 6px;
    color: var(--text-primary);
    font-size: 1rem;
}
</style>