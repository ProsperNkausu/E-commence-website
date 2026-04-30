/**
 * API Helper - Makes calls to api.php
 */

const API = {
    baseUrl: 'api/api.php',

    /**
     * Make API request
     */
    async request(action, options = {}) {
        const { method = 'GET', body = null, params = {} } = options;

        try {
            let url = `${this.baseUrl}?action=${action}`;

            // Add query parameters
            Object.entries(params).forEach(([key, value]) => {
                if (value !== null && value !== undefined) {
                    url += `&${key}=${encodeURIComponent(value)}`;
                }
            });

            const config = {
                method: method,
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                }
            };

            if (body && method !== 'GET') {
                config.body = JSON.stringify(body);
            }

            const response = await fetch(url, config);
            const data = await response.json();

            if (!response.ok) {
                throw new Error(data.message || 'API request failed');
            }

            return data;
        } catch (error) {
            console.error(`API Error (${action}):`, error);
            throw error;
        }
    },

    /**
     * Fetch all products
     */
    async getProducts(options = {}) {
        return this.request('products', { params: options });
    },

    /**
     * Fetch single product details
     */
    async getProductDetail(productId) {
        return this.request('product-detail', { params: { id: productId } });
    },

    /**
     * Fetch categories
     */
    async getCategories() {
        return this.request('categories');
    },

    /**
     * Get user profile
     */
    async getUserProfile() {
        return this.request('user-profile');
    },

    /**
     * Get cart items
     */
    async getCart() {
        return this.request('cart');
    },

    /**
     * Add item to cart
     */
    async addToCart(productId, quantity = 1) {
        return this.request('cart-add', {
            method: 'POST',
            body: { product_id: productId, quantity: quantity }
        });
    },

    /**
     * Get all orders (admin only)
     */
    async getOrders(options = {}) {
        return this.request('orders', { params: options });
    },

    /**
     * Get dashboard statistics (admin only)
     */
    async getDashboardStats() {
        return this.request('dashboard-stats');
    }
};

// Export for use
if (typeof module !== 'undefined' && module.exports) {
    module.exports = API;
}
