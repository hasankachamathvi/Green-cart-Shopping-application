// FreshMart Cart JS
// Handles: search filter, category filter, slide-out cart panel, toast

document.addEventListener('DOMContentLoaded', function () {

  // ── Build in-memory cart from PHP-rendered data ──
  const cart = {};

  if (typeof initialCart !== 'undefined') {
    initialCart.forEach(function (item) {
      const card = document.querySelector('[data-name]');
      // We store minimal data; full data fetched from card elements
      cart[item.id] = { id: item.id, qty: item.qty, name: '', price: 0, emoji: '' };
    });
  }

  // Sync badge counts on load
  Object.keys(cart).forEach(function (id) {
    const badge = document.getElementById('badge-' + id);
    if (badge && cart[id].qty > 0) {
      badge.textContent = cart[id].qty + ' in cart';
      badge.classList.add('show');
    }
  });

  updateCartCount();

  // ── Category filter ──
  window.setCategory = function (catId, btn) {
    document.querySelectorAll('.cat-chip').forEach(function (c) { c.classList.remove('active'); });
    btn.classList.add('active');

    document.querySelectorAll('.product-card').forEach(function (card) {
      if (catId === 'all' || card.dataset.category === String(catId)) {
        card.classList.remove('hidden');
      } else {
        card.classList.add('hidden');
      }
    });
  };

  // ── Search filter ──
  window.filterProducts = function () {
    const q = document.getElementById('searchInput').value.toLowerCase();
    document.querySelectorAll('.product-card').forEach(function (card) {
      const name = card.dataset.name || '';
      if (name.includes(q)) {
        card.classList.remove('hidden');
      } else {
        card.classList.add('hidden');
      }
    });
  };

  // ── Add to cart (JS-side for instant UI, real add via PHP form) ──
  window.addToCart = function (id, name, price, emoji) {
    if (typeof window.canAddToCart !== 'undefined' && !window.canAddToCart) {
      window.location.href = window.loginUrl || '../auth/login.php';
      return;
    }

    // Submit a hidden form to the PHP API
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '../api/add-to-cart.php';
    form.style.display = 'none';

    const pidInput = document.createElement('input');
    pidInput.type = 'hidden';
    pidInput.name = 'product_id';
    pidInput.value = id;
    form.appendChild(pidInput);

    const qtyInput = document.createElement('input');
    qtyInput.type = 'hidden';
    qtyInput.name = 'quantity';
    qtyInput.value = 1;
    form.appendChild(qtyInput);

    document.body.appendChild(form);

    // Optimistic UI update before submit
    if (!cart[id]) cart[id] = { id: id, qty: 0, name: name, price: price, emoji: emoji };
    cart[id].qty++;
    cart[id].name = name;
    cart[id].price = price;
    cart[id].emoji = emoji;

    const badge = document.getElementById('badge-' + id);
    if (badge) {
      badge.textContent = cart[id].qty + ' in cart';
      badge.classList.add('show');
    }

    updateCartCount();
    showToast(emoji + ' ' + name + ' added to cart!');

    // Small delay so user sees the toast, then submit
    setTimeout(function () { form.submit(); }, 400);
  };

  // ── Cart panel toggle ──
  window.toggleCart = function () {
    const overlay = document.getElementById('cartOverlay');
    const isOpen = overlay.classList.contains('open');
    if (!isOpen) renderCartPanel();
    overlay.classList.toggle('open');
    document.body.style.overflow = isOpen ? '' : 'hidden';
  };

  window.handleOverlayClick = function (e) {
    if (e.target === document.getElementById('cartOverlay')) toggleCart();
  };

  function renderCartPanel() {
    const items = Object.values(cart).filter(function (i) { return i.qty > 0; });
    const itemsEl = document.getElementById('cartItems');
    const footer = document.getElementById('cartFooter');

    if (items.length === 0) {
      itemsEl.innerHTML = '<div class="cart-empty"><div class="empty-icon">🛒</div><p>Your cart is empty</p><p style="font-size:12px;margin-top:4px">Add some fresh items!</p></div>';
      footer.style.display = 'none';
      return;
    }

    footer.style.display = 'block';
    let total = 0;

    itemsEl.innerHTML = items.map(function (item) {
      const sub = item.price * item.qty;
      total += sub;
      return '<div class="cart-item">' +
        '<div class="cart-item-emoji">' + (item.emoji || '🛒') + '</div>' +
        '<div class="cart-item-info">' +
          '<div class="cart-item-name">' + item.name + '</div>' +
          '<div class="cart-item-price">Rs. ' + item.price.toLocaleString() + ' each</div>' +
          '<div class="cart-item-qty">' +
            '<span class="qty-btn" onclick="panelUpdateQty(' + item.id + ', -1)">−</span>' +
            '<span class="qty-num">' + item.qty + '</span>' +
            '<span class="qty-btn" onclick="panelUpdateQty(' + item.id + ', 1)">+</span>' +
            '<span style="font-size:12px;color:var(--muted);margin-left:4px">= Rs.' + sub.toLocaleString() + '</span>' +
          '</div>' +
        '</div>' +
      '</div>';
    }).join('');

    document.getElementById('cartTotal').textContent = 'Rs. ' + total.toLocaleString();
  }

  window.panelUpdateQty = function (id, delta) {
    if (!cart[id]) return;
    cart[id].qty += delta;
    if (cart[id].qty <= 0) delete cart[id];
    updateCartCount();
    renderCartPanel();
  };

  function updateCartCount() {
    const total = Object.values(cart).reduce(function (s, i) { return s + i.qty; }, 0);
    const el = document.getElementById('cartCount');
    if (el) el.textContent = total;
  }

  // ── Toast ──
  let toastTimer;
  window.showToast = function (msg) {
    const t = document.getElementById('toast');
    if (!t) return;
    t.textContent = msg;
    t.classList.add('show');
    clearTimeout(toastTimer);
    toastTimer = setTimeout(function () { t.classList.remove('show'); }, 2200);
  };

});
