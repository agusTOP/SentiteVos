console.log('Bienvenida a Sentite Vos - Página de Inicio');

document.addEventListener('DOMContentLoaded', function() {
  document.querySelectorAll('.ver-mas').forEach(btn => {
    btn.addEventListener('click', function() {
      const card = this.closest('.servicio-card');
      document.querySelectorAll('.servicio-card').forEach(c => c.classList.remove('expanded'));
      card.classList.add('expanded');
    });
  });
  document.querySelectorAll('.cerrar-detalle').forEach(btn => {
    btn.addEventListener('click', function() {
      const card = this.closest('.servicio-card');
      card.classList.remove('expanded');
    });
  });
});

// Manejo del formulario de contacto (contacto.html)
document.addEventListener('DOMContentLoaded', function () {
  const form = document.getElementById('form-contacto');
  if (!form) return;
  const status = form.querySelector('.contacto-status');

  form.addEventListener('submit', async function (e) {
    e.preventDefault();
    status.textContent = '';
    const btn = form.querySelector('button[type="submit"]');
    btn.disabled = true;
    btn.textContent = 'Enviando...';

    try {
      const fd = new FormData(form);
      const res = await fetch('php/contacto_enviar.php', {
        method: 'POST',
        body: fd,
      });
      const data = await res.json().catch(() => ({ ok: false, error: 'Error inesperado' }));
      if (data.ok) {
        status.textContent = data.message || 'Mensaje enviado.';
        form.reset();
      } else {
        status.textContent = data.error || 'No se pudo enviar.';
      }
    } catch (_) {
      status.textContent = 'Error de red. Intenta nuevamente.';
    } finally {
      btn.disabled = false;
      btn.textContent = 'Enviar';
    }
  });
});