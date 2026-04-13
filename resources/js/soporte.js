document.addEventListener('DOMContentLoaded', function () {
    const btnEnviar = document.getElementById('btnEnviarSoporte');
    const form = document.getElementById('studentSupportForm');
    const message = document.getElementById('supportMessage');

    if (!btnEnviar || !form || !message) return;

    btnEnviar.addEventListener('click', function () {
        const asunto = document.getElementById('supportAsunto').value.trim();
        const tipo = document.getElementById('supportTipo').value.trim();
        const prioridad = document.getElementById('supportPrioridad').value.trim();
        const modulo = document.getElementById('supportModulo').value.trim();
        const descripcion = document.getElementById('supportDescripcion').value.trim();

        if (!asunto || !tipo || !prioridad || !modulo || !descripcion) {
            alert('Completa todos los campos antes de enviar la solicitud.');
            return;
        }

        message.classList.add('is-success');
        message.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    });

    form.addEventListener('reset', function () {
        setTimeout(() => {
            message.classList.remove('is-success');
        }, 50);
    });
});