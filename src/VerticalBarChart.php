<?php

declare(strict_types=1);

namespace Daikazu\CliCharts;

/**
 * Vertical Bar Chart implementation for CLI
 */
class VerticalBarChart extends Chart
{
    /**
     * Additional options specific to vertical bar charts
     */
    protected $options = [
        'showValues' => false,  // Show values on top of bars
        'gridLines'  => true,    // Show horizontal grid lines
        'barWidth'   => 1,         // Width of each bar in characters
    ];

    /**
     * Constructor for VerticalBarChart
     *
     * @param  array  $data  Data to be displayed
     * @param  array  $options  Optional configuration
     */
    public function __construct(array $data, array $options = [])
    {
        parent::__construct($data, $options);

        // Merge chart-specific options
        if (isset($options['showValues'])) {
            $this->options['showValues'] = (bool) $options['showValues'];
        }

        if (isset($options['gridLines'])) {
            $this->options['gridLines'] = (bool) $options['gridLines'];
        }

        if (isset($options['barWidth']) && is_int($options['barWidth']) && $options['barWidth'] > 0) {
            $this->options['barWidth'] = $options['barWidth'];
        }
    }

    /**
     * Render a vertical bar chart
     *
     * @return string The rendered chart
     */
    public function render(): string
    {
        $output = $this->drawTitle();

        $maxValue = $this->getMaxValue();
        $minValue = $this->getMinValue();
        $valueRange = $maxValue - $minValue;

        // Prepare data
        $values = array_values($this->data);
        $labels = array_keys($this->data);

        // Calculate bar width and spacing
        $totalBars = count($values);
        $maxLabelLength = $this->getMaxLabelLength();

        // Calculate spacing between bars based on label length
        $barSpacing = max(3, min($maxLabelLength, 8)); // At least 3 spaces, max 8

        // Determine height of chart (excluding axis and labels)
        $chartHeight = $this->height - 2; // Reserve space for x-axis and labels

        // Create grid for the chart with wider spacing for labels
        $gridWidth = $totalBars * $barSpacing;
        $grid = [];
        for ($y = 0; $y < $chartHeight; $y++) {
            $grid[$y] = array_fill(0, $gridWidth, ' ');
        }

        // Place bars on the grid
        for ($i = 0; $i < $totalBars; $i++) {
            $value = $values[$i];

            // Calculate bar height (handle special case of all same values)
            if ($valueRange == 0) {
                $barHeight = floor($chartHeight / 2); // Half height if all values are the same
            } else {
                $barHeight = ceil(($value - $minValue) / $valueRange * $chartHeight);
            }

            // Ensure minimum visible height if value > 0
            if ($value > 0 && $barHeight == 0) {
                $barHeight = 1;
            }

            // Fill in the bar from bottom to top
            for ($y = 1; $y <= $barHeight; $y++) {
                // Calculate position from bottom of chart area
                $pos = $chartHeight - $y;
                if ($pos >= 0 && $pos < $chartHeight) {
                    // Position bar with the new spacing
                    $grid[$pos][$i * $barSpacing] = '█';
                }
            }
        }

        // Draw y-axis with labels
        $yLabelValues = [];
        if ($valueRange > 0) {
            // Calculate labels for y-axis at top, middle, and bottom
            $yLabelValues[] = $maxValue;
            $yLabelValues[] = $minValue + $valueRange / 2;
            $yLabelValues[] = $minValue;
        } else {
            // If all values are the same
            $yLabelValues[] = $maxValue;
        }

        // Draw the chart grid with y-axis
        for ($y = 0; $y < $chartHeight; $y++) {
            $row = $grid[$y];

            // Determine if we need to show a y-axis label at this position
            $yAxisLabel = '';
            $showLabel = false;

            foreach ($yLabelValues as $idx => $labelValue) {
                $labelPosition = $valueRange > 0
                    ? $chartHeight - 1 - round(($labelValue - $minValue) / $valueRange * ($chartHeight - 1))
                    : floor($chartHeight / 2);

                if ($y == $labelPosition ||
                    ($y === 0 && $idx === 0) ||
                    ($y == $chartHeight - 1 && $idx === count($yLabelValues) - 1)) {
                    $yAxisLabel = $labelValue;
                    $showLabel = true;
                    break;
                }
            }

            // Format y-axis label
            if ($showLabel) {
                $output .= str_pad((string) (int) round($yAxisLabel), 5, ' ', STR_PAD_LEFT) . ' │';
            } else {
                $output .= '      │';
            }

            // Add dotted grid line for better readability (if enabled)
            if (isset($this->options['gridLines']) && $this->options['gridLines'] && $y % 2 === 0) {
                $output .= '·';
            } else {
                $output .= ' ';
            }

            // Draw the bars with colors (using wider spacing)
            for ($i = 0; $i < $totalBars; $i++) {
                $barPosition = $i * $barSpacing;

                // Colorize the bar (cycle through colors for different bars)
                $colorKeys = array_keys($this->colorCodes);
                $colorIndex = crc32((string) $labels[$i]) % (count($colorKeys) - 1); // -1 to skip 'reset'
                $color = $colorKeys[$colorIndex + 1]; // +1 to skip 'reset'

                if (isset($row[$barPosition]) && $row[$barPosition] === '█') {
                    // Fill in the bar with color
                    $output .= $this->colorize('█', $color);

                    // Show values only at the top segment of each bar
                    if (isset($this->options['showValues']) && $this->options['showValues'] &&
                        // Check if this is the top segment of the bar by checking the segment above
                        ($y === 0 || ($y > 0 && isset($grid[$y - 1][$barPosition]) && $grid[$y - 1][$barPosition] !== '█'))) {
                        // Show value above the bar (if there's space)
                        $valueStr = (string) $values[$i];
                        if (strlen($valueStr) <= $barSpacing - 1) {
                            $output .= $this->colorize($valueStr, $color);
                            $output .= str_repeat(' ', $barSpacing - 1 - strlen($valueStr));
                        } else {
                            $output .= str_repeat(' ', $barSpacing - 1);
                        }
                    } else {
                        $output .= str_repeat(' ', $barSpacing - 1);
                    }
                } else {
                    $output .= ' ' . str_repeat(' ', $barSpacing - 1);
                }
            }

            $output .= "\n";
        }

        // Draw the x-axis with the correct length
        $output .= '      └' . str_repeat('─', $totalBars * $barSpacing) . "\n";

        // Draw labels below x-axis, aligned under each bar
        $labelRow = '';

        // For each bar, add its label at the correct position
        for ($i = 0; $i < $totalBars; $i++) {
            $label = $labels[$i];

            // Determine how much space we have for each label
            $maxLabelDisplay = max(1, $barSpacing - 1);

            // Abbreviate multi-word labels for x-axis
            $abbreviatedLabel = $this->abbreviateLabel($label);

            // Truncate abbreviated label if still too long
            $displayLabel = substr($abbreviatedLabel, 0, $maxLabelDisplay);

            // Calculate position for this label
            // 7 spaces for left margin + position of the bar (i * barSpacing)
            $barPosition = 7 + ($i * $barSpacing);

            // Calculate label position: shift one position to the right
            // This will align the label better with the bar
            $labelPosition = $barPosition + 1;

            // Add padding to position the label correctly
            while (strlen($labelRow) < $labelPosition) {
                $labelRow .= ' ';
            }

            // Add the label
            $labelRow .= $displayLabel;
        }

        $output .= $labelRow . "\n";

        // Add a legend with values
        $output .= "\n";
        $valueLabels = [];
        for ($i = 0; $i < $totalBars; $i++) {
            $label = $labels[$i];
            $value = $values[$i];

            // Colorize the label
            $colorKeys = array_keys($this->colorCodes);
            $colorIndex = crc32((string) $label) % (count($colorKeys) - 1);
            $color = $colorKeys[$colorIndex + 1];

            // Format: Label: Value (with abbreviated label)
            $abbreviatedLabel = $this->abbreviateLabel($label);
            $valueLabels[] = $this->colorize($abbreviatedLabel, $color) . ': ' . $value;
        }

        // Display the legend in multiple columns if needed
        $legendWidth = $this->width - 6;
        $currentLine = '';

        foreach ($valueLabels as $index => $item) {
            $itemWithoutColors = preg_replace('/\033\[\d+m/', '', $item);

            // Check if adding this item would exceed the width
            if (strlen($currentLine) + strlen((string) $itemWithoutColors) + 2 > $legendWidth && ($currentLine !== '' && $currentLine !== '0')) {
                $output .= '      ' . $currentLine . "\n";
                $currentLine = $item;
            } else {
                // Use semicolon as separator instead of comma to avoid confusion with multi-word labels
                $separator = ($index > 0 && ($currentLine !== '' && $currentLine !== '0')) ? '; ' : '';
                $currentLine .= $separator . $item;
            }
        }

        if ($currentLine !== '' && $currentLine !== '0') {
            $output .= '      ' . $currentLine . "\n";
        }

        return $output;
    }

    /**
     * Abbreviate multi-word labels while maintaining uniqueness
     *
     * @param  string  $label  The original label
     * @return string The abbreviated label
     */
    protected function abbreviateLabel($label)
    {
        // If it's a single word or already short, return as is
        if (! str_contains($label, ' ') || strlen($label) <= 6) {
            return $label;
        }

        $words = explode(' ', $label);

        // Special case: If the label follows a pattern like "Something X" where X is a single character
        // or digit, keep the format but abbreviate the first word if needed
        if (count($words) === 2 && strlen($words[1]) <= 2) {
            $firstWord = $words[0];
            // If the first word is long, abbreviate it to 1-3 chars
            if (strlen($firstWord) > 3) {
                $abbreviation = substr($firstWord, 0, 1);

                return $abbreviation . ' ' . $words[1];
            }
            // First word is already short
            return $label;
        }

        $result = '';

        // Process each word
        foreach ($words as $index => $word) {
            // First word: use first letter only to save space
            if ($index === 0) {
                $result .= substr($word, 0, 1);
            }
            // Last word: keep it intact to maintain uniqueness
            elseif ($index === count($words) - 1) {
                $result .= ' ' . $word;
            }
            // Middle words: use first letter only
            else {
                $result .= ' ' . substr($word, 0, 1);
            }
        }

        return $result;
    }

    /**
     * Find the minimum value in the data set
     *
     * @return float Minimum value
     */
    protected function getMinValue()
    {
        $min = PHP_FLOAT_MAX;
        foreach ($this->data as $value) {
            if (is_numeric($value) && $value < $min) {
                $min = $value;
            }
        }

        // For bar charts, we typically want to start at 0 unless
        // we have negative values
        return $min < 0 ? $min : 0;
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
