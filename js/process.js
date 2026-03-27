(function () {
  'use strict';

  const processRoot = document.querySelector('.process-page[data-process-id][data-unit-id]');
  const form = document.querySelector('.process-questions');
  if (!processRoot || !form) {
    return;
  }

  const processId = (processRoot.dataset.processId || '').trim().toLowerCase();
  const currentUnit = (processRoot.dataset.unitId || '').trim().toLowerCase();

  if (!processId || !currentUnit) {
    return;
  }

  const cookieName = `neurodiag_process_${processId}`;
  const maxAgeSeconds = 60 * 60 * 24 * 30;

  function getCookie(name) {
    const cookieString = document.cookie || '';
    const parts = cookieString.split(';').map((part) => part.trim());
    const encodedPrefix = `${encodeURIComponent(name)}=`;

    for (const part of parts) {
      if (part.startsWith(encodedPrefix)) {
        return decodeURIComponent(part.slice(encodedPrefix.length));
      }
    }

    return null;
  }

  function setCookie(value) {
    const secureAttribute = window.location.protocol === 'https:' ? '; Secure' : '';
    document.cookie = `${encodeURIComponent(cookieName)}=${encodeURIComponent(value)}; path=/; max-age=${maxAgeSeconds}; SameSite=Lax${secureAttribute}`;
  }

  function collectAnswers() {
    const answers = {};

    form.querySelectorAll('input[type="radio"]:checked').forEach((input) => {
      const match = input.name.match(/^answers\[(.+)]$/);
      if (!match || !match[1]) {
        return;
      }

      answers[match[1]] = input.value;
    });

    return answers;
  }

  function saveState(completed) {
    const payload = {
      unit: currentUnit,
      answers: collectAnswers(),
      updatedAt: Date.now(),
      completed: Boolean(completed)
    };

    setCookie(JSON.stringify(payload));
  }

  function parseStoredState() {
    const raw = getCookie(cookieName);
    if (!raw) {
      return null;
    }

    try {
      const parsed = JSON.parse(raw);
      if (!parsed || typeof parsed !== 'object') {
        return null;
      }
      if (typeof parsed.unit !== 'string' || parsed.unit.toLowerCase() !== currentUnit) {
        return null;
      }
      if (!parsed.answers || typeof parsed.answers !== 'object') {
        return null;
      }
      return parsed;
    } catch (error) {
      return null;
    }
  }

  function restoreState() {
    const state = parseStoredState();
    if (!state) {
      return;
    }

    Object.entries(state.answers).forEach(([questionId, answerValue]) => {
      if (typeof answerValue !== 'string') {
        return;
      }

      const selector = `input[type="radio"][name="answers[${CSS.escape(questionId)}]"][value="${CSS.escape(answerValue)}"]`;
      const input = form.querySelector(selector);
      if (input) {
        input.checked = true;
      }
    });
  }

  form.addEventListener('change', function (event) {
    if (!(event.target instanceof HTMLInputElement)) {
      return;
    }

    if (event.target.type === 'radio' && event.target.name.startsWith('answers[')) {
      saveState(false);
    }
  });

  window.addEventListener('beforeunload', function () {
    saveState(false);
  });

  document.querySelectorAll('[data-process-complete="true"]').forEach((element) => {
    element.addEventListener('click', function () {
      saveState(true);
    });
  });

  restoreState();
})();
