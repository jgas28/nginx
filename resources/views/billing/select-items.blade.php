@extends('layouts.app')

@section('content')
<style>
/* Scrollable Panels */
.panel-scroll { max-height: 420px; overflow-y: auto; }
.panel-scroll-lg { max-height: 520px; overflow-y: auto; }
.panel-scroll::-webkit-scrollbar, .panel-scroll-lg::-webkit-scrollbar { width: 6px; }
.panel-scroll::-webkit-scrollbar-thumb, .panel-scroll-lg::-webkit-scrollbar-thumb { background-color: #c7d2fe; border-radius: 6px; }

/* Active selection */
.active-mtm { background-color: #eef2ff; border-color: #6366f1; }

/* Hover effect */
.delivery-item:hover, .line-item:hover { background-color: #f3f4f6; cursor: pointer; }

/* Smooth shadow & border for summary */
.billing-summary { box-shadow: 0 2px 6px rgba(0,0,0,0.05); border-radius: 1rem; }
</style>

<div class="container mx-auto max-w-7xl p-6 space-y-6">

    <!-- Billing Summary -->
    <div class="billing-summary bg-indigo-50 p-5 flex flex-col">
        <!-- Sticky Header -->
        <div class="sticky top-0 z-20 bg-indigo-50 border-b border-indigo-200 pb-3 mb-3">
            <h3 class="font-semibold text-xl mb-3">Billing Summary</h3>
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
        </div>

        <!-- Scrollable list of items -->
        <div class="overflow-y-auto max-h-64 p-2 space-y-2">
            <div id="summary_delivery" class="space-y-2"></div>
            <div id="summary_accessorial" class="space-y-2"></div>
        </div>

        <!-- Proceed button -->
        <div class="border-t border-indigo-200 pt-4 mt-4">
            <form action="{{ route('billing.storeSelection') }}" method="POST">
                @csrf
                <input type="hidden" name="delivery_requests" id="selected_delivery_requests">
                <input type="hidden" name="line_items" id="selected_line_items">
                <button id="proceed_button" class="w-full px-6 py-3 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition" disabled>
                    Proceed to Billing
                </button>
            </form>
        </div>
    </div>

    <!-- Company + Search -->
    <div class="flex gap-4">
        <select id="company_id" class="w-1/3 rounded-lg border-gray-300 p-2">
            <option value="">Select Company</option>
            @foreach($companies as $company)
                <option value="{{ $company->id }}">{{ $company->company_name }}</option>
            @endforeach
        </select>
        <input id="mtm_search" class="w-2/3 rounded-lg border-gray-300 px-4 py-2" placeholder="Search MTM or Project">
    </div>

    <!-- Panels -->
    <div class="grid grid-cols-3 gap-6">
        <!-- Delivery Requests -->
        <div class="bg-white rounded-xl shadow p-4 flex flex-col">
            <h3 class="font-semibold mb-3">Delivery Requests</h3>
            <div id="delivery_requests" class="panel-scroll space-y-2 flex-1 text-sm text-gray-700">
                Select a company
            </div>
        </div>

        <!-- Delivery & Accessorials -->
        <div class="col-span-2 bg-white rounded-xl shadow p-4 flex flex-col">
            <h3 class="font-semibold mb-3">Delivery & Accessorials</h3>
            <div id="line_items" class="panel-scroll-lg flex-1 space-y-2 text-sm text-gray-700">
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

document.getElementById('company_id').addEventListener('change', function () {
    resetAll();
    if (!this.value) return;

    fetch("{{ route('billing.getItemsByCompany') }}", {
        method: "POST",
        headers: { "Content-Type":"application/json", "X-CSRF-TOKEN":"{{ csrf_token() }}" },
        body: JSON.stringify({ company_id: this.value })
    })
    .then(r => r.json())
    .then(data => {
        deliveryRequests = data.deliveryRequests;
        lineItems = data.lineItems;
        renderDeliveryRequests();
    });
});

document.getElementById('mtm_search').addEventListener('input', e =>
    renderDeliveryRequests(e.target.value.toLowerCase())
);

function renderDeliveryRequests(search='') {
    const c = document.getElementById('delivery_requests');
    c.innerHTML = '';

    deliveryRequests
        .filter(dr => dr.mtm.toLowerCase().includes(search) || dr.project_name.toLowerCase().includes(search))
        .forEach(dr => {
            c.innerHTML += `<div onclick="selectDr(${dr.id})"
                     class="delivery-item p-3 border rounded ${activeDrId===dr.id?'active-mtm':''}">
                    <p class="font-medium">${dr.mtm}</p>
                    <p class="text-xs text-gray-500">${dr.project_name}</p>
                </div>`;
        });

    if(!c.innerHTML) c.innerHTML = '<p class="text-gray-400">No delivery requests found</p>';
}

function selectDr(id){
    activeDrId = id;
    renderDeliveryRequests(document.getElementById('mtm_search').value.toLowerCase());
    renderLineItems(id);
}

function renderLineItems(drId){
    const c = document.getElementById('line_items');
    c.innerHTML = '';
    const dr = deliveryRequests.find(d => d.id===drId);
    if(!dr) return;

    const drRate = Number(dr.delivery_rate) || 0;
    const drBilled = dr.billing_id !== null;

    // Delivery Rate
    c.innerHTML += `<div class="p-4 border rounded bg-indigo-50 mb-4 ${drBilled?'opacity-60':''}">
        <div class="flex justify-between items-center">
            <div><p class="font-semibold">Delivery Rate</p><p class="text-xs text-gray-500">${dr.mtm}</p></div>
            <div class="flex items-center space-x-2">
                <input type="checkbox" ${drBilled?'disabled':''} data-rate="${drRate}" value="dr-${dr.id}" onchange="toggleDelivery(this)">
                <span class="font-semibold">${drRate.toFixed(2)}</span>
            </div>
        </div>
        ${drBilled?'<p class="text-xs text-red-500 mt-1">Already billed</p>':''}
    </div>`;

    // Accessorials
    (lineItems[drId]||[]).forEach(item=>{
        const rate = Number(item.accessorial_rate)||0;
        const billed = item.billing_id!==null;
        c.innerHTML += `<div class="line-item flex justify-between items-center p-3 border rounded mb-2 ${billed?'opacity-60':''}">
            <div><p class="font-medium">${item.site_name}</p><p class="text-xs text-gray-500">${item.delivery_number}</p></div>
            <div class="flex items-center space-x-2">
                <input type="checkbox" ${billed?'disabled':''} data-rate="${rate}" value="li-${item.id}" onchange="toggleLineItem(this)">
                <span>${rate.toFixed(2)}</span>
            </div>
        </div>`;
    });
}

function toggleDelivery(el){
    if(el.disabled) return;
    const rate = Number(el.dataset.rate)||0;
    const id = Number(el.value.replace('dr-', ''));
    if(el.checked){
        selectedDelivery.add(id);
        summary.delivery[el.value] = { label: el.value, rate };
        deliveryTotal += rate;
    } else {
        deliveryTotal -= summary.delivery[el.value]?.rate||0;
        selectedDelivery.delete(id);
        delete summary.delivery[el.value];
    }
    updateTotals();
}

function toggleLineItem(el){
    if(el.disabled) return;
    const rate = Number(el.dataset.rate)||0;
    const id = Number(el.value.replace('li-', ''));
    if(el.checked){
        selectedLineItems.add(id);
        summary.accessorial[el.value] = { label: el.value, rate };
        accessorialTotal += rate;
    } else {
        accessorialTotal -= summary.accessorial[el.value]?.rate||0;
        selectedLineItems.delete(id);
        delete summary.accessorial[el.value];
    }
    updateTotals();
}

function renderSummary(){
    const d = document.getElementById('summary_delivery');
    const a = document.getElementById('summary_accessorial');
    d.innerHTML = a.innerHTML = '';

    if(Object.keys(summary.delivery).length){
        d.innerHTML += `<p class="font-semibold">Delivery</p>`;
        Object.keys(summary.delivery).forEach(k=>{
            const id = k.replace('dr-','');
            const dr = deliveryRequests.find(d=>d.id==id);
            if(dr) {
                d.innerHTML += `
                <div class="flex justify-between items-center">
                    <span>${dr.mtm}</span>
                    <span class="flex items-center space-x-2">
                        <span>${summary.delivery[k].rate.toFixed(2)}</span>
                        <button onclick="removeSummaryItem('${k}')" class="text-red-500 hover:text-red-700 font-bold">×</button>
                    </span>
                </div>`;
            }
        });
    }

    if(Object.keys(summary.accessorial).length){
        a.innerHTML += `<p class="font-semibold mt-2">Accessorial</p>`;
        Object.keys(summary.accessorial).forEach(k=>{
            const id = k.replace('li-','');
            for(let drId in lineItems){
                const item = lineItems[drId].find(li=>li.id==id);
                if(item){
                    a.innerHTML += `
                    <div class="flex justify-between items-center">
                        <span>${item.delivery_number}</span>
                        <span class="flex items-center space-x-2">
                            <span>${summary.accessorial[k].rate.toFixed(2)}</span>
                            <button onclick="removeSummaryItem('${k}')" class="text-red-500 hover:text-red-700 font-bold">×</button>
                        </span>
                    </div>`;
                    break;
                }
            }
        });
    }
}

function removeSummaryItem(key){
    // Remove from selected set
    if(key.startsWith('dr-')){
        const id = Number(key.replace('dr-', ''));
        selectedDelivery.delete(id);
        deliveryTotal -= summary.delivery[key]?.rate||0;
        delete summary.delivery[key];

        // Uncheck checkbox in panel
        const checkbox = document.querySelector(`input[value='${key}']`);
        if(checkbox) checkbox.checked = false;
    }
    else if(key.startsWith('li-')){
        const id = Number(key.replace('li-', ''));
        selectedLineItems.delete(id);
        accessorialTotal -= summary.accessorial[key]?.rate||0;
        delete summary.accessorial[key];

        // Uncheck checkbox in panel
        const checkbox = document.querySelector(`input[value='${key}']`);
        if(checkbox) checkbox.checked = false;
    }

    updateTotals();
}


function updateTotals(){
    document.getElementById('delivery_total').textContent = deliveryTotal.toFixed(2);
    document.getElementById('accessorial_total').textContent = accessorialTotal.toFixed(2);
    document.getElementById('grand_total').textContent = (deliveryTotal+accessorialTotal).toFixed(2);

    document.getElementById('selected_delivery_requests').value = JSON.stringify([...selectedDelivery]);
    document.getElementById('selected_line_items').value = JSON.stringify([...selectedLineItems]);

    renderSummary();

    document.getElementById('proceed_button').disabled = !(selectedDelivery.size || selectedLineItems.size);
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
