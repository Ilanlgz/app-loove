// This file handles profile-related functionalities for the application.
// It includes functions for editing and viewing user profiles, as well as managing profile data.

document.addEventListener('DOMContentLoaded', function() {
    const editProfileForm = document.getElementById('editProfileForm');
    const viewProfileButton = document.getElementById('viewProfileButton');
    const profileDataContainer = document.getElementById('profileData');

    // Function to handle profile editing
    if (editProfileForm) {
        editProfileForm.addEventListener('submit', function(event) {
            event.preventDefault();
            const formData = new FormData(editProfileForm);
            fetch('path/to/profile/update', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Profile updated successfully!');
                    // Optionally, refresh the profile data
                    loadProfileData();
                } else {
                    alert('Error updating profile: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
        });
    }

    // Function to load profile data
    function loadProfileData() {
        fetch('path/to/profile/data')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    profileDataContainer.innerHTML = `
                        <h2>${data.profile.name}</h2>
                        <p>${data.profile.description}</p>
                        <img src="${data.profile.photo}" alt="Profile Picture">
                    `;
                } else {
                    profileDataContainer.innerHTML = '<p>Error loading profile data.</p>';
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
    }

    // Load profile data on page load
    loadProfileData();

    // Event listener for viewing profile
    if (viewProfileButton) {
        viewProfileButton.addEventListener('click', function() {
            loadProfileData();
        });
    }
});