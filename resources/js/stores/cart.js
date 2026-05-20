// resources/js/stores/cart.js
import { defineStore } from 'pinia';

export const useCartStore = defineStore('cart', {
  state: () => ({
    items: [], // { product, quantity }
  }),
  getters: {
    subtotal(state) {
      return state.items.reduce((sum, i) => sum + i.product.selling_price * i.quantity, 0);
    },
    vatRate(state) {
      // Assume uniform VAT rate; take first item's vat_rate or 0
      return state.items[0]?.product?.vat_rate || 0;
    },
    vatAmount(state) {
      return state.items.reduce((sum, i) => sum + i.product.selling_price * i.quantity * (i.product.vat_rate / 100), 0);
    },
    total(state) {
      return this.subtotal + this.vatAmount;
    },
  },
  actions: {
    addItem(product) {
      const existing = this.items.find(i => i.product.id === product.id);
      if (existing) {
        existing.quantity += 1;
      } else {
        this.items.push({ product, quantity: 1 });
      }
    },
    increment(productId) {
      const item = this.items.find(i => i.product.id === productId);
      if (item) item.quantity += 1;
    },
    decrement(productId) {
      const item = this.items.find(i => i.product.id === productId);
      if (item) {
        item.quantity -= 1;
        if (item.quantity <= 0) this.removeItem(productId);
      }
    },
    removeItem(productId) {
      this.items = this.items.filter(i => i.product.id !== productId);
    },
    clear() {
      this.items = [];
    },
  },
});
