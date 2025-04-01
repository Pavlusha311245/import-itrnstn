<?php

namespace App\Commands;

use App\Enums\MessageTypeEnum;
use App\Imports\ProductImport;
use Illuminate\Console\Command;
use Maatwebsite\Excel\Excel;

use function Termwind\render;

/**
 * Class ImportCommand
 *
 * This command will import products from a CSV file.
 *
 * @see ProductImport
 *
 * @extends Command
 */
class ImportCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:products {file : Path to file for import} {--test : Run in test mode without writing to database}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command will import products from a CSV file';

    /**
     * Handles the import process for a given file. Verifies the file's existence,
     * initiates the import operation, and logs any validation errors or success messages
     * during the process.
     */
    public function handle(): int
    {
        $filePath = $this->argument('file');
        $testMode = $this->option('test');

        if (! file_exists($filePath)) {
            $this->renderMessage(MessageTypeEnum::ERROR, 'ERROR', 'File not found');

            return Command::FAILURE;
        }

        $import = new ProductImport;

        if ($testMode) {
            $this->renderMessage(MessageTypeEnum::WARNING, 'Test mode', 'No data will be inserted into database');
            $import->setTestMode(true);
        } else {
            if (! $this->confirm('Test mode is not active. To enable it use --test argument. Are you sure you want to modify the database?')) {
                $this->renderMessage(MessageTypeEnum::ERROR, 'IMPORT CANCELLED', 'Operation aborted by the user.');

                return Command::FAILURE;
            }
        }

        $this->renderMessage(MessageTypeEnum::INFO, 'IMPORT', 'Starting import...');

        $import->withOutput($this->output)->import(filePath: $filePath, readerType: Excel::CSV);
        $this->renderImportResults($import);

        return Command::SUCCESS;
    }

    /**
     * This method renders a message with a specific type, title, and message.
     */
    public function renderMessage(MessageTypeEnum $type, string $title, string $message): void
    {
        $color = $type->getClassColor();

        render(<<<HTML
        <div class="py-1">
            <div class="px-2 $color font-bold text-black">$title</div>
            <em class="ml-1">$message</em>
        </div>
        HTML
        );
    }

    /**
     * Render import results with statistics
     */
    private function renderImportResults(ProductImport $import): void
    {
        $options = [
            'all' => $import->getRowCount(),
            'success' => $import->getSuccessCount(),
            'skipped' => $import->getSkipCount(),
            'errors' => $import->errors()->count(),
        ];

        render(<<<HTML
            <div class="mb-1">
                <div class="px-2 bg-green-500 font-bold text-black">IMPORT COMPLETED</div>
                <div class="px-1">
                    <div class="flex">
                        <div>
                            <strong>Rows proceed:</strong>
                            <span class="text-blue-500 font-bold">{$options['all']}</span> rows
                        </div>
                        <div>
                            <strong>Successful:</strong>
                            <span class="text-green-500 font-bold">{$options['success']}</span> rows
                        </div>
                        <div>
                            <strong>Skipped:</strong>
                            <span class="text-yellow-500 font-bold">{$options['skipped']}</span> rows
                        </div>
                        <div>
                            <strong>Errors: </strong>
                            <span class="text-red-500 font-bold">{$options['errors']}</span> rows
                        </div>
                    </div>
                </div>
            </div>
        HTML
        );
    }
}
