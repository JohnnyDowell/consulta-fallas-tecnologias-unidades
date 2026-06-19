const host = "https://ses.lidcorp.mx";
const apiBaseUrl = host + "/Master-API/ses/FTU/ConsultarPorMes";

// Función auxiliar para normalizar cadenas quitando acentos/diacríticos (excluyendo la Ñ)
function removeAccents(str) {
    if (!str) return "";
    return String(str)
        .normalize("NFD")
        .replace(/[\u0300-\u0302\u0304-\u036f]/g, ""); // Excluye U+0303 (virgulilla de la Ñ)
}

// Función para resaltar la coincidencia de búsqueda de forma insensible a acentos
function destacarCoincidencia(textoOriginal, consulta) {
    if (!consulta || !textoOriginal) return textoOriginal || '';
    
    const textoNormalizado = removeAccents(textoOriginal);
    const consultaNormalizada = removeAccents(consulta);
    
    const consultaEscapada = consultaNormalizada.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
    if (!consultaEscapada) return textoOriginal;
    
    const regex = new RegExp(consultaEscapada, "gi");
    let resultadoHTML = "";
    let ultimoIndice = 0;
    let coincidencia;
    
    while ((coincidencia = regex.exec(textoNormalizado)) !== null) {
        const indiceInicio = coincidencia.index;
        const longitud = coincidencia[0].length;
        
        resultadoHTML += textoOriginal.substring(ultimoIndice, indiceInicio);
        const textoAEnvolver = textoOriginal.substring(indiceInicio, indiceInicio + longitud);
        
        // Usar color azul brillante (#2563eb) de alto contraste en el texto para resaltar sin mover la fuente
        resultadoHTML += `<span class="text-[#2563eb] font-bold">${textoAEnvolver}</span>`;
        
        ultimoIndice = regex.lastIndex;
    }
    
    resultadoHTML += textoOriginal.substring(ultimoIndice);
    return resultadoHTML;
}

// Variables globales de estado
let allData = [];
let filteredData = [];
let tiposResolucion = [];

let currentStatusFilter = "all";
let currentSearchQuery = "";
let currentSortColumn = "";
let currentSortDirection = "asc";

let currentPage = 1;
let pageSize = 10; // Se recalculará dinámicamente

// Estado del Selector de Mes Personalizado
let selectedYear;
let selectedMonth;
let pickerYear;
const monthNames = ["Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"];

// Carga inicial
document.addEventListener("DOMContentLoaded", () => {
    // Inicializar mes y año actual
    const now = new Date();
    selectedYear = now.getFullYear();
    selectedMonth = now.getMonth();
    pickerYear = selectedYear;
    
    // Configurar input oculto y etiquetas
    updateMonthPickerValue();
    updateMonthPickerUI();
    
    // Obtener catálogo de resoluciones y luego cargar datos
    cargarTiposResolucion(() => {
        // Calcular tamaño de página inicial basado en el tamaño de pantalla
        adjustPageSize();
        loadData();
    });

    // Escuchar el cambio de tamaño de la ventana para recalcular las filas visibles
    window.addEventListener("resize", () => {
        adjustPageSize();
        renderTable();
    });

    // Cerrar menús al dar clic fuera
    document.addEventListener("click", (e) => {
        // Cerrar menú de exportación
        const menu = document.getElementById("export-menu");
        const btn = document.getElementById("btn-export");
        if (menu && btn && !btn.contains(e.target) && !menu.contains(e.target)) {
            menu.classList.add("hidden");
        }
        
        // Cerrar selector de meses
        const pickerDropdown = document.getElementById("month-picker-dropdown");
        const pickerBtn = document.getElementById("month-picker-btn");
        if (pickerDropdown && pickerBtn && !pickerBtn.contains(e.target) && !pickerDropdown.contains(e.target)) {
            pickerDropdown.classList.add("hidden");
        }
    });
});

// Selector de Mes Personalizado: Alternar despliegue
function toggleMonthPicker() {
    const dropdown = document.getElementById("month-picker-dropdown");
    dropdown.classList.toggle("hidden");
    if (!dropdown.classList.contains("hidden")) {
        pickerYear = selectedYear; // Al abrir, mostrar el año seleccionado
        updateMonthPickerUI();
    }
}

// Selector de Mes Personalizado: Cambiar año visualizado
function changePickerYear(offset) {
    const now = new Date();
    const currentYear = now.getFullYear();
    
    // Si intentan ir a un año en el futuro, no hacer nada
    if (pickerYear + offset > currentYear) return;
    
    pickerYear += offset;
    updateMonthPickerUI();
}

// Selector de Mes Personalizado: Seleccionar mes
function selectPickerMonth(monthIndex) {
    const now = new Date();
    const currentYear = now.getFullYear();
    const currentMonth = now.getMonth();
    
    // Evitar selección en el futuro
    if (pickerYear > currentYear || (pickerYear === currentYear && monthIndex > currentMonth)) {
        return;
    }
    
    selectedYear = pickerYear;
    selectedMonth = monthIndex;
    
    updateMonthPickerValue();
    updateMonthPickerUI();
    
    // Cerrar panel
    document.getElementById("month-picker-dropdown").classList.add("hidden");
    
    // Recargar datos
    loadData();
}

// Selector de Mes Personalizado: Actualizar valor oculto y label
function updateMonthPickerValue() {
    const monthStr = String(selectedMonth + 1).padStart(2, '0');
    document.getElementById("fecha").value = `${selectedYear}-${monthStr}`;
    
    const label = `${monthNames[selectedMonth]} ${selectedYear}`;
    document.getElementById("selected-month-label").textContent = label;
}

// Selector de Mes Personalizado: Resaltar mes y año activo, restringir futuro
function updateMonthPickerUI() {
    const now = new Date();
    const currentYear = now.getFullYear();
    const currentMonth = now.getMonth();
    
    document.getElementById("picker-year-label").textContent = pickerYear;
    
    // Habilitar/Deshabilitar botón de año siguiente si ya estamos en el año actual
    const nextYearBtn = document.querySelector("#custom-month-picker button[onclick='changePickerYear(1)']");
    if (nextYearBtn) {
        if (pickerYear >= currentYear) {
            nextYearBtn.disabled = true;
            nextYearBtn.classList.add("opacity-30", "pointer-events-none");
        } else {
            nextYearBtn.disabled = false;
            nextYearBtn.classList.remove("opacity-30", "pointer-events-none");
        }
    }
    
    const buttons = document.querySelectorAll(".month-btn");
    buttons.forEach((btn, index) => {
        const isFuture = (pickerYear > currentYear) || (pickerYear === currentYear && index > currentMonth);
        
        if (isFuture) {
            btn.disabled = true;
            btn.className = "month-btn py-1.5 text-xs font-semibold rounded-lg bg-slate-50 text-slate-300 cursor-not-allowed pointer-events-none select-none";
        } else if (pickerYear === selectedYear && index === selectedMonth) {
            btn.disabled = false;
            btn.className = "month-btn py-1.5 text-xs font-bold rounded-lg bg-[#1f4e78] text-white transition-all shadow";
        } else {
            btn.disabled = false;
            btn.className = "month-btn py-1.5 text-xs font-bold rounded-lg hover:bg-slate-100 text-slate-700 transition-all";
        }
    });
}

// Calcular el tamaño de página óptimo según el viewport vertical para evitar scrollbar
function calculatePageSize() {
    const windowHeight = window.innerHeight;
    
    // Medir la altura de los componentes de forma dinámica
    const header = document.querySelector("header");
    const headerHeight = header ? header.getBoundingClientRect().height : 60;
    
    const filterSection = document.querySelector(".filter-section");
    const filterSectionHeight = filterSection ? filterSection.getBoundingClientRect().height : 60;
    
    const thead = document.querySelector("thead");
    const tableHeaderHeight = thead ? thead.getBoundingClientRect().height : 40;
    
    const tableFooter = document.getElementById("pagination-buttons")?.parentElement;
    const tableFooterHeight = tableFooter ? tableFooter.getBoundingClientRect().height : 50;
    
    const footer = document.querySelector("footer");
    const footerHeight = footer ? footer.getBoundingClientRect().height : 40;
    const footerMargin = 8; // Margen fijo para evitar feedback loop con mt-auto

    
    // Medir dinámicamente paddings y gaps para evitar valores hardcodeados
    const main = document.querySelector("main");
    let mainVerticalPadding = 0;
    if (main) {
        const style = window.getComputedStyle(main);
        mainVerticalPadding = (parseFloat(style.paddingTop) || 0) + (parseFloat(style.paddingBottom) || 0);
    }
    
    const card = document.querySelector(".card");
    let cardVerticalPadding = 0;
    let cardGap = 0;
    if (card) {
        const style = window.getComputedStyle(card);
        cardVerticalPadding = (parseFloat(style.paddingTop) || 0) + (parseFloat(style.paddingBottom) || 0);
        cardGap = (parseFloat(style.gap) || 16) * 2; // Dos gaps entre los tres elementos internos de la tarjeta
    }
    
    // 10px de margen de seguridad para bordes de tablas y sombras de tarjetas
    const totalPaddingsAndMargins = mainVerticalPadding + cardVerticalPadding + cardGap + 10; 
    
    const reservedHeight = headerHeight + filterSectionHeight + tableHeaderHeight + tableFooterHeight + footerHeight + footerMargin + totalPaddingsAndMargins;
    const availableHeight = windowHeight - reservedHeight;
    
    const averageRowHeight = 42; // Alto de fila compactada con padding py-1 y formato de fecha ajustado
    let calculatedSize = Math.floor(availableHeight / averageRowHeight);
    
    if (calculatedSize < 5) calculatedSize = 5; // Mínimo de filas para evitar romper el diseño
    return calculatedSize;
}

// Ajustar el tamaño de la página al estado de la ventana
function adjustPageSize() {
    pageSize = calculatePageSize();
}

// Cargar catálogo de tipos de resolución
function cargarTiposResolucion(callback) {
    fetch(`${host}/Master-API/ses/FTU/GetTiposResolucionFTU`)
        .then(response => response.json())
        .then(respuesta => {
            tiposResolucion = respuesta.data || respuesta || [];
            if (callback) callback();
        })
        .catch(err => {
            console.error("Error al obtener catálogo de resoluciones:", err);
            if (callback) callback();
        });
}

// Cargar datos del mes seleccionado
function loadData() {
    const selectedDate = document.getElementById("fecha").value;
    if (!selectedDate) return;
    
    const [year, month] = selectedDate.split("-");
    document.title = `SES - Consulta de Fallas - ${selectedDate}`;
    
    // Cambiar cuerpo de la tabla a cargando
    const tbody = document.getElementById("table-body");
    tbody.innerHTML = `
        <tr>
            <td colspan="13" class="text-center py-8 text-slate-400 font-semibold">
                <i class="fa-solid fa-spinner animate-spin text-lg mr-2"></i> Cargando información...
            </td>
        </tr>
    `;

    fetch(`${apiBaseUrl}?year=${year}&month=${month}`)
        .then(response => response.json())
        .then(json => {
            const list = json.data || [];
            
            // Generar campo Estado de forma dinámica
            list.forEach(row => {
                row.Estado = (row.FechaCerrado && row.FechaCerrado.trim() !== '' && row.FechaCerrado !== 'null') ? 'Atendido' : 'Por Atender';
            });
            
            allData = list;
            currentPage = 1; // Resetear a página 1
            applyFiltersAndRender();
        })
        .catch(err => {
            console.error("Error al cargar reportes:", err);
            tbody.innerHTML = `
                <tr>
                    <td colspan="13" class="text-center py-8 text-red-500 font-semibold">
                        <i class="fa-solid fa-triangle-exclamation text-lg mr-2"></i> Ocurrió un error al cargar la información.
                    </td>
                </tr>
            `;
        });
}

// Aplicar filtros, ordenamiento, KPIs y paginar
function applyFiltersAndRender() {
    // 1. Filtrar por Estado (Tabs)
    let temp = allData;
    if (currentStatusFilter === "pending") {
        temp = temp.filter(row => row.Estado === 'Por Atender');
    } else if (currentStatusFilter === "resolved") {
        temp = temp.filter(row => row.Estado === 'Atendido');
    }

    // 2. Filtrar por búsqueda
    if (currentSearchQuery !== "") {
        const queryClean = removeAccents(currentSearchQuery);
        temp = temp.filter(row => {
            const nomina = removeAccents(row.NominaCreador || "").toLowerCase();
            const creador = removeAccents(row.Nombre || "").toLowerCase();
            const unidad = removeAccents(String(row.Unidad || "")).toLowerCase();
            const depto = removeAccents(row.Departamento || "").toLowerCase();
            const planta = removeAccents(row.Planta || row.planta || "").toLowerCase();
            const problema = removeAccents(row.ProblemaDescripcion || "").toLowerCase();
            const detalle = removeAccents(row.Detalle || "").toLowerCase();
            const tecnico = removeAccents(row.ResolucionNombre || "").toLowerCase();
            const resolucion = removeAccents(row.Resolucion || "").toLowerCase();
            
            // Campos adicionales que faltaban de las columnas
            const fechaCreacion = removeAccents(row.FechaCreacion || "").toLowerCase();
            const fechaCerrado = removeAccents(row.FechaCerrado || "").toLowerCase();
            const estado = removeAccents(row.Estado || "").toLowerCase();
            
            // Obtener descripción mapeada del catálogo para el tipo de falla
            let tipoFallaDesc = "";
            if (row.TipoResolucionId) {
                const match = tiposResolucion.find(item => String(item.Id) === String(row.TipoResolucionId));
                tipoFallaDesc = match ? (match.Nombre || match.Descripcion || match.TipoResolucion || String(row.TipoResolucionId)) : String(row.TipoResolucionId);
            }
            const tipoFalla = removeAccents(tipoFallaDesc).toLowerCase();
            
            return nomina.includes(queryClean) || 
                   creador.includes(queryClean) || 
                   unidad.includes(queryClean) || 
                   depto.includes(queryClean) || 
                   planta.includes(queryClean) || 
                   problema.includes(queryClean) || 
                   detalle.includes(queryClean) || 
                   tecnico.includes(queryClean) || 
                   resolucion.includes(queryClean) ||
                   fechaCreacion.includes(queryClean) ||
                   fechaCerrado.includes(queryClean) ||
                   estado.includes(queryClean) ||
                   tipoFalla.includes(queryClean);
        });
    }

    // 3. Ordenar
    if (currentSortColumn !== "") {
        temp.sort((a, b) => {
            let valA = a[currentSortColumn];
            let valB = b[currentSortColumn];
            
            // Tratamiento especial para campos numéricos como Unidad
            if (currentSortColumn === "Unidad") {
                valA = Number(valA) || 0;
                valB = Number(valB) || 0;
            } else {
                valA = String(valA || "").toLowerCase();
                valB = String(valB || "").toLowerCase();
            }
            
            if (valA < valB) return currentSortDirection === "asc" ? -1 : 1;
            if (valA > valB) return currentSortDirection === "asc" ? 1 : -1;
            return 0;
        });
    }

    filteredData = temp;

    // 4. Actualizar contadores en pestañas
    updateKPIs();

    // 5. Paginar y renderizar
    renderTable();
}

// Actualizar contadores de las pestañas
function updateKPIs() {
    const totalMes = allData.length;
    const pendientesMes = allData.filter(r => r.Estado === 'Por Atender').length;
    const atendidosMes = allData.filter(r => r.Estado === 'Atendido').length;
    
    document.getElementById("count-all").textContent = totalMes;
    document.getElementById("count-pending").textContent = pendientesMes;
    document.getElementById("count-resolved").textContent = atendidosMes;
}

// Renderizar filas de la tabla correspondientes a la página activa
function renderTable() {
    const tbody = document.getElementById("table-body");
    const totalRecords = filteredData.length;
    
    if (totalRecords === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="13" class="text-center py-8 text-slate-400 font-semibold">
                    No se encontraron registros.
                </td>
            </tr>
        `;
        document.getElementById("pagination-info").textContent = "Mostrando 0 a 0 de 0 registros";
        document.getElementById("pagination-buttons").innerHTML = "";
        return;
    }

    // Calcular límites de página
    const maxPage = Math.ceil(totalRecords / pageSize);
    if (currentPage > maxPage) currentPage = maxPage;
    if (currentPage < 1) currentPage = 1;

    const startIdx = (currentPage - 1) * pageSize;
    const endIdx = Math.min(startIdx + pageSize, totalRecords);
    const pageData = filteredData.slice(startIdx, endIdx);

    // Formatear info de paginación
    document.getElementById("pagination-info").textContent = `Mostrando ${startIdx + 1} a ${endIdx} de ${totalRecords} registros`;

    let html = "";
    pageData.forEach((row, idx) => {
        // Cebra stripe (Fondo alternante suave)
        const rowBgClass = (idx % 2 === 0) ? 'bg-white' : 'bg-slate-50/60';

        // Estandarizar Problema a la paleta corporativa (máx 2 renglones)
        const cleanProblema = (row.ProblemaDescripcion || '').replace(/"/g, '&quot;');
        const highlightedProblema = destacarCoincidencia(row.ProblemaDescripcion || '', currentSearchQuery);
        const problemaBadge = `<span class="inline-block align-middle max-w-[95px] px-2 py-0.5 text-[11px] rounded-lg font-bold bg-[#1f4e78]/5 text-[#1f4e78] border border-[#1f4e78]/10 line-clamp-2 break-words leading-tight text-center" title="${cleanProblema}">${highlightedProblema}</span>`;

        // Formatear Estado
        let estadoBadge = "";
        if (row.Estado === 'Atendido') {
            const highlightedText = destacarCoincidencia('Atendido', currentSearchQuery);
            estadoBadge = `<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg text-xs font-bold bg-emerald-100/70 text-emerald-800 border border-emerald-200"><i class="fa-regular fa-circle-check text-[11px]"></i> ${highlightedText}</span>`;
        } else {
            const highlightedText = destacarCoincidencia('Por Atender', currentSearchQuery);
            estadoBadge = `<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg text-xs font-bold bg-amber-100/70 text-amber-800 border border-amber-200 animate-pulse"><i class="fa-solid fa-clock text-[10px]"></i> ${highlightedText}</span>`;
        }

        // Tipo de falla mapeado a descripción del catálogo
        let tipoFallaDesc = '-';
        if (row.TipoResolucionId) {
            const match = tiposResolucion.find(item => String(item.Id) === String(row.TipoResolucionId));
            tipoFallaDesc = match ? (match.Nombre || match.Descripcion || match.TipoResolucion || row.TipoResolucionId) : row.TipoResolucionId;
        }
        const highlightedTipoFalla = destacarCoincidencia(tipoFallaDesc, currentSearchQuery);

        // Renderizado del Técnico o Botón de Acción
        let tecnicoCell = row.ResolucionNombre || '-';
        
        let resolucionCell = '';
        if (row.Estado === 'Por Atender') {
            if (userHasEditPermission) {
                resolucionCell = `
                    <button onclick="openModal(${row.Id})" class="px-2.5 py-1 bg-[#1f4e78] hover:bg-[#163754] text-white font-bold rounded-lg transition-all shadow hover:shadow-md flex items-center justify-center gap-1 text-[10px] focus:outline-none">
                        <i class="fa-solid fa-wrench"></i> Atender
                    </button>
                `;
            } else {
                resolucionCell = '<span class="text-slate-400 font-semibold text-xs">-</span>';
            }
        } else {
            const cleanResolucion = (row.Resolucion || '-').replace(/"/g, '&quot;');
            const highlightedResolucion = destacarCoincidencia(row.Resolucion || '-', currentSearchQuery);
            resolucionCell = `
                <div class="max-w-[140px] line-clamp-2 break-words text-left text-slate-600 text-xs" title="${cleanResolucion}">${highlightedResolucion}</div>
            `;
        }

        let cerradoCell = '';
        if (row.Estado === 'Por Atender') {
            cerradoCell = '<span class="text-slate-400 font-semibold text-xs">-</span>';
        } else {
            cerradoCell = formatDateProposal1(row.FechaCerrado, currentSearchQuery);
        }

        // Limpiar comillas en textos para evitar romper atributos HTML de tooltip
        const cleanNombre = (row.Nombre || '').replace(/"/g, '&quot;');
        const cleanDepto = (row.Departamento || '-').replace(/"/g, '&quot;');
        const cleanPlanta = (row.Planta || row.planta || '-').replace(/"/g, '&quot;');
        const cleanDetalle = (row.Detalle || '').replace(/"/g, '&quot;');
        const cleanTecnico = (tecnicoCell || '').replace(/"/g, '&quot;');
        const cleanTipoFalla = tipoFallaDesc.replace(/"/g, '&quot;');

        // Textos con coincidencia destacada
        const highlightedNomina = destacarCoincidencia(row.NominaCreador || '', currentSearchQuery);
        const highlightedNombre = destacarCoincidencia(row.Nombre || '', currentSearchQuery);
        const highlightedUnidad = destacarCoincidencia(row.Unidad || '', currentSearchQuery);
        const highlightedDepto = destacarCoincidencia(row.Departamento || '-', currentSearchQuery);
        const highlightedPlanta = destacarCoincidencia(row.Planta || row.planta || '-', currentSearchQuery);
        const highlightedDetalle = destacarCoincidencia(row.Detalle || '', currentSearchQuery);
        const highlightedTecnico = destacarCoincidencia(tecnicoCell, currentSearchQuery);

        html += `
            <tr class="${rowBgClass} hover:bg-slate-100/60 transition-colors border-b border-slate-100">
                <td class="px-2 py-1 text-center align-middle text-slate-500 font-bold whitespace-nowrap text-xs">${highlightedNomina}</td>
                <td class="px-2 py-1 align-middle">
                    <div class="max-w-[100px] line-clamp-2 break-words text-center text-slate-800 font-semibold mx-auto text-xs" title="${cleanNombre}">${highlightedNombre}</div>
                </td>
                <td class="px-2 py-1 text-center align-middle font-bold text-slate-800 whitespace-nowrap text-xs">${highlightedUnidad}</td>
                <td class="px-2 py-1 align-middle">
                    <div class="max-w-[90px] line-clamp-2 break-words text-center text-slate-500 font-semibold mx-auto text-xs" title="${cleanDepto}">${highlightedDepto}</div>
                </td>
                <td class="px-2 py-1 align-middle">
                    <div class="max-w-[80px] line-clamp-2 break-words text-center text-slate-500 font-semibold mx-auto text-xs" title="${cleanPlanta}">${highlightedPlanta}</div>
                </td>
                <td class="px-2 py-1 text-center align-middle">${problemaBadge}</td>
                <td class="px-2 py-1 align-middle">
                    <div class="max-w-[140px] line-clamp-2 break-words text-left text-slate-600 text-xs" title="${cleanDetalle}">${highlightedDetalle}</div>
                </td>
                <td class="px-2 py-1 text-center align-middle">${formatDateProposal1(row.FechaCreacion, currentSearchQuery)}</td>
                <td class="px-2 py-1 align-middle">${resolucionCell}</td>
                <td class="px-2 py-1 text-center align-middle text-slate-500 font-semibold text-xs">
                    <div class="max-w-[100px] line-clamp-2 break-words text-center mx-auto" title="${cleanTipoFalla}">${highlightedTipoFalla}</div>
                </td>
                <td class="px-2 py-1 text-center align-middle">${cerradoCell}</td>
                <td class="px-2 py-1 text-center align-middle">
                    <div class="max-w-[90px] line-clamp-2 break-words text-center text-slate-600 font-semibold mx-auto text-xs" title="${cleanTecnico}">${highlightedTecnico}</div>
                </td>
                <td class="px-2 py-1 text-center align-middle">${estadoBadge}</td>
            </tr>
        `;
    });
    
    tbody.innerHTML = html;

    // Renderizar Botones de paginación
    renderPaginationButtons(maxPage);
}

// Renderizar barra de botones de paginación
function renderPaginationButtons(maxPage) {
    const container = document.getElementById("pagination-buttons");
    let html = "";
    
    // Botón Anterior
    html += `
        <button onclick="changePage(${currentPage - 1})" ${currentPage === 1 ? 'disabled' : ''} class="px-2 py-1 border border-slate-300 rounded-lg hover:bg-slate-50 disabled:opacity-40 disabled:hover:bg-white text-slate-600 transition-colors font-bold flex items-center">
            <i class="fa-solid fa-chevron-left text-[9px]"></i>
        </button>
    `;
    
    // Páginas numéricas (siempre mostrando la actual, primera y última)
    for (let i = 1; i <= maxPage; i++) {
        if (i === 1 || i === maxPage || (i >= currentPage - 1 && i <= currentPage + 1)) {
            html += `
                <button onclick="changePage(${i})" class="px-2.5 py-1 rounded-lg border font-bold transition-all text-xs ${currentPage === i ? 'bg-[#1f4e78] border-[#1f4e78] text-white' : 'border-slate-300 text-slate-600 hover:bg-slate-50'}">
                    ${i}
                </button>
            `;
        } else if (i === currentPage - 2 || i === currentPage + 2) {
            html += `<span class="px-1 text-slate-400 select-none text-[10px]">...</span>`;
        }
    }
    
    // Botón Siguiente
    html += `
        <button onclick="changePage(${currentPage + 1})" ${currentPage === maxPage ? 'disabled' : ''} class="px-2 py-1 border border-slate-300 rounded-lg hover:bg-slate-50 disabled:opacity-40 disabled:hover:bg-white text-slate-600 transition-colors font-bold flex items-center">
            <i class="fa-solid fa-chevron-right text-[9px]"></i>
        </button>
    `;
    
    container.innerHTML = html;
}

// Cambiar de página
function changePage(page) {
    currentPage = page;
    renderTable();
}

// Filtrar por Estado al dar clic en las pestañas
function filterStatus(status) {
    currentStatusFilter = status;
    currentPage = 1;
    
    // Actualizar estilos activos de pestañas
    const tabs = ["all", "pending", "resolved"];
    tabs.forEach(tabKey => {
        const tab = document.getElementById(`tab-${tabKey}`);
        if (tabKey === status) {
            tab.className = "flex-1 py-1.5 text-xs font-bold rounded-lg text-[#1f4e78] bg-[#ffc000] shadow-sm transition-all flex items-center justify-center gap-1.5";
        } else {
            tab.className = "flex-1 py-1.5 text-xs font-bold rounded-lg text-slate-600 hover:text-slate-800 transition-all flex items-center justify-center gap-1.5";
        }
    });
    
    applyFiltersAndRender();
}

// Búsqueda local en tiempo real
function handleSearch(query) {
    currentSearchQuery = query.toLowerCase().trim();
    currentPage = 1;
    applyFiltersAndRender();
}

// Ordenar por columna
function sortBy(column) {
    if (currentSortColumn === column) {
        // Alternar dirección
        currentSortDirection = currentSortDirection === "asc" ? "desc" : "asc";
    } else {
        currentSortColumn = column;
        currentSortDirection = "asc";
    }
    
    // Actualizar indicadores visuales de ordenación (flechas)
    const allHeaders = ["NominaCreador", "Nombre", "Unidad", "Departamento", "Planta", "ProblemaDescripcion", "FechaCreacion", "TipoResolucionDescripcion", "FechaCerrado", "ResolucionNombre", "Estado"];
    allHeaders.forEach(col => {
        const span = document.getElementById(`sort-${col}`);
        if (span) {
            if (col === column) {
                span.innerHTML = currentSortDirection === "asc" ? '<i class="fa-solid fa-caret-up ml-1 text-slate-600"></i>' : '<i class="fa-solid fa-caret-down ml-1 text-slate-600"></i>';
            } else {
                span.innerHTML = '';
            }
        }
    });
    
    applyFiltersAndRender();
}

// Parsear fecha a partes legibles
function parseDateTime(dateStr) {
    if (!dateStr || dateStr === 'null' || dateStr.trim() === '') return null;
    const parts = dateStr.split(' ');
    if (parts.length < 2) return null;
    
    const dateParts = parts[0].split('-');
    if (dateParts.length < 3) return null;
    
    const y = dateParts[0];
    const m = parseInt(dateParts[1], 10) - 1;
    const d = parseInt(dateParts[2], 10);
    
    const months = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
    const formattedDate = `${d} ${months[m]}, ${y}`;
    
    const timeParts = parts[1].split(':');
    if (timeParts.length < 2) return null;
    
    let hour = parseInt(timeParts[0], 10);
    const minute = timeParts[1];
    const ampm = hour >= 12 ? 'PM' : 'AM';
    hour = hour % 12;
    hour = hour ? hour : 12;
    const formattedTime = `${hour}:${minute} ${ampm}`;
    
    return { date: formattedDate, time: formattedTime };
}

// Propuesta 1: Doble renglón vertical con la hora dentro de un pequeño badge gris
function formatDateProposal1(dateStr, query = '') {
    const parsed = parseDateTime(dateStr);
    if (!parsed) return '-';
    
    const highlightedDate = destacarCoincidencia(parsed.date, query);
    const highlightedTime = destacarCoincidencia(parsed.time, query);
    
    return `
        <div class="text-center" data-raw-date="${dateStr}">
            <span class="block text-slate-700 font-bold text-xs whitespace-nowrap">${highlightedDate}</span>
            <span class="inline-block text-slate-500 font-bold text-[9px] bg-slate-100 border border-slate-200/60 rounded px-1.5 py-0.5 mt-[2px] whitespace-nowrap leading-none">${highlightedTime}</span>
        </div>
    `;
}

// Propuesta 2: Un solo renglón lineal con fecha y hora separadas por un punto elegante
function formatDateProposal2(dateStr) {
    const parsed = parseDateTime(dateStr);
    if (!parsed) return '';
    
    return `
        <div class="text-center text-xs text-slate-600 whitespace-nowrap font-medium" data-raw-date="${dateStr}">
            <span>${parsed.date}</span>
            <span class="mx-1 text-slate-300">•</span>
            <span class="text-slate-500 font-bold">${parsed.time}</span>
        </div>
    `;
}

// Alternar menú desplegable de exportaciones
function toggleExportMenu() {
    const menu = document.getElementById("export-menu");
    menu.classList.toggle("hidden");
}

// Exportación Nativa a CSV (Codificación con UTF-8 BOM para soporte de acentos en Excel)
function exportToCSV() {
    document.getElementById("export-menu").classList.add("hidden");
    const headers = ["Nómina", "Creador", "Unidad", "Departamento", "Planta", "Problema", "Detalle", "Fecha Creación", "Resolución", "Tipo de Falla", "Fecha Cerrado", "Técnico", "Estado"];
    const csvRows = [headers.join(",")];
    
    filteredData.forEach(row => {
        let tipoFallaDesc = '-';
        if (row.TipoResolucionId) {
            const match = tiposResolucion.find(item => String(item.Id) === String(row.TipoResolucionId));
            tipoFallaDesc = match ? (match.Nombre || match.Descripcion || match.TipoResolucion || '') : '';
        }

        const values = [
            row.NominaCreador || '',
            row.Nombre || '',
            row.Unidad || '',
            row.Departamento || '',
            row.Planta || row.planta || '',
            row.ProblemaDescripcion || '',
            row.Detalle || '',
            row.FechaCreacion || '',
            row.Resolucion || '',
            tipoFallaDesc,
            row.FechaCerrado || '',
            row.ResolucionNombre || '',
            row.Estado || ''
        ];
        
        // Escapar comillas dobles y saltos de línea para el formato CSV
        const cleanValues = values.map(val => {
            let text = String(val).replace(/"/g, '""').replace(/\r?\n|\r/g, " ");
            return `"${text}"`;
        });
        
        csvRows.push(cleanValues.join(","));
    });
    
    const csvContent = "\uFEFF" + csvRows.join("\n"); // UTF-8 BOM
    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = `reporte_fallas_${document.getElementById("fecha").value}.csv`;
    a.click();
    URL.revokeObjectURL(url);
}

// Exportación Nativa a Excel compatible (HTML compatible con XML de Microsoft Excel para cuadrícula limpia)
function exportToExcel() {
    document.getElementById("export-menu").classList.add("hidden");
    let html = `
        <html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">
        <head>
            <meta charset="utf-8">
            <!--[if gte mso 9]>
            <xml>
                <x:ExcelWorkbook>
                    <x:ExcelWorksheets>
                        <x:ExcelWorksheet>
                            <x:Name>Reportes de Fallas</x:Name>
                            <x:WorksheetOptions>
                                <x:DisplayGridlines/>
                            </x:WorksheetOptions>
                        </x:ExcelWorksheet>
                    </x:ExcelWorksheets>
                </x:ExcelWorkbook>
            </xml>
            <![endif]-->
        </head>
        <body>
            <table border="1">
                <thead>
                    <tr style="background-color: #1f4e78; color: #ffffff; font-weight: bold;">
                        <th>Nómina</th>
                        <th>Creador</th>
                        <th>Unidad</th>
                        <th>Departamento</th>
                        <th>Planta</th>
                        <th>Problema</th>
                        <th>Detalle</th>
                        <th>Fecha Creación</th>
                        <th>Resolución</th>
                        <th>Tipo de Falla</th>
                        <th>Fecha Cerrado</th>
                        <th>Técnico</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
    `;

    filteredData.forEach(row => {
        let tipoFallaDesc = '-';
        if (row.TipoResolucionId) {
            const match = tiposResolucion.find(item => String(item.Id) === String(row.TipoResolucionId));
            tipoFallaDesc = match ? (match.Nombre || match.Descripcion || match.TipoResolucion || '') : '';
        }

        html += `
            <tr>
                <td style="text-align: center;">${row.NominaCreador || ''}</td>
                <td>${row.Nombre || ''}</td>
                <td style="text-align: center;">${row.Unidad || ''}</td>
                <td style="text-align: center;">${row.Departamento || ''}</td>
                <td style="text-align: center;">${row.Planta || row.planta || ''}</td>
                <td style="text-align: center;">${row.ProblemaDescripcion || ''}</td>
                <td>${row.Detalle || ''}</td>
                <td style="text-align: center;">${row.FechaCreacion || ''}</td>
                <td>${row.Resolucion || ''}</td>
                <td style="text-align: center;">${tipoFallaDesc}</td>
                <td style="text-align: center;">${row.FechaCerrado || ''}</td>
                <td>${row.ResolucionNombre || ''}</td>
                <td style="text-align: center;">${row.Estado || ''}</td>
            </tr>
        `;
    });

    html += `
                </tbody>
            </table>
        </body>
        </html>
    `;

    const blob = new Blob([html], { type: 'application/vnd.ms-excel;charset=utf-8' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = `reporte_fallas_${document.getElementById("fecha").value}.xls`;
    a.click();
    URL.revokeObjectURL(url);
}

// Abrir Modal de Resolución
function openModal(id) {
    const report = allData.find(row => row.Id == id);
    if (!report) return;
    
    // Cargar datos en el modal
    document.getElementById("modal-report-id").value = report.Id;
    document.getElementById("modal-creador").textContent = report.Nombre || '-';
    document.getElementById("modal-nomina").textContent = report.NominaCreador || '-';
    
    const depto = report.Departamento || '-';
    const planta = report.Planta || report.planta || '-';
    document.getElementById("modal-unidad-depto").textContent = `${report.Unidad || '-'} / ${depto} / ${planta}`;
    document.getElementById("modal-problema").textContent = report.ProblemaDescripcion || '-';
    document.getElementById("modal-detalle").textContent = report.Detalle || '-';
    
    // Cargar catálogo de tipos de resolución en el select
    const select = document.getElementById("modal-tipo-resolucion");
    let options = '<option value="">Seleccione clasificación...</option>';
    tiposResolucion.forEach(item => {
        const label = item.Nombre || item.Descripcion || item.TipoResolucion || item.text;
        if (label) {
            options += `<option value="${item.Id}">${label}</option>`;
        }
    });
    select.innerHTML = options;
    
    // Limpiar campos de entrada
    select.value = "";
    document.getElementById("modal-resolucion-texto").value = "";
    
    // Mostrar modal
    const modal = document.getElementById("modal-resolucion");
    modal.classList.remove("hidden");
    document.body.style.overflow = "hidden"; // Detener scroll de la página principal
}

// Cerrar Modal
function closeModal() {
    const modal = document.getElementById("modal-resolucion");
    modal.classList.add("hidden");
    document.body.style.overflow = ""; // Restaurar scroll
}

// Guardar Resolución Técnica (Llamado PUT)
function saveFollowUp() {
    const id = document.getElementById("modal-report-id").value;
    const tipoResolucionId = document.getElementById("modal-tipo-resolucion").value;
    const resolucion = document.getElementById("modal-resolucion-texto").value.trim();
    
    if (!tipoResolucionId) {
        Swal.fire({
            icon: "info",
            title: "Clasificación requerida",
            text: "Seleccione el tipo o clasificación de la falla."
        });
        return;
    }
    
    if (!resolucion) {
        Swal.fire({
            icon: "info",
            title: "Resolución requerida",
            text: "Escriba los comentarios de cómo fue solucionado el incidente."
        });
        return;
    }

    Swal.fire({
        title: "¿Quieres guardar el registro? Ya no se podrá editar",
        text: "Esta acción cambiará el estado a Atendido de manera permanente.",
        icon: "question",
        showDenyButton: true,
        confirmButtonText: "Guardar",
        denyButtonText: 'Cancelar',
        confirmButtonColor: '#1f4e78',
        denyButtonColor: '#6c757d'
    }).then((result) => {
        if (result.isConfirmed) {
            // Mostrar estado de carga en el modal
            const btn = document.querySelector("#modal-resolucion button[onclick='saveFollowUp()']");
            const originalHtml = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = `<i class="fa-solid fa-spinner animate-spin"></i> Guardando...`;

            const formData = new URLSearchParams();
            formData.append("Id", id);
            formData.append("Resolucion", resolucion);
            formData.append("TipoResolucionId", tipoResolucionId);
            formData.append("NominaResolucion", loggedUserNomina);

            fetch(`${host}/Master-API/ses/FTU/ActualizarResolucion`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: formData.toString()
            })
            .then(res => res.json())
            .then(respuesta => {
                btn.disabled = false;
                btn.innerHTML = originalHtml;
                
                if (!respuesta.error) {
                    Swal.fire({
                        title: "¡Guardado!",
                        text: respuesta.message || "Seguimiento guardado correctamente.",
                        icon: "success"
                    });
                    closeModal();
                    loadData(); // Recargar datos
                } else {
                    Swal.fire({
                        icon: "error",
                        title: "Error",
                        text: respuesta.message || respuesta.error || "Ocurrió un error al guardar."
                    });
                }
            })
            .catch(err => {
                btn.disabled = false;
                btn.innerHTML = originalHtml;
                console.error("Error al guardar resolución:", err);
                Swal.fire({
                    icon: "error",
                    title: "Error",
                    text: "Ocurrió un error al establecer contacto con el servidor."
                });
            });
        }
    });
}
