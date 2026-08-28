(function() {
  'use strict';
  
  // Report Modal
  const reportTrigger = document.getElementById('report-trigger');
  const reportModal = document.getElementById('report-modal');
  const reportCancel = document.getElementById('report-cancel');
  
  if (reportTrigger && reportModal) {
    function openModal() {
      reportModal.removeAttribute('hidden');
      const firstInput = reportModal.querySelector('select, input, textarea');
      if (firstInput) firstInput.focus();
    }
    function closeModal() {
      reportModal.setAttribute('hidden', 'true');
      reportTrigger.focus();
    }
    
    reportTrigger.addEventListener('click', openModal);
    if(reportCancel) reportCancel.addEventListener('click', closeModal);
    
    reportModal.addEventListener('click', (e) => {
      if (e.target === reportModal) closeModal();
    });
    
    document.addEventListener('keydown', (e) => {
      if (!reportModal.hasAttribute('hidden')) {
        if (e.key === 'Escape') {
          closeModal();
        } else if (e.key === 'Tab') {
          const focusable = reportModal.querySelectorAll('a[href], button, textarea, input, select');
          const first = focusable[0];
          const last = focusable[focusable.length - 1];
          if (e.shiftKey) {
            if (document.activeElement === first) {
              last.focus();
              e.preventDefault();
            }
          } else {
            if (document.activeElement === last) {
              first.focus();
              e.preventDefault();
            }
          }
        }
      }
    });
  }

  // Effects (if enabled and prefers-reduced-motion is false)
  const effectsLayer = document.getElementById('effects-layer');
  const prefersReduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  if (effectsLayer && !prefersReduced) {
    const effectType = effectsLayer.getAttribute('data-effect');
    if (effectType === 'particles' || effectType === 'snow') {
      const count = effectType === 'snow' ? 50 : 30;
      for (let i = 0; i < count; i++) {
        const p = document.createElement('div');
        p.style.position = 'absolute';
        p.style.background = effectType === 'snow' ? '#fff' : 'var(--p-accent, #ff9900)';
        p.style.borderRadius = '50%';
        const size = Math.random() * 4 + 2;
        p.style.width = `${size}px`;
        p.style.height = `${size}px`;
        p.style.left = `${Math.random() * 100}vw`;
        p.style.top = `${Math.random() * 100}vh`;
        p.style.opacity = Math.random() * 0.5 + 0.2;
        
        if (typeof p.animate === 'function') {
          p.animate([
            { transform: `translate(0, 0)` },
            { transform: `translate(${Math.random()*100 - 50}px, ${effectType==='snow' ? '100vh' : Math.random()*100 - 50 + 'px'})` }
          ], {
            duration: Math.random() * 3000 + 3000,
            iterations: Infinity,
            direction: effectType === 'particles' ? 'alternate' : 'normal',
            easing: 'linear'
          });
        }
        
        effectsLayer.appendChild(p);
      }
    } else if (effectType === 'scanlines') {
      effectsLayer.style.background = 'linear-gradient(rgba(18, 16, 16, 0) 50%, rgba(0, 0, 0, 0.25) 50%), linear-gradient(90deg, rgba(255, 0, 0, 0.06), rgba(0, 255, 0, 0.02), rgba(0, 0, 255, 0.06))';
      effectsLayer.style.backgroundSize = '100% 2px, 3px 100%';
    } else if (effectType === 'glow') {
      effectsLayer.style.boxShadow = 'inset 0 0 100px var(--p-accent, #ff9900)';
      effectsLayer.style.opacity = '0.2';
    } else if (effectType === 'gradient') {
      effectsLayer.style.background = 'linear-gradient(45deg, var(--p-bg, #0b0a09), var(--p-accent, #ff9900))';
      effectsLayer.style.opacity = '0.1';
    } else if (effectType === 'crt') {
      effectsLayer.style.background = 'radial-gradient(circle, rgba(0,0,0,0) 60%, rgba(0,0,0,0.5) 100%)';
      effectsLayer.style.boxShadow = 'inset 0 0 50px rgba(255, 255, 255, 0.1)';
    }
  }
})();
