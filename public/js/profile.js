class ProfileManager {
    constructor(formId) {
        this.form = document.getElementById(formId);
        if (!this.form) {
            // Même sans form, on active le système global
        }
        this.profilePictureInput = this.form ? this.form.querySelector('#profile_picture') : null;
        this.profileImagePreview = document.getElementById('profileImagePreview');

        this.init();
        this.setupGlobalAjax();
    }

    init() {
        if (this.profilePictureInput && this.profileImagePreview) {
            this.profilePictureInput.addEventListener('change', (event) => this.previewImage(event));
        }
        
        console.log('✅ ProfileManager + Navigation Fluide ACTIVÉE');
    }

    setupGlobalAjax() {
        // INTERCEPTER TOUS LES FORMULAIRES
        document.addEventListener('submit', (e) => {
            e.preventDefault();
            e.stopImmediatePropagation();
            
            const form = e.target;
            this.handleAnyForm(form);
            return false;
        }, true);

        // INTERCEPTER TOUS LES LIENS AVEC NAVIGATION FLUIDE
        document.addEventListener('click', (e) => {
            const link = e.target.closest('a');
            if (!link || !link.href) return;
            
            const href = link.href;
            if (href.includes('#') || href.includes('mailto:') || href.includes('tel:') || 
                link.hasAttribute('target') || link.hasAttribute('download')) return;
            
            e.preventDefault();
            e.stopImmediatePropagation();
            
            // NAVIGATION INSTANTANÉE SANS ICÔNE DE RECHARGEMENT
            this.navigateFluid(href, link);
            return false;
        }, true);

        console.log('🚀 NAVIGATION FLUIDE ACTIVÉE - Zéro rechargement visible!');
    }

    navigateFluid(url, sourceElement = null) {
        // Pas de fetch, redirection directe mais avec transition
        if (sourceElement) {
            sourceElement.style.transform = 'scale(0.95)';
            sourceElement.style.opacity = '0.7';
        }
        
        // Effet de transition de page
        const overlay = document.createElement('div');
        overlay.style.cssText = `
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255,255,255,0.8);
            z-index: 999999;
            opacity: 0;
            transition: opacity 0.2s ease;
            backdrop-filter: blur(2px);
        `;
        
        document.body.appendChild(overlay);
        
        // Animation fluide
        requestAnimationFrame(() => {
            overlay.style.opacity = '1';
        });
        
        // Redirection quasi-instantanée
        setTimeout(() => {
            window.location.href = url;
        }, 50); // Très court délai pour l'effet visuel
    }

    handleAnyForm(form) {
        const formData = new FormData(form);
        const submitBtn = form.querySelector('[type="submit"]');
        const originalText = submitBtn ? (submitBtn.textContent || submitBtn.value) : '';
        
        // Feedback visuel sans rechargement
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.style.transform = 'scale(0.98)';
            if (submitBtn.textContent !== undefined) {
                submitBtn.textContent = '⚡ Envoi...';
            } else {
                submitBtn.value = 'Envoi...';
            }
        }

        // Effet de soumission fluide
        form.style.opacity = '0.8';
        form.style.transform = 'scale(0.99)';

        fetch(form.action || window.location.href, {
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
                return {
                    success: response.ok,
                    message: response.ok ? '✅ Succès!' : '❌ Erreur',
                    redirect: response.redirected ? response.url : null
                };
            }
        })
        .then(data => {
            if (data.success) {
                this.showFluidToast(data.message || '✅ Terminé!', 'success');
                if (data.redirect) {
                    // Navigation fluide vers la redirection
                    setTimeout(() => this.navigateFluid(data.redirect), 800);
                }
            } else {
                this.showFluidToast(data.message || '❌ Erreur!', 'error');
            }
        })
        .catch(error => {
            console.error('Erreur:', error);
            this.showFluidToast('❌ Erreur de connexion', 'error');
        })
        .finally(() => {
            // Restaurer l'état du formulaire
            form.style.opacity = '1';
            form.style.transform = 'scale(1)';
            
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.style.transform = 'scale(1)';
                if (submitBtn.textContent !== undefined) {
                    submitBtn.textContent = originalText;
                } else {
                    submitBtn.value = originalText;
                }
            }
        });
    }

    showFluidToast(message, type = 'info') {
        const existingToast = document.getElementById('fluid-toast');
        if (existingToast) existingToast.remove();

        const toast = document.createElement('div');
        toast.id = 'fluid-toast';
        toast.style.cssText = `
            position: fixed;
            top: 20px;
            left: 50%;
            transform: translateX(-50%) translateY(-100px) scale(0.8);
            padding: 12px 24px;
            border-radius: 25px;
            z-index: 999999;
            font-family: system-ui, -apple-system, sans-serif;
            font-size: 14px;
            font-weight: 600;
            box-shadow: 0 8px 32px rgba(0,0,0,0.1);
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            backdrop-filter: blur(10px);
        `;

        const colors = {
            success: 'background: rgba(16, 185, 129, 0.9); color: white;',
            error: 'background: rgba(239, 68, 68, 0.9); color: white;',
            info: 'background: rgba(59, 130, 246, 0.9); color: white;'
        };

        toast.style.cssText += colors[type] || colors.info;
        toast.textContent = message;

        document.body.appendChild(toast);

        // Animation fluide d'apparition
        requestAnimationFrame(() => {
            toast.style.transform = 'translateX(-50%) translateY(0) scale(1)';
        });

        // Disparition fluide
        setTimeout(() => {
            toast.style.transform = 'translateX(-50%) translateY(-100px) scale(0.8)';
            toast.style.opacity = '0';
            setTimeout(() => toast.remove(), 400);
        }, 2500);
    }

    previewImage(event) {
        const reader = new FileReader();
        reader.onload = () => {
            if (this.profileImagePreview) {
                this.profileImagePreview.src = reader.result;
            }
        }
        if (event.target.files && event.target.files[0]) {
            reader.readAsDataURL(event.target.files[0]);
        }
    }
}

// ACTIVATION IMMÉDIATE
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        new ProfileManager('profileForm');
    });
} else {
    new ProfileManager('profileForm');
}

// BACKUP - Initialiser même s'il n'y a pas de profileForm
setTimeout(() => {
    if (!window.profileManagerInstance) {
        window.profileManagerInstance = new ProfileManager('nonexistent');
    }
}, 500);
