// Basic class structure for future JavaScript enhancements
class AppCore {
    constructor() {
        this.initEventListeners();
        console.log('Loove App Core Initialized');
    }

    initEventListeners() {
        // Example: Smooth scroll for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const targetId = this.getAttribute('href');
                const targetElement = document.querySelector(targetId);
                if (targetElement) {
                    targetElement.scrollIntoView({
                        behavior: 'smooth'
                    });
                }
            });
        });

        // Mobile navigation toggle (if you add a burger menu)
        // const mobileNavToggle = document.getElementById('mobileNavToggle');
        // const mainNav = document.querySelector('.navbar-nav');
        // if (mobileNavToggle && mainNav) {
        //     mobileNavToggle.addEventListener('click', () => {
        //         mainNav.classList.toggle('active');
        //     });
        // }
    }

    // Utility function to show messages dynamically
    showMessage(type, message, duration = 5000) {
        const messageContainer = document.createElement('div');
        messageContainer.className = `message ${type} dynamic-message`;
        messageContainer.textContent = message;
        
        document.body.insertBefore(messageContainer, document.body.firstChild);

        setTimeout(() => {
            messageContainer.style.opacity = '0';
            setTimeout(() => messageContainer.remove(), 500);
        }, duration);

        // Style for dynamic messages (add to CSS or here)
        const style = document.createElement('style');
        style.innerHTML = `
            .dynamic-message {
                position: fixed;
                top: 20px;
                left: 50%;
                transform: translateX(-50%);
                z-index: 9999;
                padding: 15px 25px;
                border-radius: 8px;
                box-shadow: 0 4px 15px rgba(0,0,0,0.2);
                opacity: 1;
                transition: opacity 0.5s ease-out;
            }
            .dynamic-message.success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
            .dynamic-message.error { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
            .dynamic-message.info { background-color: #cce5ff; color: #004085; border: 1px solid #b8daff; }
        `;
        document.head.appendChild(style);
    }
}

// Initialize the app core logic when the DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    window.looveApp = new AppCore();
});

// Helper function for previewing image before upload
function previewImage(event) {
    const reader = new FileReader();
    const imagePreview = document.getElementById('profileImagePreview'); // Ensure this ID exists on your img tag
    if (!imagePreview) {
        console.warn('Image preview element not found.');
        return;
    }
    reader.onload = function(){
        imagePreview.src = reader.result;
    }
    if (event.target.files[0]) {
        reader.readAsDataURL(event.target.files[0]);
    }
}
