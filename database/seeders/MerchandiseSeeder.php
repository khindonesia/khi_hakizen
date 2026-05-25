<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Variant;
use App\Models\ProductImage;
use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\ProductAttribute;
use App\Models\VariantAttribute;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class MerchandiseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Create Categories
        $apparel = ProductCategory::updateOrCreate(
            ['name' => 'Apparel'],
            ['status' => 'active']
        );

        $prints = ProductCategory::updateOrCreate(
            ['name' => 'Prints'],
            ['status' => 'active']
        );

        $accessories = ProductCategory::updateOrCreate(
            ['name' => 'Accessories'],
            ['status' => 'active']
        );

        // 2. Create Attribute & Values
        $sizeAttribute = Attribute::updateOrCreate(
            ['name' => 'Size'],
            ['status' => 'active']
        );

        $sizes = ['S', 'M', 'L', 'XL'];
        $sizeValues = [];
        foreach ($sizes as $sz) {
            $sizeValues[$sz] = AttributeValue::updateOrCreate(
                ['attribute_id' => $sizeAttribute->id, 'value' => $sz],
                ['status' => 'active']
            );
        }

        // 3. Create Products data
        $productsData = [
            [
                'category_id' => $apparel->id,
                'name' => 'KHI Official T-shirt',
                'description' => 'Represent the Komunitas Historia Indonesia with pride. This official t-shirt is crafted from a premium, ultra-soft cotton blend designed for both comfort during field explorations and casual daily wear. It features our classic insignia discretely placed on the chest, embodying a sophisticated, minimalist aesthetic that honors our historical roots.',
                'price' => 150000,
                'stock' => 50,
                'is_best_seller' => true,
                'image_url' => 'https://lh3.googleusercontent.com/aida-public/AB6AXuC3mtc3u3aysBgPMOUa-odjdASV3PXdTBe5GsIbecQJJqZOS5VDorjTtDjiAvRU_neSHTkkWBmgMvv6BKns4WKgih6jTSD-m4lMKhDQmYsobY6aXCsfRGLrd8N1Rf_iwT_SDVK2EoMoDC9I-1jEHVamy4Pr3v-1dXuTWLnh80ffu2NJHF2qpXexrXOg0WAAI3htoJRxopQzdy0oR5xy3yKI4iGM4HZh3O9-nt1tGHA1HsKn6ujf4WD9AHchVOvje_hdCyvvH_jjedJK',
                'additional_images' => [
                    'https://lh3.googleusercontent.com/aida-public/AB6AXuDAlU6puQvNoGK1UpfGFMFIqreNjqYuOBHbjcMemvY9-BFQLMnrEW_DdjGOGxUEqMNnau6R745qgV5IzSpB-tTh_8b5n_SebtIn5ojkfpAlYteDn3i0zx2d5VBKfYtqkqab3VuVsWR4HDIFLG7ofSXexCVSWTp4-CMvEr4DTVG5dsk8NUKIiMi7ElpVY5s234wPhplQ9CGB_KIzppkMfF5Aibu_YpzbywOR0cNSD--vnsIa-PaAEkOeyjZERAwACKJ3xyvPwomYsBQD',
                    'https://lh3.googleusercontent.com/aida-public/AB6AXuDfVT4mPGP9oKxXZr_U4zWTm_BTB6mtcyZa0nKGNidTnuVQP7iBO_-YDR-bcr1PJw6TgdIuoOQz-m0OjFZz2gnT-CQZ8A_JUfKbhhfK14Od9dlfEh0gr7iqJ4cTNktnaGYU6F9z1qtA8qmls09cXk2n2d_sTRreoxAp8aP1wH9UPlKcD4_CG0YoTaQpddEywH4HUjrkhynruoJeYA2RoSlze2xScEfRLFdXE-GxnIOg1KoEY7mgHuzOLVSzLxeoy5k1gU2inHzDUT5S'
                ],
                'has_sizes' => true
            ],
            [
                'category_id' => $prints->id,
                'name' => 'Batavia 1850 Map Reprint',
                'description' => 'High-resolution archival print of Batavia circa 1850. Perfect for framing and display, this detailed vintage map features rich sepia-toned parchment textures with deep indigo ink lines and intricate cartographic details.',
                'price' => 250000,
                'stock' => 20,
                'is_best_seller' => false,
                'image_url' => 'https://lh3.googleusercontent.com/aida-public/AB6AXuA1p6MFjosN5KpYjR_koifZXvO4hTl8MAZ7Be1CqDViN25DOqiFU-R2Vexw_mrlYQkC3fN4MOjtH_Hs0NVdlwi3UmK5As7YnR6A3qez5HfXZ1MP7WEInr8cEdVqLyp2qY1dPMMNvzA7pUF8WZ3dVDZxwawMnCW2302dTS75XM4GbVp_pLzuBrvnp9XS3w_7KM5GxGOgtYC8S2gLo3021vUKDU4veeQcqP2syD2H2X_dDVetNfTm-O3tMVI4hVNqJXW7ec0Kq0HgM7Iq',
                'additional_images' => [],
                'has_sizes' => false
            ],
            [
                'category_id' => $accessories->id,
                'name' => 'Heritage Canvas Tote',
                'description' => 'Durable organic cotton tote bag featuring architectural sketches of iconic heritage sites. Sturdy natural canvas fabric with thick, durable straps and a minimalist stylized architectural layout printed in a deep navy color.',
                'price' => 120000,
                'stock' => 35,
                'is_best_seller' => false,
                'image_url' => 'https://lh3.googleusercontent.com/aida-public/AB6AXuBlggxLaGGFGfwjvGwplDwnpI_bYctCI9OTgID-YrrR_PkgEUMCK_KR0zcX_Gyh4E9CEi6mq3wFmvNULImzdJ5KVaXcLklYMy_M6nbOLCesu3jvr3sSfBMuR1TpYaP5M0L38Z-ncQNzpfO4bygeL8Ss7E60jXSuAdHt0LHkxvXjot6-EiEavyjozBDnmW1f4ne6t5nzYy4jDyIY9JQbJsDykMcJQIVPfkAsz7tWp83Ce9ftuD1It3ocewzPmc8HsZQw7igTBnklQmGg',
                'additional_images' => [],
                'has_sizes' => false
            ],
            [
                'category_id' => $accessories->id,
                'name' => 'Heritage Tote Bag',
                'description' => 'A clean, bright product shot of a minimalist canvas tote bag featuring the KHI logo. Made of sturdy canvas material with deep colors and spacious room for books, notes, and archival findings.',
                'price' => 85000,
                'stock' => 40,
                'is_best_seller' => false,
                'image_url' => 'https://lh3.googleusercontent.com/aida-public/AB6AXuC9fpKPxerYtHfWO3mCXGLQimIHj25DZEGjxY01NWksIQZCo1PZaeSiU4PrXplzsCmOHgbOiNA3xkJ2_JxheN0KUAcK3QtbIrL4Eu-gtBHyxgs4yvFoTfx9lVJK_jnF9X6mlNvco7mY2ewGIfC7-MJ9020ZO76rOscXX7rFQgCMWY0_da1k8QI6diDn0sMqK7o8U_Ppk1q3UbWEONhQw_rEe0ZxXToKECAh5foXDKU81FBJl6N2UIRahl9ficS15LKc5VNAEsxAJz0U',
                'additional_images' => [],
                'has_sizes' => false
            ],
            [
                'category_id' => $accessories->id,
                'name' => "Explorer's Field Journal",
                'description' => 'A flat-lay product photograph of a sleek, dark-toned notebook with a subtly debossed KHI crest on the cover. Perfect for documenting historical landmarks, recording details of tours, and jotting down research notes.',
                'price' => 60000,
                'stock' => 15,
                'is_best_seller' => false,
                'image_url' => 'https://lh3.googleusercontent.com/aida-public/AB6AXuB4V5LbChJxFN2VOL00Hd2tx1O_79BvVHNOIIvPKyaKiYyvmTtB5aqEYkWnNpdntuRt-2N7jp91qBixEZ989qtXX4ai5VD4LJduqW7eU9Ox9w43b5hBd7pvWwUMAgfGnNTY6CW_K5ko-1JFzTWLjuTOrBk3lFpqgwx-UwGqTyHRbN50sHsEm3yYSffTvd-MXK7uw1hIGeF_51zdwPn0sYFo7V8aQYT0NaHbxtxkWUKKB0XYCM2NyeTY2esh_9gUpYNVHS-NpsgHYQs3',
                'additional_images' => [],
                'has_sizes' => false
            ],
            [
                'category_id' => $accessories->id,
                'name' => 'Archivist Mug',
                'description' => 'A modern product shot of a matte black ceramic coffee mug bearing a subtle, elegant KHI emblem. Sturdy design, comfortable to hold, perfect for long research nights or morning coffee.',
                'price' => 75000,
                'stock' => 30,
                'is_best_seller' => false,
                'image_url' => 'https://lh3.googleusercontent.com/aida-public/AB6AXuCyJzCaxSEadyHExS02wz976flHXKNdrH82MXwDlV9-HyIEP2zeKd4bmh__N4Xju7qE0IEmfPe_TS92QOKTD86Ejl02FycAhVyYrdyJxmpTfNJrcVPpYX_3eEieBn77lekvOMRv4a_vdI8JVePOPLa-rx7mE30EPZoTK3TE5Gtyd3GsOIcl7ugBCf6Hltyd4E2sPJ18ijFR1IFYnh2JdUEI628_KVQIT9Ajnqf9Y7hWJ-rB5t7AyaaypUYnQK_5sVpmm0G6oNyMAoHY',
                'additional_images' => [],
                'has_sizes' => false
            ],
            [
                'category_id' => $accessories->id,
                'name' => 'Enamel Insignia Pin',
                'description' => 'A high-quality enamel lapel pin featuring the intricate KHI insignia. Rest on a soft, pale background that provides gentle contrast to the metallic edges of the pin, adding a scholarly look to any coat, jacket, or bag.',
                'price' => 35000,
                'stock' => 100,
                'is_best_seller' => false,
                'image_url' => 'https://lh3.googleusercontent.com/aida-public/AB6AXuChGkh1MrniDuZJKRD5U2rZkeIt7w8jZpq7rUQLW-3fDS35xIpt0u-68eMQQ_SD5lP3VDLjeyuLDK870QAmsDSYBmi26UvzJo0Iqv_DCQxY8FT6gvgmzMGorUAZoPQDoNKdQWAPk_LQh7YO66RNr3N9ecqZbp3iovr-0g71misUIfUoSds0YkJv6tGeQFhpvmJ3SYUeyKqVULj6Hsfqqp-q3uUJ33BV9uOp0x-RWSH-rV2I5FJFGgsBt8BFQhifB-IEzQPsac7RNoSx',
                'additional_images' => [],
                'has_sizes' => false
            ]
        ];

        foreach ($productsData as $data) {
            // Create Product
            $product = Product::updateOrCreate(
                ['slug' => Str::slug($data['name'])],
                [
                    'category_id' => $data['category_id'],
                    'name' => $data['name'],
                    'description' => $data['description'],
                    'status' => 'active',
                ]
            );

            // Clean existing product relations to avoid duplicate seed issues
            $product->variants()->delete();
            $product->images()->delete();
            $product->productAttributes()->delete();

            // Seed primary product image
            ProductImage::create([
                'product_id' => $product->id,
                'image_url' => $data['image_url'],
                'sort_order' => 0
            ]);

            // Seed additional product images if any
            if (!empty($data['additional_images'])) {
                foreach ($data['additional_images'] as $index => $additionalUrl) {
                    ProductImage::create([
                        'product_id' => $product->id,
                        'image_url' => $additionalUrl,
                        'sort_order' => $index + 1
                    ]);
                }
            }

            if ($data['has_sizes']) {
                // Populate size attributes
                foreach ($sizeValues as $sz => $val) {
                    ProductAttribute::create([
                        'product_id' => $product->id,
                        'attribute_id' => $sizeAttribute->id,
                        'attribute_value_id' => $val->id,
                    ]);
                }

                // Create size variants
                $variantsList = [
                    ['size' => 'S', 'price' => 150000, 'stock' => 10, 'is_default' => false],
                    ['size' => 'M', 'price' => 150000, 'stock' => 15, 'is_default' => true],
                    ['size' => 'L', 'price' => 150000, 'stock' => 20, 'is_default' => false],
                    ['size' => 'XL', 'price' => 155000, 'stock' => 5, 'is_default' => false],
                ];

                foreach ($variantsList as $vData) {
                    $variant = Variant::create([
                        'product_id' => $product->id,
                        'sku' => 'KHI-TSHIRT-' . $vData['size'],
                        'price' => $vData['price'],
                        'stock_quantity' => $vData['stock'],
                        'image_url' => $data['image_url'],
                        'is_default' => $vData['is_default'],
                        'status' => 'active'
                    ]);

                    VariantAttribute::create([
                        'variant_id' => $variant->id,
                        'attribute_id' => $sizeAttribute->id,
                        'attribute_value_id' => $sizeValues[$vData['size']]->id,
                    ]);
                }
            } else {
                // Create Default Variant
                Variant::create([
                    'product_id' => $product->id,
                    'sku' => strtoupper(substr(Str::slug($data['name']), 0, 4)) . '-' . $product->id . '-DEF',
                    'price' => $data['price'],
                    'stock_quantity' => $data['stock'],
                    'image_url' => $data['image_url'],
                    'is_default' => true,
                    'status' => 'active'
                ]);
            }
        }
    }
}
