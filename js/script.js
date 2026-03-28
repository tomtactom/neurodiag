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
    const closeMenu = () => {
      navToggle.setAttribute('aria-expanded', 'false');
      navbar.classList.remove('navbar-open');
    };

    navToggle.addEventListener('click', function() {
      const expanded = this.getAttribute('aria-expanded') === 'true';
      this.setAttribute('aria-expanded', String(!expanded));
      navbar.classList.toggle('navbar-open');
    });

    navbar.querySelectorAll('a').forEach(link => {
      link.addEventListener('click', () => {
        if (window.innerWidth <= 960) {
          closeMenu();
        }
      });
    });

    document.addEventListener('click', e => {
      if (window.innerWidth <= 960 && navbar.classList.contains('navbar-open')) {
        const clickedInsideNav = navbar.contains(e.target) || navToggle.contains(e.target);
        if (!clickedInsideNav) {
          closeMenu();
        }
      }
    });

    document.addEventListener('keydown', e => {
      if (e.key === 'Escape') {
        closeMenu();
      }
    });

    window.addEventListener('resize', () => {
      if (window.innerWidth > 960) {
        closeMenu();
      }
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

  // Expandable panel toggle
  const expandButtons = document.querySelectorAll('.expand-btn');
  expandButtons.forEach(btn => {
    btn.addEventListener('click', function(e) {
      e.preventDefault();
      const details = this.nextElementSibling;
      if (details && details.classList.contains('module-details')) {
        const isExpanded = this.getAttribute('aria-expanded') === 'true';
        this.setAttribute('aria-expanded', String(!isExpanded));
        details.classList.toggle('show');
        // Toggle the hidden attribute
        if (details.hidden) {
          details.removeAttribute('hidden');
        } else {
          details.setAttribute('hidden', '');
        }
        this.textContent = isExpanded ? 'Details anzeigen' : 'Details ausblenden';
      }
    });
  });

  moduleCards.forEach(card => {
    const link = card.querySelector('a.btn-secondary');
    if (link) {
      link.addEventListener('click', () => {
        setModuleCompleted();
        // Track which module was started for sequencing recommendations
        const moduleTitle = card.querySelector('h3');
        if (moduleTitle) {
          const completedModules = JSON.parse(window.localStorage.getItem('completedModulesHistory') || '[]');
          if (!completedModules.includes(moduleTitle.textContent)) {
            completedModules.push(moduleTitle.textContent);
            window.localStorage.setItem('completedModulesHistory', JSON.stringify(completedModules));
          }
        }
      });
    }
  });

  // Resources Filter Logic
  const resourceFilterButtons = document.querySelectorAll('.resources-filters .filter-btn');
  const resourceCategories = document.querySelectorAll('.resource-category');

  if (resourceFilterButtons.length) {
    resourceFilterButtons.forEach(btn => {
      btn.addEventListener('click', function() {
        const filter = this.getAttribute('data-filter');
        
        resourceFilterButtons.forEach(b => b.classList.remove('active'));
        this.classList.add('active');

        resourceCategories.forEach(category => {
          const categoryFilter = category.getAttribute('data-category');
          if (filter === 'all' || categoryFilter === filter) {
            category.style.display = 'block';
            category.style.animation = 'fadeIn 0.3s ease-out';
          } else {
            category.style.display = 'none';
          }
        });
      });
    });
  }

  // FAQ Toggle Logic
  const faqToggles = document.querySelectorAll('.faq-toggle');
  faqToggles.forEach(toggle => {
    toggle.addEventListener('click', function() {
      const isExpanded = this.getAttribute('aria-expanded') === 'true';
      const content = this.nextElementSibling;

      this.setAttribute('aria-expanded', String(!isExpanded));
      
      if (content) {
        if (isExpanded) {
          content.setAttribute('hidden', '');
        } else {
          content.removeAttribute('hidden');
        }
      }
    });
  });

  // Test Preview Modal Logic
  const previewButtons = document.querySelectorAll('.btn-preview');
  const previewModal = document.getElementById('previewModal');
  const previewClose = document.querySelector('.preview-close');
  const previewCancelBtn = document.querySelector('.preview-cancel-btn');

  if (previewButtons.length && previewModal) {
    // Vorschaufragen für einen kurzen Eindruck der jeweiligen Module
    const previewQuestions = {
      'ass': [
        'Wie belastend sind soziale Situationen mit unklaren Erwartungen für dich?',
        'Wie stark beeinflussen Reize (Licht, Geräusche, Berührung) deinen Alltag?',
        'Wie hilfreich sind feste Routinen für Ruhe und Selbststeuerung?'
      ],
      'adhs': [
        'Wie oft fällt dir der Einstieg in wichtige Aufgaben schwer?',
        'Wie häufig springt deine Aufmerksamkeit trotz Motivation weg?',
        'Wie stark beeinflusst Impulsivität Entscheidungen im Alltag?'
      ],
      'dyslexie-lrs': [
        'Wie anstrengend sind Lesen und Rechtschreibung unter Zeitdruck?',
        'Wie oft vertauschst du Buchstaben, Silben oder Wortfolgen?',
        'Wie sehr helfen dir auditive oder visuelle Strategien beim Verstehen?'
      ],
      'dysgraphie': [
        'Wie stark weicht deine schriftliche von deiner mündlichen Ausdrucksfähigkeit ab?',
        'Wie häufig bremsen Handschrift oder Schreibtempo deine Leistung?',
        'Wie hilfreich sind alternative Ausdruckswege (Tippen, Sprechen, Skizzen)?'
      ],
      'dyskalkulie': [
        'Wie sicher fühlst du dich bei Mengen, Zahlenreihen und Grundrechenarten?',
        'Wie oft brauchst du externe Hilfen für alltägliche Rechenaufgaben?',
        'Wie stark steigen Fehler bei Zeitdruck oder Stress?'
      ],
      'dyspraxie-dcd': [
        'Wie häufig benötigen Bewegungsabläufe bewusste Schritt-für-Schritt-Planung?',
        'Wie oft passieren Koordinationsfehler im Alltag?',
        'Wie sehr hilft dir strukturierte Vorbereitung bei motorischen Aufgaben?'
      ],
      'tic-tourette': [
        'Wie häufig treten unwillkürliche Bewegungen oder Laute auf?',
        'Wie deutlich nimmst du eine innere Anspannung vor dem Tic wahr?',
        'Wie gut helfen dir erlernte Strategien zur Spannungsregulation?'
      ],
      'dld': [
        'Wie oft sind längere sprachliche Anweisungen schwer verständlich?',
        'Wie häufig treten Wortfindungsprobleme in Gesprächen auf?',
        'Wie stark hilft dir visuelle Unterstützung bei Kommunikation?'
      ]
    };

    previewButtons.forEach(btn => {
      btn.addEventListener('click', function(e) {
        e.preventDefault();
        const moduleId = this.getAttribute('data-module');
        const moduleTitle = this.closest('.module-card').querySelector('h3').textContent;
        
        const title = document.getElementById('previewTitle');
        const questionsContainer = document.getElementById('previewQuestions');
        const startBtn = document.getElementById('previewStartBtn');
        
        title.textContent = `Vorschau: ${moduleTitle}`;
        questionsContainer.innerHTML = '';

        const intro = document.createElement('p');
        intro.className = 'preview-intro';
        intro.textContent = 'Kurzcheck mit Beispielitems zur Selbstorientierung (keine klinische Diagnose).';
        questionsContainer.appendChild(intro);
        
        const questions = previewQuestions[moduleId] || [];
        questions.forEach(q => {
          const qEl = document.createElement('div');
          qEl.className = 'preview-question';
          qEl.innerHTML = `<p>${q}</p>`;
          questionsContainer.appendChild(qEl);
        });
        
        startBtn.href = `process.php?process=${moduleId}`;
        startBtn.onclick = () => {
          window.location.href = `process.php?process=${moduleId}`;
        };
        
        previewModal.style.display = 'flex';
      });
    });

    if (previewClose) {
      previewClose.addEventListener('click', () => {
        previewModal.style.display = 'none';
      });
    }

    if (previewCancelBtn) {
      previewCancelBtn.addEventListener('click', () => {
        previewModal.style.display = 'none';
      });
    }

    previewModal.addEventListener('click', (e) => {
      if (e.target === previewModal) {
        previewModal.style.display = 'none';
      }
    });
  }

  updateProgress();
});
