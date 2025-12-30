<?php

declare(strict_types=1);

namespace Daikazu\CliCharts;

/**
 * Base Chart class for CLI rendering
 */
abstract class Chart
{
    protected int $width = 60;

    protected int $height = 15;

    protected string $title = '';

    protected bool $colors = true;

    /** @var array<string, string> ANSI color codes */
    protected array $colorCodes = [
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
     * @param  array<string|int, int|float>  $data  Data to be displayed
     * @param  array<string, mixed>  $options  Optional configuration
     */
    public function __construct(
        protected array $data,
        protected array $options = []
    ) {
        if (isset($this->options['width']) && is_int($this->options['width'])) {
            $this->width = $this->options['width'];
        }

        if (isset($this->options['height']) && is_int($this->options['height'])) {
            $this->height = $this->options['height'];
        }

        if (isset($this->options['title']) && is_string($this->options['title'])) {
            $this->title = $this->options['title'];
        }

        if (isset($this->options['colors']) && $this->options['colors'] === false) {
            $this->colors = false;
        }
    }

    /**
     * Abstract method to render the chart
     */
    abstract public function render(): string;

    /**
     * Apply color to text if colors are enabled
     *
     * @param  string  $text  Text to color
     * @param  string  $color  Color to apply
     * @return string Colored text or original text
     */
    protected function colorize(string $text, string $color): string
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
    protected function drawTitle(): string
    {
        if ($this->title === '') {
            return '';
        }

        $padding = (int) max(0, floor(($this->width - strlen($this->title)) / 2));

        return str_repeat(' ', $padding) . $this->colorize($this->title, 'cyan') . "\n\n";
    }

    /**
     * Find the maximum value in the data set
     *
     * @return float Maximum value
     */
    protected function getMaxValue(): float
    {
        $max = 0.0;
        foreach ($this->data as $value) {
            $floatValue = (float) $value;
            if ($floatValue > $max) {
                $max = $floatValue;
            }
        }

        return $max;
    }
}
