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