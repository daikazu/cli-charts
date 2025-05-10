<?php

namespace Daikazu\CliCharts;

/**
 * Factory class for creating charts
 */
class ChartFactory
{
    /**
     * Create a chart instance
     *
     * @param  string  $type  Type of chart to create
     * @param  array  $data  Data for the chart
     * @param  array  $options  Optional configuration
     * @return Chart The created chart
     *
     * @throws \InvalidArgumentException If chart type is invalid
     */
    public static function create($type, array $data, array $options = [])
    {
        return match (strtolower($type)) {
            'bar' => new BarChart($data, $options),
            'vbar' => new VerticalBarChart($data, $options),
            'line' => new LineChart($data, $options),
            'pie' => new PieChart($data, $options),
            default => throw new \InvalidArgumentException("Unsupported chart type: {$type}"),
        };
    }
}
