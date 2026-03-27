<!DOCTYPE html>
<html>
<head>
    <title>Rad zaposlenog</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-6">

    <h1 class="text-2xl font-bold mb-4">Pregled rada</h1>

    <a href="/employees" class="text-blue-600 underline mb-4 inline-block">
        ← Nazad
    </a>

    <div class="mb-4 flex gap-2">

        <select id="year" class="border p-2">
            @for($y = now()->year; $y >= now()->year - 5; $y--)
                <option value="{{ $y }}">{{ $y }}</option>
            @endfor
        </select>

        <select id="month" class="border p-2">
            @for($m = 1; $m <= 12; $m++)
                <option value="{{ $m }}">{{ $m }}</option>
            @endfor
        </select>

        <form method="POST" action="/work-entries" class="mb-4 flex gap-2">
        @csrf

        <input type="hidden" name="employee_id" value="{{ request()->route('id') }}">

        <input type="date" name="date" class="border p-2" required>

        <input type="time" name="time_from" class="border p-2">

        <input type="time" name="time_to" class="border p-2">

        <input type="text" name="note" placeholder="Napomena" class="border p-2">

        <button class="bg-green-600 text-white px-3 py-1 rounded">
            + Dodaj
        </button>
    </form>

        <button onclick="filterWork()"
            class="bg-blue-500 text-white px-3 py-1 rounded">
            Filtriraj
        </button>

    </div>

    <div class="bg-white rounded shadow p-4">

        <table class="w-full text-sm">
            <thead class="bg-gray-100">
                <tr>
                    <th class="p-2 text-left">Datum</th>
                    <th class="p-2 text-left">Od</th>
                    <th class="p-2 text-left">Do</th>
                    <th class="p-2 text-left">Napomena</th>
                </tr>
            </thead>

            <tbody>
                @foreach($entries as $e)
                    <tr class="border-t">
                        <td class="p-2">{{ $e->date }}</td>
                        <td class="p-2">{{ $e->time_from }}</td>
                        <td class="p-2">{{ $e->time_to }}</td>
                        <td class="p-2">{{ $e->note }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

    </div>

</body>

<script>

function filterWork() {

    let year = document.getElementById('year').value;
    let month = document.getElementById('month').value;

    let url = window.location.pathname + '?year=' + year + '&month=' + month;

    window.location.href = url;
}

</script>
</html>