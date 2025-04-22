// Hàm cập nhật số lượng sản phẩm trong icon giỏ hàng
function updateCartCount() {
    fetch('/shoppingcart/app/api/cart/count.php')
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                const cartCount = document.querySelector('.cart-count');
                if (cartCount) {
                    cartCount.textContent = data.count;
                }
            }
        })
        .catch(error => {
            console.error('Error updating cart count:', error);
        });
}

// Hàm hiển thị thông báo
function showToast(message, type = 'success') {
    const container = document.querySelector('.toast-container') || createToastContainer();
    const toast = document.createElement('div');
    toast.className = `toast ${type}`;
    toast.innerHTML = `
        <i class="fas fa-${type === 'success' ? 'check' : 'exclamation'}-circle"></i>
        <span>${message}</span>
    `;
    container.appendChild(toast);
    setTimeout(() => toast.classList.add('show'), 10);
    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => container.removeChild(toast), 300);
    }, 3000);
}

// Hàm tạo container cho toast nếu chưa tồn tại
function createToastContainer() {
    const container = document.createElement('div');
    container.className = 'toast-container';
    document.body.appendChild(container);
    return container;
}

// Hàm format tiền tệ
function formatCurrency(amount) {
    return new Intl.NumberFormat('vi-VN', {
        style: 'currency',
        currency: 'VND'
    }).format(amount).replace('₫', '') + '₫';
}

// Hàm debounce để giới hạn số lần gọi hàm
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Hàm xóa sản phẩm khỏi giỏ hàng
function removeFromCart(productId) {
    if (!confirm('Bạn có chắc chắn muốn xóa sản phẩm này?')) {
        return;
    }

    const item = document.querySelector(`.cart-item[data-id="${productId}"]`);
    if (!item) return;

    // Thêm hiệu ứng loading
    item.classList.add('removing');
    const removeBtn = item.querySelector('.remove-btn');
    if (removeBtn) {
        removeBtn.disabled = true;
        removeBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang xóa...';
    }

    fetch('/shoppingcart/app/api/cart/cart.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            action: 'remove',
            product_id: productId
        })
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            // Thêm hiệu ứng fade out và slide up
            item.style.opacity = '0';
            item.style.transform = 'translateX(100%)';
            setTimeout(() => {
                item.remove();
                updateCartCount();
                updateCartSummary();
                
                // Kiểm tra nếu giỏ hàng trống
                if (document.querySelectorAll('.cart-item').length === 0) {
                    location.reload();
                }
                
                showToast('Đã xóa sản phẩm khỏi giỏ hàng');
            }, 300);
        } else {
            throw new Error(data.message || 'Có lỗi xảy ra khi xóa sản phẩm');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        // Khôi phục trạng thái nút nếu có lỗi
        item.classList.remove('removing');
        if (removeBtn) {
            removeBtn.disabled = false;
            removeBtn.innerHTML = '<i class="fas fa-trash"></i> Xóa';
        }
        showToast(error.message || 'Có lỗi xảy ra khi xóa sản phẩm', 'error');
    });
}

// Thêm CSS cho hiệu ứng
const style = document.createElement('style');
style.textContent = `
    .toast-container {
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 1000;
    }

    .toast {
        background: white;
        padding: 12px 20px;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        margin-bottom: 10px;
        display: flex;
        align-items: center;
        gap: 8px;
        opacity: 0;
        transform: translateX(100%);
        transition: all 0.3s ease;
    }

    .toast.show {
        opacity: 1;
        transform: translateX(0);
    }

    .toast.success {
        border-left: 4px solid #4CAF50;
    }

    .toast.error {
        border-left: 4px solid #f44336;
    }

    .cart-item {
        transition: all 0.3s ease;
    }

    .cart-item.removing {
        opacity: 0.7;
        pointer-events: none;
    }

    .remove-btn {
        transition: all 0.2s ease;
    }

    .remove-btn:hover {
        background-color: #f44336;
        color: white;
    }

    .remove-btn:disabled {
        opacity: 0.7;
        cursor: not-allowed;
    }
`;
document.head.appendChild(style);

// Khởi tạo khi trang được tải
document.addEventListener('DOMContentLoaded', function() {
    // Cập nhật số lượng ban đầu
    updateCartCount();
}); 