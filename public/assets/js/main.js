/**
 * BNGRC - Application de Gestion des Dons
 * JavaScript Principal
 */

document.addEventListener('DOMContentLoaded', function() {
    
    // Animation des compteurs statistiques
    animateCounters();
    
    // Animation au scroll
    initScrollAnimations();
    
    // Navbar scroll effect
    initNavbarScroll();
    
    // Tooltips Bootstrap
    initTooltips();
});

/**
 * Animation des compteurs statistiques
 */
function animateCounters() {
    const counters = document.querySelectorAll('.stat-number');
    
    counters.forEach(counter => {
        const target = parseInt(counter.innerText.replace(/[^0-9]/g, '')) || 0;
        const suffix = counter.innerText.replace(/[0-9,]/g, '').trim();
        const duration = 1500;
        const step = target / (duration / 16);
        let current = 0;
        
        const updateCounter = () => {
            current += step;
            if (current < target) {
                counter.innerText = formatNumber(Math.floor(current)) + (suffix ? ' ' + suffix : '');
                requestAnimationFrame(updateCounter);
            } else {
                counter.innerText = formatNumber(target) + (suffix ? ' ' + suffix : '');
            }
        };
        
        // Start animation when element is visible
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    updateCounter();
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.5 });
        
        observer.observe(counter);
    });
}

/**
 * Formater les nombres avec séparateurs
 */
function formatNumber(num) {
    return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',');
}

/**
 * Animation au scroll
 */
function initScrollAnimations() {
    const animatedElements = document.querySelectorAll('.stat-card, .modern-card, .donation-item, .urgent-need-card');
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry, index) => {
            if (entry.isIntersecting) {
                setTimeout(() => {
                    entry.target.classList.add('animate-fade-in');
                }, index * 100);
                observer.unobserve(entry.target);
            }
        });
    }, {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    });
    
    animatedElements.forEach(el => {
        el.style.opacity = '0';
        observer.observe(el);
    });
}

/**
 * Effet de scroll sur la navbar
 */
function initNavbarScroll() {
    const navbar = document.querySelector('.navbar');
    
    if (!navbar) return;
    
    window.addEventListener('scroll', () => {
        if (window.scrollY > 50) {
            navbar.classList.add('navbar-scrolled');
        } else {
            navbar.classList.remove('navbar-scrolled');
        }
    });
}

/**
 * Initialisation des tooltips Bootstrap
 */
function initTooltips() {
    const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    tooltipTriggerList.forEach(tooltipTriggerEl => {
        new bootstrap.Tooltip(tooltipTriggerEl);
    });
}

/**
 * Confirmation de suppression
 */
function confirmDelete(message = 'Êtes-vous sûr de vouloir supprimer cet élément ?') {
    return confirm(message);
}

/**
 * Afficher une notification toast
 */
function showToast(message, type = 'success') {
    const toastContainer = document.getElementById('toastContainer') || createToastContainer();
    
    const toast = document.createElement('div');
    toast.className = `toast align-items-center text-white bg-${type} border-0`;
    toast.setAttribute('role', 'alert');
    toast.innerHTML = `
        <div class="d-flex">
            <div class="toast-body">
                <i class="bi bi-${type === 'success' ? 'check-circle' : type === 'danger' ? 'x-circle' : 'info-circle'} me-2"></i>
                ${message}
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    `;
    
    toastContainer.appendChild(toast);
    const bsToast = new bootstrap.Toast(toast, { autohide: true, delay: 4000 });
    bsToast.show();
    
    toast.addEventListener('hidden.bs.toast', () => toast.remove());
}

/**
 * Créer le conteneur de toasts
 */
function createToastContainer() {
    const container = document.createElement('div');
    container.id = 'toastContainer';
    container.className = 'toast-container position-fixed bottom-0 end-0 p-3';
    container.style.zIndex = '1100';
    document.body.appendChild(container);
    return container;
}

/**
 * Formater un montant en Ariary
 */
function formatCurrency(amount) {
    return new Intl.NumberFormat('fr-MG', {
        style: 'decimal',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0
    }).format(amount) + ' Ar';
}

/**
 * Calculer le pourcentage de progression
 */
function calculateProgress(current, total) {
    if (total === 0) return 0;
    return Math.min(100, Math.round((current / total) * 100));
}

/**
 * Debounce function pour optimiser les recherches
 */
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}
