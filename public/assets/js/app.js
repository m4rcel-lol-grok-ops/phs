(function () {
  'use strict';

  // Mobile nav toggle
  const toggle = document.querySelector('.nav-toggle');
  const navLinks = document.querySelector('.nav-links');
  if (toggle && navLinks) {
    toggle.addEventListener('click', () => {
      navLinks.classList.toggle('open');
      toggle.setAttribute('aria-expanded', navLinks.classList.contains('open'));
    });
  }

  // Confirm deletes
  document.querySelectorAll('[data-confirm]').forEach(el => {
    el.addEventListener('click', e => {
      if (!confirm(el.dataset.confirm || 'Are you sure?')) {
        e.preventDefault();
      }
    });
  });

  // Link reorder (simple up/down for accessibility, drag optional)
  const reorderList = document.getElementById('link-list');
  if (reorderList) {
    let dragItem = null;
    reorderList.querySelectorAll('.link-item').forEach(item => {
      item.setAttribute('draggable', 'true');
      item.addEventListener('dragstart', () => { dragItem = item; item.style.opacity = '0.5'; });
      item.addEventListener('dragend', () => { item.style.opacity = '1'; dragItem = null; saveOrder(); });
      item.addEventListener('dragover', e => { e.preventDefault(); });
      item.addEventListener('drop', e => {
        e.preventDefault();
        if (dragItem && dragItem !== item) {
          const items = [...reorderList.querySelectorAll('.link-item')];
          const from = items.indexOf(dragItem);
          const to = items.indexOf(item);
          if (from < to) item.after(dragItem);
          else item.before(dragItem);
        }
      });
    });

    function saveOrder() {
      const ids = [...reorderList.querySelectorAll('.link-item')].map(i => i.dataset.id);
      const token = document.querySelector('meta[name="csrf-token"]')?.content;
      const formData = new FormData();
      formData.append('_csrf', token);
      formData.append('order', JSON.stringify(ids));
      fetch('/dashboard/links/reorder', { method: 'POST', body: formData, credentials: 'same-origin' })
        .catch(() => {});
    }
  }

  // Music player
  const audio = document.getElementById('profile-audio');
  const playBtn = document.getElementById('music-play');
  if (audio && playBtn) {
    playBtn.addEventListener('click', () => {
      if (audio.paused) {
        audio.play();
        playBtn.textContent = '⏸';
        playBtn.setAttribute('aria-label', 'Pause');
      } else {
        audio.pause();
        playBtn.textContent = '▶';
        playBtn.setAttribute('aria-label', 'Play');
      }
    });
    audio.addEventListener('ended', () => {
      playBtn.textContent = '▶';
      playBtn.setAttribute('aria-label', 'Play');
    });
  }

  // Report modal
  const reportBtn = document.getElementById('report-btn');
  const reportModal = document.getElementById('report-modal');
  const reportClose = document.getElementById('report-close');
  if (reportBtn && reportModal) {
    reportBtn.addEventListener('click', () => { reportModal.hidden = false; });
    reportClose?.addEventListener('click', () => { reportModal.hidden = true; });
    reportModal.addEventListener('click', e => {
      if (e.target === reportModal) reportModal.hidden = true;
    });
  }

  // Auto-dismiss alerts
  document.querySelectorAll('.alert').forEach(alert => {
    setTimeout(() => {
      alert.style.transition = 'opacity 0.4s';
      alert.style.opacity = '0';
      setTimeout(() => alert.remove(), 400);
    }, 5000);
  });
})();
