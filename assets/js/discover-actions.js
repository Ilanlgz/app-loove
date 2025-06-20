/**
 * Actions pour la page discover.php
 */
window.onload = function() {
    // Like buttons
    var likes = document.querySelectorAll(".action-button.like");
    for (var i = 0; i < likes.length; i++) {
        likes[i].onclick = function() {
            var id = this.getAttribute("data-user-id");
            if (id) window.location = "ajax/like_user_simple.php?user_id=" + id;
        };
    }
    
    // Dislike buttons
    var dislikes = document.querySelectorAll(".action-button.dislike");
    for (var i = 0; i < dislikes.length; i++) {
        dislikes[i].onclick = function() {
            var id = this.getAttribute("data-user-id");
            if (id) window.location = "ajax/dislike_user_simple.php?user_id=" + id;
        };
    }
    
    // Keyboard shortcuts
    window.onkeydown = function(e) {
        var card = document.querySelector(".user-card.active");
        if (!card) return;
        var id = card.getAttribute("data-user-id");
        if (!id) return;
        if (e.key === "ArrowLeft") window.location = "ajax/dislike_user_simple.php?user_id=" + id;
        if (e.key === "ArrowRight") window.location = "ajax/like_user_simple.php?user_id=" + id;
    };
}

        if (e.key === "ArrowRight") {
            window.location.href = "ajax/like_user_simple.php?user_id=" + id;
        }
    

            window.location.href = "ajax/like_user_simple.php?user_id=" + userId;
        


