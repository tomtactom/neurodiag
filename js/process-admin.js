(function () {
  'use strict';

  const root = document.querySelector('[data-process-admin="true"]');
  if (!root) {
    return;
  }

  const processId = (root.dataset.processId || '').trim().toLowerCase();
  const csrfToken = (root.dataset.csrfToken || '').trim();
  const endpoint = (root.dataset.endpoint || 'admin/process-files.php').trim();
  const uploadForm = root.querySelector('[data-admin-upload-form="true"]');
  const fileInput = root.querySelector('input[type="file"][name="file"]');
  const unitInput = root.querySelector('input[name="unit_id"]');
  const dropzone = root.querySelector('[data-admin-dropzone="true"]');
  const sortableList = root.querySelector('[data-admin-sortable="true"]');
  const statusEl = root.querySelector('[data-admin-status="true"]');
  const saveOrderBtn = root.querySelector('[data-admin-save-order="true"]');

  if (!processId || !csrfToken || !uploadForm || !sortableList || !statusEl || !saveOrderBtn) {
    return;
  }

  let draggedItem = null;

  function setStatus(message, type) {
    statusEl.textContent = message;
    statusEl.classList.remove('is-success', 'is-error', 'is-pending');
    statusEl.classList.add(type || 'is-pending');
  }

  async function postAction(formData) {
    const response = await fetch(endpoint, {
      method: 'POST',
      credentials: 'same-origin',
      headers: {
        'X-CSRF-Token': csrfToken
      },
      body: formData
    });

    const payload = await response.json().catch(() => ({ ok: false, error: 'Ungültige Serverantwort.' }));
    if (!response.ok || !payload.ok) {
      throw new Error(payload.error || 'Aktion fehlgeschlagen.');
    }

    return payload;
  }

  function currentOrder() {
    return Array.from(sortableList.querySelectorAll('li[data-unit-id]'))
      .map((item) => (item.dataset.unitId || '').trim().toLowerCase())
      .filter(Boolean);
  }

  function attachDragHandlers(item) {
    if (!(item instanceof HTMLElement)) {
      return;
    }

    item.addEventListener('dragstart', () => {
      draggedItem = item;
      item.classList.add('is-dragging');
    });

    item.addEventListener('dragend', () => {
      item.classList.remove('is-dragging');
      draggedItem = null;
    });

    item.addEventListener('dragover', (event) => {
      event.preventDefault();
      if (!draggedItem || draggedItem === item) {
        return;
      }

      const rect = item.getBoundingClientRect();
      const before = event.clientY < rect.top + rect.height / 2;
      const parent = item.parentElement;
      if (!parent) {
        return;
      }

      if (before) {
        parent.insertBefore(draggedItem, item);
      } else {
        parent.insertBefore(draggedItem, item.nextSibling);
      }
    });
  }

  async function saveOrder() {
    const orderedUnits = currentOrder();
    if (!orderedUnits.length) {
      setStatus('Keine Units zum Speichern vorhanden.', 'is-error');
      return;
    }

    const data = new FormData();
    data.append('action', 'reorder');
    data.append('process', processId);
    data.append('ordered_units', JSON.stringify(orderedUnits));

    setStatus('Speichere Reihenfolge …', 'is-pending');
    try {
      await postAction(data);
      setStatus('Reihenfolge wurde gespeichert.', 'is-success');
    } catch (error) {
      setStatus(error instanceof Error ? error.message : 'Reihenfolge konnte nicht gespeichert werden.', 'is-error');
    }
  }

  function removeUnitFromList(unitId) {
    const entry = sortableList.querySelector(`li[data-unit-id="${CSS.escape(unitId)}"]`);
    if (entry instanceof HTMLElement) {
      entry.remove();
    }
  }

  async function deleteUnit(unitId) {
    if (!unitId) {
      return;
    }

    const confirmed = window.confirm(`Unit "${unitId}" wirklich löschen?`);
    if (!confirmed) {
      return;
    }

    const data = new FormData();
    data.append('action', 'delete');
    data.append('process', processId);
    data.append('unit_id', unitId);

    setStatus(`Lösche ${unitId} …`, 'is-pending');
    try {
      await postAction(data);
      removeUnitFromList(unitId);
      setStatus(`Unit ${unitId} wurde gelöscht.`, 'is-success');
    } catch (error) {
      setStatus(error instanceof Error ? error.message : 'Unit konnte nicht gelöscht werden.', 'is-error');
    }
  }

  function bindDeleteButtons() {
    sortableList.querySelectorAll('[data-admin-delete]').forEach((button) => {
      button.addEventListener('click', () => {
        const unitId = (button.getAttribute('data-admin-delete') || '').trim().toLowerCase();
        deleteUnit(unitId);
      });
    });
  }

  function createListItem(unitId) {
    const li = document.createElement('li');
    li.draggable = true;
    li.dataset.unitId = unitId;
    li.innerHTML = `<span class="process-admin-drag-handle" aria-hidden="true">↕</span><span><strong>${unitId}</strong> · ${unitId}</span><button type="button" data-admin-delete="${unitId}">Löschen</button>`;
    attachDragHandlers(li);
    return li;
  }

  async function uploadFile(file) {
    if (!file || !(file instanceof File)) {
      setStatus('Bitte eine Datei auswählen.', 'is-error');
      return;
    }

    const unitId = (unitInput instanceof HTMLInputElement ? unitInput.value : '').trim().toLowerCase();
    if (!unitId) {
      setStatus('Bitte eine Unit-ID eingeben.', 'is-error');
      return;
    }

    const data = new FormData();
    data.append('action', 'upload');
    data.append('process', processId);
    data.append('unit_id', unitId);
    data.append('file', file, file.name || `${unitId}.json`);

    setStatus(`Lade ${file.name} hoch …`, 'is-pending');
    try {
      await postAction(data);
      if (!sortableList.querySelector(`li[data-unit-id="${CSS.escape(unitId)}"]`)) {
        sortableList.appendChild(createListItem(unitId));
        bindDeleteButtons();
      }
      setStatus(`Unit ${unitId} wurde hochgeladen.`, 'is-success');
      uploadForm.reset();
    } catch (error) {
      setStatus(error instanceof Error ? error.message : 'Upload fehlgeschlagen.', 'is-error');
    }
  }

  Array.from(sortableList.querySelectorAll('li[data-unit-id]')).forEach(attachDragHandlers);
  bindDeleteButtons();

  saveOrderBtn.addEventListener('click', saveOrder);

  uploadForm.addEventListener('submit', (event) => {
    event.preventDefault();
    const file = fileInput instanceof HTMLInputElement && fileInput.files ? fileInput.files[0] : null;
    uploadFile(file);
  });

  if (dropzone instanceof HTMLElement) {
    const preventDefaults = (event) => {
      event.preventDefault();
      event.stopPropagation();
    };

    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach((evtName) => {
      dropzone.addEventListener(evtName, preventDefaults);
    });

    ['dragenter', 'dragover'].forEach((evtName) => {
      dropzone.addEventListener(evtName, () => dropzone.classList.add('is-active'));
    });

    ['dragleave', 'drop'].forEach((evtName) => {
      dropzone.addEventListener(evtName, () => dropzone.classList.remove('is-active'));
    });

    dropzone.addEventListener('drop', (event) => {
      const files = event.dataTransfer && event.dataTransfer.files ? event.dataTransfer.files : null;
      const firstFile = files && files.length > 0 ? files[0] : null;
      uploadFile(firstFile);
    });
  }
})();
