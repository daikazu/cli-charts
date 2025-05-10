<?php

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
        'gridLines' => true,    // Show horizontal grid lines
        'barWidth' => 1,         // Width of each bar in characters
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
    public function render()
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
                    ($y == 0 && $idx == 0) ||
                    ($y == $chartHeight - 1 && $idx == count($yLabelValues) - 1)) {
                    $yAxisLabel = $labelValue;
                    $showLabel = true;
                    break;
                }
            }

            // Format y-axis label
            if ($showLabel) {
                $output .= str_pad(round($yAxisLabel), 5, ' ', STR_PAD_LEFT).' │';
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
                $colorIndex = crc32($labels[$i]) % (count($colorKeys) - 1); // -1 to skip 'reset'
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
                    $output .= ' '.str_repeat(' ', $barSpacing - 1);
                }
            }

            $output .= "\n";
        }

        // Draw the x-axis with the correct length
        $output .= '      └'.str_repeat('─', $totalBars * $barSpacing)."\n";

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

        $output .= $labelRow."\n";

        // Add a legend with values
        $output .= "\n";
        $valueLabels = [];
        for ($i = 0; $i < $totalBars; $i++) {
            $label = $labels[$i];
            $value = $values[$i];

            // Colorize the label
            $colorKeys = array_keys($this->colorCodes);
            $colorIndex = crc32($label) % (count($colorKeys) - 1);
            $color = $colorKeys[$colorIndex + 1];

            // Format: Label: Value (with abbreviated label)
            $abbreviatedLabel = $this->abbreviateLabel($label);
            $valueLabels[] = $this->colorize($abbreviatedLabel, $color).': '.$value;
        }

        // Display the legend in multiple columns if needed
        $legendWidth = $this->width - 6;
        $currentLine = '';

        foreach ($valueLabels as $index => $item) {
            $itemWithoutColors = preg_replace('/\033\[\d+m/', '', $item);

            // Check if adding this item would exceed the width
            if (strlen($currentLine) + strlen($itemWithoutColors) + 2 > $legendWidth && ! empty($currentLine)) {
                $output .= '      '.$currentLine."\n";
                $currentLine = $item;
            } else {
                // Use semicolon as separator instead of comma to avoid confusion with multi-word labels
                $separator = ($index > 0 && ! empty($currentLine)) ? '; ' : '';
                $currentLine .= $separator.$item;
            }
        }

        if (! empty($currentLine)) {
            $output .= '      '.$currentLine."\n";
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
        if (strpos($label, ' ') === false || strlen($label) <= 6) {
            return $label;
        }

        $words = explode(' ', $label);

        // Special case: If the label follows a pattern like "Something X" where X is a single character
        // or digit, keep the format but abbreviate the first word if needed
        if (count($words) == 2 && strlen($words[1]) <= 2) {
            $firstWord = $words[0];
            // If the first word is long, abbreviate it to 1-3 chars
            if (strlen($firstWord) > 3) {
                $abbreviation = substr($firstWord, 0, 1);

                return $abbreviation.' '.$words[1];
            } else {
                // First word is already short
                return $label;
            }
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
                $result .= ' '.$word;
            }
            // Middle words: use first letter only
            else {
                $result .= ' '.substr($word, 0, 1);
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
        foreach ($this->data as $key => $value) {
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

    /**
     * Create a concise x-axis label that preserves uniqueness
     *
     * @param  string  $label  The original label
     * @return string The shortened label
     */
    private function createXAxisLabel($label)
    {
        // If it's already short or doesn't contain spaces, return as is
        if (strlen($label) <= 3 || strpos($label, ' ') === false) {
            return $label;
        }

        $words = explode(' ', $label);

        // For labels like "Category A", "Team B", etc.
        if (count($words) == 2 && strlen($words[1]) <= 2) {
            // Use first letter of first word + second word
            return substr($words[0], 0, 1).' '.$words[1];
        }

        // For other multi-word labels
        if (count($words) >= 2) {
            $result = '';
            foreach ($words as $i => $word) {
                // Add first letter of each word
                $result .= substr($word, 0, 1);
                if ($i < count($words) - 1) {
                    $result .= ' ';
                }
            }

            return $result;
        }

        // Fallback: just use first 2-3 chars
        return substr($label, 0, min(3, strlen($label)));
    }

    /**
     * Detect if labels share a common prefix pattern
     *
     * @param  array  $labels  Array of label strings
     * @return string|null Common prefix if found, null otherwise
     */
    private function detectCommonPrefix(array $labels)
    {
        if (count($labels) <= 1) {
            return null;
        }

        // Extract first words from all labels
        $firstWords = [];
        foreach ($labels as $label) {
            $parts = explode(' ', $label);
            if (count($parts) > 1) {
                $firstWords[] = $parts[0];
            }
        }

        // Count occurrences of each first word
        $counts = array_count_values($firstWords);

        // Find most common first word that appears in multiple labels
        $mostCommon = null;
        $highestCount = 1;

        foreach ($counts as $word => $count) {
            if ($count > $highestCount) {
                $mostCommon = $word;
                $highestCount = $count;
            }
        }

        // Only return if the word appears in multiple labels (>50% of labels)
        if ($highestCount > count($labels) / 2) {
            return $mostCommon;
        }

        return null;
    }

    /**
     * Get a display label with appropriate abbreviation based on context
     *
     * @param  string  $label  Original label
     * @param  string|null  $commonPrefix  Common prefix detected across labels
     * @return string Formatted display label
     */
    private function getDisplayLabel($label, $commonPrefix = null)
    {
        // If we have a common prefix pattern (like "Category A", "Category B")
        if ($commonPrefix !== null && strpos($label, $commonPrefix.' ') === 0) {
            $parts = explode(' ', $label);

            // If it's a two-part label like "Category A"
            if (count($parts) == 2) {
                // Just return the first letter of the prefix + the suffix
                return substr($commonPrefix, 0, 1).' '.$parts[1];
            }

            // For more complex labels starting with the common prefix
            $suffix = substr($label, strlen($commonPrefix) + 1);

            return substr($commonPrefix, 0, 1).' '.$suffix;
        }

        // If it's a multi-word label but doesn't match the common pattern
        if (strpos($label, ' ') !== false) {
            $words = explode(' ', $label);
            $result = '';

            foreach ($words as $index => $word) {
                if ($index === 0) {
                    // First word: first letter or two
                    $result .= substr($word, 0, min(2, strlen($word)));
                } elseif ($index === count($words) - 1) {
                    // Last word: keep fully
                    $result .= ' '.$word;
                } else {
                    // Middle words: first letter
                    $result .= ' '.substr($word, 0, 1);
                }
            }

            return $result;
        }

        // Simple single-word label, just return as is
        return $label;
    }
}
