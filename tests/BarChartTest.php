<?php

use Daikazu\CliCharts\BarChart;

test('renders bar chart with data', function () {
    $chart = new BarChart(['Food' => 100, 'Rent' => 200], [
        'colors' => false,
    ]);

    $output = $chart->render();

    expect($output)->toContain('Food')
        ->toContain('Rent')
        ->toContain('█')
        ->toContain('100')
        ->toContain('200');
});

test('renders bar chart with title', function () {
    $chart = new BarChart(['A' => 10], [
        'title'  => 'My Chart',
        'colors' => false,
    ]);

    $output = $chart->render();

    expect($output)->toContain('My Chart');
});

test('handles empty data', function () {
    $chart = new BarChart([], ['colors' => false]);

    $output = $chart->render();

    expect($output)->toBeString();
});

test('handles single data point', function () {
    $chart = new BarChart(['Only' => 50], ['colors' => false]);

    $output = $chart->render();

    expect($output)->toContain('Only')
        ->toContain('50');
});

test('handles zero values', function () {
    $chart = new BarChart(['Zero' => 0, 'Some' => 100], ['colors' => false]);

    $output = $chart->render();

    expect($output)->toContain('Zero')
        ->toContain('Some');
});

test('respects width option', function () {
    $chart = new BarChart(['A' => 100], [
        'width'  => 40,
        'colors' => false,
    ]);

    $output = $chart->render();

    // Just verify it renders without error when width is constrained
    expect($output)->toBeString()->not->toBeEmpty();
});
