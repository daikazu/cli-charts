<?php

namespace Daikazu\CliCharts;

/**
 * Base Chart class for CLI rendering
 */
abstract class Chart
{
    protected $data = [];

    protected $width = 60;

    protected $height = 15;

    protected $title = '';

    protected $colors = true;

    // ANSI color codes
    protected $colorCodes = [
        'reset' => "\033[0m",
        'red' => "\033[31m",
        'green' => "\033[32m",
        'yellow' => "\033[33m",
        'blue' => "\033[34m",
        'magenta' => "\033[35m",
        'cyan' => "\033[36m",
        'white' => "\033[37m",
    ];

    /**
     * Constructor for Chart
     *
     * @param  array  $data  Data to be displayed
     * @param  array  $options  Optional configuration
     */
    public function __construct(array $data, array $options = [])
    {
        $this->data = $data;

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
     * Apply color to text if colors are enabled
     *
     * @param  string  $text  Text to color
     * @param  string  $color  Color to apply
     * @return string Colored text or original text
     */
    protected function colorize($text, $color)
    {
        if (! $this->colors || ! isset($this->colorCodes[$color])) {
            return $text;
        }

        return $this->colorCodes[$color].$text.$this->colorCodes['reset'];
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

        $padding = max(0, floor(($this->width - strlen($this->title)) / 2));

        return str_repeat(' ', $padding).$this->colorize($this->title, 'cyan')."\n\n";
    }

    /**
     * Find the maximum value in the data set
     *
     * @return float Maximum value
     */
    protected function getMaxValue()
    {
        $max = 0;
        foreach ($this->data as $key => $value) {
            if (is_numeric($value) && $value > $max) {
                $max = $value;
            }
        }

        return $max;
    }

    /**
     * Abstract method to render the chart
     */
    abstract public function render();
}
