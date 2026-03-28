(function () {
  'use strict';

  var visual = document.querySelector('[data-norm-viz="true"]');
  if (!visual) {
    return;
  }

  var marker = visual.querySelector('.result-marker');
  var scaleMarker = visual.querySelector('.result-scale-marker');
  var hasNorm = visual.dataset.hasNorm === '1';
  var markerPercent = Number.parseFloat(visual.dataset.markerPercent || '50');

  if (!Number.isFinite(markerPercent)) {
    markerPercent = 50;
  }

  markerPercent = Math.max(0, Math.min(100, markerPercent));

  if (marker) {
    marker.style.left = markerPercent + '%';
  }

  if (scaleMarker) {
    scaleMarker.style.left = markerPercent + '%';
  }

  if (hasNorm) {
    visual.classList.add('result-visual--has-norm');
  } else {
    visual.classList.add('result-visual--no-norm');
  }
})();
