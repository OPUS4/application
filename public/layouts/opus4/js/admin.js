$(document).ready(function() {
    // submit form if role selection is changed
    $('#role').on('change', function() {
        console.log('role changed');
        $('#persons').submit();
    });
    // submit form if filter value is changed
    $('#filter').on('change', function() {
        console.log('filter changed');
    });
    // filter empty form elements from submit so they do not appear in URL
    $('#persons').submit(function() {
        $(this).find('input[name]').filter(function() {
            return !this.value;
        }).prop('name', '');
    });
});