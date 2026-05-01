/**
 * AJAX for OBE CRUD:
 * - data-ajax-submit: intercept native form submit (missions / peos etc.)
 * - data-async-no-submit: vision-style — no submit event; buttons .obe-async-save trigger fetch()
 * - data-ajax-delete: delete via form POST+_method DELETE
 * - button[data-async-delete-url]: delete without wrapping <form submit>
 *
 * Spinner: .obe-btn-spinner + .obe-btn-label inside buttons.
 */
(function () {
  if (window.__obeAjaxCrudBound) {
    return;
  }
  window.__obeAjaxCrudBound = true;

  function csrfToken() {
    var m = document.querySelector('meta[name="csrf-token"]');
    return m ? m.getAttribute('content') : '';
  }

  function escapeHtml(s) {
    var div = document.createElement('div');
    div.textContent = s;
    return div.innerHTML;
  }

  function setBtnLoading(btn, loading) {
    if (!btn) return;
    btn.disabled = !!loading;
    var spin = btn.querySelector('.obe-btn-spinner');
    var label = btn.querySelector('.obe-btn-label');
    if (spin) spin.classList.toggle('d-none', !loading);
    if (label) label.classList.toggle('opacity-50', loading);
  }

  function showAjaxErrors(form, payload) {
    var box = form.querySelector('[data-ajax-errors]');
    if (!box) return;
    var errors = payload && payload.errors ? payload.errors : null;
    var message = payload && payload.message ? payload.message : '';

    if (errors && typeof errors === 'object') {
      var parts = [];
      Object.keys(errors).forEach(function (key) {
        var msgs = errors[key];
        if (Array.isArray(msgs)) {
          msgs.forEach(function (t) { parts.push(t); });
        }
      });
      if (parts.length) {
        box.classList.remove('d-none');
        box.innerHTML = '<ul class="mb-0">' + parts.map(function (t) {
          return '<li>' + escapeHtml(String(t)) + '</li>';
        }).join('') + '</ul>';
        return;
      }
    }

    if (message) {
      box.classList.remove('d-none');
      box.innerHTML = '<div>' + escapeHtml(message) + '</div>';
    }
  }

  function clearAjaxErrors(form) {
    var box = form.querySelector('[data-ajax-errors]');
    if (box) {
      box.classList.add('d-none');
      box.innerHTML = '';
    }
  }

  /**
   * Vision / Swal-enhanced deletes: SweetAlert confirm; otherwise window.confirm.
   * Returns a Promise that resolves true if user confirmed.
   */
  function confirmDeleteDialog(message, options) {
    options = options || {};
    var title = options.title || '';
    var yes = options.confirmText || 'Yes, delete';
    var cancel = options.cancelText || 'Cancel';

    if (typeof Swal !== 'undefined' && Swal.fire) {
      var swalCfg = {
        text: message,
        icon: options.icon || 'warning',
        showCancelButton: true,
        confirmButtonText: yes,
        cancelButtonText: cancel,
        focusCancel: true,
        reverseButtons: true,
        buttonsStyling: false,
        customClass: {
          confirmButton: 'btn btn-danger me-2',
          cancelButton: 'btn btn-label-secondary'
        }
      };
      if (title) {
        swalCfg.title = title;
      }
      return Swal.fire(swalCfg).then(function (result) {
        return !!result.isConfirmed;
      });
    }

    return Promise.resolve(window.confirm(message));
  }

  /** Store / update: FormData(form), respects _method spoofing */
  function runAsyncStoreOrUpdate(form, submitBtn) {
    clearAjaxErrors(form);

    var btn = submitBtn || form.querySelector('button[type="submit"].obe-ajax-primary');
    if (!btn) btn = form.querySelector('button.obe-async-save');
    if (!btn) btn = form.querySelector('button[type="submit"]');

    setBtnLoading(btn, true);

    var fd = new FormData(form);
    var url = form.getAttribute('action');
    var method = (form.getAttribute('method') || 'POST').toUpperCase();

    fetch(url, {
      method: method,
      body: fd,
      headers: {
        Accept: 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': csrfToken()
      },
      credentials: 'same-origin'
    })
      .then(function (res) {
        return res.json().then(function (data) {
          return { ok: res.ok, status: res.status, data: data };
        }).catch(function () {
          return { ok: false, status: res.status, data: { message: 'Invalid response.' } };
        });
      })
      .then(function (result) {
        if (result.ok) {
          var d = result.data || {};
          if (typeof toastr !== 'undefined' && d.message) {
            toastr.success(d.message);
          }
          if (d.redirect) {
            window.location.href = d.redirect;
          } else {
            window.location.reload();
          }
          return;
        }
        if (result.status === 422) {
          showAjaxErrors(form, result.data);
          if (typeof toastr !== 'undefined' && result.data && result.data.message) {
            toastr.error(result.data.message);
          }
        } else {
          showAjaxErrors(form, {
            message: (result.data && result.data.message) ? result.data.message : 'Request failed.'
          });
          if (typeof toastr !== 'undefined') {
            toastr.error((result.data && result.data.message) ? result.data.message : 'Request failed.');
          }
        }
      })
      .catch(function () {
        showAjaxErrors(form, { message: 'Network error.' });
        if (typeof toastr !== 'undefined') {
          toastr.error('Network error.');
        }
      })
      .finally(function () {
        setBtnLoading(btn, false);
      });
  }

  function runAsyncDestroy(delBtn, deleteUrl, formOptional) {
    var fd = formOptional ? new FormData(formOptional) : (function () {
      var fd0 = new FormData();
      fd0.append('_token', csrfToken());
      fd0.append('_method', 'DELETE');
      return fd0;
    })();

    setBtnLoading(delBtn, true);

    fetch(deleteUrl, {
      method: 'POST',
      body: fd,
      headers: {
        Accept: 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': csrfToken()
      },
      credentials: 'same-origin'
    })
      .then(function (res) {
        return res.json().then(function (data) {
          return { ok: res.ok, data: data };
        }).catch(function () {
          return { ok: false, data: { message: 'Invalid response.' } };
        });
      })
      .then(function (result) {
        if (result.ok) {
          var d = result.data || {};
          if (typeof toastr !== 'undefined' && d.message) {
            toastr.success(d.message);
          }
          if (d.redirect) {
            window.location.href = d.redirect;
          } else {
            window.location.reload();
          }
        } else if (typeof toastr !== 'undefined') {
          toastr.error((result.data && result.data.message) ? result.data.message : 'Delete failed.');
        }
      })
      .catch(function () {
        if (typeof toastr !== 'undefined') {
          toastr.error('Network error.');
        }
      })
      .finally(function () {
        setBtnLoading(delBtn, false);
      });
  }

  /* Classic: intercept submit */
  document.addEventListener('submit', function (e) {
    var form = e.target;
    if (!(form instanceof HTMLFormElement)) return;

    if (form.hasAttribute('data-async-no-submit')) {
      e.preventDefault();
      return false;
    }

    if (form.hasAttribute('data-ajax-submit')) {
      e.preventDefault();
      runAsyncStoreOrUpdate(form, form.querySelector('button[type="submit"].obe-ajax-primary') || form.querySelector('button[type="submit"]'));
      return false;
    }

    if (form.hasAttribute('data-ajax-delete')) {
      e.preventDefault();
      var confirmMsg = form.getAttribute('data-confirm') || 'Are you sure?';
      var swalTitleForm = form.getAttribute('data-swal-title') || '';
      var yesForm = form.getAttribute('data-confirm-yes') || '';
      var noForm = form.getAttribute('data-confirm-no') || '';

      confirmDeleteDialog(confirmMsg, {
        title: swalTitleForm,
        confirmText: yesForm || undefined,
        cancelText: noForm || undefined
      }).then(function (confirmed) {
        if (!confirmed) return;
        var delBtn =
          form.querySelector('button[type="submit"].obe-ajax-delete-btn') ||
          form.querySelector('button[type="submit"]');
        runAsyncDestroy(delBtn, form.getAttribute('action'), form);
      });

      return false;
    }

    return;
  });

  /** Vision-style: Save / Update without form.submit — button type="button" + .obe-async-save */
  document.addEventListener('click', function (e) {
    var saveBtn = e.target.closest('button.obe-async-save');
    if (saveBtn) {
      var visionForm = saveBtn.closest('form[data-async-no-submit]');
      if (!visionForm) return;
      e.preventDefault();
      runAsyncStoreOrUpdate(visionForm, saveBtn);
      return;
    }

    var delStandalone = e.target.closest('button[data-async-delete-url]');
    if (delStandalone && delStandalone.closest('tbody')) {
      e.preventDefault();
      var confirmMsg = delStandalone.getAttribute('data-confirm') || 'Are you sure?';
      var swalTitle = delStandalone.getAttribute('data-swal-title') || '';
      var yesText = delStandalone.getAttribute('data-confirm-yes') || '';
      var noText = delStandalone.getAttribute('data-confirm-no') || '';

      confirmDeleteDialog(confirmMsg, {
        title: swalTitle,
        confirmText: yesText || undefined,
        cancelText: noText || undefined
      }).then(function (confirmed) {
        if (!confirmed) return;
        var delUrl = delStandalone.getAttribute('data-async-delete-url');
        runAsyncDestroy(delStandalone, delUrl, null);
      });
    }
  });

})();
