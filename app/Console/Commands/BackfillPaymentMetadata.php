<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\PaymentHistory;
use App\Services\Payments\PayphoneService;
use Illuminate\Support\Facades\Log;

class BackfillPaymentMetadata extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'payments:backfill-metadata';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch missing client data (name, phone) from Payphone for existing payments';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Starting Payment Metadata Backfill...');

        $payphoneService = new PayphoneService();
        
        // Fetch all payments. In a larger system, you'd chunk this or filter by missing data.
        // For MVP/SaaS scale, verifying all or simple filter is fine.
        $payments = PaymentHistory::all();

        $bar = $this->output->createProgressBar(count($payments));
        $bar->start();

        foreach ($payments as $payment) {
            $data = $payment->payment_data ?? [];

            // Skip if we already have the name
            if (isset($data['clientName']) && !empty($data['clientName'])) {
                $bar->advance();
                continue;
            }

            $payphoneId = $payment->stripe_id; // Stored as stripe_id
            $clientTxId = $data['clientTransactionId'] ?? null;

            if (!$payphoneId || !$clientTxId) {
                $this->error(" \nSkipping Payment ID {$payment->id}: Missing Payphone ID or ClientTxID");
                $bar->advance();
                continue;
            }

            try {
                // Rate limit slightly to be nice to API
                usleep(200000); // 200ms

                $response = $payphoneService->confirmPayment($payphoneId, $clientTxId);

                if ($response['success']) {
                    $newData = $response['data'];
                    
                    // Merge new data with existing, prioritizing new data
                    $updatedData = array_merge($data, $newData);
                    
                    $payment->payment_data = $updatedData;
                    $payment->save();
                    
                    // Log::info("Backfilled Payment {$payment->id}");
                } else {
                    $this->warn(" \nFailed to fetch data for Payment {$payment->id}: " . ($response['message'] ?? 'Unknown error'));
                }

            } catch (\Exception $e) {
                $this->error(" \nException for Payment {$payment->id}: " . $e->getMessage());
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info('Backfill process completed.');

        return 0;
    }
}
