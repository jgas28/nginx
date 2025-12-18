@extends('layouts.app')

@section('content')

<style>
    .panel-scroll { max-height: 420px; overflow-y: auto; }
    .panel-scroll-lg { max-height: 520px; overflow-y: auto; }

    .panel-scroll::-webkit-scrollbar,
    .panel-scroll-lg::-webkit-scrollbar { width: 6px; }

    .panel-scroll::-webkit-scrollbar-thumb,
    .panel-scroll-lg::-webkit-scrollbar-thumb {
        background-color: #c7d2fe;
        border-radius: 6px;
    }

    .active-mtm {
        background-color: #eef2ff;
        border-color: #6366f1;
    }
</style>

<div class="container mx-auto max-w-7xl p-6">

<h2 class="text-2xl font-semibold mb-4">Billing Selection</h2>

<!-- ================= SUMMARY ================= -->
<div class="bg-indigo-50 rounded-xl p-5 shadow mb-6 sticky top-0 z-20">
    <h3 class="font-semibold text-lg mb-3">Billing Summary</h3>

    <div class="max-h-40 overflow-y-auto text-sm mb-3 space-y-2">
        <div id="summary_delivery"></div>
        <div id="summary_accessorial"></div>
    </div>

    <div class="grid grid-cols-3 gap-4 text-sm">
        <div>
            <p class="text-gray-500">Delivery Total</p>
            <p class="font-semibold" id="delivery_total">0.00</p>
        </div>
        <div>
            <p class="text-gray-500">Accessorial Total</p>
            <p class="font-semibold" id="accessorial_total">0.00</p>
        </div>
        <div>
            <p class="text-gray-500">Grand Total</p>
            <p class="font-bold text-lg" id="grand_total">0.00</p>
        </div>
    </div>

    <form action="{{ route('billing.storeSelection') }}" method="POST" class="mt-4">
        @csrf
        <input type="hidden" name="delivery_requests" id="selected_delivery_requests">
        <input type="hidden" name="line_items" id="selected_line_items">
        <button class="mt-3 px-6 py-3 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
            Proceed to Billing
        </button>
    </form>
</div>

<!-- ================= COMPANY + SEARCH ================= -->
<div class="flex gap-4 mb-4">
    <select id="company_id" class="w-1/3 rounded-lg border-gray-300">
        <option value="">Select Company</option>
        @foreach($companies as $company)
            <option value="{{ $company->id }}">{{ $company->company_name }}</option>
        @endforeach
    </select>

    <input id="mtm_search" class="w-2/3 rounded-lg border-gray-300 px-4 py-2"
           placeholder="Search MTM or Project">
</div>

<!-- ================= PANELS ================= -->
<div class="grid grid-cols-3 gap-6">

<!-- Delivery Requests -->
<div class="bg-white rounded-xl shadow p-4">
    <h3 class="font-semibold mb-3">Delivery Requests</h3>
    <div id="delivery_requests" class="panel-scroll space-y-2 text-sm text-gray-500">
        Select a company
    </div>
</div>

<!-- Line Items -->
<div class="col-span-2 bg-white rounded-xl shadow p-4">
    <h3 class="font-semibold mb-3">Accessorial Line Items</h3>
    <div id="line_items" class="panel-scroll-lg text-sm text-gray-500">
        Select a delivery request
    </div>
</div>

</div>
</div>

<script>
let deliveryRequests = [];
let lineItems = {};
let activeDrId = null;

let selectedDelivery = new Set();
let selectedLineItems = new Set();

let deliveryTotal = 0;
let accessorialTotal = 0;

let summary = { delivery:{}, accessorial:{} };

/* ================= FETCH ================= */
document.getElementById('company_id').addEventListener('change', function () {
    resetAll();
    if (!this.value) return;

    fetch("{{ route('billing.getItemsByCompany') }}", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": "{{ csrf_token() }}"
        },
        body: JSON.stringify({ company_id: this.value })
    })
    .then(r => r.json())
    .then(data => {
        deliveryRequests = data.deliveryRequests;
        lineItems = data.lineItems;
        renderDeliveryRequests();
    });
});

/* ================= SEARCH ================= */
document.getElementById('mtm_search').addEventListener('input', e =>
    renderDeliveryRequests(e.target.value.toLowerCase())
);

/* ================= RENDER DR ================= */
function renderDeliveryRequests(search='') {
    const c = document.getElementById('delivery_requests');
    c.innerHTML = '';

    deliveryRequests
        .filter(dr =>
            dr.mtm.toLowerCase().includes(search) ||
            dr.project_name.toLowerCase().includes(search)
        )
        .forEach(dr => {
            c.innerHTML += `
                <div onclick="selectDr(${dr.id})"
                     class="p-3 border rounded cursor-pointer ${activeDrId===dr.id?'active-mtm':''}">
                    <div class="flex justify-between">
                        <div>
                            <p class="font-medium">${dr.mtm}</p>
                            <p class="text-xs">${dr.project_name}</p>
                        </div>
                        <div class="flex items-center space-x-2">
                            <input type="checkbox"
                                data-rate="${Number(dr.delivery_rate)||0}"
                                value="${dr.id}"
                                onclick="event.stopPropagation()"
                                onchange="toggleDelivery(this)">
                            <span>${Number(dr.delivery_rate||0).toFixed(2)}</span>
                        </div>
                    </div>
                </div>
            `;
        });
}

/* ================= SELECT DR ================= */
function selectDr(id) {
    activeDrId = id;
    renderDeliveryRequests(document.getElementById('mtm_search').value.toLowerCase());
    renderLineItems(id);
}

/* ================= LINE ITEMS ================= */
function renderLineItems(id) {
    const c = document.getElementById('line_items');
    c.innerHTML = '';

    (lineItems[id]||[]).forEach(item => {
        c.innerHTML += `
            <div class="flex justify-between p-3 border rounded mb-2">
                <div>
                    <p class="font-medium">${item.site_name}</p>
                    <p class="text-xs">${item.delivery_number}</p>
                </div>
                <div class="flex items-center space-x-2">
                    <input type="checkbox"
                        data-rate="${Number(item.accessorial_rate)||0}"
                        value="${item.id}"
                        onchange="toggleLineItem(this)">
                    <span>${Number(item.accessorial_rate||0).toFixed(2)}</span>
                </div>
            </div>
        `;
    });
}

/* ================= TOGGLES ================= */
function toggleDelivery(el){
    const rate = Number(el.dataset.rate)||0;
    const id = el.value;

    if(el.checked){
        selectedDelivery.add(id);
        summary.delivery[id] = { label:'MTM '+id, rate };
        deliveryTotal += rate;
    } else {
        selectedDelivery.delete(id);
        deliveryTotal -= summary.delivery[id]?.rate||0;
        delete summary.delivery[id];
    }
    updateTotals();
}

function toggleLineItem(el){
    const rate = Number(el.dataset.rate)||0;
    const id = el.value;

    if(el.checked){
        selectedLineItems.add(id);
        summary.accessorial[id] = { label:'Item '+id, rate };
        accessorialTotal += rate;
    } else {
        selectedLineItems.delete(id);
        accessorialTotal -= summary.accessorial[id]?.rate||0;
        delete summary.accessorial[id];
    }
    updateTotals();
}

/* ================= SUMMARY ================= */
function renderSummary(){
    let d = document.getElementById('summary_delivery');
    let a = document.getElementById('summary_accessorial');
    d.innerHTML = a.innerHTML = '';

    if(Object.keys(summary.delivery).length){
        d.innerHTML += `<p class="font-semibold">Delivery</p>`;
        Object.values(summary.delivery).forEach(i =>
            d.innerHTML += `<div class="flex justify-between"><span>${i.label}</span><span>${i.rate.toFixed(2)}</span></div>`
        );
    }

    if(Object.keys(summary.accessorial).length){
        a.innerHTML += `<p class="font-semibold mt-2">Accessorial</p>`;
        Object.values(summary.accessorial).forEach(i =>
            a.innerHTML += `<div class="flex justify-between"><span>${i.label}</span><span>${i.rate.toFixed(2)}</span></div>`
        );
    }
}

/* ================= TOTALS ================= */
function updateTotals(){
    document.getElementById('delivery_total').textContent = deliveryTotal.toFixed(2);
    document.getElementById('accessorial_total').textContent = accessorialTotal.toFixed(2);
    document.getElementById('grand_total').textContent = (deliveryTotal+accessorialTotal).toFixed(2);
    renderSummary();
    document.getElementById('selected_delivery_requests').value = JSON.stringify([...selectedDelivery]);
    document.getElementById('selected_line_items').value = JSON.stringify([...selectedLineItems]);
}

function resetAll(){
    deliveryRequests=[]; lineItems={}; activeDrId=null;
    selectedDelivery.clear(); selectedLineItems.clear();
    summary={delivery:{},accessorial:{}};
    deliveryTotal=accessorialTotal=0;
    updateTotals();
    document.getElementById('delivery_requests').innerHTML='Loading...';
    document.getElementById('line_items').innerHTML='Select a delivery request';
}
</script>

@endsection
