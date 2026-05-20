<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
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
        <DialogContent>
          <Form :key="formKey" v-bind="store.form()" class="space-y-4" @success="open = false">
            <DialogHeader>
              <DialogTitle>Create Category</DialogTitle>
              <DialogDescription>Enter a name for the new category.</DialogDescription>
            </DialogHeader>
            <div class="grid gap-2">
              <label for="name" class="font-medium">Name</label>
              <Input id="name" name="name" placeholder="Beverages" required />
              <InputError :message="errors.name" />
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
    </div>
    <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
      <Card v-for="category in categories" :key="category.id" class="hover:shadow-lg transition-shadow">
        <CardHeader>
          <CardTitle>{{ category.name }}</CardTitle>
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
