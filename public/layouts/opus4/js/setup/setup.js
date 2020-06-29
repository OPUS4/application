$(document).ready(function () {
    $('#state').on('change', function () {
        $('#filter').submit();
    });
    $('#scope').on('change', function () {
        $('#filter').submit();
    });
    $('#modules').on('change', function () {
        $('#filter').submit();
    });
});