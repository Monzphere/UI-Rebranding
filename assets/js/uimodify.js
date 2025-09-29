(function() {
	'use strict';

	const UIModify = {
		dimensions: {
			main: { width: 114, height: 30, label: 'Main Logo' },
			sidebar: { width: 91, height: 24, label: 'Sidebar Logo' },
			compact: { width: 24, height: 24, label: 'Compact Logo' }
		},

		maxFileSize: 5 * 1024 * 1024,
		allowedExtensions: ['svg', 'png', 'jpg', 'jpeg', 'gif', 'webp'],

		init: function() {
			this.bindEvents();
			this.setupValidation();
		},

		bindEvents: function() {
			const fileInput = document.getElementById('logo-file-input');
			const form = document.getElementById('logo-upload-form');
			const resetButton = document.getElementById('logo-reset-button');
			const logoTypeSelect = document.getElementById('logo-type-select');

			if (fileInput) {
				fileInput.addEventListener('change', this.handleFileSelect.bind(this));
			}

			if (form) {
				form.addEventListener('submit', this.handleUpload.bind(this));
			}

			if (resetButton) {
				resetButton.addEventListener('click', this.handleReset.bind(this));
			}

			if (logoTypeSelect) {
				logoTypeSelect.addEventListener('change', this.updateDimensionInfo.bind(this));
			}

			// Handle remove logo buttons
			const removeButtons = document.querySelectorAll('.js-logo-remove');
			removeButtons.forEach(button => {
				button.addEventListener('click', this.handleRemoveLogo.bind(this));
			});
		},

		setupValidation: function() {
			const form = document.getElementById('logo-upload-form');
			if (form) {
				form.setAttribute('novalidate', 'novalidate');
			}
		},

		handleFileSelect: function(event) {
			const file = event.target.files[0];
			if (!file) {
				this.clearPreview();
				return;
			}

			// Validate file
			const validation = this.validateFile(file);
			if (!validation.valid) {
				this.showValidationMessage(validation.message, 'error');
				this.clearPreview();
				return;
			}

			// Show preview
			this.showPreview(file);
		},

		validateFile: function(file) {
			if (file.size > this.maxFileSize) {
				return {
					valid: false,
					message: 'File size exceeds 5MB limit. Please choose a smaller file.'
				};
			}

			const extension = file.name.split('.').pop().toLowerCase();
			if (!this.allowedExtensions.includes(extension)) {
				return {
					valid: false,
					message: 'Invalid file format. Allowed: SVG, PNG, JPG, GIF, WebP'
				};
			}

			return { valid: true };
		},

		showPreview: function(file) {
			const reader = new FileReader();
			const logoType = document.getElementById('logo-type-select').value;
			const expectedDimensions = this.dimensions[logoType];

			reader.onload = (e) => {
				const container = document.getElementById('logo-preview-container');
				if (!container) return;

				const img = new Image();
				img.onload = () => {
					// Check dimensions
					const dimensionCheck = this.checkDimensions(
						img.width,
						img.height,
						expectedDimensions
					);

					// Update preview
					container.innerHTML = '';
					const imgElement = document.createElement('img');
					imgElement.src = e.target.result;
					imgElement.className = 'logo-preview-image';
					imgElement.alt = 'Logo Preview';
					container.appendChild(imgElement);

					// Show dimension info
					const infoDiv = document.createElement('div');
					infoDiv.style.marginTop = '10px';
					infoDiv.style.textAlign = 'center';
					infoDiv.style.fontSize = '12px';
					infoDiv.innerHTML = `
						<strong>Dimensions:</strong> ${img.width}×${img.height}px<br>
						<strong>Expected:</strong> ${expectedDimensions.width}×${expectedDimensions.height}px
					`;
					container.appendChild(infoDiv);

					// Show validation message
					if (!dimensionCheck.exact) {
						this.showValidationMessage(dimensionCheck.message, dimensionCheck.level);
					} else {
						this.showValidationMessage('Perfect! Image dimensions match requirements.', 'success');
					}
				};

				img.src = e.target.result;
			};

			reader.readAsDataURL(file);
		},

		checkDimensions: function(width, height, expected) {
			const tolerance = 5;

			if (width === expected.width && height === expected.height) {
				return {
					exact: true,
					level: 'success',
					message: ''
				};
			}

			const widthDiff = Math.abs(width - expected.width);
			const heightDiff = Math.abs(height - expected.height);

			if (widthDiff <= tolerance && heightDiff <= tolerance) {
				return {
					exact: false,
					level: 'warning',
					message: `Image dimensions are close but not exact. Expected: ${expected.width}×${expected.height}px, Got: ${width}×${height}px`
				};
			}

			return {
				exact: false,
				level: 'warning',
				message: `Warning: Image dimensions (${width}×${height}px) differ significantly from recommended size (${expected.width}×${expected.height}px). Logo may not display optimally.`
			};
		},

		showValidationMessage: function(message, type) {
			const existingMessages = document.querySelectorAll('.validation-message');
			existingMessages.forEach(msg => msg.remove());

			const messageDiv = document.createElement('div');
			messageDiv.className = `validation-message ${type} active`;
			messageDiv.textContent = message;

			const container = document.getElementById('logo-preview-container');
			if (container && container.parentNode) {
				container.parentNode.insertBefore(messageDiv, container.nextSibling);
			}
		},

		clearPreview: function() {
			const container = document.getElementById('logo-preview-container');
			if (container) {
				container.innerHTML = '<div class="logo-preview-placeholder">Upload a file to see preview</div>';
			}

			// Remove validation messages
			const messages = document.querySelectorAll('.validation-message');
			messages.forEach(msg => msg.remove());
		},

		handleUpload: function(event) {
			event.preventDefault();

			const fileInput = document.getElementById('logo-file-input');
			const logoTypeSelect = document.getElementById('logo-type-select');
			const footerInput = document.querySelector('input[name="brand_footer"]');
			const helpUrlInput = document.querySelector('input[name="brand_help_url"]');

			if (!fileInput || !fileInput.files[0]) {
				this.showValidationMessage('Please select a file to upload.', 'error');
				return false;
			}

			// Show loading state
			const uploadButton = document.getElementById('logo-upload-button');
			const originalText = uploadButton.textContent;
			uploadButton.disabled = true;
			uploadButton.textContent = 'Uploading...';

			// Prepare form data
			const formData = new FormData();
			formData.append('logo_file', fileInput.files[0]);
			formData.append('logo_type', logoTypeSelect.value);
			if (footerInput) {
				formData.append('brand_footer', footerInput.value);
			}
			if (helpUrlInput) {
				formData.append('brand_help_url', helpUrlInput.value);
			}

			// Send AJAX request
			const url = new URL('zabbix.php', window.location.origin);
			url.searchParams.set('action', 'uimodify.logo.upload');

			fetch(url.toString(), {
				method: 'POST',
				body: formData,
				headers: {
					'X-Requested-With': 'XMLHttpRequest'
				}
			})
			.then(response => response.text())
			.then(text => {
				const data = JSON.parse(text);
				if (data.success) {
					// Show success message
					this.showValidationMessage('Logo uploaded successfully! Please reload the page (F5 or Ctrl+R) to see the changes.', 'success');
					uploadButton.disabled = false;
					uploadButton.textContent = originalText;
					// Clear the file input
					fileInput.value = '';
				} else {
					this.showValidationMessage('Error: ' + (data.error || 'Failed to upload logo'), 'error');
					uploadButton.disabled = false;
					uploadButton.textContent = originalText;
				}
			})
			.catch(error => {
				console.error('Error uploading logo:', error);
				this.showValidationMessage('Failed to upload logo. Please try again.', 'error');
				uploadButton.disabled = false;
				uploadButton.textContent = originalText;
			});

			return false;
		},

		handleReset: function(event) {
			event.preventDefault();
			const fileInput = document.getElementById('logo-file-input');
			if (fileInput) {
				fileInput.value = '';
			}
			this.clearPreview();
		},

		handleRemoveLogo: function(event) {
			event.preventDefault();
			const button = event.target.closest('.js-logo-remove');
			const logoType = button.getAttribute('data-logo-type');

			if (!confirm('Remove this logo and restore Zabbix default?')) {
				return;
			}

			// Disable button during request
			button.disabled = true;
			const originalText = button.textContent;
			button.textContent = 'Removing...';

			// Send AJAX request
			const url = new URL('zabbix.php', window.location.origin);
			url.searchParams.set('action', 'uimodify.logo.remove');

			const formData = new FormData();
			formData.append('logo_type', logoType);

			fetch(url.toString(), {
				method: 'POST',
				body: formData,
				headers: {
					'X-Requested-With': 'XMLHttpRequest'
				}
			})
			.then(response => response.text())
			.then(text => {
				const data = JSON.parse(text);
				if (data.success) {
					// Show success message
					alert('Logo removed successfully! Please reload the page (F5 or Ctrl+R) to see the changes.');
					button.disabled = false;
					button.textContent = originalText;
				} else {
					alert('Error: ' + (data.error || 'Failed to remove logo'));
					button.disabled = false;
					button.textContent = originalText;
				}
			})
			.catch(error => {
				console.error('Error removing logo:', error);
				alert('Failed to remove logo. Please try again.');
				button.disabled = false;
				button.textContent = originalText;
			});
		},

		updateDimensionInfo: function() {
			this.clearPreview();

			// Clear file input to force re-validation with new dimensions
			const fileInput = document.getElementById('logo-file-input');
			if (fileInput && fileInput.value) {
				// Re-trigger preview with new dimensions
				const file = fileInput.files[0];
				if (file) {
					this.showPreview(file);
				}
			}
		}
	};

	// Initialize when DOM is ready
	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', function() {
			UIModify.init();
		});
	} else {
		UIModify.init();
	}

	// Expose to global scope if needed
	window.UIModify = UIModify;

})();