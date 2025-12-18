@extends('layouts.app')

@section('content')
<div class="container mx-auto p-6">
    <h2 class="text-3xl font-semibold mb-6 text-gray-800">Create Billing</h2>

    <form action="{{ route('billing.store') }}" method="POST">
        @csrf

        {{-- ================= BILLING SUMMARY ================= --}}
        <div class="bg-indigo-50 rounded-xl p-5 shadow mb-6">
            <h3 class="font-semibold text-lg mb-3">Billing Summary</h3>

            <div class="max-h-40 overflow-y-auto text-sm space-y-3">

                {{-- DELIVERY --}}
                <div>
                    <p class="font-semibold">Delivery</p>
                    <ul id="selected-delivery-requests" class="space-y-1">
                        @php $deliveryTotal = 0; @endphp
                        @foreach($deliveryRequests as $dr)
                            <li class="flex justify-between items-center border p-2 rounded"
                                data-id="{{ $dr->id }}"
                                data-rate="{{ $dr->delivery_rate }}">
                                <span>{{ $dr->mtm }} ({{ number_format($dr->delivery_rate,2) }})</span>
                                <button type="button" class="text-red-500"
                                    onclick="removeItem('dr', {{ $dr->id }})">&times;</button>
                            </li>
                            @php $deliveryTotal += $dr->delivery_rate; @endphp
                        @endforeach
                    </ul>
                </div>

                {{-- ACCESSORIAL --}}
                <div>
                    <p class="font-semibold">Accessorial</p>
                    <ul id="selected-line-items" class="space-y-1">
                        @php $accessorialTotal = 0; @endphp
                        @foreach($lineItems as $item)
                            <li class="flex justify-between items-center border p-2 rounded"
                                data-id="{{ $item->id }}"
                                data-rate="{{ $item->accessorial_rate }}">
                                <span>{{ $item->delivery_number }} ({{ number_format($item->accessorial_rate,2) }})</span>
                                <button type="button" class="text-red-500"
                                    onclick="removeItem('li', {{ $item->id }})">&times;</button>
                            </li>
                            @php $accessorialTotal += $item->accessorial_rate; @endphp
                        @endforeach
                    </ul>
                </div>
            </div>

            {{-- TOTALS --}}
            <div class="grid grid-cols-3 gap-4 text-sm mt-4">
                <div>
                    <p class="text-gray-500">Delivery Total</p>
                    <p id="delivery_total" class="font-semibold">{{ number_format($deliveryTotal,2) }}</p>
                </div>
                <div>
                    <p class="text-gray-500">Accessorial Total</p>
                    <p id="accessorial_total" class="font-semibold">{{ number_format($accessorialTotal,2) }}</p>
                </div>
                <div>
                    <p class="text-gray-500">Grand Total</p>
                    <p id="grand_total" class="font-bold text-lg">
                        {{ number_format($deliveryTotal + $accessorialTotal,2) }}
                    </p>
                </div>
            </div>

            <button type="button"
                onclick="openModal()"
                class="mt-4 px-4 py-2 bg-indigo-600 text-white rounded-lg">
                Add Items
            </button>
        </div>

        {{-- ================= BILLING DETAILS ================= --}}
        <div class="grid grid-cols-2 gap-6 mb-6">

            <div>
                <label class="block text-sm font-medium">SOA Number</label>
                <input name="soa_number" class="w-full border rounded-lg p-3" required>
            </div>

            <div>
                <label class="block text-sm font-medium">Company</label>
                <select id="company_id" name="company_id" class="w-full border rounded-lg p-3" required>
                    @foreach($companies as $company)
                        <option value="{{ $company->id }}"
                            {{ $company->id == ($company_id ?? null) ? 'selected' : '' }}>
                            {{ $company->company_name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium">Withholding Tax</label>
                <select name="withholding_tax_id" class="w-full border rounded-lg p-3" required>
                    @foreach($withholdingTaxes as $tax)
                        <option value="{{ $tax->id }}">{{ $tax->description }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium">Billed To</label>
                <input name="billed_to" class="w-full border rounded-lg p-3" required>
            </div>

            <div>
                <label class="block text-sm font-medium">Billing Address</label>
                <textarea name="billing_address" class="w-full border rounded-lg p-3" required></textarea>
            </div>

            <div>
                <label class="block text-sm font-medium">Billing Date</label>
                <input type="date" name="billing_date" class="w-full border rounded-lg p-3" required>
            </div>
        </div>

        {{-- HIDDEN INPUTS (ARRAY SAFE) --}}
        <div id="hidden-inputs"></div>

        <button class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-3 rounded-lg">
            Create Billing
        </button>
    </form>
</div>

<!-- ================= MODAL ================= -->
<div id="modal" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-50">
    <div class="bg-white p-6 rounded-lg max-w-6xl w-full">
        <h3 class="text-lg font-semibold mb-4">Select Items</h3>

        <!-- Search -->
        <input type="text" id="modal_search" class="w-full border rounded px-3 py-2 mb-4" placeholder="Search MTM / Project">

        <div class="grid grid-cols-2 gap-4">
            <!-- Left Panel: Delivery Requests -->
            <div class="border rounded p-2 h-96 overflow-y-auto">
                <h4 class="font-semibold mb-2">Delivery Requests</h4>
                <ul id="modal-delivery-requests" class="space-y-2 text-sm"></ul>
            </div>

            <!-- Right Panel: Delivery + Accessorials -->
            <div class="border rounded p-2 h-96 overflow-y-auto">
                <h4 class="font-semibold mb-2 flex justify-between items-center">
                    Delivery & Accessorials
                    <label><input type="checkbox" id="select_all_line_items"> Select All</label>
                </h4>
                <ul id="modal-line-items" class="space-y-2 text-sm"></ul>
                <p id="modal-line-items-placeholder" class="text-gray-400 text-sm mt-2">Select a delivery request</p>
            </div>
        </div>

        <div class="flex justify-end gap-2 mt-4">
            <button onclick="closeModal()" class="px-4 py-2 bg-gray-300 rounded">Cancel</button>
            <button onclick="addSelectedItems()" class="px-4 py-2 bg-indigo-600 text-white rounded">
                Add Selected
            </button>
        </div>
    </div>
</div>


<script>
const modal = document.getElementById('modal');
const companySelect = document.getElementById('company_id');
const modalDeliveryList = document.getElementById('modal-delivery-requests');
const modalLineItemList = document.getElementById('modal-line-items');
const selectAllCheckbox = document.getElementById('select_all_line_items');

let activeDrId = null;
let modalData = { deliveryRequests: [], lineItems: {} };

function recalcTotals(){
    let d = 0, a = 0;

    document.querySelectorAll('#selected-delivery-requests li').forEach(li => {
        const rate = parseFloat(li.dataset.rate);
        if(!isNaN(rate)) d += rate;
    });

    document.querySelectorAll('#selected-line-items li').forEach(li => {
        const rate = parseFloat(li.dataset.rate);
        if(!isNaN(rate)) a += rate;
    });

    // Display with commas
    delivery_total.textContent = d.toLocaleString(undefined, {minimumFractionDigits:2, maximumFractionDigits:2});
    accessorial_total.textContent = a.toLocaleString(undefined, {minimumFractionDigits:2, maximumFractionDigits:2});
    grand_total.textContent = (d+a).toLocaleString(undefined, {minimumFractionDigits:2, maximumFractionDigits:2});

    // Hidden inputs
    const hidden = document.getElementById('hidden-inputs');
    hidden.innerHTML = '';
    document.querySelectorAll('#selected-delivery-requests li')
        .forEach(li => hidden.innerHTML += `<input type="hidden" name="delivery_requests[]" value="${li.dataset.id}">`);
    document.querySelectorAll('#selected-line-items li')
        .forEach(li => hidden.innerHTML += `<input type="hidden" name="line_items[]" value="${li.dataset.id}">`);
}


// Remove an item from billing summary
function removeItem(type, id){
    document.querySelector(`#selected-delivery-requests li[data-id="${id}"], #selected-line-items li[data-id="${id}"]`)?.remove();
    recalcTotals();
}

// Open and close modal
function openModal(){
    modal.classList.remove('hidden');
    fetchItems();
}
function closeModal(){ 
    modal.classList.add('hidden'); 
    activeDrId = null;
    modalLineItemList.innerHTML = 'Select a delivery request';
}

// Fetch items for selected company
function fetchItems(){
    const companyId = companySelect.value;
    if(!companyId) return;

    fetch("{{ route('billing.getItemsByCompany') }}",{
        method:'POST',
        headers:{
            'Content-Type':'application/json',
            'X-CSRF-TOKEN':'{{ csrf_token() }}'
        },
        body: JSON.stringify({ company_id: companyId })
    })
    .then(r => r.json())
    .then(data => {
        modalData = data;
        renderModalDeliveryRequests();
        modalLineItemList.innerHTML = 'Select a delivery request';
    });
}

// Render delivery requests in left panel
function renderModalDeliveryRequests(search=''){
    modalDeliveryList.innerHTML = '';
    const usedIds = [...document.querySelectorAll('#selected-delivery-requests li'),
                     ...document.querySelectorAll('#selected-line-items li')].map(li => li.dataset.id);

    (modalData.deliveryRequests || [])
    .filter(dr => dr.mtm.toLowerCase().includes(search) || dr.project_name.toLowerCase().includes(search))
    .forEach(dr => {
        const active = dr.id === activeDrId ? 'bg-indigo-50 border-indigo-600' : '';
        const disabled = usedIds.includes(dr.id.toString()) ? 'opacity-60 cursor-not-allowed' : '';
        modalDeliveryList.innerHTML += `
        <li class="p-2 border rounded cursor-pointer ${active} ${disabled}" onclick="selectModalDr(${dr.id})">
            <p class="font-medium">${dr.mtm}</p>
            <p class="text-xs text-gray-500">${dr.project_name}</p>
        </li>`;
    });
}

// Search in modal delivery requests
document.getElementById('modal_search').addEventListener('input', e =>
    renderModalDeliveryRequests(e.target.value.toLowerCase())
);

// Render delivery + accessorials for selected DR in right panel
function selectModalDr(drId){
    activeDrId = drId;
    renderModalDeliveryRequests(document.getElementById('modal_search').value.toLowerCase());

    modalLineItemList.innerHTML = '';
    const placeholder = document.getElementById('modal-line-items-placeholder');
    placeholder.style.display = 'none';

    const dr = modalData.deliveryRequests.find(d => d.id === drId);
    if(!dr) return;

    const usedIds = [...document.querySelectorAll('#selected-delivery-requests li'),
                     ...document.querySelectorAll('#selected-line-items li')].map(li => li.dataset.id);

    // Delivery rate
    const deliveryDisabled = usedIds.includes(dr.id.toString()) ? 'disabled opacity-60' : '';
    modalLineItemList.innerHTML += `
    <li class="flex justify-between items-center border p-2 rounded mb-2 ${deliveryDisabled}">
        <span>${dr.mtm} (${Number(dr.delivery_rate).toLocaleString(undefined, {minimumFractionDigits:2, maximumFractionDigits:2})})</span>
        <input type="checkbox" data-type="dr" data-id="${dr.id}" data-rate="${dr.delivery_rate}" ${deliveryDisabled ? 'disabled' : ''}>
    </li>`;

    // Accessorials
    (modalData.lineItems[drId] || []).forEach(li => {
        const disabled = usedIds.includes(li.id.toString()) ? 'disabled opacity-60' : '';
        modalLineItemList.innerHTML += `
        <li class="flex justify-between items-center border p-2 rounded mb-2 ${disabled}">
            <span>${li.delivery_number} (${Number(li.accessorial_rate).toLocaleString(undefined, {minimumFractionDigits:2, maximumFractionDigits:2})})</span>
            <input type="checkbox" data-type="li" data-id="${li.id}" data-rate="${li.accessorial_rate}" ${disabled ? 'disabled' : ''}>
        </li>`;
    });

    selectAllCheckbox.checked = false;
}

// Select all for current DR
selectAllCheckbox.addEventListener('change', function(){
    modalLineItemList.querySelectorAll('input[type="checkbox"]:not(:disabled)').forEach(cb => cb.checked = this.checked);
});

// Add selected items to main billing summary
function addSelectedItems() {
    document.querySelectorAll('#modal input[type="checkbox"]:checked').forEach(cb => {
        const li = cb.closest('li');
        if(!li) return; // skip invalid checkbox

        const type = cb.dataset.type; // dr or li
        const ul = type === 'dr'
            ? document.getElementById('selected-delivery-requests')
            : document.getElementById('selected-line-items');

        // Get the label safely
        let span = li.querySelector('span');
        let label = span ? span.textContent : cb.dataset.id;

        // Append to correct list
        ul.innerHTML += `
            <li data-id="${cb.dataset.id}" data-rate="${cb.dataset.rate}" class="flex justify-between border p-2 rounded">
                <span>${label}</span>
                <button type="button" class="text-red-500" onclick="removeItem('${type}',${cb.dataset.id})">&times;</button>
            </li>
        `;
    });

    recalcTotals();
    closeModal();
}

// Initialize totals on page load
document.addEventListener('DOMContentLoaded', recalcTotals);
</script>

@endsection
