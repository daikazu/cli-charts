<?php

use Daikazu\CliCharts\PieChart;

test('renders pie chart with data', function () {
    $chart = new PieChart(['A' => 60, 'B' => 40], [
        'colors' => false,
    ]);

    $output = $chart->render();

    // Uses Braille characters for rendering
    expect($output)->toContain('A')
        ->toContain('B')
        ->toContain('%')
        ->toContain('●'); // Legend markers
});

test('renders pie chart with title', function () {
    $chart = new PieChart(['A' => 50, 'B' => 50], [
        'title'  => 'Distribution',
        'colors' => false,
    ]);

    $output = $chart->render();

    expect($output)->toContain('Distribution');
});

test('shows percentage in legend', function () {
    $chart = new PieChart(['Half' => 50, 'Other' => 50], [
        'colors' => false,
    ]);

    $output = $chart->render();

    expect($output)->toContain('50.0%');
});

test('handles zero total gracefully', function () {
    $chart = new PieChart(['A' => 0, 'B' => 0], [
        'colors' => false,
    ]);

    $output = $chart->render();

    expect($output)->toContain('Error: Total value must be greater than zero');
});

test('handles single segment', function () {
    $chart = new PieChart(['Only' => 100], [
        'colors' => false,
    ]);

    $output = $chart->render();

    expect($output)->toContain('100.0%');
});
