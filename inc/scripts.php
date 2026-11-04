<?php
/**
 * Scripts comunes: modal de confirmación de eliminación (Ej. 2.6) y
 * envío automático del formulario "Ir a página" (Ej. 2.9).
 */
?>
<!-- Modal de confirmación de eliminación (Bootstrap, Ej. 2.6) -->
<div class="modal fade" id="modalConfirmar" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title"><i class="bi bi-exclamation-triangle"></i> Confirmar eliminación</h5>
        <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
      </div>
      <div class="modal-body">
        <p>&iquest;Seguro que desea eliminar <strong id="nombreRegistro"></strong>?</p>
        <p class="text-muted small">Esta operaci&oacute;n no se puede deshacer. No se podr&aacute;n eliminar
           registros que tengan registros relacionados en otras tablas.</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
        <a id="btnConfirmarEliminar" href="#" class="btn btn-danger"><i class="bi bi-trash"></i> S&iacute;, eliminar</a>
      </div>
    </div>
  </div>
</div>
<script>
// Abrir el modal con los datos del registro seleccionado
document.addEventListener('click', function (e) {
    var boton = e.target.closest('.btn-eliminar');
    if (boton) {
        e.preventDefault();
        document.getElementById('nombreRegistro').textContent = boton.dataset.nombre;
        document.getElementById('btnConfirmarEliminar').href  = boton.dataset.url;
        $('#modalConfirmar').modal('show');
    }
});
// Ej. 2.9: saltar automáticamente al enviar "Ir a página"
document.addEventListener('change', function (e) {
    if (e.target.matches('.js-ir-pagina, select.js-autoenvio')) {
        var form = e.target.closest('form');
        if (form) { form.submit(); }
    }
});
</script>
