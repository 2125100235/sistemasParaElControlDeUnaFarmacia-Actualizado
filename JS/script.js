document.addEventListener('DOMContentLoaded', () => {
    const formulario = document.getElementById('formAcceso');
    const botonAcceso = document.getElementById('acceso');
    const mensajeError = document.getElementById('mensajeError');

    function validarYRedirigir(event) {
        // Detener recarga de la página (tanto del <a> como del <form>)
        if (event) event.preventDefault();

        const correo = document.getElementById('correo').value.trim().toLowerCase();
        const nombre = document.getElementById('nombre').value.trim();
        const contrasena = document.getElementById('contrasena').value.trim();

        // Limpiar mensajes previos
        mensajeError.textContent = '';

        // 1. Validar campos vacíos
        if (correo === '' || nombre === '' || contrasena === '') {
            mensajeError.textContent = 'Por favor, completa todos los campos.';
            return;
        }

        // 2. Validar contraseña mínima
        if (contrasena.length < 6) {
            mensajeError.textContent = 'La contraseña debe tener al menos 6 caracteres.';
            return;
        }

        // 3. Validar terminación del correo y redirigir
        if (correo.endsWith('.adm')) {
            window.location.href = 'Perfil_Administrador.html';
        } else if (correo.endsWith('.cajero')) {
            window.location.href = 'Venta.html';
        } else if (correo.endsWith('.alm')) {
            window.location.href = 'Productos_Pantalla_Almacenista.html';
        } else {
            mensajeError.textContent = 'El correo no pertenece a ningún rol válido (.adm, .cajero, .alm).';
        }
    }

    // Escuchar el clic en el enlace <a>
    if (botonAcceso) {
        botonAcceso.addEventListener('click', validarYRedirigir);
    }

    // Escuchar si presiona la tecla Enter dentro de algún input del formulario
    if (formulario) {
        formulario.addEventListener('submit', validarYRedirigir);
    }
});