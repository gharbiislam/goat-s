$(document).ready(function() {
    if ($('.datanew').length > 0) {
        // Check if DataTable is already initialized
        if (!$.fn.DataTable.isDataTable('.datanew')) {
            // Initialize DataTable with the specified design
            $('.datanew').DataTable({
                "bFilter": true,
                "sDom": 'fBtlpi',
                "pagingType": 'numbers',
                "ordering": true,
                "language": {
                    search: ' ',
                    sLengthMenu: '_MENU_',
                    searchPlaceholder: "Search...",
                    info: "_START_ - _END_ of _TOTAL_ items",
                },
                initComplete: (settings, json) => {
                    $('.dataTables_filter').appendTo('#tableSearch');
                    $('.dataTables_filter').appendTo('.search-input');
                }
            });
        }
    }
});