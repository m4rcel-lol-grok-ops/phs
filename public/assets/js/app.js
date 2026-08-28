(function() {
  'use strict';

  // Mobile Nav
  const navToggle = document.querySelector('.nav-toggle');
  const navLinks = document.querySelector('.nav-links');
  if (navToggle && navLinks) {
    navToggle.addEventListener('click', () => {
      const isExpanded = navLinks.getAttribute('aria-expanded') === 'true';
      navLinks.setAttribute('aria-expanded', !isExpanded);
      navToggle.setAttribute('aria-expanded', !isExpanded);
    });

    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape' && navLinks.getAttribute('aria-expanded') === 'true') {
        navLinks.setAttribute('aria-expanded', 'false');
        navToggle.setAttribute('aria-expanded', 'false');
        navToggle.focus();
      }
    });

    document.addEventListener('click', (e) => {
      if (!e.target.closest('.site-header') && navLinks.getAttribute('aria-expanded') === 'true') {
        navLinks.setAttribute('aria-expanded', 'false');
        navToggle.setAttribute('aria-expanded', 'false');
      }
    });

    window.addEventListener('resize', () => {
      if (window.innerWidth > 820 && navLinks.getAttribute('aria-expanded') === 'true') {
        navLinks.setAttribute('aria-expanded', 'false');
        navToggle.setAttribute('aria-expanded', 'false');
      }
    });
  }

  // Flash Dismissal
  document.querySelectorAll('.alert-close').forEach(btn => {
    btn.addEventListener('click', (e) => {
      const alert = e.target.closest('.alert');
      if (alert) alert.remove();
    });
  });
  
  document.querySelectorAll('.alert-success, .alert-info').forEach(alert => {
    setTimeout(() => {
      if (alert && alert.parentNode) alert.remove();
    }, 4000);
  });

  // Confirmations
  document.querySelectorAll('form[data-confirm]').forEach(form => {
    form.addEventListener('submit', (e) => {
      if (!window.confirm(form.getAttribute('data-confirm'))) {
        e.preventDefault();
      }
    });
  });

  // Copy to clipboard
  document.querySelectorAll('[data-copy]').forEach(btn => {
    btn.addEventListener('click', () => {
      navigator.clipboard.writeText(btn.getAttribute('data-copy')).then(() => {
        const originalText = btn.innerText;
        btn.innerText = 'Copied!';
        setTimeout(() => btn.innerText = originalText, 2000);
      });
    });
  });

  // Conditional Fields
  const conditionTriggers = document.querySelectorAll('[data-conditions]');
  function evaluateConditions() {
    conditionTriggers.forEach(trigger => {
      const conditions = JSON.parse(trigger.getAttribute('data-conditions') || '{}');
      for (const [targetId, values] of Object.entries(conditions)) {
        const target = document.getElementById(targetId);
        if (target) {
          const match = values.includes(trigger.value) || (trigger.type === 'checkbox' && values.includes(trigger.checked));
          if (match) {
            target.removeAttribute('hidden');
          } else {
            target.setAttribute('hidden', 'true');
          }
        }
      }
    });
  }
  conditionTriggers.forEach(t => t.addEventListener('change', evaluateConditions));
  evaluateConditions();

  // Link Reordering (Basic Drag & Drop)
  const list = document.querySelector('.sortable-list');
  if (list) {
    let draggedItem = null;
    list.addEventListener('dragstart', (e) => {
      if (e.target.classList.contains('link-row') || e.target.closest('.link-drag-handle')) {
        draggedItem = e.target.closest('.link-row');
        draggedItem.style.opacity = 0.5;
        e.dataTransfer.effectAllowed = 'move';
      }
    });
    list.addEventListener('dragend', (e) => {
      if (draggedItem) {
        draggedItem.style.opacity = '';
        saveOrder();
        draggedItem = null;
      }
    });
    list.addEventListener('dragover', (e) => {
      e.preventDefault();
      e.dataTransfer.dropEffect = 'move';
      const afterElement = getDragAfterElement(list, e.clientY);
      const row = draggedItem;
      if (row) {
        const details = row.nextElementSibling && row.nextElementSibling.tagName === 'DETAILS' ? row.nextElementSibling : null;
        if (afterElement == null) {
          list.appendChild(row);
          if (details) list.appendChild(details);
        } else {
          list.insertBefore(row, afterElement);
          if (details) list.insertBefore(details, afterElement);
        }
      }
    });

    function getDragAfterElement(container, y) {
      const draggableElements = [...container.querySelectorAll('.link-row:not(.dragging)')].filter(el => el !== draggedItem);
      return draggableElements.reduce((closest, child) => {
        const box = child.getBoundingClientRect();
        const offset = y - box.top - box.height / 2;
        if (offset < 0 && offset > closest.offset) {
          return { offset: offset, element: child };
        } else {
          return closest;
        }
      }, { offset: Number.NEGATIVE_INFINITY }).element;
    }

    function saveOrder() {
      const order = [...list.querySelectorAll('.link-row')].map(r => r.getAttribute('data-id'));
      const csrfMeta = document.querySelector('meta[name="csrf-token"]');
      if (!csrfMeta) return;
      const csrf = csrfMeta.content;
      
      fetch('/dashboard/links/reorder', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams({ _csrf: csrf, order: JSON.stringify(order) })
      }).then(res => res.json()).then(data => {
        const announcer = document.getElementById('sr-announcer');
        if (announcer) announcer.textContent = 'Links reordered successfully.';
      }).catch(err => console.error(err));
    }
  }
})();
