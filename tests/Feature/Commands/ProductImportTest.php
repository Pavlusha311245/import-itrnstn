<?php

namespace Tests\Feature\Commands;

use App\Commands\ImportCommand;
use App\Imports\ProductImport;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Artisan;
use Maatwebsite\Excel\Excel;
use Mockery;

beforeEach(function () {
    $this->tempFilePath = tempnam(sys_get_temp_dir(), 'test_import_');
    file_put_contents($this->tempFilePath, "id,name,price\n1,Test Product,100\n");
});

afterEach(function () {
    if (file_exists($this->tempFilePath)) {
        unlink($this->tempFilePath);
    }

    Mockery::close();
});

test('command returns FAILURE if the file does not exist', function () {
    $exitCode = Artisan::call('import:products', [
        'file' => 'non_existent_file.csv',
    ]);

    expect($exitCode)->toBe(Command::FAILURE);
    $output = Artisan::output();
    expect($output)->toContain('ERROR')
        ->and($output)->toContain('File not found');
});

test('command activates test mode when --test option is specified', function () {
    $importMock = Mockery::mock(ProductImport::class);
    $importMock->shouldReceive('setTestMode')
        ->once()
        ->with(true)
        ->andReturnSelf();
    $importMock->shouldReceive('withOutput')
        ->once()
        ->andReturnSelf();
    $importMock->shouldReceive('import')
        ->once()
        ->with($this->tempFilePath, Excel::CSV);
    $importMock->shouldReceive('getRowCount')->andReturn(1);
    $importMock->shouldReceive('getSuccessCount')->andReturn(1);
    $importMock->shouldReceive('getSkipCount')->andReturn(0);
    $importMock->shouldReceive('errors')->andReturn(collect());

    App::instance(ProductImport::class, $importMock);

    $exitCode = Artisan::call('import:products', [
        'file' => $this->tempFilePath,
        '--test' => true,
    ]);

    expect($exitCode)->toBe(Command::SUCCESS);
    $output = Artisan::output();
    expect($output)->toContain('Test mode');
    expect($output)->toContain('No data will be inserted into database');
});

test('command cancels import when user denies confirmation', function () {
    $command = Mockery::mock(ImportCommand::class)->makePartial();
    $command->shouldReceive('confirm')
        ->once()
        ->with('Test mode is not active. To enable it use --test argument. Are you sure you want to modify the database?')
        ->andReturn(false);

    $this->app->instance(ImportCommand::class, $command);

    $exitCode = Artisan::call('import:products', [
        'file' => $this->tempFilePath,
    ]);

    expect($exitCode)->toBe(Command::FAILURE);
    $output = Artisan::output();
    expect($output)->toContain('IMPORT CANCELLED');
    expect($output)->toContain('Operation aborted by the user');
});

test('command successfully imports file and returns SUCCESS', function () {
    $command = Mockery::mock(ImportCommand::class)->makePartial();
    $command->shouldReceive('confirm')
        ->once()
        ->andReturn(true);

    $importMock = Mockery::mock(ProductImport::class);
    $importMock->shouldReceive('withOutput')
        ->once()
        ->andReturnSelf();
    $importMock->shouldReceive('import')
        ->once()
        ->with($this->tempFilePath, Excel::CSV);
    $importMock->shouldReceive('getRowCount')->andReturn(10);
    $importMock->shouldReceive('getSuccessCount')->andReturn(8);
    $importMock->shouldReceive('getSkipCount')->andReturn(1);
    $importMock->shouldReceive('errors')->andReturn(collect([1]));

    App::instance(ProductImport::class, $importMock);

    $this->app->instance(ImportCommand::class, $command);

    $exitCode = Artisan::call('import:products', [
        'file' => $this->tempFilePath,
    ]);

    expect($exitCode)->toBe(Command::SUCCESS);
    $output = Artisan::output();
    expect($output)->toContain('IMPORT COMPLETED');
    expect($output)->toContain('10');
    expect($output)->toContain('8');
    expect($output)->toContain('1');
    expect($output)->toContain('1');
});
