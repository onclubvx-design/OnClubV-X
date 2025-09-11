// Datos de ejemplo de caddies
let caddies = [
    {
        id: 1,
        nombre: "Carlos Mendoza",
        documento: "12345678",
        telefono: "301-234-5678",
        email: "carlos@email.com",
        idUnico: "CD001",
        isActive: false,
        attendance: []
    },
    {
        id: 2,
        nombre: "Ana Rodríguez",
        documento: "87654321",
        telefono: "302-876-5432",
        email: "ana@email.com",
        idUnico: "CD002",
        isActive: true,
        attendance: [
            { type: 'entrada', datetime: '2025-07-04 08:30:00' }
        ]
    },
    {
        id: 3,
        nombre: "Miguel Torres",
        documento: "11223344",
        telefono: "303-112-2334",
        email: "miguel@email.com",
        idUnico: "CD003",
        isActive: false,
        attendance: [
            { type: 'entrada', datetime: '2025-07-04 09:00:00' },
            { type: 'salida', datetime: '2025-07-04 17:00:00' }
        ]
    },
    {
        id: 4,
        nombre: "Laura Jiménez",
        documento: "55667788",
        telefono: "304-556-6778",
        email: "laura@email.com",
        idUnico: "CD004",
        isActive: true,
        attendance: [
            { type: 'entrada', datetime: '2025-07-04 07:45:00' }
        ]
    },
    {
        id: 5,
        nombre: "Roberto Silva",
        documento: "99887766",
        telefono: "305-998-8776",
        email: "roberto@email.com",
        idUnico: "CD005",
        isActive: true,
        attendance: [
            { type: 'entrada', datetime: '2025-07-04 08:15:00' }
        ]
    }
];

// Función para obtener fecha y hora actual
function obtenerFechaHoraActual() {
    const ahora = new Date();
    return ahora.toLocaleString('es-CO', {
        timeZone: 'America/Bogota',
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit',
        hour12: false
    });
}

// Registrar entrada
function registrarEntrada(caddieId) {
    const caddie = caddies.find(c => c.id === caddieId);
    if (!caddie) return;

    if (caddie.isActive) {
        mostrarMensaje('error', `${caddie.nombre} ya se encuentra activo`);
        return;
    }

    const fechaHora = obtenerFechaHoraActual();
    caddie.attendance.push({
        type: 'entrada',
        datetime: fechaHora
    });
    caddie.isActive = true;

    renderCaddies();
    mostrarMensaje('success', `Entrada registrada para ${caddie.nombre} a las ${fechaHora}`);
}

// Registrar salida
function registrarSalida(caddieId) {
    const caddie = caddies.find(c => c.id === caddieId);
    if (!caddie) return;

    if (!caddie.isActive) {
        mostrarMensaje('error', `${caddie.nombre} no se encuentra activo`);
        return;
    }

    const fechaHora = obtenerFechaHoraActual();
    caddie.attendance.push({
        type: 'salida',
        datetime: fechaHora
    });
    caddie.isActive = false;

    renderCaddies();
    mostrarMensaje('success', `Salida registrada para ${caddie.nombre} a las ${fechaHora}`);
}

// Función para verificar cierre automático a las 7 PM
function verificarCierreAutomatico() {
    const ahora = new Date();
    const hora = ahora.getHours();
    
    // Si son las 7 PM o después (19:00)
    if (hora >= 19) {
        let caddiesCerrados = [];
        
        caddies.forEach(caddie => {
            if (caddie.isActive) {
                const fechaHora = obtenerFechaHoraActual();
                caddie.attendance.push({
                    type: 'salida',
                    datetime: fechaHora,
                    auto: true // Marcar como salida automática
                });
                caddie.isActive = false;
                caddiesCerrados.push(caddie.nombre);
            }
        });
        
        if (caddiesCerrados.length > 0) {
            mostrarNotificacionCierre(caddiesCerrados);
            renderCaddies();
        }
    }
}

// Mostrar notificación de cierre automático
function mostrarNotificacionCierre(caddiesCerrados) {
    const notification = document.getElementById('autoCloseNotification');
    notification.innerHTML = `
        <strong>⏰ Cierre Automático (7:00 PM)</strong><br>
        Se registró salida automática para: ${caddiesCerrados.join(', ')}
    `;
    notification.style.display = 'block';
    setTimeout(() => notification.style.display = 'none', 10000);
}

// Función para mostrar historial completo
function mostrarHistorialCompleto(caddieId) {
    const caddie = caddies.find(c => c.id === caddieId);
    if (!caddie) return;

    const modal = document.getElementById('historyModal');
    const modalContent = document.getElementById('modalContent');
    
    let historialHtml = `
        <div style="margin-bottom: 20px;">
            <h4 style="color: #0f4c3a; margin-bottom: 10px;">${caddie.nombre} (${caddie.idUnico})</h4>
            <p style="color: #666; margin-bottom: 15px;">Historial completo de asistencia</p>
        </div>
    `;

    if (caddie.attendance && caddie.attendance.length > 0) {
        historialHtml += '<div>';
        caddie.attendance.slice().reverse().forEach(entry => {
            const isAuto = entry.auto ? ' (Automático)' : '';
            historialHtml += `
                <div class="attendance-entry ${entry.type}" style="margin-bottom: 10px;">
                    <span class="attendance-type">${entry.type}${isAuto}</span>
                    <span class="attendance-time">${entry.datetime}</span>
                </div>
            `;
        });
        historialHtml += '</div>';
    } else {
        historialHtml += '<p style="color: #666; text-align: center; padding: 20px;">No hay registros de asistencia</p>';
    }

    modalContent.innerHTML = historialHtml;
    modal.style.display = 'block';
}

// Cerrar modal
function closeHistoryModal() {
    document.getElementById('historyModal').style.display = 'none';
}

// Cerrar modal al hacer clic fuera
window.onclick = function(event) {
    const modal = document.getElementById('historyModal');
    if (event.target === modal) {
        modal.style.display = 'none';
    }
}

// Mostrar mensajes de éxito o error
function mostrarMensaje(tipo, mensaje) {
    const successDiv = document.getElementById('successMessage');
    const errorDiv = document.getElementById('errorMessage');

    successDiv.style.display = 'none';
    errorDiv.style.display = 'none';

    if (tipo === 'success') {
        successDiv.textContent = mensaje;
        successDiv.style.display = 'block';
        setTimeout(() => successDiv.style.display = 'none', 5000);
    } else if (tipo === 'error') {
        errorDiv.textContent = mensaje;
        errorDiv.style.display = 'block';
        setTimeout(() => errorDiv.style.display = 'none', 5000);
    }
}

// Renderizar lista de caddies
function renderCaddies(caddiesToRender = caddies) {
    const caddiesList = document.getElementById('caddiesList');
    caddiesList.innerHTML = '';

    caddiesToRender.forEach(caddie => {
        const caddieCard = document.createElement('div');
        caddieCard.className = `caddie-card ${caddie.isActive ? 'active' : ''}`;

        // Mostrar solo la entrada más reciente
        let attendanceHtml = '';
        if (caddie.attendance && caddie.attendance.length > 0) {
            const lastEntry = caddie.attendance[caddie.attendance.length - 1];
            const isAuto = lastEntry.auto ? ' (Automático)' : '';
            const hasMoreEntries = caddie.attendance.length > 1;
            
            attendanceHtml = `
                <div class="attendance-history">
                    <h4>📊 Último Registro</h4>
                    <div class="attendance-entry ${lastEntry.type}">
                        <span class="attendance-type">${lastEntry.type}${isAuto}</span>
                        <span class="attendance-time">${lastEntry.datetime}</span>
                    </div>
                    ${hasMoreEntries ? `
                        <button class="btn-history" onclick="mostrarHistorialCompleto(${caddie.id})">
                            Ver historial completo (${caddie.attendance.length - 1} más)
                        </button>
                    ` : ''}
                </div>
            `;
        }

        caddieCard.innerHTML = `
            <div class="caddie-header">
                <div>
                    <div class="caddie-name">${caddie.nombre}</div>
                    <div class="caddie-id">ID: ${caddie.idUnico}</div>
                </div>
                <span class="status-badge ${caddie.isActive
?