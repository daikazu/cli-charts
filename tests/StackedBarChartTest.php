<?php

use Daikazu\CliCharts\StackedBarChart;

test('renders stacked bar chart with data', function () {
    $chart = new StackedBarChart(['A' => 60, 'B' => 40], [
        'colors' => false,
    ]);

    $output = $chart->render();

    expect($output)->toContain('[')
        ->toContain(']')
        ->toContain('█')
        ->toContain('A')
        ->toContain('B')
        ->toContain('%');
});

test('renders with title', function () {
    $chart = new StackedBarChart(['A' => 50, 'B' => 50], [
        'title'  => 'Distribution',
        'colors' => false,
    ]);

    $output = $chart->render();

    expect($output)->toContain('Distribution');
});

test('shows percentages in legend', function () {
    $chart = new StackedBarChart(['Half' => 50, 'Other' => 50], [
        'colors' => false,
    ]);

    $output = $chart->render();

    expect($output)->toContain('50%');
});

test('handles zero total gracefully', function () {
    $chart = new StackedBarChart(['A' => 0, 'B' => 0], [
        'colors' => false,
    ]);

    $output = $chart->render();

    expect($output)->toContain('Error: Total value must be greater than zero');
});

test('handles single segment', function () {
    $chart = new StackedBarChart(['Only' => 100], [
        'colors' => false,
    ]);

    $output = $chart->render();

    expect($output)->toContain('Only')
        ->toContain('100%');
});

test('handles many segments', function () {
    $chart = new StackedBarChart([
        'A' => 30,
        'B' => 25,
        'C' => 20,
        'D' => 15,
        'E' => 10,
    ], ['colors' => false]);

    $output = $chart->render();

    expect($output)->toContain('A')
        ->toContain('B')
        ->toContain('C')
        ->toContain('D')
        ->toContain('E');
});
