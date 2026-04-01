@extends('layouts.app')

@section('content')

<style>
.active-cell {
    background-color: rgba(59, 130, 246, 0.15);
}

.active-row {
    outline: 2px solid #3b82f6;
    outline-offset: -2px;
}

.active-row td:first-child {
    position: relative;
}

.active-row td:first-child::before {
    content: "";
    position: absolute;
    left: 0;
    top: 0;
    bottom: 0;
    width: 4px;
    background: #3b82f6;
}
</style>



<div class="p-6">
    <h1 class="text-2xl font-bold mb-4">Kalendar rada</h1>

    <!-- FILTERI (kasnije) -->
    <div class="mb-4">
        <select id="employeeFilter" class="border p-2 rounded">
            <option value="">Svi zaposleni</option>
        </select>
    </div>

    <!-- KALENDAR -->
   {{-- <div id="calendar"></div> --}}

    <div class="bg-white shadow rounded-xl p-4 mt-6 overflow-x-auto">

        <div class="flex gap-4 mb-3 text-sm">
            <div class="flex items-center gap-1">
                <div class="w-4 h-4 bg-green-500"></div> Rad
            </div>
            <div class="flex items-center gap-1">
                <div class="w-4 h-4 bg-yellow-400"></div> Odsustvo
            </div>
            <div class="flex items-center gap-1">
                <div class="w-4 h-4 bg-red-500"></div> Problem
            </div>
            <div class="flex items-center gap-1">
                <div class="w-4 h-4 bg-gray-200"></div> Vikend
            </div>
            <div class="flex items-center gap-1">
                <div class="w-4 h-4 bg-blue-200"></div> Praznik
            </div>
        </div>

        <div class="flex gap-2 mb-4 items-center">

            <!-- MESEC -->
            <select id="gridMonth" class="border p-1 rounded">
                @for($m = 1; $m <= 12; $m++)
                    <option value="{{ $m }}" {{ $m == $month ? 'selected' : '' }}>
                        {{ \Carbon\Carbon::create()->month($m)->translatedFormat('F') }}
                    </option>
                @endfor
            </select>

            <!-- GODINA -->
            <select id="gridYear" class="border p-1 rounded">
                @for($y = now()->year; $y >= now()->year - 5; $y--)
                    <option value="{{ $y }}" {{ $y == $year ? 'selected' : '' }}>
                        {{ $y }}
                    </option>
                @endfor
            </select>

            <!-- DUGME -->
            <button onclick="changeGridDate()" class="bg-blue-500 text-white px-3 py-1 rounded">
                Prikaži
            </button>

        </div>

        <h2 class="font-bold mb-3">📊 Mesečni pregled zaposlenih</h2>

        <table class="min-w-full text-xs border">
            <thead>
                <tr>
                    <td class="p-2 font-semibold bg-gray-100 hover:scale-110 transition">{{ auth()->user()->employee->first_name ?? '' }}</td>

                    @for($d = 1; $d <= 31; $d++)
                        <th class="p-1 border text-center w-8">{{ $d }}</th>
                    @endfor
                </tr>
            </thead>

            <tbody>

            @foreach($employees as $emp)
            <tr>

                <!-- 👇 sticky ime -->
                <td class="p-2 font-semibold bg-gray-100 whitespace-nowrap sticky left-0 z-10">
                    {{ $emp->first_name }} {{ $emp->last_name }}
                </td>

                @for($d = 1; $d <= $daysInMonth; $d++)

                    @php
                        $entry = $emp->entries->firstWhere('day', $d);
                        $date = \Carbon\Carbon::create($year, $month, $d);
                        $isWeekend = $date->isWeekend();
                        $isHoliday = in_array($date->format('Y-m-d'), $holidays);
                    @endphp

                    @if(!$entry)

                        @php
                            if ($isHoliday) {
                                $color = 'bg-blue-200 text-blue-800 font-semibold'; // praznik
                            } elseif ($isWeekend) {
                                $color = 'bg-gray-200 text-gray-400'; // vikend
                            } else {
                                $color = 'bg-white text-gray-300'; // normalan dan
                            }

                            $label = '';
                        @endphp

                    @else

                        @php
                            $type = $entry->type ?? null;

                            $color = match(true) {
                                $type?->counts_as_work && $isWeekend => 'bg-green-700 text-white',
                                $type?->counts_as_work => 'bg-green-500 text-white font-bold',   // rad
                                
                                $type?->affects_vacation => 'bg-amber-400 text-black font-bold', // godišnji
                                $type && !$type->is_paid => 'bg-red-500 text-white font-bold',   // problem
                                default => 'bg-gray-100 text-gray-400'
                            };

                            $label = $type->code ?? '';
                        @endphp

                    @endif

                    <td 
                        class="w-8 h-8 text-center align-middle border {{ $color }} cursor-pointer hover:opacity-80"
                        onclick="openDay({{ $d }}, {{ $emp->id }}, this, event)"
                        title="
                            {{ $entry?->type?->name ?? 'Nema unosa' }}
                            @if($entry && $entry->time_from && $entry->time_to)
                            ({{ \Carbon\Carbon::parse($entry->time_from)->format('H:i') }} - {{ \Carbon\Carbon::parse($entry->time_to)->format('H:i') }})
                            @endif
                            "
                    >
                        {{ $label }}
                    </td>

                @endfor

            </tr>
            @endforeach

            </tbody>
        </table>

    </div>
</div>


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

    <div id="dayModal" class="fixed inset-0 bg-black/40 hidden items-center justify-center z-50">
        <div class="bg-white w-[500px] rounded-xl shadow p-4">

            <div class="flex justify-between mb-3">
                <h2 id="modalTitle" class="font-bold">Unosi</h2>
                <button onclick="closeModal()">✖</button>
            </div>

            <div id="modalDate" class="text-sm text-gray-500 mb-3"></div>

            <div id="entriesList" class="space-y-2 mb-3 max-h-[200px] overflow-auto"></div>

            <div class="grid grid-cols-3 gap-2 mb-3">
                <input type="time" id="timeFrom" class="border p-2">
                <input type="time" id="timeTo" class="border p-2">

                <select id="entryType" class="border p-2">
                    @foreach($types as $t)
                        <option value="{{ $t->id }}">
                            {{ $t->code }} - {{ $t->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <button onclick="addEntry()" class="bg-green-600 text-white w-full p-2 rounded">
                + Dodaj unos
            </button>

        </div>
    </div>
 

</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>

  

    function changeGridDate() {
    let m = document.getElementById('gridMonth').value;
    let y = document.getElementById('gridYear').value;

    let url = window.location.pathname + '?month=' + m + '&year=' + y;

    window.location.href = url;
}

function closeModal() {
    document.getElementById('dayModal').classList.add('hidden');
}



    window.openDay = function(day, employeeId) {

        console.log("klik radi", day, employeeId);

        let date = `{{ $year }}-{{ str_pad($month,2,'0',STR_PAD_LEFT) }}-${String(day).padStart(2,'0')}`;

        modalState.employeeId = employeeId;
        modalState.date = date;

        document.getElementById('modalDate').innerText =
            new Date(date).toLocaleDateString('sr-RS');

        document.getElementById('dayModal').classList.remove('hidden');
        document.getElementById('dayModal').classList.add('flex');

        loadEntries();
    }

let modalState = { employeeId:null, date:null, entries:[] };

function loadEntries() {

    fetch(`/work-entries/day?employee_id=${modalState.employeeId}&date=${modalState.date}`)
        .then(res => {
            if (!res.ok) throw new Error("Server error");
            return res.json();
        })
        .then(data => {
            modalState.entries = data;
            renderEntries();
        })
        .catch(err => {
            console.error("loadEntries error:", err);
        });
}

function renderEntries() {

    let c = document.getElementById('entriesList');
    c.innerHTML = '';

    if (!modalState.entries.length) {
        c.innerHTML = '<div class="text-gray-400 text-sm">Nema unosa</div>';
        return;
    }

    modalState.entries.forEach(e => {

        let div = document.createElement('div');
        div.className = "border p-2 text-sm flex justify-between";

        div.innerHTML = `
            <span>${e.time_from ?? '--'} - ${e.time_to ?? '--'} | ${e.type.name}</span>
            <button onclick="deleteEntry(${e.id})" class="text-red-500">✖</button>
        `;

        c.appendChild(div);
    });
}

function addEntry() {

    let from = document.getElementById('timeFrom').value;
    let to = document.getElementById('timeTo').value;
    let typeId = document.getElementById('entryType').value;

    if (!from || !to) {
        alert("Unesi vreme");
        return;
    }

    fetch('/work-entries', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: new URLSearchParams({
            employee_id: modalState.employeeId,
            date: modalState.date,
            time_from: from,
            time_to: to,
            work_entry_type_id: typeId
        })
    })
    .then(res => res.json())
    .then(() => loadEntries())
    .catch(err => {
        console.error("ADD ERROR:", err);
        alert("Greška pri upisu");
    });
}

function deleteEntry(id) {
    fetch(`/work-entries/${id}`, {
        method:'DELETE',
        headers:{
            'X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content
        }
    }).then(()=> loadEntries());
}


function filterWork() {

    let year = document.getElementById('year').value;
    let month = document.getElementById('month').value;

    let url = window.location.pathname + '?year=' + year + '&month=' + month;

    window.location.href = url;
}


function updateCellUI(el, type) {

    el.innerHTML = type.code;

    el.className = "w-8 h-8 text-center align-middle border cursor-pointer";

    if (type.counts_as_work) {
        el.classList.add('bg-green-500','text-white','font-bold');
    } 
    else if (type.affects_vacation) {
        el.classList.add('bg-amber-400','text-black','font-bold');
    } 
    else if (!type.is_paid) {
        el.classList.add('bg-red-500','text-white','font-bold');
    } 
    else {
        el.classList.add('bg-gray-100');
    }
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

    const ctx = document.getElementById('workChart')?.getContext('2d');
    if (!ctx) return;

            const chartData = {!! json_encode([
            "regular" => $regularMinutes ?? 0,
            "extra" => $extraMinutes ?? 0,
            "unpaid" => $unpaidMinutes ?? 0
        ]) !!};

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


@endsection