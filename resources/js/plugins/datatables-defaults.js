import languageES from './datatables-language-es';

export default {
  responsive: true,
  scrollX: true,
  autoWidth: false,
  language: languageES,
  pageLength: 10,
  lengthMenu: [
    [10, 25, 50, 100],
    [10, 25, 50, 100]
  ],
  order: [[0, 'asc']],
  columnDefs: [
    {
      targets: -1,
      orderable: false,
      searchable: false,
      className: 'text-center'
    }
  ],
  dom:
    "<'row align-items-center mb-3'" +
    "<'col-md-4'l>" +
    "<'col-md-8 d-flex justify-content-md-end justify-content-center align-items-center gap-2'Bf>" +
    '>' +
    "<'row'<'col-12'tr>>" +
    "<'row align-items-center gy-2 mt-3'<'col-md-5'i><'col-md-7 d-flex justify-content-md-end justify-content-center'p>>",
  buttons: [
    {
      extend: 'collection',

      text:
        '<span class="d-flex align-items-center gap-1">' +
        '<i class="bx bx-export"></i>' +
        '<span class="d-sm-inline-block">Exportar</span>' +
        '</span>',

      className: 'btn btn-label-secondary',

      buttons: [
        {
          extend: 'copyHtml5',
          text: '<i class="bx bx-copy me-1"></i> Copiar'
        },
        {
          extend: 'excelHtml5',
          text: '<i class="bx bx-spreadsheet me-1"></i> Excel'
        },
        {
          extend: 'csvHtml5',
          text: '<i class="bx bx-table me-1"></i> CSV'
        },
        {
          extend: 'pdfHtml5',
          text: '<i class="bx bx-file me-1"></i> PDF',
          orientation: 'landscape',
          pageSize: 'A4'
        },
        {
          extend: 'print',
          text: '<i class="bx bx-printer me-1"></i> Imprimir'
        }
      ]
    }
  ],

  initComplete: function () {
    $('.dt-search input').removeClass('form-control-sm').addClass('form-control');
  }
};
