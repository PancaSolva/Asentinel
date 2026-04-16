<?php

namespace App\Health\Checks;

use App\Health\CheckResult;
use Illuminate\Support\Facades\DB;
use Throwable;

final class DatabaseHealthCheck
{
    public function run(): CheckResult
    {
        try {
            DB::connection()->getPdo();
            DB::select('SELECT 1');

            return new CheckResult('database', true);
        } catch (Throwable $exception) {
            return new CheckResult('database', false, [
                'error' => $exception->getMessage(),
            ]);
        }
    }
}
