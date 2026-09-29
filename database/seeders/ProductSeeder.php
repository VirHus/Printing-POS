<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $printing = Category::create([
            'name' => 'Printing',
            'description' => 'Document and image printing services',
        ]);

        $photocopy = Category::create([
            'name' => 'Photocopy',
            'description' => 'Black and white and color photocopy services',
        ]);

        $scanning = Category::create([
            'name' => 'Scanning',
            'description' => 'Document scanning services',
        ]);

        $rushId = Category::create([
            'name' => 'Rush ID',
            'description' => 'Rush ID photo and printing services',
        ]);

        /*
         * PRINTING
         */

        Product::create([
            'category_id' => $printing->id,
            'name' => 'Text Only - Black & White',
            'description' => 'Black and white text-only printing',
            'price' => 2.00,
            'unit' => 'page',
        ]);

        Product::create([
            'category_id' => $printing->id,
            'name' => 'Text with Image - Black & White',
            'description' => 'Black and white printing with images',
            'price' => 3.00,
            'unit' => 'page',
        ]);

        Product::create([
            'category_id' => $printing->id,
            'name' => 'Image Only - Black & White',
            'description' => 'Black and white image printing',
            'price' => 3.00,
            'unit' => 'page',
        ]);

        Product::create([
            'category_id' => $printing->id,
            'name' => 'Text Only - Partial Color',
            'description' => 'Text printing with partial color',
            'price' => 5.00,
            'unit' => 'page',
        ]);

        Product::create([
            'category_id' => $printing->id,
            'name' => 'Text with Image - Partial Color',
            'description' => 'Printing with text, images, and partial color',
            'price' => 7.00,
            'unit' => 'page',
        ]);

        Product::create([
            'category_id' => $printing->id,
            'name' => 'Image Only - Partial Color',
            'description' => 'Partial color image printing',
            'price' => 7.00,
            'unit' => 'page',
        ]);

        Product::create([
            'category_id' => $printing->id,
            'name' => 'Text Only - Full Color',
            'description' => 'Full color text printing',
            'price' => 10.00,
            'unit' => 'page',
        ]);

        Product::create([
            'category_id' => $printing->id,
            'name' => 'Text with Image - Full Color',
            'description' => 'Full color printing with text and images',
            'price' => 10.00,
            'unit' => 'page',
        ]);

        Product::create([
            'category_id' => $printing->id,
            'name' => 'Image Only - Full Color',
            'description' => 'Full color image printing',
            'price' => 10.00,
            'unit' => 'page',
        ]);

        /*
         * PHOTOCOPY
         */

        Product::create([
            'category_id' => $photocopy->id,
            'name' => 'Black & White Photocopy',
            'description' => 'Black and white photocopy',
            'price' => 2.00,
            'unit' => 'page',
        ]);

        Product::create([
            'category_id' => $photocopy->id,
            'name' => 'Color Photocopy',
            'description' => 'Color photocopy',
            'price' => 5.00,
            'unit' => 'page',
        ]);

        /*
         * SCANNING
         */

        Product::create([
            'category_id' => $scanning->id,
            'name' => 'Document Scanning',
            'description' => 'Document scanning service',
            'price' => 10.00,
            'unit' => 'page',
        ]);

        /*
         * RUSH ID
         */

        Product::create([
            'category_id' => $rushId->id,
            'name' => 'Rush ID',
            'description' => 'Rush ID photo and printing service',
            'price' => 50.00,
            'unit' => 'set',
        ]);
    }
}