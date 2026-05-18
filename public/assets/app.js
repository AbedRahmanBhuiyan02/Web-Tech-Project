const root = document.querySelector('.page');
const baseUrl = root?.dataset.baseUrl || 'index.php';
const csrf = root?.dataset.csrf || '';

function post(page, data) {
  data.append('csrf', csrf);
  return fetch(`${baseUrl}?page=${page}`, { method: 'POST', body: data }).then((r) => r.json());
}

document.querySelectorAll('[data-validate]').forEach((form) => {
  form.addEventListener('submit', (event) => {
    const invalid = [...form.querySelectorAll('[required]')].some((field) => !field.value.trim());
    const password = form.querySelector('input[name="password"]');
    if (invalid || (password && password.value.length < 8)) {
      event.preventDefault();
      alert('Please complete required fields correctly.');
    }
  });
});

const search = document.querySelector('#medicine-search');
if (search) {
  search.addEventListener('input', async () => {
    const params = new URLSearchParams(new FormData(search));
    const response = await fetch(`${baseUrl}?page=api-medicines-search&${params}`);
    const payload = await response.json();
    const list = document.querySelector('#medicine-list');
    list.innerHTML = payload.medicines.map((m) => `
      <article class="card medicine-card">
        <div class="image-fallback">${m.name.slice(0, 1).toUpperCase()}</div>
        <span class="badge">${m.category_type}</span>
        <h3>${escapeHtml(m.name)}</h3>
        <p>${escapeHtml(m.description)}</p>
        <p><strong>${escapeHtml(m.vendor_name)}</strong> - ${escapeHtml(m.category_name)}</p>
        <div class="split"><strong>৳${Number(m.price).toFixed(2)}</strong><span>Stock ${m.availability}</span></div>
      </article>
    `).join('');
  });
}

document.addEventListener('submit', async (event) => {
  if (event.target.matches('.ajax-add-cart')) {
    event.preventDefault();
    const payload = await post('api-cart-add', new FormData(event.target));
    alert(payload.ok ? 'Added to cart.' : payload.error);
  }
  if (event.target.matches('.ajax-cart-update')) {
    event.preventDefault();
    const payload = await post('api-cart-update', new FormData(event.target));
    if (payload.ok) location.reload();
  }
  if (event.target.matches('.ajax-cart-remove')) {
    event.preventDefault();
    const payload = await post('api-cart-remove', new FormData(event.target));
    if (payload.ok) location.reload();
  }
});

document.querySelectorAll('.ajax-status').forEach((button) => {
  button.addEventListener('click', async () => {
    const data = new FormData();
    data.append('order_id', button.dataset.id);
    data.append('status', button.dataset.status);
    const payload = await post('api-order-status', data);
    if (payload.ok) location.reload();
  });
});

function escapeHtml(value) {
  return String(value).replace(/[&<>"']/g, (char) => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[char]));
}
