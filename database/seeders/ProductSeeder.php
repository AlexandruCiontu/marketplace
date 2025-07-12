<?php

namespace Database\Seeders;

use App\Enums\ProductStatusEnum;
use App\Models\Category;
use App\Models\Department;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all departments and filter to only those with categories
        $departments = Department::whereHas('categories')->get();
        $categories = Category::all();

        // Verify we have departments with categories
        if ($departments->isEmpty()) {
            echo "\nNo departments with categories found. Please create categories for departments first.\n";
            return;
        }

        echo "\nFound " . $departments->count() . " departments with categories.\n";

        // Temporarily disable model events for performance
        Product::unsetEventDispatcher();

        // Use database transaction for better performance
        \DB::transaction(function () use ($departments, $categories) {
            $totalProducts = 1000;
            $batchSize = 1000; // Insert 1000 records at a time
            $totalBatches = ceil($totalProducts / $batchSize);

            echo "\nCreating $totalProducts products in $totalBatches batches...\n";

            // Use memory-efficient batch processing
            for ($batch = 0; $batch < $totalBatches; $batch++) {
                $productData = [];
                $currentBatchSize = min($batchSize, $totalProducts - ($batch * $batchSize));

                echo "Processing batch " . ($batch + 1) . " of $totalBatches (" . ($batch * $batchSize) . "-" .
                    (($batch * $batchSize) + $currentBatchSize) . ")...\n";

                // Pre-generate all data for the batch before DB insertion
                for ($i = 0; $i < $currentBatchSize; $i++) {
                    // Select random department with categories
                    $department = $departments->random();

                    // Get categories for this department
                    $departmentCategories = $categories->where('department_id', $department->id);
                    $category = $departmentCategories->random();

                    // Generate product title once for both title and slug
                    $title = $this->generateProductTitle();

                    $productData[] = [
                        'title' => $title,
                        'slug' => Str::slug($title . '-' . Str::random(5)),
                        'description' => $this->generateProductDescription(),
                        'price' => $this->generatePrice(),
                        'department_id' => $department->id,
                        'category_id' => $category->id,
                        'created_by' => 2,
                        'updated_by' => 2,
                        'status' => ProductStatusEnum::Published->value,
                        'quantity' => rand(1, 100),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }

                // Insert the batch
                \DB::table('products')->insert($productData);

                // Clear memory
                unset($productData);

                if (($batch + 1) % 10 === 0) {
                    echo "Completed " . (($batch + 1) * $batchSize) . " of $totalProducts products\n";
                }
            }
        });

        echo "\nFinished creating 1,000,000 products!\n";
    }

    /**
     * Generate a realistic product title
     */
    private function generateProductTitle(): string
    {
        $adjectives = ['Modern', 'Premium', 'Elite', 'Ergonomic', 'Durable', 'Smart', 'Classic', 'Luxury', 'Professional', 'Essential'];
        $products = ['Chair', 'Table', 'Laptop', 'Smartphone', 'Headphones', 'Watch', 'Keyboard', 'Monitor', 'Camera', 'Backpack', 'Wallet', 'Desk', 'Tablet', 'Speaker', 'Microphone'];
        $brands = ['TechMaster', 'HomeStyle', 'EliteGear', 'ProTech', 'SmartLife', 'NexGen', 'FutureHome', 'ArtisanCraft', 'InnovateTech', 'EcoFriendly'];

        return $adjectives[array_rand($adjectives)] . ' ' . $products[array_rand($products)] . ' by ' . $brands[array_rand($brands)];
    }

    /**
     * Generate a realistic product description
     */
    private function generateProductDescription(): string
    {
        $features = [
            'Made with premium quality materials',
            'Designed for maximum comfort and usability',
            'Comes with a 2-year warranty',
            'Energy-efficient and eco-friendly',
            'Perfect for home and office use',
            'Sleek and modern design',
            'Bluetooth connectivity',
            'Water and dust resistant',
            'Lightweight and portable',
            'Available in multiple colors',
            'Simple setup, no assembly required',
            'Advanced ergonomic design',
            'Handcrafted with attention to detail',
            'Seamless integration with smart devices',
            'Long-lasting battery life'
        ];

        $description = '';
        $numFeatures = rand(3, 6);

        for ($i = 0; $i < $numFeatures; $i++) {
            $description .= '• ' . $features[array_rand($features)] . "\n";
        }

        return $description;
    }

    /**
     * Generate a realistic price
     */
    private function generatePrice(): float
    {
        // Generate a price between $9.99 and $999.99
        return round(rand(999, 99999) / 100, 2);
    }
}
