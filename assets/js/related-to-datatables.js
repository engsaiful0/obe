/**
 * Related To CRUD — single name field — AJAX + spinners
 */
'use strict';

let fvRelatedTo, offCanvasRelatedTo, dtRelatedTo;

const SpinnerUtils = {
    show: function (element, text) {
        text = text || 'Loading...';
        if (typeof element === 'string') {
            element = $(element);
        }
        element.prop('disabled', true);
        element.data('original-text', element.html());
        element.html(
            '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>' +
                text
        );
    },
    hide: function (element) {
        if (typeof element === 'string') {
            element = $(element);
        }
        element.prop('disabled', false);
        var t = element.data('original-text');
        if (t) {
            element.html(t);
        }
    }
};

const AjaxUtils = {
    request: function (options) {
        const defaults = {
            type: 'GET',
            dataType: 'json',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            beforeSend: function () {
                if (options.showSpinner && options.spinnerElement) {
                    SpinnerUtils.show(options.spinnerElement, options.spinnerText);
                }
            },
            complete: function () {
                if (options.showSpinner && options.spinnerElement) {
                    SpinnerUtils.hide(options.spinnerElement);
                }
            },
            success: function (response) {
                if (options.onSuccess) {
                    options.onSuccess(response);
                }
            },
            error: function (xhr) {
                var message = 'An error occurred. Please try again.';
                if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                    message = Object.values(xhr.responseJSON.errors).flat().join('<br>');
                } else if (xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                }
                if (typeof toastr !== 'undefined') {
                    toastr.error(message);
                } else {
                    alert(message);
                }
                if (options.onError) {
                    options.onError(xhr);
                }
            }
        };

        return $.ajax($.extend(defaults, options));
    }
};

document.addEventListener('DOMContentLoaded', function () {
    var formEl = document.getElementById('form-related-to-record');
    if (!formEl) {
        return;
    }

    setTimeout(function () {
        var addBtn = document.querySelector('.create-new');
        var canvas = document.getElementById('related-to-offcanvas');

        if (addBtn && canvas) {
            addBtn.addEventListener('click', function () {
                offCanvasRelatedTo =
                    offCanvasRelatedTo || new bootstrap.Offcanvas(canvas);
                formEl.reset();
                $('#form-related-to-record').removeAttr('data-record-id');
                $('#relatedToCanvasTitle').text('New Related To');
                offCanvasRelatedTo.show();
            });
        }
    }, 200);

    fvRelatedTo = FormValidation.formValidation(formEl, {
        fields: {
            name: {
                validators: {
                    notEmpty: {
                        message: 'Name is required'
                    }
                }
            }
        },
        plugins: {
            trigger: new FormValidation.plugins.Trigger(),
            bootstrap5: new FormValidation.plugins.Bootstrap5({
                eleValidClass: '',
                rowSelector: '.col-sm-12'
            }),
            submitButton: new FormValidation.plugins.SubmitButton(),
            autoFocus: new FormValidation.plugins.AutoFocus()
        },
        init: function (instance) {
            instance.on('plugins.message.placed', function (e) {
                if (
                    e.element.parentElement &&
                    e.element.parentElement.classList.contains('input-group')
                ) {
                    e.element.parentElement.insertAdjacentElement(
                        'afterend',
                        e.messageElement
                    );
                }
            });
        }
    });
});

$(function () {
    if (typeof window.relatedToUrls === 'undefined') {
        return;
    }

    var tbl = $('.datatables-basic');

    if (tbl.length) {
        dtRelatedTo = tbl.DataTable({
            processing: true,
            serverSide: false,
            ajax: {
                url: window.relatedToUrls.getData,
                type: 'GET',
                dataSrc: 'data',
                error: function () {
                    if (typeof toastr !== 'undefined') {
                        toastr.error('Failed to load Related To entries.');
                    }
                }
            },
            columns: [
                {
                    data: 'id',
                    render: function (data, type, row, meta) {
                        return meta.row + 1;
                    }
                },
                { data: 'name' },
                { data: null, orderable: false, searchable: false }
            ],
            columnDefs: [
                {
                    targets: -1,
                    title: 'Actions',
                    render: function () {
                        return (
                            '<div class="d-inline-block">' +
                            '<a href="javascript:;" class="btn btn-sm btn-text-secondary rounded-pill btn-icon related-to-edit"><i class="ti ti-pencil ti-md"></i></a>' +
                            '<a href="javascript:;" class="btn btn-sm btn-text-secondary rounded-pill btn-icon related-to-delete"><i class="ti ti-trash ti-md"></i></a>' +
                            '</div>'
                        );
                    }
                }
            ],
            order: [[1, 'asc']],
            dom:
                '<"card-header flex-column flex-md-row"<"head-label text-center"><"dt-action-buttons text-end pt-6 pt-md-0"B>><"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6 d-flex justify-content-center justify-content-md-end mt-n6 mt-md-0"f>>t<"row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
            displayLength: 10,
            lengthMenu: [7, 10, 25, 50],
            buttons: [
                {
                    text:
                        '<i class="ti ti-plus me-sm-1"></i> <span class="d-none d-sm-inline-block">Add New Record</span>',
                    className:
                        'create-new btn btn-primary waves-effect waves-light'
                }
            ],
            responsive: true,
            initComplete: function () {
                $('.card-header').first().after('<hr class="my-0">');
                $('div.head-label').html(
                    '<h5 class="card-title mb-0">Related To</h5>'
                );
            }
        });
    }

    if (fvRelatedTo) {
        fvRelatedTo.on('core.form.valid', function () {
            var recordId = $('#form-related-to-record').attr('data-record-id');
            var url = window.relatedToUrls.store;
            var method = 'POST';
            var msg = 'Related To added successfully.';

            if (recordId) {
                url = window.relatedToUrls.update + '/' + recordId;
                method = 'PUT';
                msg = 'Related To updated successfully.';
            }

            var $btn = $('#form-related-to-record button[type="submit"]');
            AjaxUtils.request({
                url: url,
                type: method,
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content'),
                    name: $('.dt-related-to-name').val()
                },
                showSpinner: true,
                spinnerElement: $btn,
                spinnerText: recordId ? 'Updating...' : 'Saving...',
                onSuccess: function () {
                    dtRelatedTo.ajax.reload(null, false);
                    if (offCanvasRelatedTo) {
                        offCanvasRelatedTo.hide();
                    }
                    $('#form-related-to-record').removeAttr('data-record-id');
                    document.getElementById('form-related-to-record').reset();
                    if (typeof toastr !== 'undefined') {
                        toastr.success(msg);
                    }
                }
            });
        });
    }

    $('.datatables-basic tbody').on('click', '.related-to-edit', function () {
        var rd = dtRelatedTo.row($(this).parents('tr')).data();
        var $btn = $(this);
        SpinnerUtils.show($btn, 'Loading...');

        offCanvasRelatedTo =
            offCanvasRelatedTo ||
            new bootstrap.Offcanvas(document.getElementById('related-to-offcanvas'));

        setTimeout(function () {
            $('.dt-related-to-name').val(rd.name || '');
            $('#form-related-to-record').attr('data-record-id', rd.id);
            $('#relatedToCanvasTitle').text('Edit Related To');
            offCanvasRelatedTo.show();
            SpinnerUtils.hide($btn);
        }, 100);
    });

    $('.datatables-basic tbody').on('click', '.related-to-delete', function () {
        var rd = dtRelatedTo.row($(this).parents('tr')).data();
        var $del = $(this);

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
        }).then(function (res) {
            if (!res.value) {
                return;
            }
            SpinnerUtils.show($del, 'Deleting...');
            AjaxUtils.request({
                url: window.relatedToUrls.destroy + '/' + rd.id,
                type: 'DELETE',
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                showSpinner: false,
                onSuccess: function () {
                    dtRelatedTo.row($del.parents('tr')).remove().draw(false);
                    SpinnerUtils.hide($del);
                    if (typeof toastr !== 'undefined') {
                        toastr.success('Related To deleted successfully.');
                    }
                },
                onError: function () {
                    SpinnerUtils.hide($del);
                }
            });
        });
    });
});
