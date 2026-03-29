<!-- modules/pos.php -->
<!-- This content is loaded via AJAX into the #main-content area -->

<div class="pos-grid">
    <div class="pos-left">
        <div class="search-bar">
            <input type="text" id="medicine-search" placeholder="Scan barcode or type medicine name...">
        </div>
        <div class="item-grid" id="item-grid-display">
            <!-- Medicine items will be populated here via JS -->
            <p>Search for products to get started.</p>
        </div>
    </div>
    <div class="pos-right">
        <div class="customer-section" style="margin-bottom: 1rem; background: var(--card-dark); padding: 1rem; border-radius: 8px; border: 1px solid var(--border-color);">
            <h4 style="margin-bottom: 0.5rem; color: var(--accent-green);">Customer</h4>
            <div style="display: flex; gap: 0.5rem;">
                <div style="flex-grow: 1; position: relative;">
                    <input type="text" id="customer-search" placeholder="Search customer..." style="width: 100%; padding: 0.5rem; border-radius: 4px; border: 1px solid var(--border-color); background: var(--bg-dark); color: var(--text-primary);">
                    <input type="hidden" id="selected-customer-id" value="0">
                    <div id="customer-results" style="position: absolute; top: 100%; left: 0; right: 0; background: var(--card-dark); border: 1px solid var(--border-color); z-index: 10; display: none; max-height: 200px; overflow-y: auto;"></div>
                </div>
                <button id="add-customer-btn" class="btn btn-secondary" style="padding: 0.5rem;">+</button>
            </div>
            <div id="customer-display" style="margin-top: 0.5rem; font-size: 0.9rem; color: var(--text-secondary);">
                Selected: <span id="current-customer-name" style="color: var(--text-primary); font-weight: bold;">Walk-in Customer</span>
            </div>
        </div>

        <div class="billing-summary">
            <h3>Billing Summary</h3>
            <div class="cart-items" id="cart-items-container">
                <!-- Cart items will be populated by js/app.js -->
            </div>
            <div class="totals-section">
                <div>
                    <span>Subtotal</span>
                    <span id="subtotal">0.00</span>
                </div>
                <div>
                    <span>Tax</span>
                    <span id="total-tax">0.00</span>
                </div>
                <!-- Discount removed as per request -->
                <div class="grand-total">
                    <span>Grand Total</span>
                    <span id="grand-total">0.00</span>
                </div>
            </div>
            <div class="payment-section">
                <div style="margin-bottom: 1rem; text-align: right;">
                    <label for="print-receipt-toggle" style="font-size: 0.9rem; cursor: pointer; user-select: none;">
                        <input type="checkbox" id="print-receipt-toggle" style="vertical-align: middle; margin-right: 0.5rem;">
                        Print Receipt After Save
                    </label>
                </div>
                <button id="process-checkout" class="btn btn-primary btn-block">Process Checkout</button>
            </div>
        </div>
    </div>
</div>
<?php include 'customer_modal.php'; ?>
