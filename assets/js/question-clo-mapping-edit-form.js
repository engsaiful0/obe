/**
 * Edit single Question–CLO row: toggle part letter by “multiple parts”, CLO → Bloom suggestion.
 */
(function () {
  function csrfToken() {
    var m = document.querySelector('meta[name="csrf-token"]');
    return m ? m.getAttribute('content') : '';
  }

  function fetchJson(url) {
    return fetch(url, {
      headers: {
        Accept: 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': csrfToken()
      },
      credentials: 'same-origin'
    }).then(function (res) {
      return res.json().then(function (data) {
        return { ok: res.ok, data: data };
      });
    }).catch(function () {
      return { ok: false, data: null };
    });
  }

  function bindMulti(form) {
    var radios = form.querySelectorAll('[data-qcm-multi-toggle]');
    var partSel = form.querySelector('[data-qcm-part-select]');
    if (!radios.length || !partSel) {
      return;
    }
    function sync() {
      var multi = form.querySelector('#qcm_has_multi_yes') && form.querySelector('#qcm_has_multi_yes').checked;
      partSel.disabled = !multi;
      if (!multi) {
        partSel.value = '';
      }
    }
    radios.forEach(function (r) {
      r.addEventListener('change', sync);
    });
    sync();
  }

  function bindCloBloom(form) {
    var clo = form.querySelector('[data-qcm-edit-clo]');
    var bloom = form.querySelector('[data-qcm-edit-bloom]');
    if (!clo || !bloom) {
      return;
    }
    var tpl = clo.getAttribute('data-url-bloom') || '';

    clo.addEventListener('change', function () {
      var opt = clo.selectedOptions[0];
      if (!opt || !clo.value) {
        bloom.value = '';
        return;
      }
      var bid = opt.getAttribute('data-bloom-id');
      if (bid && bid !== '') {
        bloom.value = bid;
      }
      if (tpl && clo.value) {
        var url = tpl.split('__CLO_ID__').join(String(clo.value));
        fetchJson(url).then(function (res) {
          if (res.ok && res.data && res.data.bloom_id) {
            bloom.value = String(res.data.bloom_id);
          }
        });
      }
    });
  }

  document.addEventListener('DOMContentLoaded', function () {
    var form = document.getElementById('qcm-edit-form');
    if (!form) {
      return;
    }
    bindMulti(form);
    bindCloBloom(form);
  });
})();
