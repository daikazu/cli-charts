# PHP CLI Charts



[![Latest Version on Packagist](https://img.shields.io/packagist/v/daikazu/cli-charts.svg?style=flat-square)](https://packagist.org/packages/daikazu/cli-charts)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/daikazu/cli-charts/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/daikazu/cli-charts/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/daikazu/cli-charts/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/daikazu/cli-charts/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/daikazu/cli-charts.svg?style=flat-square)](https://packagist.org/packages/daikazu/cli-charts)

A simple PHP package for rendering beautiful charts in the command line interface.

## Features

- Render bar charts and line charts in the terminal
- Customizable chart dimensions
- ANSI color support (with graceful fallback for terminals without color)
- Simple API with factory pattern

## Installation

You can install the package via composer:

```bash
composer require your-username/php-cli-charts
```

## Usage

### Basic Example

```php
<?php

require 'vendor/autoload.php';

use CLICharts\ChartFactory;

// Create data for the chart
$data = [
    'Category A' => 25,
    'Category B' => 40,
    'Category C' => 75,
    'Category D' => 30
];

// Create a bar chart
$chart = ChartFactory::create('bar', $data, [
    'title' => 'Sample Bar Chart',
    'width' => 80,
    'height' => 15,
    'colors' => true
]);

// Render the chart to the console
echo $chart->render();
```

### Available Chart Types

1. **Bar Chart** - Displays horizontal bars for each category

   ```php
   $chart = ChartFactory::create('bar', $data, $options);
   ```

2. **Vertical Bar Chart** - Displays vertical bars for each category

   ```php
   $chart = ChartFactory::create('vbar', $data, $options);
   ```

3. **Line Chart** - Displays a simple line chart connecting data points

   ```php
   $chart = ChartFactory::create('line', $data, $options);
   ```

4. **Pie Chart** - Displays a pie chart with percentage distribution

   ```php
   $chart = ChartFactory::create('pie', $data, $options);
   ```

### Configuration Options

You can customize the charts with these options:

- `width` - Total width of the chart in characters (default: 60)
- `height` - Height of the chart in lines (default: 15)
- `title` - Title to display above the chart
- `colors` - Enable/disable ANSI colors (default: true)

#### Vertical Bar Chart Options

Vertical bar charts support additional options:

- `showValues` - Display values on top of bars (default: false)
- `gridLines` - Show horizontal grid lines for easier reading (default: true)
- `barWidth` - Width of each bar in characters (default: 1)

```php
$chart = ChartFactory::create('vbar', $data, [
    'title' => 'Monthly Sales',
    'width' => 80,
    'height' => 20,
    'showValues' => true,
    'gridLines' => true
]);

## Example Output

### Bar Chart

```
                      Monthly Expenses (in $)                      

Food          │ ████████████████████████ 1200
Rent          │ ████████████████████████████████████████ 1800
Transport     │ ████████ 400
Entertainment │ ███████ 350
Utilities     │ █████ 250
Savings       │ ██████████ 500
```

### Line Chart

```
                   Temperature Trend (in °F)                   

    95 │                     ●
       │                    / \
       │                   /   \
       │                  /     \
       │                 /       \
       │                /         \
    70 │               /           \
       │              /             \
       │            ●/               \● 
       │           /                   \
       │          /                     \
       │        ●/                       \●
    45 │ ●─────●                           ●───●
       └──────────────────────────────────────
          Ja Fe Ma Ap Ma Ju Ju Au Se Oc No De
```

## Testing

```bash
composer test
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
