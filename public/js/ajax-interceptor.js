class AjaxInterceptor {
    constructor() {
        this.isEnabled = true;
        this.init();
    }

    init() {
        // Attendre que le DOM soit chargé
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => this.setupInterceptors());
        } else {
            this.setupInterceptors();
        }
    }

    setupInterceptors() {
        // Intercepter TOUS les formulaires
        this.interceptForms();
        
        // Intercepter TOUS les liens (sauf exceptions)
        this.interceptLinks();
        
        // Observer les nouveaux éléments ajoutés dynamiquement
        this.observeNewElements();
        
        console.log('AjaxInterceptor initialized - No more page reloads!');
    }

    interceptForms() {
        // Intercepter tous les formulaires existants
        document.querySelectorAll('form').forEach(form => {
            this.setupFormInterception(form);
        });
    }

    setupFormInterception(form) {
        // Éviter de dupliquer les event listeners
        if (form.hasAttribute('data-ajax-intercepted')) return;
        form.setAttribute('data-ajax-intercepted', 'true');

        form.addEventListener('submit', (event) => {
            event.preventDefault();
            event.stopPropagation();
            this.handleFormSubmit(form);
        }, true); // useCapture = true pour intercepter avant tout
    }

    interceptLinks() {
        document.querySelectorAll('a').forEach(link => {
            this.setupLinkInterception(link);
        });
    }

    setupLinkInterception(link) {
        // Éviter de dupliquer les event listeners
        if (link.hasAttribute('data-ajax-intercepted')) return;
        
        // Exceptions - ne pas intercepter ces liens
        const href = link.getAttribute('href');
        if (!href || 
            href.startsWith('#') || 
            href.startsWith('mailto:') || 
            href.startsWith('tel:') || 
            href.startsWith('javascript:') ||
            link.hasAttribute('target') ||
            link.hasAttribute('download') ||
            link.classList.contains('no-ajax')) {
            return;
        }

        link.setAttribute('data-ajax-intercepted', 'true');
        
        link.addEventListener('click', (event) => {
            event.preventDefault();
            event.stopPropagation();
            this.handleLinkClick(link);
        }, true); // useCapture = true
    }

    handleFormSubmit(form) {
        const formData = new FormData(form);
        const submitBtn = form.querySelector('button[type="submit"], input[type="submit"]');
        const originalText = this.getButtonText(submitBtn);
        
        // Visual feedback
        this.setButtonLoading(submitBtn, true);
        
        const action = form.getAttribute('action') || window.location.href;
        
        fetch(action, {
            method: form.method || 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => {
            const contentType = response.headers.get('content-type');
            if (contentType && contentType.includes('application/json')) {
                return response.json();
            } else {
                // Si pas JSON, traiter comme HTML
                return response.text().then(html => ({
                    success: response.ok,
                    message: response.ok ? 'Opération réussie' : 'Une erreur est survenue',
                    html: html
                }));
            }
        })
        .then(data => {
            this.handleResponse(data, form);
        })
        .catch(error => {
            console.error('Erreur AJAX Form:', error);
            this.showMessage('Une erreur est survenue. Veuillez réessayer.', 'error');
        })
        .finally(() => {
            this.setButtonLoading(submitBtn, false, originalText);
        });
    }

    handleLinkClick(link) {
        const href = link.getAttribute('href');
        
        // Visual feedback
        const originalText = link.textContent;
        link.style.opacity = '0.6';
        
        fetch(href, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => {
            const contentType = response.headers.get('content-type');
            if (contentType && contentType.includes('application/json')) {
                return response.json();
            } else {
                // Pour les liens normaux, rediriger après un court délai
                setTimeout(() => {
                    window.location.href = href;
                }, 100);
                return { success: true, message: 'Redirection...' };
            }
        })
        .then(data => {
            if (data) {
                this.handleResponse(data);
            }
        })
        .catch(error => {
            console.error('Erreur AJAX Link:', error);
            // Fallback: redirection normale
            window.location.href = href;
        })
        .finally(() => {
            link.style.opacity = '1';
        });
    }

    handleResponse(data, sourceElement = null) {
        if (data.success) {
            if (data.message) {
                this.showMessage(data.message, 'success');
            }
            
            if (data.redirect) {
                setTimeout(() => {
                    window.location.href = data.redirect;
                }, 1000);
            } else if (data.reload) {
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
            } else if (data.html && sourceElement) {
                // Remplacer le contenu si HTML fourni
                this.updateContent(data.html, sourceElement);
            }
        } else {
            this.showMessage(data.message || 'Une erreur est survenue', 'error');
        }
    }

    updateContent(html, sourceElement) {
        // Mise à jour partielle du contenu si possible
        const tempDiv = document.createElement('div');
        tempDiv.innerHTML = html;
        
        // Logique pour mettre à jour le contenu
        // (peut être étendue selon les besoins)
    }

    getButtonText(button) {
        if (!button) return '';
        return button.textContent || button.value || '';
    }

    setButtonLoading(button, isLoading, originalText = '') {
        if (!button) return;
        
        if (isLoading) {
            button.disabled = true;
            if (button.textContent !== undefined) {
                button.textContent = 'Chargement...';
            } else {
                button.value = 'Chargement...';
            }
        } else {
            button.disabled = false;
            if (button.textContent !== undefined) {
                button.textContent = originalText;
            } else {
                button.value = originalText;
            }
        }
    }

    observeNewElements() {
        // Observer pour les éléments ajoutés dynamiquement
        const observer = new MutationObserver((mutations) => {
            mutations.forEach((mutation) => {
                mutation.addedNodes.forEach((node) => {
                    if (node.nodeType === 1) { // Element node
                        // Nouveaux formulaires
                        if (node.tagName === 'FORM') {
                            this.setupFormInterception(node);
                        } else {
                            node.querySelectorAll && node.querySelectorAll('form').forEach(form => {
                                this.setupFormInterception(form);
                            });
                        }
                        
                        // Nouveaux liens
                        if (node.tagName === 'A') {
                            this.setupLinkInterception(node);
                        } else {
                            node.querySelectorAll && node.querySelectorAll('a').forEach(link => {
                                this.setupLinkInterception(link);
                            });
                        }
                    }
                });
            });
        });

        observer.observe(document.body, {
            childList: true,
            subtree: true
        });
    }

    showMessage(message, type = 'info') {
        // Supprimer l'ancien message
        const existingMessage = document.getElementById('ajax-message');
        if (existingMessage) {
            existingMessage.remove();
        }

        const messageDiv = document.createElement('div');
        messageDiv.id = 'ajax-message';
        messageDiv.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 15px 20px;
            border-radius: 8px;
            z-index: 99999;
            max-width: 350px;
            word-wrap: break-word;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            font-family: Arial, sans-serif;
            font-size: 14px;
            font-weight: 500;
            transform: translateX(100%);
            transition: transform 0.3s ease-in-out;
        `;
        
        const styles = {
            success: 'background: #d4edda; color: #155724; border-left: 4px solid #28a745;',
            error: 'background: #f8d7da; color: #721c24; border-left: 4px solid #dc3545;',
            info: 'background: #d1ecf1; color: #0c5460; border-left: 4px solid #17a2b8;'
        };
        
        messageDiv.style.cssText += styles[type] || styles.info;
        messageDiv.textContent = message;
        
        document.body.appendChild(messageDiv);
        
        setTimeout(() => {
            messageDiv.style.transform = 'translateX(0)';
        }, 100);
        
        setTimeout(() => {
            messageDiv.style.transform = 'translateX(100%)';
            setTimeout(() => {
                if (messageDiv.parentNode) {
                    messageDiv.remove();
                }
            }, 300);
        }, 4000);
    }
}

// Auto-initialisation dès que le script est chargé
window.ajaxInterceptor = new AjaxInterceptor();
