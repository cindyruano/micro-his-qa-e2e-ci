(() => {
  const state = { scenario: 'happy', step: 1, deployed: false };
  const views = ['dashboard', 'plan', 'run', 'gate', 'deploy'];
  const runList = document.querySelector('#runList');
  const live = document.querySelector('#liveMessage');
  const toast = document.querySelector('#toast');

  function announce(message) {
    live.textContent = message;
    toast.textContent = message;
    toast.hidden = false;
    window.clearTimeout(announce.timer);
    announce.timer = window.setTimeout(() => { toast.hidden = true; }, 3200);
  }

  function renderRuns() {
    const error = state.scenario === 'error';
    runList.innerHTML = `
      <article class="run-card" data-run="8e12">
        <div><div class="run-main"><span class="badge ${error ? 'red' : 'green'}">${error ? 'FAIL' : 'PASS'}</span><strong>8e12••</strong><span>Auth + Quality Gate</span></div><div class="run-meta">commit a1b2c3d · tenant-7f2a•• · hace 2 min</div></div>
        <div class="run-right"><span class="badge ${error ? 'red' : 'green'}">Gate ${error ? 'REJECTED' : 'APPROVED'}</span><div class="run-meta">${error ? 'Hash mismatch' : 'Evidence ready'}</div></div>
      </article>
      <article class="run-card"><div><div class="run-main"><span class="badge blue">RUNNING</span><strong>7ca9••</strong><span>Suite crítica</span></div><div class="run-meta">commit f8e7d6c · tenant-7f2a•• · actualizando</div></div><div class="run-right"><span class="badge blue">PENDING</span></div></article>`;
    document.querySelector('#gateBadge').textContent = error ? 'FAIL' : 'PASS';
    document.querySelector('#gateBadge').className = `badge ${error ? 'red' : 'green'}`;
    document.querySelector('#gateResult').className = `gate-result ${error ? 'error-result' : ''}`;
    document.querySelector('#gateResult .result-mark').textContent = error ? '!' : '✓';
    document.querySelector('#gateResult .result-mark').style.background = error ? 'var(--red)' : 'var(--green)';
    document.querySelector('#gateResult strong').textContent = error ? 'Versión bloqueada' : 'Versión aprobada';
    document.querySelector('#gateMessage').textContent = error ? 'El hash de evidencia no coincide con el artefacto descargado.' : 'Todas las métricas cumplen los umbrales.';
    document.querySelector('#coverage').textContent = error ? '92%' : '92%';
    document.querySelector('#critical').textContent = error ? '1' : '0';
    document.querySelector('#p95').textContent = error ? '1.42s' : '1.42s';
    document.querySelector('#evidenceState').textContent = error ? 'INVALID' : 'READY';
    document.querySelector('#hash').textContent = error ? 'SHA-256 mismatch' : 'SHA-256 9f2a...c81e';
    document.querySelector('#deployButton').textContent = error ? 'Bloqueado por Gate' : 'Aprobar despliegue';
    document.querySelector('#deployButton').disabled = error;
  }

  function showView(name) {
    views.forEach((id) => document.getElementById(id)?.classList.toggle('active-view', id === name));
    document.querySelectorAll('.bottom-nav a').forEach((link) => link.classList.toggle('active', link.getAttribute('href') === `#${name}` || (name === 'run' && link.getAttribute('href') === '#dashboard')));
    if (name === 'run') setProgress(2); else if (name === 'gate') setProgress(3); else if (name === 'deploy') setProgress(4); else if (name === 'plan') setProgress(1); else setProgress(1);
    window.location.hash = name;
  }

  function setProgress(step) {
    document.querySelectorAll('.progress-step').forEach((item) => {
      const number = Number(item.dataset.step);
      item.classList.toggle('active', number === step);
      item.classList.toggle('done', number < step);
    });
  }

  document.querySelectorAll('[data-scenario]').forEach((button) => button.addEventListener('click', () => {
    state.scenario = button.dataset.scenario;
    document.querySelectorAll('[data-scenario]').forEach((item) => item.classList.toggle('active', item === button));
    renderRuns();
    announce(state.scenario === 'happy' ? 'Escenario feliz cargado.' : 'Error crítico cargado: el despliegue quedará bloqueado.');
  }));

  document.querySelector('#planButton').addEventListener('click', () => showView('plan'));
  document.querySelectorAll('[data-back]').forEach((button) => button.addEventListener('click', () => showView('dashboard')));
  document.querySelectorAll('[data-dashboard]').forEach((button) => button.addEventListener('click', () => showView('dashboard')));
  document.querySelector('#planForm').addEventListener('submit', (event) => {
    event.preventDefault();
    if (!event.currentTarget.reportValidity()) return;
    announce('Ejecución creada con Idempotency-Key.');
    showView('run');
  });
  document.querySelector('#advanceButton').addEventListener('click', () => {
    announce('Suites E2E finalizadas; navegando al Quality Gate.');
    showView('gate');
  });
  document.querySelector('#retryButton').addEventListener('click', () => {
    if (state.scenario === 'error') {
      state.scenario = 'happy';
      document.querySelector('[data-scenario="happy"]').click();
      announce('Reintento encolado sin duplicar la ejecución.');
    } else announce('La ejecución ya cumple el Quality Gate.');
  });
  document.querySelector('#deployButton').addEventListener('click', () => {
    if (state.scenario === 'error') return;
    state.deployed = true;
    announce('Despliegue aprobado.');
    showView('deploy');
  });
  runList.addEventListener('click', () => showView('gate'));
  document.querySelectorAll('.bottom-nav a').forEach((link) => link.addEventListener('click', (event) => { event.preventDefault(); showView(link.getAttribute('href').slice(1)); }));

  renderRuns();
  const initial = window.location.hash.slice(1);
  if (views.includes(initial)) showView(initial);
})();
