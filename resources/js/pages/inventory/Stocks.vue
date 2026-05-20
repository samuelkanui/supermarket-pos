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
import { store } from '@/routes/stocks';
import type { Stock, Branch } from '@/types';

type Props = {
  stocks: Stock[];
  branches: Branch[];
};

defineProps<Props>();

const open = ref(false);
const formKey = ref(0);
const editOpen = ref(false);
const deleteOpen = ref(false);
const editFormKey = ref(0);
const deleteFormKey = ref(0);
const selectedStock = ref(null);
function handleOpenChange(value: boolean) {
  open.value = value;
  if (!value) formKey.value++;
function handleOpenChange(value: boolean) {
  open.value = value;
  if (!value) formKey.value++;
}
function handleEditOpenChange(value) {
  editOpen.value = value;
  if (!value) editFormKey.value++;
}
function handleDeleteOpenChange(value) {
  deleteOpen.value = value;
  if (!value) deleteFormKey.value++;
}
function startEdit(stock) {
  selectedStock.value = stock;
  editOpen.value = true;
}
function startDelete(stock) {
  selectedStock.value = stock;
  deleteOpen.value = true;
}
</script>

<template>
  <Head title="Stocks" />

  <Card class="w-full">
    <CardHeader class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
      <CardTitle>Branch Stocks</CardTitle>
      <Dialog :open="open" @update:open="handleOpenChange">
        <DialogTrigger as-child>
          <Button data-test="new-stock-button"><Plus class="mr-2" /> Adjust Stock</Button>
        </DialogTrigger>
        <DialogContent>
          <Form :key="formKey" v-bind="store.form()" class="space-y-4" v-slot="{ errors, processing }" @success="open = false">
            <div class="grid gap-2">
              <Label for="branch_id">Branch</Label>
              <Select name="branch_id" :default-value="''">
                <SelectTrigger>
                  <SelectValue placeholder="Select branch" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem v-for="b in branches" :key="b.id" :value="b.id.toString()">
                    {{ b.name }}
                  </SelectItem>
                </SelectContent>
              </Select>
              <InputError :message="errors.branch_id" />
            </div>
            <div class="grid gap-2">
              <Label for="product_id">Product</Label>
              <Select name="product_id" :default-value="''">
                <SelectTrigger>
                  <SelectValue placeholder="Select product" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem v-for="p in store.products" :key="p.id" :value="p.id.toString()">
                    {{ p.name }}
                  </SelectItem>
                </SelectContent>
              </Select>
              <InputError :message="errors.product_id" />
            </div>
            <div class="grid gap-2">
              <Label for="adjustment">Adjustment</Label>
              <Input id="adjustment" name="adjustment" type="number" placeholder="e.g., +5 or -3" required />
              <InputError :message="errors.adjustment" />
            </div>
            <div class="flex justify-end gap-2">
              <DialogClose as-child>
                <Button variant="secondary">Cancel</Button>
              </DialogClose>
              <Button type="submit" :disabled="processing">Save</Button>
            </div>
          </Form>
        </DialogContent>
      </Dialog>
<!-- Edit Stock Dialog -->
<Dialog :open="editOpen" @update:open="handleEditOpenChange">
  <DialogContent>
    <Form :key="editFormKey" v-bind="store.form(selectedStock?.id)" class="space-y-4" v-slot="{ errors, processing }" @success="editOpen = false">
      <DialogHeader>
        <DialogTitle>Edit Stock</DialogTitle>
        <DialogDescription>Adjust quantity for the selected stock.</DialogDescription>
      </DialogHeader>
      <div class="grid gap-2">
        <Label for="edit-quantity">Quantity</Label>
        <Input id="edit-quantity" name="quantity" type="number" :value="selectedStock?.quantity" required />
        <InputError :message="errors.quantity" />
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

<!-- Delete Stock Confirmation -->
<Dialog :open="deleteOpen" @update:open="handleDeleteOpenChange">
  <DialogContent>
    <DialogHeader>
      <DialogTitle>Delete Stock</DialogTitle>
      <DialogDescription>Are you sure you want to delete stock for "{{ selectedStock?.product?.name }}" at "{{ selectedStock?.branch?.name }}"?</DialogDescription>
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
            <TableCell class="font-medium">Branch</TableCell>
            <TableCell class="font-medium">Product</TableCell>
            <TableCell class="font-medium">Quantity</TableCell>
            <TableCell class="w-24 text-center">Actions</TableCell>
          </TableRow>
        </TableHeader>
        <TableBody>
          <TableRow v-for="stock in stocks" :key="stock.id">
            <TableCell>{{ stock.branch?.name ?? '-' }}</TableCell>
            <TableCell>{{ stock.product?.name ?? '-' }}</TableCell>
            <TableCell>{{ stock.quantity }}</TableCell>
            <TableCell class="flex justify-center gap-2">
              <Button variant="ghost" size="sm" data-test="edit-stock" @click="startEdit(stock)"><Pencil class="h-4 w-4" /></Button>
              <Button variant="ghost" size="sm" data-test="delete-stock" @click="startDelete(stock)"><Trash2 class="h-4 w-4 text-red-500" /></Button>
            </TableCell>
          </TableRow>
          <TableRow v-if="stocks.length === 0">
            <TableCell colspan="4" class="text-center py-8 text-muted-foreground">No stock records.</TableCell>
          </TableRow>
        </TableBody>
      </Table>
    </CardContent>
  </Card>
</template>
