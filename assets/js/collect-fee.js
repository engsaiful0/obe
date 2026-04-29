$(document).ready(function () {
    const $yearSelect = $('#year_id');
    const $semesterSelect = $('#semester_id');
    const $technologySelect = $('#technology_id');
    const $studentSelect = $('#student_id');
    const $feeHeadsTableBody = $('#fee_heads_table tbody');
    const $selectAllCheckbox = $('#select_all_fees');
    const $discountInput = $('#discount');
    const $totalPayableInput = $('#total_payable');
    const $netPayableInput = $('#net_payable');
    const $fineAmountInput = $('#fine_amount');
    const $studentLoadingSpinner = $('#student-loading-spinner'); // Student loading spinner

    // Function to fetch students based on selected year, semester, and optionally technology
    function fetchStudents() {
        const yearId = $yearSelect.val();
        const semesterId = $semesterSelect.val();
        const technologyId = $technologySelect.val();

        // Clear previous students and reset student select
        $studentSelect.empty().append('<option value="">Select Student</option>');

        // Clear fee heads table
        $feeHeadsTableBody.empty();

        // Reset totals
        $totalPayableInput.val('');
        $netPayableInput.val('');
        $fineAmountInput.val('৳0.00').removeClass('text-danger fw-bold');
        $discountInput.val('').prop('disabled', true);

        // Only year and semester are required
        if (yearId && semesterId) {
            // Show the spinner while fetching data
            $studentLoadingSpinner.removeClass('d-none');
            $studentSelect.prop('disabled', true);

            // Build URL - technology is optional
            let url = window.getStudentsUrl.replace(':year_id', yearId).replace(':semester_id', semesterId);
            if (technologyId) {
                url = url.replace('/:technology_id', '/' + technologyId);
            } else {
                url = url.replace('/:technology_id', '');
            }

            $.ajax({
                url: url,
                method: 'GET',
                success: function (data) {
                    console.log('Students loaded:', data.length);

                    // Append new students
                    if (data && data.length > 0) {
                        $.each(data, function (index, student) {
                            const option = $('<option>')
                                .val(student.id)
                                .text(`${student.student_unique_id} - ${student.full_name_in_english_block_letter}`);
                            $studentSelect.append(option);
                        });

                        // Show success message with filtering info
                        let filterInfo = `Year and Semester`;
                        if (technologyId) {
                            filterInfo += ` and Technology`;
                        }
                        toastr.success(`${data.length} students loaded successfully for ${filterInfo}`);
                    } else {
                        $studentSelect.append('<option value="" disabled>No students found for selected criteria</option>');
                        toastr.warning('No students found for the selected criteria');
                    }

                    // Hide the spinner after loading
                    $studentLoadingSpinner.addClass('d-none');
                    $studentSelect.prop('disabled', false);
                },
                error: function (xhr, status, error) {
                    console.error('Error fetching students:', error);

                    // Show error message
                    toastr.error('Failed to load students. Please try again.');

                    // Hide the spinner if there is an error
                    $studentLoadingSpinner.addClass('d-none');
                    $studentSelect.prop('disabled', false);

                    // Add error option
                    $studentSelect.append('<option value="" disabled>Error loading students</option>');
                }
            });
        } else {
            // If year or semester is not selected, clear students
            $studentSelect.empty().append('<option value="">Select Student</option>');
        }
    }

    // Function to check if selected months are already paid
    function checkPaidStatus() {
        const studentId = $studentSelect.val();
        const selectedMonths = $('#months').val() || [];

        if (!studentId || selectedMonths.length === 0) {
            return;
        }

        $.ajax({
            url: window.checkPaidStatusUrl,
            method: 'POST',
            data: {
                student_id: studentId,
                months: selectedMonths,
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function (response) {
                if (response.success) {
                    handlePaidStatusResponse(response);
                }
            },
            error: function (xhr) {
                console.error('Error checking paid status:', xhr.responseText);
            }
        });
    }

    // Function to handle paid status response
    function handlePaidStatusResponse(response) {
        const monthsSelect = $('#months');
        const monthsSection = $('#months_section');

        // Clear previous messages
        $('.paid-month-message').remove();

        if (response.has_paid_months) {
            // Disable paid months
            response.paid_months.forEach(function (month) {
                const option = monthsSelect.find(`option[value="${month.month_id}"]`);
                option.prop('disabled', true).addClass('paid-fee');

                // Add visual indicator
                option.text(option.text() + ' (Already Paid)');
            });

            // Show warning message
            const warningMessage = $('<div class="alert alert-warning paid-month-message mt-2">')
                .html('<i class="ti ti-alert-triangle me-2"></i><strong>Warning:</strong> Some selected months have already been paid and will be excluded from collection.');
            monthsSection.after(warningMessage);
        }

        if (response.has_unpaid_months) {
            // Enable unpaid months
            response.unpaid_months.forEach(function (month) {
                const option = monthsSelect.find(`option[value="${month.month_id}"]`);
                option.prop('disabled', false).removeClass('paid-fee');

                // Remove visual indicator if it exists
                const text = option.text().replace(' (Already Paid)', '');
                option.text(text);
            });

            // Fetch fees for unpaid months only
            const unpaidMonthIds = response.unpaid_months.map(month => month.month_id);
            fetchFeesForMonths(unpaidMonthIds);
        } else {
            // All months are paid, clear fee table
            $feeHeadsTableBody.empty();
            $totalPayableInput.val('');
            $netPayableInput.val('');
            $fineAmountInput.val('৳0.00').removeClass('text-danger fw-bold');
        }
    }

    // Function to fetch fees for specific months
    function fetchFeesForMonths(monthIds) {
        const semesterId = $semesterSelect.val();
        const feeType = $('#fee_type').val();
        const paymentDate = $('#date').val() || new Date().toISOString().split('T')[0];

        if (semesterId && feeType && monthIds.length > 0) {
            let url = window.getFeesUrl.replace(':semester_id', semesterId).replace(':fee_type', feeType);
            let params = [];

            // Add months parameter
            params.push('months=' + monthIds.join(','));

            // Add payment date for fine calculation
            params.push('payment_date=' + paymentDate);

            if (params.length > 0) {
                url += '?' + params.join('&');
            }

            $.ajax({
                url: url,
                method: 'GET',
                success: function (data) {
                    $feeHeadsTableBody.empty(); // Clear previous fee heads

                    // Check if any fee has a fine to show/hide the fine column
                    const hasFines = data.some(fee => (parseFloat(fee.fine_amount) || 0) > 0);
                    const fineColumnHeader = $('#fine_column_header');
                    if (hasFines) {
                        fineColumnHeader.show();
                    } else {
                        fineColumnHeader.hide();
                    }

                    $.each(data, function (index, fee) {
                        const fineAmount = parseFloat(fee.fine_amount) || 0;
                        const overdueDays = fee.overdue_days || 0;
                        const fineDisplay = fineAmount > 0 ? `৳${fineAmount.toFixed(2)}` : '৳0.00';
                        const overdueDisplay = overdueDays > 0 ? `(${overdueDays} days overdue)` : '';

                        const row = $('<tr>').html(`
                            <td><input id="fee_head_${fee.id}" type="checkbox" class="fee-head-checkbox" name="fee_heads[]" value="${fee.id}" data-amount="${fee.amount}" data-fine-amount="${fineAmount}" data-is-discountable="${fee.is_discountable}"></td>
                            <td id="fee_head_name_${fee.id}">${fee.name} ${overdueDisplay}</td>
                            <td id="fee_head_amount_${fee.id}">৳${parseFloat(fee.amount).toFixed(2)}</td>
                            <td id="fee_head_fine_${fee.id}" class="text-danger">${fineDisplay}</td>
                        `);
                        $feeHeadsTableBody.append(row);
                    });

                    // Update select all checkbox state
                    updateSelectAllCheckbox();

                    // Calculate totals
                    calculateTotal();
                },
                error: function (xhr) {
                    console.error('Error fetching fees:', xhr.responseText);
                    showToast('Error', 'Failed to fetch fees. Please try again.', 'error');
                }
            });
        }
    }

    // Function to fetch fee heads based on selected semester
    function fetchFees(callback) {
        const semesterId = $semesterSelect.val();
        const feeType = $('#fee_type').val();
        const selectedMonths = $('#months').val() || [];
        const paymentDate = $('#date').val() || new Date().toISOString().split('T')[0];

        if (semesterId && feeType) {
            let url = window.getFeesUrl.replace(':semester_id', semesterId).replace(':fee_type', feeType);
            let params = [];

            // Add months parameter if fee type is Monthly or Both and months are selected
            if ((feeType === 'Monthly' || feeType === 'Both') && selectedMonths.length > 0) {
                params.push('months=' + selectedMonths.join(','));
            }

            // Add payment date for fine calculation
            params.push('payment_date=' + paymentDate);

            if (params.length > 0) {
                url += '?' + params.join('&');
            }

            $.ajax({
                url: url,
                method: 'GET',
                success: function (data) {
                    $feeHeadsTableBody.empty(); // Clear previous fee heads

                    // Check if any fee has a fine to show/hide the fine column
                    const hasFines = data.some(fee => (parseFloat(fee.fine_amount) || 0) > 0);
                    const fineColumnHeader = $('#fine_column_header');
                    if (hasFines) {
                        fineColumnHeader.show();
                    } else {
                        fineColumnHeader.hide();
                    }

                    $.each(data, function (index, fee) {
                        const fineAmount = parseFloat(fee.fine_amount) || 0;
                        const overdueDays = fee.overdue_days || 0;
                        const fineDisplay = fineAmount > 0 ? `৳${fineAmount.toFixed(2)}` : '৳0.00';
                        const overdueDisplay = overdueDays > 0 ? `(${overdueDays} days overdue)` : '';

                        const row = $('<tr>').html(`
                            <td><input id="fee_head_${fee.id}" type="checkbox" class="fee-head-checkbox" name="fee_heads[]" value="${fee.id}" data-amount="${fee.amount}" data-fine-amount="${fineAmount}" data-is-discountable="${fee.is_discountable}"></td>
                            <td id="fee_head_name_${fee.id}">${fee.name} ${overdueDisplay}</td>
                            <td id="fee_head_amount_${fee.id}">৳${parseFloat(fee.amount).toFixed(2)}</td>
                           
                        `);
                        $feeHeadsTableBody.append(row);
                    });
                    if (callback) {
                        callback();
                    }
                }
            });
        }
    }

    // Calculate total payable amount based on selected fee heads and discount
    function calculateTotal() {
        let total = 0;
        let totalFineAmount = 0;
        let isDiscountable = false;

        const feeType = $('#fee_type').val();
        const selectedMonths = $('#months').val() || [];

        $('.fee-head-checkbox:checked').each(function () {
            const amount = parseFloat($(this).data('amount'));
            const fineAmount = parseFloat($(this).data('fine-amount')) || 0;
            const feeHeadName = $(this).closest('tr').find('td').eq(1).text();

            if (feeType === 'Monthly' && feeHeadName.toLowerCase().includes('monthly')) {
                total += (amount + fineAmount) * selectedMonths.length;
                totalFineAmount += fineAmount * selectedMonths.length;
            } else {
                total += amount + fineAmount;
                totalFineAmount += fineAmount;
            }

            if ($(this).data('is-discountable')) {
                isDiscountable = true;
            }
        });

        // Update fine amount field
        $fineAmountInput.val('৳' + totalFineAmount.toFixed(2));

        // Update fine amount field styling based on amount
        if (totalFineAmount > 0) {
            $fineAmountInput.addClass('text-danger fw-bold');
        } else {
            $fineAmountInput.removeClass('text-danger fw-bold');
        }

        if (isDiscountable) {
            $discountInput.prop('disabled', false);
        } else {
            $discountInput.prop('disabled', true);
            $discountInput.val(0);
        }

        const discount = parseFloat($discountInput.val()) || 0;
        total -= discount;

        $totalPayableInput.val(total.toFixed(2));
        $netPayableInput.val(total.toFixed(2));
    }

    function calculateNetTotalwithDiscount() {
        let total = 0;
        let totalFineAmount = 0;
        let isDiscountable = false;

        const feeType = $('#fee_type').val();
        const selectedMonths = $('#months').val() || [];

        $('.fee-head-checkbox:checked').each(function () {
            const amount = parseFloat($(this).data('amount'));
            const fineAmount = parseFloat($(this).data('fine-amount')) || 0;
            const feeHeadName = $(this).closest('tr').find('td').eq(1).text();

            if (feeType === 'Monthly' && feeHeadName.toLowerCase().includes('monthly')) {
                total += (amount + fineAmount) * selectedMonths.length;
                totalFineAmount += fineAmount * selectedMonths.length;
            } else {
                total += amount + fineAmount;
                totalFineAmount += fineAmount;
            }

            if ($(this).data('is-discountable')) {
                isDiscountable = true;
            }
        });

        // Update fine amount field
        $fineAmountInput.val('৳' + totalFineAmount.toFixed(2));

        // Update fine amount field styling based on amount
        if (totalFineAmount > 0) {
            $fineAmountInput.addClass('text-danger fw-bold');
        } else {
            $fineAmountInput.removeClass('text-danger fw-bold');
        }

        if (isDiscountable) {
            $discountInput.prop('disabled', false);
        } else {
            $discountInput.prop('disabled', true);
            $discountInput.val(0);
        }

        const discount = parseFloat($discountInput.val()) || 0;
        total -= discount;

        // $totalPayableInput.val(total.toFixed(2));
        $netPayableInput.val(total.toFixed(2));
    }

    // Event listeners
    $yearSelect.on('change', function () {
        // Clear students when year changes
        $studentSelect.empty().append('<option value="">Select Student</option>');
        $feeHeadsTableBody.empty();
        $totalPayableInput.val('');
        $netPayableInput.val('');
        $fineAmountInput.val('৳0.00').removeClass('text-danger fw-bold');
        $discountInput.val('').prop('disabled', true);

        // If semester is also selected, fetch students
        if ($semesterSelect.val()) {
            fetchStudents();
        }
    });

    $semesterSelect.on('change', function () {
        // Clear students when semester changes
        $studentSelect.empty().append('<option value="">Select Student</option>');
        $feeHeadsTableBody.empty();
        $totalPayableInput.val('');
        $netPayableInput.val('');
        $fineAmountInput.val('৳0.00').removeClass('text-danger fw-bold');
        $discountInput.val('').prop('disabled', true);

        // If year is also selected, fetch students
        if ($yearSelect.val()) {
            fetchStudents(); // Fetch students for the selected year and semester
        }

        // Always fetch fees when semester changes
        fetchFees(); // Fetch fees for the selected semester
    });

    // Technology dropdown change event
    $technologySelect.on('change', function () {
        // Clear students when technology changes
        $studentSelect.empty().append('<option value="">Select Student</option>');
        $feeHeadsTableBody.empty();
        $totalPayableInput.val('');
        $netPayableInput.val('');
        $fineAmountInput.val('৳0.00').removeClass('text-danger fw-bold');
        $discountInput.val('').prop('disabled', true);

        // If year and semester are also selected, fetch students
        if ($yearSelect.val() && $semesterSelect.val()) {
            fetchStudents(); // Fetch students for the selected criteria
        }
    });

    const $feeTypeSelect = $('#fee_type');
    const $monthsSection = $('#months_section');

    $feeTypeSelect.on('change', function () {
        if ($(this).val() === 'Monthly' || $(this).val() === 'Both') {
            $monthsSection.show();
        } else {
            $monthsSection.hide();
        }
        fetchFees();
    });
    $studentSelect.on('change', function () {
        const studentId = $(this).val();
        if (studentId) {
            // Clear previous fee heads
            $feeHeadsTableBody.empty();
            $totalPayableInput.val('');
            $netPayableInput.val('');
            $fineAmountInput.val('৳0.00').removeClass('text-danger fw-bold');
            $discountInput.val('').prop('disabled', true);

            // Clear previous paid month messages
            $('.paid-month-message').remove();

            // Reset month options
            $('#months option').prop('disabled', false).removeClass('paid-fee');
            $('#months option').each(function () {
                const text = $(this).text().replace(' (Already Paid)', '');
                $(this).text(text);
            });

            // Fetch fees and check paid fee heads
            fetchFees(function () {
                checkPaidFeeHeads(studentId);
            });
        } else {
            // Clear fee heads if no student selected
            $feeHeadsTableBody.empty();
            $totalPayableInput.val('');
            $netPayableInput.val('');
            $fineAmountInput.val('৳0.00').removeClass('text-danger fw-bold');
            $discountInput.val('').prop('disabled', true);
        }
    });

    function checkPaidFeeHeads(studentId) {
        const academicYearId = $yearSelect.val(); // Note: this is actually year_id but the route expects academic_year_id
        const semesterId = $semesterSelect.val();
        $.ajax({
            url: window.getPaidFeeHeadsUrl.replace(':student_id', studentId).replace(':academic_year_id', academicYearId).replace(':semester_id', semesterId),
            method: 'GET',
            success: function (paidFeeHeads) {

                const uniquePaidFeeHeadIds = [...new Set(paidFeeHeads.ids)];
                const uniquePaidMonths = [...new Set(paidFeeHeads.months)];

                // Loop through each unique paid fee head
                uniquePaidFeeHeadIds.forEach(function (feeHeadId) {
                    const checkbox = $(`#fee_head_${feeHeadId}`);
                    const feeHeadNameCell = $(`#fee_head_name_${feeHeadId}`);
                    const feeHeadAmountCell = $(`#fee_head_amount_${feeHeadId}`);

                    // Check and disable the checkbox for non-monthly fees
                    const feeHeadName = feeHeadNameCell.text();
                    if (!feeHeadName.toLowerCase().includes('monthly')) {
                        checkbox.prop('checked', true).prop('disabled', true);
                        feeHeadNameCell.append(' <span class="text-danger">(Paid)</span>');
                        feeHeadAmountCell.addClass('paid-fee');
                    }
                });

                // Disable paid months in the months dropdown
                uniquePaidMonths.forEach(function (month) {
                    $(`#months option[value="${month}"]`).prop('disabled', true).append(' (Paid)');
                });


                // Recalculate total after marking the paid fees
                calculateTotal();
            }
        });
    }



    $feeHeadsTableBody.on('change', '.fee-head-checkbox', function () {
        calculateTotal();
    });

    $selectAllCheckbox.on('change', function () {
        $('.fee-head-checkbox').prop('checked', $selectAllCheckbox.prop('checked'));
        calculateTotal();
    });

    $discountInput.on('input', calculateNetTotalwithDiscount);
    $('#months').on('change', function () {
        // Refetch fees when months selection changes for Monthly or Both fee types
        const feeType = $('#fee_type').val();
        if (feeType === 'Monthly' || feeType === 'Both') {
            // Check paid status for selected months
            checkPaidStatus();
        }
        calculateTotal();
    });

    // Refetch fees when payment date changes to recalculate fines
    $('#date').on('change', function () {
        const feeType = $('#fee_type').val();
        const semesterId = $semesterSelect.val();
        if (feeType && semesterId) {
            fetchFees();
        }
    });

    // Initialize form
    initializeForm();

    function initializeForm() {
        // Set today's date as default
        const today = new Date().toISOString().split('T')[0];
        $('#date').val(today);

        // Clear any existing data
        $studentSelect.empty().append('<option value="">Select Student</option>');
        $feeHeadsTableBody.empty();
        $totalPayableInput.val('');
        $netPayableInput.val('');
        $fineAmountInput.val('৳0.00').removeClass('text-danger fw-bold');
        $discountInput.val('').prop('disabled', true);

        console.log('Fee collection form initialized');
    }
});

// Delete Record
$(document).on('click', '.delete-fee-btn', function () {
    console.log('Delete button clicked via jQuery');
    var $button = $(this);
    var feeId = $button.data('id');
    var deleteUrl = $button.data('url');
    var row = $button.closest('tr');
    
    Swal.fire({
        title: 'Are you sure?',
        text: "You won't be able to revert this!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, delete it!',
        customClass: {
            confirmButton: 'btn btn-primary me-3',
            cancelButton: 'btn btn-label-secondary'
        },
        buttonsStyling: false
    }).then(function (result) {
        if (result.value) {
            var originalContent = $button.html();
            
            // Show button spinner
            $button.html('<i class="ti ti-loader-2 ti-spin ti-md"></i>');
            $button.prop('disabled', true).addClass('loading');
            
            // Show global spinner
            $("#global-spinner").show();
            
            $('.delete-fee-btn').prop('disabled', true);
            
            $.ajax({
                url: deleteUrl,
                type: 'DELETE',
                timeout: 30000,
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                success: function (response) {
                    console.log('Delete success response:', response);
                    toastr.success(response.message);
                    
                    row.fadeOut(300, function () {
                        $(this).remove();
                        var tbody = $('tbody');
                        if (tbody.children().length === 0) {
                            tbody.html('<tr><td colspan="9" class="text-center">No fee collections found.</td></tr>');
                        }
                    });
                    
                    setTimeout(function () {
                        window.location.reload();
                    }, 1000);
                },
                error: function (error) {
                    console.log('Delete error:', error);
                    
                    $button.html(originalContent).prop('disabled', false).removeClass('loading');
                    $('.delete-fee-btn').prop('disabled', false);
                    
                    if (error.status === 0) {
                        toastr.error('Request timeout. Please check your connection and try again.');
                    } else if (error.responseJSON && error.responseJSON.message) {
                        toastr.error(error.responseJSON.message);
                    } else {
                        toastr.error('An error occurred while deleting the record.');
                    }
                },
                complete: function () {
                    // Hide global spinner when done
                    $("#global-spinner").hide();
                }
            });
        }
    });
});


function checkPaidFeeHeadsOnEdit(studentId, academicYearId, semesterId) {
    $.ajax({
        url: window.getPaidFeeHeadsUrl.replace(':student_id', studentId).replace(':academic_year_id', academicYearId).replace(':semester_id', semesterId),
        method: 'GET',
        success: function (paidFeeHeadIds) {
            // Remove duplicates by converting the array to a Set and back to an array
            const uniquePaidFeeHeadIds = [...new Set(paidFeeHeadIds)];

            $('.form-check-input').each(function () {
                const feeHeadId = $(this).val();
                const feeHeadNameCell = $(`#fee_head_name_${feeHeadId}`);
                const feeHeadAmountCell = $(`#fee_head_amount_${feeHeadId}`);

                // Check if the current fee head is in the list of paid fee heads
                if (uniquePaidFeeHeadIds.includes(parseInt(feeHeadId))) {
                    // Disable the checkbox
                    $(this).prop('disabled', true);

                    // Add "Paid" label next to the fee head name
                    feeHeadNameCell.append(' <span class="text-danger">(Paid)</span>');

                    // Optionally, you can also add a class to the amount cell to indicate the fee is paid
                    feeHeadAmountCell.addClass('paid-fee');
                }
            });
        }
    });
}

