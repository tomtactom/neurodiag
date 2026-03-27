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

  // Module Sequencing: Show recommended next modules based on diagnostic history
  const sequenceRecommendations = {
    'aq-test': ['asrs-test', 'dyspraxia-test', 'dld-test'],
    'asrs-test': ['aq-test', 'dyslexia-test', 'dyspraxia-test'],
    'dyslexia-test': ['dysgraphia-test', 'dyskalkulie-test', 'dld-test'],
    'dysgraphia-test': ['dyslexia-test', 'dyspraxia-test', 'dld-test'],
    'dyskalkulie-test': ['dyslexia-test', 'asrs-test', 'dld-test'],
    'dyspraxia-test': ['aq-test', 'dysgraphia-test', 'asrs-test'],
    'tic-test': ['asrs-test', 'aq-test', 'dld-test'],
    'dld-test': ['dyslexia-test', 'aq-test', 'asrs-test']
  };

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

  // Quick-check symptom filtering
  const quickCheckInputs = document.querySelectorAll('.quick-check-input');
  if (quickCheckInputs.length) {
    quickCheckInputs.forEach(input => {
      input.addEventListener('change', function() {
        const selectedCategories = [];
        quickCheckInputs.forEach(cb => {
          if (cb.checked) {
            selectedCategories.push(cb.value);
          }
        });

        moduleCards.forEach(card => {
          const cardCategory = card.dataset.category;
          if (selectedCategories.length === 0) {
            card.style.display = 'flex';
          } else if (selectedCategories.includes(cardCategory)) {
            card.style.display = 'flex';
          } else {
            card.style.display = 'none';
          }
        });
      });
    });
  }

  // Module Sequencing: Display recommended next modules
  const renderSequenceRecommendations = () => {
    const completedHistory = JSON.parse(window.localStorage.getItem('completedModulesHistory') || '[]');
    if (completedHistory.length === 0) return; // Don't show if no modules started yet

    const sequenceSection = document.getElementById('sequence-recommendations');
    const container = document.getElementById('recommendedModulesContainer');
    
    if (!sequenceSection || !container) return;

    // Get module titles that were completed
    const moduleIdMap = {
      'Autismus': 'aq-test',
      'ADHS': 'asrs-test',
      'Dyslexie': 'dyslexia-test',
      'Dysgraphie': 'dysgraphia-test',
      'Dyskalkulie': 'dyskalkulie-test',
      'Dyspraxie': 'dyspraxia-test',
      'Tics/Tourette': 'tic-test',
      'Sprachstörung': 'dld-test'
    };

    // Get the last completed module
    const lastModule = completedHistory[completedHistory.length - 1];
    const lastModuleId = moduleIdMap[lastModule];
    
    if (!lastModuleId || !sequenceRecommendations[lastModuleId]) return;

    const recommended = sequenceRecommendations[lastModuleId];
    const moduleTitleMap = Object.fromEntries(
      Object.entries(moduleIdMap).map(([k, v]) => [v, k])
    );

    container.innerHTML = '';
    recommended.slice(0, 3).forEach(moduleId => {
      const moduleTitle = moduleTitleMap[moduleId] || moduleId;
      const item = document.createElement('div');
      item.className = 'seq-item';
      item.innerHTML = `
        <h3>${moduleTitle}</h3>
        <p>Könnte weitere Einblicke geben, basierend auf deinen bisherigen Ergebnissen.</p>
        <a href="tests/test.php?module=${moduleId}" class="btn btn-secondary">Modul starten</a>
      `;
      container.appendChild(item);
    });

    sequenceSection.style.display = 'block';
  };

  // Render sequencing recommendations on page load
  if (document.location.pathname.includes('diagnostics.php')) {
    renderSequenceRecommendations();
  }

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
    // Mock test questions for preview (in real implementation, fetch from API)
    const previewQuestions = {
      'aq-test': [
        'Ich bevorzuge es, meine Zeit allein zu verbringen oder mit nur sehr engen Personen.',
        'Ich habe eine lebhafte innere Welt mit großem Detailreichtum.',
        'Ich mag es, wenn Dinge vorhersehbar und strukturiert sind.'
      ],
      'asrs-test': [
        'Ich zappele oder bin in Bewegung, auch wenn ich sitzen sollte.',
        'Ich habe Schwierigkeiten, mich auf eine Aufgabe zu konzentrieren.',
        'Ich bin impulsiv und handle manchmal ohne viel zu überlegen.'
      ],
      'dyslexia-test': [
        'Ich habe Schwierigkeiten mit schnellem Lesen oder Rechtschreibung.',
        'Ich lese gerne, aber es braucht länger, um Wörter zu erkennen.',
        'Ich verstehe besser durch Hören oder Bilder als durch Lesen.'
      ],
      'dysgraphia-test': [
        'Schreiben fällt mir deutlich schwerer als Sprechen.',
        'Meine Handschrift ist manchmal schwer zu lesen.',
        'Ich drücke meine Gedanken lieber mündlich aus.'
      ],
      'dyskalkulie-test': [
        'Ich habe Schwierigkeiten mit Rechnen oder Zahlenverständnis.',
        'Mathematik ist für mich besonders anstrengend.',
        'Ich zähle oft an den Fingern oder brauche andere Hilfsmittel.'
      ],
      'dyspraxia-test': [
        'Ich habe Schwierigkeiten mit Bewegungskoordination.',
        'Einfache motorische Aufgaben erfordern bewusste Planung.',
        'Ich bin manchmal ungeschickt oder stoße gegen Dinge.'
      ],
      'tic-test': [
        'Ich habe unwillkürliche Bewegungen oder Geräusche.',
        'Diese Bewegungen/Laute entstehen aus innerer Spannung.',
        'Ich kann diese Bewegungen/Laute nur begrenzt kontrollieren.'
      ],
      'dld-test': [
        'Ich habe Schwierigkeiten mit Sprache oder Sprachverarbeitung.',
        'Ich verstehe besser, wenn langsam und deutlich gesprochen wird.',
        'Ich drücke mich schwerer aus als ich verstehe.'
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
        
        const questions = previewQuestions[moduleId] || [];
        questions.forEach(q => {
          const qEl = document.createElement('div');
          qEl.className = 'preview-question';
          qEl.innerHTML = `<p>${q}</p>`;
          questionsContainer.appendChild(qEl);
        });
        
        startBtn.href = `tests/test.php?module=${moduleId}`;
        startBtn.onclick = () => {
          window.location.href = `tests/test.php?module=${moduleId}`;
        };
        
        previewModal.style.display = 'flex';
      });
    });

    previewClose.addEventListener('click', () => {
      previewModal.style.display = 'none';
    });

    previewCancelBtn.addEventListener('click', () => {
      previewModal.style.display = 'none';
    });

    previewModal.addEventListener('click', (e) => {
      if (e.target === previewModal) {
        previewModal.style.display = 'none';
      }
    });
  }

  updateProgress();
});