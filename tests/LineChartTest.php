<?php

use Daikazu\CliCharts\LineChart;

test('renders line chart with data', function () {
    $chart = new LineChart(['Jan' => 10, 'Feb' => 20, 'Mar' => 15], [
        'colors' => false,
    ]);

    $output = $chart->render();

    // Uses Braille characters for smooth lines
    expect($output)->toContain('│')
        ->toContain('─')
        ->toContain('Jan');
});

test('renders line chart with title', function () {
    $chart = new LineChart(['A' => 10, 'B' => 20], [
        'title'  => 'Trend Line',
        'colors' => false,
    ]);

    $output = $chart->render();

    expect($output)->toContain('Trend Line');
});

test('handles flat data (all same values)', function () {
    $chart = new LineChart(['A' => 50, 'B' => 50, 'C' => 50], [
        'colors' => false,
    ]);

    $output = $chart->render();

    // Flat data should render a horizontal line
    expect($output)->toContain('│')
        ->toContain('─');
});

test('handles ascending data', function () {
    $chart = new LineChart(['A' => 10, 'B' => 20, 'C' => 30], [
        'colors' => false,
    ]);

    $output = $chart->render();

    expect($output)->toBeString();
});

test('handles descending data', function () {
    $chart = new LineChart(['A' => 30, 'B' => 20, 'C' => 10], [
        'colors' => false,
    ]);

    $output = $chart->render();

    expect($output)->toBeString();
});

test('supports custom line and point colors', function () {
    $chart = new LineChart(['A' => 10, 'B' => 20, 'C' => 15], [
        'lineColor'  => 'cyan',
        'pointColor' => 'yellow',
    ]);

    $output = $chart->render();

    // Should contain both cyan (line) and yellow (points) ANSI codes
    expect($output)->toContain("\033[36m") // cyan
        ->toContain("\033[33m"); // yellow
});

test('uses default cyan color when no colors specified', function () {
    $chart = new LineChart(['A' => 10, 'B' => 20], []);

    $output = $chart->render();

    expect($output)->toContain("\033[36m"); // cyan is default
});
