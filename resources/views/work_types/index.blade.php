@extends('layouts.app')

@section('content')

<div class="p-6 max-w-4xl mx-auto">

    <h1 class="text-2xl font-bold mb-4">Tipovi rada</h1>

    <!-- FORM -->
    <form method="POST" action="/work-types" class="mb-6 flex gap-2 flex-wrap">
        @csrf

        <input type="text" name="code"
            placeholder="Šifra (npr. od 1 do 98)"
            class="border p-2">

        <input type="text" name="name" placeholder="Naziv"
            class="border p-2">

        <select name="input_type" class="border p-2">
            <option value="time">Satni</option>
            <option value="range">Dnevni (od-do)</option>
        </select>

        <label class="flex items-center gap-1">
            <input type="checkbox" name="is_paid">
            Plaćen
        </label>

        <label class="flex items-center gap-1">
            <input type="checkbox" name="counts_as_work">
            Računa se kao rad
        </label>

        <label class="flex items-center gap-1">
            <input type="checkbox" name="affects_vacation">
            Troši godišnji odmor
        </label>

        <input type="number" step="0.01" name="pay_multiplier"
                        placeholder="Koeficijent"
                        class="border p-2 w-32">

        <button class="bg-green-600 text-white px-4 py-2 rounded">
            + Dodaj
        </button>
    </form>

    <!-- LISTA -->
    <table class="w-full">
        <thead class="bg-gray-200 text-xs uppercase">
            <tr>
                <th class="p-3 text-left">Naziv</th>
                <th class="p-3 text-left">Tip</th>
                <th class="p-3 text-left">Plaćen</th>
                <th class="p-3">Rad</th>
                <th class="p-3">Godišnji</th>
                <th class="p-3 text-left">Koef.</th>
            </tr>
        </thead>

        <tbody>
            @foreach($types as $t)
                <tr class="border-t">
                    <td class="p-3">{{ $t->name }}</td>
                    <td class="p-3">
                        {{ $t->input_type === 'range' ? 'Dnevni' : 'Satni' }}
                    </td>
                    <td class="p-3">
                        {{ $t->is_paid ? 'Da' : 'Ne' }}
                    </td>
                    <td class="p-3">{{ $t->counts_as_work ? 'Da' : 'Ne' }}</td>
                    <td class="p-3">{{ $t->affects_vacation ? 'Da' : 'Ne' }}</td>
                    <td class="p-3">{{ $t->pay_multiplier }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

</div>

@endsection