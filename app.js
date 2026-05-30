document.getElementById("btn").addEventListener("click", send);
document.getElementById("histBtn").addEventListener("click", loadHistory);

async function ai(message) {
  const res = await fetch("ia.php", {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({ message }),
  });
  const data = await res.json();
  return data.reply;
}

async function loadHistory() {
  document.getElementById("history").innerHTML = "Cargando historial...";
  await new Promise(resolve => setTimeout(resolve, 1000)); // Simula un retraso
  const res = await fetch("ia.php?action=history");
  const data = await res.json();
  const history = data.history ?? [];

  const container = document.getElementById("history");
  if (!container) return;

  container.innerHTML = history.map(item => `
    <div class="message user">
      <strong>Tú:</strong> ${item.user_message}
    </div>
    <div class="message ai">
      <strong>Respuesta:</strong> ${item.ai_response}
    </div>
  `).join("");
}

async function send() {
  const msg = document.getElementById("input").value;
  if (!msg) return;

  document.getElementById("response").innerHTML = "Procesando...";

  const response = await ai(msg);
  document.getElementById("response").innerHTML = response;
}