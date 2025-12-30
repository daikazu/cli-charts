<?php

declare(strict_types=1);

namespace Daikazu\CliCharts;

/**
 * Bar Chart implementation for CLI
 */
class BarChart extends Chart
{
    /**
     * Render a horizontal bar chart
     *
     * @return string The rendered chart
     */
    public function render(): string
    {
        $output = $this->drawTitle();

        $maxValue = $this->getMaxValue();
        $maxLabelLength = $this->getMaxLabelLength();
        $availableWidth = $this->width - $maxLabelLength - 3;

        foreach ($this->data as $label => $value) {
            $numericValue = (float) $value;
            $barLength = $maxValue > 0 ? (int) round(($numericValue / $maxValue) * $availableWidth) : 0;

            // Format the label with consistent spacing
            $labelOutput = str_pad((string) $label, $maxLabelLength, ' ', STR_PAD_RIGHT);

            // Draw the bar
            $bar = str_repeat('█', $barLength);

            // Colorize the bar (cycle through colors)
            $colorKeys = array_keys($this->colorCodes);
            $colorIndex = crc32((string) $label) % (count($colorKeys) - 1); // -1 to skip 'reset'
            $color = $colorKeys[$colorIndex + 1]; // +1 to skip 'reset'

            $output .= $labelOutput . ' │ ' . $this->colorize($bar, $color) . ' ' . $value . "\n";
        }

        return $output;
    }

    /**
     * Get the maximum label length
     *
     * @return int Maximum label length
     */
    private function getMaxLabelLength(): int
    {
        $maxLength = 0;
        foreach (array_keys($this->data) as $label) {
            $length = strlen((string) $label);
            if ($length > $maxLength) {
                $maxLength = $length;
            }
        }

        return $maxLength;
    }
}
