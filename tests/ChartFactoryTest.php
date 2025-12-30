<?php

use Daikazu\CliCharts\BarChart;
use Daikazu\CliCharts\ChartFactory;
use Daikazu\CliCharts\LineChart;
use Daikazu\CliCharts\PercentageBarChart;
use Daikazu\CliCharts\PieChart;
use Daikazu\CliCharts\StackedBarChart;
use Daikazu\CliCharts\VerticalBarChart;

test('creates bar chart', function () {
    $chart = ChartFactory::create('bar', ['A' => 10, 'B' => 20]);

    expect($chart)->toBeInstanceOf(BarChart::class);
});

test('creates vertical bar chart', function () {
    $chart = ChartFactory::create('vbar', ['A' => 10, 'B' => 20]);

    expect($chart)->toBeInstanceOf(VerticalBarChart::class);
});

test('creates line chart', function () {
    $chart = ChartFactory::create('line', ['A' => 10, 'B' => 20]);

    expect($chart)->toBeInstanceOf(LineChart::class);
});

test('creates pie chart', function () {
    $chart = ChartFactory::create('pie', ['A' => 10, 'B' => 20]);

    expect($chart)->toBeInstanceOf(PieChart::class);
});

test('throws exception for invalid chart type', function () {
    ChartFactory::create('invalid', ['A' => 10]);
})->throws(InvalidArgumentException::class, 'Unsupported chart type: invalid');

test('chart type is case insensitive', function () {
    $chart1 = ChartFactory::create('BAR', ['A' => 10]);
    $chart2 = ChartFactory::create('Bar', ['A' => 10]);

    expect($chart1)->toBeInstanceOf(BarChart::class);
    expect($chart2)->toBeInstanceOf(BarChart::class);
});

test('creates stacked bar chart', function () {
    $chart = ChartFactory::create('stacked', ['A' => 10, 'B' => 20]);

    expect($chart)->toBeInstanceOf(StackedBarChart::class);
});

test('creates stacked bar chart with alias', function () {
    $chart = ChartFactory::create('sbar', ['A' => 10, 'B' => 20]);

    expect($chart)->toBeInstanceOf(StackedBarChart::class);
});

test('creates percentage bar chart', function () {
    $chart = ChartFactory::create('percent', ['A' => 10, 'B' => 20]);

    expect($chart)->toBeInstanceOf(PercentageBarChart::class);
});

test('creates percentage bar chart with alias', function () {
    $chart = ChartFactory::create('pbar', ['A' => 10, 'B' => 20]);

    expect($chart)->toBeInstanceOf(PercentageBarChart::class);
});
