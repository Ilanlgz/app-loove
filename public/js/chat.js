class ChatClient {
    constructor(chatAreaId, messageInputId, sendButtonId, recipientId) {
        this.chatArea = document.getElementById(chatAreaId);
        this.messageInput = document.getElementById(messageInputId);
        this.sendButton = document.getElementById(sendButtonId);
        this.recipientId = recipientId; // The user ID of the person being chatted with
        this.userId = null; // Current logged-in user's ID, to be set
        this.lastMessageTimestamp = 0; // For polling or long-polling

        if (!this.chatArea || !this.messageInput || !this.sendButton) {
            console.warn('Chat UI elements not found.');
            return;
        }
        this.init();
    }

    init() {
        // Fetch current user ID (e.g., from a global JS variable or a data attribute)
        // this.userId = window.looveApp.currentUser.id; 

        this.sendButton.addEventListener('click', () => this.sendMessage());
        this.messageInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                this.sendMessage();
            }
        });

        // Load initial messages
        this.loadMessages();

        // Start polling for new messages (simple polling example)
        // setInterval(() => this.fetchNewMessages(), 5000); // Poll every 5 seconds
        console.log(`ChatClient initialized for recipient: ${this.recipientId}`);
    }

    async sendMessage() {
        const messageContent = this.messageInput.value.trim();
        if (!messageContent || !this.recipientId || !this.userId) return;

        try {
            const response = await fetch('/api/messages/send', { // Adjust API endpoint
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    // 'X-CSRF-TOKEN': 'your_csrf_token_here' // If using CSRF tokens
                },
                body: JSON.stringify({
                    recipient_id: this.recipientId,
                    content: messageContent
                })
            });

            if (response.ok) {
                const result = await response.json();
                if (result.success) {
                    this.appendMessage({ 
                        sender_id: this.userId, 
                        content: messageContent, 
                        sent_at: new Date().toISOString(),
                        sender_name: 'You' // Or fetch current user's name
                    }, true); // true for isOwnMessage
                    this.messageInput.value = '';
                } else {
                    window.looveApp.showMessage('error', result.message || 'Failed to send message.');
                }
            } else {
                window.looveApp.showMessage('error', 'Error sending message. Please try again.');
            }
        } catch (error) {
            console.error('SendMessage Error:', error);
            window.looveApp.showMessage('error', 'Network error. Could not send message.');
        }
    }

    async loadMessages() {
        if (!this.recipientId || !this.userId) return;
        try {
            // Adjust API endpoint as needed
            const response = await fetch(`/api/messages/history/${this.recipientId}`); 
            if (response.ok) {
                const messages = await response.json();
                this.chatArea.innerHTML = ''; // Clear existing messages
                messages.forEach(msg => this.appendMessage(msg, msg.sender_id === this.userId));
                this.scrollToBottom();
                if (messages.length > 0) {
                    this.lastMessageTimestamp = new Date(messages[messages.length - 1].sent_at).getTime();
                }
            } else {
                window.looveApp.showMessage('error', 'Could not load chat history.');
            }
        } catch (error) {
            console.error('LoadMessages Error:', error);
            window.looveApp.showMessage('error', 'Network error. Could not load messages.');
        }
    }
    
    async fetchNewMessages() {
        if (!this.recipientId || !this.userId) return;
        try {
            // Adjust API endpoint to fetch messages after a certain timestamp
            const response = await fetch(`/api/messages/new/${this.recipientId}?since=${this.lastMessageTimestamp}`);
            if (response.ok) {
                const newMessages = await response.json();
                if (newMessages.length > 0) {
                    newMessages.forEach(msg => this.appendMessage(msg, msg.sender_id === this.userId));
                    this.scrollToBottom();
                    this.lastMessageTimestamp = new Date(newMessages[newMessages.length - 1].sent_at).getTime();
                }
            }
        } catch (error) {
            console.error('FetchNewMessages Error:', error);
        }
    }

    appendMessage(message, isOwnMessage = false) {
        const messageElement = document.createElement('div');
        messageElement.classList.add('chat-message');
        if (isOwnMessage) {
            messageElement.classList.add('own-message');
        }

        const senderName = isOwnMessage ? 'You' : (message.sender_name || 'Them');
        const time = new Date(message.sent_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });

        messageElement.innerHTML = `
            <div class="message-sender">${senderName}</div>
            <div class="message-content">${this.sanitizeHTML(message.content)}</div>
            <div class="message-time">${time}</div>
        `;
        this.chatArea.appendChild(messageElement);
    }

    scrollToBottom() {
        this.chatArea.scrollTop = this.chatArea.scrollHeight;
    }

    sanitizeHTML(str) {
        const temp = document.createElement('div');
        temp.textContent = str;
        return temp.innerHTML;
    }
}

// Example Usage (would be instantiated when a chat window is opened):
// document.addEventListener('DOMContentLoaded', () => {
//     // This needs to be dynamic, e.g., when a user clicks on a match to chat
//     // const chatContainer = document.getElementById('chatContainer'); // Assuming a container for the chat UI
//     // if (chatContainer && chatContainer.dataset.recipientId) {
//     //     const recipientId = parseInt(chatContainer.dataset.recipientId);
//     //     // Assume current user ID is available, e.g., window.looveApp.currentUser.id
//     //     if (window.looveApp && window.looveApp.currentUser) {
//     //         const chatClient = new ChatClient('chatMessagesArea', 'chatMessageInput', 'sendChatMessageButton', recipientId);
//     //         chatClient.userId = window.looveApp.currentUser.id; 
//     //     } else {
//     //         console.warn("Current user data not available for chat initialization.");
//     //     }
//     // }
// });
