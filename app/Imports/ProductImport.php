<?php

namespace App\Imports;

use App\Models\Product;
use Illuminate\Database\Eloquent\Model;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithProgressBar;
use Maatwebsite\Excel\Concerns\WithUpserts;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Validators\Failure;

/**
 * Class ProductImport
 *
 *
 * @see \App\Models\Product
 * @see \App\Commands\ImportCommand
 */
class ProductImport implements SkipsOnError, SkipsOnFailure, ToModel, WithHeadingRow, WithProgressBar, WithUpserts, WithValidation
{
    use Importable, SkipsErrors, SkipsFailures;

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
        $cost = $row['cost_in_gbp'] ?? 0;
        $stock = $row['stock'] ?? 0;
        $discontinued = $row['discontinued'] ?? false;

        if (($cost < 5 && $stock < 10) || $cost > 1000) {
            $this->skipCount++;

            return null;
        }

        $this->successCount++;

        if ($this->testMode) {
            return null;
        }

        return new Product([
            'strProductCode' => $row['product_code'],
            'strProductName' => $row['product_name'],
            'strProductDesc' => $row['product_description'],
            'strProductStock' => $stock,
            'strProductCost' => $cost,
            'decPrice' => $cost,
            'intStockLevel' => $stock,
            'dtmAdded' => now(),
            'dtmDiscontinued' => $discontinued ? now() : null,
        ]);
    }

    public function headingRow(): int
    {
        return 1;
    }

    public function startRow(): int
    {
        return 2;
    }

    /**
     * The array of rules to apply to the data.
     */
    public function rules(): array
    {
        return [
            'product_code' => 'required', // strProductCode
            'product_name' => 'required', // strProductName,
            'product_description' => 'required', // strProductDesc
            'cost_in_gbp' => 'required|numeric', // strProductCost
            'stock' => 'required|integer', // strProductStock
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

    /**
     * This method returns the number of rows that were successfully imported.
     */
    public function getSuccessCount(): int
    {
        return $this->successCount;
    }

    /**
     * This method returns the number of rows that were skipped during the import.
     */
    public function getSkipCount(): int
    {
        return $this->skipCount;
    }

    /**
     * This method returns the total number of rows processed.
     */
    public function getRowCount(): int
    {
        return $this->successCount + $this->skipCount + count($this->failedRows);
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

    /**
     * This method returns the array of rows that failed validation.
     */
    public function getFailedRows(): array
    {
        return $this->failedRows;
    }

    /**
     * Handles the failures provided by processing each Failure object and storing relevant details.
     *
     * @param  Failure  ...$failures  A collection of Failure objects containing row data and error messages.
     */
    public function onFailure(Failure ...$failures): void
    {
        foreach ($failures as $failure) {
            $this->failedRows[] = [
                'Row' => $failure->row(),
                'Product Code' => $failure->values()['product_code'] ?? null,
                'Messages' => $failure->errors(),
            ];
        }
    }
}
