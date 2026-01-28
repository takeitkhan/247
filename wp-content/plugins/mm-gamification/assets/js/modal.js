/**
 * Displays a gamification points notification modal.
 *
 * @param {object} data The data for the modal.
 * @param {string} [data.title='Points Earned!'] The title to display in the modal.
 * @param {string} data.message The descriptive message.
 * @param {number} data.points The number of points earned.
 * @param {string} [data.buttonText='Awesome!'] The text for the close button.
 */
function showGamificationPointsModal(data) {
    // Default values
    const defaults = {
        title: 'Points Earned!',
        message: 'You just earned',
        points: 0,
        buttonText: 'Awesome!',
    };
    // Merge user data with defaults
    const config = { ...defaults, ...data };

    const modalElement = document.getElementById('gamificationPointsModal');
    if (!modalElement) {
        console.error('Gamification modal element not found!');
        return;
    }

    // Get the Bootstrap 5 modal instance
    const gamificationModal = bootstrap.Modal.getOrCreateInstance(modalElement);

    // Update modal content
    const modalTitle = document.getElementById('gamification-modal-title');
    const modalMessage = document.getElementById('gamification-modal-message');

    if (modalTitle) {
        modalTitle.textContent = config.title;
    }
    if (modalMessage) {
        // The message already contains the points from the server-side processing.
        modalMessage.innerHTML = config.message;
    }

    // 🔊 Play notification sound if available (from theme)
    if (typeof playNotificationSound === 'function') {
        console.log('🎵 Playing gamification sound via theme audio system');
        playNotificationSound();
    } else {
        // Fallback: Play sound directly if theme audio system not available
        console.log('📻 Playing gamification sound directly');
        try {
            const audio = new Audio(
                document.currentScript?.getAttribute('data-sound-url') || 
                '/wp-content/themes/247empowerment/assets/sounds/coin.mp3'
            );
            audio.volume = 0.5;
            audio.play().catch(err => console.warn('⚠️ Could not play sound:', err.name));
        } catch (err) {
            console.warn('❌ Sound play failed:', err);
        }
    }

    // Show the modal
    gamificationModal.show();
}

// On page load, check if the server sent any notification data
document.addEventListener('DOMContentLoaded', function() {
    if (typeof gamificationNotification !== 'undefined') {
        showGamificationPointsModal(gamificationNotification);
    }
});
