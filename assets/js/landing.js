/**
 * Enhanced Landing Page JavaScript with GSAP Animations
 * Holy Family High School - Gate Security System
 */

// Register GSAP plugins
gsap.registerPlugin(ScrollTrigger);

// Animation State Management
const AnimationState = {
    currentPhase: 'idle',
    isRunning: false,
    scenarios: [
        { type: 'granted', probability: 0.8 },
        { type: 'denied', probability: 0.2 }
    ]
};

// GSAP Animation Controller
class GSAPAnimationController {
    constructor() {
        this.initializeElements();
        this.setupInitialStates();
        this.initPageAnimations();
        this.initScrollAnimations();
        this.init();
    }
    
    initializeElements() {
        this.vehicle = document.getElementById('animatedVehicle');
        this.card = document.getElementById('animatedCard');
        this.barrier = document.getElementById('gateBarrier');
        this.display = document.getElementById('scannerDisplay');
        this.leds = document.getElementById('scannerLeds');
        this.status = document.getElementById('animationStatus');
        this.gateContainer = document.querySelector('.gate-animation-container');
    }
    
    setupInitialStates() {
        // Set initial positions and states using GSAP
        gsap.set(this.vehicle, { x: -120, scale: 1 });
        gsap.set(this.card, { scale: 0, opacity: 0, y: 0 });
        gsap.set(this.barrier, { rotation: 0 });
        
        // Set initial LED state
        this.setLEDState('idle');
    }
    
    initPageAnimations() {
        // Hero section entrance animation
        const tl = gsap.timeline({ delay: 0.5 });
        
        tl.from('.school-highlight', {
            duration: 1.2,
            y: 50,
            opacity: 0,
            ease: 'power3.out'
        })
        .from('.system-subtitle', {
            duration: 1,
            y: 30,
            opacity: 0,
            ease: 'power2.out'
        }, '-=0.8')
        .from('.hero-description', {
            duration: 1,
            y: 20,
            opacity: 0,
            ease: 'power2.out'
        }, '-=0.6')
        .from('.gate-animation-container', {
            duration: 1.5,
            scale: 0.8,
            opacity: 0,
            ease: 'back.out(1.7)'
        }, '-=0.4')
        .from('.animation-label', {
            duration: 0.8,
            y: -20,
            opacity: 0,
            ease: 'power2.out'
        }, '-=0.5');
        
        // Navigation animation
        gsap.from('.landing-nav', {
            duration: 1,
            y: -100,
            opacity: 0,
            ease: 'power3.out',
            delay: 0.2
        });
        
        gsap.from('.nav-brand', {
            duration: 1,
            x: -50,
            opacity: 0,
            ease: 'power2.out',
            delay: 0.8
        });
        
        gsap.from('.nav-menu li', {
            duration: 0.8,
            y: -20,
            opacity: 0,
            stagger: 0.1,
            ease: 'power2.out',
            delay: 1
        });
    }
    
    initScrollAnimations() {
        // Features section animation
        gsap.from('.feature-card', {
            scrollTrigger: {
                trigger: '.features-section',
                start: 'top 80%',
                end: 'bottom 20%',
                toggleActions: 'play none none reverse'
            },
            duration: 1,
            y: 50,
            opacity: 0,
            stagger: 0.2,
            ease: 'power2.out'
        });
        
        // About section animation
        gsap.from('.about-description', {
            scrollTrigger: {
                trigger: '.about-section',
                start: 'top 80%',
                toggleActions: 'play none none reverse'
            },
            duration: 1.2,
            x: -50,
            opacity: 0,
            ease: 'power2.out'
        });
        
        gsap.from('.stat-item', {
            scrollTrigger: {
                trigger: '.about-stats',
                start: 'top 80%',
                toggleActions: 'play none none reverse'
            },
            duration: 1,
            y: 30,
            opacity: 0,
            stagger: 0.15,
            ease: 'back.out(1.7)'
        });
        
        gsap.from('.showcase-item', {
            scrollTrigger: {
                trigger: '.security-showcase',
                start: 'top 80%',
                toggleActions: 'play none none reverse'
            },
            duration: 1,
            x: 50,
            opacity: 0,
            stagger: 0.2,
            ease: 'power2.out'
        });
        
        // Footer animation
        gsap.from('.footer-content', {
            scrollTrigger: {
                trigger: '.landing-footer',
                start: 'top 90%',
                toggleActions: 'play none none reverse'
            },
            duration: 1,
            y: 30,
            opacity: 0,
            ease: 'power2.out'
        });
    }
    
    init() {
        // Start gate animation after page animations
        setTimeout(() => this.startAnimationLoop(), 2500);
    }
    
    startAnimationLoop() {
        if (AnimationState.isRunning) return;
        this.runFullSequence();
    }
    
    async runFullSequence() {
        AnimationState.isRunning = true;
        
        try {
            // Reset everything first
            this.resetAnimation();
            
            // Step 1: Car approaches gate
            await this.step1_CarApproaches();
            
            // Step 2: Car stops at scanner
            await this.step2_CarStops();
            
            // Step 3: Card presented and scanning
            await this.step3_CardScanning();
            
            // Step 4: Process access (random grant/deny)
            const scenario = this.getRandomScenario();
            
            if (scenario.type === 'granted') {
                await this.sequenceGranted();
            } else {
                await this.sequenceDenied();
            }
            
            // Step 5: Reset and loop
            await this.wait(2000);
            AnimationState.isRunning = false;
            
            // Start next cycle
            setTimeout(() => this.startAnimationLoop(), 3000);
            
        } catch (error) {
            console.error('Animation error:', error);
            AnimationState.isRunning = false;
        }
    }
    
    // STEP 1: Car approaching gate with GSAP
    async step1_CarApproaches() {
        this.updateStatus('Vehicle approaching gate...');
        
        return new Promise(resolve => {
            gsap.to(this.vehicle, {
                duration: 2,
                x: '32vw',
                ease: 'power2.out',
                onComplete: resolve
            });
        });
    }
    
    // STEP 2: Car stops at scanner with GSAP
    async step2_CarStops() {
        this.updateStatus('Vehicle stopped at scanner...');
        
        return new Promise(resolve => {
            gsap.to(this.vehicle, {
                duration: 0.8,
                scale: 1.05,
                ease: 'elastic.out(1, 0.3)',
                yoyo: true,
                repeat: 1,
                onComplete: resolve
            });
        });
    }
    
    // STEP 3: Card scanning with enhanced GSAP animations
    async step3_CardScanning() {
        this.updateStatus('Scanning RFID card...');
        
        // Show card with bounce effect
        gsap.set(this.card, { scale: 0, opacity: 0 });
        
        return new Promise(resolve => {
            const tl = gsap.timeline({
                onComplete: resolve
            });
            
            tl.to(this.card, {
                duration: 0.6,
                scale: 1,
                opacity: 1,
                ease: 'back.out(1.7)'
            })
            .to(this.card, {
                duration: 1.5,
                y: -10,
                ease: 'power1.inOut',
                yoyo: true,
                repeat: -1
            }, '-=0.2');
            
            // Start scanning display
            this.display.textContent = 'SCANNING...';
            this.setLEDState('scanning');
            
            setTimeout(resolve, 2000);
        });
    }
    
    // GRANTED SEQUENCE with enhanced GSAP
    async sequenceGranted() {
        // Stop card floating animation
        gsap.killTweensOf(this.card);
        
        // Access granted
        this.updateStatus('Access Granted!');
        this.display.textContent = 'GRANTED';
        this.setLEDState('success');
        
        // Card success animation
        return new Promise(resolve => {
            const tl = gsap.timeline();
            
            tl.to(this.card, {
                duration: 0.5,
                scale: 1.3,
                rotation: 360,
                ease: 'power2.out'
            })
            .to(this.card, {
                duration: 0.5,
                scale: 0,
                opacity: 0,
                ease: 'power2.in'
            })
            .call(() => {
                this.updateStatus('Gate opening...');
            })
            .to(this.barrier, {
                duration: 1.5,
                rotation: -85,
                ease: 'power2.inOut',
                onComplete: () => {
                    this.barrier.style.background = 'repeating-linear-gradient(45deg, #00FF00, #00FF00 25px, #006600 25px, #006600 50px)';
                }
            })
            .call(() => {
                this.updateStatus('Vehicle passing through...');
            })
            .to(this.vehicle, {
                duration: 2.5,
                x: '120vw',
                ease: 'power2.in'
            })
            .call(() => {
                this.updateStatus('Gate closing...');
            })
            .to(this.barrier, {
                duration: 1.5,
                rotation: 0,
                ease: 'power2.inOut',
                onComplete: () => {
                    this.barrier.style.background = 'repeating-linear-gradient(45deg, #FFD700, #FFD700 25px, #000 25px, #000 50px)';
                    this.updateStatus('System ready');
                    this.display.textContent = 'Waiting...';
                    this.setLEDState('idle');
                    resolve();
                }
            });
        });
    }
    
    // DENIED SEQUENCE with enhanced GSAP
    async sequenceDenied() {
        // Stop card floating animation
        gsap.killTweensOf(this.card);
        
        // Access denied
        this.updateStatus('Access Denied!');
        this.display.textContent = 'DENIED';
        this.setLEDState('error');
        
        return new Promise(resolve => {
            const tl = gsap.timeline();
            
            tl.to(this.card, {
                duration: 0.6,
                rotation: -10,
                ease: 'power2.inOut',
                yoyo: true,
                repeat: 3
            })
            .to(this.card, {
                duration: 0.5,
                scale: 0,
                opacity: 0,
                ease: 'power2.in'
            })
            .call(() => {
                this.updateStatus('Unauthorized - Vehicle reversing...');
            })
            .to(this.vehicle, {
                duration: 2.5,
                x: -120,
                ease: 'power2.out',
                onComplete: () => {
                    this.updateStatus('System ready');
                    this.display.textContent = 'Waiting...';
                    this.setLEDState('idle');
                    resolve();
                }
            });
        });
    }
    
    // Reset all animation elements
    resetAnimation() {
        // Kill all existing tweens
        gsap.killTweensOf([this.vehicle, this.card, this.barrier]);
        
        // Reset positions with GSAP
        gsap.set(this.vehicle, { x: -120, scale: 1, rotation: 0 });
        gsap.set(this.card, { scale: 0, opacity: 0, y: 0, rotation: 0 });
        gsap.set(this.barrier, { rotation: 0 });
        
        // Reset barrier color
        this.barrier.style.background = 'repeating-linear-gradient(45deg, #FFD700, #FFD700 25px, #000 25px, #000 50px)';
        
        // Reset scanner
        this.display.textContent = 'Waiting...';
        this.setLEDState('idle');
    }
    
    // Set LED indicator state with GSAP animations
    setLEDState(state) {
        const redLED = this.leds.querySelector('.led.red');
        const greenLED = this.leds.querySelector('.led.green');
        
        // Kill existing LED animations
        gsap.killTweensOf([redLED, greenLED]);
        
        // Remove all states
        redLED.classList.remove('active', 'blink');
        greenLED.classList.remove('active', 'blink');
        
        switch (state) {
            case 'idle':
                redLED.classList.add('active');
                gsap.to(redLED, { duration: 0.3, opacity: 1, ease: 'power2.out' });
                gsap.to(greenLED, { duration: 0.3, opacity: 0.3, ease: 'power2.out' });
                break;
                
            case 'scanning':
                gsap.to([redLED, greenLED], {
                    duration: 0.6,
                    opacity: 1,
                    ease: 'power2.inOut',
                    yoyo: true,
                    repeat: -1
                });
                break;
                
            case 'success':
                greenLED.classList.add('active');
                gsap.to(greenLED, { duration: 0.3, opacity: 1, scale: 1.2, ease: 'back.out(1.7)' });
                gsap.to(redLED, { duration: 0.3, opacity: 0.3, ease: 'power2.out' });
                break;
                
            case 'error':
                redLED.classList.add('active');
                gsap.to(redLED, {
                    duration: 0.3,
                    opacity: 1,
                    scale: 1.2,
                    ease: 'power2.inOut',
                    yoyo: true,
                    repeat: 5
                });
                gsap.to(greenLED, { duration: 0.3, opacity: 0.3, ease: 'power2.out' });
                break;
        }
    }
    
    // Update status label with GSAP animation
    updateStatus(message) {
        if (this.status) {
            this.status.textContent = message;
            
            gsap.fromTo(this.status, 
                { scale: 1 },
                { 
                    duration: 0.3,
                    scale: 1.05,
                    ease: 'back.out(1.7)',
                    yoyo: true,
                    repeat: 1
                }
            );
        }
    }
    
    // Get random scenario
    getRandomScenario() {
        const random = Math.random();
        let cumulative = 0;
        
        for (const scenario of AnimationState.scenarios) {
            cumulative += scenario.probability;
            if (random <= cumulative) {
                return scenario;
            }
        }
        
        return AnimationState.scenarios[0];
    }
    
    // Helper function to wait
    wait(ms) {
        return new Promise(resolve => setTimeout(resolve, ms));
    }
}

// Modal Management with GSAP
function openLoginModal() {
    const modal = document.getElementById('loginModal');
    if (modal) {
        modal.style.display = 'flex';
        
        // GSAP animation for modal entrance
        gsap.fromTo(modal, 
            { opacity: 0 },
            { duration: 0.3, opacity: 1, ease: 'power2.out' }
        );
        
        gsap.fromTo(modal.querySelector('.modal-container'),
            { scale: 0.7, y: -50, opacity: 0 },
            { 
                duration: 0.5, 
                scale: 1, 
                y: 0, 
                opacity: 1, 
                ease: 'back.out(1.7)',
                delay: 0.1
            }
        );
        
        setTimeout(() => {
            const usernameField = modal.querySelector('input[name="username"]');
            if (usernameField) {
                usernameField.focus();
            }
        }, 600);
    }
}

function closeLoginModal(event) {
    const modal = document.getElementById('loginModal');
    if (modal && (!event || event.target === modal || event.target.classList.contains('modal-close'))) {
        
        // GSAP animation for modal exit
        gsap.to(modal.querySelector('.modal-container'), {
            duration: 0.3,
            scale: 0.7,
            y: -50,
            opacity: 0,
            ease: 'power2.in'
        });
        
        gsap.to(modal, {
            duration: 0.3,
            opacity: 0,
            ease: 'power2.in',
            delay: 0.1,
            onComplete: () => {
                modal.style.display = 'none';
            }
        });
    }
}

// Mobile Menu Management with GSAP
function toggleMobileMenu() {
    const navMenu = document.querySelector('.nav-menu');
    const toggle = document.querySelector('.mobile-menu-toggle');
    
    if (navMenu && toggle) {
        const isActive = navMenu.classList.contains('mobile-active');
        
        if (!isActive) {
            navMenu.classList.add('mobile-active');
            toggle.classList.add('active');
            
            // Animate menu items
            gsap.fromTo(navMenu.querySelectorAll('li'), 
                { y: -20, opacity: 0 },
                { 
                    duration: 0.5,
                    y: 0,
                    opacity: 1,
                    stagger: 0.1,
                    ease: 'power2.out'
                }
            );
        } else {
            gsap.to(navMenu.querySelectorAll('li'), {
                duration: 0.3,
                y: -20,
                opacity: 0,
                stagger: 0.05,
                ease: 'power2.in',
                onComplete: () => {
                    navMenu.classList.remove('mobile-active');
                    toggle.classList.remove('active');
                }
            });
        }
    }
}

// Smooth Scrolling for Navigation Links
function initSmoothScrolling() {
    const links = document.querySelectorAll('a[href^="#"]');
    
    links.forEach(link => {
        link.addEventListener('click', function(e) {
            const targetId = this.getAttribute('href');
            if (targetId === '#') return;
            
            e.preventDefault();
            const targetElement = document.querySelector(targetId);
            
            if (targetElement) {
                const offsetTop = targetElement.offsetTop - 80;
                
                window.scrollTo({
                    top: offsetTop,
                    behavior: 'smooth'
                });
                
                // Close mobile menu if open
                const navMenu = document.querySelector('.nav-menu');
                const toggle = document.querySelector('.mobile-menu-toggle');
                if (navMenu && navMenu.classList.contains('mobile-active')) {
                    navMenu.classList.remove('mobile-active');
                    toggle.classList.remove('active');
                }
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
    
    if (window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        capabilities.preferReducedMotion = true;
        capabilities.supportsAnimations = false;
    }
    
    if (window.innerWidth <= 768) {
        capabilities.isMobile = true;
    }
    
    return capabilities;
}

// Keyboard Navigation Support
function initKeyboardNavigation() {
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeLoginModal();
        }
    });
}

// Accessibility Enhancements
function initAccessibility() {
    const animationContainer = document.querySelector('.gate-animation-container');
    if (animationContainer) {
        animationContainer.setAttribute('aria-label', 'Gate security system demonstration');
        animationContainer.setAttribute('role', 'img');
    }
    
    const status = document.getElementById('animationStatus');
    if (status) {
        status.setAttribute('aria-live', 'polite');
        status.setAttribute('aria-atomic', 'true');
    }
}

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
    
    // Modal close button
    const modalClose = modal?.querySelector('.modal-close');
    if (modalClose) {
        modalClose.addEventListener('click', closeLoginModal);
    }
}

// Initialize Everything with GSAP
document.addEventListener('DOMContentLoaded', function() {
    try {
        const capabilities = checkPerformanceCapabilities();
        
        // Initialize GSAP animation controller
        if (capabilities.supportsAnimations && !capabilities.preferReducedMotion) {
            new GSAPAnimationController();
        }
        
        // Initialize other features
        initSmoothScrolling();
        initKeyboardNavigation();
        initAccessibility();
        initEventListeners();
        
        // Add hover animations to interactive elements
        initHoverAnimations();
        
        console.log('Landing page with GSAP animations initialized successfully');
        
    } catch (error) {
        console.error('Error initializing landing page:', error);
    }
});

// Add hover animations for interactive elements
function initHoverAnimations() {
    // Feature cards hover animation
    document.querySelectorAll('.feature-card').forEach(card => {
        card.addEventListener('mouseenter', () => {
            gsap.to(card, {
                duration: 0.3,
                y: -10,
                scale: 1.02,
                boxShadow: '0 15px 35px rgba(58, 67, 76, 0.2)',
                ease: 'power2.out'
            });
        });
        
        card.addEventListener('mouseleave', () => {
            gsap.to(card, {
                duration: 0.3,
                y: 0,
                scale: 1,
                boxShadow: '0 2px 15px rgba(58, 67, 76, 0.1)',
                ease: 'power2.out'
            });
        });
    });
    
    // Showcase items hover animation
    document.querySelectorAll('.showcase-item').forEach(item => {
        item.addEventListener('mouseenter', () => {
            gsap.to(item, {
                duration: 0.3,
                y: -8,
                scale: 1.02,
                ease: 'power2.out'
            });
            
            gsap.to(item.querySelector('i'), {
                duration: 0.3,
                scale: 1.1,
                rotation: 5,
                ease: 'power2.out'
            });
        });
        
        item.addEventListener('mouseleave', () => {
            gsap.to(item, {
                duration: 0.3,
                y: 0,
                scale: 1,
                ease: 'power2.out'
            });
            
            gsap.to(item.querySelector('i'), {
                duration: 0.3,
                scale: 1,
                rotation: 0,
                ease: 'power2.out'
            });
        });
    });
    
    // Button hover animations
    document.querySelectorAll('.btn-login-modal, .btn-login').forEach(btn => {
        btn.addEventListener('mouseenter', () => {
            gsap.to(btn, {
                duration: 0.3,
                y: -3,
                scale: 1.05,
                ease: 'power2.out'
            });
        });
        
        btn.addEventListener('mouseleave', () => {
            gsap.to(btn, {
                duration: 0.3,
                y: 0,
                scale: 1,
                ease: 'power2.out'
            });
        });
    });
    
    // Navigation links hover animation
    document.querySelectorAll('.nav-menu a').forEach(link => {
        link.addEventListener('mouseenter', () => {
            gsap.to(link, {
                duration: 0.2,
                scale: 1.05,
                ease: 'power2.out'
            });
        });
        
        link.addEventListener('mouseleave', () => {
            gsap.to(link, {
                duration: 0.2,
                scale: 1,
                ease: 'power2.out'
            });
        });
    });
}

// Handle window resize
window.addEventListener('resize', function() {
    const capabilities = checkPerformanceCapabilities();
    
    if (capabilities.isMobile) {
        document.body.classList.add('mobile-optimized');
    } else {
        document.body.classList.remove('mobile-optimized');
    }
});

// Pause animation when tab is not visible
document.addEventListener('visibilitychange', function() {
    if (document.hidden) {
        AnimationState.isRunning = false;
    }
});

// Export for external use
window.LandingPageController = {
    openLoginModal,
    closeLoginModal,
    toggleMobileMenu
};