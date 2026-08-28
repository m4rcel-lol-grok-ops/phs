/* Public profile page: music player, report modal, optional particle effects. */
(function () {
  'use strict';

  var reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  /* ------------------------------------------------------------- music */

  var audio = document.getElementById('profile-audio');
  var playBtn = document.getElementById('music-play');

  if (audio && playBtn) {
    var setPlaying = function (playing) {
      playBtn.textContent = playing ? '❚❚' : '▶';
      playBtn.setAttribute('aria-label', playing ? 'Pause track' : 'Play track');
    };

    playBtn.addEventListener('click', function () {
      if (audio.paused) {
        // play() rejects on autoplay policy or a dead URL; surface it on the
        // button instead of failing silently.
        var attempt = audio.play();
        if (attempt && typeof attempt.catch === 'function') {
          attempt.catch(function () {
            playBtn.textContent = '⚠';
            playBtn.setAttribute('aria-label', 'Track could not be played');
          });
        }
      } else {
        audio.pause();
      }
    });

    audio.addEventListener('play', function () { setPlaying(true); });
    audio.addEventListener('pause', function () { setPlaying(false); });
    audio.addEventListener('ended', function () { setPlaying(false); });
    audio.addEventListener('error', function () {
      playBtn.textContent = '⚠';
      playBtn.setAttribute('aria-label', 'Track could not be loaded');
    });
  }

  /* ------------------------------------------------------- report modal */

  var openBtn = document.getElementById('report-btn');
  var modal = document.getElementById('report-modal');

  if (openBtn && modal) {
    var closeBtn = document.getElementById('report-close');
    var reason = document.getElementById('report-reason');
    var lastFocused = null;

    var open = function () {
      lastFocused = document.activeElement;
      modal.hidden = false;
      document.body.style.overflow = 'hidden';
      if (reason) reason.focus();
    };

    var close = function () {
      modal.hidden = true;
      document.body.style.overflow = '';
      if (lastFocused && typeof lastFocused.focus === 'function') lastFocused.focus();
    };

    openBtn.addEventListener('click', open);
    if (closeBtn) closeBtn.addEventListener('click', close);

    // Click on the backdrop only — not on the card itself.
    modal.addEventListener('mousedown', function (e) {
      if (e.target === modal) close();
    });

    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape' && !modal.hidden) close();
    });

    // Keep focus inside the dialog while it is open.
    modal.addEventListener('keydown', function (e) {
      if (e.key !== 'Tab') return;
      var focusable = modal.querySelectorAll('select, textarea, button, [href], input:not([type="hidden"])');
      if (!focusable.length) return;
      var first = focusable[0];
      var last = focusable[focusable.length - 1];
      if (e.shiftKey && document.activeElement === first) {
        e.preventDefault();
        last.focus();
      } else if (!e.shiftKey && document.activeElement === last) {
        e.preventDefault();
        first.focus();
      }
    });
  }

  /* ------------------------------------------------ particle / snow effect */

  var layer = document.querySelector('.fx-layer[data-fx]');
  if (layer && !reduceMotion) {
    var kind = layer.dataset.fx;
    var count = kind === 'snow' ? 40 : 28;
    var fragment = document.createDocumentFragment();

    for (var i = 0; i < count; i++) {
      var dot = document.createElement('span');
      var size = kind === 'snow' ? rand(2, 6) : rand(2, 4);
      dot.className = 'fx-dot';
      dot.style.width = size + 'px';
      dot.style.height = size + 'px';
      dot.style.left = rand(0, 100) + '%';
      dot.style.animationDuration = rand(kind === 'snow' ? 9 : 6, kind === 'snow' ? 20 : 14) + 's';
      // Negative delay starts each dot mid-flight, so the screen is populated
      // immediately instead of filling in from the top.
      dot.style.animationDelay = '-' + rand(0, 18) + 's';
      dot.style.setProperty('--fx-drift', rand(-60, 60) + 'px');
      dot.style.setProperty('--fx-opacity', (rand(30, 75) / 100).toString());
      fragment.appendChild(dot);
    }
    layer.appendChild(fragment);
  }

  function rand(min, max) {
    return Math.floor(Math.random() * (max - min + 1)) + min;
  }
})();
