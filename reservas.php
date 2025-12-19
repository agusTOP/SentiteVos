<?php
session_start();
require_once __DIR__ . '/config/helpers.php';
ensureCsrfToken();
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reservar turno - Sentite Vos</title>
    <link rel="stylesheet" href="styles/bootstrap.min.css">
    <script src="scripts/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="styles/styles.css">
    <link rel="icon" href="assets/icon.ico">
</head>

<body>
    <?php include __DIR__ . '/includes/nav.php'; ?>
    <main class="container py-5">
        <div class="row justify-content-center">
            <div class="col-12 col-md-7 col-lg-6">
                <div class="bg-white rounded shadow-sm p-4">
                    <h2 class="mb-4" style="color:#3a7ca5;">Reservar turno</h2>
                    <?php if ($msg = flash_get('error')): ?>
                        <div class="alert alert-danger"><?php echo e($msg); ?></div>
                    <?php endif; ?>
                    <?php if ($msg = flash_get('success')): ?>
                        <div class="alert alert-success"><?php echo e($msg); ?></div>
                    <?php endif; ?>
                    <?php if (empty($_SESSION['usuario_id'])): ?>
                        <div class="alert alert-info">Debes iniciar sesión para reservar. <a href="login.php">Iniciar
                                sesión</a></div>
                    <?php else: ?>
                           <div class="d-flex justify-content-between align-items-center mb-3">
                              <span class="small text-muted">Selecciona fecha y hora disponibles.</span>
                              <a class="btn btn-sm btn-outline-primary" href="perfil.php?tab=reservas">Ver mis reservas</a>
                           </div>
                        <form method="POST" action="php/reservas_crear.php" id="form-reserva">
                            <?php echo csrf_field(); ?>
                            <div class="mb-3">
                                <label class="form-label" for="servicio">Servicio</label>
                                <select class="form-select" id="servicio" name="servicio" required>
                                    <option value="">Selecciona un servicio</option>
                                    <option value="Depilación">Depilación</option>
                                    <option value="Cejas">Cejas</option>
                                    <option value="Pestañas">Pestañas</option>
                                    <option value="Facial">Tratamiento facial</option>
                                </select>
                            </div>
                            <div class="row g-3">
                                <div class="col-6">
                                    <label class="form-label" for="fecha">Fecha</label>
                                    <input type="date" class="form-control" id="fecha" name="fecha" required>
                                </div>
                                <div class="col-6">
                                    <label class="form-label" for="hora">Hora</label>
                                    <select class="form-select" id="hora" name="hora" required>
                                        <option value="">Selecciona la hora</option>
                                    </select>
                                </div>
                            </div>
                            <div class="mb-3 mt-3">
                                <label class="form-label" for="notas">Notas (opcional)</label>
                                <textarea class="form-control" id="notas" name="notas" rows="2" maxlength="300"></textarea>
                            </div>
                            <button class="btn btn-login" type="submit">Reservar</button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>
    <?php include __DIR__ . '/includes/footer.html'; ?>
    <script src="scripts/include.js"></script>
    <script>
        const horaSel = document.getElementById('hora');
        const fechaInput = document.getElementById('fecha');
        const today = new Date().toISOString().split('T')[0];
        if(fechaInput){
            fechaInput.min = today;
        }

        function generarSlots() {
            const slots = [];
            for (let h = 9; h <= 19; h++) { // 09:00 a 19:00 cada 30min
                ['00', '30'].forEach(m => slots.push(`${String(h).padStart(2, '0')}:${m}:00`));
            }
            return slots;
        }

        async function cargarDisponibilidad() {
            const fecha = fechaInput.value;
            if (!fecha) return;
            const res = await fetch(`php/reservas_disponibilidad.php?fecha=${encodeURIComponent(fecha)}`, { cache: 'no-store' });
            const ocupadas = await res.json();
            const todos = generarSlots();
            horaSel.innerHTML = '<option value="">Selecciona la hora</option>' + todos.map(h => {
                const disabled = ocupadas.includes(h) ? 'disabled' : '';
                const label = h.substring(0, 5);
                return `<option value="${h}" ${disabled}>${label}</option>`;
            }).join('');
        }

        fechaInput?.addEventListener('change', cargarDisponibilidad);
    </script>
</body>

</html>