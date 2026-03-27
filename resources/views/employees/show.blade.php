@extends('layouts.app')

@section('content')

<div class="p-6 max-w-4xl mx-auto">

    <!-- ===================================================== -->
    <!-- HEADER -->
    <!-- ===================================================== -->
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">
            {{ $employee->first_name }} {{ $employee->last_name }}
        </h1>

        <a href="/employees" class="text-blue-600 underline">
            ← Nazad
        </a>
    </div>

    <!-- ===================================================== -->
    <!-- OSNOVNI PODACI -->
    <!-- ===================================================== -->
    <div class="bg-white p-6 rounded shadow mb-6">

        <h2 class="font-semibold mb-4">Osnovni podaci</h2>

        <div class="grid grid-cols-2 gap-4 text-sm">

            <div>
                <div class="text-gray-500">Matični broj</div>
                <div>{{ $employee->employee_number }}</div>
            </div>

            <div>
                <div class="text-gray-500">Pozicija</div>
                <div>{{ $employee->position }}</div>
            </div>

            <div>
                <div class="text-gray-500">Organizaciona jedinica</div>
                <div>{{ $employee->organizationalUnit?->name }}</div>
            </div>

            <div>
                <div class="text-gray-500">Email</div>
                <div>{{ $employee->email }}</div>
            </div>

        </div>
    </div>

    <!-- ===================================================== -->
    <!-- GODIŠNJI ODMOR -->
    <!-- ===================================================== -->
    <div class="bg-blue-50 p-6 rounded shadow">

        <h2 class="font-semibold mb-4">Godišnji odmor</h2>

        @php
            $total = $vacation->total_days ?? 0;
            $used = $vacation->used_days ?? 0;
            $remaining = $total - $used;
        @endphp

        <div class="grid grid-cols-3 gap-4 text-center">

            <div class="bg-white p-4 rounded shadow">
                <div class="text-gray-500 text-sm">Ukupno</div>
                <div class="text-xl font-bold">{{ $total }}</div>
            </div>

            <div class="bg-white p-4 rounded shadow">
                <div class="text-gray-500 text-sm">Iskorišćeno</div>
                <div class="text-xl font-bold text-orange-500">{{ $used }}</div>
            </div>

            <div class="bg-white p-4 rounded shadow">
                <div class="text-gray-500 text-sm">Preostalo</div>
                <div class="text-xl font-bold text-green-600">{{ $remaining }}</div>
            </div>

        </div>

    </div>

    <!-- ===================================================== -->
    <!-- AKCIJE -->
    <!-- ===================================================== -->
    <div class="mt-6 flex gap-3">

        <a href="/employees/{{ $employee->id }}/work"
        class="bg-blue-600 text-white px-4 py-2 rounded">
            Pregled rada
        </a>

        <button class="bg-yellow-400 px-4 py-2 rounded">
            Izmeni
        </button>

    </div>

</div>

@endsection