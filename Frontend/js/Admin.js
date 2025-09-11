// Variable global para almacenar caddies
let caddiesData = [];

document.addEventListener('DOMContentLoaded', function() {
    console.log('Admin.js cargado correctamente');
    
    // Configurar tabs
    const tabButtons = document.querySelectorAll('.tab-btn');
    const tabPanes = document.querySelectorAll('.tab-pane');
    
    tabButtons.forEach(button => {
        button.addEventListener('click', () => {
            const tabId = button.getAttribute('data-tab');
            
            // Remover clase active de todos los botones y paneles
            tabButtons.forEach(btn => btn.classList.remove('active'));
            tabPanes.forEach(pane => pane.classList.remove('active'));
            
            // Agregar clase active al botón y panel actual
            button.classList.add('active');
            document.getElementById(tabId).classList.add('active');
            
            // Cargar historial automáticamente cuando se hace clic en la pestaña
            if (tabId === 'historial') {
                cargarHistorial();
            }
        });
    });
    
    // Cargar estadísticas y lista de caddies
    loadStats();
    loadCaddies();
    loadReportFilters();
    
    // Manejar el formulario de caddies
    document.getElementById('caddie-form').addEventListener('submit', function(e) {
        e.preventDefault();
        saveCaddie();
    });
    
    // Búsqueda en tiempo real
    document.getElementById('search-caddie').addEventListener('input', function() {
        filterCaddies(this.value);
    });
});

// Función para cargar estadísticas
function loadStats() {
    fetch('../../Backend/Api/Caddies.php?action=stats')
        .then(response => {
            if (!response.ok) {
                throw new Error('Error en la API: ' + response.status);
            }
            return response.json();
        })
        .then(data => {
            console.log('Estadísticas cargadas:', data);
            document.getElementById('active-caddies').textContent = data.active || '0';
            document.getElementById('total-caddies').textContent = data.total || '0';
        })
        .catch(error => {
            console.error('Error loading stats:', error);
            document.getElementById('active-caddies').textContent = '0';
            document.getElementById('total-caddies').textContent = '0';
        });
}

// Función para cargar caddies
function loadCaddies() {
    fetch('../../Backend/Api/Caddies.php?action=getAll')
        .then(response => {
            if (!response.ok) {
                throw new Error('Error en la API: ' + response.status);
            }
            return response.json();
        })
        .then(data => {
            console.log('Caddies cargados:', data);
            caddiesData = data;
            renderCaddiesTable(data);
        })
        .catch(error => {
            console.error('Error loading caddies:', error);
            // Mostrar mensaje de error en la tabla
            const tbody = document.querySelector('#caddies-table tbody');
            tbody.innerHTML = '<tr><td colspan="6" style="text-align: center; color: red; padding: 20px;">Error al cargar los caddies. Verifica la consola para más detalles.</td></tr>';
        });
}

// Función para renderizar la tabla de caddies
function renderCaddiesTable(caddies) {
    const tbody = document.querySelector('#caddies-table tbody');
    tbody.innerHTML = '';
    
    if (caddies.length === 0) {
        tbody.innerHTML = '<tr><td colspan="6" style="text-align: center; padding: 20px;">No hay caddies registrados</td></tr>';
        return;
    }
    
    caddies.forEach(caddie => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>${caddie.codigo_unico}</td>
            <td>${caddie.nombre} ${caddie.apellido}</td>
            <td>${caddie.documento_identidad}</td>
            <td>${caddie.tipo}</td>
            <td class="${caddie.activo ? 'status-active' : 'status-inactive'}">
                ${caddie.activo ? 'Activo' : 'Inactivo'}
            </td>
            <td>
                <button onclick="editCaddie(${caddie.id})">Editar</button>
                <button onclick="deleteCaddie(${caddie.id})">Eliminar</button>
            </td>
        `;
        tbody.appendChild(tr);
    });
}

// Función para filtrar caddies
function filterCaddies(searchTerm) {
    const rows = document.querySelectorAll('#caddies-table tbody tr');
    let hasResults = false;
    
    rows.forEach(row => {
        if (row.cells && row.cells.length > 1) {
            const text = row.textContent.toLowerCase();
            if (text.includes(searchTerm.toLowerCase())) {
                row.style.display = '';
                hasResults = true;
            } else {
                row.style.display = 'none';
            }
        }
    });
    
    // Mostrar mensaje si no hay resultados
    if (!hasResults && searchTerm) {
        const tbody = document.querySelector('#caddies-table tbody');
        tbody.innerHTML = '<tr><td colspan="6" style="text-align: center; padding: 20px;">No se encontraron caddies que coincidan con "' + searchTerm + '"</td></tr>';
    } else if (!hasResults) {
        loadCaddies(); // Recargar si se borra la búsqueda
    }
}

// Función para guardar caddie (crear o actualizar)
function saveCaddie() {
    const formData = new FormData();
    formData.append('nombre', document.getElementById('nombre').value);
    formData.append('apellido', document.getElementById('apellido').value);
    formData.append('edad', document.getElementById('edad').value);
    formData.append('telefono', document.getElementById('telefono').value);
    formData.append('documento_identidad', document.getElementById('documento').value);
    formData.append('tipo', document.getElementById('tipo').value);
    formData.append('correo', document.getElementById('correo').value);
    
    const caddieId = document.getElementById('caddie-id').value;
    const action = caddieId ? 'update' : 'create';
    
    if (caddieId) {
        formData.append('id', caddieId);
    }
    
    fetch(`../../Backend/Api/Caddies.php?action=${action}`, {
        method: 'POST',
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Error en la API: ' + response.status);
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            alert('Caddie guardado exitosamente');
            document.getElementById('caddie-form').reset();
            document.getElementById('caddie-id').value = '';
            document.getElementById('btn-submit').textContent = 'Agregar Caddie';
            loadStats();
            loadCaddies();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error saving caddie:', error);
        alert('Error al guardar el caddie. Verifica la consola para más detalles.');
    });
}

// Función para editar caddie
function editCaddie(id) {
    fetch(`../../Backend/Api/Caddies.php?action=get&id=${id}`)
        .then(response => {
            if (!response.ok) {
                throw new Error('Error en la API: ' + response.status);
            }
            return response.json();
        })
        .then(data => {
            document.getElementById('caddie-id').value = data.id;
            document.getElementById('nombre').value = data.nombre;
            document.getElementById('apellido').value = data.apellido;
            document.getElementById('edad').value = data.edad;
            document.getElementById('telefono').value = data.telefono;
            document.getElementById('documento').value = data.documento_identidad;
            document.getElementById('tipo').value = data.tipo;
            document.getElementById('correo').value = data.correo || '';
            document.getElementById('btn-submit').textContent = 'Actualizar Caddie';
            
            // Scroll al formulario
            document.getElementById('caddie-form').scrollIntoView({behavior: 'smooth'});
        })
        .catch(error => {
            console.error('Error editing caddie:', error);
            alert('Error al cargar los datos del caddie. Verifica la consola para más detalles.');
        });
}

// Función para eliminar caddie
function deleteCaddie(id) {
    if (confirm('¿Estás seguro de que deseas eliminar este caddie?')) {
        fetch(`../../Backend/Api/Caddies.php?action=delete&id=${id}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Error en la API: ' + response.status);
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    alert('Caddie eliminado exitosamente');
                    loadStats();
                    loadCaddies();
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error deleting caddie:', error);
                alert('Error al eliminar el caddie. Verifica la consola para más detalles.');
            });
    }
}

// Función para buscar caddie para control de asistencia
function searchCaddie() {
    const searchTerm = document.getElementById('search-attendance').value;
    
    if (!searchTerm) {
        alert('Por favor ingrese un término de búsqueda');
        return;
    }
    
    fetch(`../../Backend/Api/Caddies.php?action=search&term=${encodeURIComponent(searchTerm)}`)
        .then(response => {
            if (!response.ok) {
                throw new Error('Error en la API: ' + response.status);
            }
            return response.json();
        })
        .then(data => {
            if (data.length > 0) {
                const caddie = data[0];
                const resultDiv = document.getElementById('attendance-result');
                const resultName = document.getElementById('result-name');
                const resultInfo = document.getElementById('result-info');
                
                resultName.textContent = `${caddie.nombre} ${caddie.apellido}`;
                resultInfo.textContent = `Código: ${caddie.codigo_unico} | Documento: ${caddie.documento_identidad} | Tipo: ${caddie.tipo}`;
                
                // Configurar botones según estado
                document.getElementById('btn-entrada').style.display = caddie.activo ? 'none' : 'inline-block';
                document.getElementById('btn-salida').style.display = caddie.activo ? 'inline-block' : 'none';
                
                // Guardar ID para usar en registrarEntrada/Salida
                resultDiv.setAttribute('data-caddie-id', caddie.id);
                resultDiv.style.display = 'block';
            } else {
                alert('No se encontró ningún caddie con esos datos');
            }
        })
        .catch(error => {
            console.error('Error searching caddie:', error);
            alert('Error al buscar el caddie. Verifica la consola para más detalles.');
        });
}

// Función para registrar entrada
function registrarEntrada() {
    const caddieId = document.getElementById('attendance-result').getAttribute('data-caddie-id');
    
    fetch('../../Backend/Api/Asistencias.php?action=entrada', {
        method: 'POST',
        body: JSON.stringify({ caddie_id: caddieId }),
        headers: {
            'Content-Type': 'application/json'
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Error en la API: ' + response.status);
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            alert('Entrada registrada exitosamente');
            document.getElementById('attendance-result').style.display = 'none';
            document.getElementById('search-attendance').value = '';
            loadStats();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error registering entry:', error);
        alert('Error al registrar la entrada. Verifica la consola para más detalles.');
    });
}

// Función para registrar salida
function registrarSalida() {
    const caddieId = document.getElementById('attendance-result').getAttribute('data-caddie-id');
    
    fetch('../../Backend/Api/Asistencias.php?action=salida', {
        method: 'POST',
        body: JSON.stringify({ caddie_id: caddieId }),
        headers: {
            'Content-Type': 'application/json'
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Error en la API: ' + response.status);
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            alert('Salida registrada exitosamente');
            document.getElementById('attendance-result').style.display = 'none';
            document.getElementById('search-attendance').value = '';
            loadStats();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error registering exit:', error);
        alert('Error al registrar la salida. Verifica la consola para más detalles.');
    });
}

// Función para cargar filtros de reportes
function loadReportFilters() {
    // Cargar años (últimos 5 años)
    const yearSelect = document.getElementById('report-year');
    const currentYear = new Date().getFullYear();
    
    for (let i = currentYear - 5; i <= currentYear; i++) {
        const option = document.createElement('option');
        option.value = i;
        option.textContent = i;
        if (i === currentYear) option.selected = true;
        yearSelect.appendChild(option);
    }
    
    // Cargar lista de caddies para reportes e historial
    fetch('../../Backend/Api/Caddies.php?action=getAll')
        .then(response => {
            if (!response.ok) {
                throw new Error('Error en la API: ' + response.status);
            }
            return response.json();
        })
        .then(data => {
            // Para reportes
            const caddieSelect = document.getElementById('report-caddie');
            caddieSelect.innerHTML = '<option value="all">Todos los caddies</option>';
            
            // Para historial
            const filtroCaddie = document.getElementById('filtro-caddie');
            filtroCaddie.innerHTML = '<option value="all">Todos los caddies</option>';
            
            data.forEach(caddie => {
                // Opción para reportes
                const option1 = document.createElement('option');
                option1.value = caddie.id;
                option1.textContent = `${caddie.nombre} ${caddie.apellido} (${caddie.codigo_unico})`;
                caddieSelect.appendChild(option1);
                
                // Opción para historial
                const option2 = document.createElement('option');
                option2.value = caddie.id;
                option2.textContent = `${caddie.nombre} ${caddie.apellido} (${caddie.codigo_unico})`;
                filtroCaddie.appendChild(option2);
            });
        })
        .catch(error => {
            console.error('Error loading filters:', error);
        });
}

// Función para generar reporte
function generateReport() {
    const month = document.getElementById('report-month').value;
    const year = document.getElementById('report-year').value;
    const caddieId = document.getElementById('report-caddie').value;
    
    fetch(`../../Backend/Api/Reportes.php?action=generate&month=${month}&year=${year}&caddie_id=${caddieId}`)
        .then(response => {
            if (!response.ok) {
                throw new Error('Error en la API: ' + response.status);
            }
            return response.json();
        })
        .then(data => {
            renderReport(data);
        })
        .catch(error => {
            console.error('Error generating report:', error);
            alert('Error al generar el reporte. Verifica la consola para más detalles.');
        });
}

// Función para renderizar reporte
function renderReport(data) {
    const resultsDiv = document.getElementById('report-results');
    // Implementar la visualización del reporte según la estructura de datos
    resultsDiv.innerHTML = '<p>Función de reportes en desarrollo. Los datos recibidos son:</p><pre>' + JSON.stringify(data, null, 2) + '</pre>';
}

// Funciones para exportar (estas necesitarían implementación adicional en el backend)
function exportPDF() {
    alert('Funcionalidad de exportar PDF en desarrollo');
}

function exportExcel() {
    alert('Funcionalidad de exportar Excel en desarrollo');
}

// Función para cargar el historial de asistencias
function cargarHistorial() {
    const caddieId = document.getElementById('filtro-caddie').value;
    const fecha = document.getElementById('filtro-fecha').value;
    
    let url = `../../Backend/Api/Asistencias.php?action=historial&fecha=${fecha}`;
    if (caddieId !== 'all') {
        url += `&caddie_id=${caddieId}`;
    }
    
    fetch(url)
        .then(response => {
            if (!response.ok) {
                throw new Error('Error en la API: ' + response.status);
            }
            return response.json();
        })
        .then(data => {
            renderizarHistorial(data);
        })
        .catch(error => {
            console.error('Error cargando historial:', error);
            const tbody = document.querySelector('#tabla-historial tbody');
            tbody.innerHTML = '<tr><td colspan="7" style="text-align: center; color: red; padding: 20px;">Error al cargar el historial. Verifica la consola para más detalles.</td></tr>';
        });
}

// Función para renderizar el historial
function renderizarHistorial(data) {
    const tbody = document.querySelector('#tabla-historial tbody');
    tbody.innerHTML = '';
    
    if (data.length === 0) {
        tbody.innerHTML = '<tr><td colspan="7" style="text-align: center; padding: 20px;">No hay registros de asistencias para esta fecha</td></tr>';
        return;
    }
    
    data.forEach(registro => {
        const tr = document.createElement('tr');
        
        // Formatear fechas y calcular duración
        const entrada = new Date(registro.fecha_entrada);
        const salida = registro.fecha_salida ? new Date(registro.fecha_salida) : null;
        
        const duracion = salida ? calcularDuracion(entrada, salida) : 'En curso';
        
        tr.innerHTML = `
            <td>${registro.codigo_unico}</td>
            <td>${registro.nombre} ${registro.apellido}</td>
            <td>${formatearFecha(entrada)}</td>
            <td>${formatearHora(entrada)}</td>
            <td>${salida ? formatearFecha(salida) : '-'}</td>
            <td>${salida ? formatearHora(salida) : '-'}</td>
            <td class="duracion-cell">${duracion}</td>
        `;
        tbody.appendChild(tr);
    });
}

// Función auxiliar para formatear fecha
function formatearFecha(fecha) {
    return fecha.toLocaleDateString('es-CO');
}

// Función auxiliar para formatear hora
function formatearHora(fecha) {
    return fecha.toLocaleTimeString('es-CO', { 
        hour: '2-digit', 
        minute: '2-digit',
        second: '2-digit'
    });
}

// Función para calcular duración
function calcularDuracion(entrada, salida) {
    const diffMs = salida - entrada;
    const diffHrs = Math.floor(diffMs / (1000 * 60 * 60));
    const diffMins = Math.floor((diffMs % (1000 * 60 * 60)) / (1000 * 60));
    
    return `${diffHrs}h ${diffMins}m`;
}

// Función para exportar historial a CSV
function exportarHistorial() {
    const tabla = document.getElementById('tabla-historial');
    let csv = [];
    
    // Encabezados
    const headers = [];
    for (let i = 0; i < tabla.rows[0].cells.length; i++) {
        headers.push(tabla.rows[0].cells[i].textContent);
    }
    csv.push(headers.join(','));
    
    // Datos
    for (let i = 1; i < tabla.rows.length; i++) {
        const row = [];
        for (let j = 0; j < tabla.rows[i].cells.length; j++) {
            row.push(tabla.rows[i].cells[j].textContent);
        }
        csv.push(row.join(','));
    }
    
    // Descargar
    const link = document.createElement('a');
    link.href = 'data:text/csv;charset=utf-8,' + encodeURIComponent(csv.join('\n'));
    link.download = `historial_asistencias_${new Date().toISOString().split('T')[0]}.csv`;
    link.click();
}
