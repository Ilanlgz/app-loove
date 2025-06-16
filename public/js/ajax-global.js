class AjaxGlobal {
    constructor() {
        this.init();
    }

    init() {
        // Auto-initialiser tous les formulaires avec classe 'ajax-form'
        document.addEventListener('DOMContentLoaded', () => {
            this.initAjaxForms();
            this.initAjaxLinks();
        });
    }

    initAjaxForms() {
        const ajaxForms = document.querySelectorAll('.ajax-form, form[data-ajax="true"]');
        ajaxForms.forEach(form => {
            form.addEventListener('submit', (event) => {
                event.preventDefault();
                this.handleFormSubmit(form);
            });
        });
    }

    initAjaxLinks() {
        const ajaxLinks = document.querySelectorAll('.ajax-link, a[data-ajax="true"]');
        ajaxLinks.forEach(link => {
            link.addEventListener('click', (event) => {
                event.preventDefault();
                this.handleLinkClick(link);
            });
        });
    }

    handleFormSubmit(form) {
        const formData = new FormData(form);
        const submitBtn = form.querySelector('button[type="submit"], input[type="submit"]');
        const originalText = submitBtn ? submitBtn.textContent || submitBtn.value : '';
        
        // Désactiver le bouton pendant la requête
        if (submitBtn) {
            submitBtn.disabled = true;
            if (submitBtn.textContent !== undefined) {
                submitBtn.textContent = 'Chargement...';
            } else {
                submitBtn.value = 'Chargement...';
            }
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
            this.handleResponse(data);
        })
        .catch(error => {
            console.error('Erreur AJAX:', error);
            this.showMessage('Une erreur est survenue. Veuillez réessayer.', 'error');
        })
        .finally(() => {
            // Réactiver le bouton
            if (submitBtn) {
                submitBtn.disabled = false;
                if (submitBtn.textContent !== undefined) {
                    submitBtn.textContent = originalText;
                } else {
                    submitBtn.value = originalText;
                }
            }
        });
    }

    handleLinkClick(link) {
        const url = link.href;
        
        fetch(url, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            this.handleResponse(data);
        })
        .catch(error => {
            console.error('Erreur AJAX:', error);
            // Fallback: redirection normale
            window.location.href = url;
        });
    }

    handleResponse(data) {
        if (data.success) {
            this.showMessage(data.message, 'success');
            if (data.redirect) {
                setTimeout(() => {
                    window.location.href = data.redirect;
                }, 1000);
            }
            if (data.reload) {
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
            }
        } else {
            this.showMessage(data.message, 'error');
        }
    }

    showMessage(message, type = 'info') {
        // Supprimer l'ancien message s'il existe
        const existingMessage = document.getElementById('ajax-message');
        if (existingMessage) {
            existingMessage.remove();
        }

        // Créer le nouveau message
        const messageDiv = document.createElement('div');
        messageDiv.id = 'ajax-message';
        messageDiv.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 15px 20px;
            border-radius: 8px;
            z-index: 9999;
            max-width: 350px;
            word-wrap: break-word;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            font-family: Arial, sans-serif;
            font-size: 14px;
            font-weight: 500;
            transform: translateX(100%);
            transition: transform 0.3s ease-in-out;
        `;
        
        // Styles selon le type
        const styles = {
            success: 'background: #d4edda; color: #155724; border-left: 4px solid #28a745;',
            error: 'background: #f8d7da; color: #721c24; border-left: 4px solid #dc3545;',
            info: 'background: #d1ecf1; color: #0c5460; border-left: 4px solid #17a2b8;',
            warning: 'background: #fff3cd; color: #856404; border-left: 4px solid #ffc107;'
        };
        
        messageDiv.style.cssText += styles[type] || styles.info;
        messageDiv.textContent = message;
        
        document.body.appendChild(messageDiv);
        
        // Animation d'apparition
        setTimeout(() => {
            messageDiv.style.transform = 'translateX(0)';
        }, 100);
        
        // Auto-hide après 4 secondes
        setTimeout(() => {
            messageDiv.style.transform = 'translateX(100%)';
            setTimeout(() => {
                if (messageDiv.parentNode) {
                    messageDiv.parentNode.removeChild(messageDiv);
                }
            }, 300);
        }, 4000);
    }

    // Méthodes utilitaires pour usage externe
    static showMessage(message, type = 'info') {
        const instance = window.ajaxGlobal || new AjaxGlobal();
        instance.showMessage(message, type);
    }

    static submitForm(formSelector) {
        const form = document.querySelector(formSelector);
        if (form) {
            const instance = window.ajaxGlobal || new AjaxGlobal();
            instance.handleFormSubmit(form);
        }
    }
}

// Initialiser automatiquement
window.ajaxGlobal = new AjaxGlobal();
