document.getElementById('enviarBtn').addEventListener('click', fetchData);
document.getElementById('historialBtn').addEventListener('click', mostrarHistorial);

async function fetchData() {
    const message = document.getElementById('input').value.trim();
    if(message) {
        const responseBox = document.getElementById('response');
        responseBox.innerHTML = 'Cargando...';
        await new Promise(resolve => setTimeout(resolve, 1500)); // Simula un retraso de 2 segundos
        const response = await fetch('ia.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ message: message })
        });
        const data = await response.json();
        responseBox.innerHTML = data['response'];
    }
}

async function mostrarHistorial() {
    const historialBox = document.getElementById('historial');
    historialBox.innerHTML = 'Cargando historial...';

    await new Promise(resolve => setTimeout(resolve, 2000)); // Simula un retraso de 2 segundos

    const response = await fetch('ia.php', { method: 'GET' });
    const data = await response.json();

    historialBox.innerHTML = '';
    data.forEach(entry => {
        // Mensaje del usuario
        const userDiv = document.createElement('div');
        userDiv.classList.add('user-entry');
        userDiv.textContent = `USER: ${entry.user_message}`;
        historialBox.appendChild(userDiv);

        // Respuesta del asistente
        const assistantDiv = document.createElement('div');
        assistantDiv.classList.add('assistant-entry');
        assistantDiv.textContent = `ASSISTANT: ${entry.ai_response}`;
        historialBox.appendChild(assistantDiv);
    });
}