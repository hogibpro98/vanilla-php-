<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Select2 AJAX Example</title>
    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
</head>
<body>

<h1>Select Employee</h1>

<select id="employeeSelect" style="width: 300px;">
    <option value="" selected disabled>Search for an employee...</option>
</select>

<script>
$(document).ready(function() {
    $('#employeeSelect').select2({
        ajax: {
            url: 'get_data.php', // Path to your PHP script
            dataType: 'json',
            delay: 250, // Wait 250ms after typing stops to start AJAX request
            data: function (params) {
                return {
                    q: params.term // Search term
                };
            },
            processResults: function (data) {
                // Transforms the top-level key of the response object from 'items' to 'results'
                return {
                    results: data.results
                };
            },
            cache: true
        },
        placeholder: 'Search for an employee',
        minimumInputLength: 0 // Allow searching immediately
    });
});
</script>

</body>
</html>
