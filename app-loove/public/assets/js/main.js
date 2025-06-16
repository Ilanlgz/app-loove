// This file contains general application functionalities.

// Function to handle user registration
function registerUser(event) {
    event.preventDefault();
    const formData = new FormData(event.target);
    fetch('path/to/registration/api', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Registration successful!');
            window.location.href = 'path/to/login/page';
        } else {
            alert('Registration failed: ' + data.message);
        }
    })
    .catch(error => console.error('Error:', error));
}

// Function to handle user login
function loginUser(event) {
    event.preventDefault();
    const formData = new FormData(event.target);
    fetch('path/to/login/api', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Login successful!');
            window.location.href = 'path/to/home/page';
        } else {
            alert('Login failed: ' + data.message);
        }
    })
    .catch(error => console.error('Error:', error));
}

// Function to initialize event listeners
function init() {
    const registrationForm = document.getElementById('registrationForm');
    if (registrationForm) {
        registrationForm.addEventListener('submit', registerUser);
    }

    const loginForm = document.getElementById('loginForm');
    if (loginForm) {
        loginForm.addEventListener('submit', loginUser);
    }
}

// Call init on page load
document.addEventListener('DOMContentLoaded', init);