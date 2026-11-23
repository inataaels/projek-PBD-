document.addEventListener('DOMContentLoaded', function () {
  const body = document.getElementById('rows'), tpl = document.getElementById('tpl');
  if (!body || !tpl) return;
  const margin = document.getElementById('margin');
  const fmt = n => Number(n).toLocaleString('id-ID');
  const pct = () => (margin && margin.selectedOptions[0]) ? parseFloat(margin.selectedOptions[0].dataset.persen || 0) : 0;

  function hitung() {
    let s = 0;
    body.querySelectorAll('tr').forEach(tr => {
      const j = +tr.querySelector('.jml').value || 0, h = +tr.querySelector('.hrg').value || 0;
      tr.querySelector('.sub').textContent = fmt(j * h);
      s += j * h;
    });
    const ppn = Math.round(s * (+document.querySelector('.totals').dataset.ppn) / 100);
    document.getElementById('tSub').textContent = fmt(s);
    document.getElementById('tPpn').textContent = fmt(ppn);
    document.getElementById('tTot').textContent = fmt(s + ppn);
  }
  function isiHarga(tr) {
    const o = tr.querySelector('.brg').selectedOptions[0];
    if (o && o.dataset.harga) tr.querySelector('.hrg').value = Math.round(o.dataset.harga * (1 + pct() / 100));
  }
  const tambah = () => body.appendChild(tpl.content.cloneNode(true));

  document.getElementById('addRow').onclick = tambah;
  body.addEventListener('click', e => {
    if (e.target.classList.contains('del')) { e.target.closest('tr').remove(); hitung(); }
  });
  body.addEventListener('change', e => {
    if (e.target.classList.contains('brg')) isiHarga(e.target.closest('tr'));
    hitung();
  });
  body.addEventListener('input', hitung);
  if (margin) margin.addEventListener('change', () => { body.querySelectorAll('tr').forEach(isiHarga); hitung(); });
  tambah();
});
