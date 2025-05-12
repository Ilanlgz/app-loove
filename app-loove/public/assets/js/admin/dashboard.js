// This file contains functionality for the admin dashboard. 

document.addEventListener('DOMContentLoaded', function() {
    // Initialize dashboard elements
    const userStats = document.getElementById('user-stats');
    const reportStats = document.getElementById('report-stats');
    const subscriptionStats = document.getElementById('subscription-stats');

    // Fetch user statistics
    fetch('/api/admin/user-stats')
        .then(response => response.json())
        .then(data => {
            userStats.innerHTML = `Total Users: ${data.totalUsers}`;
        })
        .catch(error => console.error('Error fetching user stats:', error));

    // Fetch report statistics
    fetch('/api/admin/report-stats')
        .then(response => response.json())
        .then(data => {
            reportStats.innerHTML = `Total Reports: ${data.totalReports}`;
        })
        .catch(error => console.error('Error fetching report stats:', error));

    // Fetch subscription statistics
    fetch('/api/admin/subscription-stats')
        .then(response => response.json())
        .then(data => {
            subscriptionStats.innerHTML = `Total Subscriptions: ${data.totalSubscriptions}`;
        })
        .catch(error => console.error('Error fetching subscription stats:', error));

    // Event listener for user management actions
    document.getElementById('user-management').addEventListener('click', function(event) {
        if (event.target.classList.contains('activate-user')) {
            const userId = event.target.dataset.userId;
            manageUser(userId, 'activate');
        } else if (event.target.classList.contains('deactivate-user')) {
            const userId = event.target.dataset.userId;
            manageUser(userId, 'deactivate');
        }
    });

    function manageUser(userId, action) {
        fetch(`/api/admin/manage-user/${userId}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ action: action })
        })
        .then(response => response.json())
        .then(data => {
            alert(data.message);
            // Refresh stats or user list if necessary
        })
        .catch(error => console.error('Error managing user:', error));
    }
});