<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Dialog, DialogTrigger, DialogContent, DialogClose } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Table, TableHeader, TableBody, TableRow, TableCell } from '@/components/ui/table';
import { Card, CardHeader, CardTitle, CardContent } from '@/components/ui/card';
import { store } from '@/routes/suppliers';
import type { Supplier } from '@/types';

type Props = {
  suppliers: Supplier[];
};

defineProps<Props>();
</script>

<template>
  <Head title="Suppliers" />

  <Card class="w-full">
    <CardHeader class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
      <CardTitle>Suppliers</CardTitle>
      <Dialog>
        <DialogTrigger as-child>
          <Button data-test="new-supplier-button"><Plus class="mr-2" /> New Supplier</Button>
        </DialogTrigger>
        <DialogContent>
          <Form :key="formKey" v-bind="store.form()" class="space-y-4" v-slot="{ errors, processing }" @success="/* refresh list */">
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
            <div class="flex justify-end gap-2">
              <DialogClose as-child>
                <Button variant="secondary">Cancel</Button>
              </DialogClose>
              <Button type="submit" :disabled="processing">Create</Button>
            </div>
          </Form>
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
              <Button variant="ghost" size="sm" data-test="edit-supplier" @click="/* edit */"><Pencil class="h-4 w-4" /></Button>
              <Button variant="ghost" size="sm" data-test="delete-supplier" @click="/* delete */"><Trash2 class="h-4 w-4 text-red-500" /></Button>
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
