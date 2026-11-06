document.querySelectorAll('.checkout-dialog').forEach(dialog => {
  const form = dialog.querySelector('[data-checkout-form]');
  const submit = form.querySelector('[data-checkout-submit]');
  const error = form.querySelector('[data-checkout-error]');
  const coupon = form.elements.ma_km;
  let sequence = 0, controller, creating = false, created = false, ready = false;
  const money = value => Number(value).toLocaleString('vi-VN') + ' đ';
  const buttonLabel = () => form.elements.method.value === 'VNPAY' ? 'Tiếp tục với VNPay' : 'Xác nhận đặt cọc tại showroom';
  function invalidate() {
    ready = false; submit.disabled = true; form.elements.quoted_total.value = '';
    sequence++; controller?.abort();
    form.querySelectorAll('[data-total]').forEach(el => el.textContent = '—');
  }
  async function request(action, signal) {
    const body = new FormData(form); body.set('action', action);
    const response = await fetch(form.action, {method: 'POST', body, signal, headers: {'Accept': 'application/json'}});
    let data;
    try { data = await response.json(); } catch { throw new Error('Chưa kết nối được. Vui lòng thử lại hoặc tải lại giỏ hàng.'); }
    if (!response.ok) throw new Error(data.error || 'Chưa thực hiện được thao tác. Vui lòng thử lại.');
    return data;
  }
  async function preview() {
    if (creating || created) return;
    invalidate(); const version = sequence; controller = new AbortController();
    error.textContent = 'Đang cập nhật số tiền…';
    try {
      const data = await request('preview', controller.signal);
      if (version !== sequence) return;
      form.querySelectorAll('[data-total]').forEach(el => el.textContent = money(data[el.dataset.total]));
      form.elements.quoted_total.value = String(data.total);
      form.querySelector('[data-selected-vin]').textContent = 'VIN: ' + form.elements.vin.value + ' · ' + data.showroom;
      ready = true; submit.disabled = false; error.textContent = ''; submit.textContent = buttonLabel();
    } catch (err) { if (version === sequence && err.name !== 'AbortError') error.textContent = err.message; }
  }
  document.querySelector(`[data-open-checkout="${dialog.id}"]`)?.addEventListener('click', () => {
    dialog.showModal(); document.body.classList.add('modal-open'); preview();
  });
  dialog.querySelectorAll('[data-close-checkout]').forEach(button => button.addEventListener('click', () => {if (!creating) dialog.close();}));
  dialog.addEventListener('cancel', event => {if (creating) event.preventDefault();});
  dialog.addEventListener('close', () => {
    controller?.abort(); sequence++; document.body.classList.remove('modal-open');
    if (created) location.reload();
  });
  form.querySelector('[data-preview-checkout]').addEventListener('click', preview);
  form.elements.vin.addEventListener('change', preview);
  coupon.addEventListener('input', () => {invalidate(); error.textContent = 'Nhấn Áp dụng để cập nhật số tiền.';});
  form.querySelectorAll('[name=method]').forEach(el => el.addEventListener('change', () => submit.textContent = buttonLabel()));
  form.addEventListener('submit', async event => {
    event.preventDefault();
    if (creating || created || !form.reportValidity()) return;
    if (!ready) {await preview(); return;}
    creating = true; submit.disabled = true; submit.textContent = 'Đang tạo đơn…'; error.textContent = '';
    // Snapshot the fields before disabling controls so FormData keeps their values.
    const pending = request('create');
    const controls = [...dialog.querySelectorAll('button, input, select')].filter(el => !el.disabled);
    controls.forEach(el => el.disabled = true);
    try {
      const data = await pending; created = true;
      if (data.payment_url) {
        const destination = new URL(data.payment_url);
        if (destination.protocol !== 'https:' || destination.hostname !== 'sandbox.vnpayment.vn') throw new Error('Không mở được cổng thanh toán. Xem lại đơn trong mục Đơn mua xe.');
        location.assign(destination.href); return;
      }
      form.hidden = true;
      const result = dialog.querySelector('[data-checkout-result]');
      result.hidden = false;
      dialog.querySelector('[data-order-message]').textContent = `${data.contract} · ${data.status}. Số tiền cọc: ${money(data.deposit)}. Hạn giữ: ${data.deadline}.`;
      dialog.querySelector('[data-order-link]').href = data.order_url;
      result.focus(); dialog.scrollTop = 0;
    } catch (err) { error.textContent = err.message; }
    finally {
      creating = false; controls.forEach(el => el.disabled = false);
      submit.disabled = !ready || created; submit.textContent = buttonLabel();
      dialog.querySelectorAll('[data-close-checkout]').forEach(el => el.disabled = false);
    }
  });
});
