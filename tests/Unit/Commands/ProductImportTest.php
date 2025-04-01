<?php

namespace Tests\Unit\Commands;

use App\Commands\ImportCommand;
use App\Enums\MessageTypeEnum;
use App\Imports\ProductImport;
use Mockery;
use ReflectionClass;
use Symfony\Component\Console\Output\BufferedOutput;

test('renderMessage outputs a message with correct formatting', function () {
    $command = new ImportCommand;
    $output = new BufferedOutput;

    $reflectionClass = new ReflectionClass($command);
    $outputProperty = $reflectionClass->getProperty('output');
    $outputProperty->setValue($command, $output);

    $type = Mockery::mock(MessageTypeEnum::class);
    $type->shouldReceive('getClassColor')->andReturn('bg-red-500');

    $command->renderMessage($type, 'Test Title', 'Test Message');

    $outputContent = $output->fetch();
    expect($outputContent)->toContain('bg-red-500');
    expect($outputContent)->toContain('Test Title');
    expect($outputContent)->toContain('Test Message');
});

test('renderImportResults correctly displays statistics', function () {
    $command = new ImportCommand;
    $output = new BufferedOutput;

    $reflectionClass = new \ReflectionClass($command);
    $outputProperty = $reflectionClass->getProperty('output');
    $outputProperty->setAccessible(true);
    $outputProperty->setValue($command, $output);

    $import = Mockery::mock(ProductImport::class);
    $import->shouldReceive('getRowCount')->andReturn(100);
    $import->shouldReceive('getSuccessCount')->andReturn(80);
    $import->shouldReceive('getSkipCount')->andReturn(15);
    $import->shouldReceive('errors')->andReturn(collect([1, 2, 3, 4, 5]));

    $reflectionMethod = new \ReflectionMethod($command, 'renderImportResults');
    $reflectionMethod->invoke($command, $import);

    $outputContent = $output->fetch();
    expect($outputContent)->toContain('IMPORT COMPLETED')
        ->and($outputContent)->toContain('100')
        ->and($outputContent)->toContain('80')
        ->and($outputContent)->toContain('15')
        ->and($outputContent)->toContain('5');
});

afterEach(function () {
    Mockery::close();
});
