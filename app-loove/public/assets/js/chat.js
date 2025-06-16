// This file handles chat functionalities.

document.addEventListener('DOMContentLoaded', function() {
    const chatForm = document.getElementById('chat-form');
    const chatMessages = document.getElementById('chat-messages');
    const messageInput = document.getElementById('message-input');
    const userId = chatForm.dataset.userId; // Assuming user ID is stored in a data attribute

    chatForm.addEventListener('submit', function(event) {
        event.preventDefault();
        const message = messageInput.value.trim();

        if (message) {
            sendMessage(userId, message);
            messageInput.value = '';
        }
    });

    function sendMessage(userId, message) {
        const xhr = new XMLHttpRequest();
        xhr.open('POST', '/send-message.php', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.onreadystatechange = function() {
            if (xhr.readyState === XMLHttpRequest.DONE && xhr.status === 200) {
                const response = JSON.parse(xhr.responseText);
                if (response.success) {
                    appendMessage(response.message, 'sent');
                } else {
                    console.error('Error sending message:', response.error);
                }
            }
        };
        xhr.send('user_id=' + encodeURIComponent(userId) + '&message=' + encodeURIComponent(message));
    }

    function appendMessage(message, type) {
        const messageElement = document.createElement('div');
        messageElement.classList.add('message', type);
        messageElement.textContent = message;
        chatMessages.appendChild(messageElement);
        chatMessages.scrollTop = chatMessages.scrollHeight; // Scroll to the bottom
    }

    // Function to fetch messages periodically
    function fetchMessages() {
        const xhr = new XMLHttpRequest();
        xhr.open('GET', '/fetch-messages.php?user_id=' + encodeURIComponent(userId), true);
        xhr.onreadystatechange = function() {
            if (xhr.readyState === XMLHttpRequest.DONE && xhr.status === 200) {
                const messages = JSON.parse(xhr.responseText);
                chatMessages.innerHTML = ''; // Clear current messages
                messages.forEach(msg => appendMessage(msg.content, msg.type));
            }
        };
        xhr.send();
    }

    // Fetch messages every 5 seconds
    setInterval(fetchMessages, 5000);
});