/**
 * ================================
 * CART MANAGER (REBUILT)
 * Handles Guest & Logged-in Cart
 * ================================
 */
class CartManager {
    constructor() {
        this.storage = localStorage;
        this.cartKey = 'cart';
        this.apiBase = '/tematech-innovation/api/';
    }

    // ================================
    // STORAGE
    // ================================
    getCart() {
        try {
            return JSON.parse(this.storage.getItem(this.cartKey)) || {};
        } catch (e) {
            console.error('Cart parse error:', e);
            return {};
        }
    }

    saveCart(cart) {
        this.storage.setItem(this.cartKey, JSON.stringify(cart));
        this.triggerUpdate();
    }

    clearCart(clearDb = true) {
        this.storage.removeItem(this.cartKey);
        this.triggerUpdate();

        if (this.isUserLoggedIn() && clearDb) {
            this.clearCartInDatabase();
        }
    }

    // ================================
    // CART ACTIONS
    // ================================
    addItem(id, quantity = 1, data = {}) {
        const cart = this.getCart();
        if (!id) return;

        if (cart[id]) {
            cart[id].quantity += quantity;
        } else {
            cart[id] = {
                product_id: id,
                id: id,
                name: data.name || 'Product',
                price: parseFloat(data.price) || 0,
                image: data.image || '',
                category: data.category || '',
                quantity: parseInt(quantity) || 1
            };
        }

        this.saveCart(cart);

        if (this.isUserLoggedIn()) {
            this.syncCartToDatabase();
        }
    }

    async removeItem(id) {
    const cart = this.getCart();
    if (!cart[id]) return;

    // Remove the item locally
    delete cart[id];
    this.saveCart(cart);

    if (!this.isUserLoggedIn()) return;

    try {
        // Check if cart is now empty
        if (Object.keys(cart).length === 0) {
            // Call API to clear DB cart completely
            await fetch(this.apiBase + 'clear-cart.php', { method: 'POST' });
        } else {
            // Sync remaining items normally
            await this.syncCartToDatabase();
        }
    } catch (err) {
        console.error('Error updating DB after removing item:', err);
    }
}

    async updateQuantity(id, qty) {
    const cart = this.getCart();
    if (!cart[id]) return;

    qty = parseInt(qty);

    if (qty <= 0) {
        delete cart[id];
    } else {
        cart[id].quantity = qty;
    }

    this.saveCart(cart);

    if (!this.isUserLoggedIn()) return;

    try {
        if (Object.keys(cart).length === 0) {
            // If cart is now empty, clear DB cart
            await fetch(this.apiBase + 'clear-cart.php', { method: 'POST' });
        } else {
            // Sync remaining items normally
            await this.syncCartToDatabase();
        }
    } catch (err) {
        console.error('Error updating DB after quantity change:', err);
    }
}

    getItem(id) {
        return this.getCart()[id] || null;
    }

    // ================================
    // LOGIN CHECK
    // ================================
    isUserLoggedIn() {
        const userId = document.body.dataset.userId || '';
        return userId !== '';
    }

    // ================================
    // DATABASE SYNC
    // ================================
    formatCartForAPI() {
        return Object.values(this.getCart()).map(item => ({
            product_id: item.product_id,
            quantity: item.quantity
        }));
    }

async syncCartToDatabase() {
    const cart = this.formatCartForAPI();
    if (!cart.length) return false;

    try {
        const res = await fetch(this.apiBase + 'sync-cart.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ items: cart }) // ✅ FIXED
        });

        const text = await res.text(); // 👈 debug raw response
        console.log('SYNC RESPONSE:', text);

        const data = JSON.parse(text);

        return data.success === true;

    } catch (err) {
        console.error('Sync error:', err);
        return false;
    }
}

    async loadCartFromDB() {
        try {
            const res = await fetch(this.apiBase + 'get-cart.php');
            const data = await res.json();

            if (!data.success || !data.cart) return false;

            const cart = {};
            data.cart.forEach(item => {
                cart[item.product_id] = {
                    product_id: item.product_id,
                    id: item.product_id,
                    name: item.product_name,
                    price: parseFloat(item.price),
                    image: item.image,
                    category: item.category || '',
                    quantity: parseInt(item.quantity)
                };
            });

            this.saveCart(cart);
            return true;

        } catch (e) {
            console.error('Load DB cart error:', e);
            return false;
        }
    }

   async clearCartInDatabase() {
    if (!this.isUserLoggedIn()) return;

    try {
        const res = await fetch(this.apiBase + 'clear-cart.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' }
        });

        const data = await res.json();
        console.log('Clear cart response:', data);

        this.triggerUpdate(); // update UI after clearing
    } catch (err) {
        console.error('Clear DB cart error:', err);
    }
}

    // ================================
    // INITIALIZATION
    // ================================
    async initialize() {
    const isLoggedIn = this.isUserLoggedIn();
    const localCart = this.getCart();
    const hasLocalItems = Object.keys(localCart).length > 0;

    // ============================
    // GUEST → USE LOCAL STORAGE
    // ============================
    if (!isLoggedIn) {
        this.triggerUpdate();
        return;
    }

    try {
        // ============================
        // LOGGED IN → LOAD DB CART
        // ============================
        const hasDbItems = await this.loadCartFromDB();

        // ============================
        // IF LOCAL ITEMS EXIST → MERGE
        // ============================
        if (hasLocalItems) {

            const syncSuccess = await this.syncCartToDatabase();

            if (syncSuccess) {
                // Reload DB AFTER successful merge
                await this.loadCartFromDB();

                // NOW safe to clear local (optional)
                // this.storage.removeItem(this.cartKey);
            } else {
                console.warn('Cart sync failed, keeping local data');
            }
        }

        this.triggerUpdate();

    } catch (err) {
        console.error('Cart initialization error:', err);

        // FALLBACK → USE LOCAL STORAGE
        this.triggerUpdate();
    }
}

    // ================================
    // EVENTS
    // ================================
    triggerUpdate() {
        document.dispatchEvent(new Event('cartUpdated'));
    }
}

/**
 * ================================
 * GLOBAL INSTANCE
 * ================================
 */
const cart = new CartManager();

/**
 * ================================
 * FORMAT CURRENCY
 * ================================
 */
function formatCurrency(amount) {
    return 'K' + Number(amount).toLocaleString('en-ZM', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
}

/**
 * ================================
 * RENDER CART
 * ================================
 */
function renderCart() {
    const container = document.getElementById('cart-items');
    if (!container) return;

    const items = Object.values(cart.getCart());

    const subtotalElem = document.getElementById('cart-subtotal');
    const totalElem = document.getElementById('cart-total');
    const countElem = document.getElementById('cart-count');

    if (!items.length) {
        container.innerHTML = `
            <div class="empty-cart">
                <i class="fas fa-shopping-cart"></i>
                <h2>Your cart is empty</h2>
                <p>Looks like you haven't added anything yet</p>
                <a href="/tematech-innovation/index.php?page=products" class="btn-primary">
                    Start Shopping
                </a>
            </div>
        `;
        if (subtotalElem) subtotalElem.innerText = formatCurrency(0);
        if (totalElem) totalElem.innerText = formatCurrency(0);
        if (countElem) countElem.innerText = '0 items in your cart';
        return;
    }

    let subtotal = 0;
    let html = '';

    items.forEach(item => {
        subtotal += item.price * item.quantity;

        html += `
        <div class="cart-item">
            <div class="item-image">
                ${item.image ? `<img src="${item.image}" alt="${item.name}">` : `<i class="fas fa-box"></i>`}
            </div>

            <div class="item-details">
                <div>
                    <div class="item-name">${item.name}</div>
                    <div class="item-category">${item.category}</div>
                </div>
                <div class="item-price">${formatCurrency(item.price)}</div>
            </div>

            <div class="item-actions">
                <div class="quantity-control">
                    <button onclick="changeQty('${item.id}', -1)" class="quantity-btn">-</button>
                    <span>${item.quantity}</span>
                    <button onclick="changeQty('${item.id}', 1)" class="quantity-btn">+</button>
                </div>

                <button onclick="removeCartItem('${item.id}')" style="background: #e74c3c; color: #fff; border: none; padding: 5px 10px; cursor: pointer;">
                    Remove
                </button>
            </div>
        </div>
        `;
    });

    container.innerHTML = html;
    if (subtotalElem) subtotalElem.innerText = formatCurrency(subtotal);
    if (totalElem) totalElem.innerText = formatCurrency(subtotal);
    if (countElem) {
        const totalItems = items.reduce((sum, i) => sum + i.quantity, 0);
        countElem.innerText = `${totalItems} item${totalItems !== 1 ? 's' : ''}`;
    }
}

/**
 * ================================
 * GLOBAL ACTIONS
 * ================================
 */
function addToCart(id, name, price, image, category = '', qty = 1) {
    cart.addItem(id, qty, { name, price, image, category });
}

function changeQty(id, delta) {
    const item = cart.getItem(id);
    if (!item) return;
    cart.updateQuantity(id, item.quantity + delta);
}

function removeCartItem(id) {
    cart.removeItem(id);
}



/**
 * ================================
 * INIT
 * ================================
 */
document.addEventListener('DOMContentLoaded', async () => {
    await cart.initialize();
    renderCart();
    document.addEventListener('cartUpdated', renderCart);
});