document.addEventListener('DOMContentLoaded', function() {
    // Réparation des boutons de conversation
    const conversationButtons = document.querySelectorAll('.conversation-item');
    conversationButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            const userId = this.getAttribute('data-user-id');
            if (userId) {
                window.location.href = 'messages.php?user=' + userId;
            }
        });
    });

    // Réparation des boutons like/dislike
    const likeButtons = document.querySelectorAll('.action-button.like');
    likeButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const userId = this.getAttribute('data-user-id');
            if (userId) {
                likeUser(userId);
            }
        });
    });

    const dislikeButtons = document.querySelectorAll('.action-button.dislike');
    dislikeButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const userId = this.getAttribute('data-user-id');
            if (userId) {
                dislikeUser(userId);
            }
        });
    });
});

// Fonction de like utilisateur
function likeUser(userId) {
    fetch('ajax/like_user.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'user_id=' + userId
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Animation de succès
            const card = document.querySelector(`.user-card[data-user-id="${userId}"]`);
            if (card) {
                card.classList.add('liked');
                setTimeout(() => {
                    card.style.display = 'none';
                    loadNextUser();
                }, 500);
            }
            
            // Si c'est un match, afficher la notification
            if (data.match) {
                showMatchNotification(data.user_name);
            }
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
    });
}

// Fonction de dislike utilisateur
function dislikeUser(userId) {
    fetch('ajax/dislike_user.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'user_id=' + userId
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Animation de succès
            const card = document.querySelector(`.user-card[data-user-id="${userId}"]`);
            if (card) {
                card.classList.add('disliked');
                setTimeout(() => {
                    card.style.display = 'none';
                    loadNextUser();
                }, 500);
            }
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
    });
}

// Fonction pour charger l'utilisateur suivant
function loadNextUser() {
    // Utilise AJAX pour charger un nouvel utilisateur
    const nextCard = document.querySelector('.user-card[style="display: none;"]');
    if (nextCard) {
        nextCard.style.display = '';
    } else {
        // Plus d'utilisateurs à afficher, recharger la page
        location.reload();
    }
}