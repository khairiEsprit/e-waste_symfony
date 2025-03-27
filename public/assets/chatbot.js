document.addEventListener('DOMContentLoaded', function() {
    const chatForm = document.getElementById('chat-form');
    const chatInput = document.getElementById('chat-input');
    const chatMessages = document.getElementById('chat-messages');

    // Clear chat history on page load
    clearChatHistory()
        .then(() => {
            // Optionally load history here if you want to keep some logic, but since we're clearing, we skip it
            chatMessages.innerHTML = ''; // Clear the UI immediately
        })
        .catch(error => {
            console.error('Error clearing history:', error);
        });

    // Handle form submission
    chatForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const message = chatInput.value.trim();
        
        if (message) {
            // Add user message immediately
            addMessage(message, true, new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }));
            chatInput.value = '';
            scrollToBottom();
            
            // Show typing indicator
            const typingIndicator = addMessage('Typing...', false);
            
            // Send message to server
            const formData = new FormData();
            formData.append('message', message);
            
            fetch('/chatbot/send', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                chatMessages.removeChild(typingIndicator);
                addMessage(data.bot_response, false, data.timestamp);
                scrollToBottom();
            })
            .catch(error => {
                console.error('Error:', error);
                chatMessages.removeChild(typingIndicator);
                addMessage('Sorry, there was an error processing your request.', false);
                scrollToBottom();
            });
        }
    });

    // Function to clear chat history
    function clearChatHistory() {
        return fetch('/chatbot/clear-history', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Failed to clear chat history');
            }
            return response.json();
        })
        .then(data => {
            if (data.status === 'success') {
                console.log('Chat history cleared');
            }
        });
    }

    // Function to add a message to the chat
    function addMessage(content, isFromUser, time = null) {
        const messageDiv = document.createElement('div');
        messageDiv.className = `mb-2 d-flex ${isFromUser ? 'justify-content-end' : 'justify-content-start'}`;
        
        const messageContent = document.createElement('div');
        messageContent.className = `p-3 rounded ${isFromUser ? 'bg-primary text-white' : 'bg-light'}`;
        messageContent.style.maxWidth = '80%';
        messageContent.innerHTML = content;
        
        if (time) {
            const timeSpan = document.createElement('span');
            timeSpan.className = 'small ms-2 text-muted';
            timeSpan.textContent = time;
            messageContent.appendChild(timeSpan);
        }
        
        messageDiv.appendChild(messageContent);
        chatMessages.appendChild(messageDiv);
        
        return messageDiv;
    }

    // Function to scroll to the bottom of the chat
    function scrollToBottom() {
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }

    // Enable pressing Enter to send (Shift+Enter for new lines)
    chatInput.addEventListener('keydown', function(e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            chatForm.dispatchEvent(new Event('submit'));
        }
    });
});