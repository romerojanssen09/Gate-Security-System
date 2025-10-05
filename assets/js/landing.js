/**
 * Landing Page JavaScript
 * Holy Family High School - Gate Security System
 */

// Animation State Management
const AnimationState = {
    currentPhase: 'idle',
    gatePosition: 'closed',
    vehiclePosition: { x: 0, y: 0 },
    scannerStatus: 'waiting',
    cardPosition: { x: 0, y: 0, visible: false },
    timers: {
        phaseTimer: null,
        resetTimer: null,
        loopTimer: null
    }
};

// Animation Controller
class GateAnimationController {
    constructor() {
        this.vehicle = document.getElementById('animatedVehicle');
        this.card = document.getElementById('animatedCard');
        this.barrier = document.getElementById('gateBarrier');
        this.display = document.getElementById('scannerDisplay');
        this.leds = document.getElementById('scannerLeds');
        this.status = document.getElementById('animationStatus');
        
        this.scenarios = [
            { type: 'granted', message: 'Access Granted', probability: 0.7 },
            { type: 'denied', message: 'Access Denied', probability: 0.3 }
        ];
        
        this.init();
    }
    
    init() {
        // Start the animation loop
        this.startAnimationLoop();
    }
    
    startAnimationLoop() {
        // Initial delay before starting
        setTimeout(() => {
            this.runAnimationSequence();
        }, 2000);
    }
    
    runAnimationSequence() {
        this.resetAnimation();
        
        // Phase 1: Car approaches
        this.updateStatus('Car approaching gate...');
        this.vehicle.classList.add('waiting');
        
        setTimeout(() => {
            // Phase 2: RFID scanning
            this.startScanning();
        }, 2000);
    }
    
    startScanning() {
        this.updateStatus('Scanning RFID card...');
        this.display.textContent = 'Scanning...';
        
        // Show card animation
        this.card.classList.add('scanning');
        
        // LED scanning effect
        this.animateLEDs('scanning');
        
        setTimeout(() => {
            this.processAccess();
        }, 3000);
    }
    
    processAccess() {
        // Randomly select scenario
        const scenario = this.getRandomScenario();
        
        if (scenario.type === 'granted') {
            this.grantAccess();
        } else {
            this.denyAccess();
        }
    }
    
    grantAccess() {
        this.updateStatus('Access Granted - Gate Opening');
        this.display.textContent = 'ACCESS GRANTED';
        this.animateLEDs('success');
        
        // Open gate
        this.barrier.classList.add('open');
        
        setTimeout(() => {
            // Car moves through
            this.updateStatus('Vehicle passing through...');
            this.vehicle.classList.add('moving');
            
            setTimeout(() => {
                // Close gate
                this.updateStatus('Gate closing...');
                this.barrier.classList.remove('open');
                
                setTimeout(() => {
                    this.completeSequence();
                }, 2000);
            }, 2000);
        }, 1500);
    }
    
    denyAccess() {
        this.updateStatus('Access Denied - Gate Remains Closed');
        this.display.textContent = 'ACCESS DENIED';
        this.animateLEDs('error');
        
        setTimeout(() => {
            // Car reverses
            this.updateStatus('Vehicle reversing...');
            this.vehicle.style.transform = 'translateX(-100px)';
            
            setTimeout(() => {
                this.completeSequence();
            }, 2000);
        }, 2000);
    }
    
    completeSequence() {
        this.updateStatus('Resetting system...');
        
        setTimeout(() => {
            // Reset and start new cycle
            this.runAnimationSequence();
        }, 3000);
    }
    
    resetAnimation() {
        // Reset all elements to initial state
        this.vehicle.classList.remove('waiting', 'moving');
        this.vehicle.style.transform = '';
        this.card.classList.remove('scanning');
        this.barrier.classList.remove('open');
        this.display.textContent = 'Waiting...';
        this.resetLEDs();
    }
    
    animateLEDs(type) {
        const redLED = this.leds.querySelector('.led.red');
        const greenLED = this.leds.querySelector('.led.green');
        
        // Reset LEDs
        redLED.classList.remove('active');
        greenLED.classList.remove('active');
        
        switch (type) {
            case 'scanning':
                // Alternating LED pattern
                let scanCount = 0;
                const scanInterval = setInterval(() => {
                    if (scanCount % 2 === 0) {
                        redLED.classList.add('active');
                        greenLED.classList.remove('active');
                    } else {
                        redLED.classList.remove('active');
                        greenLED.classList.add('active');
                    }
                    scanCount++;
                    if (scanCount >= 6) {
                        clearInterval(scanInterval);
                    }
                }, 500);
                break;
                
            case 'success':
                greenLED.classList.add('active');
                break;
                
            case 'error':
                redLED.classList.add('active');
                break;
        }
    }
    
    resetLEDs() {
        const leds = this.leds.querySelectorAll('.led');
        leds.forEach(led => led.classList.remove('active'));
    }
    
    updateStatus(message) {
        if (this.status) {
            this.status.textContent = message;
        }
    }
    
    getRandomScenario() {
        const random = Math.random();
        let cumulative = 0;
        
        for (const scenario of this.scenarios) {
            cumulative += scenario.probability;
            if (random <= cumulative) {
                return scenario;
            }
        }
        
        return this.scenarios[0]; // Fallback
    }
}

// Modal Management
function openLoginModal() {
    const modal = document.getElementById('loginModal');
    if (modal) {
        modal.classList.add('show');
        // Focus on username field
        setTimeout(() => {
            const usernameField = modal.querySelector('input[name="username"]');
            if (usernameField) {
                usernameField.focus();
            }
        }, 300);
    }
}

function closeLoginModal(event) {
    const modal = document.getElementById('loginModal');
    if (modal && (!event || event.target === modal)) {
        modal.classList.remove('show');
    }
}

// Mobile Menu Management
function toggleMobileMenu() {
    const navMenu = document.querySelector('.nav-menu');
    const toggle = document.querySelector('.mobile-menu-toggle');
    
    if (navMenu && toggle) {
        navMenu.classList.toggle('mobile-active');
        toggle.classList.toggle('active');
    }
}

// Smooth Scrolling for Navigation Links
function initSmoothScrolling() {
    const links = document.querySelectorAll('a[href^="#"]');
    
    links.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            
            const targetId = this.getAttribute('href');
            const targetElement = document.querySelector(targetId);
            
            if (targetElement) {
                const offsetTop = targetElement.offsetTop - 80; // Account for fixed nav
                
                window.scrollTo({
                    top: offsetTop,
                    behavior: 'smooth'
                });
            }
        });
    });
}

// Performance Optimization
function checkPerformanceCapabilities() {
    const capabilities = {
        supportsAnimations: true,
        preferReducedMotion: false,
        isMobile: false
    };
    
    // Check for reduced motion preference
    if (window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        capabilities.preferReducedMotion = true;
        capabilities.supportsAnimations = false;
    }
    
    // Check for mobile device
    if (window.innerWidth <= 768) {
        capabilities.isMobile = true;
    }
    
    // Check for low-end device indicators
    if (navigator.hardwareConcurrency && navigator.hardwareConcurrency <= 2) {
        capabilities.supportsAnimations = false;
    }
    
    return capabilities;
}

// Keyboard Navigation Support
function initKeyboardNavigation() {
    document.addEventListener('keydown', function(e) {
        // ESC key closes modal
        if (e.key === 'Escape') {
            closeLoginModal();
        }
        
        // Enter key on login button opens modal
        if (e.key === 'Enter' && e.target.classList.contains('btn-login-modal')) {
            openLoginModal();
        }
    });
}

// Accessibility Enhancements
function initAccessibility() {
    // Add ARIA labels to animation elements
    const animationContainer = document.querySelector('.gate-animation-container');
    if (animationContainer) {
        animationContainer.setAttribute('aria-label', 'Gate security system demonstration');
        animationContainer.setAttribute('role', 'img');
    }
    
    // Add screen reader announcements for animation states
    const status = document.getElementById('animationStatus');
    if (status) {
        status.setAttribute('aria-live', 'polite');
        status.setAttribute('aria-atomic', 'true');
    }
}

// Error Handling
class AnimationErrorHandler {
    static handleAnimationError(error, component) {
        console.warn(`Animation error in ${component}:`, error);
        
        // Implement fallback strategies
        switch (error.type) {
            case 'PERFORMANCE_LOW':
                this.enableReducedMotionMode();
                break;
            case 'RESOURCE_LOAD_FAILED':
                this.showStaticFallback(component);
                break;
            case 'BROWSER_UNSUPPORTED':
                this.enableBasicMode();
                break;
            default:
                console.error('Unhandled animation error:', error);
        }
    }
    
    static enableReducedMotionMode() {
        document.body.classList.add('reduced-motion');
    }
    
    static showStaticFallback(component) {
        const container = document.querySelector('.gate-animation-container');
        if (container) {
            container.innerHTML = `
                <div class="static-fallback">
                    <i class="fas fa-shield-alt fa-5x" style="color: var(--brand-primary);"></i>
                    <h3>Gate Security System</h3>
                    <p>RFID-based access control for Holy Family High School</p>
                </div>
            `;
        }
    }
    
    static enableBasicMode() {
        // Disable complex animations, keep basic functionality
        const style = document.createElement('style');
        style.textContent = `
            .gate-animation-container * {
                animation: none !important;
                transition: none !important;
            }
        `;
        document.head.appendChild(style);
    }
}

// Initialize Everything
document.addEventListener('DOMContentLoaded', function() {
    try {
        // Check performance capabilities
        const capabilities = checkPerformanceCapabilities();
        
        // Initialize animation only if supported
        if (capabilities.supportsAnimations && !capabilities.preferReducedMotion) {
            new GateAnimationController();
        } else {
            AnimationErrorHandler.showStaticFallback('gate-animation');
        }
        
        // Initialize other features
        initSmoothScrolling();
        initKeyboardNavigation();
        initAccessibility();
        initEventListeners();
        
        console.log('Landing page initialized successfully');
        
    } catch (error) {
        console.error('Error initializing landing page:', error);
        AnimationErrorHandler.handleAnimationError(error, 'initialization');
    }
});

// Initialize Event Listeners
function initEventListeners() {
    // Login button
    const loginButton = document.getElementById('loginButton');
    if (loginButton) {
        loginButton.addEventListener('click', openLoginModal);
    }
    
    // Mobile menu toggle
    const mobileToggle = document.getElementById('mobileMenuToggle');
    if (mobileToggle) {
        mobileToggle.addEventListener('click', toggleMobileMenu);
    }
    
    // Modal close on overlay click
    const modal = document.getElementById('loginModal');
    if (modal) {
        modal.addEventListener('click', closeLoginModal);
    }
    
    // Prevent modal close when clicking inside modal content
    const modalContainer = modal?.querySelector('.modal-container');
    if (modalContainer) {
        modalContainer.addEventListener('click', function(e) {
            e.stopPropagation();
        });
    }
}

// Handle window resize for responsive behavior
window.addEventListener('resize', function() {
    const capabilities = checkPerformanceCapabilities();
    
    // Adjust animations based on screen size
    if (capabilities.isMobile) {
        document.body.classList.add('mobile-optimized');
    } else {
        document.body.classList.remove('mobile-optimized');
    }
});

// Export for potential external use
window.LandingPageController = {
    openLoginModal,
    closeLoginModal,
    toggleMobileMenu,
    AnimationErrorHandler
};