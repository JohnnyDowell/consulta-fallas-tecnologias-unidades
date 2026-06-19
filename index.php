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
<html lang="es" dir="ltr">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>SES - Consulta de Fallas de Tecnologías en Unidades</title>
    <link rel="icon" type="image/x-icon" href="./images/favicon.png">
    <!-- Jquery -->
    <script src="https://code.jquery.com/jquery-3.6.1.min.js" integrity="sha256-o88AwQnZB+VDvE9tvIXrMQaPlFFSUTR+nldQm1LuPXQ=" crossorigin="anonymous"></script>
    <!-- bootstrap -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/css/bootstrap.min.css" integrity="sha384-gH2yIJqKdNHPEq0n4Mqa/HGKIhSkIHeL5AyhkYV8i59U5AR6csBvApHHNl/vI1Bx" crossorigin="anonymous">
    <!-- Data Table -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.12.1/css/jquery.dataTables.min.css">
    <script src="https://cdn.datatables.net/1.12.1/js/jquery.dataTables.min.js"></script>
    <!-- Data Table Buttons -->
    <script src="libraries/dataTables/Buttons/dataTables.buttons.min.js"></script>
    <script src="libraries/dataTables/Buttons/jszip.min.js"></script>
    <script src="libraries/dataTables/Buttons/pdfmake.min.js"></script>
    <script src="libraries/dataTables/Buttons/vfs_fonts.js"></script>
    <script src="libraries/dataTables/Buttons/buttons.html5.min.js"></script>
    <script src="libraries/dataTables/Buttons/buttons.print.min.js"></script>
    <link href="libraries/dataTables/Buttons/buttons.dataTables.min.css" rel="stylesheet"/>
    <!-- Sweet Alert 2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- Font Awesome (button icons) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css" integrity="sha512-KfkfwYDsLkIlwQp6LFnl8zNdLGxu9YAA1QvwINks4PhcElQSvqcyVLLD9aMhXd13uQjoXtEKNosOWaZqXgel0g==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <!-- Custom Style -->
    <link href="css/tableStyle/style.css" rel="stylesheet"/>
    <link rel="stylesheet" href="css/styles.css?v=<?php echo file_exists('css/styles.css') ? filemtime('css/styles.css') : '1'; ?>">
  </head>
  <body>
    <header class="py-2">
      <div class="container position-relative">
        <h1 class="text-white">SES - Fallas de Tecnologías en Unidades</h1>
        <input name="fecha" value="" id="fecha" class="position-absolute t-0 r-0 form-control search-date" type="month" onchange="loadData(this.value)">
        <div class="d-flex justify-content-start">
          <div class="align-items-center d-flex justify-content-center">
            <span class="bold text-info">Usuario:</span>
            &nbsp;
            <span class="text-white">
              <?php echo htmlspecialchars($nombreUsuario); ?>
            </span>
            &nbsp;
            &nbsp;
            <a href="login/" class="text-warning ms-3">
              Cerrar sesión
            </a>
          </div>
        </div>
      </div>
    </header>
    <div class="container-fluid main py-3">
      <div class="card p-3">
        <div class="table-responsive">
          <table id="reportsTable" class="blueTable" style="width: 100%;">
          </table>
        </div>
      </div>
    </div>
    <footer class="p-4">
      <div class="container">
        <span class="text-white">
            Settepi Tijuana - Administración y Nuevos Proyectos - 2026
        </span>
      </div>
    </footer>
  </body>
  <script type="text/javascript">
    var table;
    const host = "https://ses.lidcorp.mx";
    const apiBaseUrl = host + "/Master-API/ses/FTU/ConsultarPorMes";
    const nominaUsuario = "<?php echo $nominaUsuario; ?>";
    const tienePermiso = <?php echo $permisoUsuario ? 'true' : 'false'; ?>;
    var tiposResolucion = [];

    // Helper para formatear fechas a diseño de doble línea con iconos
    function formatVisualDate(dateStr) {
      if (!dateStr) return '';
      const parts = dateStr.split(' ');
      if (parts.length < 2) return dateStr;
      
      const dateParts = parts[0].split('-');
      if (dateParts.length < 3) return dateStr;
      
      const y = dateParts[0];
      const m = parseInt(dateParts[1], 10) - 1;
      const d = parseInt(dateParts[2], 10);
      
      const months = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
      const formattedDate = `${d} ${months[m]}, ${y}`;
      
      const timeParts = parts[1].split(':');
      if (timeParts.length < 2) return dateStr;
      
      let hour = parseInt(timeParts[0], 10);
      const minute = timeParts[1];
      const ampm = hour >= 12 ? 'PM' : 'AM';
      hour = hour % 12;
      hour = hour ? hour : 12;
      const formattedTime = `${hour}:${minute} ${ampm}`;
      
      return `<div class="text-center d-inline-block" data-raw-date="${dateStr}">
        <span class="d-block text-dark font-weight-bold" style="font-size: 0.85rem; white-space: nowrap;">
          ${formattedDate}
        </span>
        <span class="d-block text-muted" style="font-size: 0.75rem; margin-top: 2px; white-space: nowrap;">
          ${formattedTime}
        </span>
      </div>`;
    }

    // Inicializar el filtro de mes con el mes actual
    const date = new Date();
    const year = date.toLocaleString('default', { year: 'numeric' });
    const month = date.toLocaleString('default', { month: '2-digit' });
    document.getElementById("fecha").value = year + "-" + month;

    function loadData(value){
      document.title = "SES - Consulta de Fallas - " + $("#fecha")[0].value;

      if(table){
        table.ajax.reload();
      }
      else{
        createTable(apiBaseUrl);
      }
    }

    function createTable(url){
      // Configuración de exportación para botones de DataTables
      const exportFormat = {
        format: {
          body: function (data, row, column, node) {
            // Si la columna es Problema (índice 3), retornar el texto limpio del botón
            if (column === 3) {
              var button = node.querySelector('button');
              return button ? button.textContent.trim() : data;
            }
            // Si la columna es Detalle (índice 4), retornar el texto limpio del div
            if (column === 4) {
              var div = node.querySelector('div');
              return div ? div.textContent.trim() : data;
            }
            // Si la columna es Fecha Cerrado (índice 8) y contiene el botón de guardar
            if (column === 8) {
              var button = node.querySelector('button');
              if (button && button.textContent.includes('Guardar')) {
                return '';
              }
            }
            // Si la columna es Fecha Creación (5) o Fecha Cerrado (8) con formato de doble línea
            if (column === 5 || column === 8) {
              var container = node.querySelector('[data-raw-date]');
              return container ? container.getAttribute('data-raw-date') : data;
            }
            // Si la columna es Resolución (índice 6), exportar el valor del textarea si existe, o el texto del div
            if (column === 6) {
              var textarea = node.querySelector('textarea');
              if (textarea) return textarea.value;
              var div = node.querySelector('div');
              return div ? div.textContent.trim() : data;
            }
            // Si la columna es Tipo de Falla (índice 7), exportar el valor del select si existe
            if (column === 7) {
              var select = node.querySelector('select');
              return select ? select.value : data;
            }
            // Si la columna es Estado (índice 10), exportar el texto limpio sin etiquetas HTML
            if (column === 10) {
              var badge = node.querySelector('.badge');
              return badge ? badge.textContent.trim() : data;
            }
            return data;
          }
        }
      };

      table = $('#reportsTable').DataTable({
        scrollX: false,
        responsive: false,
        autoWidth: false,
        dom: 'Blfrtip',
        buttons: [
          {
              extend:    'copyHtml5',
              text:      '<i class="fa-solid fa-copy gray"></i> Copiar',
              titleAttr: 'Copiar',
              exportOptions: exportFormat
          },
          {
              extend:    'excelHtml5',
              text:      '<i class="fa-solid fa-file-excel green"></i> Excel',
              titleAttr: 'Excel',
              exportOptions: exportFormat
          },
          {
              extend:    'csvHtml5',
              text:      '<i class="fa-solid fa-file-text black"></i> CSV',
              titleAttr: 'CSV',
              exportOptions: exportFormat
          },
          {
              extend:    'pdfHtml5',
              text:      '<i class="fa-solid fa-file-pdf red"></i> PDF',
              titleAttr: 'PDF',
              exportOptions: exportFormat
          },
          {
              extend:    'print',
              text:      '<i class="fa-solid fa-print blue"></i> Imprimir',
              titleAttr: 'Imprimir',
              exportOptions: exportFormat
          }
        ],
        language: {'url': 'https://cdn.datatables.net/plug-ins/a5734b29083/i18n/Spanish.json' },
        ajax: {
          url: url,
          type: 'GET',
          data: function ( d ) {
            const selectedMonthStr = $("#fecha").val(); // Formato YYYY-MM
            if (selectedMonthStr) {
              const parts = selectedMonthStr.split('-');
              d.year = parts[0];
              d.month = parts[1];
            } else {
              const date = new Date();
              d.year = date.getFullYear();
              d.month = String(date.getMonth() + 1).padStart(2, '0');
            }
          },
          dataSrc: function ( json ) {
            const list = json.data || [];
            list.forEach(function(row) {
              row.Estado = (row.FechaCerrado && row.FechaCerrado.trim() !== '' && row.FechaCerrado !== 'null') ? 'Atendido' : 'Por Atender';
            });
            return list;
          }
        },
        initComplete: function(settings, json) {
          // Ajustar las columnas automáticamente después de que la tabla sea inicializada
          setTimeout(function() {
            table.columns.adjust();
          }, 100);
        },

        columns: [
          { "data" : "NominaCreador", "title" : "Nómina", "className": "dt-head-center dt-body-center no-wrap", "width": "1%" },
          { 
            "data" : "Nombre", 
            "title" : "Creador", 
            "className": "dt-head-center dt-body-center", 
            "width": "1%",
            "render": function(data, type, row) {
              if (type === 'display' && data) {
                return `<div style="max-width: 110px; min-width: 80px; text-align: center; white-space: normal; word-break: break-word;">${data}</div>`;
              }
              return data || '';
            }
          },
          { "data" : "Unidad", "title" : "Unidad", "className": "dt-head-center dt-body-center no-wrap", "width": "1%" },
          { 
            "data" : "ProblemaDescripcion", 
            "title" : "Problema",
            "className": "dt-head-center dt-body-center",
            "width": "1%",
            "render": function(data, type, row) {
              const probMap = {
                'falla en camara': { text: 'Falla en Cámara', css: 'badge-camara' },
                'falla en memoria': { text: 'Falla en Memoria', css: 'badge-memoria' },
                'gps': { text: 'GPS', css: 'badge-gps' },
                'dmas': { text: 'DMAS', css: 'badge-dmas' }
              };
              const key = (data || '').toLowerCase().trim();
              const info = probMap[key] || { text: data || '', css: '' };
              if (type === 'display') {
                return `<div class="text-center"><button type="button" class="btn btn-badge ${info.css}" style="white-space: normal !important; max-width: 95px; display: inline-block; line-height: 1.2;">${info.text}</button></div>`;
              }
              if (type === 'filter') {
                return info.text;
              }
              return data;
            }
          },
          { 
            "data" : "Detalle", 
            "title" : "Detalle", 
            "className": "dt-head-center dt-body-center", 
            "width": "60%",
            "render": function(data, type, row) {
              if (type === 'display') {
                return `<div style="min-width: 150px; max-width: 500px; text-align: left; white-space: normal; word-break: break-word;">${data}</div>`;
              }
              return data;
            }
          },
          { 
            "data" : "FechaCreacion", 
            "title" : "Fecha Creación", 
            "className": "dt-head-center dt-body-center no-wrap",
            "width": "1%",
            "render": function(data, type, row) {
              if (type === 'display') {
                return formatVisualDate(data);
              }
              return data;
            }
          },
          { 
            "data" : "Resolucion", 
            "title" : "Resolución", 
            "className": "dt-head-center dt-body-center",
            "width": "30%",
            "render": function(data, type, row) {
              if (tienePermiso && row.Estado === 'Por Atender' && type === 'display') {
                return `<textarea id="Resolucion_${row.Id}" class="form-control" placeholder="Escriba la resolución..." rows="2" style="width: 100%; min-width: 130px; max-width: 260px; font-size: 12px; padding: 4px;"></textarea>`;
              }
              if (type === 'display') {
                return `<div style="min-width: 140px; max-width: 400px; text-align: left; white-space: normal; word-break: break-word;">${data || '-'}</div>`;
              }
              return data || '';
            }
          },
          { 
            "data" : "TipoResolucionId", 
            "title" : "Tipo de Falla", 
            "className": "dt-head-center dt-body-center no-wrap",
            "width": "1%",
            "render": function(data, type, row) {
              if (tienePermiso && row.Estado === 'Por Atender' && type === 'display') {
                let options = '<option value="">Seleccione...</option>';
                tiposResolucion.forEach(function(item) {
                  const label = item.Nombre || item.Descripcion || item.TipoResolucion || item.text;
                  if (label) {
                    options += `<option value="${item.Id}">${label}</option>`;
                  }
                });
                return `<select id="TipoFalla_${row.Id}" class="form-select" style="width: 100px; min-width: 90px; font-size: 12px; padding: 2px 4px; height: auto;">
                          ${options}
                        </select>`;
              }
              if (data && tiposResolucion.length > 0) {
                const match = tiposResolucion.find(function(item) {
                  return String(item.Id) === String(data);
                });
                if (match) {
                  return match.Nombre || match.Descripcion || match.TipoResolucion || data;
                }
              }
              return data || '-';
            }
          },
          { 
            "data" : "FechaCerrado", 
            "title" : "Fecha Cerrado", 
            "className": "dt-head-center dt-body-center no-wrap",
            "width": "1%",
            "render": function(data, type, row) {
              if (tienePermiso && row.Estado === 'Por Atender' && type === 'display') {
                return `<button class="btn btn-success btn-badge" onclick="GuardarSeguimiento(${row.Id})"><i class="fa-regular fa-floppy-disk"></i> Guardar</button>`;
              }
              if (type === 'display') {
                return formatVisualDate(data) || '-';
              }
              return data || '';
            }
          },
          { 
            "data" : "ResolucionNombre", 
            "title" : "Técnico", 
            "className": "dt-head-center dt-body-center",
            "width": "1%",
            "render": function(data, type, row) {
              if (type === 'display') {
                return `<div style="max-width: 110px; min-width: 80px; text-align: center; white-space: normal; word-break: break-word;">${data ? data : '-'}</div>`;
              }
              return data || '';
            }
          },
          { 
            "data" : "Estado", 
            "title" : "Estado", 
            "className": "dt-head-center dt-body-center no-wrap",
            "width": "1%",
            "render": function(data, type, row) {
              if (type === 'display') {
                if (data === 'Atendido') {
                  return `<span class="badge bg-success text-white" style="padding: 6px 12px; border-radius: 12px; font-size: 0.85rem; font-weight: bold;">Atendido</span>`;
                }
                return `<span class="badge bg-warning text-dark" style="padding: 6px 12px; border-radius: 12px; font-size: 0.85rem; font-weight: bold;">Por Atender</span>`;
              }
              return data;
            }
          }
        ]
      });
      
      // Ajustar columnas al redimensionar la ventana
      $(window).on('resize', function () {
        table.columns.adjust();
      });
    }

    function GuardarSeguimiento(id) {
      const resolucion = $("#Resolucion_" + id).val().trim();
      const tipoResolucionId = $("#TipoFalla_" + id).val();

      if (!resolucion) {
        Swal.fire({
          icon: "info",
          title: "Resolución requerida",
          text: "Escriba la resolución del reporte antes de guardar."
        });
        return;
      }

      if (!tipoResolucionId) {
        Swal.fire({
          icon: "info",
          title: "Tipo de resolución requerido",
          text: "Seleccione el tipo de resolución."
        });
        return;
      }

      const nomina = nominaUsuario;

      Swal.fire({
        title: "¿Quieres guardar el registro? Ya no se podrá editar",
        text: "Esta acción cambiará el estado a Atendido de manera permanente.",
        icon: "question",
        showDenyButton: true,
        confirmButtonText: "Guardar",
        denyButtonText: `Cancelar`,
        confirmButtonColor: '#198754',
        denyButtonColor: '#6c757d'
      }).then((result) => {
        if (result.isConfirmed) {
          $.ajax({
            url: host + '/Master-API/ses/FTU/ActualizarResolucion',
            type: 'PUT',
            data: {
              Id: id,
              Resolucion: resolucion,
              TipoResolucionId: tipoResolucionId,
              NominaResolucion: nomina
            },
            dataType: 'json',
            success: function(respuesta) {
              if(!respuesta.error){
                Swal.fire({
                  title: "¡Guardado!",
                  text: respuesta.message || "Seguimiento guardado correctamente.",
                  icon: "success"
                });
                table.ajax.reload();
              }
              else{
                Swal.fire({
                  icon: "error",
                  title: "Error",
                  text: respuesta.message || respuesta.error || "Ocurrió un error al guardar."
                });
              }
            },
            error: function(xhr, status, error) {
              Swal.fire({
                icon: "error",
                title: "Error",
                text: "Ocurrió un error al procesar la solicitud."
              });
            }
          });
        }
      });
    }

    // Cargar catálogo de tipos de resolución antes de cargar los datos de la tabla
    function cargarTiposResolucion(callback) {
      $.ajax({
        url: host + "/Master-API/ses/FTU/GetTiposResolucionFTU",
        type: 'GET',
        dataType: 'json',
        success: function(respuesta) {
          tiposResolucion = respuesta.data || respuesta || [];
          if (callback) callback();
        },
        error: function(xhr, status, error) {
          console.error("Error al obtener tipos de resolución de producción:", error);
          if (callback) callback();
        }
      });
    }

    // Desactivar el manejo automático de errores de DataTables (muestra advertencias en alert)
    $.fn.dataTable.ext.errMode = 'none';

    // Carga inicial de datos tras obtener el catálogo
    cargarTiposResolucion(function() {
      loadData();
    });
  </script>
</html>
