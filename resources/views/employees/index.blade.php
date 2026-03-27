<!-- // resources/views/employees/index.blade.php -->

@extends('layouts.app')

@section('content')

<div class="p-6">

    <!-- ===================================================== -->
    <!-- HEADER -->
    <!-- ===================================================== -->
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Zaposleni</h1>

        <button onclick="window.location.href='/employees/create'"
            class="bg-green-600 text-white px-4 py-2 rounded">
            + Dodaj zaposlenog
        </button>
    </div>

    <!-- ===================================================== -->
    <!-- TABELA -->
    <!-- ===================================================== -->
    <div class="bg-white shadow rounded-lg overflow-hidden">

        <table class="w-full text-sm">
            <thead class="bg-gray-100">
                <tr>
                    <th class="p-3 text-left">Matični broj</th>
                    <th class="p-3 text-left">Ime i prezime</th>
                    <th class="p-3 text-left">Organizaciona jedinica</th>
                </tr>
            </thead>

            <tbody>
                @foreach($employees as $emp)
                    <tr class="border-t hover:bg-gray-100 cursor-pointer">
                        <td class="p-3">
                            <a href="/employees/{{ $emp->id }}" class="block w-full h-full">
                                {{ $emp->employee_number }}
                            </a>
                        </td>

                        <td class="p-3">
                            <a href="/employees/{{ $emp->id }}" class="block w-full h-full">
                                {{ $emp->first_name }} {{ $emp->last_name }}
                            </a>
                        </td>

                        <td class="p-3">
                            <a href="/employees/{{ $emp->id }}" class="block w-full h-full">
                                {{ $emp->organizationalUnit?->name }}
                            </a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

    </div>

</div>

@endsection