/**
 * Event Chat & AI Assistant
 * Handles real-time event discussion with AI-powered responses
 */

class EventChat {
    constructor(eventId) {
        this.eventId = eventId;
        this.chatContainer = document.getElementById('eventChatContainer');
        this.chatInput = document.getElementById('eventChatInput');
        this.sendBtn = document.getElementById('eventChatSendBtn');
        this.includeAICheckbox = document.getElementById('includeAIResponse');
        this.tutoringQuestion = document.getElementById('tutoringQuestion');
        this.tutoringSubmitBtn = document.getElementById('tutoringSubmitBtn');
        this.tutoringResponse = document.getElementById('tutoringResponse');

        this.init();
    }

    init() {
        this.loadMessages();
        this.attachEventListeners();
        this.setupAutoRefresh();
    }

    attachEventListeners() {
        if (this.sendBtn) {
            this.sendBtn.addEventListener('click', () => this.sendMessage());
        }

        if (this.chatInput) {
            this.chatInput.addEventListener('keypress', (e) => {
                if (e.key === 'Enter' && !e.shiftKey) {
                    e.preventDefault();
                    this.sendMessage();
                }
            });
        }

        if (this.tutoringSubmitBtn) {
            this.tutoringSubmitBtn.addEventListener('click', () => this.askTutoring());
        }

        if (this.tutoringQuestion) {
            this.tutoringQuestion.addEventListener('keypress', (e) => {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    this.askTutoring();
                }
            });
        }
    }

    async loadMessages() {
        try {
            const response = await fetch(`/api/event-chat/${this.eventId}/messages`);
            const data = await response.json();

            if (data.success) {
                this.renderMessages(data.data || []);
            } else {
                this.showError('Failed to load messages');
            }
        } catch (error) {
            console.error('Error loading messages:', error);
            this.showError('Connection error');
        }
    }

    renderMessages(messages) {
        this.chatContainer.innerHTML = '';

        if (messages.length === 0) {
            this.chatContainer.innerHTML = `
                <div class="text-center text-muted py-5">
                    <i class="fas fa-comments fa-2x d-block mb-2"></i>
                    <p>No messages yet. Start the conversation!</p>
                </div>
            `;
            return;
        }

        messages.forEach(msg => {
            const messageEl = this.createMessageElement(msg);
            this.chatContainer.appendChild(messageEl);
        });

        // Scroll to bottom
        this.chatContainer.scrollTop = this.chatContainer.scrollHeight;
    }

    createMessageElement(message) {
        const isAI = message.isAiGenerated === true;
        const div = document.createElement('div');
        div.className = `mb-3 ${isAI ? 'text-end' : 'text-start'}`;
        
        const avatarClass = isAI ? 'bg-info' : 'bg-secondary';
        const senderName = message.sender === 'ai_assistant' ? 'AI Assistant' : message.sender;

        div.innerHTML = `
            <div class="d-inline-block ${isAI ? 'bg-info' : 'bg-light'} rounded p-2" style="max-width: 70%;">
                <small class="d-block ${isAI ? 'text-white' : 'text-dark'} fw-bold mb-1">
                    ${senderName}
                </small>
                <p class="mb-0 ${isAI ? 'text-white' : 'text-dark'}">
                    ${this.escapeHtml(message.message)}
                </p>
                <small class="${isAI ? 'text-info-light' : 'text-muted'} d-block mt-1">
                    ${new Date(message.createdAt).toLocaleTimeString()}
                </small>
            </div>
            <div class="mt-2">
                <button class="btn btn-sm btn-outline-heart like-btn" data-message-id="${message.id}">
                    <i class="fas fa-heart"></i> <span class="likes-count">${message.likes || 0}</span>
                </button>
            </div>
        `;

        // Attach like event listener
        div.querySelector('.like-btn').addEventListener('click', () => this.likeMessage(message.id));

        return div;
    }

    async sendMessage() {
        const message = this.chatInput.value.trim();
        if (!message) return;

        this.chatInput.disabled = true;
        this.sendBtn.disabled = true;
        this.sendBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

        try {
            const response = await fetch(`/api/event-chat/${this.eventId}/send`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': this.getCsrfToken()
                },
                body: JSON.stringify({
                    message: message,
                    includeAI: this.includeAICheckbox?.checked ?? true
                })
            });

            const data = await response.json();

            if (data.success) {
                this.chatInput.value = '';
                await this.loadMessages();
            } else {
                this.showError(data.message || 'Failed to send message');
            }
        } catch (error) {
            console.error('Error sending message:', error);
            this.showError('Connection error while sending message');
        } finally {
            this.chatInput.disabled = false;
            this.sendBtn.disabled = false;
            this.sendBtn.innerHTML = '<i class="fas fa-paper-plane"></i> Send';
            this.chatInput.focus();
        }
    }

    async askTutoring() {
        const question = this.tutoringQuestion.value.trim();
        if (!question) return;

        const educationLevel = prompt('What is your education level? (e.g., beginner, intermediate, advanced)', 'beginner');
        if (educationLevel === null) return;

        this.tutoringSubmitBtn.disabled = true;
        this.tutoringSubmitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Asking...';

        try {
            const response = await fetch(`/api/event-chat/${this.eventId}/tutoring`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': this.getCsrfToken()
                },
                body: JSON.stringify({
                    question: question,
                    educationLevel: educationLevel,
                    addToChat: true
                })
            });

            const data = await response.json();

            if (data.success) {
                this.tutoringResponse.classList.remove('d-none');
                this.tutoringResponse.innerHTML = `
                    <h6 class="mb-3">AI Tutor Response:</h6>
                    <p>${this.escapeHtml(data.data.response)}</p>
                    <small class="text-muted d-block mt-2">
                        Education Level: ${educationLevel}
                    </small>
                `;
                this.tutoringQuestion.value = '';
                
                // Also reload chat to show tutoring message
                setTimeout(() => this.loadMessages(), 1000);
            } else {
                this.showError(data.message || 'Failed to get tutoring response');
            }
        } catch (error) {
            console.error('Error asking tutoring:', error);
            this.showError('Connection error while asking tutor');
        } finally {
            this.tutoringSubmitBtn.disabled = false;
            this.tutoringSubmitBtn.innerHTML = '<i class="fas fa-lightbulb"></i> Ask';
        }
    }

    async likeMessage(messageId) {
        try {
            const response = await fetch(`/api/event-chat/message/${messageId}/like`, {
                method: 'POST',
                headers: {
                    'X-CSRF-Token': this.getCsrfToken()
                }
            });

            const data = await response.json();

            if (data.success) {
                // Update like count in UI
                const btn = document.querySelector(`[data-message-id="${messageId}"] .likes-count`);
                if (btn) {
                    btn.textContent = data.data.likes;
                }
            }
        } catch (error) {
            console.error('Error liking message:', error);
        }
    }

    setupAutoRefresh() {
        // Refresh messages every 10 seconds
        setInterval(() => {
            this.loadMessages();
        }, 10000);
    }

    showError(message) {
        const alert = document.createElement('div');
        alert.className = 'alert alert-danger alert-dismissible fade show';
        alert.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        this.chatContainer.parentElement.insertBefore(alert, this.chatContainer);

        setTimeout(() => alert.remove(), 5000);
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
        window.eventChat = new EventChat(eventId);
    } else {
        // Try to get from URL or data attribute
        const eventId = new URLSearchParams(window.location.search).get('id');
        if (eventId) {
            window.eventChat = new EventChat(eventId);
        }
    }
});

// Export for use in other modules
if (typeof module !== 'undefined' && module.exports) {
    module.exports = EventChat;
}
