const root = document.querySelector('.page');
const baseUrl = root?.dataset.baseUrl || 'index.php';
const csrf = root?.dataset.csrf || '';
const userRole = root?.dataset.userRole || '';
const publicBase = baseUrl.replace(/index\.php.*$/, '');

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
  syncTypeCategory(search);
  const runSearch = async () => {
    syncTypeCategory(search);
    const params = new URLSearchParams(new FormData(search));
    const response = await fetch(`${baseUrl}?page=api-medicines-search&${params}`);
    const payload = await response.json();
    const list = document.querySelector('#medicine-list');
    list.innerHTML = payload.medicines.map((m) => `
      <article class="card medicine-card">
        ${m.image_path ? `<img class="medicine-image" src="${escapeHtml(assetUrl(m.image_path))}" alt="${escapeHtml(m.name)}" data-fallback-name="${escapeHtml(m.name)}">` : `<div class="image-fallback">${m.name.slice(0, 1).toUpperCase()}</div>`}
        <span class="badge">${m.category_type}</span>
        <h3>${escapeHtml(m.name)}</h3>
        <p>${escapeHtml(m.description)}</p>
        <p><strong>${escapeHtml(m.vendor_name)}</strong> - ${escapeHtml(m.category_name)}</p>
        <div class="split"><strong>Tk ${Number(m.price).toFixed(2)}</strong><span>Stock ${m.availability}</span></div>
        ${userRole === 'customer' ? `
          <form class="ajax-add-cart">
            <input type="hidden" name="csrf" value="${escapeHtml(csrf)}">
            <input type="hidden" name="medicine_id" value="${Number(m.id)}">
            <input type="number" name="quantity" value="1" min="1" max="${Number(m.availability)}">
            <button type="submit">Add to Cart</button>
          </form>
        ` : ''}
      </article>
    `).join('');
  };
  search.addEventListener('input', runSearch);
  search.addEventListener('change', runSearch);
}

document.querySelectorAll('form[data-validate="medicine"]').forEach((form) => {
  syncTypeCategory(form);
  form.addEventListener('change', () => syncTypeCategory(form));
});

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
    else alert(payload.error || 'Unable to update cart.');
  }
  if (event.target.matches('.ajax-cart-remove')) {
    event.preventDefault();
    const payload = await post('api-cart-remove', new FormData(event.target));
    if (payload.ok) location.reload();
    else alert(payload.error || 'Unable to remove item.');
  }
});

document.addEventListener('error', (event) => {
  if (event.target.matches('.medicine-image')) {
    event.target.replaceWith(imageFallback(event.target.dataset.fallbackName));
  }
}, true);

document.querySelectorAll('.ajax-status').forEach((button) => {
  button.addEventListener('click', async () => {
    const data = new FormData();
    data.append('order_id', button.dataset.id);
    data.append('status', button.dataset.status);
    const payload = await post('api-order-status', data);
    if (payload.ok) location.reload();
    else alert(payload.error || 'Unable to update order.');
  });
});

function escapeHtml(value) {
  return String(value).replace(/[&<>"']/g, (char) => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[char]));
}

function assetUrl(path) {
  if (/^https?:\/\//.test(path) || path.startsWith('/')) return path;
  return `${publicBase}${path}`;
}

function imageFallback(name) {
  const fallback = document.createElement('div');
  fallback.className = 'image-fallback';
  fallback.textContent = String(name || '?').slice(0, 1).toUpperCase();
  return fallback;
}

function syncTypeCategory(scope) {
  const typeSelect = scope.querySelector('select[name="type"], select[name="medicine_type"]');
  const categorySelect = scope.querySelector('select[name="category_id"]');
  if (!typeSelect || !categorySelect) return;

  const selectedType = typeSelect.value;
  [...categorySelect.options].forEach((option) => {
    const optionType = option.dataset.type || '';
    option.hidden = selectedType !== '' && optionType !== '' && optionType !== selectedType;
  });

  const selectedOption = categorySelect.selectedOptions[0];
  if (selectedOption?.hidden) {
    categorySelect.value = '';
  }
}
