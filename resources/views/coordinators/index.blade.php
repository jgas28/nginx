@extends('layouts.app')

@section('title', 'FCZCNYX')

@section('content')
<div class="container">
    <!-- Filter Form -->
    <form method="GET" id="filter-form" class="mb-4 flex flex-wrap gap-4">
        <input type="text" name="mtm" placeholder="Search MTM" value="{{ request('mtm') }}"
            class="border rounded px-4 py-2">

        <input 
            type="date" 
            name="date_from" 
            value="{{ request()->filled('date_from') ? \Carbon\Carbon::parse(request('date_from'))->format('Y-m-d') : '' }}"
            class="border rounded px-4 py-2" 
            autocomplete="off">

        <input 
            type="date" 
            name="date_to" 
            value="{{ request()->filled('date_to') ? \Carbon\Carbon::parse(request('date_to'))->format('Y-m-d') : '' }}"
            class="border rounded px-4 py-2" 
            autocomplete="off">

        <input type="hidden" name="tab" id="active-tab" value="{{ request('tab', 'list') }}">
    </form>

    <!-- Tabs -->
    <div class="flex border-b space-x-4" id="tabs">
        <button class="tab-button border-b-2 px-4 py-2 text-gray-600 hover:text-blue-600" data-tab="list">List</button>
        <button class="tab-button border-b-2 px-4 py-2 text-gray-600 hover:text-blue-600" data-tab="status4">Pull Out</button>
        <button class="tab-button border-b-2 px-4 py-2 text-gray-600 hover:text-blue-600" data-tab="status8">For Allocation</button>
        <button class="tab-button border-b-2 px-4 py-2 text-gray-600 hover:text-blue-600" data-tab="status9">Allocated</button>
        <button class="tab-button border-b-2 px-4 py-2 text-gray-600 hover:text-blue-600" data-tab="status10">Delivered</button>
        <button class="tab-button border-b-2 px-4 py-2 text-gray-600 hover:text-blue-600" data-tab="status11">Hold/Cancel</button>
        <button class="tab-button border-b-2 px-4 py-2 text-gray-600 hover:text-blue-600" data-tab="staging">Staging</button>
        <button class="tab-button border-b-2 px-4 py-2 text-gray-600 hover:text-blue-600" data-tab="accessorial">Accessorial</button>
    </div>

    <!-- Tab Content -->
    <div>
        <div id="tab-list" class="tab-content"></div>
        <div id="tab-status4" class="tab-content hidden"></div>
        <div id="tab-status8" class="tab-content hidden"></div>
        <div id="tab-status9" class="tab-content hidden"></div>
        <div id="tab-status10" class="tab-content hidden"></div>
        <div id="tab-status11" class="tab-content hidden"></div>
        <div id="tab-staging" class="tab-content hidden"></div>
        <div id="tab-accessorial" class="tab-content hidden"></div>
    </div>
</div>

<!-- Scripts -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    const tabButtons = document.querySelectorAll('.tab-button');
    const tabContents = document.querySelectorAll('.tab-content');
    const filterForm = document.getElementById('filter-form');
    const tabHiddenInput = document.getElementById('active-tab');
    let debounceTimer;

    function activateTab(tabName) {
        tabContents.forEach(content => content.classList.add('hidden'));
        tabButtons.forEach(btn => {
            btn.classList.remove('border-blue-500', 'text-blue-600');
            btn.classList.add('text-gray-600');
        });

        const targetTab = document.getElementById('tab-' + tabName);
        const targetButton = document.querySelector(`.tab-button[data-tab="${tabName}"]`);

        if (targetTab && targetButton) {
            targetTab.classList.remove('hidden');
            targetButton.classList.add('border-blue-500', 'text-blue-600');
        }

        tabHiddenInput.value = tabName;

        fetchTabData(tabName);
    }

    function fetchTabData(tabName) {
        const formData = new FormData(filterForm);
        formData.set('tab', tabName);

        const query = new URLSearchParams(formData).toString();

        fetch("{{ route('coordinators.loadTabData') }}?" + query, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(res => res.text())
        .then(html => {
            const targetTab = document.getElementById('tab-' + tabName);
            if (targetTab) {
                targetTab.innerHTML = html;
                targetTab.scrollIntoView({ behavior: 'smooth' });
                initializeFilterInputs(); // rebind events after render
            }
        });
    }

    function handleFilterInput() {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => {
            const tab = tabHiddenInput.value || 'list';
            fetchTabData(tab);
        }, 400);
    }

    function initializeFilterInputs() {
        const inputs = filterForm.querySelectorAll('input');
        inputs.forEach(input => {
            input.removeEventListener('input', handleFilterInput);
            input.removeEventListener('change', handleFilterInput);

            if (input.type === 'date') {
                input.addEventListener('change', handleFilterInput);
            } else {
                input.addEventListener('input', handleFilterInput);
            }
        });
    }

    // Bind tab clicks
    tabButtons.forEach(button => {
        button.addEventListener('click', () => {
            const tab = button.dataset.tab;

            // Update hidden input
            tabHiddenInput.value = tab;

            // Update URL
            const url = new URL(window.location);
            url.searchParams.set('tab', tab);
            window.history.replaceState({}, '', url);

            activateTab(tab);
        });
    });

    // Handle pagination links
    document.addEventListener('click', function (e) {
        const paginationLink = e.target.closest('.pagination a');
        if (paginationLink) {
            e.preventDefault();
            const url = new URL(paginationLink.href);
            const tab = url.searchParams.get('tab') || 'list';

            fetch(url, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(res => res.text())
            .then(html => {
                const targetTab = document.getElementById('tab-' + tab);
                if (targetTab) {
                    targetTab.innerHTML = html;
                    initializeFilterInputs();
                }
            });
        }
    });

    // On first load
    const initialTab = new URLSearchParams(window.location.search).get('tab') || 'list';
    activateTab(initialTab);
    initializeFilterInputs();
});
</script>
@endsection
