// Toast notification system
const Toast = {
    init() {
        // Create toast container if not exists
        if (!document.querySelector('.toast-container')) {
            const container = document.createElement('div');
            container.className = 'toast-container';
            document.body.appendChild(container);
        }

        // Add CSS styles
        if (!document.getElementById('toast-styles')) {
            const style = document.createElement('style');
            style.id = 'toast-styles';
            style.textContent = `
                .toast-container {
                    position: fixed;
                    top: 20px;
                    right: 20px;
                    z-index: 9999;
                }

                .custom-toast {
                    padding: 12px 24px;
                    margin-bottom: 10px;
                    border-radius: 8px;
                    color: white;
                    font-size: 14px;
                    opacity: 0;
                    transform: translateX(100%);
                    animation: slideIn 0.3s forwards;
                    display: flex;
                    align-items: center;
                    gap: 8px;
                }

                .custom-toast.success {
                    background-color: #4CAF50;
                }

                .custom-toast.error {
                    background-color: #f44336;
                }

                .custom-toast.warning {
                    background-color: #ff9800;
                }

                .custom-toast.info {
                    background-color: #2196F3;
                }

                @keyframes slideIn {
                    to {
                        opacity: 1;
                        transform: translateX(0);
                    }
                }

                @keyframes fadeOut {
                    from {
                        opacity: 1;
                        transform: translateX(0);
                    }
                    to {
                        opacity: 0;
                        transform: translateX(100%);
                    }
                }
            `;
            document.head.appendChild(style);
        }
    },

    show(message, type = 'success', duration = 3000) {
        this.init();
        
        const toast = document.createElement('div');
        toast.className = `custom-toast ${type}`;
        
        // Add icon based on type
        const icon = document.createElement('i');
        switch (type) {
            case 'success':
                icon.className = 'fas fa-check-circle';
                break;
            case 'error':
                icon.className = 'fas fa-exclamation-circle';
                break;
            case 'warning':
                icon.className = 'fas fa-exclamation-triangle';
                break;
            case 'info':
                icon.className = 'fas fa-info-circle';
                break;
        }
        
        const messageSpan = document.createElement('span');
        messageSpan.textContent = message;
        
        toast.appendChild(icon);
        toast.appendChild(messageSpan);
        
        const container = document.querySelector('.toast-container');
        container.appendChild(toast);

        // Auto remove after duration
        setTimeout(() => {
            toast.style.animation = 'fadeOut 0.3s forwards';
            setTimeout(() => {
                container.removeChild(toast);
            }, 300);
        }, duration);
    },

    success(message, duration) {
        this.show(message, 'success', duration);
    },

    error(message, duration) {
        this.show(message, 'error', duration);
    },

    warning(message, duration) {
        this.show(message, 'warning', duration);
    },

    info(message, duration) {
        this.show(message, 'info', duration);
    }
};

// Make Toast available globally
window.Toast = Toast; 