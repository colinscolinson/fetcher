<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class WbApiService
{
    protected string $host;
    protected string $key;
    protected int $limit;

    public function __construct()
    {
        $this->host = rtrim(config('services.wb.host', env('API_HOST', 'http://109.73.206.144:6969')), '/');
        $this->key = config('services.wb.key', env('API_KEY', 'E6kUTYrYwZq2tN4QEtyzsbEBk3ie'));
        $this->limit = (int) config('services.wb.limit', env('API_LIMIT', 100));
    }

    /**
     * Делает запрос и возвращает массив объектов в data (или пустой массив).
     * Пагинация: передаём page, собираем пока data не пустой.
     *
     * @param string $path '/api/sales' etc
     * @param array $params dateFrom, dateTo (если нужно)
     * @return array
     * @throws \Exception
     */

    protected function fetchAllPages(string $path, array $params = []): array
    {
        $page = $params['page'] ?? 1;
        $all = [];

        do {
            $params['page'] = $page;
            $params['key'] = $this->key;
            $params['limit'] = $this->limit;

            $url = $this->host . $path;

            $response = Http::timeout(30)->get($url, $params);

            if (!$response->ok()) {
                throw new \Exception("WB API request failed: {$url} -> " . $response->status());
            }

            $json = $response->json();

            $data = $json['data'] ?? [];

            if (!is_array($data)) {
                break;
            }

            if (count($data) === 0) {
                break;
            }

            $all = array_merge($all, $data);

            if (count($data) < $this->limit) {
                break;
            }

            $page++;
        } while (true);

        return $all;
    }

    public function fetchSales(string $dateFrom, string $dateTo): array
    {
        return $this->fetchAllPages('/api/sales', [
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
        ]);
    }

    public function fetchOrders(string $dateFrom, string $dateTo): array
    {
        return $this->fetchAllPages('/api/orders', [
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
        ]);
    }

    public function fetchIncomes(string $dateFrom, string $dateTo): array
    {
        return $this->fetchAllPages('/api/incomes', [
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
        ]);
    }

    public function fetchStocks(string $dateFrom): array
    {
        return $this->fetchAllPages('/api/stocks', [
            'dateFrom' => now()->toDateString(),
        ]);
    }
}
