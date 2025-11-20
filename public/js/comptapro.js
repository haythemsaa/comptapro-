/**
 * ComptaPro Tunisia - Main JavaScript
 * Animations, interactions et utilitaires
 */

// Configuration
const Config = {
    API_BASE_URL: '/api/v1',
    TOAST_DURATION: 5000,
    ANIMATION_DURATION: 300
};

// Utility functions
const Utils = {
    /**
     * Format number with thousands separator
     */
    formatNumber(num, decimals = 0) {
        return new Intl.NumberFormat('fr-TN', {
            minimumFractionDigits: decimals,
            maximumFractionDigits: decimals
        }).format(num);
    },

    /**
     * Format currency
     */
    formatCurrency(amount, currency = 'TND') {
        return new Intl.NumberFormat('fr-TN', {
            style: 'currency',
            currency: currency
        }).format(amount);
    },

    /**
     * Format date
     */
    formatDate(date, format = 'short') {
        const options = format === 'short'
            ? { year: 'numeric', month: '2-digit', day: '2-digit' }
            : { year: 'numeric', month: 'long', day: 'numeric' };

        return new Intl.DateTimeFormat('fr-FR', options).format(new Date(date));
    },

    /**
     * Debounce function
     */
    debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    },

    /**
     * Throttle function
     */
    throttle(func, limit) {
        let inThrottle;
        return function() {
            const args = arguments;
            const context = this;
            if (!inThrottle) {
                func.apply(context, args);
                inThrottle = true;
                setTimeout(() => inThrottle = false, limit);
            }
        };
    }
};

// Toast Notifications
const Toast = {
    container: null,

    init() {
        if (!this.container) {
            this.container = document.createElement('div');
            this.container.className = 'toast-container';
            document.body.appendChild(this.container);
        }
    },

    show(type, title, message, duration = Config.TOAST_DURATION) {
        this.init();

        const toast = document.createElement('div');
        toast.className = `toast show align-items-center text-white bg-${type} border-0`;
        toast.setAttribute('role', 'alert');
        toast.innerHTML = `
            <div class="d-flex">
                <div class="toast-body">
                    <strong>${title}</strong><br>
                    ${message}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        `;

        this.container.appendChild(toast);

        // Auto remove after duration
        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => toast.remove(), 300);
        }, duration);

        // Close button
        toast.querySelector('.btn-close').addEventListener('click', () => {
            toast.classList.remove('show');
            setTimeout(() => toast.remove(), 300);
        });
    },

    success(title, message) {
        this.show('success', title, message);
    },

    error(title, message) {
        this.show('danger', title, message);
    },

    warning(title, message) {
        this.show('warning', title, message);
    },

    info(title, message) {
        this.show('info', title, message);
    }
};

// Loading Overlay
const Loading = {
    show(message = 'Chargement...') {
        const overlay = document.createElement('div');
        overlay.id = 'loadingOverlay';
        overlay.className = 'position-fixed top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center';
        overlay.style.cssText = 'background: rgba(0,0,0,0.7); z-index: 9999;';
        overlay.innerHTML = `
            <div class="text-center text-white">
                <div class="spinner-border mb-3" style="width: 3rem; height: 3rem;" role="status"></div>
                <h4>${message}</h4>
            </div>
        `;
        document.body.appendChild(overlay);
    },

    hide() {
        const overlay = document.getElementById('loadingOverlay');
        if (overlay) {
            overlay.remove();
        }
    }
};

// API Helper
const API = {
    async request(endpoint, options = {}) {
        const defaultOptions = {
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
            }
        };

        const config = { ...defaultOptions, ...options };

        try {
            const response = await fetch(Config.API_BASE_URL + endpoint, config);
            const data = await response.json();

            if (!response.ok) {
                throw new Error(data.error || 'Une erreur est survenue');
            }

            return data;
        } catch (error) {
            console.error('API Error:', error);
            Toast.error('Erreur', error.message);
            throw error;
        }
    },

    get(endpoint) {
        return this.request(endpoint, { method: 'GET' });
    },

    post(endpoint, body) {
        return this.request(endpoint, {
            method: 'POST',
            body: JSON.stringify(body)
        });
    },

    put(endpoint, body) {
        return this.request(endpoint, {
            method: 'PUT',
            body: JSON.stringify(body)
        });
    },

    delete(endpoint) {
        return this.request(endpoint, { method: 'DELETE' });
    }
};

// Counter Animation
function animateCounter(element, start, end, duration) {
    let startTimestamp = null;
    const step = (timestamp) => {
        if (!startTimestamp) startTimestamp = timestamp;
        const progress = Math.min((timestamp - startTimestamp) / duration, 1);
        const value = Math.floor(progress * (end - start) + start);
        element.textContent = Utils.formatNumber(value, 0);
        if (progress < 1) {
            window.requestAnimationFrame(step);
        }
    };
    window.requestAnimationFrame(step);
}

// Smooth scroll to element
function smoothScrollTo(element) {
    element.scrollIntoView({
        behavior: 'smooth',
        block: 'start'
    });
}

// Confirm dialog
function confirmDialog(message, callback) {
    if (confirm(message)) {
        callback();
    }
}

// File upload handler
class FileUploader {
    constructor(inputElement, options = {}) {
        this.input = inputElement;
        this.options = {
            maxSize: options.maxSize || 10 * 1024 * 1024, // 10MB
            acceptedTypes: options.acceptedTypes || ['image/jpeg', 'image/png', 'application/pdf'],
            onProgress: options.onProgress || null,
            onComplete: options.onComplete || null,
            onError: options.onError || null
        };

        this.init();
    }

    init() {
        this.input.addEventListener('change', (e) => this.handleFile(e.target.files[0]));
    }

    handleFile(file) {
        if (!file) return;

        // Validate file size
        if (file.size > this.options.maxSize) {
            this.options.onError?.('Fichier trop volumineux. Maximum 10 MB.');
            return;
        }

        // Validate file type
        if (!this.options.acceptedTypes.includes(file.type)) {
            this.options.onError?.('Type de fichier non accepté.');
            return;
        }

        this.upload(file);
    }

    async upload(file) {
        const formData = new FormData();
        formData.append('document', file);
        formData.append('type', 'invoice');

        try {
            // Simulate progress
            let progress = 0;
            const interval = setInterval(() => {
                progress += 10;
                this.options.onProgress?.(progress);
                if (progress >= 100) {
                    clearInterval(interval);
                }
            }, 500);

            const response = await fetch('/api/v1/automation/documents/upload', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                }
            });

            const data = await response.json();

            if (data.success) {
                this.options.onComplete?.(data);
            } else {
                this.options.onError?.(data.error);
            }
        } catch (error) {
            this.options.onError?.(error.message);
        }
    }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    // Initialize tooltips
    const tooltips = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    tooltips.forEach(tooltip => new bootstrap.Tooltip(tooltip));

    // Initialize popovers
    const popovers = document.querySelectorAll('[data-bs-toggle="popover"]');
    popovers.forEach(popover => new bootstrap.Popover(popover));

    // Animate counters
    const counters = document.querySelectorAll('.counter');
    const observerOptions = {
        threshold: 0.5,
        rootMargin: '0px 0px -100px 0px'
    };

    const counterObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const target = entry.target;
                const endValue = parseInt(target.getAttribute('data-target'));
                animateCounter(target, 0, endValue, 2000);
                counterObserver.unobserve(target);
            }
        });
    }, observerOptions);

    counters.forEach(counter => counterObserver.observe(counter));

    // Add ripple effect to buttons
    document.querySelectorAll('.btn').forEach(button => {
        button.addEventListener('click', function(e) {
            const ripple = document.createElement('span');
            const diameter = Math.max(button.clientWidth, button.clientHeight);
            const radius = diameter / 2;

            ripple.style.width = ripple.style.height = `${diameter}px`;
            ripple.style.left = `${e.clientX - button.offsetLeft - radius}px`;
            ripple.style.top = `${e.clientY - button.offsetTop - radius}px`;
            ripple.classList.add('ripple-effect');

            const existingRipple = button.querySelector('.ripple-effect');
            if (existingRipple) {
                existingRipple.remove();
            }

            button.appendChild(ripple);

            setTimeout(() => ripple.remove(), 600);
        });
    });

    // Auto-hide alerts after 5 seconds
    setTimeout(() => {
        document.querySelectorAll('.alert-dismissible').forEach(alert => {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        });
    }, 5000);

    // Add animation to elements on scroll
    const animateOnScroll = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('animate__animated', 'animate__fadeInUp');
                animateOnScroll.unobserve(entry.target);
            }
        });
    }, { threshold: 0.1 });

    document.querySelectorAll('.animate-on-scroll').forEach(element => {
        animateOnScroll.observe(element);
    });
});

// Export for use in other scripts
window.ComptaPro = {
    Utils,
    Toast,
    Loading,
    API,
    animateCounter,
    smoothScrollTo,
    confirmDialog,
    FileUploader
};
