class AjaxHandler {
    static showMessage(message, type = 'info') {
        // Créer ou mettre à jour le div de message
        let messageDiv = document.getElementById('ajax-message');
        if (!messageDiv) {
            messageDiv = document.createElement('div');
            messageDiv.id = 'ajax-message';
            messageDiv.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                padding: 15px;
                border-radius: 5px;
                z-index: 9999;
                max-width: 300px;
                word-wrap: break-word;
            `;
            document.body.appendChild(messageDiv);
        }
        
        // Styles selon le type
        const styles = {
            success: 'background: #d4edda; color: #155724; border: 1px solid #c3e6cb;',
            error: 'background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb;',
            info: 'background: #d1ecf1; color: #0c5460; border: 1px solid #bee5eb;'
        };
        
        messageDiv.style.cssText += styles[type] || styles.info;
        messageDiv.textContent = message;
        
        // Auto-hide après 3 secondes
        setTimeout(() => {
            if (messageDiv.parentNode) {
                messageDiv.parentNode.removeChild(messageDiv);
            }
        }, 3000);
    }
    
    static handleForm(formId, successCallback = null) {
        const form = document.getElementById(formId);
        if (!form) return;
        
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(form);
            const submitBtn = form.querySelector('button[type="submit"]');
            const originalText = submitBtn ? submitBtn.textContent : '';
            
            // Désactiver le bouton pendant la requête
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.textContent = 'Chargement...';
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
                    AjaxHandler.showMessage(data.message, 'success');
                    if (data.redirect) {
                        setTimeout(() => {
                            window.location.href = data.redirect;
                        }, 1000);
                    } else if (successCallback) {
                        successCallback(data);
                    }
                } else {
                    AjaxHandler.showMessage(data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Erreur AJAX:', error);
                AjaxHandler.showMessage('Une erreur est survenue. Veuillez réessayer.', 'error');
            })
            .finally(() => {
                // Réactiver le bouton
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.textContent = originalText;
                }
            });
        });
    }
}

// Auto-initialisation pour les formulaires avec la classe 'ajax-form'
document.addEventListener('DOMContentLoaded', function() {
    const ajaxForms = document.querySelectorAll('.ajax-form');
    ajaxForms.forEach(form => {
        AjaxHandler.handleForm(form.id);
    });
});
