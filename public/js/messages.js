class MessagesManager {
    constructor() {
        this.init();
    }

    init() {
        // Intercepter l'envoi de messages
        const messageForm = document.getElementById('messageForm');
        if (messageForm) {
            messageForm.addEventListener('submit', (e) => {
                e.preventDefault();
                this.sendMessage(messageForm);
            });
        }

        console.log('✅ Messages Manager initialisé');
    }

    async sendMessage(form) {
        const formData = new FormData(form);
        const submitBtn = form.querySelector('[type="submit"]');
        const originalText = submitBtn ? submitBtn.textContent : '';
        
        // Feedback visuel
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.textContent = '📤 Envoi...';
        }

        try {
            const response = await fetch('process_send_message.php', {
                method: 'POST',
                body: formData
            });

            const data = await response.json();
            
            if (data.success) {
                this.showNotification('✅ Message envoyé!', 'success');
                
                // Vider le formulaire
                const messageInput = form.querySelector('[name="content"]');
                if (messageInput) messageInput.value = '';
                
                // Recharger les messages (optionnel)
                setTimeout(() => {
                    this.refreshMessages();
                }, 500);
                
            } else {
                this.showNotification('❌ ' + data.message, 'error');
            }
            
        } catch (error) {
            console.error('Erreur envoi message:', error);
            this.showNotification('❌ Erreur de connexion', 'error');
        } finally {
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.textContent = originalText;
            }
        }
    }

    refreshMessages() {
        // Recharger la page ou actualiser la conversation
        window.location.reload();
    }

    showNotification(message, type = 'info') {
        const notification = document.createElement('div');
        notification.style.cssText = `
            position: fixed;
            bottom: 20px;
            right: 20px;
            padding: 12px 20px;
            border-radius: 8px;
            z-index: 9999;
            font-weight: 500;
            color: white;
            transform: translateY(100px);
            transition: transform 0.3s ease;
        `;
        
        const colors = {
            success: '#10b981',
            error: '#ef4444',
            info: '#3b82f6'
        };
        
        notification.style.backgroundColor = colors[type] || colors.info;
        notification.textContent = message;
        
        document.body.appendChild(notification);
        
        setTimeout(() => notification.style.transform = 'translateY(0)', 100);
        setTimeout(() => {
            notification.style.transform = 'translateY(100px)';
            setTimeout(() => notification.remove(), 300);
        }, 3000);
    }
}

// Initialiser automatiquement
document.addEventListener('DOMContentLoaded', () => {
    new MessagesManager();
});
