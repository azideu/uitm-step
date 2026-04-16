/* assets/js/chat.js */

const chatContainer = document.getElementById('chat-messages');
const receiverId = chatContainer ? chatContainer.dataset.receiver : null;
const inputField = document.getElementById('chat-input');
let lastMessageCount = 0;

function fetchMessages() {
    if (!receiverId) return;

    fetch(`api/fetch_messages.php?user=${receiverId}`)
        .then(response => response.json())
        .then(data => {
            if (data.messages && data.messages.length > lastMessageCount) {
                renderMessages(data.messages);
                lastMessageCount = data.messages.length;
                scrollToBottom();
            }
        })
        .catch(err => console.error('Error fetching messages:', err));
}

function renderMessages(messages) {
    chatContainer.innerHTML = ''; // Clear for simple re-render
    
    messages.forEach(msg => {
        const msgDiv = document.createElement('div');
        msgDiv.className = `max-w-[75%] rounded px-4 py-2 mb-3 shadow text-sm ${msg.is_mine ? 'bg-uitmPurple text-white ml-auto rounded-br-none' : 'bg-gray-100 text-gray-800 mr-auto rounded-bl-none'}`;
        
        msgDiv.innerHTML = `
            <div class="mb-1">${msg.content}</div>
            <div class="text-[10px] text-right ${msg.is_mine ? 'text-purple-200' : 'text-gray-400'}">${msg.timestamp}</div>
        `;
        
        chatContainer.appendChild(msgDiv);
    });
}

function scrollToBottom() {
    chatContainer.scrollTop = chatContainer.scrollHeight;
}

function sendMessage() {
    const content = inputField.value.trim();
    if (!content || !receiverId) return;

    inputField.value = ''; // clear immediately
    
    fetch('api/send_message.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            receiver_id: receiverId,
            content: content
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Fetch immediately to show the new message
            fetchMessages();
        } else {
            alert(data.error || 'Failed to send message.');
        }
    })
    .catch(err => console.error('Error sending message:', err));
}

if (chatContainer) {
    // Initial fetch
    fetchMessages();
    // Poll every 3 seconds
    setInterval(fetchMessages, 3000);
}
