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
  const stepNavigation = form.querySelector('[data-question-step-navigation="true"]');
  const backButton = stepNavigation ? stepNavigation.querySelector('[data-question-back]') : null;
  const nextButton = stepNavigation ? stepNavigation.querySelector('[data-question-next]') : null;
  const progressLabel = stepNavigation ? stepNavigation.querySelector('[data-question-progress]') : null;
  let currentQuestionIndex = 0;

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

    return Array.from(questionCard.querySelectorAll('input, select, textarea')).filter(function (field) {
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

  function focusCurrentQuestion() {
    const card = questionItems[currentQuestionIndex];
    if (!(card instanceof HTMLElement)) {
      return;
    }

    const firstInput = card.querySelector('input, select, textarea');
    if (firstInput instanceof HTMLElement) {
      firstInput.focus({ preventScroll: true });
      return;
    }

    const legend = card.querySelector('legend');
    if (legend instanceof HTMLElement) {
      legend.setAttribute('tabindex', '-1');
      legend.focus({ preventScroll: true });
    }
  }

  function renderQuestionStep() {
    if (!questionItems.length || !(stepNavigation instanceof HTMLElement)) {
      return;
    }

    questionItems.forEach(function (item, index) {
      if (!(item instanceof HTMLElement)) {
        return;
      }

      item.hidden = index !== currentQuestionIndex;
    });

    if (backButton instanceof HTMLButtonElement) {
      backButton.disabled = currentQuestionIndex === 0;
    }

    if (nextButton instanceof HTMLButtonElement) {
      const isLast = currentQuestionIndex === questionItems.length - 1;
      nextButton.textContent = isLast ? 'Fertig' : 'Weiter →';
      nextButton.disabled = !questionHasAnswer(questionItems[currentQuestionIndex]);
    }

    if (progressLabel instanceof HTMLElement) {
      progressLabel.textContent = `Frage ${currentQuestionIndex + 1} von ${questionItems.length}`;
    }
  }

  function goToQuestion(index, shouldFocus) {
    const boundedIndex = Math.max(0, Math.min(index, questionItems.length - 1));
    currentQuestionIndex = boundedIndex;
    renderQuestionStep();
    if (shouldFocus) {
      focusCurrentQuestion();
    }
  }

  function goToFirstUnanswered() {
    if (!questionItems.length) {
      return;
    }

    const firstUnanswered = questionItems.findIndex(function (questionCard) {
      return !questionHasAnswer(questionCard);
    });

    goToQuestion(firstUnanswered >= 0 ? firstUnanswered : questionItems.length - 1, false);
  }

  form.addEventListener('change', function (event) {
    if (!(event.target instanceof HTMLInputElement)) {
      if (event.target instanceof HTMLSelectElement || event.target instanceof HTMLTextAreaElement) {
        if (event.target.name.startsWith('answers[')) {
          saveState(false);
          renderQuestionStep();
        }
      }
      return;
    }

    if (event.target.name.startsWith('answers[')) {
      saveState(false);
      renderQuestionStep();

      if (event.target.type === 'radio') {
        const activeQuestionCard = questionItems[currentQuestionIndex];
        if (activeQuestionCard && activeQuestionCard.contains(event.target) && currentQuestionIndex < questionItems.length - 1) {
          window.setTimeout(function () {
            goToQuestion(currentQuestionIndex + 1, true);
          }, 120);
        }
      }
    }
  });

  form.addEventListener('input', function (event) {
    if (!(event.target instanceof HTMLInputElement || event.target instanceof HTMLTextAreaElement || event.target instanceof HTMLSelectElement)) {
      return;
    }

    if (event.target.name.startsWith('answers[')) {
      saveState(false);
      renderQuestionStep();
    }
  });

  if (backButton instanceof HTMLButtonElement) {
    backButton.addEventListener('click', function () {
      goToQuestion(currentQuestionIndex - 1, true);
    });
  }

  if (nextButton instanceof HTMLButtonElement) {
    nextButton.addEventListener('click', function () {
      if (currentQuestionIndex >= questionItems.length - 1) {
        const nextUnitLink = document.querySelector('.process-navigation a:last-child');
        if (nextUnitLink instanceof HTMLAnchorElement) {
          nextUnitLink.focus();
          nextUnitLink.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
        return;
      }
      goToQuestion(currentQuestionIndex + 1, true);
    });
  }

  window.addEventListener('beforeunload', function () {
    saveState(false);
  });

  document.querySelectorAll('[data-process-complete="true"]').forEach((element) => {
    element.addEventListener('click', function () {
      saveState(true);
    });
  });

  restoreState();
  goToFirstUnanswered();
  renderQuestionStep();
})();
