/**
 * Diagnostics Process Framework - JavaScript
 * Handles phase navigation, session persistence, form data, and progress tracking
 */

document.addEventListener('DOMContentLoaded', function() {
  
  // ========================================
  // INITIALIZATION
  // ========================================

  const container = document.querySelector('.diagnostic-container');
  const moduleElement = container ? container.getAttribute('data-module') : null;
  const currentPhase = container ? parseInt(container.getAttribute('data-phase')) : 1;

  if (!moduleElement) return;

  const moduleDataScript = document.getElementById('moduleData');
  const moduleData = moduleDataScript ? JSON.parse(moduleDataScript.textContent) : {};

  // Session key for localStorage
  const sessionKey = `diagnostics_${moduleElement}`;

  // ========================================
  // SESSION MANAGEMENT
  // ========================================

  const Session = {
    get: function() {
      const stored = localStorage.getItem(sessionKey);
      return stored ? JSON.parse(stored) : this.init();
    },

    init: function() {
      const initial = {
        moduleId: moduleElement,
        startTime: Date.now(),
        currentPhase: currentPhase,
        screening: {},
        main: {},
        reflection: {},
        completed: false
      };
      this.save(initial);
      return initial;
    },

    save: function(data) {
      localStorage.setItem(sessionKey, JSON.stringify(data));
    },

    update: function(key, value) {
      const session = this.get();
      session[key] = value;
      session.currentPhase = currentPhase;
      this.save(session);
    },

    addTime: function() {
      const session = this.get();
      const elapsed = Math.round((Date.now() - session.startTime) / 1000);
      session.duration = elapsed;
      this.save(session);
    }
  };

  // Initialize or retrieve session
  const currentSession = Session.get();

  // ========================================
  // PHASE NAVIGATION
  // ========================================

  const Navigation = {
    goToPhase: function(phaseNum) {
      const url = new URL(window.location);
      url.searchParams.set('phase', phaseNum);
      window.location.href = url.toString();
    },

    setupPhaseButtons: function() {
      // Next phase buttons
      document.querySelectorAll('[data-next-phase]').forEach(btn => {
        btn.addEventListener('click', function(e) {
          e.preventDefault();
          
          // Validate current phase data before moving forward
          if (!Navigation.validatePhase(currentPhase)) {
            alert('Bitte fülle die erforderlichen Felder aus, bevor du weitergehen kannst.');
            return;
          }

          const nextPhase = parseInt(this.getAttribute('data-next-phase'));
          Session.update('currentPhase', nextPhase);
          Navigation.goToPhase(nextPhase);
        });
      });

      // Previous phase buttons
      document.querySelectorAll('[data-prev-phase]').forEach(btn => {
        btn.addEventListener('click', function(e) {
          e.preventDefault();
          const prevPhase = parseInt(this.getAttribute('data-prev-phase'));
          Navigation.goToPhase(prevPhase);
        });
      });
    },

    validatePhase: function(phase) {
      if (phase === 2) {
        // Screening is optional - always allow proceeding
        return true;
      } else if (phase === 3) {
        // Main test - check if at least some answers are given
        const testInputs = document.querySelectorAll('.main-test-form input[type="radio"]:checked');
        return testInputs.length > 0;
      } else if (phase === 4) {
        // Reflection - optional but encourage
        return true;
      }
      return true;
    }
  };

  // ========================================
  // FORM DATA PERSISTENCE
  // ========================================

  const FormPersistence = {
    restoreFormData: function() {
      // Restore screening answers
      document.querySelectorAll('.screening-input').forEach(input => {
        const question = input.getAttribute('data-question');
        const savedValue = currentSession.screening[question];
        if (savedValue && input.value === savedValue) {
          input.checked = true;
        }
      });

      // Restore main test answers
      document.querySelectorAll('.test-input').forEach(input => {
        const question = input.getAttribute('data-question');
        const savedValue = currentSession.main[question];
        if (savedValue && input.value === savedValue) {
          input.checked = true;
        }
      });

      // Restore reflection text
      document.querySelectorAll('.reflection-textarea').forEach(textarea => {
        const key = textarea.getAttribute('id');
        const savedValue = currentSession.reflection[key];
        if (savedValue) {
          textarea.value = savedValue;
        }
      });
    },

    saveFormData: function() {
      // Save screening answers
      document.querySelectorAll('.screening-input:checked').forEach(input => {
        const question = input.getAttribute('data-question');
        currentSession.screening[question] = input.value;
      });

      // Save main test answers
      document.querySelectorAll('.test-input:checked').forEach(input => {
        const question = input.getAttribute('data-question');
        currentSession.main[question] = input.value;
      });

      // Save reflection text
      document.querySelectorAll('.reflection-textarea').forEach(textarea => {
        const key = textarea.getAttribute('id');
        currentSession.reflection[key] = textarea.value;
      });

      Session.save(currentSession);
    },

    setupAutoSave: function() {
      // Save form data on change
      document.querySelectorAll('.screening-input, .test-input, .reflection-textarea').forEach(el => {
        el.addEventListener('change', () => FormPersistence.saveFormData());
        el.addEventListener('blur', () => FormPersistence.saveFormData());
      });

      // Save on page unload
      window.addEventListener('beforeunload', () => {
        Session.addTime();
        FormPersistence.saveFormData();
      });
    }
  };

  // ========================================
  // RESULTS HANDLING
  // ========================================

  const Results = {
    downloadPDF: function() {
      const element = document.querySelector('.results-container');
      if (!element) return;

      // Use html2pdf or similar library
      console.log('PDF generation would use html2pdf library');
      // Example: html2pdf().set(options).from(element).save('diagnostik-report.pdf');
      
      // Fallback: Use browser print dialog
      alert('PDF-Download wird durch html2pdf-Library bereitgestellt.\nVorerst können Sie die Druckvorschau nutzen (Strg+P).');
    },

    printReport: function() {
      window.print();
    },

    setupResultsButtons: function() {
      document.getElementById('downloadReportBtn')?.addEventListener('click', function(e) {
        e.preventDefault();
        Results.downloadPDF();
      });

      document.getElementById('printReportBtn')?.addEventListener('click', function(e) {
        e.preventDefault();
        Results.printReport();
      });

      document.getElementById('startNewModuleBtn')?.addEventListener('click', function(e) {
        e.preventDefault();
        // Clear current session and go back to diagnostics overview
        localStorage.removeItem(sessionKey);
        window.location.href = 'diagnostics.php';
      });
    }
  };

  // ========================================
  // PROGRESS TRACKING
  // ========================================

  const Progress = {
    updateIndicator: function() {
      // Update step indicators
      document.querySelectorAll('.phase-step').forEach(step => {
        const phaseNum = parseInt(step.getAttribute('data-phase'));
        if (phaseNum < currentPhase) {
          step.classList.add('completed');
        } else if (phaseNum === currentPhase) {
          step.classList.add('active');
        }
      });

      // Update progress bar
      const progressFill = document.querySelector('.progress-fill');
      if (progressFill) {
        const percentage = (currentPhase / 5) * 100;
        progressFill.style.width = percentage + '%';
      }
    },

    updateStatusCount: function() {
      const statusEl = document.getElementById('statusCount');
      if (statusEl) {
        const answers = Object.keys(currentSession.screening).length +
                       Object.keys(currentSession.main).length;
        statusEl.textContent = `Fortschritt: ${answers} Antworten erfasst`;
      }
    }
  };

  // ========================================
  // INITIALIZATION
  // ========================================

  // Setup all event listeners and restore data
  FormPersistence.restoreFormData();
  FormPersistence.setupAutoSave();
  Navigation.setupPhaseButtons();
  Results.setupResultsButtons();
  Progress.updateIndicator();
  Progress.updateStatusCount();

  // ========================================
  // HELPER: Show/Hide with Transitions
  // ========================================

  function slideDown(element, duration = 300) {
    element.style.overflow = 'hidden';
    element.style.maxHeight = '0';
    element.style.opacity = '0';
    
    setTimeout(() => {
      element.style.transition = `max-height ${duration}ms ease-out, opacity ${duration}ms ease-out`;
      element.style.maxHeight = element.scrollHeight + 'px';
      element.style.opacity = '1';
    }, 10);
  }

  function slideUp(element, duration = 300) {
    element.style.transition = `max-height ${duration}ms ease-out, opacity ${duration}ms ease-out`;
    element.style.maxHeight = '0';
    element.style.opacity = '0';
    
    setTimeout(() => {
      element.style.display = 'none';
    }, duration);
  }

  // ========================================
  // PHASE-SPECIFIC INTERACTIONS
  // ========================================

  // Phase 2: Screening - No special interactions needed
  
  // Phase 3: Main Test - Optional: Add question navigation with keyboard arrows
  if (currentPhase === 3) {
    document.addEventListener('keydown', function(e) {
      if (e.key === 'ArrowDown') {
        // Move to next question
        const questions = document.querySelectorAll('.test-question-group');
        if (questions.length > 1) {
          questions[1].scrollIntoView({ behavior: 'smooth' });
        }
      }
    });
  }

  // Phase 4: Reflection - Auto-expand textarea on focus
  document.querySelectorAll('.reflection-textarea').forEach(textarea => {
    textarea.addEventListener('focus', function() {
      this.style.minHeight = '120px';
    });
  });

  // ========================================
  // DEBUG: Log Session to Console
  // ========================================

  if (window.location.hash === '#debug') {
    console.log('Current Session:', currentSession);
    console.log('Module Data:', moduleData);
  }

});

// ========================================
// EXPORT: Session Management API
// ========================================

window.DiagnosticsAPI = {
  getSession: function(moduleId) {
    const stored = localStorage.getItem(`diagnostics_${moduleId}`);
    return stored ? JSON.parse(stored) : null;
  },

  clearSession: function(moduleId) {
    localStorage.removeItem(`diagnostics_${moduleId}`);
  },

  getAllSessions: function() {
    const sessions = {};
    for (let i = 0; i < localStorage.length; i++) {
      const key = localStorage.key(i);
      if (key.startsWith('diagnostics_')) {
        sessions[key] = JSON.parse(localStorage.getItem(key));
      }
    }
    return sessions;
  }
};
