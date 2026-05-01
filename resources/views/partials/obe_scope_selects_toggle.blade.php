{{-- Vision/Mission: show University only when scope=University; Department only when scope=Department. --}}
<script>
(function () {
  function apply(form, opts) {
    opts = opts || {};
    var isInitial = opts.initial === true;
    var typeSel = form.querySelector('select[name="type"]');
    var uWrap = form.querySelector('[data-obe-field="university"]');
    var dWrap = form.querySelector('[data-obe-field="department"]');
    var uSel = form.querySelector('select[name="university_id"]');
    var dSel = form.querySelector('select[name="department_id"]');
    if (!typeSel || !uWrap || !dWrap) return;

    var typ = typeSel.value;
    if (typ === 'University') {
      uWrap.classList.remove('d-none');
      dWrap.classList.add('d-none');
      if (!isInitial && dSel) dSel.selectedIndex = 0;
      if (uSel) uSel.required = true;
      if (dSel) dSel.required = false;
    } else if (typ === 'Department') {
      uWrap.classList.add('d-none');
      dWrap.classList.remove('d-none');
      if (!isInitial && uSel) uSel.selectedIndex = 0;
      if (uSel) uSel.required = false;
      if (dSel) dSel.required = true;
    } else {
      uWrap.classList.add('d-none');
      dWrap.classList.add('d-none');
      if (!isInitial) {
        if (uSel) uSel.selectedIndex = 0;
        if (dSel) dSel.selectedIndex = 0;
      }
      if (uSel) uSel.required = false;
      if (dSel) dSel.required = false;
    }
  }

  document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('form').forEach(function (form) {
      if (!form.querySelector('[data-obe-field="university"]')) return;
      if (!form.querySelector('[data-obe-field="department"]')) return;
      var typeSel = form.querySelector('select[name="type"]');
      if (!typeSel) return;
      apply(form, { initial: true });
      typeSel.addEventListener('change', function () {
        apply(form, { initial: false });
      });
    });
  });
})();
</script>
