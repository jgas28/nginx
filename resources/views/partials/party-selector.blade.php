@php
    $idPrefix = $idPrefix ?? 'party';
    $partyTypeField = $partyTypeField ?? 'party_type';
    $employeeField = $employeeField ?? 'employee_id';
    $supplierField = $supplierField ?? 'supplier_id';
    $partyRequired = (bool) ($partyRequired ?? false);
    $partyTypeValue = old($partyTypeField, $partyTypeValue ?? ($partyRequired ? 'employee' : ''));
    $employeeValue = old($employeeField, $employeeValue ?? '');
    $supplierValue = old($supplierField, $supplierValue ?? '');
    $wrapperClass = $wrapperClass ?? 'space-y-4';
    $selectClass = $selectClass ?? 'w-full border rounded px-3 py-2';
    $typeLabel = $typeLabel ?? 'Party Type';
    $typePlaceholder = $typePlaceholder ?? 'Select party type';
    $employeeLabel = $employeeLabel ?? 'Employee';
    $employeePlaceholder = $employeePlaceholder ?? 'Select employee';
    $supplierLabel = $supplierLabel ?? 'Supplier';
    $supplierPlaceholder = $supplierPlaceholder ?? 'Select supplier';
@endphp

<div
    class="{{ $wrapperClass }}"
    data-party-selector
    data-party-required="{{ $partyRequired ? 'true' : 'false' }}"
    data-default-party="{{ $partyTypeValue }}"
>
    <div>
        <label class="block mb-1 font-medium" for="{{ $idPrefix }}_type">{{ $typeLabel }}</label>
        <select
            name="{{ $partyTypeField }}"
            id="{{ $idPrefix }}_type"
            data-party-role="type"
            class="{{ $selectClass }}"
            @if($partyRequired) required @endif
        >
            <option value="">{{ $typePlaceholder }}</option>
            <option value="employee" {{ $partyTypeValue === 'employee' ? 'selected' : '' }}>Employee</option>
            <option value="supplier" {{ $partyTypeValue === 'supplier' ? 'selected' : '' }}>Supplier</option>
        </select>
    </div>

    <div data-party-role="employee-wrapper">
        <label class="block mb-1 font-medium" for="{{ $idPrefix }}_employee">{{ $employeeLabel }}</label>
        <select
            name="{{ $employeeField }}"
            id="{{ $idPrefix }}_employee"
            data-party-role="employee"
            class="{{ $selectClass }}"
        >
            <option value="">{{ $employeePlaceholder }}</option>
            @foreach ($employees as $employee)
                <option value="{{ $employee->id }}" {{ (string) $employeeValue === (string) $employee->id ? 'selected' : '' }}>
                    {{ $employee->fname }} {{ $employee->lname }}
                </option>
            @endforeach
        </select>
    </div>

    <div data-party-role="supplier-wrapper">
        <label class="block mb-1 font-medium" for="{{ $idPrefix }}_supplier">{{ $supplierLabel }}</label>
        <select
            name="{{ $supplierField }}"
            id="{{ $idPrefix }}_supplier"
            data-party-role="supplier"
            class="{{ $selectClass }}"
        >
            <option value="">{{ $supplierPlaceholder }}</option>
            @foreach ($suppliers as $supplier)
                <option value="{{ $supplier->id }}" {{ (string) $supplierValue === (string) $supplier->id ? 'selected' : '' }}>
                    {{ $supplier->supplier_name }}
                </option>
            @endforeach
        </select>
    </div>
</div>

@once
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('[data-party-selector]').forEach(function (container) {
                const typeSelect = container.querySelector('[data-party-role="type"]');
                const employeeWrapper = container.querySelector('[data-party-role="employee-wrapper"]');
                const employeeSelect = container.querySelector('[data-party-role="employee"]');
                const supplierWrapper = container.querySelector('[data-party-role="supplier-wrapper"]');
                const supplierSelect = container.querySelector('[data-party-role="supplier"]');
                const isRequired = container.dataset.partyRequired === 'true';

                if (!typeSelect || !employeeWrapper || !employeeSelect || !supplierWrapper || !supplierSelect) {
                    return;
                }

                if (!typeSelect.value && container.dataset.defaultParty) {
                    typeSelect.value = container.dataset.defaultParty;
                }

                function syncPartySelector() {
                    const selectedType = typeSelect.value;
                    const showEmployee = selectedType === 'employee';
                    const showSupplier = selectedType === 'supplier';

                    employeeWrapper.classList.toggle('hidden', !showEmployee);
                    supplierWrapper.classList.toggle('hidden', !showSupplier);

                    employeeSelect.disabled = !showEmployee;
                    supplierSelect.disabled = !showSupplier;

                    employeeSelect.required = isRequired && showEmployee;
                    supplierSelect.required = isRequired && showSupplier;
                }

                typeSelect.addEventListener('change', syncPartySelector);
                syncPartySelector();
            });
        });
    </script>
@endonce
