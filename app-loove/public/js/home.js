document.addEventListener('DOMContentLoaded', function() {
    // Gestion des stories
    const stories = document.querySelectorAll('.story:not(.add-story)');
    const storyModal = document.getElementById('storyModal');
    if (!storyModal) return; // Sortir si l'élément n'existe pas
    
    const closeStoryBtn = document.querySelector('.close-story');
    const progressBar = document.querySelector('.progress-bar');
    const storyImage = document.querySelector('.story-image');
    const storyUserAvatar = document.querySelector('.story-user-avatar');
    const storyUserName = document.querySelector('.story-user-name');
    const storyTimestamp = document.querySelector('.story-timestamp');
    
    // Données des stories (pour la démo)
    const storyData = [
        { 
            id: 1000, 
            userId: 101, 
            userName: 'Emma Martin', 
            userAvatar: 'demo-user-1.jpg', 
            image: 'story-1.jpg', 
            timestamp: '2 heures' 
        },
        { 
            id: 1001, 
            userId: 102, 
            userName: 'Thomas Dubois', 
            userAvatar: 'demo-user-2.jpg', 
            image: 'story-2.jpg', 
            timestamp: '3 heures' 
        },
        { 
            id: 1002, 
            userId: 103, 
            userName: 'Sophie Laurent', 
            userAvatar: 'demo-user-3.jpg', 
            image: 'story-3.jpg', 
            timestamp: '5 heures' 
        },
        { 
            id: 1003, 
            userId: 104, 
            userName: 'Lucas Bernard', 
            userAvatar: 'demo-user-4.jpg', 
            image: 'story-4.jpg', 
            timestamp: '1 heure' 
        },
        { 
            id: 1004, 
            userId: 105, 
            userName: 'Chloé Moreau', 
            userAvatar: 'demo-user-5.jpg', 
            image: 'story-5.jpg', 
            timestamp: '30 minutes' 
        }
    ];
    
    stories.forEach(story => {
        story.addEventListener('click', function() {
            const storyId = parseInt(this.getAttribute('data-story-id'));
            openStory(storyId);
        });
    });
    
    function openStory(storyId) {
        // Trouver les données de la story
        const story = storyData.find(s => s.id === storyId) || storyData[0];
        
        // Construire les chemins d'URL pour les images
        const baseUrl = window.location.pathname.split('/public')[0] + '/public';
        const avatarSrc = `${baseUrl}/assets/images/demo/${story.userAvatar}`;
        const imageSrc = `${baseUrl}/assets/images/demo/${story.image}`;
        
        // Mettre à jour le contenu modal
        storyUserAvatar.src = avatarSrc;
        storyUserName.textContent = story.userName;
        storyTimestamp.textContent = story.timestamp;
        storyImage.src = imageSrc;
        
        // Afficher le modal
        storyModal.style.display = 'flex';
        
        // Animer la barre de progression
        progressBar.style.width = '0';
        setTimeout(() => {
            progressBar.style.width = '100%';
        }, 100);
        
        // Fermer automatiquement après 5 secondes
        setTimeout(() => {
            closeStory();
        }, 5100);
    }
    
    function closeStory() {
        storyModal.style.display = 'none';
        progressBar.style.width = '0';
    }
    
    if (closeStoryBtn) {
        closeStoryBtn.addEventListener('click', closeStory);
    }
    
    // Fermer en cliquant à l'extérieur
    storyModal.addEventListener('click', function(e) {
        if (e.target === storyModal) {
            closeStory();
        }
    });
    
    // Gestion des filtres de posts
    const tabBtns = document.querySelectorAll('.tab-btn');
    const postCards = document.querySelectorAll('.post-card');
    
    tabBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            // Activer le bouton
            tabBtns.forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            
            // Filtrer les posts (simulé pour la démo)
            const filter = this.getAttribute('data-filter');
            filterPosts(filter);
        });
    });
    
    function filterPosts(filter) {
        // Cette fonction simule un filtrage pour la démo
        // Dans un cas réel, vous feriez une requête AJAX ou un filtrage côté serveur
        postCards.forEach(card => {
            // Animation de sortie
            card.style.opacity = '0';
            card.style.transform = 'translateY(20px)';
            
            setTimeout(() => {
                // Animation d'entrée après un délai aléatoire
                setTimeout(() => {
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, Math.random() * 300);
            }, 300);
        });
    }
    
    // Gestion des likes (simulé pour la démo)
    const likeButtons = document.querySelectorAll('.btn-like');
    
    likeButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            const icon = this.querySelector('i');
            const countElement = this.querySelector('.like-count');
            let count = parseInt(countElement.textContent);
            
            if (icon.classList.contains('fa-heart-o')) {
                // Like
                icon.classList.remove('fa-heart-o');
                icon.classList.add('fa-heart');
                icon.style.color = '#e74c3c';
                countElement.textContent = count + 1;
            } else {
                // Unlike
                icon.classList.remove('fa-heart');
                icon.classList.add('fa-heart-o');
                icon.style.color = '';
                countElement.textContent = count - 1;
            }
        });
    });
    
    // Animation initiale des posts
    postCards.forEach((card, index) => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        card.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
        card.style.transitionDelay = (index * 0.05) + 's';
        
        setTimeout(() => {
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';
        }, 100);
    });
});
