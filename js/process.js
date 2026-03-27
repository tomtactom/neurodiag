/**
 * Diagnostics Process Framework - JavaScript
 * Cookie-basierte Persistenz für Prozessfortschritt
 */

document.addEventListener('DOMContentLoaded', function () {
  const container = document.querySelector('.diagnostic-container');
  if (!container) return;

  const moduleId = container.getAttribute('data-module');
  const currentPhase = parseInt(container.getAttribute('data-phase') || '1', 10);
  const maxAgeSeconds = 60 * 60 * 24 * 30; // 30 Tage
  const cookieKey = `neurodiag_process_${moduleId}`;

  if (!moduleId) return;

  const CookieStore = {
    buildAttributes: function () {
      const attrs = [`path=/`, `max-age=${maxAgeSeconds}`, 'SameSite=Lax'];
      if (window.location.protocol === 'https:') {
        attrs.push('Secure');
      }
      return attrs.join('; ');
    },

    set: function (value) {
      document.cookie = `${cookieKey}=${encodeURIComponent(JSON.stringify(value))}; ${this.buildAttributes()}`;
    },

    get: function () {
      const raw = document.cookie
        .split('; ')
        .find((entry) => entry.startsWith(`${cookieKey}=`));

      if (!raw) return null;

      try {
        return JSON.parse(decodeURIComponent(raw.split('=').slice(1).join('=')));
      } catch (error) {
        return null;
      }
    },

    remove: function () {
      const attrs = ['path=/', 'max-age=0', 'SameSite=Lax'];
      if (window.location.protocol === 'https:') {
        attrs.push('Secure');
      }
      document.cookie = `${cookieKey}=; ${attrs.join('; ')}`;
    }
  };

  const State = {
    normalize: function (input) {
      const base = {
        processId: moduleId,
        currentUnit: currentPhase,
        answers: {
          screening: {},
          main: {},
          reflection: {}
        },
        updatedAt: Date.now(),
        completed: false
      };

      if (!input || typeof input !== 'object') {
        return base;
      }

      const state = { ...base, ...input };
      state.currentUnit = Number.isInteger(state.currentUnit) ? state.currentUnit : currentPhase;
      state.answers = state.answers && typeof state.answers === 'object' ? state.answers : base.answers;
      state.answers.screening = state.answers.screening && typeof state.answers.screening === 'object' ? state.answers.screening : {};
      state.answers.main = state.answers.main && typeof state.answers.main === 'object' ? state.answers.main : {};
      state.answers.reflection = state.answers.reflection && typeof state.answers.reflection === 'object' ? state.answers.reflection : {};
      state.updatedAt = typeof state.updatedAt === 'number' ? state.updatedAt : Date.now();
      state.completed = Boolean(state.completed);
      state.processId = moduleId;
      return state;
    },

    get: function () {
      return this.normalize(CookieStore.get());
    },

    save: function (state) {
      const normalized = this.normalize(state);
      normalized.updatedAt = Date.now();
      CookieStore.set(normalized);
      return normalized;
    },

    markCompleted: function () {
      const state = this.get();
      state.completed = true;
      state.currentUnit = 5;
      this.save(state);
    },

    clear: function () {
      CookieStore.remove();
    }
  };

  let currentState = State.get();

  const FormPersistence = {
    restore: function () {
      document.querySelectorAll('.screening-input').forEach((input) => {
        const question = input.getAttribute('data-question');
        const savedValue = currentState.answers.screening[question];
        if (savedValue !== undefined && String(savedValue) === input.value) {
          input.checked = true;
        }
      });

      document.querySelectorAll('.test-input').forEach((input) => {
        const question = input.getAttribute('data-question');
        const savedValue = currentState.answers.main[question];
        if (savedValue !== undefined && String(savedValue) === input.value) {
          input.checked = true;
        }
      });

      document.querySelectorAll('.reflection-textarea').forEach((textarea) => {
        const key = textarea.getAttribute('id');
        const savedValue = currentState.answers.reflection[key];
        if (typeof savedValue === 'string') {
          textarea.value = savedValue;
        }
      });
    },

    save: function () {
      const nextState = State.get();
      nextState.currentUnit = currentPhase;

      document.querySelectorAll('.screening-input:checked').forEach((input) => {
        nextState.answers.screening[input.getAttribute('data-question')] = input.value;
      });

      document.querySelectorAll('.test-input:checked').forEach((input) => {
        nextState.answers.main[input.getAttribute('data-question')] = input.value;
      });

      document.querySelectorAll('.reflection-textarea').forEach((textarea) => {
        nextState.answers.reflection[textarea.getAttribute('id')] = textarea.value;
      });

      currentState = State.save(nextState);
    },

    setupAutoSave: function () {
      document.querySelectorAll('.screening-input, .test-input, .reflection-textarea').forEach((element) => {
        element.addEventListener('change', () => this.save());
        element.addEventListener('blur', () => this.save());
      });

      window.addEventListener('beforeunload', () => this.save());
    }
  };

  const Navigation = {
    validatePhase: function (phase) {
      if (phase === 3) {
        return document.querySelectorAll('.main-test-form input[type="radio"]:checked').length > 0;
      }
      return true;
    },

    goToPhase: function (phaseNum) {
      const url = new URL(window.location.href);
      url.searchParams.set('phase', String(phaseNum));
      window.location.href = url.toString();
    },

    setup: function () {
      document.querySelectorAll('[data-next-phase]').forEach((button) => {
        button.addEventListener('click', (event) => {
          event.preventDefault();

          if (!this.validatePhase(currentPhase)) {
            alert('Bitte fülle die erforderlichen Felder aus, bevor du weitergehen kannst.');
            return;
          }

          const nextPhase = parseInt(button.getAttribute('data-next-phase') || '1', 10);
          const nextState = State.get();
          nextState.currentUnit = nextPhase;
          if (nextPhase >= 5) {
            nextState.completed = true;
          }
          currentState = State.save(nextState);
          this.goToPhase(nextPhase);
        });
      });

      document.querySelectorAll('[data-prev-phase]').forEach((button) => {
        button.addEventListener('click', (event) => {
          event.preventDefault();
          const prevPhase = parseInt(button.getAttribute('data-prev-phase') || '1', 10);
          const nextState = State.get();
          nextState.currentUnit = prevPhase;
          currentState = State.save(nextState);
          this.goToPhase(prevPhase);
        });
      });
    }
  };

  const Progress = {
    updateIndicator: function () {
      document.querySelectorAll('.phase-step').forEach((step) => {
        const phaseNum = parseInt(step.getAttribute('data-phase') || '0', 10);
        if (phaseNum < currentPhase) {
          step.classList.add('completed');
        } else if (phaseNum === currentPhase) {
          step.classList.add('active');
        }
      });

      const progressFill = document.querySelector('.progress-fill');
      if (progressFill) {
        progressFill.style.width = `${(currentPhase / 5) * 100}%`;
      }
    }
  };

  const Results = {
    setup: function () {
      document.getElementById('startNewModuleBtn')?.addEventListener('click', (event) => {
        event.preventDefault();
        State.clear();
        window.location.href = 'diagnostics.php';
      });

      document.getElementById('downloadReportBtn')?.addEventListener('click', (event) => {
        event.preventDefault();
        alert('PDF-Download ist noch nicht aktiv. Nutze bitte vorübergehend die Druckfunktion des Browsers.');
      });

      document.getElementById('printReportBtn')?.addEventListener('click', (event) => {
        event.preventDefault();
        window.print();
      });
    }
  };

  // Initial restoration and hooks
  FormPersistence.restore();
  FormPersistence.setupAutoSave();
  Navigation.setup();
  Progress.updateIndicator();
  Results.setup();

  if (currentPhase >= 5) {
    State.markCompleted();
  }
});

window.DiagnosticsAPI = {
  getProcessCookie: function (moduleId) {
    const key = `neurodiag_process_${moduleId}=`;
    const raw = document.cookie.split('; ').find((entry) => entry.startsWith(key));
    if (!raw) return null;

    try {
      return JSON.parse(decodeURIComponent(raw.slice(key.length)));
    } catch (error) {
      return null;
    }
  }
};
