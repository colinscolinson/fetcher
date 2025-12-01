<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\WbApiService;
use App\Models\Stock;
use App\Models\Income;
use App\Models\Sale;
use App\Models\Order;
use Carbon\Carbon;

class ImportWbData extends Command
{
    protected $signature = 'wb:import {--from=} {--to=}';
    protected $description = 'Import WB data (stocks,incomes,sales,orders)';

    protected WbApiService $wb;

    public function __construct(WbApiService $wb)
    {
        parent::__construct();
        $this->wb = $wb;
    }

    public function handle()
    {
        // dateFrom/dateTo: если не передали — ставим разумные значения
        $from = $this->option('from') ?? now()->subDays(7)->toDateString();
        $to = $this->option('to') ?? now()->toDateString();

        $this->info("Importing sales from {$from} to {$to}");
        $sales = $this->wb->fetchSales($from, $to);
        $this->saveSales($sales);

        $this->info("Importing orders from {$from} to {$to}");
        $orders = $this->wb->fetchOrders($from, $to);
        $this->saveOrders($orders);

        $this->info("Importing incomes from {$from} to {$to}");
        $incomes = $this->wb->fetchIncomes($from, $to);
        $this->saveIncomes($incomes);

        // stocks — только за day = from (по твоему описанию)
        $stockDate = $this->option('from') ?? now()->toDateString();
        $this->info("Importing stocks for {$stockDate}");
        $stocks = $this->wb->fetchStocks($stockDate);
        $this->saveStocks($stocks);

        $this->info('WB import finished.');
        return 0;
    }

    protected function saveSales(array $rows)
    {
        $this->info("Saving ".count($rows)." sales...");
        $bar = $this->output->createProgressBar(count($rows));
        $bar->start();

        foreach ($rows as $row) {
            try {
                // нормализуем ключи — в ответе могут быть snake_case как у миграций
                $saleId = $row['sale_id'] ?? ($row['saleID'] ?? null);
                $date = isset($row['date']) ? Carbon::parse($row['date'])->toDateString() : null;
                $lastChange = isset($row['last_change_date']) ? Carbon::parse($row['last_change_date'])->toDateString() : null;

                Sale::updateOrCreate(
                    ['sale_id' => (string)($saleId ?? uniqid('sale_'))],
                    [
                        'g_number' => $row['g_number'] ?? ($row['gNumber'] ?? null),
                        'date' => $date,
                        'last_change_date' => $lastChange,
                        'supplier_article' => $row['supplier_article'] ?? null,
                        'tech_size' => $row['tech_size'] ?? null,
                        'barcode' => $row['barcode'] ?? null,
                        'total_price' => $row['total_price'] ?? $row['totalPrice'] ?? null,
                        'discount_percent' => isset($row['discount_percent']) ? (int)$row['discount_percent'] : (isset($row['discountPercent']) ? (int)$row['discountPercent'] : null),
                        'is_supply' => $row['is_supply'] ?? null,
                        'is_realization' => $row['is_realization'] ?? null,
                        'warehouse_name' => $row['warehouse_name'] ?? ($row['warehouseName'] ?? null),
                        'country_name' => $row['country_name'] ?? null,
                        'oblast_okrug_name' => $row['oblast_okrug_name'] ?? null,
                        'region_name' => $row['region_name'] ?? null,
                        'income_id' => $row['income_id'] ?? null,
                        'odid' => $row['odid'] ?? null,
                        'spp' => $row['spp'] ?? null,
                        'for_pay' => $row['for_pay'] ?? null,
                        'finished_price' => $row['finished_price'] ?? null,
                        'price_with_disc' => $row['price_with_disc'] ?? null,
                        'nm_id' => $row['nm_id'] ?? null,
                        'subject' => $row['subject'] ?? null,
                        'category' => $row['category'] ?? null,
                        'brand' => $row['brand'] ?? null,
                        'is_storno' => $row['is_storno'] ?? null,
                        'payload' => $row,
                    ]
                );
            } catch (\Throwable $e) {
                report($e);
            }
            $bar->advance();
        }

        $bar->finish();
        $this->line('');
    }

    protected function saveOrders(array $rows)
    {
        $this->info("Saving ".count($rows)." orders...");
        $bar = $this->output->createProgressBar(count($rows));
        $bar->start();

        foreach ($rows as $row) {
            try {
                $gNumber = $row['g_number'] ?? null;
                $odid = $row['odid'] ?? null;
                $date = isset($row['date']) ? Carbon::parse($row['date'])->toDateTimeString() : null;
                $lastChange = isset($row['last_change_date']) ? Carbon::parse($row['last_change_date'])->toDateString() : null;

                Order::updateOrCreate(
                    ['g_number' => (string)($gNumber ?? uniqid('g_')), 'odid' => $odid ?? 0],
                    [
                        'date' => $date,
                        'last_change_date' => $lastChange,
                        'supplier_article' => $row['supplier_article'] ?? null,
                        'tech_size' => $row['tech_size'] ?? null,
                        'barcode' => $row['barcode'] ?? null,
                        'total_price' => $row['total_price'] ?? null,
                        'discount_percent' => $row['discount_percent'] ?? null,
                        'warehouse_name' => $row['warehouse_name'] ?? null,
                        'oblast' => $row['oblast'] ?? null,
                        'income_id' => $row['income_id'] ?? null,
                        'nm_id' => $row['nm_id'] ?? null,
                        'subject' => $row['subject'] ?? null,
                        'category' => $row['category'] ?? null,
                        'brand' => $row['brand'] ?? null,
                        'is_cancel' => $row['is_cancel'] ?? null,
                        'cancel_dt' => isset($row['cancel_dt']) ? Carbon::parse($row['cancel_dt'])->toDateTimeString() : null,
                        'payload' => $row,
                    ]
                );
            } catch (\Throwable $e) {
                report($e);
            }
            $bar->advance();
        }

        $bar->finish();
        $this->line('');
    }

    protected function saveIncomes(array $rows)
    {
        $this->info("Saving ".count($rows)." incomes...");
        $bar = $this->output->createProgressBar(count($rows));
        $bar->start();

        foreach ($rows as $row) {
            try {
                $incomeId = $row['income_id'] ?? null;
                $barcode = $row['barcode'] ?? null;
                $date = isset($row['date']) ? Carbon::parse($row['date'])->toDateString() : null;

                Income::updateOrCreate(
                    ['income_id' => $incomeId, 'barcode' => $barcode],
                    [
                        'number' => $row['number'] ?? null,
                        'date' => $date,
                        'last_change_date' => $row['last_change_date'] ?? null,
                        'supplier_article' => $row['supplier_article'] ?? null,
                        'tech_size' => $row['tech_size'] ?? null,
                        'quantity' => $row['quantity'] ?? null,
                        'total_price' => $row['total_price'] ?? null,
                        'date_close' => $row['date_close'] ?? null,
                        'warehouse_name' => $row['warehouse_name'] ?? null,
                        'nm_id' => $row['nm_id'] ?? null,
                        'payload' => $row,
                    ]
                );
            } catch (\Throwable $e) {
                report($e);
            }
            $bar->advance();
        }

        $bar->finish();
        $this->line('');
    }

    protected function saveStocks(array $rows)
    {
        $this->info("Saving ".count($rows)." stocks...");
        $bar = $this->output->createProgressBar(count($rows));
        $bar->start();

        foreach ($rows as $row) {
            try {
                $date = isset($row['date']) ? Carbon::parse($row['date'])->toDateString() : null;

                Stock::updateOrCreate(
                    [
                        'barcode' => $row['barcode'] ?? 0,
                        'warehouse_name' => $row['warehouse_name'] ?? ($row['warehouseName'] ?? null),
                        'tech_size' => $row['tech_size'] ?? null,
                        'date' => $date,
                    ],
                    [
                        'supplier_article' => $row['supplier_article'] ?? null,
                        'quantity' => $row['quantity'] ?? null,
                        'is_supply' => $row['is_supply'] ?? null,
                        'is_realization' => $row['is_realization'] ?? null,
                        'quantity_full' => $row['quantity_full'] ?? null,
                        'in_way_to_client' => $row['in_way_to_client'] ?? null,
                        'in_way_from_client' => $row['in_way_from_client'] ?? null,
                        'nm_id' => $row['nm_id'] ?? null,
                        'subject' => $row['subject'] ?? null,
                        'category' => $row['category'] ?? null,
                        'brand' => $row['brand'] ?? null,
                        'sc_code' => $row['sc_code'] ?? null,
                        'price' => $row['price'] ?? null,
                        'discount' => $row['discount'] ?? null,
                    ]
                );
            } catch (\Throwable $e) {
                report($e);
            }
            $bar->advance();
        }

        $bar->finish();
        $this->line('');
    }
}
