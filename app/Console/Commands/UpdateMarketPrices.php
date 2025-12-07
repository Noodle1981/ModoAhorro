<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\EfficiencyBenchmark;
use App\Services\Market\MarketPriceService;
use Illuminate\Support\Facades\Log;

class UpdateMarketPrices extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'prices:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update efficiency benchmark prices using Mercado Libre API';

    /**
     * Execute the console command.
     */
    public function handle(MarketPriceService $priceService)
    {
        $this->info('Starting price update from Mercado Libre...');

        $benchmarks = EfficiencyBenchmark::whereNotNull('meli_search_term')->get();
        $total = $benchmarks->count();
        $bar = $this->output->createProgressBar($total);

        $bar->start();

        foreach ($benchmarks as $benchmark) {
            try {
                // Sleep to avoid rate limiting
                usleep(200000); // 0.2 seconds

                $newPrice = $priceService->fetchPrice($benchmark->meli_search_term);

                if ($newPrice) {
                    $benchmark->update([
                        'average_market_price' => $newPrice,
                        'updated_at' => now(), // Explicitly update timestamp
                    ]);
                    // Log for debugging (optional, can be verbose)
                    // Log::info("Updated {$benchmark->meli_search_term}: ${$newPrice}");
                } else {
                    $this->warn("\nCould not fetch price for: {$benchmark->meli_search_term}");
                }

            } catch (\Exception $e) {
                $this->error("\nError updating {$benchmark->meli_search_term}: " . $e->getMessage());
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info('Price update completed successfully!');
    }
}
