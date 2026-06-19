<?php
session_start();

if (empty($_SESSION["usuario"])) {
    header("Location: login/");
    exit;
}

$nominaUsuario = $_SESSION["nomina"];
$nombreUsuario = $_SESSION["usuario"];
$permisoUsuario = $_SESSION["permiso"];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SES - Consulta de Fallas de Tecnologías en Unidades</title>
    <link rel="icon" type="image/x-icon" href="./images/favicon.png">
    
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Sweet Alert 2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css" integrity="sha512-KfkfwYDsLkIlwQp6LFnl8zNdLGxu9YAA1QvwINks4PhcElQSvqcyVLLD9aMhXd13uQjoXtEKNosOWaZqXgel0g==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap');
        body {
            font-family: 'Outfit', 'Segoe UI', sans-serif;
        }
        table tbody td {
            vertical-align: middle !important;
        }
        footer {
            border: none !important;
            border-top: none !important;
            box-shadow: none !important;
            outline: none !important;
        }

        
        /* Ocultar elementos innecesarios al imprimir en PDF */
        @media print {
            body {
                background: white !important;
                color: black !important;
                padding: 0 !important;
                font-size: 10px !important;
            }
            header, footer, .kpi-section, .filter-section, .no-print, button, .modal-backdrop {
                display: none !important;
            }
            main {
                padding: 0 !important;
            }
            .card {
                box-shadow: none !important;
                border: none !important;
                background: transparent !important;
                padding: 0 !important;
            }
            table {
                width: 100% !important;
                border-collapse: collapse !important;
            }
            th, td {
                border: 1px solid #94a3b8 !important;
                padding: 6px 4px !important;
                font-size: 9px !important;
                color: black !important;
                white-space: normal !important;
            }
            .badge {
                border: none !important;
                background: transparent !important;
                color: black !important;
                padding: 0 !important;
                font-weight: bold !important;
            }
        }
    </style>
</head>
<body class="bg-slate-50 min-h-screen flex flex-col">

    <!-- Header Banner FOG Style -->
    <header class="relative bg-white border-b border-slate-200 select-none z-40 shadow-sm shrink-0 no-print">
        <div class="max-w-[1600px] w-[98%] mx-auto flex flex-col sm:flex-row items-center justify-between px-[clamp(10px,1.2vw,16px)] py-2 gap-3 sm:gap-0">
            <!-- Left Brand Logo -->
            <div class="flex items-center gap-4">
                <div class="bg-[#1f4e78] text-[#ffc000] font-black text-2xl px-4 py-1.5 rounded shadow-sm border border-[#163754]">
                    FTU
                </div>
                <div class="flex flex-col items-start leading-none">
                    <h1 class="text-base font-bold text-[#1f4e78] tracking-tight mb-1">FALLAS DE TECNOLOGÍAS EN UNIDADES</h1>
                    <span class="text-xs text-slate-500 font-medium">Control de Seguimiento y Cierre Operativo</span>
                </div>
            </div>
            
            <!-- Right Profile Info & Logout -->
            <div class="flex items-center gap-4 text-xs">
                <!-- User Profile Card -->
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center font-bold text-[#1f4e78] border border-slate-200 uppercase shrink-0">
                        <?php echo substr($nombreUsuario, 0, 1); ?>
                    </div>
                    <div class="flex flex-col items-start leading-tight">
                        <span class="font-bold text-slate-700"><?php echo htmlspecialchars($nombreUsuario); ?></span>
                        <span class="text-[9px] text-[#1f4e78] font-bold uppercase tracking-wider">Nómina: <?php echo htmlspecialchars($nominaUsuario); ?></span>
                    </div>
                </div>
                
                <div class="h-6 w-px bg-slate-200 hidden sm:block"></div>
                
                <!-- Logout Button -->
                <a href="login/" title="Cerrar sesión" class="flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg border border-slate-200 hover:border-red-100 hover:bg-red-50 text-slate-600 hover:text-red-600 transition-colors font-semibold">
                    <i class="fa-solid fa-right-from-bracket"></i>
                    <span>Salir</span>
                </a>
            </div>
        </div>
        <!-- Yellow Bottom Accent Line -->
        <div class="h-[4px] bg-[#ffc000] w-full"></div>
    </header>

    <!-- Main Container -->
    <main class="flex-1 max-w-[1600px] w-[98%] mx-auto p-[clamp(10px,1.2vw,16px)] pb-0 flex flex-col gap-6">
        


        <!-- Main Dashboard Card -->
        <section class="card bg-white rounded-2xl shadow-md border border-slate-200 border-t-4 border-t-[#1f4e78] p-[clamp(10px,1.2vw,14px)] flex flex-col flex-1 gap-2">
            
            <!-- Filter Bar & Actions (No Print) -->
            <div class="filter-section flex flex-col lg:flex-row justify-between items-stretch lg:items-center gap-2 pb-2 border-b border-slate-100 no-print">
                <!-- Left: Status Tabs -->
                <div class="flex border-b border-slate-200 p-0.5 bg-slate-100/80 rounded-xl max-w-md w-full">
                    <button onclick="filterStatus('all')" id="tab-all" class="flex-1 py-1.5 text-xs font-bold rounded-lg text-[#1f4e78] bg-[#ffc000] shadow-sm transition-all flex items-center justify-center gap-1.5">
                        Todos
                        <span class="bg-slate-200 text-slate-700 px-1.5 py-0.5 rounded text-[10px]" id="count-all">0</span>
                    </button>
                    <button onclick="filterStatus('pending')" id="tab-pending" class="flex-1 py-1.5 text-xs font-bold rounded-lg text-slate-600 hover:text-slate-800 transition-all flex items-center justify-center gap-1.5">
                        Por Atender
                        <span class="bg-amber-100 text-amber-800 px-1.5 py-0.5 rounded text-[10px]" id="count-pending">0</span>
                    </button>
                    <button onclick="filterStatus('resolved')" id="tab-resolved" class="flex-1 py-1.5 text-xs font-bold rounded-lg text-slate-600 hover:text-slate-800 transition-all flex items-center justify-center gap-1.5">
                        Atendidos
                        <span class="bg-emerald-100 text-emerald-800 px-1.5 py-0.5 rounded text-[10px]" id="count-resolved">0</span>
                    </button>
                </div>
                
                <!-- Right: Search, Date & Export buttons -->
                <div class="flex flex-wrap items-center gap-3">
                    <!-- Date Picker -->
                    <div class="relative inline-block text-left" id="custom-month-picker">
                        <!-- Trigger Button -->
                        <button onclick="toggleMonthPicker()" id="month-picker-btn" class="px-3 py-2 bg-slate-50 border border-slate-300 hover:bg-slate-100 rounded-xl text-xs font-bold text-[#1f4e78] flex items-center gap-2 shadow-sm transition-colors focus:outline-none">
                            <i class="fa-regular fa-calendar-days text-slate-400"></i>
                            <span id="selected-month-label">Cargando...</span>
                            <i class="fa-solid fa-chevron-down text-[10px] text-slate-400"></i>
                        </button>
                        
                        <!-- Dropdown Panel -->
                        <div id="month-picker-dropdown" class="hidden absolute right-0 mt-2 w-60 bg-white rounded-2xl shadow-xl border border-slate-200 p-4 z-50 animate-fade-in">
                            <!-- Year Header Control -->
                            <div class="flex items-center justify-between pb-3 border-b border-slate-100 select-none">
                                <button onclick="changePickerYear(-1)" class="w-7 h-7 rounded-lg hover:bg-slate-100 flex items-center justify-center text-slate-500 hover:text-slate-800 transition-colors">
                                    <i class="fa-solid fa-chevron-left text-[10px]"></i>
                                </button>
                                <span id="picker-year-label" class="font-bold text-slate-800 text-sm">2026</span>
                                <button onclick="changePickerYear(1)" class="w-7 h-7 rounded-lg hover:bg-slate-100 flex items-center justify-center text-slate-500 hover:text-slate-800 transition-colors">
                                    <i class="fa-solid fa-chevron-right text-[10px]"></i>
                                </button>
                            </div>
                            
                            <!-- Months Grid -->
                            <div class="grid grid-cols-3 gap-1.5 pt-3 select-none">
                                <button onclick="selectPickerMonth(0)" class="month-btn py-1.5 text-xs font-bold rounded-lg hover:bg-slate-50 text-slate-700 transition-all">Ene</button>
                                <button onclick="selectPickerMonth(1)" class="month-btn py-1.5 text-xs font-bold rounded-lg hover:bg-slate-50 text-slate-700 transition-all">Feb</button>
                                <button onclick="selectPickerMonth(2)" class="month-btn py-1.5 text-xs font-bold rounded-lg hover:bg-slate-50 text-slate-700 transition-all">Mar</button>
                                <button onclick="selectPickerMonth(3)" class="month-btn py-1.5 text-xs font-bold rounded-lg hover:bg-slate-50 text-slate-700 transition-all">Abr</button>
                                <button onclick="selectPickerMonth(4)" class="month-btn py-1.5 text-xs font-bold rounded-lg hover:bg-slate-50 text-slate-700 transition-all">May</button>
                                <button onclick="selectPickerMonth(5)" class="month-btn py-1.5 text-xs font-bold rounded-lg hover:bg-slate-50 text-slate-700 transition-all">Jun</button>
                                <button onclick="selectPickerMonth(6)" class="month-btn py-1.5 text-xs font-bold rounded-lg hover:bg-slate-50 text-slate-700 transition-all">Jul</button>
                                <button onclick="selectPickerMonth(7)" class="month-btn py-1.5 text-xs font-bold rounded-lg hover:bg-slate-50 text-slate-700 transition-all">Ago</button>
                                <button onclick="selectPickerMonth(8)" class="month-btn py-1.5 text-xs font-bold rounded-lg hover:bg-slate-50 text-slate-700 transition-all">Sep</button>
                                <button onclick="selectPickerMonth(9)" class="month-btn py-1.5 text-xs font-bold rounded-lg hover:bg-slate-50 text-slate-700 transition-all">Oct</button>
                                <button onclick="selectPickerMonth(10)" class="month-btn py-1.5 text-xs font-bold rounded-lg hover:bg-slate-50 text-slate-700 transition-all">Nov</button>
                                <button onclick="selectPickerMonth(11)" class="month-btn py-1.5 text-xs font-bold rounded-lg hover:bg-slate-50 text-slate-700 transition-all">Dic</button>
                            </div>
                        </div>
                        <input type="hidden" id="fecha">
                    </div>
                    
                    <!-- Search Input -->
                    <div class="relative flex-1 min-w-[200px] sm:flex-none">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400">
                            <i class="fa-solid fa-magnifying-glass"></i>
                        </span>
                        <input type="text" id="search" oninput="handleSearch(this.value)" placeholder="Buscar reportes..." class="w-full pl-9 pr-4 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs font-medium text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#1f4e78] focus:border-[#1f4e78] transition-colors">
                    </div>
                    
                    <!-- Export Dropdown -->
                    <div class="relative">
                        <button onclick="toggleExportMenu()" id="btn-export" class="px-3 py-2 bg-white border border-slate-300 hover:bg-slate-50 rounded-xl text-xs font-bold text-slate-700 flex items-center gap-1.5 transition-colors focus:outline-none shadow-sm">
                            <i class="fa-solid fa-download"></i>
                            <span>Exportar</span>
                            <i class="fa-solid fa-chevron-down text-[10px] text-slate-400"></i>
                        </button>
                        <!-- Dropdown Menu -->
                        <div id="export-menu" class="hidden absolute right-0 mt-2 w-44 bg-white rounded-xl shadow-lg border border-slate-100 py-1.5 z-50 animate-fade-in">
                            <button onclick="exportToExcel()" class="w-full px-4 py-2 text-left text-xs font-semibold text-slate-700 hover:bg-slate-50 hover:text-emerald-600 flex items-center gap-2 transition-colors">
                                <i class="fa-solid fa-file-excel text-emerald-500 text-sm"></i> Descargar Excel
                            </button>
                            <button onclick="exportToCSV()" class="w-full px-4 py-2 text-left text-xs font-semibold text-slate-700 hover:bg-slate-50 hover:text-slate-900 flex items-center gap-2 transition-colors">
                                <i class="fa-solid fa-file-csv text-slate-500 text-sm"></i> Descargar CSV
                            </button>
                            <button onclick="window.print()" class="w-full px-4 py-2 text-left text-xs font-semibold text-slate-700 hover:bg-slate-50 hover:text-red-600 flex items-center gap-2 transition-colors">
                                <i class="fa-solid fa-file-pdf text-red-500 text-sm"></i> Descargar PDF / Imprimir
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Table Container -->
            <div class="overflow-x-auto flex-1 border border-slate-100 rounded-xl">
                <table id="reports-table" class="min-w-full text-[13px] text-slate-700 font-medium">
                    <thead class="bg-slate-50 border-b border-slate-200 select-none text-[#1f4e78] font-bold text-xs">
                        <tr>
                            <th onclick="sortBy('NominaCreador')" class="px-2 py-1.5 text-center cursor-pointer hover:bg-slate-100 transition-colors whitespace-nowrap">
                                Nómina <span id="sort-NominaCreador"></span>
                            </th>
                            <th onclick="sortBy('Nombre')" class="px-2 py-1.5 text-center cursor-pointer hover:bg-slate-100 transition-colors whitespace-nowrap">
                                Creador <span id="sort-Nombre"></span>
                            </th>
                            <th onclick="sortBy('Unidad')" class="px-2 py-1.5 text-center cursor-pointer hover:bg-slate-100 transition-colors whitespace-nowrap">
                                Unidad <span id="sort-Unidad"></span>
                            </th>
                            <th onclick="sortBy('Departamento')" class="px-2 py-1.5 text-center cursor-pointer hover:bg-slate-100 transition-colors whitespace-nowrap">
                                Departamento <span id="sort-Departamento"></span>
                            </th>
                            <th onclick="sortBy('Planta')" class="px-2 py-1.5 text-center cursor-pointer hover:bg-slate-100 transition-colors whitespace-nowrap">
                                Planta <span id="sort-Planta"></span>
                            </th>
                            <th onclick="sortBy('ProblemaDescripcion')" class="px-2 py-1.5 text-center cursor-pointer hover:bg-slate-100 transition-colors whitespace-nowrap">
                                Problema <span id="sort-ProblemaDescripcion"></span>
                            </th>
                            <th class="px-2 py-1.5 text-left whitespace-nowrap">Detalle</th>
                            <th onclick="sortBy('FechaCreacion')" class="px-2 py-1.5 text-center cursor-pointer hover:bg-slate-100 transition-colors whitespace-nowrap">
                                Fecha Creación <span id="sort-FechaCreacion"></span>
                            </th>
                            <th class="px-2 py-1.5 text-left whitespace-nowrap">Resolución</th>
                            <th onclick="sortBy('TipoResolucionDescripcion')" class="px-2 py-1.5 text-center cursor-pointer hover:bg-slate-100 transition-colors whitespace-nowrap">
                                Tipo de Falla <span id="sort-TipoResolucionDescripcion"></span>
                            </th>
                            <th onclick="sortBy('FechaCerrado')" class="px-2 py-1.5 text-center cursor-pointer hover:bg-slate-100 transition-colors whitespace-nowrap">
                                Fecha Cerrado <span id="sort-FechaCerrado"></span>
                            </th>
                            <th onclick="sortBy('ResolucionNombre')" class="px-2 py-1.5 text-center cursor-pointer hover:bg-slate-100 transition-colors whitespace-nowrap">
                                Técnico <span id="sort-ResolucionNombre"></span>
                            </th>
                            <th onclick="sortBy('Estado')" class="px-2 py-1.5 text-center cursor-pointer hover:bg-slate-100 transition-colors whitespace-nowrap">
                                Estado <span id="sort-Estado"></span>
                            </th>
                        </tr>
                    </thead>
                    <tbody id="table-body" class="divide-y divide-slate-100">
                        <!-- Loaded Dynamically by JS -->
                    </tbody>
                </table>
            </div>
            
            <!-- Table Footer / Pagination (No Print) -->
            <div class="flex flex-col sm:flex-row justify-between items-center gap-2 pt-1.5 text-xs font-semibold text-slate-500 select-none no-print">
                <span id="pagination-info">Mostrando 0 a 0 de 0 registros</span>
                <div class="flex items-center gap-1.5" id="pagination-buttons">
                    <!-- Loaded Dynamically by JS -->
                </div>
            </div>
            
        </section>
        
    </main>

    <!-- Modal Form for resolution capture -->
    <div id="modal-resolucion" class="hidden fixed inset-0 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center z-50 p-4 no-print transition-all">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg overflow-hidden border border-slate-200 flex flex-col animate-fade-in">
            <!-- Header: FOG Blue Gradient with Yellow Accent Line -->
            <div class="px-6 py-4 bg-gradient-to-r from-[#1f4e78] to-[#163754] flex items-center justify-between text-white relative">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-[#ffc000] text-[#1f4e78] flex items-center justify-center font-bold shadow-sm">
                        <i class="fa-solid fa-wrench text-sm"></i>
                    </div>
                    <div class="flex flex-col">
                        <h3 class="font-bold text-xs uppercase tracking-wider">Cierre de Seguimiento</h3>
                        <span class="text-[9px] text-[#ffc000] font-bold uppercase tracking-widest mt-0.5">Captura Técnica</span>
                    </div>
                </div>
                <button onclick="closeModal()" class="text-white/80 hover:text-white text-2xl font-bold focus:outline-none transition-colors">&times;</button>
            </div>
            <!-- Yellow Line Divider -->
            <div class="h-[4px] bg-[#ffc000] w-full"></div>

            <!-- Body Content -->
            <div class="p-6 space-y-5 max-h-[70vh] overflow-y-auto bg-slate-50/50">
                <!-- Metadata Info Grid -->
                <div class="grid grid-cols-2 gap-4 bg-white p-4 rounded-xl text-xs font-semibold text-slate-600 border border-slate-200/60 shadow-sm relative overflow-hidden">
                    <div class="absolute left-0 top-0 bottom-0 w-[4px] bg-[#1f4e78]"></div>
                    <div>
                        <span class="text-[9px] text-slate-400 block uppercase tracking-wider font-bold mb-0.5">
                            <i class="fa-solid fa-user mr-1 text-slate-400"></i> Creador del reporte
                        </span>
                        <span class="text-slate-800 font-bold" id="modal-creador">-</span>
                    </div>
                    <div>
                        <span class="text-[9px] text-slate-400 block uppercase tracking-wider font-bold mb-0.5">
                            <i class="fa-solid fa-id-card mr-1 text-slate-400"></i> Nómina
                        </span>
                        <span class="text-slate-800 font-bold" id="modal-nomina">-</span>
                    </div>
                    <div>
                        <span class="text-[9px] text-slate-400 block uppercase tracking-wider font-bold mb-0.5">
                            <i class="fa-solid fa-bus mr-1 text-slate-400"></i> Unidad / Depto / Planta
                        </span>
                        <span class="text-slate-800 font-bold" id="modal-unidad-depto">-</span>
                    </div>
                    <div>
                        <span class="text-[9px] text-slate-400 block uppercase tracking-wider font-bold mb-0.5">
                            <i class="fa-solid fa-triangle-exclamation mr-1 text-slate-400"></i> Problema
                        </span>
                        <span class="text-slate-800 font-bold" id="modal-problema">-</span>
                    </div>
                    <div class="col-span-2 pt-2 border-t border-slate-100">
                        <span class="text-[9px] text-slate-400 block uppercase tracking-wider font-bold mb-0.5">
                            <i class="fa-solid fa-file-text mr-1 text-slate-400"></i> Detalle observado
                        </span>
                        <p class="text-slate-700 font-medium leading-relaxed mt-0.5 bg-slate-50 p-2.5 rounded-lg border border-slate-100/80 whitespace-pre-wrap text-[11px]" id="modal-detalle">-</p>
                    </div>
                </div>
                
                <!-- Inputs Form -->
                <div class="space-y-4">
                    <input type="hidden" id="modal-report-id">
                    
                    <div class="flex flex-col gap-1.5">
                        <label class="text-[10px] font-bold text-slate-500 uppercase tracking-wider flex items-center gap-1">
                            <i class="fa-solid fa-tags text-[#1f4e78]"></i>
                            <span>Clasificación de Falla / Daño *</span>
                        </label>
                        <select id="modal-tipo-resolucion" class="w-full border border-slate-300 rounded-xl p-2.5 focus:ring-2 focus:ring-[#1f4e78] focus:border-[#1f4e78] focus:outline-none text-xs font-semibold bg-white shadow-sm transition-all">
                            <!-- Loaded Dynamically -->
                        </select>
                    </div>
                    
                    <div class="flex flex-col gap-1.5">
                        <label class="text-[10px] font-bold text-slate-500 uppercase tracking-wider flex items-center gap-1">
                            <i class="fa-solid fa-comment-dots text-[#1f4e78]"></i>
                            <span>Resolución / Solución técnica *</span>
                        </label>
                        <textarea id="modal-resolucion-texto" class="w-full border border-slate-300 rounded-xl p-2.5 focus:ring-2 focus:ring-[#1f4e78] focus:border-[#1f4e78] focus:outline-none text-xs font-medium bg-white shadow-sm transition-all resize-y" rows="4" placeholder="Escriba detalladamente cómo se solucionó la falla técnica..."></textarea>
                    </div>
                </div>
                
                <!-- Info Hint -->
                <div class="p-3 bg-amber-50 border border-amber-200 rounded-xl flex items-start gap-2.5 text-[11px] text-amber-800 font-medium shadow-sm">
                    <i class="fa-solid fa-circle-info text-amber-600 mt-0.5 text-sm shrink-0"></i>
                    <span>Al hacer clic en <strong>Guardar y Cerrar</strong>, se registrará su nómina como técnico resolutor y el estado del reporte cambiará permanentemente a <strong>Atendido</strong>.</span>
                </div>
            </div>
            
            <!-- Footer Actions -->
            <div class="px-6 py-4 border-t border-slate-100 bg-white flex justify-end gap-2 text-xs shrink-0 font-bold shadow-inner">
                <button onclick="closeModal()" class="px-4 py-2 border border-slate-300 rounded-xl hover:bg-slate-50 text-slate-600 transition-colors">Cancelar</button>
                <button onclick="saveFollowUp()" class="px-4 py-2 bg-[#1f4e78] hover:bg-[#163754] text-white rounded-xl transition-all shadow hover:shadow-md active:scale-[0.98] flex items-center gap-1.5">
                    <i class="fa-regular fa-floppy-disk"></i>
                    <span>Guardar y Cerrar</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Footer (No Print) -->
    <footer class="py-2 mt-auto no-print text-center select-none shrink-0 bg-transparent">
        <div class="max-w-[1600px] w-[98%] mx-auto px-[clamp(10px,1.2vw,16px)] flex justify-center items-center">
            <span class="text-[11px] text-slate-400 font-semibold tracking-wider uppercase">
                Settepi Tijuana - Administración y Nuevos Proyectos - 2026
            </span>
        </div>
    </footer>

    <!-- Script Application Logic -->
    <script>
        const loggedUserNomina = "<?php echo $nominaUsuario; ?>";
        const userHasEditPermission = <?php echo $permisoUsuario ? 'true' : 'false'; ?>;
    </script>
    <script src="js/app.js?v=<?php echo time(); ?>"></script>
</body>
</html>
