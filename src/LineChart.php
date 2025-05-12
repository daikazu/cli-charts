<?php

namespace Daikazu\CliCharts;

/**
 * Line Chart implementation for CLI
 */
class LineChart extends Chart
{
    /**
     * Render a simple line chart
     *
     * @return string The rendered chart
     */
    public function render()
    {
        $output = $this->drawTitle();

        $maxValue = $this->getMaxValue();
        $minValue = $this->getMinValue();
        $valueRange = $maxValue - $minValue;

        $points = array_values($this->data);
        $labels = array_keys($this->data);

        // Calculate required width based on labels
        $requiredWidth = $this->calculateRequiredWidth($labels);
        $this->width = max($this->width, $requiredWidth);

        // Calculate the actual height (excluding axes and labels)
        $chartHeight = $this->height - 1;

        // Create a 2D grid for the chart (filled with spaces)
        $grid = [];
        for ($y = 0; $y < $chartHeight; $y++) {
            $grid[$y] = array_fill(0, count($points) * 4, ' '); // Use 4 spaces between points
        }

        // Calculate positions for each data point
        $positions = [];
        for ($i = 0; $i < count($points); $i++) {
            $value = $points[$i];
            // Scale the value to fit in the chart height
            // If minValue == maxValue, place the point in the middle
            if ($valueRange == 0) {
                $y = floor($chartHeight / 2);
            } else {
                $y = $chartHeight - 1 - floor(($value - $minValue) / $valueRange * ($chartHeight - 1));
            }
            $y = max(0, min($chartHeight - 1, $y)); // Ensure y is within bounds
            $positions[$i] = $y;
            $grid[$y][$i * 4] = '●'; // Position points with proper spacing
        }

        // Add connecting lines between points - with improved, cleaner connections
        for ($i = 1; $i < count($positions); $i++) {
            $prev_y = $positions[$i - 1];
            $curr_y = $positions[$i];
            $prev_x = ($i - 1) * 4;
            $curr_x = $i * 4;

            if ($prev_y == $curr_y) {
                // Simple horizontal line between points on the same level
                for ($x = $prev_x + 1; $x < $curr_x; $x++) {
                    $grid[$prev_y][$x] = '─';
                }
            } else {
                // Determine direction (going up or down)
                $step = ($curr_y > $prev_y) ? 1 : -1;

                // Cleaner approach: Draw horizontal line from previous point
                // then add a corner, then vertical line, then connect to next point

                // Draw horizontal segment from previous point
                $middleX = $prev_x + 2; // Stop horizontal line 2 characters after the previous point
                for ($x = $prev_x + 1; $x < $middleX; $x++) {
                    $grid[$prev_y][$x] = '─';
                }

                // Place corner at the bend point
                //                $grid[$prev_y][$middleX] = ($step === 1) ? '╯' : '╮';
                $grid[$prev_y][$middleX] = ($step === 1) ? '╮' : '╯';

                // Draw vertical line after the corner
                for ($y = $prev_y + $step; $y != $curr_y; $y += $step) {
                    $grid[$y][$middleX] = '│';
                }

                // Place the corner at the bottom/top of the line
                $grid[$curr_y][$middleX] = ($step === 1) ? '╰' : '╭';

                // Draw horizontal line to the current point
                for ($x = $middleX + 1; $x < $curr_x; $x++) {
                    $grid[$curr_y][$x] = '─';
                }
            }
        }

        // Draw y-axis with labels
        $yLabelValues = [];
        if ($valueRange > 0) {
            // Calculate good value points for y-axis (top, middle, bottom)
            $yLabelValues[] = $maxValue;
            $yLabelValues[] = $minValue + $valueRange / 2;
            $yLabelValues[] = $minValue;
        } else {
            // If all values are the same
            $yLabelValues[] = $maxValue;
        }

        // Render the chart grid with axes
        for ($y = 0; $y < $chartHeight; $y++) {
            $row = $grid[$y];

            // Determine if we need to show a y-axis label at this position
            $yAxisLabel = '';
            $labelY = round(($chartHeight - 1 - $y) / ($chartHeight - 1) * $valueRange + $minValue);

            // Check if this y position should show a label
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

            // Format the y-axis label
            if ($showLabel) {
                $output .= str_pad(round($yAxisLabel), 5, ' ', STR_PAD_LEFT).' │ ';
            } else {
                $output .= '      │ ';
            }

            // Draw the row with points and lines
            for ($x = 0; $x < count($row); $x++) {
                // Display the cell content with appropriate color
                if ($row[$x] === '●') {
                    $output .= $this->colorize($row[$x], 'red');
                } elseif (in_array($row[$x], ['╭', '╮', '╯', '╰', '│', '─'])) {
                    $output .= $this->colorize($row[$x], 'blue');
                } else {
                    $output .= $row[$x];
                }
            }
            $output .= "\n";
        }

        // Draw the x-axis line with improved characters
        $output .= '      └'.str_repeat('─', count($points) * 4)."\n";

        // Draw x-axis labels
        $output .= '       ';

        // First pass: check if labels follow a pattern like "Category A", "Category B"
        $hasPattern = false;
        $commonPrefix = null;

        // Check if we have labels with a consistent pattern (first word + identifier)
        if (count($labels) > 1) {
            $firstWords = [];
            foreach ($labels as $label) {
                $parts = explode(' ', $label);
                if (count($parts) > 1) {
                    $firstWords[] = $parts[0];
                }
            }

            if (count($firstWords) > 0) {
                $uniqueFirstWords = array_unique($firstWords);
                if (count($uniqueFirstWords) === 1) {
                    $hasPattern = true;
                    $commonPrefix = reset($uniqueFirstWords);
                }
            }
        }

        for ($i = 0; $i < count($labels); $i++) {
            $label = $labels[$i];

            // Create a smart abbreviation for the label
            if ($hasPattern && strpos($label, $commonPrefix) === 0) {
                // For labels like "Category A", just use "A"
                $parts = explode(' ', $label);
                if (count($parts) > 1) {
                    $labelText = $parts[1];
                } else {
                    $labelText = substr($label, 0, 2);
                }
            } elseif (strpos($label, ' ') !== false) {
                // For other multi-word labels
                $parts = explode(' ', $label);
                $labelText = '';
                foreach ($parts as $part) {
                    $labelText .= substr($part, 0, 1);
                }
            } else {
                // Regular labels: first 2 chars
                $labelText = substr($label, 0, 2);
            }

            // Calculate position for this label
            // 7 chars for left margin + position of the point (i * 4)
            $targetPosition = 7 + ($i * 4);
            $currentPosition = strlen($output);

            // Add spaces to reach the target position
            if ($targetPosition > $currentPosition) {
                $output .= str_repeat(' ', $targetPosition - $currentPosition);
            }

            // Add the label with proper spacing
            $output .= $labelText;

            // Add extra space after the label if it's not the last one
            if ($i < count($labels) - 1) {
                $output .= '  ';
            }
        }
        $output .= "\n";

        return $output;
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
        // If there's a large gap between min and 0, keep the min.
        // Otherwise, start from 0 for better visual scaling.
        if ($min > 0 && $min < 0.3 * $this->getMaxValue()) {
            return 0;
        }

        return $min;
    }

    /**
     * Calculate the required width based on label lengths
     *
     * @param  array  $labels  Array of labels
     * @return int Required width in characters
     */
    private function calculateRequiredWidth(array $labels)
    {
        $totalPoints = count($labels);
        if ($totalPoints === 0) {
            return $this->width;
        }

        // Calculate minimum spacing needed between points
        $minSpacing = 4; // Minimum characters between points
        $leftMargin = 7; // Space for y-axis labels
        $rightMargin = 2; // Space for right edge

        // Calculate total width needed
        $totalWidth = $leftMargin + ($totalPoints - 1) * $minSpacing + $rightMargin;

        return $totalWidth;
    }
}
