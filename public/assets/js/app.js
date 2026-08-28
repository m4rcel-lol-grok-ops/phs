/* Site chrome: navigation, confirmations, link reordering, alert dismissal. */
(function () {
  'use strict';

  var reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  /* ------------------------------------------------------------ mobile nav */

  var toggle = document.querySelector('.nav-toggle');
  var nav = document.getElementById('primary-nav');

  if (toggle && nav) {
    var setNav = function (open) {
      nav.classList.toggle('is-open', open);
      toggle.setAttribute('aria-expanded', String(open));
      toggle.setAttribute('aria-label', open ? 'Close menu' : 'Open menu');
      document.body.classList.toggle('nav-open', open);
    };

    toggle.addEventListener('click', function () {
      setNav(!nav.classList.contains('is-open'));
    });

    // Escape closes the menu and returns focus to the button that opened it.
    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape' && nav.classList.contains('is-open')) {
        setNav(false);
        toggle.focus();
      }
    });

    document.addEventListener('click', function (e) {
      if (nav.classList.contains('is-open') && !nav.contains(e.target) && !toggle.contains(e.target)) {
        setNav(false);
      }
    });

    // The menu is only a mobile affordance; resizing past the breakpoint while
    // it is open would otherwise leave body scroll locked.
    window.addEventListener('resize', function () {
      if (window.innerWidth > 820 && nav.classList.contains('is-open')) {
        setNav(false);
      }
    });
  }

  /* ---------------------------------------------------- confirm before act */

  document.querySelectorAll('[data-confirm]').forEach(function (el) {
    var handler = function (e) {
      if (!window.confirm(el.dataset.confirm || 'Are you sure?')) {
        e.preventDefault();
        e.stopPropagation();
      }
    };
    // Bind to submit on forms and click on buttons/links, so a confirmation
    // cannot be bypassed by pressing Enter inside a field.
    if (el.tagName === 'FORM') {
      el.addEventListener('submit', handler);
    } else {
      el.addEventListener('click', handler);
    }
  });

  /* --------------------------------------------------- copy-to-clipboard */

  document.querySelectorAll('[data-copy]').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var text = btn.dataset.copy;
      var done = function () {
        var original = btn.textContent;
        btn.textContent = 'Copied';
        btn.disabled = true;
        window.setTimeout(function () {
          btn.textContent = original;
          btn.disabled = false;
        }, 1400);
      };
      if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(text).then(done).catch(function () {});
        return;
      }
      // navigator.clipboard is unavailable over plain http, which is the
      // normal case for a self-hosted instance behind a proxy.
      var field = document.createElement('textarea');
      field.value = text;
      field.setAttribute('readonly', '');
      field.style.position = 'fixed';
      field.style.opacity = '0';
      document.body.appendChild(field);
      field.select();
      try { document.execCommand('copy'); done(); } catch (err) { /* nothing to do */ }
      document.body.removeChild(field);
    });
  });

  /* -------------------------------------------- appearance conditional UI */

  document.querySelectorAll('[data-toggles]').forEach(function (input) {
    var target = document.getElementById(input.dataset.toggles);
    if (!target) return;
    var sync = function () {
      var on = input.type === 'checkbox' ? input.checked : Boolean(input.value);
      target.classList.toggle('is-visible', on);
    };
    input.addEventListener('change', sync);
    sync();
  });

  document.querySelectorAll('[data-shows-when]').forEach(function (group) {
    var config = group.dataset.showsWhen.split(':');
    var source = document.querySelector('[name="' + config[0] + '"]');
    if (!source) return;
    var expected = config[1].split('|');
    var sync = function () {
      group.classList.toggle('is-visible', expected.indexOf(source.value) !== -1);
    };
    source.addEventListener('change', sync);
    sync();
  });

  /* ------------------------------------------------------- link reordering */

  var list = document.getElementById('link-list');
  if (list) {
    var dragged = null;

    var saveOrder = function () {
      var ids = Array.prototype.map.call(
        list.querySelectorAll('.link-item'),
        function (item) { return item.dataset.id; }
      );
      var token = document.querySelector('meta[name="csrf-token"]');
      var body = new FormData();
      body.append('_csrf', token ? token.content : '');
      body.append('order', JSON.stringify(ids));

      fetch('/dashboard/links/reorder', {
        method: 'POST',
        body: body,
        credentials: 'same-origin',
        headers: { 'Accept': 'application/json' }
      })
        .then(function (res) { return res.ok ? res.json() : Promise.reject(res); })
        .then(function () { announce('Order saved.'); })
        // The order is only persisted server-side; if that failed, reloading is
        // the honest way to show the user what is actually stored.
        .catch(function () { announce('Could not save the new order. Reloading…'); window.setTimeout(function () { window.location.reload(); }, 1200); });
    };

    var announce = function (message) {
      var region = document.getElementById('reorder-status');
      if (region) region.textContent = message;
    };

    list.querySelectorAll('.link-item').forEach(function (item) {
      var handle = item.querySelector('.link-handle');
      if (!handle) return;

      // Only the handle starts a drag, so text inside the row stays selectable.
      handle.addEventListener('mousedown', function () { item.setAttribute('draggable', 'true'); });
      handle.addEventListener('touchstart', function () { item.setAttribute('draggable', 'true'); }, { passive: true });
      item.addEventListener('mouseup', function () { item.removeAttribute('draggable'); });

      item.addEventListener('dragstart', function (e) {
        dragged = item;
        item.classList.add('is-dragging');
        if (e.dataTransfer) {
          e.dataTransfer.effectAllowed = 'move';
          e.dataTransfer.setData('text/plain', item.dataset.id);
        }
      });

      item.addEventListener('dragend', function () {
        item.classList.remove('is-dragging');
        item.removeAttribute('draggable');
        list.querySelectorAll('.is-drop-target').forEach(function (el) {
          el.classList.remove('is-drop-target');
        });
        if (dragged) { dragged = null; saveOrder(); }
      });

      item.addEventListener('dragover', function (e) {
        e.preventDefault();
        if (dragged && dragged !== item) item.classList.add('is-drop-target');
      });

      item.addEventListener('dragleave', function () { item.classList.remove('is-drop-target'); });

      item.addEventListener('drop', function (e) {
        e.preventDefault();
        item.classList.remove('is-drop-target');
        if (!dragged || dragged === item) return;

        var rows = Array.prototype.slice.call(list.querySelectorAll('.link-item'));
        var from = rows.indexOf(dragged);
        var to = rows.indexOf(item);
        // Each row is followed by its own <details> editor; move the pair so
        // the edit form never detaches from its link.
        var draggedGroup = groupOf(dragged);
        if (from < to) {
          insertAfter(draggedGroup, groupOf(item));
        } else {
          groupOf(item)[0].before.apply(groupOf(item)[0], draggedGroup);
        }
      });
    });

    var groupOf = function (item) {
      var nodes = [item];
      var next = item.nextElementSibling;
      if (next && next.classList.contains('link-editor')) nodes.push(next);
      return nodes;
    };

    var insertAfter = function (nodes, targetGroup) {
      var anchor = targetGroup[targetGroup.length - 1];
      nodes.forEach(function (node) {
        anchor.after(node);
        anchor = node;
      });
    };
  }

  /* --------------------------------------------------------- auto-dismiss */

  document.querySelectorAll('.alert').forEach(function (alert) {
    // Errors stay until the user navigates; they usually need to be read and
    // acted on, unlike a success confirmation.
    if (alert.classList.contains('alert-error')) return;
    window.setTimeout(function () {
      if (reduceMotion) { alert.remove(); return; }
      alert.style.transition = 'opacity 0.4s ease, transform 0.4s ease';
      alert.style.opacity = '0';
      alert.style.transform = 'translateY(-6px)';
      window.setTimeout(function () { alert.remove(); }, 420);
    }, 6000);
  });
})();
