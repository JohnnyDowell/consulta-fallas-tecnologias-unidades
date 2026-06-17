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
              Administrador (Demo)
            </span>
          </div>
        </div>
      </div>
    </header>
    <div class="container-fluid main py-3">
      <div class="card p-3">
        <table id="reportsTable" class="blueTable" style="width: 100%;">
        </table>
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
    const mockJsonUrl = "data/reportes.json";

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
        createTable(mockJsonUrl);
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
            // Si la columna es Resolución (índice 6), exportar el valor del textarea si existe
            if (column === 6) {
              var textarea = node.querySelector('textarea');
              return textarea ? textarea.value : data;
            }
            // Si la columna es Tipo de Falla (índice 7), exportar el valor del select si existe
            if (column === 7) {
              var select = node.querySelector('select');
              return select ? select.value : data;
            }
            // Si la columna es Fecha Cerrado (índice 8) y contiene el botón, exportar vacío
            if (column === 8) {
              var button = node.querySelector('button');
              return button ? '' : data;
            }
            // Si la columna es Estado (índice 9), exportar el texto limpio sin etiquetas HTML
            if (column === 9) {
              var badge = node.querySelector('.badge');
              return badge ? badge.textContent.trim() : data;
            }
            return data;
          }
        }
      };

      table = $('#reportsTable').DataTable({
        scrollX: true,
        responsive: true,
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
          dataSrc: function ( json ) {
            const selectedMonth = $("#fecha")[0].value; // Formato YYYY-MM
            if (!selectedMonth) {
              return json.data;
            }
            // Filtrar los datos en el cliente para que coincidan con el mes seleccionado en fechaCreacion
            return json.data.filter(v => {
              return v.fechaCreacion && v.fechaCreacion.startsWith(selectedMonth);
            });
          }
        },
        initComplete: function(settings, json) {
          // Ajustar las columnas automáticamente después de que la tabla sea inicializada
          setTimeout(function() {
            table.columns.adjust();
          }, 100);
        },
        columns: [
          { "data" : "Nomina", "title" : "Nómina", "className": "dt-head-center dt-body-center" },
          { "data" : "Nombre", "title" : "Creador", "className": "dt-head-center dt-body-center" },
          { "data" : "Unidad", "title" : "Unidad", "className": "dt-head-center dt-body-center" },
          { 
            "data" : "Problema", 
            "title" : "Problema",
            "className": "dt-head-center dt-body-center",
            "render": function(data, type, row) {
              const probMap = {
                'Falla en Camara': { text: 'Falla en Cámara', css: 'badge-camara' },
                'Falla en memoria': { text: 'Falla en Memoria', css: 'badge-memoria' },
                'GPS': { text: 'GPS', css: 'badge-gps' },
                'DMAS': { text: 'DMAS', css: 'badge-dmas' }
              };
              const info = probMap[data] || { text: data || '', css: '' };
              if (type === 'display') {
                return `<div class="text-center"><button type="button" class="btn btn-badge ${info.css}">${info.text}</button></div>`;
              }
              if (type === 'filter') {
                return info.text;
              }
              return data;
            }
          },
          { "data" : "Detalle", "title" : "Detalle", "className": "dt-head-center dt-body-center" },
          { "data" : "fechaCreacion", "title" : "Fecha Creación", "className": "dt-head-center dt-body-center" },
          { 
            "data" : "Resolucion", 
            "title" : "Resolución", 
            "className": "dt-head-center dt-body-center",
            "render": function(data, type, row) {
              if (row.Estado === 'Por Atender' && type === 'display') {
                return `<textarea id="Resolucion_${row.Id}" class="form-control" placeholder="Escriba la resolución..." rows="2" style="width: 250px; min-width: 200px; font-size: 13px;"></textarea>`;
              }
              return data || '';
            }
          },
          { 
            "data" : "TipoFalla", 
            "title" : "Tipo de Falla", 
            "className": "dt-head-center dt-body-center",
            "render": function(data, type, row) {
              if (row.Estado === 'Por Atender' && type === 'display') {
                return `<select id="TipoFalla_${row.Id}" class="form-select" style="width: 130px; min-width: 110px; font-size: 13px;">
                          <option value="">Seleccione...</option>
                          <option value="Falla">Falla</option>
                          <option value="Daño">Daño</option>
                        </select>`;
              }
              return data || '';
            }
          },
          { 
            "data" : "FechaCerrado", 
            "title" : "Fecha Cerrado", 
            "className": "dt-head-center dt-body-center",
            "render": function(data, type, row) {
              if (row.Estado === 'Por Atender' && type === 'display') {
                return `<button class="btn btn-success btn-badge" onclick="GuardarSeguimiento(${row.Id})"><i class="fa-regular fa-floppy-disk"></i> Guardar</button>`;
              }
              return data || '';
            }
          },
          { 
            "data" : "Estado", 
            "title" : "Estado", 
            "className": "dt-head-center dt-body-center",
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
      const tipoFalla = $("#TipoFalla_" + id).val();

      if (!resolucion) {
        Swal.fire({
          icon: "info",
          title: "Resolución requerida",
          text: "Escriba la resolución del reporte antes de guardar."
        });
        return;
      }

      if (!tipoFalla) {
        Swal.fire({
          icon: "info",
          title: "Tipo de falla requerido",
          text: "Seleccione si es Falla o Daño."
        });
        return;
      }

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
            url: 'api/actualizar.php',
            type: 'POST',
            data: {
              id: id,
              resolucion: resolucion,
              tipoFalla: tipoFalla
            },
            dataType: 'json',
            success: function(respuesta) {
              if(!respuesta.error){
                Swal.fire({
                  title: "¡Guardado!",
                  text: respuesta.message,
                  icon: "success"
                });
                table.ajax.reload();
              }
              else{
                Swal.fire({
                  icon: "error",
                  title: "Error",
                  text: respuesta.message
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

    // Desactivar el manejo automático de errores de DataTables (muestra advertencias en alert)
    $.fn.dataTable.ext.errMode = 'none';

    // Carga inicial de datos
    loadData();
  </script>
</html>
