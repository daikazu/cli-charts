<?php

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
    public function render()
    {
        $output = $this->drawTitle();

        $maxValue = $this->getMaxValue();
        $maxLabelLength = $this->getMaxLabelLength();
        $availableWidth = $this->width - $maxLabelLength - 3;

        foreach ($this->data as $label => $value) {
            $barLength = $maxValue > 0 ? round(($value / $maxValue) * $availableWidth) : 0;

            // Format the label with consistent spacing
            $labelOutput = str_pad($label, $maxLabelLength, ' ', STR_PAD_RIGHT);

            // Draw the bar
            $bar = str_repeat('█', $barLength);

            // Colorize the bar (cycle through colors)
            $colorKeys = array_keys($this->colorCodes);
            $colorIndex = crc32($label) % (count($colorKeys) - 1); // -1 to skip 'reset'
            $color = $colorKeys[$colorIndex + 1]; // +1 to skip 'reset'

            $output .= $labelOutput.' │ '.$this->colorize($bar, $color).' '.$value."\n";
        }

        return $output;
    }

    /**
     * Get the maximum label length
     *
     * @return int Maximum label length
     */
    private function getMaxLabelLength()
    {
        $maxLength = 0;
        foreach ($this->data as $label => $value) {
            $length = strlen($label);
            if ($length > $maxLength) {
                $maxLength = $length;
            }
        }

        return $maxLength;
    }
}
