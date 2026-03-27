// NeuroDiag JavaScript - Basic interactivity

document.addEventListener('DOMContentLoaded', function() {
    // Add smooth scrolling to navigation links
    const navLinks = document.querySelectorAll('nav a');
    navLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            // For now, just prevent default if it's a placeholder
            if (this.getAttribute('href').startsWith('#')) {
                e.preventDefault();
            }
        });
    });

    // Add a simple alert for the start button
    const startBtn = document.querySelector('.btn');
    if (startBtn) {
        startBtn.addEventListener('click', function(e) {
            alert('NeuroDiag: Ihre Diagnostik-Reise beginnt hier!');
        });
    }

    // Basic form validation placeholder for future forms
    // This will be expanded for diagnostic forms
});