/**
 * SimpleFeed Plugin for WonderCMS
 * JavaScript functionality with optimized code and event delegation
 */
document.addEventListener('DOMContentLoaded', function() {
    initializeSimpleFeedFunctionality();
});

/**
 * Initialize all SimpleFeed functionality
 */
function initializeSimpleFeedFunctionality() {
    // Event delegation for forms
    setupFormValidation();
    
    // Preview button functionality
    setupPreviewButton();
    
    // Auto-resize textareas
    setupTextareaResizing();
    
    // Tag highlight in feed list
    highlightCurrentTag();
    
    // Add thumbnail preview in the edit form
    setupImagePreviews();
    
    // Confirm delete actions
    setupDeleteConfirmation();
}

/**
 * Setup form validation using event delegation
 */
function setupFormValidation() {
    document.body.addEventListener('submit', function(e) {
        const form = e.target;
        
        // Only handle post forms
        if (form.id === 'postForm') {
            const titleField = form.querySelector('#title');
            const dateField = form.querySelector('#date');
            let isValid = true;

            // Clear previous errors
            form.querySelectorAll('.field-error').forEach(el => el.remove());
            
            // Reset border colors
            form.querySelectorAll('input, textarea').forEach(el => {
                el.style.borderColor = '';
            });

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
            const imageField = form.querySelector('#image');
            if (imageField && imageField.value.trim() && !isValidURL(imageField.value)) {
                addErrorMessage(imageField, 'Please enter a valid URL');
                isValid = false;
            }

            if (!isValid) {
                e.preventDefault();
                // Scroll to the first error
                const firstError = form.querySelector('.field-error');
                if (firstError) {
                    firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
            }
        }
    });
}

/**
 * Set up preview button functionality
 */
function setupPreviewButton() {
    // Using event delegation for preview button
    document.body.addEventListener('click', function(e) {
        if (e.target && e.target.id === 'previewBtn') {
            const form = document.getElementById('postForm');
            if (!form) return;
            
            // Get form values
            const title = form.querySelector('#title').value || 'Post Preview';
            const content = form.querySelector('#content').value || 'No content';
            const image = form.querySelector('#image').value || '';
            const useMarkdown = form.querySelector('input[name="use_markdown"]:checked').value === '1';

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
                        
                        /* Markdown content styling */
                        .content blockquote { border-left: 4px solid #ddd; padding-left: 1em; margin-left: 0; color: #555; font-style: italic; }
                        .content pre { background: #f5f5f5; padding: 1em; border-radius: 4px; overflow-x: auto; }
                        .content code { background: #f5f5f5; padding: 0.2em 0.4em; border-radius: 3px; font-family: monospace; }
                        .content pre code { padding: 0; background: none; }
                        .content table { border-collapse: collapse; width: 100%; margin-bottom: 1.2em; }
                        .content th, .content td { border: 1px solid #ddd; padding: 8px; }
                        .content th { background-color: #f5f5f5; text-align: left; }
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

            // Add content - note that we don't have actual Markdown parsing in the preview
            previewHTML += `
                    <div class="content">${useMarkdown ?
                '<div style="color:#777;margin-bottom:15px;font-size:12px;">(Note: Markdown will be properly rendered when saved)</div>' +
                escapeHTML(content).replace(/\n/g, '<br>') :
                content}</div>
                </body>
                </html>
            `;

            // Write to preview window
            previewWindow.document.write(previewHTML);
            previewWindow.document.close();
        }
    });
}

/**
 * Set up automatic textarea resizing
 */
function setupTextareaResizing() {
    // Auto-resize textareas when content changes
    document.querySelectorAll('textarea').forEach(textarea => {
        textarea.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = (this.scrollHeight) + 'px';
        });

        // Trigger on load to adjust initial height
        textarea.dispatchEvent(new Event('input'));
    });
}

/**
 * Highlight current tag in tag list
 */
function highlightCurrentTag() {
    const urlParams = new URLSearchParams(window.location.search);
    const currentTag = urlParams.get('tag');

    if (currentTag) {
        document.querySelectorAll('.sf-tag').forEach(tag => {
            if (tag.textContent.trim() === currentTag) {
                tag.classList.add('sf-tag-active');
            }
        });
    }
}

/**
 * Set up image preview functionality for the image URL field
 */
function setupImagePreviews() {
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
}

/**
 * Set up confirmation dialogs for delete actions
 */
function setupDeleteConfirmation() {
    // Using event delegation for delete confirmations
    document.body.addEventListener('click', function(e) {
        if (e.target && e.target.classList.contains('btn-delete')) {
            if (!confirm('Are you sure you want to delete this? This action cannot be undone.')) {
                e.preventDefault();
            }
        }
    });
}

/**
 * Helper Functions
 */

/**
 * Add error message to a form field
 * @param {HTMLElement} field - The field element to attach error to
 * @param {string} message - Error message to display
 */
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

/**
 * Check if string is a valid URL
 * @param {string} string - URL to check
 * @returns {boolean} - True if valid URL
 */
function isValidURL(string) {
    try {
        new URL(string);
        return true;
    } catch (_) {
        return false;
    }
}

/**
 * Escape HTML special characters
 * @param {string} str - String to escape
 * @returns {string} - Escaped string
 */
function escapeHTML(str) {
    if (!str) return '';
    return str
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

/**
 * Helper for removing feedback messages after a delay
 */
function setupFeedbackDismissal() {
    // Auto-dismiss feedback messages after 5 seconds
    const feedbackMessages = document.querySelectorAll('.sf-feedback-message');
    if (feedbackMessages.length) {
        setTimeout(() => {
            feedbackMessages.forEach(el => {
                el.style.opacity = '0';
                setTimeout(() => el.remove(), 500);
            });
        }, 5000);
    }
}

// Initialize auto-dismissal of feedback messages
setupFeedbackDismissal();
