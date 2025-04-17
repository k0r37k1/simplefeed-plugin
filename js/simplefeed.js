/**
 * SimpleFeed Plugin for WonderCMS
 * JavaScript functionality
 */
document.addEventListener('DOMContentLoaded', function() {
    // Form validation for post edit form
    const postForm = document.getElementById('postForm');
    if (postForm) {
        postForm.addEventListener('submit', function(e) {
            const titleField = document.getElementById('title');
            const dateField = document.getElementById('date');
            let isValid = true;

            // Clear previous errors
            document.querySelectorAll('.field-error').forEach(el => el.remove());

            // Validate title
            if (!titleField.value.trim()) {
                addErrorMessage(titleField, 'Title is required');
                isValid = false;
            }

            // Validate date
            if (!dateField.value || !/^\d{4}-\d{2}-\d{2}$/.test(dateField.value)) {
                addErrorMessage(dateField, 'Valid date in YYYY-MM-DD format is required');
                isValid = false;
            }

            // Check image URL if entered
            const imageField = document.getElementById('image');
            if (imageField.value.trim() && !isValidURL(imageField.value)) {
                addErrorMessage(imageField, 'Please enter a valid URL');
                isValid = false;
            }

            if (!isValid) {
                e.preventDefault();
                // Scroll to the first error
                const firstError = document.querySelector('.field-error');
                if (firstError) {
                    firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
            }
        });

        // Preview button functionality
        const previewBtn = document.getElementById('previewBtn');
        if (previewBtn) {
            previewBtn.addEventListener('click', function() {
                // Get form values
                const title = document.getElementById('title').value || 'Post Preview';
                const content = document.getElementById('content').value || 'No content';
                const image = document.getElementById('image').value || '';

                // Create preview window
                const previewWindow = window.open('', 'preview', 'width=800,height=600,scrollbars=yes');

                // Create preview content
                let previewHTML = `
                    <!DOCTYPE html>
                    <html>
                    <head>
                        <title>Preview: ${escapeHTML(title)}</title>
                        <style>
                            body { font-family: Arial, sans-serif; line-height: 1.6; max-width: 800px; margin: 0 auto; padding: 20px; }
                            h1 { margin-top: 0; }
                            img { max-width: 100%; height: auto; }
                            .preview-notice { background: #ffffd8; padding: 10px; border: 1px solid #e6e6b8; margin-bottom: 20px; }
                        </style>
                    </head>
                    <body>
                        <div class="preview-notice">This is a preview. Content has not been saved yet.</div>
                        <h1>${escapeHTML(title)}</h1>
                `;

                // Add image if provided
                if (image) {
                    previewHTML += `<div><img src="${escapeHTML(image)}" alt="Preview image"></div>`;
                }

                // Add content
                previewHTML += `
                        <div>${content}</div>
                    </body>
                    </html>
                `;

                // Write to preview window
                previewWindow.document.write(previewHTML);
                previewWindow.document.close();
            });
        }

        // Auto-resize textareas
        document.querySelectorAll('textarea').forEach(textarea => {
            textarea.addEventListener('input', function() {
                this.style.height = 'auto';
                this.style.height = (this.scrollHeight) + 'px';
            });

            // Trigger on load
            textarea.dispatchEvent(new Event('input'));
        });
    }

    // Tag highlight in feed list
    const urlParams = new URLSearchParams(window.location.search);
    const currentTag = urlParams.get('tag');

    if (currentTag) {
        document.querySelectorAll('.sf-tag').forEach(tag => {
            if (tag.textContent.trim() === currentTag) {
                tag.classList.add('sf-tag-active');
            }
        });
    }

    // Add thumbnail preview in the edit form
    const imageInput = document.getElementById('image');
    if (imageInput) {
        // Create image preview element
        const previewContainer = document.createElement('div');
        previewContainer.className = 'image-preview';
        previewContainer.style.marginTop = '10px';
        previewContainer.style.display = 'none';

        const previewImage = document.createElement('img');
        previewImage.style.maxWidth = '200px';
        previewImage.style.maxHeight = '150px';
        previewImage.style.border = '1px solid #ddd';
        previewImage.style.borderRadius = '4px';
        previewImage.style.padding = '3px';

        previewContainer.appendChild(previewImage);
        imageInput.parentNode.appendChild(previewContainer);

        // Update preview when URL changes
        imageInput.addEventListener('input', function() {
            const url = this.value.trim();
            if (url && isValidURL(url)) {
                previewImage.src = url;
                previewImage.alt = 'Image Preview';
                previewContainer.style.display = 'block';
            } else {
                previewContainer.style.display = 'none';
            }
        });

        // Trigger preview on load if URL exists
        if (imageInput.value.trim()) {
            imageInput.dispatchEvent(new Event('input'));
        }
    }

    // Confirm delete actions
    document.querySelectorAll('.btn-delete').forEach(btn => {
        btn.addEventListener('click', function(e) {
            if (!confirm('Are you sure you want to delete this post? This action cannot be undone.')) {
                e.preventDefault();
            }
        });
    });
});

/**
 * Helper Functions
 */

// Add error message to a form field
function addErrorMessage(field, message) {
    const errorDiv = document.createElement('div');
    errorDiv.className = 'field-error';
    errorDiv.style.color = '#d9534f';
    errorDiv.style.fontSize = '12px';
    errorDiv.style.marginTop = '5px';
    errorDiv.textContent = message;

    field.style.borderColor = '#d9534f';
    field.parentNode.appendChild(errorDiv);
}

// Check if string is a valid URL
function isValidURL(string) {
    try {
        new URL(string);
        return true;
    } catch (_) {
        return false;
    }
}

// Escape HTML special characters
function escapeHTML(str) {
    return str
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}