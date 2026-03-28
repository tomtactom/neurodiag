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
  const questionItems = Array.from(form.querySelectorAll('[data-question-item="true"]'));
  let smoothTransitionLock = false;

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
    const fields = form.querySelectorAll('input, select, textarea');

    fields.forEach((field) => {
      if (!(field instanceof HTMLInputElement || field instanceof HTMLSelectElement || field instanceof HTMLTextAreaElement)) {
        return;
      }

      const keyMatch = field.name.match(/^answers\[([^\]]+)\](\[\])?$/);
      if (!keyMatch || !keyMatch[1]) {
        return;
      }

      const questionId = keyMatch[1];
      const isList = Boolean(keyMatch[2]);

      if (field instanceof HTMLInputElement) {
        if (field.type === 'radio') {
          if (field.checked) {
            answers[questionId] = field.value;
          }
          return;
        }

        if (field.type === 'checkbox') {
          if (!isList) {
            if (field.checked) {
              answers[questionId] = field.value;
            }
            return;
          }

          if (!Array.isArray(answers[questionId])) {
            answers[questionId] = [];
          }

          if (field.checked) {
            answers[questionId].push(field.value);
          }
          return;
        }
      }

      if (field.value !== '') {
        answers[questionId] = field.value;
      }
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
      if (Array.isArray(answerValue)) {
        answerValue.forEach((value) => {
          if (typeof value !== 'string') {
            return;
          }

          const checkboxSelector = `input[type="checkbox"][name="answers[${CSS.escape(questionId)}][]"][value="${CSS.escape(value)}"]`;
          const input = form.querySelector(checkboxSelector);
          if (input instanceof HTMLInputElement) {
            input.checked = true;
          }
        });
        return;
      }

      if (typeof answerValue !== 'string') {
        return;
      }

      const radioSelector = `input[type="radio"][name="answers[${CSS.escape(questionId)}]"][value="${CSS.escape(answerValue)}"]`;
      const radioInput = form.querySelector(radioSelector);
      if (radioInput instanceof HTMLInputElement) {
        radioInput.checked = true;
        return;
      }

      const inputSelector = `[name="answers[${CSS.escape(questionId)}]"]`;
      const input = form.querySelector(inputSelector);
      if (input instanceof HTMLInputElement || input instanceof HTMLTextAreaElement || input instanceof HTMLSelectElement) {
        input.value = answerValue;
      }
    });
  }

  function getQuestionInputs(questionCard) {
    if (!(questionCard instanceof HTMLElement)) {
      return [];
    }

    return Array.from(questionCard.querySelectorAll('input, select, textarea')).filter((field) => {
      return field instanceof HTMLInputElement || field instanceof HTMLSelectElement || field instanceof HTMLTextAreaElement;
    });
  }

  function questionHasAnswer(questionCard) {
    const fields = getQuestionInputs(questionCard);
    if (!fields.length) {
      return true;
    }

    let radioSeen = false;
    let checkboxSeen = false;
    let radioChecked = false;
    let checkboxChecked = false;

    for (const field of fields) {
      if (field instanceof HTMLInputElement && field.type === 'radio') {
        radioSeen = true;
        if (field.checked) {
          radioChecked = true;
        }
        continue;
      }

      if (field instanceof HTMLInputElement && field.type === 'checkbox') {
        checkboxSeen = true;
        if (field.checked) {
          checkboxChecked = true;
        }
        continue;
      }

      if (String(field.value || '').trim() !== '') {
        return true;
      }
    }

    if (radioSeen) {
      return radioChecked;
    }

    if (checkboxSeen) {
      return checkboxChecked;
    }

    return false;
  }

  function markAnsweredStates() {
    questionItems.forEach((questionCard) => {
      if (!(questionCard instanceof HTMLElement)) {
        return;
      }

      questionCard.classList.toggle('is-answered', questionHasAnswer(questionCard));
    });
  }

  function focusFirstInput(questionCard) {
    if (!(questionCard instanceof HTMLElement)) {
      return;
    }

    const firstInput = questionCard.querySelector('input, select, textarea');
    if (firstInput instanceof HTMLElement) {
      firstInput.focus({ preventScroll: true });
    }
  }

  function jumpToNextQuestion(currentCard) {
    if (!(currentCard instanceof HTMLElement) || smoothTransitionLock) {
      return;
    }

    const currentIndex = questionItems.indexOf(currentCard);
    if (currentIndex < 0 || currentIndex >= questionItems.length - 1) {
      return;
    }

    const nextUnansweredIndex = questionItems.findIndex((card, index) => {
      return index > currentIndex && !questionHasAnswer(card);
    });
    const targetIndex = nextUnansweredIndex >= 0 ? nextUnansweredIndex : currentIndex + 1;
    const targetCard = questionItems[targetIndex];

    if (!(targetCard instanceof HTMLElement)) {
      return;
    }

    smoothTransitionLock = true;
    targetCard.classList.add('is-target');
    targetCard.scrollIntoView({ behavior: 'smooth', block: 'center' });

    window.setTimeout(() => {
      focusFirstInput(targetCard);
      targetCard.classList.remove('is-target');
      smoothTransitionLock = false;
    }, 360);
  }

  function maybeAutoAdvance(element) {
    if (!(element instanceof HTMLElement)) {
      return;
    }

    const questionCard = element.closest('[data-question-item="true"]');
    if (!(questionCard instanceof HTMLElement)) {
      return;
    }

    if (questionCard.dataset.autoAdvance !== 'true' || !questionHasAnswer(questionCard)) {
      return;
    }

    jumpToNextQuestion(questionCard);
  }

  form.addEventListener('change', (event) => {
    const target = event.target;
    if (!(target instanceof HTMLInputElement || target instanceof HTMLTextAreaElement || target instanceof HTMLSelectElement)) {
      return;
    }

    if (!target.name.startsWith('answers[')) {
      return;
    }

    saveState(false);
    markAnsweredStates();

    if (!(target instanceof HTMLInputElement && target.type === 'checkbox')) {
      maybeAutoAdvance(target);
    }
  });

  form.addEventListener('input', (event) => {
    const target = event.target;
    if (!(target instanceof HTMLInputElement || target instanceof HTMLTextAreaElement || target instanceof HTMLSelectElement)) {
      return;
    }

    if (!target.name.startsWith('answers[')) {
      return;
    }

    saveState(false);
    markAnsweredStates();
  });

  window.addEventListener('beforeunload', () => {
    saveState(false);
  });

  document.querySelectorAll('[data-process-complete="true"]').forEach((element) => {
    element.addEventListener('click', () => {
      saveState(true);
    });
  });

  restoreState();
  markAnsweredStates();
})();
