-- Unique constraint to avoid overlapping reservations (one slot per fecha+hora)
ALTER TABLE reservas ADD UNIQUE INDEX uq_reserva_slot (fecha, hora);

-- Helpful indexes
ALTER TABLE reservas ADD INDEX idx_reservas_usuario (usuario_id);
