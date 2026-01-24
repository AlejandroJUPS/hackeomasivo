<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Gamepad Debug</title>
<style>
body {
    background: #000;
    color: #fff;
    font-family: Arial, sans-serif;
    padding: 20px;
}
.container {
    max-width: 800px;
    margin: 0 auto;
}
h1 {
    color: #ffd700;
}
.debug-info {
    background: #1a1a1a;
    padding: 15px;
    border-radius: 4px;
    margin: 10px 0;
    font-family: monospace;
}
.button-pressed {
    background: #e53935;
    padding: 10px;
    margin: 5px 0;
    border-radius: 3px;
}
.axis-info {
    background: #0066cc;
    padding: 10px;
    margin: 5px 0;
    border-radius: 3px;
}
.buttons-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 10px;
    margin-top: 20px;
}
.button-box {
    background: #222;
    padding: 15px;
    border-radius: 4px;
    text-align: center;
    border: 2px solid #333;
}
.button-box.active {
    border-color: #ffd700;
    background: #333;
}
</style>
</head>
<body>

<div class="container">
    <h1>🎮 Gamepad Debug</h1>
    <p>Presiona cada botón de tu gamepad para ver a qué número corresponde</p>
    
    <div class="debug-info">
        <div>Estado: <span id="status">No conectado</span></div>
        <div>Gamepad: <span id="gamepad-name">-</span></div>
    </div>
    
    <h2>Últimos eventos</h2>
    <div id="events" style="max-height: 200px; overflow-y: auto;"></div>
    
    <h2>Botones (16 números: 0-15)</h2>
    <div class="buttons-grid" id="buttons-grid"></div>
    
    <h2>Ejes Analógicos</h2>
    <div id="axes-info" style="background: #1a1a1a; padding: 15px; border-radius: 4px;"></div>
</div>

<script src="data/src/gamepad.js"></script>
<script>
const eventsDiv = document.getElementById('events');
const statusDiv = document.getElementById('status');
const nameDiv = document.getElementById('gamepad-name');
const buttonsGrid = document.getElementById('buttons-grid');
const axesDiv = document.getElementById('axes-info');

// Crear grid de botones
for (let i = 0; i < 16; i++) {
    const box = document.createElement('div');
    box.className = 'button-box';
    box.innerHTML = `<strong>BTN ${i}</strong><br><small id="btn-${i}"></small>`;
    box.id = `btn-box-${i}`;
    buttonsGrid.appendChild(box);
}

try {
    const handler = new GamepadHandler();
    
    handler.on('connected', (e) => {
        statusDiv.textContent = 'Conectado ✓';
        nameDiv.textContent = `Gamepad ${e.gamepadIndex}`;
    });
    
    handler.on('disconnected', (e) => {
        statusDiv.textContent = 'Desconectado';
    });
    
    handler.on('buttondown', (e) => {
        console.log(`BOTÓN PRESIONADO - Índice: ${e.index}, Label: ${e.label}`);
        
        // Mostrar en grid
        const box = document.getElementById(`btn-box-${e.index}`);
        if (box) {
            box.classList.add('active');
            document.getElementById(`btn-${e.index}`).textContent = e.label;
        }
        
        // Mostrar en eventos
        const event = document.createElement('div');
        event.className = 'button-pressed';
        event.textContent = `🔴 PRESIONADO: Botón #${e.index} (${e.label})`;
        eventsDiv.insertBefore(event, eventsDiv.firstChild);
        if (eventsDiv.children.length > 10) eventsDiv.removeChild(eventsDiv.lastChild);
    });
    
    handler.on('buttonup', (e) => {
        const box = document.getElementById(`btn-box-${e.index}`);
        if (box) {
            box.classList.remove('active');
        }
    });
    
    handler.on('axischanged', (e) => {
        console.log(`EJE CAMBIÓ - ${e.axis}: ${e.value.toFixed(2)}, Label: ${e.label}`);
        
        const axisInfo = document.createElement('div');
        axisInfo.className = 'axis-info';
        axisInfo.textContent = `Eje: ${e.axis} = ${e.value.toFixed(2)}`;
        axesDiv.innerHTML = axisInfo.innerHTML;
    });
    
} catch(err) {
    console.error('Error:', err);
    statusDiv.textContent = 'Error al cargar GamepadHandler';
}
</script>

</body>
</html>
