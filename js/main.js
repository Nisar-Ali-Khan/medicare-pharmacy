/* =============================================
   MediCare Pharmacy — Main JavaScript
   ============================================= */

const API = {
  auth:     'php/auth.php',
  products: 'php/products.php',
  orders:   'php/orders.php'
};

// ── STATE ──
let cartCount = 0;
let currentUser = null;

// ── DOM READY ──
document.addEventListener('DOMContentLoaded', () => {
  checkAuth();
  updateCartBadge();
});

// ── AUTH ──
async function checkAuth() {
  try {
    const res  = await fetch(`${API.auth}?action=check`);
    const data = await res.json();
    currentUser = data.loggedIn ? data.user : null;
    updateNavAuth();
    if (typeof onAuthReady === 'function') onAuthReady(currentUser);
  } catch(e) { console.log('Auth check failed'); }
}

function updateNavAuth() {
  const loginBtn  = document.getElementById('nav-login');
  const logoutBtn = document.getElementById('nav-logout');
  const userName  = document.getElementById('nav-username');
  const adminLink = document.getElementById('nav-admin');

  if (currentUser) {
    loginBtn  && loginBtn.classList.add('hidden');
    logoutBtn && logoutBtn.classList.remove('hidden');
    if (userName) userName.textContent = `Hi, ${currentUser.name.split(' ')[0]}`;
    if (adminLink && currentUser.role === 'admin') adminLink.classList.remove('hidden');
  } else {
    loginBtn  && loginBtn.classList.remove('hidden');
    logoutBtn && logoutBtn.classList.add('hidden');
    if (userName) userName.textContent = '';
  }
}

async function logout() {
  await fetch(`${API.auth}?action=logout`);
  currentUser = null;
  updateNavAuth();
  showToast('Logged out successfully', 'success');
  setTimeout(() => window.location.href = 'index.html', 800);
}

// ── CART ──
async function updateCartBadge() {
  try {
    const res  = await fetch(`${API.orders}?action=get_cart`);
    const data = await res.json();
    cartCount  = data.count || 0;
    const badge = document.getElementById('cart-count');
    if (badge) {
      badge.textContent = cartCount;
      badge.style.display = cartCount > 0 ? 'flex' : 'none';
    }
  } catch(e) {}
}

async function addToCart(productId, qty = 1) {
  if (!currentUser) {
    showModal('login-modal');
    return;
  }
  try {
    const fd = new FormData();
    fd.append('product_id', productId);
    fd.append('quantity', qty);
    const res  = await fetch(`${API.orders}?action=add_to_cart`, { method: 'POST', body: fd });
    const data = await res.json();
    if (data.success) {
      showToast('Added to cart! 🛒', 'success');
      updateCartBadge();
    } else {
      showToast(data.error || 'Failed to add', 'error');
    }
  } catch(e) { showToast('Something went wrong', 'error'); }
}

async function loadCart() {
  const container = document.getElementById('cart-items');
  if (!container) return;
  container.innerHTML = '<div class="spinner"></div>';

  const res  = await fetch(`${API.orders}?action=get_cart`);
  const data = await res.json();

  if (!data.cart || data.cart.length === 0) {
    container.innerHTML = `
      <div class="empty-state">
        <span class="empty-icon">🛒</span>
        <h3>Cart is empty</h3>
        <p>Add some medicines to get started</p>
      </div>`;
    document.getElementById('cart-total-amount').textContent = 'Rs. 0';
    return;
  }

  container.innerHTML = data.cart.map(item => `
    <div class="cart-item" id="cart-item-${item.id}">
      <div class="cart-item-img">💊</div>
      <div class="cart-item-info">
        <div class="cart-item-name">${item.name}</div>
        <div class="cart-item-price">Rs. ${parseFloat(item.price).toFixed(0)}</div>
        <div class="qty-control">
          <button class="qty-btn" onclick="updateQty(${item.id}, ${item.quantity - 1})">−</button>
          <span class="qty-display">${item.quantity}</span>
          <button class="qty-btn" onclick="updateQty(${item.id}, ${item.quantity + 1})">+</button>
          <button class="qty-btn" style="color:#dc3545" onclick="removeCartItem(${item.id})">🗑</button>
        </div>
      </div>
    </div>
  `).join('');

  document.getElementById('cart-total-amount').textContent = `Rs. ${parseFloat(data.total).toFixed(0)}`;
}

async function updateQty(cartId, newQty) {
  const fd = new FormData();
  fd.append('cart_id', cartId);
  fd.append('quantity', newQty);
  await fetch(`${API.orders}?action=update_cart`, { method: 'POST', body: fd });
  loadCart();
  updateCartBadge();
}

async function removeCartItem(cartId) {
  await fetch(`${API.orders}?action=remove_cart&cart_id=${cartId}`);
  loadCart();
  updateCartBadge();
}

// ── MODAL HELPERS ──
function showModal(id) {
  document.getElementById(id)?.classList.add('active');
  document.body.style.overflow = 'hidden';
}
function closeModal(id) {
  document.getElementById(id)?.classList.remove('active');
  document.body.style.overflow = '';
}

// Close on overlay click
document.addEventListener('click', e => {
  if (e.target.classList.contains('modal-overlay')) {
    e.target.classList.remove('active');
    document.body.style.overflow = '';
  }
});

// ── CART PANEL ──
function openCart() {
  document.getElementById('cart-panel')?.classList.add('open');
  document.getElementById('cart-overlay')?.classList.add('active');
  document.body.style.overflow = 'hidden';
  loadCart();
}
function closeCart() {
  document.getElementById('cart-panel')?.classList.remove('open');
  document.getElementById('cart-overlay')?.classList.remove('active');
  document.body.style.overflow = '';
}

// ── TOAST ──
let toastTimer;
function showToast(msg, type = 'default') {
  let toast = document.getElementById('global-toast');
  if (!toast) {
    toast = document.createElement('div');
    toast.id = 'global-toast';
    toast.className = 'toast';
    document.body.appendChild(toast);
  }
  toast.textContent = msg;
  toast.className = `toast ${type} show`;
  clearTimeout(toastTimer);
  toastTimer = setTimeout(() => toast.classList.remove('show'), 3000);
}

// ── FORM HELPERS ──
function setAlert(containerId, msg, type = 'error') {
  const el = document.getElementById(containerId);
  if (el) {
    el.innerHTML = `<div class="alert alert-${type}">
      ${type === 'error' ? '⚠️' : '✅'} ${msg}
    </div>`;
  }
}

// ── LOGIN FORM ──
async function handleLogin(e) {
  e.preventDefault();
  const form  = e.target;
  const email = form.querySelector('[name=email]').value;
  const pass  = form.querySelector('[name=password]').value;
  const btn   = form.querySelector('button[type=submit]');

  btn.disabled = true;
  btn.textContent = 'Logging in...';

  const fd = new FormData();
  fd.append('action', 'login');
  fd.append('email', email);
  fd.append('password', pass);

  try {
    const res  = await fetch(API.auth, { method: 'POST', body: fd });
    const data = await res.json();
    if (data.success) {
      currentUser = data.user;
      updateNavAuth();
      closeModal('login-modal');
      showToast(`Welcome back, ${data.user.name.split(' ')[0]}! 👋`, 'success');
      updateCartBadge();
      if (typeof onLoginSuccess === 'function') onLoginSuccess(data.user);
    } else {
      setAlert('login-alert', data.error);
    }
  } catch(e) { setAlert('login-alert', 'Something went wrong'); }

  btn.disabled = false;
  btn.textContent = 'Login';
}

// ── REGISTER FORM ──
async function handleRegister(e) {
  e.preventDefault();
  const form = e.target;
  const btn  = form.querySelector('button[type=submit]');
  const fd   = new FormData(form);
  fd.append('action', 'register');

  btn.disabled = true;
  btn.textContent = 'Registering...';

  try {
    const res  = await fetch(API.auth, { method: 'POST', body: fd });
    const data = await res.json();
    if (data.success) {
      setAlert('register-alert', data.message, 'success');
      setTimeout(() => { closeModal('register-modal'); showModal('login-modal'); }, 1500);
    } else {
      setAlert('register-alert', data.error);
    }
  } catch(e) { setAlert('register-alert', 'Something went wrong'); }

  btn.disabled = false;
  btn.textContent = 'Register';
}

// ── PLACE ORDER ──
async function placeOrder() {
  const address = document.getElementById('delivery-address')?.value;
  if (!address) { showToast('Please enter delivery address', 'error'); return; }

  const btn = document.getElementById('place-order-btn');
  btn.disabled = true;
  btn.textContent = 'Placing Order...';

  const fd = new FormData();
  fd.append('address', address);

  try {
    const res  = await fetch(`${API.orders}?action=place_order`, { method: 'POST', body: fd });
    const data = await res.json();
    if (data.success) {
      closeCart();
      showToast(`Order #${data.order_id} placed! ✅`, 'success');
      updateCartBadge();
      if (typeof onOrderSuccess === 'function') onOrderSuccess(data);
    } else {
      showToast(data.error || 'Order failed', 'error');
    }
  } catch(e) { showToast('Something went wrong', 'error'); }

  btn.disabled = false;
  btn.textContent = 'Place Order';
}

// ── PRODUCTS ──
async function fetchProducts(params = {}) {
  const query = new URLSearchParams({ action: 'list', ...params });
  const res   = await fetch(`${API.products}?${query}`);
  return await res.json();
}

async function fetchCategories() {
  const res  = await fetch(`${API.products}?action=categories`);
  const data = await res.json();
  return data.categories || [];
}

function getProductEmoji(category) {
  const map = { 'Medicines': '💊', 'Vitamins & Supplements': '🌿', 'Personal Care': '🧴', 'Medical Devices': '🩺', 'Baby Care': '👶', 'First Aid': '🩹' };
  return map[category] || '💊';
}

function productCard(p) {
  const emoji = getProductEmoji(p.category_name);
  const lowStock = p.stock < 10;
  return `
    <div class="product-card">
      <div class="product-img">
        ${emoji}
        ${p.requires_prescription == 1 ? '<span class="rx-badge">Rx Required</span>' : ''}
      </div>
      <div class="product-body">
        <div class="product-category">${p.category_name || 'General'}</div>
        <div class="product-name">${p.name}</div>
        <div class="product-desc">${p.description || ''}</div>
        <div class="product-footer">
          <div>
            <div class="product-price">Rs. ${parseFloat(p.price).toFixed(0)} <span>/ unit</span></div>
            ${lowStock ? `<div class="stock-low">Only ${p.stock} left!</div>` : ''}
          </div>
          <button class="btn btn-primary btn-sm" onclick="addToCart(${p.id})">+ Cart</button>
        </div>
      </div>
    </div>`;
}
