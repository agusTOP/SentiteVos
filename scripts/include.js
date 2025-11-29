// Incluye nav.html y footer.html en los divs correspondientes
function includeHTML(id, file, callback) {
  fetch(file)
    .then(res => res.text())
    .then(html => {
      const el = document.getElementById(id);
      if (!el) return;
      el.innerHTML = html;
      if (typeof callback === 'function') callback();
    });
}

function enhanceAuthButtons() {
  fetch('php/session_status.php', { cache: 'no-store' })
    .then(r => r.json())
    .then(data => {
      const container = document.getElementById('auth-buttons');
      if (!container) return;
      if (data.logged_in) {
        const nombreCorto = data.nombre ? data.nombre.split(' ')[0] : 'Usuario';
        container.innerHTML = `
          <a href="perfil.php" class="btn btn-login">${nombreCorto}</a>
          <a href="php/logout.php" class="btn btn-register">Salir</a>
        `;
      }
    })
    .catch(() => { });
}

document.addEventListener('DOMContentLoaded', function () {
  if (document.getElementById('nav-include')) {
    includeHTML('nav-include', 'nav.html', enhanceAuthButtons);
  }
  if (document.getElementById('footer-include')) includeHTML('footer-include', 'footer.html');
});
