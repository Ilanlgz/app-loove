// SYSTÈME ANTI-RECHARGEMENT ULTRA SIMPLE
(function() {
    console.log('🔥 ANTI-RELOAD ACTIVÉ');
    
    // Intercepter TOUS les formulaires
    document.addEventListener('submit', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        const form = e.target;
        console.log('❌ FORM BLOQUÉ:', form.action);
        
        // Traitement AJAX
        const formData = new FormData(form);
        const btn = form.querySelector('[type="submit"]');
        if (btn) btn.textContent = 'Chargement...';
        
        fetch(form.action || window.location.href, {
            method: form.method || 'POST',
            body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(r => r.json().catch(() => ({ success: true, message: 'OK' })))
        .then(data => {
            alert(data.message || 'Terminé!');
            if (data.redirect) setTimeout(() => window.location.href = data.redirect, 1000);
        })
        .catch(() => alert('Erreur!'))
        .finally(() => {
            if (btn) btn.textContent = 'Envoyer';
        });
        
        return false;
    }, true);
    
    // Intercepter TOUS les liens
    document.addEventListener('click', function(e) {
        const link = e.target.closest('a');
        if (!link || !link.href) return;
        
        const href = link.href;
        if (href.includes('#') || href.includes('mailto:') || href.includes('tel:')) return;
        
        e.preventDefault();
        e.stopPropagation();
        
        console.log('❌ LINK BLOQUÉ:', href);
        
        // Redirection avec délai
        setTimeout(() => {
            window.location.href = href;
        }, 100);
        
        return false;
    }, true);
    
})();
