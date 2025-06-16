// This file handles the matching logic between users based on preferences.

document.addEventListener('DOMContentLoaded', function() {
    const likeButtons = document.querySelectorAll('.like-button');
    const passButtons = document.querySelectorAll('.pass-button');

    likeButtons.forEach(button => {
        button.addEventListener('click', function() {
            const userId = this.dataset.userId;
            handleMatch(userId, true);
        });
    });

    passButtons.forEach(button => {
        button.addEventListener('click', function() {
            const userId = this.dataset.userId;
            handleMatch(userId, false);
        });
    });

    function handleMatch(userId, isLike) {
        const xhr = new XMLHttpRequest();
        xhr.open('POST', '/api/match', true);
        xhr.setRequestHeader('Content-Type', 'application/json;charset=UTF-8');
        xhr.onload = function() {
            if (xhr.status === 200) {
                const response = JSON.parse(xhr.responseText);
                if (response.success) {
                    alert(isLike ? 'You liked this profile!' : 'You passed on this profile.');
                } else {
                    alert('An error occurred: ' + response.message);
                }
            } else {
                alert('Request failed. Status: ' + xhr.status);
            }
        };
        xhr.send(JSON.stringify({ userId: userId, like: isLike }));
    }
});