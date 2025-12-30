<?php

declare(strict_types=1);

namespace Daikazu\CliCharts;

/**
 * Base Chart class for CLI rendering
 */
abstract class Chart
{
    protected $width = 60;

    protected $height = 15;

    protected $title = '';

    protected $colors = true;

    // ANSI color codes
    protected $colorCodes = [
        'reset'   => "\033[0m",
        'red'     => "\033[31m",
        'green'   => "\033[32m",
        'yellow'  => "\033[33m",
        'blue'    => "\033[34m",
        'magenta' => "\033[35m",
        'cyan'    => "\033[36m",
        'white'   => "\033[37m",
    ];

    /**
     * Constructor for Chart
     *
     * @param  array  $data  Data to be displayed
     * @param  array  $options  Optional configuration
     */
    public function __construct(protected array $data, array $options = [])
    {
        if (isset($options['width'])) {
            $this->width = $options['width'];
        }

        if (isset($options['height'])) {
            $this->height = $options['height'];
        }

        if (isset($options['title'])) {
            $this->title = $options['title'];
        }

        if (isset($options['colors']) && $options['colors'] === false) {
            $this->colors = false;
        }
    }

    /**
     * Abstract method to render the chart
     */
    abstract public function render();

    /**
     * Apply color to text if colors are enabled
     *
     * @param  string  $text  Text to color
     * @param  string  $color  Color to apply
     * @return string Colored text or original text
     */
    protected function colorize(string $text, $color)
    {
        if (! $this->colors || ! isset($this->colorCodes[$color])) {
            return $text;
        }

        return $this->colorCodes[$color] . $text . $this->colorCodes['reset'];
    }

    /**
     * Draw the title of the chart
     *
     * @return string The formatted title
     */
    protected function drawTitle()
    {
        if (empty($this->title)) {
            return '';
        }

        $padding = (int) max(0, floor(($this->width - strlen((string) $this->title)) / 2));

        return str_repeat(' ', $padding) . $this->colorize($this->title, 'cyan') . "\n\n";
    }

    /**
     * Find the maximum value in the data set
     *
     * @return float Maximum value
     */
    protected function getMaxValue()
    {
        $max = 0;
        foreach ($this->data as $value) {
            if (is_numeric($value) && $value > $max) {
                $max = $value;
            }
        }

        return $max;
    }
}
