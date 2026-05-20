<script setup lang="ts">
import { ref } from 'vue';
import { DialogClose } from '@/components/ui/dialog';
const open = ref(false);
const editOpen = ref(false);
const deleteOpen = ref(false);
const formKey = ref(0);
const editFormKey = ref(0);
const deleteFormKey = ref(0);
const selectedCategory = ref(null);
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
function startEdit(category) {
  selectedCategory.value = category;
  editOpen.value = true;
}
function startDelete(category) {
  selectedCategory.value = category;
  deleteOpen.value = true;
}
import { Plus } from 'lucide-vue-next';
import { Card, CardHeader, CardTitle, CardContent } from '@/components/ui/card';
import { Dialog, DialogTrigger, DialogContent, DialogHeader, DialogTitle, DialogDescription, DialogFooter, DialogClose } from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import InputError from '@/components/InputError.vue';
import { store } from '@/routes/categories';
import type { Category } from '@/types';

type Props = {
  categories: Category[];
};

defineProps<Props>();

const open = ref(false);
const formKey = ref(0);
function handleOpenChange(value: boolean) {
  open.value = value;
  if (!value) formKey.value++;
}
</script>

<template>
  <Head title="Categories" />
  <div class="flex flex-col space-y-6">
    <div class="flex items-center justify-between">
      <h1 class="text-2xl font-bold">Categories</h1>
      <Dialog :open="open" @update:open="handleOpenChange">
        <DialogTrigger as-child>
          <Button variant="primary">
            <Plus class="h-4 w-4 mr-2" /> New Category
          </Button>
        </DialogTrigger>
        <!-- Edit Category Dialog -->
        <Dialog :open="editOpen" @update:open="handleEditOpenChange">
          <DialogContent>
            <Form :key="editFormKey" v-bind="store.form(selectedCategory?.id)" class="space-y-4" @success="editOpen = false">
              <DialogHeader>
                <DialogTitle>Edit Category</DialogTitle>
                <DialogDescription>Update the category name.</DialogDescription>
              </DialogHeader>
              <div class="grid gap-2">
                <label for="edit-name" class="font-medium">Name</label>
                <Input id="edit-name" name="name" :value="selectedCategory?.name" required />
              </div>
              <DialogFooter class="gap-2">
                <DialogClose as-child>
                  <Button variant="secondary">Cancel</Button>
                </DialogClose>
                <Button type="submit">Update</Button>
              </DialogFooter>
            </Form>
          </DialogContent>
        </Dialog>
        <!-- Delete Confirmation Dialog -->
        <Dialog :open="deleteOpen" @update:open="handleDeleteOpenChange">
          <DialogContent>
            <DialogHeader>
              <DialogTitle>Delete Category</DialogTitle>
              <DialogDescription>Are you sure you want to delete "{{ selectedCategory?.name }}"?</DialogDescription>
            </DialogHeader>
            <DialogFooter class="gap-2">
              <DialogClose as-child>
                <Button variant="secondary">Cancel</Button>
              </DialogClose>
              <Button variant="destructive">Delete</Button>
            </DialogFooter>
          </DialogContent>
        </Dialog>
        <DialogContent>
          <Form :key="formKey" v-bind="store.form()" class="space-y-4" @success="open = false">
            <DialogHeader>
              <DialogTitle>Create Category</DialogTitle>
              <DialogDescription>Enter a name for the new category.</DialogDescription>
            </DialogHeader>
            <div class="grid gap-2">
              <label for="name" class="font-medium">Name</label>
              <Input id="name" name="name" placeholder="Beverages" required />
            </div>
            <DialogFooter class="gap-2">
              <DialogClose as-child>
                <Button variant="secondary">Cancel</Button>
              </DialogClose>
              <Button type="submit">Create</Button>
            </DialogFooter>
          </Form>
        </DialogContent>
      </Dialog>
    </div>
    <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
      <Card v-for="category in categories" :key="category.id" class="hover:shadow-lg transition-shadow">
        <CardHeader class="flex justify-between items-center">
          <CardTitle>{{ category.name }}</CardTitle>
          <div class="flex gap-2">
            <Button size="sm" variant="ghost" @click="startEdit(category)"><Pencil class="h-4 w-4" /></Button>
            <Button size="sm" variant="ghost" @click="startDelete(category)"><Trash2 class="h-4 w-4 text-red-500" /></Button>
          </div>
        </CardHeader>
        <CardContent>
          <p class="text-sm text-muted-foreground">{{ category.description ?? 'No description' }}</p>
        </CardContent>
      </Card>
    </div>
  </div>
</template>

<style scoped>
/* Premium gradient background for the page */
div.flex.flex-col.space-y-6 {
  background: linear-gradient(135deg, hsl(210, 30%, 95%), hsl(210, 15%, 98%));
  padding: 2rem;
  border-radius: 1rem;
}
</style>
