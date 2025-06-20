@extends('layouts.app')

@section('title', 'Rejected Approval')

@section('content')
<div class="max-w-7xl mx-auto p-6 bg-white shadow rounded">
    <h1 class="text-2xl font-bold mb-6 text-gray-800">Rejected Cash Vouchers</h1>

    @if ($cashVouchers->isEmpty())
        <p class="text-gray-600">No rejected cash vouchers found.</p>
        @else
            <form action="{{ route('adminCV.rejectPrintMultiple') }}" method="POST" target="_blank" id="multiplePrintForm">
            @csrf

            <div class="mb-4">
                <button type="submit"
                        id="printSelectedBtn"
                        class="hidden bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 text-sm">
                    Print Selected
                </button>
            </div>
                <table class="min-w-full border border-collapse border-gray-300 text-sm text-left">
                    <thead>
                        <tr class="bg-gray-100 text-gray-700">
                            <th class="border px-4 py-2">
                                <input type="checkbox" id="selectAll" class="form-checkbox">
                            </th>
                            <th class="border px-4 py-2">CVR Number</th>
                            <th class="border px-4 py-2">Amount</th>
                            <th class="border px-4 py-2">Reject Remarks</th>
                            <th class="border px-4 py-2">Actions</th> 
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($cashVouchers as $voucher)
                            <tr class="hover:bg-gray-50">
                                <td class="border px-4 py-2">
                                    <input type="checkbox" name="voucher_ids[]" value="{{ $voucher->id }}" class="voucher-checkbox">
                                </td>
                                <td class="border px-4 py-2">{{ $voucher->cvr_number }}</td>
                                <td class="border px-4 py-2">₱{{ number_format($voucher->amount, 2) }}</td>
                                <td class="border px-4 py-2">
                                    @php $remarks = json_decode($voucher->reject_remarks, true); @endphp
                                    @if (is_array($remarks))
                                        <ul class="list-disc pl-5 space-y-1">
                                            @foreach ($remarks as $remark)
                                                <li>{{ $remark }}</li>
                                            @endforeach
                                        </ul>
                                    @else
                                        <span>{{ $voucher->reject_remarks }}</span>
                                    @endif
                                </td>
                                <td class="border px-4 py-2">
                                    <a href="{{ route('adminCV.editCVR', $voucher->id) }}"
                                    class="inline-block bg-blue-600 text-white px-3 py-1 rounded hover:bg-blue-700 text-xs transition">
                                        Edit
                                    </a>
                                    <a href="{{ route('adminCV.rejectPrintView', ['id' => $voucher->id]) }}"
                                    target="_blank"
                                    class="inline-block bg-blue-600 text-white px-3 py-1 rounded hover:bg-blue-700 text-xs transition">
                                        Print
                                    </a>
                                </td> 
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </form>
    @endif
</div>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const checkboxes = document.querySelectorAll('.voucher-checkbox');
        const selectAll = document.getElementById('selectAll');
        const printBtn = document.getElementById('printSelectedBtn');

        const togglePrintButton = () => {
            const anyChecked = [...checkboxes].some(checkbox => checkbox.checked);
            printBtn.classList.toggle('hidden', !anyChecked);
        };

        checkboxes.forEach(checkbox => {
            checkbox.addEventListener('change', togglePrintButton);
        });

        selectAll.addEventListener('change', () => {
            checkboxes.forEach(checkbox => {
                checkbox.checked = selectAll.checked;
            });
            togglePrintButton();
        });
    });
</script>
@endsection
