<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Product;
use Illuminate\Console\Command;

class SmartShopDemoRefresh extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'smartshop:demo-refresh {--fresh : Run full migrate:fresh --seed reset}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Quickly refresh SmartShop demo data for products or full database.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        if ((bool) $this->option('fresh')) {
            $this->components->info('Running full database refresh with seeders...');

            $code = $this->call('migrate:fresh', [
                '--seed' => true,
                '--force' => true,
            ]);

            if ($code !== self::SUCCESS) {
                return self::FAILURE;
            }

            $this->components->info('SmartShop demo data fully refreshed.');

            return self::SUCCESS;
        }

        $this->components->info('Refreshing product catalog only...');

        Product::query()->delete();

        $code = $this->call('db:seed', [
            '--class' => 'Database\\Seeders\\ProductSeeder',
            '--force' => true,
        ]);

        if ($code !== self::SUCCESS) {
            return self::FAILURE;
        }

        $this->components->info('Product catalog refreshed.');

        return self::SUCCESS;
    }
}
