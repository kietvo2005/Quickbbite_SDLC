# Admin CRUD AJAX Refactoring Guide

## Overview

This guide explains how to convert all remaining admin CRUD pages (Categories, Restaurants, Users, Reviews, Messages) to use AJAX-based operations with toast notifications, no page reloads, and preservation of scroll/table position.

## Key Files Created

### Backend AJAX Endpoints

Located in `/admin/ajax/` directory:

- **Foods**: `create_food.php`, `update_food.php`, `delete_food.php`
- **Categories**: `create_category.php`, `update_category.php`, `delete_category.php`
- **Restaurants**: `create_restaurant.php`, `update_restaurant.php`, `delete_restaurant.php`
- **Users**: `update_user_status.php`, `delete_user.php`
- **Reviews**: `delete_review.php`
- **Messages**: `delete_message.php`
- **Orders**: `update_order_status.php` (already implemented)
- **Payments**: `update_payment_status.php` (already implemented)

### Frontend Utilities

- **`/assets/js/admin-crud.js`**: Shared JavaScript utilities for:
  - Toast notifications (`.admin-toast`)
  - Confirmation dialogs (`showAdminConfirm()`)
  - AJAX request handler (`AdminCRUD` object with `.create()`, `.update()`, `.delete()` methods)
  - Form control disable/enable utilities

### Styling

Added to `/includes/header.php`:
- `.admin-toast` styles (success/error gradients, animations)
- Modal styling for dark mode support

## Pattern for Converting Admin Pages

### Step 1: Remove POST Form Processing

**Before:**
```php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    // Process form, validate, update DB
    set_flash('success', 'Item updated.');
    redirect(BASE_URL . 'pages/admin/page.php');
}
```

**After:**
```php
// No POST processing here - handled by AJAX endpoints
```

### Step 2: Remove Delete GET Handler

**Before:**
```php
if (isset($_GET['action']) && $_GET['action'] === 'delete') {
    // Delete from DB
    set_flash('info', 'Item deleted.');
    redirect(BASE_URL . 'pages/admin/page.php');
}
```

**After:**
```php
// No GET delete handler - handled by AJAX endpoint
```

### Step 3: Convert Form to AJAX

**Before:**
```html
<form action="page.php" method="POST" enctype="multipart/form-data">
    <?php echo csrf_input(); ?>
    <input type="hidden" name="action" value="add">
    <input type="text" name="name" required>
    <button type="submit">Add</button>
</form>
```

**After:**
```html
<form id="itemForm" class="needs-validation" novalidate enctype="multipart/form-data">
    <input type="hidden" name="csrf_token" value="<?php echo e($csrfToken); ?>">
    <input type="text" name="name" required>
    <div class="invalid-feedback">Name is required.</div>
    <button type="submit" class="btn btn-brand">Add</button>
</form>
```

### Step 4: Add JavaScript Handler

```javascript
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('itemForm');
    const csrfToken = document.querySelector('input[name="csrf_token"]').value;

    form.addEventListener('submit', async function (e) {
        e.preventDefault();

        if (!form.checkValidity()) {
            form.classList.add('was-validated');
            return;
        }

        const formData = new FormData(form);
        const itemId = formData.get('item_id');
        const isEdit = itemId && itemId !== '';
        const endpoint = isEdit
            ? '<?php echo BASE_URL; ?>admin/ajax/update_item.php'
            : '<?php echo BASE_URL; ?>admin/ajax/create_item.php';

        const submitBtn = form.querySelector('button[type="submit"]');
        submitBtn.disabled = true;

        try {
            const response = await fetch(endpoint, {
                method: 'POST',
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                form.reset();
                form.classList.remove('was-validated');
                // Optionally refresh table or navigate
                showAdminToast(data.message, 'success');
                // window.location.reload(); // If you want to refresh
            } else {
                showAdminToast(data.message || 'Unable to save.', 'error');
            }
        } catch (error) {
            console.error('Error:', error);
            showAdminToast('An error occurred.', 'error');
        } finally {
            submitBtn.disabled = false;
        }
    });

    // Delete button handler
    document.querySelectorAll('.delete-item-btn').forEach(btn => {
        btn.addEventListener('click', async function () {
            const itemId = this.dataset.itemId;
            const itemName = this.dataset.itemName || 'this item';

            const confirmed = await showAdminConfirm(
                'Confirm Delete',
                `Delete "${itemName}"? This action cannot be undone.`,
                'Delete',
                'Cancel'
            );

            if (!confirmed) return;

            this.disabled = true;

            try {
                const response = await fetch('<?php echo BASE_URL; ?>admin/ajax/delete_item.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
                    },
                    body: new URLSearchParams({
                        item_id: itemId,
                        csrf_token: csrfToken
                    })
                });

                const data = await response.json();

                if (data.success) {
                    const row = document.querySelector(`tr[data-item-id="${itemId}"]`);
                    if (row) {
                        row.remove();
                    }
                    showAdminToast('Item deleted.', 'success');
                } else {
                    showAdminToast(data.message || 'Unable to delete.', 'error');
                    this.disabled = false;
                }
            } catch (error) {
                console.error('Error:', error);
                showAdminToast('An error occurred.', 'error');
                this.disabled = false;
            }
        });
    });
});
```

### Step 5: Update Table Row Markup

Add `data-*` attributes to table rows for easy targeting:

```html
<tr data-item-id="<?php echo $item['id']; ?>">
    <td class="item-name"><?php echo e($item['name']); ?></td>
    <td class="text-end">
        <button type="button" class="btn btn-sm btn-light edit-btn" data-item-id="<?php echo $item['id']; ?>">
            <i class="bi bi-pencil-square"></i>
        </button>
        <button type="button" class="btn btn-sm btn-outline-danger delete-item-btn" 
                data-item-id="<?php echo $item['id']; ?>" 
                data-item-name="<?php echo e($item['name']); ?>">
            <i class="bi bi-trash3"></i>
        </button>
    </td>
</tr>
```

## Remaining Pages to Convert

### 1. Categories (`pages/admin/categories.php`)

**AJAX Endpoints Already Created:**
- `/admin/ajax/create_category.php` ✓
- `/admin/ajax/update_category.php` ✓
- `/admin/ajax/delete_category.php` ✓

**Steps:**
1. Remove POST/GET handlers
2. Convert form to AJAX (follow pattern above)
3. Add table delete buttons with data attributes
4. Add JavaScript handler for form and delete buttons

### 2. Restaurants (`pages/admin/restaurants.php`)

**AJAX Endpoints Already Created:**
- `/admin/ajax/create_restaurant.php` ✓
- `/admin/ajax/update_restaurant.php` ✓
- `/admin/ajax/delete_restaurant.php` ✓

**Steps:**
1. Same as categories
2. Handle logo image upload in form
3. Update table row styling

### 3. Users (`pages/admin/users.php`)

**AJAX Endpoints Already Created:**
- `/admin/ajax/update_user_status.php` ✓
- `/admin/ajax/delete_user.php` ✓

**Steps:**
1. Remove POST status update handler
2. Convert status dropdown to AJAX (similar to orders.php pattern)
3. Convert delete button to AJAX with confirmation
4. Prevent self-deletion validation

### 4. Reviews (`pages/admin/reviews.php`)

**AJAX Endpoint Already Created:**
- `/admin/ajax/delete_review.php` ✓

**Steps:**
1. Remove GET delete handler
2. Convert delete button to AJAX with confirmation
3. Update table styling

### 5. Contact Messages (`pages/admin/messages.php`)

**AJAX Endpoint Already Created:**
- `/admin/ajax/delete_message.php` ✓

**Steps:**
1. Remove POST status update handler (marked as read/resolved)
2. Convert status buttons to AJAX
3. Convert delete button to AJAX
4. Optional: Add modal viewer for full message text

## Global Utilities Reference

### Toast Notifications

```javascript
// Success notification
showAdminToast('Operation completed successfully.', 'success');

// Error notification
showAdminToast('Something went wrong.', 'error');

// Info notification
showAdminToast('Here is some information.', 'info');
```

### Confirmation Dialog

```javascript
const confirmed = await showAdminConfirm(
    'Delete Item',              // Title
    'Are you sure?',           // Message
    'Delete',                  // Confirm button text
    'Cancel'                   // Cancel button text
);

if (confirmed) {
    // User clicked Delete
}
```

### AdminCRUD Helper

```javascript
// Create operation
AdminCRUD.create(
    '<?php echo BASE_URL; ?>admin/ajax/create_item.php',
    formData,
    {
        onSuccess: (data) => {
            console.log('Item created:', data.item);
        },
        onError: (error) => {
            console.error('Error:', error.message);
        },
        errorMessage: 'Unable to create item.'
    }
);

// Update operation
AdminCRUD.update(
    '<?php echo BASE_URL; ?>admin/ajax/update_item.php',
    formData,
    { onSuccess, onError, errorMessage }
);

// Delete operation (with confirmation)
AdminCRUD.delete(
    '<?php echo BASE_URL; ?>admin/ajax/delete_item.php',
    { item_id: 123, csrf_token: token },
    {
        title: 'Delete Item',
        message: 'Are you sure you want to delete this item?',
        onSuccess: (data) => { ... },
        errorMessage: 'Unable to delete item.'
    }
);
```

## Testing Checklist

For each converted page, verify:

- [ ] No page reloads after create/update/delete
- [ ] Scroll position remains unchanged
- [ ] Toast notifications appear (success and error)
- [ ] Confirmation dialog appears before delete
- [ ] CSRF protection working
- [ ] File uploads handled correctly
- [ ] Form validation shows inline messages
- [ ] Buttons disabled during operation
- [ ] Table rows update dynamically
- [ ] No console errors
- [ ] Dark mode styling applied
- [ ] Works on mobile devices

## Error Handling Best Practices

All AJAX endpoints follow this response format:

**Success:**
```json
{
    "success": true,
    "message": "Item created successfully.",
    "item": { ...data... }
}
```

**Error:**
```json
{
    "success": false,
    "message": "Validation error: Name is required."
}
```

**Security:**
- All endpoints verify CSRF token
- All endpoints verify admin role
- All endpoints use prepared statements
- All endpoints sanitize input
- No sensitive data in error messages
- HTTP error codes used appropriately (400, 403, 404, 500)

## File Upload Handling

For pages with file uploads (Foods, Categories, Restaurants):

1. Use `FormData` object to capture files
2. Handle file validation on both client and server
3. Delete old files when updating
4. Use unique filenames to prevent conflicts
5. All endpoints already handle this in the created AJAX files

## Optional Enhancements

1. **Inline editing**: Add edit button that transforms row into editable form
2. **Bulk actions**: Add checkboxes for multi-item delete
3. **Search/filter**: Preserve search terms in requests
4. **Pagination**: Update pagination AJAX requests
5. **Real-time validation**: Show validation errors as user types
6. **Undo actions**: Cache deleted items for 30 seconds with "Undo" button
7. **Loading skeleton**: Show placeholder rows while loading
8. **Optimistic updates**: Update UI immediately, rollback on error

## Completed Implementations

✓ Foods (`pages/admin/foods.php`) - Fully AJAX-based
✓ Orders (`pages/admin/orders.php`) - Status updates AJAX
✓ Payments (`pages/admin/orders.php`) - Status updates AJAX

## Next Steps

1. Apply the refactoring pattern to Categories
2. Apply to Restaurants
3. Apply to Users
4. Apply to Reviews
5. Apply to Messages
6. Test all pages thoroughly
7. Deploy to production
