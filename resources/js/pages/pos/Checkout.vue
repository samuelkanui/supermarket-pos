<?php
// resources/js/pages/pos/Checkout.vue (Vue 3 + Inertia)
// Premium split-screen checkout with barcode input, cart preview, and payment selection.

<template>
  <div class="checkout-screen flex h-screen bg-gradient-to-br from-[#1e3a8a] to-[#3b82f6] p-6">
    <div v-if="flashMessage" class="p-4 mb-4 bg-green-200 text-green-800 rounded">{{ flashMessage }}</div>
    <!-- Left: Product list & barcode input -->
    <section class="flex-1 mr-4 flex flex-col">
      <div class="mb-4">
        <input
          v-model="barcode"
          @keyup.enter="addProductByBarcode"
          placeholder="Scan barcode…"
          class="w-full rounded-lg bg-white bg-opacity-20 backdrop-blur-md px-4 py-2 text-white placeholder-gray-200 focus:outline-none focus:ring-2 focus:ring-white"
        />
      </div>

      <div class="flex-1 overflow-y-auto space-y-2">
  <transition-group name="list" tag="div">
    <div v-for="product in products" :key="product.id" class="p-3 rounded-lg bg-white bg-opacity-10 hover:bg-opacity-20 transition cursor-pointer" @click="addProduct(product)">
      <div class="flex justify-between items-center text-white">
        <div class="font-medium">{{ product.name }}</div>
        <div class="text-sm">{{ formatCurrency(product.selling_price) }}</div>
      </div>
    </div>
  </transition-group>
</div>
    </section>

    <!-- Right: Cart & payment -->
    <section class="w-96 bg-white bg-opacity-30 backdrop-filter backdrop-blur-lg rounded-xl shadow-xl p-6 flex flex-col border border-white border-opacity-20">
      <h2 class="text-2xl font-bold text-gray-800 mb-4">Cart</h2>
      <div class="flex-1 overflow-y-auto space-y-2">
        <transition-group name="list" tag="div">
          <div v-for="item in cart.items" :key="item.product.id" class="flex justify-between items-center">
            <div class="flex-1">
              <div class="font-medium">{{ item.product.name }}</div>
              <div class="text-sm text-gray-600">{{ formatCurrency(item.product.selling_price) }}</div>
            </div>
            <div class="flex items-center space-x-2">
              <button @click="decrement(item)" class="px-2 py-1 bg-gray-200 rounded">-</button>
              <span class="w-6 text-center">{{ item.quantity }}</span>
              <button @click="increment(item)" class="px-2 py-1 bg-gray-200 rounded">+</button>
            </div>
          </div>
        </transition-group>
      </div>
      <div class="mt-4 border-t pt-4">
        <div class="flex justify-between text-lg font-semibold">
          <span>Total</span>
          <span>{{ formatCurrency(cart.subtotal) }}</span>
        </div>
        <div class="flex justify-between text-sm text-gray-600">
          <span>VAT ({{ cart.vatRate }}%)</span>
          <span>{{ formatCurrency(cart.vatAmount) }}</span>
        </div>
        <div class="flex justify-between text-xl font-bold text-gray-800 mt-2">
          <span>Grand Total</span>
          <span>{{ formatCurrency(cart.total) }}</span>
        </div>
      </div>
      <div class="mt-6">
        <select v-model="paymentMethod" class="w-full rounded p-2 border">
          <option value="cash">Cash</option>
          <option value="card">Card</option>
          <option value="mpesa">M‑Pesa</option>
        </select>
        <!-- Optional payment reference (e.g., transaction ID) -->
        <input
          v-model="paymentReference"
          placeholder="Payment reference (optional)"
          class="w-full mt-2 rounded p-2 border bg-white bg-opacity-20 backdrop-blur-md text-white placeholder-gray-200 focus:outline-none focus:ring-2 focus:ring-white"
        />
        <button
          @click="checkout"
          class="w-full mt-4 bg-blue-600 hover:bg-blue-700 text-white py-2 rounded transition flex items-center justify-center"
          :disabled="cart.items.length === 0 || isSubmitting"
        ><span v-if="!isSubmitting">Complete Sale</span>
        <svg v-else class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
        </svg>
      </button>
      </div>
    </section>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onBeforeUnmount } from 'vue';
import { router } from '@inertiajs/vue3';
import { useCartStore } from '@/stores/cart';
import { usePage } from '@inertiajs/vue3';

const { props } = usePage();
const products = props.products as Array<any>;
const flashMessage = computed(() => (props.flash as any)?.message || null);

const barcode = ref('');
const paymentMethod = ref('cash');
const paymentReference = ref('');
const cart = useCartStore();
let barcodeListener: (e: KeyboardEvent) => void = () => void;
const isSubmitting = ref(false);

function formatCurrency(value: number) {
  return new Intl.NumberFormat('en-KE', { style: 'currency', currency: 'KES' }).format(value);
}

function addProduct(product: any) {
  cart.addItem(product);
}

function addProductByBarcode() {
  const found = products.find(p => p.barcode === barcode.value);
  if (found) addProduct(found);
  barcode.value = '';
}

function increment(item: any) { cart.increment(item.product.id); }
function decrement(item: any) { cart.decrement(item.product.id); }

async function checkout() {
  if (isSubmitting.value) return;
  isSubmitting.value = true;
  await router.post(route('pos.sale.store'), {
      items: cart.items.map(i => ({ product_id: i.product.id, quantity: i.quantity, price: i.product.selling_price, vat_rate: i.product.vat_rate })),
      subtotal: cart.subtotal,
      total_vat: cart.vatAmount,
      total: cart.total,
      payment_method: paymentMethod.value,
      payment_reference: paymentReference.value || null,
    });
  cart.clear();
  isSubmitting.value = false;
}

onMounted(() => {
  // Focus barcode input on load
  const input = document.querySelector('input') as HTMLInputElement;
  input?.focus();
  // Global barcode scanner listener (captures rapid keystrokes ending with Enter)
  let buffer = '';
  const maxInterval = 100; // ms between characters
  let lastTime = Date.now();
  barcodeListener = (e: KeyboardEvent) => {
    const now = Date.now();
    if (now - lastTime > maxInterval) {
        buffer = '';
    }
    lastTime = now;
    if (e.key === 'Enter') {
        if (buffer) {
            barcode.value = buffer;
            addProductByBarcode();
            buffer = '';
        }
    } else if (e.key.length === 1) {
        buffer += e.key;
    }
};
window.addEventListener('keydown', barcodeListener);
});
onBeforeUnmount(() => {
    if (barcodeListener) {
        window.removeEventListener('keydown', barcodeListener);
    }
});
</script>

<style scoped>
.checkout-screen {
  backdrop-filter: blur(8px);
}

/* List transition for cart items */
.list-enter-active, .list-leave-active {
  transition: all 0.3s ease;
}
.list-enter-from {
  opacity: 0;
  transform: translateY(-10px);
}
.list-leave-to {
  opacity: 0;
  transform: translateY(10px);
}
</style>
