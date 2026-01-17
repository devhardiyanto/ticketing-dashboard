<script setup lang="ts">
import { Button } from '@/components/ui/button';
import type { Role } from '@/types/dashboard';
import { Pencil } from 'lucide-vue-next';

defineProps<{
    role: Role;
    onEdit: (role: Role) => void;
}>();
</script>

<template>
    <div class="flex items-center gap-2">
        <Button variant="ghost" size="icon" @click="onEdit(role)">
            <Pencil class="h-4 w-4" />
            <span class="sr-only">Edit Legacy Role</span>
        </Button>
    </div>
</template>
