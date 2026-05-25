// ================================
// NAVIGATION & MOBILE MENU
// ================================

const hamburger = document.querySelector('.hamburger');
const navLinks = document.querySelector('.nav-links');
const navItems = document.querySelectorAll('.nav-links a');

// Toggle mobile menu
hamburger.addEventListener('click', () => {
    navLinks.classList.toggle('active');
    hamburger.classList.toggle('active');
});

// Close menu when clicking on a link
navItems.forEach(item => {
    item.addEventListener('click', () => {
        navLinks.classList.remove('active');
        hamburger.classList.remove('active');
    });
});

// ================================
// SCROLL ANIMATIONS
// ================================

const observerOptions = {
    threshold: 0.1,
    rootMargin: '0px 0px -50px 0px'
};

const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            entry.target.style.opacity = '1';
            entry.target.style.transform = 'translateY(0)';
        }
    });
}, observerOptions);

// Observe all cards and sections for animation
const animatedElements = document.querySelectorAll(
    '.service-card, .project-card, .process-step, .testimonial-card, .achievement-card'
);

animatedElements.forEach(el => {
    el.style.opacity = '0';
    el.style.transform = 'translateY(20px)';
    el.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
    observer.observe(el);
});

// ================================
// SMOOTH SCROLL NAVIGATION
// ================================

document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        const href = this.getAttribute('href');
        if (href !== '#' && document.querySelector(href)) {
            e.preventDefault();
            const target = document.querySelector(href);
            const offsetTop = target.offsetTop - 80; // Account for sticky navbar
            
            window.scrollTo({
                top: offsetTop,
                behavior: 'smooth'
            });
        }
    });
});

// ================================
// BUTTON INTERACTIONS
// ================================

const buttons = document.querySelectorAll('.btn');

buttons.forEach(button => {
    button.addEventListener('mouseenter', function() {
        this.style.transform = 'translateY(-2px)';
    });

    button.addEventListener('mouseleave', function() {
        this.style.transform = 'translateY(0)';
    });

    button.addEventListener('click', function(e) {
        // Create ripple effect
        const ripple = document.createElement('span');
        const rect = this.getBoundingClientRect();
        const size = Math.max(rect.width, rect.height);
        const x = e.clientX - rect.left - size / 2;
        const y = e.clientY - rect.top - size / 2;

        ripple.style.width = ripple.style.height = size + 'px';
        ripple.style.left = x + 'px';
        ripple.style.top = y + 'px';
        ripple.classList.add('ripple');

        this.appendChild(ripple);

        setTimeout(() => ripple.remove(), 600);
    });
});

// ================================
// COUNTER ANIMATION FOR ACHIEVEMENTS
// ================================

function animateCounter(element, target, duration = 2000) {
    const increment = target / (duration / 16);
    let current = 0;
    
    const counter = setInterval(() => {
        current += increment;
        if (current >= target) {
            element.textContent = target + '+';
            clearInterval(counter);
        } else {
            element.textContent = Math.floor(current) + '+';
        }
    }, 16);
}

// Trigger counter animation when achievement section is visible
const achievementCards = document.querySelectorAll('.achievement-number');
let countersAnimated = false;

const achievementObserver = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting && !countersAnimated) {
            countersAnimated = true;
            achievementCards.forEach(card => {
                const target = parseInt(card.textContent);
                animateCounter(card, target);
            });
        }
    });
}, { threshold: 0.5 });

achievementCards.forEach(card => achievementObserver.observe(card));

// ================================
// SCROLL TO TOP BUTTON
// ================================

function createScrollToTopButton() {
    const button = document.createElement('button');
    button.id = 'scrollToTop';
    button.innerHTML = '↑';
    button.style.cssText = `
        position: fixed;
        bottom: 2rem;
        right: 2rem;
        width: 50px;
        height: 50px;
        border-radius: 50%;
        background: linear-gradient(135deg, #1E3A5F, #2F5D9F);
        color: white;
        border: none;
        cursor: pointer;
        opacity: 0;
        visibility: hidden;
        transition: opacity 0.3s, visibility 0.3s;
        z-index: 999;
        font-size: 1.5rem;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 4px 12px rgba(30, 58, 95, 0.3);
    `;

    document.body.appendChild(button);

    window.addEventListener('scroll', () => {
        if (window.pageYOffset > 300) {
            button.style.opacity = '1';
            button.style.visibility = 'visible';
        } else {
            button.style.opacity = '0';
            button.style.visibility = 'hidden';
        }
    });

    button.addEventListener('click', () => {
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    });

    button.addEventListener('mouseenter', function() {
        this.style.transform = 'scale(1.1)';
    });

    button.addEventListener('mouseleave', function() {
        this.style.transform = 'scale(1)';
    });
}

createScrollToTopButton();

// ================================
// HOVER EFFECTS FOR CARDS
// ================================

const serviceCards = document.querySelectorAll('.service-card');
const projectCards = document.querySelectorAll('.project-card');

function addCardHoverEffect(cards) {
    cards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.boxShadow = '0 10px 30px rgba(30, 58, 95, 0.15)';
        });

        card.addEventListener('mouseleave', function() {
            this.style.boxShadow = '0 1px 2px rgba(0, 0, 0, 0.05)';
        });
    });
}

addCardHoverEffect(serviceCards);
addCardHoverEffect(projectCards);

// ================================
// NAVBAR SHADOW ON SCROLL
// ================================

const navbar = document.querySelector('.navbar');

window.addEventListener('scroll', () => {
    if (window.scrollY > 10) {
        navbar.style.boxShadow = '0 4px 12px rgba(30, 58, 95, 0.1)';
    } else {
        navbar.style.boxShadow = '0 1px 2px rgba(0, 0, 0, 0.05)';
    }
});

// ================================
// FORM INTERACTION PLACEHOLDERS
// ================================

// For CTA section buttons - you can connect these to actual forms
const ctaButtons = document.querySelectorAll('.cta-buttons .btn');

ctaButtons.forEach(button => {
    button.addEventListener('click', function(e) {
        e.preventDefault();
        const buttonText = this.textContent;
        
        if (buttonText.includes('Quote')) {
            console.log('Redirecting to quote form...');
            // window.location.href = '/quote';
            alert('Quote form would open here. Connect to your actual form/service.');
        } else if (buttonText.includes('Contact')) {
            console.log('Redirecting to contact form...');
            // window.location.href = '/contact';
            alert('Contact form would open here. Connect to your actual form/service.');
        } else if (buttonText.includes('Consultation')) {
            console.log('Redirecting to consultation booking...');
            alert('Consultation booking would open here. Connect to your calendar/service.');
        }
    });
});

// ================================
// DYNAMIC ACTIVE NAVIGATION LINK
// ================================

function updateActiveNavLink() {
    const sections = document.querySelectorAll('section[id]');
    const navLinks = document.querySelectorAll('.nav-links a[href^="#"]');

    window.addEventListener('scroll', () => {
        let current = '';

        sections.forEach(section => {
            const sectionTop = section.offsetTop;
            const sectionHeight = section.clientHeight;
            
            if (pageYOffset >= sectionTop - 200) {
                current = section.getAttribute('id');
            }
        });

        navLinks.forEach(link => {
            link.classList.remove('active');
            if (link.getAttribute('href') === '#' + current) {
                link.classList.add('active');
                link.style.color = '#F59E0B';
            } else {
                link.style.color = '';
            }
        });
    });
}

updateActiveNavLink();

// ================================
// LAZY LOADING FOR IMAGES
// ================================

function setupLazyLoading() {
    const images = document.querySelectorAll('img[data-src]');
    
    const imageObserver = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const img = entry.target;
                img.src = img.dataset.src;
                img.removeAttribute('data-src');
                observer.unobserve(img);
            }
        });
    });

    images.forEach(img => imageObserver.observe(img));
}

setupLazyLoading();

// ================================
// INITIALIZATION MESSAGE
// ================================

console.log('%c🎨 HashTag Website Loaded Successfully!', 'color: #1E3A5F; font-size: 16px; font-weight: bold;');
console.log('%cColor System:', 'color: #F59E0B; font-weight: bold;');
console.log('Primary Blue: #1E3A5F, Secondary Blue: #2F5D9F, Accent Orange: #F59E0B');
