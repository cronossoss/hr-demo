<!DOCTYPE html>
<html>
<head>
    <title>HR Demo</title>

    <!-- @vite(['resources/js/app.js']) -->
     <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
     @vite(['resources/css/app.css', 'resources/js/app.js'])
     
     
    <script>
        console.log('JS placeholder');
    </script>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">

    @yield('content')

    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/sr.js"></script>
    <script>
document.addEventListener('DOMContentLoaded', function() {

    // DATE
    flatpickr(".date-picker", {
        dateFormat: "d.m.Y",
        locale: "sr",
        allowInput: true,
        clickOpens: true
    });

    // TIME
    flatpickr(".time-picker", {
        enableTime: true,
        noCalendar: true,
        dateFormat: "H:i",
        time_24hr: true,
        allowInput: true,
        clickOpens: true
    });

});
</script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

</body>

</html>