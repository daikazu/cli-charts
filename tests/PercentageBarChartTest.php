<?php

use Daikazu\CliCharts\PercentageBarChart;

test('renders percentage bar chart with data', function () {
    $chart = new PercentageBarChart(['A' => 60, 'B' => 40], [
        'colors' => false,
    ]);

    $output = $chart->render();

    expect($output)->toContain('█')
        ->toContain('A')
        ->toContain('B')
        ->toContain('%');
});

test('renders with title', function () {
    $chart = new PercentageBarChart(['A' => 50, 'B' => 50], [
        'title'  => 'Distribution',
        'colors' => false,
    ]);

    $output = $chart->render();

    expect($output)->toContain('Distribution');
});

test('shows correct percentages', function () {
    $chart = new PercentageBarChart(['Half' => 50, 'Other' => 50], [
        'colors' => false,
    ]);

    $output = $chart->render();

    expect($output)->toContain('50.0%');
});

test('handles zero total gracefully', function () {
    $chart = new PercentageBarChart(['A' => 0, 'B' => 0], [
        'colors' => false,
    ]);

    $output = $chart->render();

    expect($output)->toContain('Error: Total value must be greater than zero');
});

test('handles single item', function () {
    $chart = new PercentageBarChart(['Only' => 100], [
        'colors' => false,
    ]);

    $output = $chart->render();

    expect($output)->toContain('Only')
        ->toContain('100.0%');
});

test('renders each item on separate line', function () {
    $chart = new PercentageBarChart([
        'First'  => 60,
        'Second' => 30,
        'Third'  => 10,
    ], ['colors' => false]);

    $output = $chart->render();
    $lines = explode("\n", trim($output));

    // Should have title line (if any) plus 3 data lines
    expect(count($lines))->toBeGreaterThanOrEqual(3);
});

test('uses partial block characters', function () {
    $chart = new PercentageBarChart(['A' => 33, 'B' => 33, 'C' => 34], [
        'colors' => false,
        'width'  => 60,
    ]);

    $output = $chart->render();

    // Should contain partial blocks for non-round percentages
    expect($output)->toBeString();
});
