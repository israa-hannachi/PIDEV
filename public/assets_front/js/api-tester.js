/**
 * API Testing Dashboard
 * Test all Chat and Poster API endpoints
 */

class APITester {
    constructor(eventId) {
        this.eventId = eventId;
        this.resultsContainer = document.getElementById('testResults');
        this.toggleBtn = document.getElementById('toggleTestPanel');
        this.panelContent = document.getElementById('testPanelContent');
        this.clearBtn = document.getElementById('clearTestResults');
        
        this.init();
    }

    init() {
        // Chat API Test Buttons
        document.getElementById('testChatGetMessages')?.addEventListener('click', () => this.testGetMessages());
        document.getElementById('testChatSend')?.addEventListener('click', () => this.testSendMessage());
        document.getElementById('testChatStats')?.addEventListener('click', () => this.testChatStats());
        document.getElementById('testChatTutoring')?.addEventListener('click', () => this.testChatTutoring());

        // Poster API Test Buttons
        document.getElementById('testPosterGenerate')?.addEventListener('click', () => this.testPosterGenerate());
        document.getElementById('testPosterGetActive')?.addEventListener('click', () => this.testGetActivePoster());
        document.getElementById('testPosterVersions')?.addEventListener('click', () => this.testGetPosterVersions());
        document.getElementById('testPosterStats')?.addEventListener('click', () => this.testPosterStats());

        // Panel controls
        this.toggleBtn?.addEventListener('click', () => this.togglePanel());
        this.clearBtn?.addEventListener('click', () => this.clearResults());
    }

    togglePanel() {
        const isHidden = this.panelContent.style.display === 'none';
        this.panelContent.style.display = isHidden ? 'block' : 'none';
        const icon = this.toggleBtn.querySelector('i');
        icon.className = isHidden ? 'fas fa-chevron-up' : 'fas fa-chevron-down';
    }

    clearResults() {
        this.resultsContainer.innerHTML = '<p class="text-muted">Cleared. Click any button above to test API endpoints...</p>';
    }

    log(title, method, endpoint, status, data, time) {
        const timestamp = new Date().toLocaleTimeString();
        const statusColor = status >= 200 && status < 300 ? '#28a745' : '#dc3545';
        
        const html = `
            <div style="margin-bottom: 10px; padding: 8px; background: #f5f5f5; border-left: 3px solid ${statusColor};">
                <div style="color: #333; font-weight: bold;">
                    <span style="color: ${statusColor};">${status}</span> ${method} ${endpoint}
                </div>
                <div style="color: #666; font-size: 11px; margin-top: 3px;">
                    <strong>${title}</strong> | ${timestamp} | ${time}ms
                </div>
                <div style="color: #888; margin-top: 5px; white-space: pre-wrap; word-break: break-word;">
                    ${this.escapeHtml(JSON.stringify(data, null, 2))}
                </div>
            </div>
        `;
        
        this.resultsContainer.innerHTML += html;
        this.resultsContainer.scrollTop = this.resultsContainer.scrollHeight;
    }

    async testGetMessages() {
        const startTime = performance.now();
        try {
            const response = await fetch(`/api/event-chat/${this.eventId}/messages`);
            const data = await response.json();
            const time = Math.round(performance.now() - startTime);
            this.log('Get Chat Messages', 'GET', `/api/event-chat/${this.eventId}/messages`, response.status, data, time);
        } catch (error) {
            this.log('Get Chat Messages', 'GET', `/api/event-chat/${this.eventId}/messages`, 0, {error: error.message}, 0);
        }
    }

    async testSendMessage() {
        const startTime = performance.now();
        try {
            const response = await fetch(`/api/event-chat/${this.eventId}/send`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': this.getCsrfToken()
                },
                body: JSON.stringify({
                    message: 'Test message from API tester',
                    includeAI: true
                })
            });
            const data = await response.json();
            const time = Math.round(performance.now() - startTime);
            this.log('Send Message', 'POST', `/api/event-chat/${this.eventId}/send`, response.status, data, time);
        } catch (error) {
            this.log('Send Message', 'POST', `/api/event-chat/${this.eventId}/send`, 0, {error: error.message}, 0);
        }
    }

    async testChatStats() {
        const startTime = performance.now();
        try {
            const response = await fetch(`/api/event-chat/${this.eventId}/stats`);
            const data = await response.json();
            const time = Math.round(performance.now() - startTime);
            this.log('Chat Statistics', 'GET', `/api/event-chat/${this.eventId}/stats`, response.status, data, time);
        } catch (error) {
            this.log('Chat Statistics', 'GET', `/api/event-chat/${this.eventId}/stats`, 0, {error: error.message}, 0);
        }
    }

    async testChatTutoring() {
        const startTime = performance.now();
        try {
            const response = await fetch(`/api/event-chat/${this.eventId}/tutoring`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': this.getCsrfToken()
                },
                body: JSON.stringify({
                    question: 'What is the main topic of this event?',
                    educationLevel: 'beginner',
                    addToChat: true
                })
            });
            const data = await response.json();
            const time = Math.round(performance.now() - startTime);
            this.log('Chat Tutoring', 'POST', `/api/event-chat/${this.eventId}/tutoring`, response.status, data, time);
        } catch (error) {
            this.log('Chat Tutoring', 'POST', `/api/event-chat/${this.eventId}/tutoring`, 0, {error: error.message}, 0);
        }
    }

    async testPosterGenerate() {
        const startTime = performance.now();
        try {
            const response = await fetch(`/api/event-poster/${this.eventId}/generate`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': this.getCsrfToken()
                },
                body: JSON.stringify({
                    style: 'modern'
                })
            });
            const data = await response.json();
            const time = Math.round(performance.now() - startTime);
            this.log('Generate Poster', 'POST', `/api/event-poster/${this.eventId}/generate`, response.status, data, time);
        } catch (error) {
            this.log('Generate Poster', 'POST', `/api/event-poster/${this.eventId}/generate`, 0, {error: error.message}, 0);
        }
    }

    async testGetActivePoster() {
        const startTime = performance.now();
        try {
            const response = await fetch(`/api/event-poster/${this.eventId}/active`);
            const data = await response.json();
            const time = Math.round(performance.now() - startTime);
            this.log('Get Active Poster', 'GET', `/api/event-poster/${this.eventId}/active`, response.status, data, time);
        } catch (error) {
            this.log('Get Active Poster', 'GET', `/api/event-poster/${this.eventId}/active`, 0, {error: error.message}, 0);
        }
    }

    async testGetPosterVersions() {
        const startTime = performance.now();
        try {
            const response = await fetch(`/api/event-poster/${this.eventId}/versions`);
            const data = await response.json();
            const time = Math.round(performance.now() - startTime);
            this.log('Get Poster Versions', 'GET', `/api/event-poster/${this.eventId}/versions`, response.status, data, time);
        } catch (error) {
            this.log('Get Poster Versions', 'GET', `/api/event-poster/${this.eventId}/versions`, 0, {error: error.message}, 0);
        }
    }

    async testPosterStats() {
        const startTime = performance.now();
        try {
            const response = await fetch(`/api/event-poster/${this.eventId}/stats`);
            const data = await response.json();
            const time = Math.round(performance.now() - startTime);
            this.log('Poster Statistics', 'GET', `/api/event-poster/${this.eventId}/stats`, response.status, data, time);
        } catch (error) {
            this.log('Poster Statistics', 'GET', `/api/event-poster/${this.eventId}/stats`, 0, {error: error.message}, 0);
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

// Initialize API Tester
document.addEventListener('DOMContentLoaded', function() {
    const eventIdElement = document.querySelector('[data-event-id]');
    if (eventIdElement) {
        const eventId = eventIdElement.dataset.eventId;
        window.apiTester = new APITester(eventId);
        console.log('✅ API Tester initialized for event:', eventId);
    }
});

// Export for use in other modules
if (typeof module !== 'undefined' && module.exports) {
    module.exports = APITester;
}
