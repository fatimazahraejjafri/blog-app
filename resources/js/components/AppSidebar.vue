<script setup lang="ts">
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import { BookOpen, Folder, LayoutGrid, Users } from 'lucide-vue-next';
import { dashboard } from '@/routes';
import posts from '@/routes/posts';


const props = defineProps<{
  roles: string[]
}>();

const mainNavItems = computed(() => {
  if (props.roles.includes('admin')) {
    return [
      { title: 'Dashboard', href: dashboard(), icon: LayoutGrid },
      { title: 'Manage Posts', href: '/admin/posts', icon: Folder },
    ];
  }

  // Default for normal users
  return [
    { title: 'Home', href: dashboard(), icon: LayoutGrid },
    { title: 'Posts', href: posts.index(), icon: Folder },
  ];
});
</script>
<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="dashboard()">
                            <AppLogo />
                        </Link>
                        
                        
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
