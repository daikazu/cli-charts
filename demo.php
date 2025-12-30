<?php

declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';

use Daikazu\CliCharts\ChartFactory;

echo "\n";
echo "=== CLI Charts Demo ===\n";
echo "\n";

// Sample data sets
$salesData = [
    'Jan' => 120,
    'Feb' => 180,
    'Mar' => 150,
    'Apr' => 220,
    'May' => 190,
    'Jun' => 250,
];

$categoryData = [
    'Food'          => 1200,
    'Rent'          => 1800,
    'Transport'     => 400,
    'Entertainment' => 350,
    'Utilities'     => 250,
];

$marketShare = [
    'Chrome'  => 65,
    'Safari'  => 19,
    'Firefox' => 8,
    'Edge'    => 5,
    'Other'   => 3,
];

// Demo function
function demoChart(string $type, string $title, array $data, array $options = []): void
{
    echo str_repeat('─', 60) . "\n";
    echo "Chart Type: {$type}\n";
    echo str_repeat('─', 60) . "\n\n";

    try {
        $options = array_merge(['title' => $title, 'width' => 60, 'height' => 15], $options);
        $chart = ChartFactory::create($type, $data, $options);
        echo $chart->render();
    } catch (Throwable $e) {
        echo 'ERROR: ' . $e->getMessage() . "\n";
        echo 'File: ' . $e->getFile() . ':' . $e->getLine() . "\n";
    }

    echo "\n\n";
}

// Run demos
demoChart('bar', 'Monthly Expenses ($)', $categoryData);
demoChart('vbar', 'Monthly Sales', $salesData, ['showValues' => true, 'gridLines' => true]);
demoChart('line', 'Sales Trend', $salesData, ['lineColor' => 'cyan', 'pointColor' => 'red']);
demoChart('pie', 'Browser Market Share', $marketShare);
demoChart('stacked', 'Browser Market Share', $marketShare);
demoChart('percent', 'Browser Market Share', $marketShare);

echo "=== Demo Complete ===\n\n";
