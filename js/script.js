// NeuroDiag JavaScript - Basic interactivity

document.addEventListener('DOMContentLoaded', function() {
  const navToggle = document.querySelector('.nav-toggle');
  const navbar = document.querySelector('.navbar');
  const themeToggle = document.getElementById('themeToggle');
  const body = document.body;

  const applyTheme = mode => {
    if (mode === 'dark') {
      body.classList.add('theme-dark');
      body.classList.remove('theme-light');
      themeToggle.textContent = '☀️';
    } else {
      body.classList.add('theme-light');
      body.classList.remove('theme-dark');
      themeToggle.textContent = '🌙';
    }
    localStorage.setItem('neurodiagTheme', mode);
  };

  const savedTheme = localStorage.getItem('neurodiagTheme');
  if (savedTheme) {
    applyTheme(savedTheme);
  } else {
    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
    applyTheme(prefersDark ? 'dark' : 'light');
  }

  if (themeToggle) {
    themeToggle.addEventListener('click', function() {
      const active = body.classList.contains('theme-dark') ? 'dark' : 'light';
      applyTheme(active === 'dark' ? 'light' : 'dark');
    });
  }

  if (navToggle && navbar) {
    navToggle.addEventListener('click', function() {
      const expanded = this.getAttribute('aria-expanded') === 'true';
      this.setAttribute('aria-expanded', String(!expanded));
      navbar.classList.toggle('navbar-open');
    });
  }

  document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function(e) {
      e.preventDefault();
      document.querySelector(this.getAttribute('href'))?.scrollIntoView({ behavior: 'smooth' });
    });
  });

  const banner = document.getElementById('cookieBanner');
  const acceptBtn = document.getElementById('cookieAccept');
  const rejectBtn = document.getElementById('cookieReject');

  const handleCookie = consent => {
    localStorage.setItem('cookieConsent', consent);
    if (banner) banner.style.display = 'none';
  };

  const consentState = localStorage.getItem('cookieConsent');
  if (consentState) {
    if (banner) banner.style.display = 'none';
  } else {
    if (banner) banner.style.display = 'flex';
  }

  if (acceptBtn) acceptBtn.addEventListener('click', () => handleCookie('accepted'));
  if (rejectBtn) rejectBtn.addEventListener('click', () => handleCookie('rejected'));

  const observer = new IntersectionObserver(entries => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        entry.target.classList.add('animated-visible');
        observer.unobserve(entry.target);
      }
    });
  }, { threshold: 0.15 });

  document.querySelectorAll('.feature-card, .module-card, .resource-card').forEach(el => {
    el.classList.add('animated-hidden');
    observer.observe(el);
  });
});