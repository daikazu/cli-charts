<?php

declare(strict_types=1);

namespace Daikazu\CliCharts;

use InvalidArgumentException;

/**
 * Factory class for creating charts
 */
class ChartFactory
{
    /**
     * Create a chart instance
     *
     * @param  string  $type  Type of chart to create
     * @param  array<string|int, int|float>  $data  Data for the chart
     * @param  array<string, mixed>  $options  Optional configuration
     * @return BarChart|VerticalBarChart|LineChart|PieChart|StackedBarChart|PercentageBarChart The created chart
     *
     * @throws InvalidArgumentException If chart type is invalid
     */
    public static function create(string $type, array $data, array $options = []): BarChart|VerticalBarChart|LineChart|PieChart|StackedBarChart|PercentageBarChart
    {
        return match (strtolower($type)) {
            'bar'  => new BarChart($data, $options),
            'vbar' => new VerticalBarChart($data, $options),
            'line' => new LineChart($data, $options),
            'pie'  => new PieChart($data, $options),
            'stacked', 'sbar' => new StackedBarChart($data, $options),
            'percent', 'pbar' => new PercentageBarChart($data, $options),
            default => throw new InvalidArgumentException("Unsupported chart type: {$type}"),
        };
    }
}
