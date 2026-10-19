// This file can be used for additional JavaScript functionality
document.addEventListener('DOMContentLoaded', function() {
    // Any global JavaScript can go here
    
    // Example: Confirm before deleting
    const deleteButtons = document.querySelectorAll('.delete-btn');
    deleteButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            if (!confirm('Are you sure you want to delete this?')) {
                e.preventDefault();
            }
        });
    });
    
    // Example: Form validation
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            // Add any form validation logic here
            // If validation fails, call e.preventDefault()
        });
    });
});