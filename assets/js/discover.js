/**
 * Script pour la page discover.php
 * Gère les fonctionnalités de like/dislike
 */

document.addEventListener("DOMContentLoaded", function() {
    // Gestion des boutons Like
    var likeButtons = document.querySelectorAll(".action-button.like");
    for (var i = 0; i < likeButtons.length; i++) {
        likeButtons[i].onclick = function() {
            var userId = this.getAttribute("data-user-id");
            if (userId) {
                window.location.href = "ajax/like_user_simple.php?user_id=" + userId;
            }
        };
    }
    
    // Gestion des boutons Dislike
    var dislikeButtons = document.querySelectorAll(".action-button.dislike");
    for (var i = 0; i < dislikeButtons.length; i++) {
        dislikeButtons[i].onclick = function() {
            var userId = this.getAttribute("data-user-id");
            if (userId) {
                window.location.href = "ajax/dislike_user_simple.php?user_id=" + userId;
            }
        };
    }
    
    // Raccourcis clavier
    document.onkeydown = function(e) {
        var activeCard = document.querySelector(".user-card.active");
        if (!activeCard) return;
        
        var userId = activeCard.getAttribute("data-user-id");
        if (!userId) return;
        
        if (e.key === "ArrowLeft") {
            window.location.href = "ajax/dislike_user_simple.php?user_id=" + userId;
        } else if (e.key === "ArrowRight") {
            window.location.href = "ajax/like_user_simple.php?user_id=" + userId;
        }
    };
});
