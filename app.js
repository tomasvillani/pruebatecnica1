document.getElementById('enviarBtn').addEventListener('click', () => {
    const message = document.getElementById('input').value.trim();
    generarRespuesta(message);
});
document.getElementById('historialBtn').addEventListener('click', mostrarHistorial);

async function getServices() {
    const res = await fetch(
        "https://1wdnbdbf.api.sanity.io/v2026-05-30/data/query/production?query=*[_type==%22service%22]&perspective=drafts"
    );
    const data = await res.json();
    return data.result;
}

async function generarRespuesta(message) {
    if (!message) return;

    const responseBox = document.getElementById('response');
    responseBox.innerHTML = 'Cargando...';

    await new Promise(resolve => setTimeout(resolve, 1000));

    const response = await fetch('ia.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ message })
    });

    const data = await response.json();
    responseBox.innerHTML = data.response;
    document.getElementById('input').value = '';
}

async function mostrarHistorial() {
    const historialBox = document.getElementById('historial');
    historialBox.innerHTML = 'Cargando historial...';

    await new Promise(resolve => setTimeout(resolve, 1000)); // Simula un retraso de 1 segundo

    const response = await fetch('ia.php', { method: 'GET' });
    const data = await response.json();

    historialBox.innerHTML = '';
    data.forEach(entry => {
        const userDiv = document.createElement('div');
        userDiv.classList.add('user-entry');
        userDiv.textContent = `USER: ${entry.user_message}`;
        historialBox.appendChild(userDiv);

        const assistantDiv = document.createElement('div');
        assistantDiv.classList.add('assistant-entry');
        assistantDiv.textContent = `ASSISTANT: ${entry.ai_response}`;
        historialBox.appendChild(assistantDiv);
    });
}