/**
 * Event Poster AI Generation
 * Handles AI-generated event poster creation, versioning, and management
 */

class EventPoster {
    constructor(eventId) {
        this.eventId = eventId;
        this.posterContainer = document.getElementById('posterContainer');
        this.generateBtn = document.getElementById('generatePosterBtn');
        this.versionsBtn = document.getElementById('posterVersionsBtn');
        this.posterStyle = document.getElementById('posterStyle');
        this.posterStatus = document.getElementById('posterStatus');

        this.init();
    }

    init() {
        this.loadActivePoster();
        this.attachEventListeners();
    }

    attachEventListeners() {
        if (this.generateBtn) {
            this.generateBtn.addEventListener('click', () => this.generatePoster());
        }

        if (this.versionsBtn) {
            this.versionsBtn.addEventListener('click', () => this.showVersions());
        }
    }

    async loadActivePoster() {
        try {
            const response = await fetch(`/api/event-poster/${this.eventId}/active`);
            const data = await response.json();

            if (data.success && data.data) {
                this.renderPoster(data.data);
            } else {
                this.posterContainer.innerHTML = `
                    <div class="text-center text-muted py-4">
                        <i class="fas fa-image fa-2x d-block mb-2"></i>
                        <p>No poster yet</p>
                    </div>
                `;
            }
        } catch (error) {
            console.error('Error loading poster:', error);
        }
    }

    renderPoster(poster) {
        const downloadUrl = `/api/event-poster/poster/${poster.id}/download`;
        
        this.posterContainer.innerHTML = `
            <div class="position-relative">
                <img src="${this.escapeHtml(poster.imageUrl)}" 
                     alt="Event Poster" 
                     class="img-fluid rounded w-100"
                     style="max-height: 400px; object-fit: contain;">
                
                <div class="mt-2">
                    <small class="text-muted d-block">
                        <i class="fas fa-palette"></i> Style: <strong>${this.escapeHtml(poster.style)}</strong>
                    </small>
                    <small class="text-muted d-block">
                        <i class="fas fa-calendar"></i> Generated: <strong>${new Date(poster.generatedAt).toLocaleDateString()}</strong>
                    </small>
                    {% if poster.downloadCount %}
                        <small class="text-muted d-block">
                            <i class="fas fa-download"></i> Downloads: <strong>${poster.downloadCount}</strong>
                        </small>
                    {% endif %}
                </div>

                <div class="d-grid gap-2 mt-3">
                    <a href="${downloadUrl}" 
                       class="btn btn-sm btn-primary"
                       download>
                        <i class="fas fa-download"></i> Download
                    </a>
                </div>
            </div>
        `;
    }

    async generatePoster() {
        const style = this.posterStyle?.value || 'modern';

        if (!confirm('Generate a new AI poster? This will replace the current one.')) {
            return;
        }

        this.generateBtn.disabled = true;
        this.generateBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Generating...';
        this.showStatus('Generating poster with AI...', 'info');

        try {
            const response = await fetch(`/api/event-poster/${this.eventId}/generate`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': this.getCsrfToken()
                },
                body: JSON.stringify({
                    style: style
                })
            });

            const data = await response.json();

            if (data.success) {
                this.showStatus('Poster generated successfully!', 'success');
                setTimeout(() => {
                    this.loadActivePoster();
                    this.hideStatus();
                }, 2000);
            } else {
                this.showStatus(data.message || 'Failed to generate poster', 'danger');
            }
        } catch (error) {
            console.error('Error generating poster:', error);
            this.showStatus('Connection error while generating poster', 'danger');
        } finally {
            this.generateBtn.disabled = false;
            this.generateBtn.innerHTML = '<i class="fas fa-magic"></i> Generate Poster';
        }
    }

    async showVersions() {
        try {
            const response = await fetch(`/api/event-poster/${this.eventId}/versions`);
            const data = await response.json();

            if (data.success) {
                this.displayVersionsModal(data.data || []);
            } else {
                this.showStatus('Failed to load poster versions', 'danger');
            }
        } catch (error) {
            console.error('Error loading versions:', error);
            this.showStatus('Connection error', 'danger');
        }
    }

    displayVersionsModal(versions) {
        if (versions.length === 0) {
            alert('No poster versions available');
            return;
        }

        // Create modal HTML
        let versionsHtml = `
            <div class="row">
        `;

        versions.forEach((version, index) => {
            const isActive = version.isActive ? 'border-success border-2' : '';
            versionsHtml += `
                <div class="col-md-6 mb-3">
                    <div class="card ${isActive}">
                        <img src="${this.escapeHtml(version.imageUrl)}" 
                             class="card-img-top" 
                             alt="Poster Version ${index + 1}"
                             style="height: 200px; object-fit: contain;">
                        <div class="card-body p-2">
                            <small class="d-block">
                                <strong>${new Date(version.generatedAt).toLocaleDateString()}</strong>
                            </small>
                            <small class="text-muted d-block">
                                Style: ${this.escapeHtml(version.style)}
                            </small>
                            ${version.isActive ? '<span class="badge bg-success">Active</span>' : ''}
                            
                            <div class="d-grid gap-1 mt-2">
                                ${!version.isActive ? `
                                    <button class="btn btn-sm btn-outline-primary activate-version" data-version-id="${version.id}">
                                        <i class="fas fa-check"></i> Activate
                                    </button>
                                ` : ''}
                                <a href="/api/event-poster/poster/${version.id}/download" 
                                   class="btn btn-sm btn-outline-secondary" download>
                                    <i class="fas fa-download"></i> Download
                                </a>
                                <button class="btn btn-sm btn-outline-danger delete-version" data-version-id="${version.id}">
                                    <i class="fas fa-trash"></i> Delete
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        });

        versionsHtml += '</div>';

        // Show in modal (using Bootstrap modal if available)
        if (typeof bootstrap !== 'undefined') {
            const modalElement = document.createElement('div');
            modalElement.className = 'modal fade';
            modalElement.innerHTML = `
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Poster Versions</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            ${versionsHtml}
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            `;

            document.body.appendChild(modalElement);
            const modal = new bootstrap.Modal(modalElement);
            modal.show();

            // Attach event listeners
            modalElement.querySelectorAll('.activate-version').forEach(btn => {
                btn.addEventListener('click', () => {
                    const versionId = btn.dataset.versionId;
                    this.activateVersion(versionId, modalElement);
                });
            });

            modalElement.querySelectorAll('.delete-version').forEach(btn => {
                btn.addEventListener('click', () => {
                    const versionId = btn.dataset.versionId;
                    this.deleteVersion(versionId, modalElement);
                });
            });

            // Clean up modal when hidden
            modalElement.addEventListener('hidden.bs.modal', () => {
                document.body.removeChild(modalElement);
            });
        } else {
            // Fallback if Bootstrap modal not available
            alert('Poster versions:\n' + versions.map(v => `${new Date(v.generatedAt).toLocaleDateString()} - ${v.style}`).join('\n'));
        }
    }

    async activateVersion(versionId, modalElement) {
        try {
            const response = await fetch(`/api/event-poster/poster/${versionId}/activate`, {
                method: 'POST',
                headers: {
                    'X-CSRF-Token': this.getCsrfToken()
                }
            });

            const data = await response.json();

            if (data.success) {
                this.showStatus('Version activated!', 'success');
                setTimeout(() => {
                    this.loadActivePoster();
                    this.hideStatus();
                    // Close modal
                    if (modalElement && typeof bootstrap !== 'undefined') {
                        bootstrap.Modal.getInstance(modalElement).hide();
                    }
                }, 1500);
            } else {
                this.showStatus(data.message || 'Failed to activate version', 'danger');
            }
        } catch (error) {
            console.error('Error activating version:', error);
            this.showStatus('Connection error', 'danger');
        }
    }

    async deleteVersion(versionId, modalElement) {
        if (!confirm('Delete this poster version?')) {
            return;
        }

        try {
            const response = await fetch(`/api/event-poster/poster/${versionId}/delete`, {
                method: 'POST',
                headers: {
                    'X-CSRF-Token': this.getCsrfToken()
                }
            });

            const data = await response.json();

            if (data.success) {
                this.showStatus('Version deleted!', 'success');
                setTimeout(() => {
                    this.showVersions();
                    this.hideStatus();
                }, 1500);
            } else {
                this.showStatus(data.message || 'Failed to delete version', 'danger');
            }
        } catch (error) {
            console.error('Error deleting version:', error);
            this.showStatus('Connection error', 'danger');
        }
    }

    showStatus(message, type = 'info') {
        if (!this.posterStatus) return;
        
        this.posterStatus.className = `alert alert-${type} py-2 px-3 small`;
        this.posterStatus.textContent = message;
        this.posterStatus.classList.remove('d-none');
    }

    hideStatus() {
        if (this.posterStatus) {
            this.posterStatus.classList.add('d-none');
        }
    }

    escapeHtml(text) {
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return text.replace(/[&<>"']/g, m => map[m]);
    }

    getCsrfToken() {
        return document.querySelector('meta[name="csrf-token"]')?.content || 
               document.querySelector('input[name="_token"]')?.value || '';
    }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    const eventIdElement = document.querySelector('[data-event-id]');
    if (eventIdElement) {
        const eventId = eventIdElement.dataset.eventId;
        window.eventPoster = new EventPoster(eventId);
    } else {
        // Try to get from URL or data attribute
        const eventId = new URLSearchParams(window.location.search).get('id');
        if (eventId) {
            window.eventPoster = new EventPoster(eventId);
        }
    }
});

// Export for use in other modules
if (typeof module !== 'undefined' && module.exports) {
    module.exports = EventPoster;
}
