/* ── Canvas particle background ── */
(function () {
  const c = document.getElementById('bg');
  const ctx = c.getContext('2d');
  let W, H, pts;

  function resize() {
    W = c.width = window.innerWidth;
    H = c.height = window.innerHeight;
  }
  resize();
  window.addEventListener('resize', resize);

  function makePts(n) {
    return Array.from({ length: n }, () => ({
      x: Math.random() * W, y: Math.random() * H,
      vx: (Math.random() - .5) * .25,
      vy: (Math.random() - .5) * .25,
      r: Math.random() * 1.5 + .5,
      o: Math.random() * .5 + .2
    }));
  }
  pts = makePts(90);

  let mx = W / 2, my = H / 2;
  window.addEventListener('mousemove', e => { mx = e.clientX; my = e.clientY });

  function draw() {
    ctx.clearRect(0, 0, W, H);
    // draw connections
    for (let i = 0; i < pts.length; i++) {
      for (let j = i + 1; j < pts.length; j++) {
        const dx = pts[i].x - pts[j].x, dy = pts[i].y - pts[j].y;
        const d = Math.sqrt(dx * dx + dy * dy);
        if (d < 120) {
          ctx.strokeStyle = `rgba(26,107,255,${.08 * (1 - d / 120)})`;
          ctx.lineWidth = .6;
          ctx.beginPath();
          ctx.moveTo(pts[i].x, pts[i].y);
          ctx.lineTo(pts[j].x, pts[j].y);
          ctx.stroke();
        }
      }
    }
    // draw dots
    pts.forEach(p => {
      p.x += p.vx; p.y += p.vy;
      if (p.x < 0 || p.x > W) p.vx *= -1;
      if (p.y < 0 || p.y > H) p.vy *= -1;
      // slight attraction to mouse
      const dx = mx - p.x, dy = my - p.y;
      const d = Math.sqrt(dx * dx + dy * dy);
      if (d < 200) { p.vx += dx / d * .015; p.vy += dy / d * .015 }
      // dampen
      p.vx *= .99; p.vy *= .99;
      ctx.beginPath();
      ctx.arc(p.x, p.y, p.r, 0, Math.PI * 2);
      ctx.fillStyle = `rgba(26,107,255,${p.o})`;
      ctx.fill();
    });
    requestAnimationFrame(draw);
  }
  draw();
})();

/* ── Feature tabs & Auto-Play ── */
const tabs = document.querySelectorAll('.feat-tab');
const panels = document.querySelectorAll('.feat-panel');
const featureSec = document.getElementById('features');
let currentTabIdx = 0;
let autoPlayInterval;

function switchTab(idx) {
  tabs.forEach(t => t.classList.remove('active'));
  panels.forEach(p => p.classList.remove('active'));
  currentTabIdx = idx;
  tabs[idx].classList.add('active');
  const panelId = tabs[idx].dataset.panel;
  document.getElementById(panelId).classList.add('active');
}

function startAutoPlay() {
  stopAutoPlay();
  autoPlayInterval = setInterval(() => {
    switchTab((currentTabIdx + 1) % tabs.length);
  }, 6000);
}

function stopAutoPlay() {
  clearInterval(autoPlayInterval);
}

tabs.forEach((tab, i) => {
  tab.addEventListener('click', () => {
    switchTab(i);
    startAutoPlay();
  });
});

if (featureSec) {
  featureSec.addEventListener('mouseenter', stopAutoPlay);
  featureSec.addEventListener('mouseleave', startAutoPlay);
  startAutoPlay();
}

/* ── 3D Tilt on Feature Cards ── */
document.querySelectorAll('.feat-panel__card').forEach(card => {
  card.addEventListener('mousemove', e => {
    const rect = card.getBoundingClientRect();
    const x = e.clientX - rect.left;
    const y = e.clientY - rect.top;
    const centerX = rect.width / 2;
    const centerY = rect.height / 2;
    const rotateX = ((y - centerY) / centerY) * -4;
    const rotateY = ((x - centerX) / centerX) * 4;
    card.style.transform = `perspective(1200px) rotateX(${rotateX}deg) rotateY(${rotateY}deg) scale3d(1.02, 1.02, 1.02)`;
  });
  card.addEventListener('mouseleave', () => {
    card.style.transform = `perspective(1200px) rotateX(0) rotateY(0) scale3d(1, 1, 1)`;
  });
});

/* ── Scroll reveal ── */
const observer = new IntersectionObserver(entries => {
  entries.forEach(e => { if (e.isIntersecting) e.target.classList.add('visible') });
}, { threshold: .1, rootMargin: '0px 0px -60px 0px' });
document.querySelectorAll('.reveal').forEach(el => observer.observe(el));

/* ── Nav scroll state ── */
window.addEventListener('scroll', () => {
  const nav = document.querySelector('.nav');
  if (window.scrollY > 60) {
    nav.style.background = 'rgba(6,12,26,.95)';
  } else {
    nav.style.background = 'rgba(6,12,26,.7)';
  }
});

/* ── Animated numbers ── */
function animateNum(el, target, suffix = '') {
  let start = 0;
  const dur = 1800;
  const t0 = performance.now();
  function step(now) {
    const p = Math.min((now - t0) / dur, 1);
    const ease = 1 - Math.pow(1 - p, 3);
    el.textContent = Math.round(start + (target - start) * ease) + suffix;
    if (p < 1) requestAnimationFrame(step);
  }
  requestAnimationFrame(step);
}
const numObs = new IntersectionObserver(entries => {
  entries.forEach(e => {
    if (e.isIntersecting) {
      const el = e.target;
      const val = parseInt(el.dataset.val);
      const suffix = el.dataset.suffix || '';
      animateNum(el, val, suffix);
      numObs.unobserve(el);
    }
  });
}, { threshold: .5 });
document.querySelectorAll('[data-val]').forEach(el => numObs.observe(el));

/* ── Go To Top Button ── */
const goTopBtn = document.getElementById('goTopBtn');
if (goTopBtn) {
  window.addEventListener('scroll', () => {
    if (window.scrollY > 400) {
      goTopBtn.classList.add('visible');
    } else {
      goTopBtn.classList.remove('visible');
    }
  });

  goTopBtn.addEventListener('click', () => {
    window.scrollTo({
      top: 0,
      behavior: 'smooth'
    });
  });
}
