class MessageHandler {
    constructor() {
        this.init();
    }

    init() {
        // Intercepter tous les formulaires de messages
        document.addEventListener('submit', (e) => {
            const form = e.target;
            if (form.classList.contains('message-form') || form.id.includes('message')) {
                e.preventDefault();
                this.handleMessageForm(form);
            }
        });

        // Intercepter les liens de messages
        document.addEventListener('click', (e) => {
            const link = e.target.closest('a');
            if (link && (link.classList.contains('message-link') || link.href.includes('message'))) {
                e.preventDefault();
                this.handleMessageLink(link);
            }
        });

        console.log('✅ MessageHandler AJAX activé');
    }

    handleMessageForm(form) {
        const formData = new FormData(form);
        const submitBtn = form.querySelector('[type="submit"]');
        const originalText = submitBtn ? submitBtn.textContent : '';

        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.textContent = '📤 Envoi...';
        }

        fetch(form.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                this.showToast(data.message, 'success');
                form.reset(); // Vider le formulaire
                
                // Recharger les messages si nécessaire
                if (data.reload_messages) {
                    this.reloadMessages();
                }
            } else {
                this.showToast(data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Erreur:', error);
            this.showToast('Erreur de connexion', 'error');
        })
        .finally(() => {
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.textContent = originalText;
            }
        });
    }

    handleMessageLink(link) {
        const href = link.href;
        
        fetch(href, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                if (data.redirect) {
                    window.location.href = data.redirect;
                } else if (data.html) {
                    this.updateContent(data.html);
                }
            } else {
                this.showToast(data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Erreur:', error);
            window.location.href = href; // Fallback
        });
    }

    reloadMessages() {
        const messagesContainer = document.getElementById('messages-container');
        if (messagesContainer) {
            // Logique pour recharger les messages
            messagesContainer.style.opacity = '0.5';
            
            setTimeout(() => {
                messagesContainer.style.opacity = '1';
            }, 500);
        }
    }

    updateContent(html) {
        const container = document.getElementById('main-content');
        if (container) {
            container.innerHTML = html;
        }
    }

    showToast(message, type = 'info') {
        const existingToast = document.getElementById('message-toast');
        if (existingToast) existingToast.remove();

        const toast = document.createElement('div');
        toast.id = 'message-toast';
        toast.style.cssText = `
            position: fixed;
            bottom: 20px;
            right: 20px;
            padding: 12px 20px;
            border-radius: 8px;
            z-index: 999999;
            max-width: 300px;
            font-family: system-ui, sans-serif;
            font-size: 14px;
            font-weight: 500;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            transform: translateY(100px);
            transition: transform 0.3s ease;
        `;

        const colors = {
            success: 'background: #10b981; color: white;',
            error: 'background: #ef4444; color: white;',
            info: 'background: #3b82f6; color: white;'
        };

        toast.style.cssText += colors[type] || colors.info;
        toast.textContent = message;

        document.body.appendChild(toast);

        setTimeout(() => toast.style.transform = 'translateY(0)', 100);
        setTimeout(() => {
            toast.style.transform = 'translateY(100px)';
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    }
}

// Initialiser automatiquement
new MessageHandler();
