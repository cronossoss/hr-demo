<!DOCTYPE html>
<html>

<head>
    <title>Dodaj zaposlenog</title>
</head>

<body>

    <h1>Dodaj zaposlenog</h1>

    <a href="{{ route('employees.index') }}">← Nazad</a>

    @if ($errors->any())
    <div style="color:red;">
        <ul>
            @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <form method="POST" action="{{ route('employees.store') }}">
        @csrf

        <label>Ime:</label><br>
        <input type="text" name="first_name"><br><br>

        <label>Prezime:</label><br>
        <input type="text" name="last_name"><br><br>

        <label>Pozicija:</label><br>
        <input type="text" name="position"><br><br>

        <label>Organizaciona jedinica:</label><br>
        <select name="organizational_unit_id">
            <option value="">-- izaberi --</option>
            @foreach($units as $unit)
            <option value="{{ $unit->id }}">{{ $unit->name }}</option>
            @endforeach
        </select><br><br>

        <button type="submit">Sačuvaj</button>
    </form>

</body>

</html>