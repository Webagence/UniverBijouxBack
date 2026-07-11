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

        // Reset existing roles/permissions to apply new definitions
        \DB::statement('SET FOREIGN_KEY_CHECKS=0');
        \DB::table('model_has_roles')->truncate();
        \DB::table('model_has_permissions')->truncate();
        \DB::table('role_has_permissions')->truncate();
        \DB::table('roles')->truncate();
        \DB::table('permissions')->truncate();
        \DB::statement('SET FOREIGN_KEY_CHECKS=1');

        // ─── Toutes les permissions ─────────────────────────────────────
        $perms = [

            // Produits / Catalogue
            'view_product', 'create_product', 'edit_product', 'delete_product', 'publish_product',
            'view_universe', 'create_universe', 'edit_universe', 'delete_universe',
            'view_variant', 'create_variant', 'edit_variant', 'delete_variant',
            'view_brand', 'create_brand', 'edit_brand', 'delete_brand',
            'view_attribute', 'create_attribute', 'edit_attribute', 'delete_attribute',
            'add_image', 'edit_image', 'delete_image',
            'view_stock', 'modify_stock', 'import_stock',
            'view_import_products',
            'view_discount', 'create_discount', 'edit_discount', 'delete_discount',
            'view_site',

            // Commandes
            'view_order', 'create_order', 'edit_order', 'delete_order', 'edit_order_status',
            'prepare_order', 'generate_invoice', 'generate_delivery_note', 'print_order',
            'assign_carrier', 'generate_shipment', 'add_tracking_number', 'edit_shipping_status',
            'add_order_comment',

            // Factures
            'view_invoice', 'create_invoice', 'edit_invoice', 'delete_invoice',

            // Clients
            'view_user', 'create_user', 'edit_user', 'delete_user', 'view_user_orders',
            'disable_user_account',

            // Tickets / SAV
            'view_ticket', 'create_ticket', 'edit_ticket', 'delete_ticket',
            'manage_return', 'manage_refund', 'reply_ticket',

            // Contenu CMS
            'view_manage_content', 'edit_manage_content',
            'view_manage_portal_content', 'edit_manage_portal_content',
            'view_manage_settings', 'edit_manage_settings',
            'view_manage_maintenance', 'edit_manage_maintenance',
            'view_manage_shippingbo',
            'view_manage_stripe',
            'view_manage_translations',
            'view_stats',
            'view_blog', 'create_blog', 'edit_blog', 'publish_blog', 'unpublish_blog', 'delete_blog',
            'view_media', 'create_media', 'edit_media', 'delete_media',
            'edit_seo_tags', 'edit_seo_metadata', 'edit_menu',

            // Témoignages / FAQ
            'view_testimonial', 'create_testimonial', 'edit_testimonial', 'delete_testimonial',
            'view_faq_item', 'create_faq_item', 'edit_faq_item', 'delete_faq_item',

            // Transporteurs
            'view_shipping_carrier', 'create_shipping_carrier', 'edit_shipping_carrier', 'delete_shipping_carrier',

            // Maintenance
            'view_maintenance_subscriber', 'delete_maintenance_subscriber',
        ];

        foreach ($perms as $p) {
            Permission::create(['name' => $p, 'guard_name' => 'web']);
        }

        // ─── Rôles ──────────────────────────────────────────────────────

        $admin = Role::create(['name' => 'admin', 'guard_name' => 'web']);
        $admin->givePermissionTo(Permission::all());

        // ── 1. Catalog Manager ──────────────────────────────────────────
        $cat = Role::create(['name' => 'catalog_manager', 'guard_name' => 'web']);
        $cat->givePermissionTo([
            'view_product', 'create_product', 'edit_product', 'delete_product', 'publish_product',
            'view_universe', 'create_universe', 'edit_universe', 'delete_universe',
            'view_variant', 'create_variant', 'edit_variant', 'delete_variant',
            'view_brand', 'create_brand', 'edit_brand', 'delete_brand',
            'view_attribute', 'create_attribute', 'edit_attribute', 'delete_attribute',
            'add_image', 'edit_image', 'delete_image',
            'view_stock', 'modify_stock', 'import_stock',
            'view_import_products',
            'view_discount', 'create_discount', 'edit_discount', 'delete_discount',
            'view_site',
            'view_stats',
        ]);

        // ── 2. Order Manager ────────────────────────────────────────────
        $ord = Role::create(['name' => 'order_manager', 'guard_name' => 'web']);
        $ord->givePermissionTo([
            'view_order', 'edit_order_status', 'prepare_order',
            'generate_invoice', 'generate_delivery_note', 'print_order',
            'assign_carrier', 'generate_shipment', 'add_tracking_number', 'edit_shipping_status',
            'view_user', 'view_user_orders',
            'view_shipping_carrier',
            'view_manage_shippingbo',
        ]);

        // ── 3. Customer Support ─────────────────────────────────────────
        $sup = Role::create(['name' => 'customer_support', 'guard_name' => 'web']);
        $sup->givePermissionTo([
            'view_user', 'edit_user', 'disable_user_account', 'view_user_orders',
            'view_order', 'add_order_comment', 'edit_order_status',
            'manage_return', 'manage_refund',
            'view_ticket', 'create_ticket', 'edit_ticket', 'delete_ticket', 'reply_ticket',
            'view_faq_item', 'create_faq_item', 'edit_faq_item', 'delete_faq_item',
        ]);

        // ── 4. Content Manager ──────────────────────────────────────────
        $cnt = Role::create(['name' => 'content_manager', 'guard_name' => 'web']);
        $cnt->givePermissionTo([
            'view_manage_content', 'edit_manage_content',
            'view_manage_portal_content', 'edit_manage_portal_content',
            'view_manage_settings', 'edit_manage_settings',
            'view_blog', 'create_blog', 'edit_blog', 'publish_blog', 'unpublish_blog', 'delete_blog',
            'view_media', 'create_media', 'edit_media', 'delete_media',
            'edit_seo_tags', 'edit_seo_metadata', 'edit_menu',
            'view_testimonial', 'create_testimonial', 'edit_testimonial', 'delete_testimonial',
            'view_faq_item', 'create_faq_item', 'edit_faq_item', 'delete_faq_item',
            'view_manage_translations',
            'view_manage_maintenance', 'edit_manage_maintenance',
            'view_maintenance_subscriber', 'delete_maintenance_subscriber',
            'view_stats',
        ]);

        // ── Rôle API pour les clients B2B ──────────────────────────────
        Role::create(['name' => 'pro', 'guard_name' => 'web']);

        // ── Assigner les rôles aux utilisateurs existants ───────────────
        User::where('email', 'admin@univerbijoux.com')->get()->each->assignRole('admin');
        User::where('email', 'contact@boutique-ecrin.fr')->get()->each->assignRole('pro');
        User::where('email', 'contact@ondine-lyon.fr')->get()->each->assignRole('pro');
    }
}
