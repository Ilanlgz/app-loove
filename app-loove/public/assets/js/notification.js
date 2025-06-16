// This file manages notifications for users, including displaying and handling notification events.

document.addEventListener('DOMContentLoaded', function() {
    // Function to display notifications
    function displayNotification(message) {
        const notificationContainer = document.getElementById('notification-container');
        const notification = document.createElement('div');
        notification.className = 'notification';
        notification.innerText = message;

        notificationContainer.appendChild(notification);

        // Automatically remove notification after 5 seconds
        setTimeout(() => {
            notificationContainer.removeChild(notification);
        }, 5000);
    }

    // Example of receiving a notification (this should be replaced with actual event handling)
    function receiveNotification() {
        const message = "You have a new message!";
        displayNotification(message);
    }

    // Simulate receiving a notification for demonstration purposes
    setInterval(receiveNotification, 10000); // Every 10 seconds
});