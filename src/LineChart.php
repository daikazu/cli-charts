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

        // Calculate the actual height (excluding axes and labels)
        $chartHeight = $this->height - 1;

        // Create a 2D grid for the chart (filled with spaces)
        $grid = [];
        for ($y = 0; $y < $chartHeight; $y++) {
            $grid[$y] = array_fill(0, count($points) * 2, ' '); // Double the width to ensure proper spacing
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
            $grid[$y][$i * 2] = '●'; // Position points with proper spacing
        }

        // Add connecting lines between points
        for ($i = 1; $i < count($positions); $i++) {
            $prev_y = $positions[$i - 1];
            $curr_y = $positions[$i];
            $prev_x = ($i - 1) * 2; // Calculate x position with spacing
            $curr_x = $i * 2;       // Calculate x position with spacing

            // Draw line between points
            if ($prev_y == $curr_y) {
                // Horizontal line
                for ($x = $prev_x + 1; $x < $curr_x; $x++) {
                    $grid[$prev_y][$x] = '─';
                }
            } else {
                // Diagonal line
                $start_y = min($prev_y, $curr_y);
                $end_y = max($prev_y, $curr_y);

                // Determine the direction
                $going_up = $prev_y > $curr_y;

                // Calculate the slope
                $slope = ($curr_y - $prev_y) / ($curr_x - $prev_x);

                // Draw the connecting line
                for ($x = $prev_x + 1; $x < $curr_x; $x++) {
                    // Calculate the y position for this x using linear interpolation
                    $interpolated_y = $prev_y + round(($x - $prev_x) * $slope);
                    $interpolated_y = max(0, min($chartHeight - 1, $interpolated_y));

                    // Use appropriate connector based on direction
                    if ($going_up) {
                        $grid[$interpolated_y][$x] = '/';
                    } else {
                        $grid[$interpolated_y][$x] = '\\';
                    }
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
                } elseif ($row[$x] === '/' || $row[$x] === '\\' || $row[$x] === '─') {
                    $output .= $this->colorize($row[$x], 'blue');
                } else {
                    $output .= $row[$x];
                }
            }
            $output .= "\n";
        }

        // Draw the x-axis line
        $output .= '      └'.str_repeat('─', count($points) * 2)."\n";

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

            // For first label, just add it
            if ($i === 0) {
                $output .= $labelText;
            } else {
                // For other labels, add enough space to position it correctly
                // Calculate absolute position for this label (7 chars offset + 2*i for proper alignment)
                $targetPosition = 7 + ($i * 2);
                $currentPosition = strlen($output);

                // Add spaces to reach the target position
                if ($targetPosition > $currentPosition) {
                    $output .= str_repeat(' ', $targetPosition - $currentPosition);
                    $output .= $labelText;
                } else {
                    // If we're already past the target (shouldn't happen but just in case)
                    $output .= ' '.$labelText;
                }
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
}
