// Admin JavaScript functionality
document.addEventListener('DOMContentLoaded', function() {
    // Form validation
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            const requiredFields = form.querySelectorAll('[required]');
            let valid = true;
            
            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    valid = false;
                    field.classList.add('border-red-500');
                    
                    // Add error message
                    let errorMsg = field.parentNode.querySelector('.field-error');
                    if (!errorMsg) {
                        errorMsg = document.createElement('p');
                        errorMsg.className = 'field-error text-red-500 text-sm mt-1';
                        errorMsg.textContent = 'This field is required';
                        field.parentNode.appendChild(errorMsg);
                    }
                } else {
                    field.classList.remove('border-red-500');
                    const errorMsg = field.parentNode.querySelector('.field-error');
                    if (errorMsg) {
                        errorMsg.remove();
                    }
                }
            });
            
            if (!valid) {
                e.preventDefault();
                showNotification('Please fill in all required fields.', 'error');
            }
        });
    });

    // Image preview functionality
    const imageInputs = document.querySelectorAll('input[type="file"]');
    imageInputs.forEach(input => {
        input.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                // Check file size (5MB max)
                if (file.size > 5 * 1024 * 1024) {
                    showNotification('File size must be less than 5MB', 'error');
                    e.target.value = '';
                    return;
                }

                // Check file type
                const validTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
                if (!validTypes.includes(file.type)) {
                    showNotification('Please select a valid image file (JPEG, PNG, GIF)', 'error');
                    e.target.value = '';
                    return;
                }

                const reader = new FileReader();
                const previewId = e.target.id + '-preview';
                let preview = document.getElementById(previewId);
                
                if (!preview) {
                    preview = document.createElement('div');
                    preview.id = previewId;
                    preview.className = 'image-preview mt-2';
                    e.target.parentNode.appendChild(preview);
                }
                
                reader.onload = function(e) {
                    preview.innerHTML = `
                        <div class="relative inline-block">
                            <img src="${e.target.result}" class="max-w-xs rounded-lg shadow">
                            <button type="button" class="absolute top-0 right-0 bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center text-xs" onclick="this.parentElement.remove()">×</button>
                        </div>
                    `;
                }
                reader.readAsDataURL(file);
            }
        });
    });

    // Auto-save functionality for text areas
    let autoSaveTimer;
    const textareas = document.querySelectorAll('textarea');
    textareas.forEach(textarea => {
        textarea.addEventListener('input', function() {
            clearTimeout(autoSaveTimer);
            // Show saving indicator
            const savingIndicator = document.getElementById('saving-indicator');
            if (savingIndicator) {
                savingIndicator.classList.remove('hidden');
            }
            
            autoSaveTimer = setTimeout(() => {
                // Here you can implement actual auto-save logic
                if (savingIndicator) {
                    savingIndicator.classList.add('hidden');
                }
                showNotification('Changes saved automatically', 'success');
            }, 2000);
        });
    });

    // Confirm before deleting
    const deleteLinks = document.querySelectorAll('a[onclick*="confirm"]');
    deleteLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            if (!confirm('Are you sure you want to delete this item?')) {
                e.preventDefault();
            }
        });
    });

    // Notification system
    window.showNotification = function(message, type = 'info') {
        const notification = document.createElement('div');
        notification.className = `fixed top-4 right-4 z-50 p-4 rounded-lg shadow-lg transform transition-transform duration-300 ${
            type === 'success' ? 'bg-green-500 text-white' :
            type === 'error' ? 'bg-red-500 text-white' :
            type === 'warning' ? 'bg-yellow-500 text-gray-800' :
            'bg-blue-500 text-white'
        }`;
        notification.innerHTML = `
            <div class="flex items-center">
                <span>${message}</span>
                <button class="ml-4" onclick="this.parentElement.parentElement.remove()">×</button>
            </div>
        `;
        
        document.body.appendChild(notification);
        
        // Auto remove after 5 seconds
        setTimeout(() => {
            if (notification.parentElement) {
                notification.remove();
            }
        }, 5000);
    };

    // Tab functionality for admin pages
    const tabs = document.querySelectorAll('[data-tab]');
    tabs.forEach(tab => {
        tab.addEventListener('click', function() {
            const target = this.getAttribute('data-tab');
            
            // Hide all tab contents
            document.querySelectorAll('.tab-content').forEach(content => {
                content.classList.add('hidden');
            });
            
            // Show target tab content
            document.getElementById(`tab-${target}`).classList.remove('hidden');
            
            // Update active tab
            tabs.forEach(t => t.classList.remove('active'));
            this.classList.add('active');
        });
    });

    // Character counter for text areas
    const textAreasWithCounter = document.querySelectorAll('[data-max-length]');
    textAreasWithCounter.forEach(textarea => {
        const maxLength = textarea.getAttribute('data-max-length');
        const counter = document.createElement('div');
        counter.className = 'text-sm text-gray-500 mt-1';
        counter.textContent = `0/${maxLength} characters`;
        
        textarea.parentNode.appendChild(counter);
        
        textarea.addEventListener('input', function() {
            const currentLength = this.value.length;
            counter.textContent = `${currentLength}/${maxLength} characters`;
            
            if (currentLength > maxLength) {
                counter.classList.add('text-red-500');
            } else {
                counter.classList.remove('text-red-500');
            }
        });
    });
});

// Utility function to format file size
function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}

// Contact form handling
const contactForm = document.getElementById('contactForm');
if (contactForm) {
    contactForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        
        fetch('includes/contact-process.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.text())
        .then(data => {
            if (data === 'success') {
                showNotification('Message sent successfully! We will get back to you soon.', 'success');
                contactForm.reset();
            } else {
                showNotification('Error sending message. Please try again.', 'error');
            }
        })
        .catch(error => {
            showNotification('Network error. Please try again.', 'error');
        });
    });
}