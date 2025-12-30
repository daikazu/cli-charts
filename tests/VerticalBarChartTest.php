<?php

use Daikazu\CliCharts\VerticalBarChart;

test('renders vertical bar chart with data', function () {
    $chart = new VerticalBarChart(['A' => 10, 'B' => 20], [
        'colors' => false,
    ]);

    $output = $chart->render();

    expect($output)->toContain('█')
        ->toContain('│')
        ->toContain('─');
});

test('renders with title', function () {
    $chart = new VerticalBarChart(['A' => 10], [
        'title'  => 'Sales Data',
        'colors' => false,
    ]);

    $output = $chart->render();

    expect($output)->toContain('Sales Data');
});

test('shows values when option enabled', function () {
    $chart = new VerticalBarChart(['A' => 42], [
        'showValues' => true,
        'colors'     => false,
    ]);

    $output = $chart->render();

    expect($output)->toContain('42');
});

test('shows grid lines when option enabled', function () {
    $chart = new VerticalBarChart(['A' => 10, 'B' => 20], [
        'gridLines' => true,
        'colors'    => false,
    ]);

    $output = $chart->render();

    expect($output)->toContain('·');
});

test('hides grid lines when option disabled', function () {
    $chart = new VerticalBarChart(['A' => 10, 'B' => 20], [
        'gridLines' => false,
        'colors'    => false,
    ]);

    $output = $chart->render();

    expect($output)->not->toContain('·');
});

test('handles multi-word labels', function () {
    $chart = new VerticalBarChart(['Category A' => 10, 'Category B' => 20], [
        'colors' => false,
    ]);

    $output = $chart->render();

    expect($output)->toBeString();
});
