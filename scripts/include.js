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
        const adminLink = data.rol === 'admin' ? '<a href="admin/dashboard.php" class="btn btn-admin">Panel Admin</a>' : '';
        container.innerHTML = `
          ${adminLink}
          <a href="perfil.php" class="btn btn-login">${nombreCorto}</a>
          <a href="php/logout.php" class="btn btn-register">Salir</a>
        `;
      }
    })
    .catch(() => { });
}

function setActiveNav() {
  const links = document.querySelectorAll('.navbar-nav .nav-link');
  if (!links.length) return;

  const normalize = (path) => {
    try {
      // Quita index.html para tratar la home como "/"
      return path.replace(/\/index\.html$/, '/');
    } catch (_) {
      return path;
    }
  };

  const current = normalize(window.location.pathname);

  links.forEach(link => {
    link.classList.remove('active');
    try {
      const href = link.getAttribute('href');
      const linkPath = normalize(new URL(href, window.location.href).pathname);
      if (linkPath === current) {
        link.classList.add('active');
      }
    } catch (_) {
      // Ignorar URLs mal formadas
    }
  });
}

document.addEventListener('DOMContentLoaded', function () {
  // En páginas HTML puras, incluir parciales vía JS
  if (document.getElementById('nav-include')) {
    includeHTML('nav-include', 'includes/nav.php', function () {
      enhanceAuthButtons();
      setActiveNav();
    });
  } else {
    // En páginas PHP, el nav ya está en el DOM
    enhanceAuthButtons();
    setActiveNav();
  }

  if (document.getElementById('footer-include')) {
    includeHTML('footer-include', 'includes/footer.html');
  }
});
