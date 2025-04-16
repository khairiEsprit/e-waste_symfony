/**
 * Gamification System JavaScript
 * Handles animations, interactions, and dynamic elements for the gamification system
 */

document.addEventListener('DOMContentLoaded', function() {
    // Initialize animations
    initAnimations();
    
    // Initialize counters
    initCounters();
    
    // Initialize confetti for badges
    initConfetti();
    
    // Initialize tooltips
    initTooltips();
    
    // Check for flash messages to trigger badge earned animation
    checkForBadgeEarned();
});

/**
 * Initialize animations for elements
 */
function initAnimations() {
    // Add animation classes to elements
    const fadeInElements = document.querySelectorAll('.stats-card');
    fadeInElements.forEach((el, index) => {
        el.classList.add('animate-fadeIn');
        el.classList.add(`delay-${(index + 1) * 100}`);
    });
    
    const scaleInElements = document.querySelectorAll('.badge-card');
    scaleInElements.forEach((el, index) => {
        el.classList.add('animate-scaleIn');
        el.classList.add(`delay-${(index + 1) * 100}`);
    });
    
    const slideLeftElements = document.querySelectorAll('.action-card');
    slideLeftElements.forEach((el, index) => {
        el.classList.add('animate-slideInLeft');
        el.classList.add(`delay-${(index + 1) * 100}`);
    });
    
    // Add pulse animation to important elements
    const pulseElements = document.querySelectorAll('.btn-primary, .leaderboard-rank i');
    pulseElements.forEach(el => {
        el.classList.add('animate-pulse');
    });
}

/**
 * Initialize number counters for statistics
 */
function initCounters() {
    const counters = document.querySelectorAll('.counter-value');
    
    counters.forEach(counter => {
        const target = parseInt(counter.getAttribute('data-target'));
        const duration = 1500; // ms
        const step = Math.ceil(target / (duration / 16)); // 60fps
        
        let current = 0;
        const updateCounter = () => {
            current += step;
            if (current > target) current = target;
            counter.textContent = current;
            
            if (current < target) {
                requestAnimationFrame(updateCounter);
            }
        };
        
        updateCounter();
    });
}

/**
 * Initialize confetti animation for badge cards
 */
function initConfetti() {
    // Create confetti container
    const confettiContainer = document.createElement('div');
    confettiContainer.className = 'confetti-container';
    confettiContainer.style.position = 'fixed';
    confettiContainer.style.top = '0';
    confettiContainer.style.left = '0';
    confettiContainer.style.width = '100%';
    confettiContainer.style.height = '100%';
    confettiContainer.style.pointerEvents = 'none';
    confettiContainer.style.zIndex = '9999';
    
    // Add confetti pieces
    for (let i = 1; i <= 9; i++) {
        const confetti = document.createElement('div');
        confetti.className = `confetti-piece confetti${i}`;
        confettiContainer.appendChild(confetti);
    }
    
    document.body.appendChild(confettiContainer);
}

/**
 * Initialize tooltips for interactive elements
 */
function initTooltips() {
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
}

/**
 * Check for flash messages indicating a badge was earned
 */
function checkForBadgeEarned() {
    const flashMessages = document.querySelectorAll('.alert-success');
    
    flashMessages.forEach(message => {
        if (message.textContent.includes('earned a new reward') || message.textContent.includes('badge')) {
            // Show confetti
            showConfetti();
            
            // Add special highlight to the message
            message.style.animation = 'pulse 2s infinite';
            message.style.border = '2px solid gold';
            message.style.background = 'rgba(255, 215, 0, 0.1)';
        }
    });
}

/**
 * Show confetti animation
 */
function showConfetti() {
    const confettiContainer = document.querySelector('.confetti-container');
    if (confettiContainer) {
        confettiContainer.style.display = 'block';
        
        // Hide confetti after animation completes
        setTimeout(() => {
            confettiContainer.style.display = 'none';
        }, 5000);
    }
}

/**
 * Progress bar animation
 */
function animateProgressBars() {
    const progressBars = document.querySelectorAll('.progress-bar');
    
    progressBars.forEach(bar => {
        const width = bar.getAttribute('aria-valuenow') + '%';
        bar.style.width = '0%';
        
        setTimeout(() => {
            bar.style.width = width;
        }, 200);
    });
}

// Call progress bar animation when page loads
window.addEventListener('load', animateProgressBars);

/**
 * Handle action submission with animation
 */
document.addEventListener('DOMContentLoaded', function() {
    const actionForm = document.querySelector('form[action*="action/submit"]');
    
    if (actionForm) {
        actionForm.addEventListener('submit', function(e) {
            const submitButton = this.querySelector('button[type="submit"]');
            
            if (submitButton) {
                // Disable button and show loading state
                submitButton.disabled = true;
                submitButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Processing...';
            }
        });
    }
});
