@extends('layouts.app')

@section('content')

<div class="bg-gray-100 p-6">


    <h1 class="text-2xl font-bold mb-4">Pregled rada</h1>

    <a href="/employees" class="text-blue-600 underline mb-4 inline-block">
        ← Nazad
    </a>

    

        <div class="mb-4 bg-white p-4 rounded shadow flex gap-2 flex-wrap">

            <form method="POST" action="/work-entries" class="flex gap-2 flex-wrap">
                @csrf

                <select name="work_entry_type_id" id="type" class="border p-2" required>

                    <option value="" disabled selected hidden>
                        Izaberite vrstu rada
                    </option>

                    @foreach($types as $t)
                        <option value="{{ $t->id }}"
                            data-type="{{ $t->input_type }}">
                            [{{ str_pad($t->code, 3, ' ', STR_PAD_RIGHT) }}] {{ $t->name }}
                        </option>
                    @endforeach

                </select>

                <div id="timeBlock" class="flex gap-2">
                    <input type="text" name="date" class="date-picker border p-2" placeholder="Datum">
                    <input type="text" name="time_from" class="time-picker border p-2" placeholder="Od">
                    <input type="text" name="time_to" class="time-picker border p-2" placeholder="Do">
                </div>

                <div id="rangeBlock" class="flex gap-2 hidden">
                    <input type="text" name="date_from" class="date-picker border p-2" placeholder="Od">
                    <input type="text" name="date_to" class="date-picker border p-2" placeholder="Do">
                </div>

                <input type="hidden" name="employee_id" value="{{ request()->route('id') }}">

                <input type="text" name="note" class="border p-2" placeholder="Napomena">

                <button class="bg-green-600 text-white px-4 py-2 rounded">
                    + Dodaj
                </button>

            </form>

        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">

        <!-- LEVO: KARTICE -->
        <div class="flex flex-col gap-3">

        <div class="bg-white shadow p-3 rounded flex items-center justify-between">
            <div>
                <div class="text-gray-500 text-xs">Redovan rad (100%)</div>
                <div class="text-lg font-bold">
                    {{ floor($regularMinutes / 60) }}h {{ $regularMinutes % 60 }}m
                </div>
            </div>
            <div class="text-2xl">🟢</div>
        </div>

        <div class="bg-yellow-100 shadow p-3 rounded flex items-center justify-between">
            <div>
                <div class="text-gray-500 text-xs">Uvećan rad</div>
                <div class="text-lg font-bold text-yellow-700">
                    {{ floor($extraMinutes / 60) }}h {{ $extraMinutes % 60 }}m
                </div>
            </div>
            <div class="text-2xl">🟡</div>
        </div>

        <div class="bg-red-100 shadow p-3 rounded flex items-center justify-between">
            <div>
                <div class="text-gray-500 text-xs">Neplaćeno</div>
                <div class="text-lg font-bold text-red-700">
                    {{ floor($unpaidMinutes / 60) }}h {{ $unpaidMinutes % 60 }}m
                </div>
            </div>
            <div class="text-2xl">🔴</div>
        </div>

    </div>

        <!-- DESNO: GRAFIK -->
        <div class="md:col-span-2 bg-white p-4 rounded shadow h-[250px]">
            <canvas id="workChart"></canvas>
        </div>

    </div>
        
        
        
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

            <button onclick="filterWork()"
                class="bg-blue-500 text-white px-3 py-1 rounded">
                Filtriraj
            </button>

            <button onclick="resetFilter()"
                class="bg-gray-500 text-white px-3 py-1 rounded">
                Reset
            </button>

        </div>


        

  

    <div class="bg-white rounded shadow p-4">

        <table class="w-full text-sm">
            <thead class="bg-gray-200 text-gray-700 uppercase text-xs">
                <tr>
                    <th class="p-3 text-left">Datum</th>
                    <th class="p-3 text-left">Od</th>
                    <th class="p-3 text-left">Do</th>
                    <th class="p-3 text-left">Tip</th>
                    <th class="p-3 text-left">Napomena</th>
                </tr>
            </thead>

            <tbody>
                @foreach($timeEntries as $e)
                    @php
                        $m = $e->type->pay_multiplier ?? 1;

                        if ($m == 1) {
                            $rowClass = 'bg-white';
                        } elseif ($m > 1) {
                            $rowClass = 'bg-yellow-50';
                        } elseif ($m == 0) {
                            $rowClass = 'bg-red-50';
                        } else {
                            $rowClass = 'bg-gray-50';
                        }
                    @endphp

                    <tr class="border-t hover:bg-gray-100 {{ $rowClass }}">
                        <td class="p-3">{{ \Carbon\Carbon::parse($e->date)->format('d.m.Y') }}</td>
                        <td class="p-3">{{ $e->time_from ? \Carbon\Carbon::parse($e->time_from)->format('H:i') : '-' }}</td>
                        <td class="p-3">{{ \Carbon\Carbon::parse($e->time_to)->format('H:i') }}</td>
                        <td class="p-3">
                            <span class="px-2 py-1 rounded text-xs font-semibold
                                {{ $m == 1 ? 'bg-gray-200' : '' }}
                                {{ $m > 1 ? 'bg-yellow-200 text-yellow-800' : '' }}
                                {{ $m == 0 ? 'bg-red-200 text-red-800' : '' }}">
                                {{ $e->type->name ?? '-' }}
                            </span>
                        </td>
                        <td class="p-3">{{ $e->note }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <h2 class="text-lg font-semibold mb-2 mt-6">Odsustva</h2>

        <table class="w-full">
            <thead class="bg-gray-200 text-xs uppercase">
                <tr>
                    <th class="p-3 text-left">Tip</th>
                    <th class="p-3 text-left">Od</th>
                    <th class="p-3 text-left">Do</th>
                    <th class="p-3 text-left">Dani</th>
                </tr>
            </thead>

            <tbody>

                @php
                    $groups = [];

                    foreach ($rangeEntries->sortBy('date') as $entry) {

                        $last = end($groups);

                        if (
                            !$last ||
                            $last['type_id'] !== $entry->work_entry_type_id ||
                            \Carbon\Carbon::parse($entry->date)
                                ->diffInDays($last['to']) > 1
                        ) {
                            $groups[] = [
                                'type_id' => $entry->work_entry_type_id,
                                'type' => $entry->type->name,
                                'from' => \Carbon\Carbon::parse($entry->date),
                                'to' => \Carbon\Carbon::parse($entry->date),
                                'days' => 1
                            ];
                        } else {
                            $groups[array_key_last($groups)]['to'] = \Carbon\Carbon::parse($entry->date);
                            $groups[array_key_last($groups)]['days']++;
                        }
                    }
                @endphp

                @foreach($groups as $g)
                <tr class="border-t bg-blue-50">

                    <td class="p-3">{{ $g['type'] }}</td>

                    <td class="p-3">{{ $g['from']->format('d.m.Y') }}</td>

                    <td class="p-3">{{ $g['to']->format('d.m.Y') }}</td>

                    <td class="p-3 font-semibold">{{ $g['days'] }}</td>

                </tr>
                @endforeach

            </tbody>
        </table>

    </div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>

function filterWork() {

    let year = document.getElementById('year').value;
    let month = document.getElementById('month').value;

    let url = window.location.pathname + '?year=' + year + '&month=' + month;

    window.location.href = url;
}


function resetFilter() {
    window.location.href = window.location.pathname;
}

const typeSelect = document.getElementById('type');
const timeBlock = document.getElementById('timeBlock');
const rangeBlock = document.getElementById('rangeBlock');

function updateForm() {

    if (!typeSelect.value) return;

    let selected = typeSelect.options[typeSelect.selectedIndex];
    let type = selected.dataset.type;

    if (type === 'range') {
        timeBlock.classList.add('hidden');
        rangeBlock.classList.remove('hidden');
    } else {
        timeBlock.classList.remove('hidden');
        rangeBlock.classList.add('hidden');
    }
}

// promena tipa
typeSelect.addEventListener('change', updateForm);

// inicijalno stanje (ključ!)
window.addEventListener('load', updateForm);


document.addEventListener('DOMContentLoaded', function () {

    const ctx = document.getElementById('workChart');
    if (!ctx) return;

    const chartData = @json([
        'regular' => $regularMinutes ?? 0,
        'extra' => $extraMinutes ?? 0,
        'unpaid' => $unpaidMinutes ?? 0
    ]);

    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['Redovan', 'Uvećan', 'Neplaćen'],
            datasets: [{
                data: [
                    chartData.regular,
                    chartData.extra,
                    chartData.unpaid
                ],
                backgroundColor: [
                    '#e5e7eb', // sivo (redovan)
                    '#fde68a', // žuto (uvećan)
                    '#fecaca'  // crveno (neplaćen)
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,

            plugins: {
                legend: {
                    position: 'right',
                        labels: {
                            boxWidth: 12,
                            padding: 15
                        }
                },

                tooltip: {
                    callbacks: {
                        label: function(context) {
                            let minutes = context.raw;

                            let total = context.dataset.data.reduce((a, b) => a + b, 0);

                            let percent = total ? ((minutes / total) * 100).toFixed(1) : 0;

                            let h = Math.floor(minutes / 60);
                            let m = minutes % 60;

                            return `${context.label}: ${h}h ${m}m (${percent}%)`;
                        }
                    }
                }
            }
        }
            });

});

</script>

</div>
@endsection