/* navbar.js — inject navbar & modals into every page */

document.addEventListener('DOMContentLoaded', () => {
  // Inject Navbar
  const nav = document.createElement('nav');
  nav.className = 'navbar';
  nav.innerHTML = `
    <div class="nav-inner">
      <a href="index.html" class="nav-logo">
        <div class="logo-icon">⚕</div>
        Medi<span>Care</span>
      </a>
      <ul class="nav-links">
        <li><a href="index.html">Home</a></li>
        <li><a href="products.html">Products</a></li>
        <li><a href="about.html">About</a></li>
        <li><a href="contact.html">Contact</a></li>
        <li id="nav-admin" class="hidden"><a href="admin.html">Admin</a></li>
      </ul>
      <div class="nav-actions">
        <span id="nav-username" style="font-size:0.85rem;color:var(--text-muted);font-weight:500"></span>
        <button class="btn-cart" onclick="openCart()">
          🛒 <span class="cart-badge" id="cart-count" style="display:none">0</span>
        </button>
        <button id="nav-login" class="btn btn-outline btn-sm" onclick="showModal('login-modal')">Login</button>
        <button id="nav-logout" class="btn btn-primary btn-sm hidden" onclick="logout()">Logout</button>
      </div>
    </div>`;
  document.body.insertBefore(nav, document.body.firstChild);

  // Inject Cart Panel
  document.body.insertAdjacentHTML('beforeend', `
    <div class="cart-overlay" id="cart-overlay" onclick="closeCart()"></div>
    <div class="cart-panel" id="cart-panel">
      <div class="cart-header">
        <h2 style="font-weight:800">🛒 Your Cart</h2>
        <button class="modal-close" onclick="closeCart()">✕</button>
      </div>
      <div class="cart-items" id="cart-items">
        <div class="empty-state">
          <span class="empty-icon">🛒</span>
          <h3>Cart is empty</h3>
        </div>
      </div>
      <div class="cart-footer">
        <div class="form-group">
          <label>Delivery Address</label>
          <textarea id="delivery-address" placeholder="Enter your complete delivery address..." rows="2"></textarea>
        </div>
        <div class="cart-total">
          <span>Total</span>
          <span id="cart-total-amount">Rs. 0</span>
        </div>
        <button id="place-order-btn" class="btn btn-primary btn-full" onclick="placeOrder()">Place Order</button>
      </div>
    </div>`);

  // Inject Login Modal
  document.body.insertAdjacentHTML('beforeend', `
    <div class="modal-overlay" id="login-modal">
      <div class="modal">
        <div class="modal-header">
          <h2>🔐 Login</h2>
          <button class="modal-close" onclick="closeModal('login-modal')">✕</button>
        </div>
        <div id="login-alert"></div>
        <form onsubmit="handleLogin(event)">
          <div class="form-group">
            <label>Email Address</label>
            <input type="email" name="email" placeholder="your@email.com" required>
          </div>
          <div class="form-group">
            <label>Password</label>
            <input type="password" name="password" placeholder="••••••••" required>
          </div>
          <button type="submit" class="btn btn-primary btn-full">Login</button>
        </form>
        <p style="text-align:center;margin-top:1rem;font-size:0.88rem;color:var(--text-muted)">
          Don't have an account? 
          <a href="#" onclick="closeModal('login-modal');showModal('register-modal')" style="color:var(--primary);font-weight:600">Register</a>
        </p>
      </div>
    </div>`);

  // Inject Register Modal
  document.body.insertAdjacentHTML('beforeend', `
    <div class="modal-overlay" id="register-modal">
      <div class="modal">
        <div class="modal-header">
          <h2>📝 Register</h2>
          <button class="modal-close" onclick="closeModal('register-modal')">✕</button>
        </div>
        <div id="register-alert"></div>
        <form onsubmit="handleRegister(event)">
          <div class="form-group">
            <label>Full Name</label>
            <input type="text" name="full_name" placeholder="Your full name" required>
          </div>
          <div class="form-group">
            <label>Email Address</label>
            <input type="email" name="email" placeholder="your@email.com" required>
          </div>
          <div class="form-group">
            <label>Phone Number</label>
            <input type="tel" name="phone" placeholder="+92 300 0000000">
          </div>
          <div class="form-group">
            <label>Password</label>
            <input type="password" name="password" placeholder="Min. 6 characters" required>
          </div>
          <button type="submit" class="btn btn-primary btn-full">Create Account</button>
        </form>
        <p style="text-align:center;margin-top:1rem;font-size:0.88rem;color:var(--text-muted)">
          Already registered? 
          <a href="#" onclick="closeModal('register-modal');showModal('login-modal')" style="color:var(--primary);font-weight:600">Login</a>
        </p>
      </div>
    </div>`);

  // Highlight active nav link
  const path = window.location.pathname.split('/').pop() || 'index.html';
  document.querySelectorAll('.nav-links a').forEach(a => {
    if (a.getAttribute('href') === path) a.classList.add('active');
  });
});
