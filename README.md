# PHP CLI Charts

[![Latest Version on Packagist](https://img.shields.io/packagist/v/daikazu/cli-charts.svg?style=flat-square)](https://packagist.org/packages/daikazu/cli-charts)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/daikazu/cli-charts/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/daikazu/cli-charts/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/daikazu/cli-charts/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/daikazu/cli-charts/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/daikazu/cli-charts.svg?style=flat-square)](https://packagist.org/packages/daikazu/cli-charts)

A PHP library for rendering beautiful charts in the command line using Unicode characters, Braille patterns, and ANSI colors.

## Features

- 6 chart types: Bar, Vertical Bar, Line, Pie, Stacked Bar, and Percentage Bar
- High-resolution rendering using Braille characters for smooth curves and lines
- ANSI color support with automatic color cycling
- Customizable dimensions, titles, and display options
- Simple factory pattern API
- Requires PHP 8.4+

## Installation

```bash
composer require daikazu/cli-charts
```

## Quick Start

```php
<?php

use Daikazu\CliCharts\ChartFactory;

$data = [
    'Jan' => 120,
    'Feb' => 180,
    'Mar' => 150,
    'Apr' => 220,
];

$chart = ChartFactory::create('bar', $data, [
    'title' => 'Monthly Sales',
    'width' => 60,
]);

echo $chart->render();
```

## Chart Types

### Bar Chart

Horizontal bar chart with colored bars proportional to values.

```php
$chart = ChartFactory::create('bar', $data, $options);
```

**Example:**

```php
$data = [
    'Food'          => 1200,
    'Rent'          => 1800,
    'Transport'     => 400,
    'Entertainment' => 350,
    'Utilities'     => 250,
];

$chart = ChartFactory::create('bar', $data, [
    'title' => 'Monthly Expenses ($)',
    'width' => 60,
]);

echo $chart->render();
```

**Output:**

```
                    Monthly Expenses ($)

Food          │ █████████████████████████████ 1200
Rent          │ ████████████████████████████████████████████ 1800
Transport     │ ██████████ 400
Entertainment │ █████████ 350
Utilities     │ ██████ 250
```

---

### Vertical Bar Chart

Vertical bar chart with optional grid lines, value display, and legend.

```php
$chart = ChartFactory::create('vbar', $data, $options);
```

**Options:**

| Option | Type | Default | Description |
|--------|------|---------|-------------|
| `showValues` | bool | `false` | Display values above bars |
| `gridLines` | bool | `true` | Show horizontal grid lines |
| `barWidth` | int | `1` | Width of each bar in characters |

**Example:**

```php
$data = [
    'Jan' => 120,
    'Feb' => 180,
    'Mar' => 150,
    'Apr' => 220,
    'May' => 190,
    'Jun' => 250,
];

$chart = ChartFactory::create('vbar', $data, [
    'title' => 'Monthly Sales',
    'width' => 60,
    'height' => 15,
    'showValues' => true,
    'gridLines' => true,
]);

echo $chart->render();
```

**Output:**

```
                       Monthly Sales

  250 │·               █
      │          █     █
      │·         █     █
      │    █     █  █  █
      │·   █     █  █  █
      │    █  █  █  █  █
  125 │·█  █  █  █  █  █
      │ █  █  █  █  █  █
      │·█  █  █  █  █  █
      │ █  █  █  █  █  █
      │·█  █  █  █  █  █
      │ █  █  █  █  █  █
    0 │·█  █  █  █  █  █
      └──────────────────
        Ja Fe Ma Ap Ma Ju

      Jan: 120; Feb: 180; Mar: 150
      Apr: 220; May: 190; Jun: 250
```

---

### Line Chart

Line chart using Braille characters for smooth, high-resolution lines.

```php
$chart = ChartFactory::create('line', $data, $options);
```

**Options:**

| Option | Type | Default | Description |
|--------|------|---------|-------------|
| `lineColor` | string | `'cyan'` | Color for the line |
| `pointColor` | string | `null` | Color for data points (uses lineColor if null) |

**Example:**

```php
$data = [
    'Jan' => 120,
    'Feb' => 180,
    'Mar' => 150,
    'Apr' => 220,
    'May' => 190,
    'Jun' => 250,
];

$chart = ChartFactory::create('line', $data, [
    'title' => 'Sales Trend',
    'width' => 60,
    'height' => 15,
    'lineColor' => 'cyan',
    'pointColor' => 'red',
]);

echo $chart->render();
```

**Output:**

```
                        Sales Trend

  250 │                                                  ⡠⠛
      │                                                ⡠⠊
      │                              ⢀⣄              ⡠⠊
      │                             ⢠⠊⠉⠑⠢⢄⡀        ⢀⠔⠁
  207 │                           ⢀⠔⠁     ⠈⠑⠤⣀   ⢀⠔⠁
      │                          ⡠⠃           ⠉⠲⡶⠁
      │         ⢀⠾⠦⣀           ⢠⠊
  163 │       ⢀⠔⠁   ⠉⠒⠤⡀     ⢀⠔⠁
      │     ⢀⠔⠁        ⠈⠑⠢⢄⣀⡠⠃
      │    ⡠⠃              ⠙⠁
      │  ⡠⠊
  120 │⣤⠊
      └────────────────────────────────────────────────────
      Jan      Feb       Mar        Apr       May       Ju
```

---

### Pie Chart

Circular pie chart rendered using Braille characters with a color legend.

```php
$chart = ChartFactory::create('pie', $data, $options);
```

**Example:**

```php
$data = [
    'Chrome'  => 65,
    'Safari'  => 19,
    'Firefox' => 8,
    'Edge'    => 5,
    'Other'   => 3,
];

$chart = ChartFactory::create('pie', $data, [
    'title' => 'Browser Market Share',
    'width' => 60,
]);

echo $chart->render();
```

**Output:**

```
                    Browser Market Share

          ⢀⣀⣠⣤⣤⣤⣤⣤⣀⣀         
       ⣀⣴⣾⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣶⣄⡀     
     ⣠⣾⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣦⡀   
   ⢀⣼⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣄  
  ⢀⣾⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣆ 
  ⣸⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⡀
  ⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⡇
  ⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⡇
  ⢻⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⠃
  ⠘⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⡟ 
   ⠘⢿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⠟  
    ⠈⠻⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⡿⠋   
      ⠈⠛⢿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⠟⠋     
         ⠈⠙⠛⠻⠿⠿⠿⠿⠿⠛⠛⠉        

  ● Chrome   65.0% (65)
  ● Safari   19.0% (19)
  ● Firefox   8.0% (8)
  ● Edge      5.0% (5)
  ● Other     3.0% (3)
```

---

### Stacked Bar Chart

Single horizontal bar divided into colored segments with percentages.

```php
$chart = ChartFactory::create('stacked', $data, $options);
// Alias: ChartFactory::create('sbar', $data, $options);
```

**Example:**

```php
$data = [
    'Chrome'  => 65,
    'Safari'  => 19,
    'Firefox' => 8,
    'Edge'    => 5,
    'Other'   => 3,
];

$chart = ChartFactory::create('stacked', $data, [
    'title' => 'Browser Market Share',
    'width' => 60,
]);

echo $chart->render();
```

**Output:**

```
                    Browser Market Share

[█████████████████████████████████████▊███████████████▋██▉█▊]
 Chrome 65%   Safari 19%   Firefox 8%   Edge 5%   Other 3%
```

---

### Percentage Bar Chart

Vertical list where each item has a horizontal percentage bar.

```php
$chart = ChartFactory::create('percent', $data, $options);
// Alias: ChartFactory::create('pbar', $data, $options);
```

**Example:**

```php
$data = [
    'Chrome'  => 65,
    'Safari'  => 19,
    'Firefox' => 8,
    'Edge'    => 5,
    'Other'   => 3,
];

$chart = ChartFactory::create('percent', $data, [
    'title' => 'Browser Market Share',
    'width' => 60,
]);

echo $chart->render();
```

**Output:**

```
                    Browser Market Share

Chrome  █████████████████████████████▎                65.0%
Safari  ████████▌                                     19.0%
Firefox ███▋                                           8.0%
Edge    ██▎                                            5.0%
Other   █▍                                             3.0%
```

---

## Common Options

All chart types support these base options:

| Option | Type | Default | Description |
|--------|------|---------|-------------|
| `width` | int | `60` | Total width in characters |
| `height` | int | `15` | Chart height in lines |
| `title` | string | `''` | Title displayed above the chart |
| `colors` | bool | `true` | Enable/disable ANSI colors |

## Available Colors

Charts automatically cycle through these ANSI colors:

- `red`
- `green`
- `yellow`
- `blue`
- `magenta`
- `cyan`
- `white`

Colors are assigned based on a hash of the label, ensuring consistent colors for the same labels across renders.

## API Reference

### ChartFactory

```php
ChartFactory::create(string $type, array $data, array $options = []): Chart
```

**Parameters:**

- `$type` - Chart type: `'bar'`, `'vbar'`, `'line'`, `'pie'`, `'stacked'`/`'sbar'`, `'percent'`/`'pbar'`
- `$data` - Associative array of `label => value` pairs
- `$options` - Configuration options array

**Returns:** Chart instance

**Throws:** `InvalidArgumentException` for unsupported chart types

### Chart Classes

All chart classes extend the abstract `Chart` class and implement:

```php
public function render(): string
```

Returns the complete chart as a string ready to output.

**Direct instantiation:**

```php
use Daikazu\CliCharts\BarChart;
use Daikazu\CliCharts\VerticalBarChart;
use Daikazu\CliCharts\LineChart;
use Daikazu\CliCharts\PieChart;
use Daikazu\CliCharts\StackedBarChart;
use Daikazu\CliCharts\PercentageBarChart;

$chart = new BarChart($data, $options);
echo $chart->render();
```

## Testing

```bash
composer test
```

Run the visual demo:

```bash
php demo.php
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Mike Wall](https://github.com/daikazu)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
