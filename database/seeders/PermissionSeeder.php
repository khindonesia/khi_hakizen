<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [

            // PERMISSION MANAGEMENT
            'permissions.view',
            'permissions.create',
            'permissions.update',
            'permissions.delete',

            // KNOWLEDGE BASE
            'knowledge.view',
            'knowledge.create',
            'knowledge.update',
            'knowledge.delete',

            // PANEL ACCESS
            'panel.access',

            // PRODUCT
            'product.view',
            'product.create',
            'product.update',
            'product.delete',

            // ORDER DASHBOARD (VIEW ONLY)
            'order-dashboard.view',

            // SELLER ADDRESS
            'seller-address.view',
            'seller-address.create',
            'seller-address.update',
            'seller-address.delete',

            // PAGE
            'page.view',
            'page.create',
            'page.update',
            'page.delete',

            // ROLE
            'role.view',
            'role.create',
            'role.update',
            'role.delete',

            // SETTING
            'setting.view',
            'setting.create',
            'setting.update',
            'setting.delete',

            // PRODUCT CATEGORY
            'product-category.view',
            'product-category.create',
            'product-category.update',
            'product-category.delete',

            // PRODUCT ATTRIBUTE
            'product-attribute.view',
            'product-attribute.create',
            'product-attribute.update',
            'product-attribute.delete',

            // POST
            'post.view',
            'post.create',
            'post.update',
            'post.delete',

            // HOME PAGE CONTENT
            'home-page-content.view',
            'home-page-content.create',
            'home-page-content.update',
            'home-page-content.delete',

            // Organization
            'organization.view',
            'organization.create',
            'organization.update',
            'organization.delete',

            // EBOOK
            'ebook.view',
            'ebook.create',
            'ebook.update',
            'ebook.delete',

            // EVENT
            'event.view',
            'event.create',
            'event.update',
            'event.delete',

            // USER
            'user.view',
            'user.create',
            'user.update',
            'user.delete',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
            ]);
        }
    }
}
