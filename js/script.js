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

  document.querySelectorAll('.feature-card, .module-card, .resource-card, .info-card').forEach(el => {
    el.classList.add('animated-hidden');
    observer.observe(el);
  });

  const filterButtons = document.querySelectorAll('.filter-btn');
  const moduleCards = document.querySelectorAll('.module-card');
  const statusCount = document.getElementById('statusCount');
  const progressFill = document.getElementById('progressFill');

  const updateProgress = () => {
    const completed = window.localStorage.getItem('completedModules');
    const count = completed ? Number(completed) : 0;
    statusCount.textContent = count > 0 ? `${count} von 8 Modulen ausgewählt` : 'Noch kein Modul gestartet';
    const pct = Math.min(100, Math.round((count / 8) * 100));
    if (progressFill) {
      progressFill.style.width = `${pct}%`;
    }
  };

  const setModuleCompleted = () => {
    const completed = window.localStorage.getItem('completedModules');
    const count = completed ? Number(completed) : 0;
    const newCount = Math.min(8, count + 1);
    window.localStorage.setItem('completedModules', newCount);
    updateProgress();
  };

  moduleCards.forEach(card => {
    const link = card.querySelector('a.btn-secondary');
    if (link) {
      link.addEventListener('click', () => setModuleCompleted());
    }
  });

  if (filterButtons.length) {
    filterButtons.forEach(btn => {
      btn.addEventListener('click', function() {
        const tag = this.getAttribute('data-filter');
        document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
        this.classList.add('active');

        moduleCards.forEach(card => {
          if (tag === 'all' || card.dataset.category === tag) {
            card.style.display = 'flex';
          } else {
            card.style.display = 'none';
          }
        });
      });
    });
  }

  updateProgress();
});