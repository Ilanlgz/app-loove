<?php include BASE_PATH . '/app/views/layout/header.php'; ?>
<?php include BASE_PATH . '/app/views/layout/navbar.php'; ?>

<style>
.discover-stats {
    display: flex;
    justify-content: center;
    gap: 2rem;
    margin-bottom: 2rem;
    padding: 1rem;
    background: white;
    border-radius: 15px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.card-stack-container {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 2rem;
}

.card-stack {
    position: relative;
    width: 350px;
    height: 500px;
}

.profile-card {
    position: absolute;
    width: 100%;
    height: 100%;
    background: white;
    border-radius: 20px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.15);
    padding: 1.5rem;
    cursor: grab;
    user-select: none;
}

.profile-image {
    width: 80px;
    height: 80px;
    background: #FF4458;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 2rem;
    font-weight: bold;
    margin: 0 auto 1rem;
}

.profile-info {
    text-align: center;
}

.profile-name {
    font-size: 1.5rem;
    margin-bottom: 0.5rem;
    color: #2d3748;
    font-weight: 600;
}

.action-buttons {
    display: flex;
    gap: 2rem;
    justify-content: center;
}

.btn-pass-large,
.btn-like-large {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    border: none;
    font-size: 1.5rem;
    cursor: pointer;
    transition: all 0.3s;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}

.btn-pass-large {
    background: #f56565;
    color: white;
}

.btn-like-large {
    background: #48bb78;
    color: white;
}
</style>

<div class="container">
    <h1 style='margin-bottom: 2rem; text-align: center;'>Découvrir des profils</h1>

    <div class="discover-stats">
        <span class="stat">♥ 0 likés</span>
        <span class="stat">✗ 0 passés</span>
        <span class="stat">★ 0 matchs</span>
    </div>

    <div class="card-stack-container">
        <div class="card-stack" id="cardStack">
            <!-- Les cartes seront chargées ici par JavaScript -->
        </div>

        <div class="action-buttons">
            <button class="btn-pass-large" onclick="passCurrentProfile()" title="Passer">✗</button>
            <button class="btn-like-large" onclick="likeCurrentProfile()" title="Liker">♥</button>
        </div>
    </div>
</div>

<script>
// JavaScript pour le système de swipe et matching
let currentProfileIndex = 0;
let profiles = [];

// Simuler des profils
const allProfiles = [
    {id: 1, name: 'Emma', age: 25, location: 'Paris', occupation: 'Photographe', bio: 'Passionnée de voyages', interests: 'Voyages, Photo, Art'},
    {id: 2, name: 'Julie', age: 28, location: 'Lyon', occupation: 'Biologiste', bio: 'Amoureuse de la nature', interests: 'Nature, Randonnée, Science'},
    {id: 3, name: 'Sophie', age: 24, location: 'Marseille', occupation: 'Danseuse', bio: 'Artiste dans l\'âme', interests: 'Danse, Art, Musique'}
];

function initializeCards() {
    profiles = [...allProfiles];
    renderCards();
}

function renderCards() {
    const cardStack = document.getElementById('cardStack');
    cardStack.innerHTML = '';
    
    for (let i = 0; i < Math.min(3, profiles.length); i++) {
        const profile = profiles[i];
        const card = createProfileCard(profile, i);
        cardStack.appendChild(card);
    }
}

function createProfileCard(profile, index) {
    const card = document.createElement('div');
    card.className = 'profile-card';
    card.style.zIndex = 3 - index;
    
    card.innerHTML = `
        <div class="profile-image">${profile.name.charAt(0).toUpperCase()}</div>
        <div class="profile-info">
            <h2 class="profile-name">${profile.name}, ${profile.age} ans</h2>
            <div class="profile-detail">📍 ${profile.location}</div>
            <div class="profile-detail">💼 ${profile.occupation}</div>
            <div class="profile-bio">${profile.bio}</div>
        </div>
    `;
    
    return card;
}

function likeCurrentProfile() {
    if (profiles.length === 0) return;
    
    // Simuler un match (33% de chance)
    const isMatch = Math.random() < 0.33;
    
    if (isMatch) {
        showMatchAnimation(profiles[0].name);
    } else {
        showNotification('♥ Profil liké !');
    }
    
    nextProfile();
}

function passCurrentProfile() {
    if (profiles.length === 0) return;
    
    showNotification('👋 Profil passé');
    nextProfile();
}

function nextProfile() {
    profiles.shift();
    if (profiles.length === 0) {
        profiles = [...allProfiles];
    }
    renderCards();
}

function showMatchAnimation(profileName) {
    const overlay = document.createElement('div');
    overlay.style.cssText = `
        position: fixed; top: 0; left: 0; width: 100%; height: 100%;
        background: linear-gradient(135deg, #FF4458, #ff6b6b);
        z-index: 10000; display: flex; align-items: center; justify-content: center;
        color: white; text-align: center; font-size: 2rem;
    `;
    overlay.innerHTML = `
        <div>
            <div style="font-size: 4rem; margin-bottom: 1rem;">♥♥♥</div>
            <h1>C'est un MATCH !</h1>
            <p>Vous et ${profileName} vous êtes likés mutuellement !</p>
        </div>
    `;
    document.body.appendChild(overlay);
    
    setTimeout(() => overlay.remove(), 3000);
}

function showNotification(message) {
    const notification = document.createElement('div');
    notification.style.cssText = `
        position: fixed; top: 20px; right: 20px; background: white;
        padding: 1rem 1.5rem; border-radius: 10px; z-index: 1000;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    `;
    notification.textContent = message;
    document.body.appendChild(notification);
    
    setTimeout(() => notification.remove(), 2000);
}

document.addEventListener('DOMContentLoaded', initializeCards);
</script>

<?php include BASE_PATH . '/app/views/layout/footer.php'; ?>
