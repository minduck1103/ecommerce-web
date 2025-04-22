// Custom Dialog Functionality
function createCustomDialog() {
    // Create dialog elements if they don't exist
    if (!document.getElementById('customDialog')) {
        const dialogHTML = `
            <div class="custom-dialog-overlay" id="customDialog">
                <div class="custom-dialog">
                    <div class="custom-dialog-message" id="dialogMessage"></div>
                    <div class="custom-dialog-buttons">
                        <button class="custom-dialog-button confirm" id="dialogConfirm">OK</button>
                        <button class="custom-dialog-button cancel" id="dialogCancel">Cancel</button>
                    </div>
                </div>
            </div>
        `;
        document.body.insertAdjacentHTML('beforeend', dialogHTML);

        // Add styles
        const styles = `
            .custom-dialog-overlay {
                display: none;
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background-color: rgba(0, 0, 0, 0.5);
                z-index: 1000;
            }

            .custom-dialog {
                position: fixed;
                top: 50%;
                left: 50%;
                transform: translate(-50%, -50%);
                background-color: #1a1a1a;
                color: white;
                padding: 20px;
                border-radius: 8px;
                z-index: 1001;
                min-width: 320px;
                box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            }

            .custom-dialog-message {
                margin-bottom: 20px;
                font-size: 16px;
                text-align: center;
            }

            .custom-dialog-buttons {
                display: flex;
                justify-content: center;
                gap: 10px;
            }

            .custom-dialog-button {
                padding: 8px 20px;
                border: none;
                border-radius: 4px;
                cursor: pointer;
                font-size: 14px;
                transition: background-color 0.2s;
            }

            .custom-dialog-button.confirm {
                background-color: #7da3c0;
                color: white;
            }

            .custom-dialog-button.cancel {
                background-color: #0c4a6e;
                color: white;
            }

            .custom-dialog-button:hover {
                opacity: 0.9;
            }
        `;

        const styleSheet = document.createElement('style');
        styleSheet.textContent = styles;
        document.head.appendChild(styleSheet);
    }
}

function showCustomDialog(message, onConfirm) {
    createCustomDialog();
    
    const dialog = document.getElementById('customDialog');
    const messageElement = document.getElementById('dialogMessage');
    const confirmButton = document.getElementById('dialogConfirm');
    const cancelButton = document.getElementById('dialogCancel');

    // Xóa event listeners cũ nếu có
    const newConfirmButton = confirmButton.cloneNode(true);
    const newCancelButton = cancelButton.cloneNode(true);
    confirmButton.parentNode.replaceChild(newConfirmButton, confirmButton);
    cancelButton.parentNode.replaceChild(newCancelButton, cancelButton);

    messageElement.textContent = message;
    dialog.style.display = 'block';

    // Thêm event listeners mới
    newConfirmButton.addEventListener('click', function() {
        dialog.style.display = 'none';
        if (typeof onConfirm === 'function') {
            onConfirm();
        }
    });

    newCancelButton.addEventListener('click', function() {
        dialog.style.display = 'none';
    });
} 