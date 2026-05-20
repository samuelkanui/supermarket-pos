<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Plus, Pencil, Trash2 } from 'lucide-vue-next';
import { Dialog, DialogTrigger, DialogContent, DialogClose } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectTrigger, SelectValue, SelectContent, SelectItem } from '@/components/ui/select';
import { Table, TableHeader, TableBody, TableRow, TableCell } from '@/components/ui/table';
import { Card, CardHeader, CardTitle, CardContent } from '@/components/ui/card';
import { store } from '@/routes/products';
import type { Product, Category } from '@/types';

type Props = {
  products: Product[];
  categories: Category[];
};

defineProps<Props>();

const open = ref(false);
const editOpen = ref(false);
const deleteOpen = ref(false);
const formKey = ref(0);
const editFormKey = ref(0);
const deleteFormKey = ref(0);
const selectedProduct = ref(null);
function handleOpenChange(value: boolean) {
  open.value = value;
  if (!value) formKey.value++;
}
function handleEditOpenChange(value: boolean) {
  editOpen.value = value;
  if (!value) editFormKey.value++;
}
function handleDeleteOpenChange(value: boolean) {
  deleteOpen.value = value;
  if (!value) deleteFormKey.value++;
}
function startEdit(product) {
  selectedProduct.value = product;
  editOpen.value = true;
}
function startDelete(product) {
  selectedProduct.value = product;
  deleteOpen.value = true;
}
</script>

<template>
  <Head title="Products" />

  <Card class="w-full">
    <CardHeader class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
      <CardTitle>Products</CardTitle>
      <Dialog :open="open" @update:open="handleOpenChange">
        <DialogTrigger as-child>
          <Button data-test="new-product-button"><Plus class="mr-2" /> New Product</Button>
        </DialogTrigger>
        <DialogContent>
          <Form :key="formKey" v-bind="store.form()" class="space-y-4" v-slot="{ errors, processing }" @success="open = false">
            <div class="grid gap-2">
              <Label for="name">Name</Label>
              <Input id="name" name="name" placeholder="Apple" required />
              <InputError :message="errors.name" />
            </div>
            <div class="grid gap-2">
              <Label for="sku">SKU</Label>
              <Input id="sku" name="sku" placeholder="APP-001" />
              <InputError :message="errors.sku" />
            </div>
            <div class="grid gap-2">
              <Label for="barcode">Barcode</Label>
              <Input id="barcode" name="barcode" placeholder="1234567890123" />
              <InputError :message="errors.barcode" />
            </div>
            <div class="grid gap-2">
              <Label for="cost">Cost</Label>
              <Input id="cost" name="cost" type="number" step="0.01" placeholder="0.00" />
              <InputError :message="errors.cost" />
            </div>
            <div class="grid gap-2">
              <Label for="price">Selling Price</Label>
              <Input id="price" name="price" type="number" step="0.01" placeholder="0.00" />
              <InputError :message="errors.price" />
            </div>
            <div class="grid gap-2">
              <Label for="category_id">Category</Label>
              <Select name="category_id" :default-value="''">
                <SelectTrigger>
                  <SelectValue placeholder="Select category" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem v-for="cat in categories" :key="cat.id" :value="cat.id.toString()">
                    {{ cat.name }}
                  </SelectItem>
                </SelectContent>
              </Select>
              <InputError :message="errors.category_id" />
            </div>
            <div class="flex justify-end gap-2">
              <DialogClose as-child>
                <Button variant="secondary">Cancel</Button>
              </DialogClose>
              <Button type="submit" :disabled="processing">Create</Button>
            </div>
          </Form>
        </DialogContent>
      </Dialog>

      <!-- Edit Product Dialog -->
      <Dialog :open="editOpen" @update:open="handleEditOpenChange">
        <DialogContent>
          <Form :key="editFormKey" v-bind="store.form(selectedProduct?.id)" class="space-y-4" v-slot="{ errors, processing }" @success="editOpen = false">
            <DialogHeader>
              <DialogTitle>Edit Product</DialogTitle>
              <DialogDescription>Update product details.</DialogDescription>
            </DialogHeader>
            <div class="grid gap-2">
              <Label for="edit-name">Name</Label>
              <Input id="edit-name" name="name" :value="selectedProduct?.name" required />
              <InputError :message="errors.name" />
            </div>
            <div class="grid gap-2">
              <Label for="edit-sku">SKU</Label>
              <Input id="edit-sku" name="sku" :value="selectedProduct?.sku" />
            </div>
            <div class="grid gap-2">
              <Label for="edit-price">Price</Label>
              <Input id="edit-price" name="price" type="number" step="0.01" :value="selectedProduct?.price" />
            </div>
            <DialogFooter class="gap-2">
              <DialogClose as-child>
                <Button variant="secondary">Cancel</Button>
              </DialogClose>
              <Button type="submit" :disabled="processing">Update</Button>
            </DialogFooter>
          </Form>
        </DialogContent>
      </Dialog>

      <!-- Delete Product Confirmation -->
      <Dialog :open="deleteOpen" @update:open="handleDeleteOpenChange">
        <DialogContent>
          <DialogHeader>
            <DialogTitle>Delete Product</DialogTitle>
            <DialogDescription>Are you sure you want to delete "{{ selectedProduct?.name }}"?</DialogDescription>
          </DialogHeader>
          <DialogFooter class="gap-2">
            <DialogClose as-child>
              <Button variant="secondary">Cancel</Button>
            </DialogClose>
            <Button variant="destructive" @click="/* call delete API */">Delete</Button>
          </DialogFooter>
        </DialogContent>
      </Dialog>

    </CardHeader>

    <CardContent>
      <Table>
        <TableHeader>
          <TableRow>
            <TableCell class="font-medium">Name</TableCell>
            <TableCell class="font-medium">SKU</TableCell>
            <TableCell class="font-medium">Price</TableCell>
            <TableCell class="font-medium">Category</TableCell>
            <TableCell class="w-24 text-center">Actions</TableCell>
          </TableRow>
        </TableHeader>
        <TableBody>
          <TableRow v-for="product in products" :key="product.id">
            <TableCell>{{ product.name }}</TableCell>
            <TableCell>{{ product.sku ?? '-' }}</TableCell>
            <TableCell>{{ product.price?.toFixed(2) ?? '-' }}</TableCell>
            <TableCell>{{ product.category?.name ?? '-' }}</TableCell>
            <TableCell class="flex justify-center gap-2">
              <Button variant="ghost" size="sm" data-test="edit-product" @click="startEdit(product)"><Pencil class="h-4 w-4" /></Button>
                <Button variant="ghost" size="sm" data-test="delete-product" @click="startDelete(product)"><Trash2 class="h-4 w-4 text-red-500" /></Button>
            </TableCell>
          </TableRow>
          <TableRow v-if="products.length === 0">
            <TableCell colspan="5" class="text-center py-8 text-muted-foreground">No products found.</TableCell>
          </TableRow>
        </TableBody>
      </Table>
    </CardContent>
  </Card>
</template>
