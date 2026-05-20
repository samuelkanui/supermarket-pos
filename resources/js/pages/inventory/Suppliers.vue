<script setup lang="ts">
import { ref } from 'vue';
import { Head } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Dialog, DialogTrigger, DialogContent, DialogClose } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Plus, Pencil, Trash2 } from 'lucide-vue-next';
import { router } from '@inertiajs/vue3';
import { Table, TableHeader, TableBody, TableRow, TableCell } from '@/components/ui/table';
import { Card, CardHeader, CardTitle, CardContent } from '@/components/ui/card';
import { store } from '@/routes/suppliers';
import type { Supplier } from '@/types';

type Props = {
  suppliers: Supplier[];
};

defineProps<Props>();

// Dialog state management
const open = ref(false);
const editOpen = ref(false);
const deleteOpen = ref(false);
const formKey = ref(0);
const editFormKey = ref(0);
const deleteFormKey = ref(0);
const selectedSupplier = ref(null);
function handleOpenChange(value) {
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
function startEdit(supplier) {
  selectedSupplier.value = supplier;
  editOpen.value = true;
}
function startDelete(supplier) {
  selectedSupplier.value = supplier;
  deleteOpen.value = true;
}

function deleteSupplier() {
  if (!selectedSupplier.value) return;
  if (confirm('Are you sure you want to delete this supplier?')) {
    // Assuming a global `route` helper is available for generating URLs
    router.delete(route('suppliers.destroy', [selectedSupplier.value.id]));
    // Close the dialog after deletion
    deleteOpen.value = false;
    // Optionally, you may want to refresh the list or emit an event
  }
}
</script>

<template>
  <Head title="Suppliers" />

  <Card class="w-full">
    <CardHeader class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
      <CardTitle>Suppliers</CardTitle>
      <Dialog :open="open" @update:open="handleOpenChange">
        <DialogTrigger as-child>
          <Button data-test="new-supplier-button"><Plus class="mr-2" /> New Supplier</Button>
        </DialogTrigger>
        <DialogContent>
          <Form :key="formKey" v-bind="store.form()" class="space-y-4" v-slot="{ errors, processing }" @success="open = false">
            <DialogHeader>
              <DialogTitle>Create Supplier</DialogTitle>
              <DialogDescription>Enter supplier details.</DialogDescription>
            </DialogHeader>
            <div class="grid gap-2">
              <Label for="name">Name</Label>
              <Input id="name" name="name" placeholder="Acme Corp" required />
              <InputError :message="errors.name" />
            </div>
            <div class="grid gap-2">
              <Label for="email">Email</Label>
              <Input id="email" name="email" type="email" placeholder="contact@acme.com" />
              <InputError :message="errors.email" />
            </div>
            <div class="grid gap-2">
              <Label for="phone">Phone</Label>
              <Input id="phone" name="phone" placeholder="+123456789" />
              <InputError :message="errors.phone" />
            </div>
            <div class="grid gap-2">
              <Label for="address">Address</Label>
              <Input id="address" name="address" placeholder="123 Main St" />
              <InputError :message="errors.address" />
            </div>
            <DialogFooter class="gap-2">
              <DialogClose as-child>
                <Button variant="secondary">Cancel</Button>
              </DialogClose>
              <Button type="submit" :disabled="processing">Create</Button>
            </DialogFooter>
          </Form>
        </DialogContent>
      </Dialog>

      <!-- Edit Supplier Dialog -->
      <Dialog :open="editOpen" @update:open="handleEditOpenChange">
        <DialogContent>
          <Form :key="editFormKey" v-bind="store.form(selectedSupplier?.id)" class="space-y-4" v-slot="{ errors, processing }" @success="editOpen = false">
            <DialogHeader>
              <DialogTitle>Edit Supplier</DialogTitle>
              <DialogDescription>Update supplier information.</DialogDescription>
            </DialogHeader>
            <div class="grid gap-2">
              <Label for="edit-name">Name</Label>
              <Input id="edit-name" name="name" :value="selectedSupplier?.name" required />
              <InputError :message="errors.name" />
            </div>
            <div class="grid gap-2">
              <Label for="edit-email">Email</Label>
              <Input id="edit-email" name="email" type="email" :value="selectedSupplier?.email" />
              <InputError :message="errors.email" />
            </div>
            <div class="grid gap-2">
              <Label for="edit-phone">Phone</Label>
              <Input id="edit-phone" name="phone" :value="selectedSupplier?.phone" />
              <InputError :message="errors.phone" />
            </div>
            <div class="grid gap-2">
              <Label for="edit-address">Address</Label>
              <Input id="edit-address" name="address" :value="selectedSupplier?.address" />
              <InputError :message="errors.address" />
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

      <!-- Delete Supplier Confirmation -->
      <Dialog :open="deleteOpen" @update:open="handleDeleteOpenChange">
        <DialogContent>
          <DialogHeader>
            <DialogTitle>Delete Supplier</DialogTitle>
            <DialogDescription>Are you sure you want to delete "{{ selectedSupplier?.name }}"?</DialogDescription>
          </DialogHeader>
          <DialogFooter class="gap-2">
            <DialogClose as-child>
              <Button variant="secondary">Cancel</Button>
            </DialogClose>
            <Button variant="destructive" @click="deleteSupplier" :disabled="false">Delete</Button>
          </DialogFooter>
        </DialogContent>
      </Dialog>
    </CardHeader>

    <CardContent>
      <Table>
        <TableHeader>
          <TableRow>
            <TableCell class="font-medium">Name</TableCell>
            <TableCell class="font-medium">Email</TableCell>
            <TableCell class="font-medium">Phone</TableCell>
            <TableCell class="w-24 text-center">Actions</TableCell>
          </TableRow>
        </TableHeader>
        <TableBody>
          <TableRow v-for="supplier in suppliers" :key="supplier.id">
            <TableCell>{{ supplier.name }}</TableCell>
            <TableCell>{{ supplier.email }}</TableCell>
            <TableCell>{{ supplier.phone }}</TableCell>
            <TableCell class="flex justify-center gap-2">
              <Button variant="ghost" size="sm" data-test="edit-supplier" @click="startEdit(supplier)"><Pencil class="h-4 w-4" /></Button>
              <Button variant="ghost" size="sm" data-test="delete-supplier" @click="startDelete(supplier)"><Trash2 class="h-4 w-4 text-red-500" /></Button>
            </TableCell>
          </TableRow>
          <TableRow v-if="suppliers.length === 0">
            <TableCell colspan="4" class="text-center py-8 text-muted-foreground">No suppliers found.</TableCell>
          </TableRow>
        </TableBody>
      </Table>
    </CardContent>
  </Card>
</template>
