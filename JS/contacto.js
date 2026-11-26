document.addEventListener('DOMContentLoaded', () => {
    const formContacto = document.getElementById('form-contacto');
    const btnTexto = document.getElementById('btn_contacto');
    const btnSubmit = document.getElementById('acceso');
    const mensajeEl = document.getElementById('mensaje-contacto');

    if (!formContacto) return;

    formContacto.addEventListener('submit', async (e) => {
        e.preventDefault();

        const formData = new FormData(formContacto);

        btnSubmit.disabled = true;
        btnTexto.textContent = 'Enviando...';
        mensajeEl.textContent = '';

        try {
            const respuesta = await fetch(formContacto.action, {
                method: 'POST',
                body: formData
            });

            const resultado = await respuesta.json();

            mensajeEl.textContent = resultado.mensaje;
            mensajeEl.style.color = resultado.exito ? '#16C79A' : '#d9534f';

            if (resultado.exito) {
                formContacto.reset();
            }
        } catch (error) {
            console.error('Error al enviar formulario de contacto:', error);
            mensajeEl.textContent = 'Error de conexión al intentar enviar el mensaje.';
            mensajeEl.style.color = '#d9534f';
        } finally {
            btnSubmit.disabled = false;
            btnTexto.textContent = 'Contactar';
        }
    });
});