<?php

namespace App\Imports;

use App\Models\Product;
use Illuminate\Database\Eloquent\Model;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithProgressBar;
use Maatwebsite\Excel\Concerns\WithUpserts;
use Maatwebsite\Excel\Concerns\WithValidation;

/**
 * Class ProductImport
 *
 *
 * @see \App\Models\Product
 * @see \App\Commands\ImportCommand
 */
class ProductImport implements SkipsOnError, ToModel, WithProgressBar, WithUpserts, WithValidation
{
    use Importable, SkipsErrors;

    /**
     * The number of rows that were successfully imported.
     */
    private int $successCount = 0;

    /**
     * The number of rows that were skipped during the import.
     */
    private int $skipCount = 0;

    /**
     * This property is used to determine database write mode.
     */
    private bool $testMode = false;

    /**
     * The array of rows that failed validation.
     */
    private array $failedRows = [];

    /**
     * The model to return.
     */
    public function model(array $row): Model|Product|null
    {
        $cost = $row[3] ?? 0;
        $stock = $row[4] ?? 0;
        $discontinued = $row[5] ?? false;

        if (($cost < 5 && $stock < 10) || $cost > 1000) {
            $this->skipCount++;

            return null;
        }

        $this->successCount++;

        if ($this->testMode) {
            return null;
        }

        return new Product([
            'strProductCode' => $row[0],
            'strProductName' => $row[1],
            'strProductDesc' => $row[2],
            'strProductStock' => $stock,
            'strProductCost' => $cost,
            'decPrice' => $cost,
            'intStockLevel' => $stock,
            'dtmAdded' => now(),
            'dtmDiscontinued' => $discontinued ? now() : null,
        ]);
    }

    /**
     * The array of rules to apply to the data.
     */
    public function rules(): array
    {
        return [
            '0' => 'required', // strProductCode
            '1' => 'required', // strProductName
        ];
    }

    /**
     * Returns a string that specifies the unique key or identifier.
     *
     * @return string The unique key or identifier.
     */
    public function uniqueBy(): string
    {
        return 'id';
    }

    public function getSuccessCount(): int
    {
        return $this->successCount;
    }

    public function getSkipCount(): int
    {
        return $this->skipCount;
    }

    public function getRowCount(): int
    {
        return $this->successCount + $this->skipCount;
    }

    /**
     * Sets the test mode for the import.
     *
     * @return $this
     */
    public function setTestMode(true $true): self
    {
        $this->testMode = $true;

        return $this;
    }
}
