const root = document.querySelector('.page');
const baseUrl = root?.dataset.baseUrl || 'index.php';
const csrf = root?.dataset.csrf || '';

function post(page, data) {
  data.append('csrf', csrf);
  return fetch(`${baseUrl}?page=${page}`, { method: 'POST', body: data }).then((response) => response.json());
}

document.querySelectorAll('.ajax-status').forEach((button) => {
  button.addEventListener('click', async () => {
    const data = new FormData();
    data.append('order_id', button.dataset.id);
    data.append('status', button.dataset.status);
    const payload = await post('api-order-status', data);
    if (payload.ok) {
      location.reload();
      return;
    }
    alert(payload.error || 'Unable to update order status.');
  });
});
