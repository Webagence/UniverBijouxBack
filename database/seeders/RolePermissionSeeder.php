<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Define all permissions for Filament resources/pages
        $permissions = [
            // Resources
            'view_product', 'create_product', 'edit_product', 'delete_product',
            'view_universe', 'create_universe', 'edit_universe', 'delete_universe',
            'view_order', 'create_order', 'edit_order', 'delete_order',
            'view_invoice', 'create_invoice', 'edit_invoice', 'delete_invoice',
            'view_discount', 'create_discount', 'edit_discount', 'delete_discount',
            'view_ticket', 'create_ticket', 'edit_ticket', 'delete_ticket',
            'view_user', 'create_user', 'edit_user', 'delete_user',
            'view_testimonial', 'create_testimonial', 'edit_testimonial', 'delete_testimonial',
            'view_faq_item', 'create_faq_item', 'edit_faq_item', 'delete_faq_item',
            'view_shipping_carrier', 'create_shipping_carrier', 'edit_shipping_carrier', 'delete_shipping_carrier',
            'view_site', 'create_site', 'edit_site', 'delete_site',
            'view_maintenance_subscriber', 'delete_maintenance_subscriber',

            // Pages
            'view_import_products',
            'view_manage_content', 'edit_manage_content',
            'view_manage_portal_content', 'edit_manage_portal_content',
            'view_manage_settings', 'edit_manage_settings',
            'view_manage_maintenance', 'edit_manage_maintenance',
            'view_manage_shippingbo',
            'view_manage_stripe',
            'view_manage_translations',
            'view_stats',
        ];

        foreach ($permissions as $perm) {
            Permission::create(['name' => $perm, 'guard_name' => 'web']);
        }

        // — Role definitions —
        $superAdmin = Role::create(['name' => 'admin', 'guard_name' => 'web']);
        $superAdmin->givePermissionTo(Permission::all());

        // 1. Catalog Manager
        $catalogManager = Role::create(['name' => 'catalog_manager', 'guard_name' => 'web']);
        $catalogManager->givePermissionTo([
            'view_product', 'create_product', 'edit_product', 'delete_product',
            'view_universe', 'create_universe', 'edit_universe', 'delete_universe',
            'view_import_products',
            'view_discount', 'create_discount', 'edit_discount', 'delete_discount',
            'view_site',
            'view_stats',
        ]);

        // 2. Order Manager
        $orderManager = Role::create(['name' => 'order_manager', 'guard_name' => 'web']);
        $orderManager->givePermissionTo([
            'view_order', 'edit_order',
            'view_invoice',
            'view_user',
            'view_shipping_carrier',
            'view_manage_shippingbo',
        ]);

        // 3. Customer Support
        $support = Role::create(['name' => 'customer_support', 'guard_name' => 'web']);
        $support->givePermissionTo([
            'view_ticket', 'create_ticket', 'edit_ticket', 'delete_ticket',
            'view_user',
            'view_faq_item', 'create_faq_item', 'edit_faq_item', 'delete_faq_item',
            'view_order',
            'view_testimonial', 'edit_testimonial', 'delete_testimonial',
        ]);

        // 4. Content Manager
        $contentManager = Role::create(['name' => 'content_manager', 'guard_name' => 'web']);
        $contentManager->givePermissionTo([
            'view_manage_content', 'edit_manage_content',
            'view_manage_portal_content', 'edit_manage_portal_content',
            'view_manage_settings', 'edit_manage_settings',
            'view_testimonial', 'create_testimonial', 'edit_testimonial', 'delete_testimonial',
            'view_faq_item', 'create_faq_item', 'edit_faq_item', 'delete_faq_item',
            'view_manage_translations',
            'view_manage_maintenance', 'edit_manage_maintenance',
            'view_maintenance_subscriber', 'delete_maintenance_subscriber',
            'view_stats',
        ]);

        // Assign admin role to existing admin users
        $admins = User::where('email', 'admin@univerbijoux.com')->get();
        foreach ($admins as $admin) {
            $admin->assignRole('admin');
        }
    }
}
