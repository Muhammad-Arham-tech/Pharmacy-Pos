<!-- Hidden Modal for Adding Customer -->
<div id="add-customer-modal" class="modal-backdrop" style="display: none;">
    <div class="modal-content" style="max-width: 400px;">
        <div class="modal-header">
            <h3>Add New Customer</h3>
            <button id="close-customer-modal" class="modal-close">&times;</button>
        </div>
        <div class="modal-body">
            <form id="new-customer-form">
                <div class="form-group">
                    <label>Name *</label>
                    <input type="text" name="name" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Phone</label>
                    <input type="text" name="phone" class="form-control">
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" class="form-control">
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Save Customer</button>
                </div>
            </form>
        </div>
    </div>
</div>
