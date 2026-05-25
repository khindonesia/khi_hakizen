<?php

use App\Filament\Resources\ProductResource\Pages\EditProduct;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

it('hydrates the product category when editing a product', function () {
    Filament::setCurrentPanel(Filament::getPanel('admin'));

    $adminRole = Role::firstOrCreate([
        'name' => 'admin',
        'guard_name' => 'web',
    ]);

    $admin = User::withoutEvents(function (): User {
        return User::create([
            'name' => 'Admin User',
            'email' => 'admin-resource-test@example.com',
            'username' => 'admin-resource-test',
            'password' => Hash::make('password'),
        ]);
    });

    $admin->assignRole($adminRole);

    $category = ProductCategory::create([
        'name' => 'Apparel',
        'status' => 'active',
    ]);

    $product = Product::create([
        'category_id' => $category->getKey(),
        'name' => 'Test Product',
        'slug' => 'test-product',
        'description' => null,
        'status' => 'active',
    ]);

    Livewire::actingAs($admin)
        ->test(EditProduct::class, ['record' => $product->getKey()])
        ->assertSet('data.category_id', $category->getKey());
});
