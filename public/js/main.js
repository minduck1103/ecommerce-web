// Initialize tooltips
var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
    return new bootstrap.Tooltip(tooltipTriggerEl)
})

// Initialize popovers
var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'))
var popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
    return new bootstrap.Popover(popoverTriggerEl)
})

// Handle file input change
document.querySelectorAll('.custom-file-input').forEach(function(input) {
    input.addEventListener('change', function(e) {
        var fileName = e.target.files[0].name;
        var nextSibling = e.target.nextElementSibling;
        nextSibling.innerText = fileName;
    });
});

// Handle form validation
(function () {
    'use strict'
    var forms = document.querySelectorAll('.needs-validation')
    Array.prototype.slice.call(forms).forEach(function (form) {
        form.addEventListener('submit', function (event) {
            if (!form.checkValidity()) {
                event.preventDefault()
                event.stopPropagation()
            }
            form.classList.add('was-validated')
        }, false)
    })
})()

// Handle quantity input
document.querySelectorAll('.quantity-input').forEach(function(input) {
    input.addEventListener('change', function(e) {
        var value = parseInt(e.target.value);
        var min = parseInt(e.target.min);
        var max = parseInt(e.target.max);
        
        if (value < min) e.target.value = min;
        if (max && value > max) e.target.value = max;
    });
});

// Handle price formatting
document.querySelectorAll('.price-input').forEach(function(input) {
    input.addEventListener('input', function(e) {
        var value = e.target.value.replace(/[^\d.]/g, '');
        var parts = value.split('.');
        if (parts.length > 2) parts.pop();
        if (parts[1] && parts[1].length > 2) parts[1] = parts[1].substr(0, 2);
        e.target.value = parts.join('.');
    });
}); 