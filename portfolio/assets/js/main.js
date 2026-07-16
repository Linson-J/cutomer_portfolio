document.addEventListener('DOMContentLoaded', () => {
    initTheme();
    initMobileMenu();
    initScrollReveal();
    initTypingEffect();
    initSkillsAnimation();
    initContactForm();
});

/* --- Theme Handler --- */
function initTheme() {
    const themeToggleBtn = document.getElementById('theme-toggle');
    if (!themeToggleBtn) return;
    
    // Default to dark mode or stored preference
    const savedTheme = localStorage.getItem('theme') || 'dark';
    document.documentElement.setAttribute('data-theme', savedTheme);
    
    themeToggleBtn.addEventListener('click', () => {
        const currentTheme = document.documentElement.getAttribute('data-theme');
        const newTheme = currentTheme === 'light' ? 'dark' : 'light';
        
        document.documentElement.setAttribute('data-theme', newTheme);
        localStorage.setItem('theme', newTheme);
    });
}

/* --- Mobile Hamburger Menu --- */
function initMobileMenu() {
    const hamburger = document.getElementById('hamburger');
    const navMenu = document.getElementById('nav-menu');
    const navLinks = document.querySelectorAll('.nav-link');
    
    if (!hamburger || !navMenu) return;
    
    const toggleMenu = () => {
        hamburger.classList.toggle('active');
        navMenu.classList.toggle('active');
    };
    
    hamburger.addEventListener('click', toggleMenu);
    
    navLinks.forEach(link => {
        link.addEventListener('click', () => {
            hamburger.classList.remove('active');
            navMenu.classList.remove('active');
        });
    });
}

/* --- Scroll Reveal & Nav Link Highlights --- */
function initScrollReveal() {
    const reveals = document.querySelectorAll('.reveal');
    const navLinks = document.querySelectorAll('.nav-link');
    const sections = document.querySelectorAll('section');
    
    const revealObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('active');
            }
        });
    }, {
        root: null,
        threshold: 0.15,
        rootMargin: '0px'
    });
    
    reveals.forEach(el => revealObserver.observe(el));
    
    // Active Navigation Highlight on Scroll
    window.addEventListener('scroll', () => {
        let currentId = '';
        const scrollPosition = window.scrollY + 120; // offset for navbar
        
        sections.forEach(section => {
            const sectionTop = section.offsetTop;
            const sectionHeight = section.clientHeight;
            
            if (scrollPosition >= sectionTop && scrollPosition < sectionTop + sectionHeight) {
                currentId = section.getAttribute('id');
            }
        });
        
        navLinks.forEach(link => {
            link.classList.remove('active');
            if (link.getAttribute('href') === `#${currentId}`) {
                link.classList.add('active');
            }
        });
    });
}

/* --- Typing Effect on Hero Section --- */
function initTypingEffect() {
    const typingTextEl = document.getElementById('typing-text');
    if (!typingTextEl) return;
    
    const rawTitles = typingTextEl.getAttribute('data-titles');
    const titles = rawTitles ? JSON.parse(rawTitles) : ['Full-Stack Developer', 'Software Architect', 'Designer'];
    
    let titleIndex = 0;
    let charIndex = 0;
    let isDeleting = false;
    let delay = 100;
    
    function type() {
        const currentTitle = titles[titleIndex];
        
        if (isDeleting) {
            typingTextEl.textContent = currentTitle.substring(0, charIndex - 1);
            charIndex--;
            delay = 50;
        } else {
            typingTextEl.textContent = currentTitle.substring(0, charIndex + 1);
            charIndex++;
            delay = 120;
        }
        
        // Handle word completions
        if (!isDeleting && charIndex === currentTitle.length) {
            isDeleting = true;
            delay = 2000; // Pause at full word
        } else if (isDeleting && charIndex === 0) {
            isDeleting = false;
            titleIndex = (titleIndex + 1) % titles.length;
            delay = 500; // Pause before typing next word
        }
        
        setTimeout(type, delay);
    }
    
    setTimeout(type, 1000);
}

/* --- Animated Skills Level Bars --- */
function initSkillsAnimation() {
    const skillsSection = document.getElementById('skills');
    if (!skillsSection) return;
    
    const skillBars = document.querySelectorAll('.skill-bar-fill');
    
    const skillsObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                skillBars.forEach(bar => {
                    const level = bar.getAttribute('data-level');
                    bar.style.width = level + '%';
                });
                // Unobserve once animated
                skillsObserver.unobserve(entry.target);
            }
        });
    }, {
        threshold: 0.1
    });
    
    skillsObserver.observe(skillsSection);
}

/* --- AJAX Contact Form Handler --- */
function initContactForm() {
    const contactForm = document.getElementById('contact-form');
    if (!contactForm) return;
    
    contactForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        const submitBtn = contactForm.querySelector('button[type="submit"]');
        const originalText = submitBtn.textContent;
        submitBtn.disabled = true;
        submitBtn.textContent = 'Sending...';
        
        const formData = new FormData(contactForm);
        
        try {
            // Send request via AJAX to index.php which will process the request
            const response = await fetch('', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            
            const result = await response.json();
            
            if (result.success) {
                showToast(result.message || 'Message sent successfully!', 'success');
                contactForm.reset();
            } else {
                showToast(result.message || 'An error occurred. Please try again.', 'error');
            }
        } catch (error) {
            console.error('Contact Form Error:', error);
            showToast('Unable to send message. Check network connection.', 'error');
        } finally {
            submitBtn.disabled = false;
            submitBtn.textContent = originalText;
        }
    });
}

/* --- Toast Notifications --- */
function showToast(message, type = 'success') {
    // Remove any existing toasts
    const existingToast = document.querySelector('.toast');
    if (existingToast) {
        existingToast.remove();
    }
    
    const toast = document.createElement('div');
    toast.className = `toast ${type}`;
    toast.textContent = message;
    
    document.body.appendChild(toast);
    
    // Trigger animation frame
    setTimeout(() => {
        toast.classList.add('show');
    }, 100);
    
    // Auto-remove after 4 seconds
    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => toast.remove(), 400);
    }, 4000);
}
