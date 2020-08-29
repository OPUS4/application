$(document).ready(function () {
    // submit form if role selection is changed
    $('#role').on('change', function () {
        $('#persons').submit();
    });
    // submit form if filter value is changed
    $('#filter').on('change', function () {
        $('#persons').submit();
    });
});