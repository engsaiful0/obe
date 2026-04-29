@extends('layouts/layoutMaster')

@section('title', 'Edit Expense')

<!-- Page Scripts -->
@section('page-script')
<script>
    $(document).ready(function() {
        // Function to toggle bus fields visibility
        function toggleBusFields() {
            const expenseHeadSelect = $('#expense_head_id');
            const selectedOption = expenseHeadSelect.find('option:selected');
            const expenseHeadName = selectedOption.text().trim();
            const isTransport = expenseHeadName.toLowerCase() === 'transport';
            

            const busSubTypeWrapper = $('#bus_sub_type_wrapper');
            const busIdWrapper = $('#bus_id_wrapper');

            if (isTransport) {
                busSubTypeWrapper.show();
                // Show bus field only if bus sub type is selected
                const busSubTypeId = $('#bus_sub_type_id').val();
                if (busSubTypeId) {
                    busIdWrapper.show();
                } else {
                    busIdWrapper.hide();
                }
            } else {
                busSubTypeWrapper.hide();
                busIdWrapper.hide();
                // Reset values when hidden
                $('#bus_sub_type_id').val('');
                $('#bus_id').val('').html('<option value="">Select Bus</option>');
            }
        }

        // Trigger when expense head changes
        $('#expense_head_id').on('change', function() {
            toggleBusFields();
        });

        // Trigger when bus sub type changes
        $('#bus_sub_type_id').on('change', function() {
            let subTypeId = $('#bus_sub_type_id').val();

            // Show/hide bus field based on sub type selection
            if (subTypeId) {
                $('#bus_id_wrapper').show();
            } else {
                $('#bus_id_wrapper').hide();
                $('#bus_id').val('').html('<option value="">Select Bus</option>');
            }

            // Call function to load vehicles
            loadBuses(subTypeId);
        });

        // Initialize visibility on page load
        toggleBusFields();

        // Function to load vehicle list
        function loadBuses(subTypeId) {

            if (!subTypeId) {
                $('#bus_id').html('<option value="">Select Bus</option>');
                return;
            }
            var busUrlForTypeAndSubType = "{{ route('buses.get-buses-by-subtype') }}";

            $.ajax({
                url: busUrlForTypeAndSubType,
                type: 'GET',
                data: {
                    bus_sub_type_id: subTypeId
                },
                beforeSend: function() {
                    $('#bus_id').html('<option>Loading...</option>');
                },
                success: function(response) {
                    $('#bus_id').empty().append('<option value="">Select Bus</option>');

                    if (response && response.success && response.buses && response.buses.length > 0) {
                        $.each(response.buses, function(index, bus) {
                            $('#bus_id').append(
                                $('<option>', {
                                    value: bus.id,
                                    text: bus.bus_number
                                })
                            );
                        });
                    } else {
                        $('#bus_id').append('<option value="">No buses found</option>');
                    }
                },
                error: function() {
                    $('#bus_id').html('<option value="">Error loading buses</option>');
                }
            });
        }
    });
</script>
<script src="{{ asset('assets/js/expense-edit.js') }}?v={{ time() }}"></script>
@endsection

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Edit Expense</h5>
                </div>
                <div class="card-body">
                    <form id="expense-form" method="POST" action="{{ route('expenses.update', $expense->id) }}">
                        @csrf
                        @method('PUT')
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label" for="memo_no">Memo No*</label>
                                <input placeholder="Enter Memo No" type="text" class="form-control" id="memo_no" value="{{ $expense->memo_no }}" name="memo_no" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="bill_no">Bill No*</label>
                                <input placeholder="Enter Bill No" type="text" class="form-control" id="bill_no" value="{{ $expense->bill_no }}" name="bill_no" required>
                            </div>
                            <!-- Expense Head -->
                            <div class="col-md-6">
                                <label class="form-label" for="expense_head_id">Expense Head *</label>
                                <select class="form-select" id="expense_head_id" name="expense_head_id" required>
                                    <option value="">Select Expense Head</option>
                                    @foreach($expenseHeads as $expenseHead)
                                    <option value="{{ $expenseHead->id }}" {{ $expense->expense_head_id == $expenseHead->id ? 'selected' : '' }}>{{ $expenseHead->name }}</option>
                                    @endforeach
                                </select>
                                <div class="invalid-feedback"></div>
                            </div>
                            <!-- Supplier -->
                            <div class="col-md-6">
                                <label class="form-label" for="supplier_id">Supplier</label>
                                <select class="form-select" id="supplier_id" name="supplier_id">
                                    <option value="">Select Supplier</option>
                                    @foreach($suppliers as $supplier)
                                    <option value="{{ $supplier->id }}" {{ $expense->supplier_id == $supplier->id ? 'selected' : '' }}>{{ $supplier->supplier_name }}</option>
                                    @endforeach
                                </select>
                                <div class="invalid-feedback"></div>
                            </div>
                            <!-- Vehicle Sub Type -->
                            <div class="col-md-6" id="bus_sub_type_wrapper" style="display: none;">
                                <label class="form-label" for="bus_sub_type_id">Bus Sub Type</label>
                                <select class="form-select" id="bus_sub_type_id" name="bus_sub_type_id">
                                    <option value="">Select Bus Sub Type</option>
                                    @foreach($busSubTypes as $busSubType)
                                    <option value="{{ $busSubType->id }}" {{ $expense->bus_sub_type_id == $busSubType->id ? 'selected' : '' }}>{{ $busSubType->sub_type_name }}</option>
                                    @endforeach
                                </select>
                                <div class="invalid-feedback"></div>
                            </div>

                            <!-- Vehicle -->
                            <div class="col-md-6" id="bus_id_wrapper" style="display: none;">
                                <label class="form-label" for="bus_id">Bus</label>
                                <select class="form-select" id="bus_id" name="bus_id">
                                    <option value="">Select Vehicle</option>
                                    @foreach($buses as $bus)
                                    <option value="{{ $bus->id }}" {{ $expense->bus_id == $bus->id ? 'selected' : '' }}>{{ $bus->bus_number }}</option>
                                    @endforeach
                                </select>
                                <div class="invalid-feedback"></div>
                            </div>
                            <!-- Expense Date -->
                            <div class="col-md-6">
                                <label class="form-label" for="expense_date">Expense Date *</label>
                                <input type="date" class="form-control" id="expense_date" name="expense_date"
                                    value="{{ $expense->expense_date }}" required>
                                <div class="invalid-feedback"></div>
                            </div>

                            <!-- Amount -->
                            <div class="col-md-6">
                                <label class="form-label" for="amount">Amount *</label>
                                <input type="number" class="form-control" id="amount" name="amount"
                                    step="0.01" min="0" value="{{ $expense->amount }}" placeholder="0.00" required>
                                <div class="invalid-feedback"></div>
                            </div>







                            <!-- Employee -->
                            <div class="col-md-6">
                                <label class="form-label" for="employee_id">Employee</label>
                                <select title="Concerned Employee" class="form-select" id="employee_id" name="employee_id">
                                    <option value="">Select Employee</option>
                                    @foreach($employees as $employee)
                                    <option value="{{ $employee->id }}" {{ $expense->employee_id == $employee->id ? 'selected' : '' }}>{{ $employee->employee_name }}</option>
                                    @endforeach
                                </select>
                                <div class="invalid-feedback"></div>
                            </div>


                            <!-- Remarks -->
                            <div class="col-md-6">
                                <label class="form-label" for="remarks">Remarks</label>
                                <textarea class="form-control" id="remarks" name="remarks" rows="3"
                                    placeholder="Enter expense remarks">{{ $expense->remarks }}</textarea>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>

                        <div class="row mt-4">
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary me-2">
                                    <i class="ti ti-edit me-1"></i> Update Expense
                                </button>
                                <a href="{{ route('expenses.index') }}" class="btn btn-outline-secondary">
                                    <i class="ti ti-arrow-left me-1"></i> Back to List
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection