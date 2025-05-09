<?php

namespace Daikazu\CliCharts;




/**
 * Pie Chart implementation for CLI
 */
class PieChart extends Chart
{
    // Full block character for drawing the pie
    protected $fullBlock = '█';
    protected $emptyBlock = ' ';

    /**
     * Render a pie chart
     *
     * @return string The rendered chart
     */
    public function render()
    {
        $output = $this->drawTitle();

        // Calculate total and percentages
        $total = array_sum($this->data);
        if ($total <= 0) {
            return $output . "Error: Total value must be greater than zero.\n";
        }

        $percentages = [];
        foreach ($this->data as $label => $value) {
            $percentages[$label] = ($value / $total) * 100;
        }

        // Sort the data by value in descending order for better visual appearance
        arsort($this->data);

        // Calculate the pie chart dimensions
        // Terminal characters are typically about twice as tall as they are wide
        // so we'll use an aspect ratio of 2:1 (width:height) to make the pie look circular
        $radius = min(10, floor($this->width / 6)); // Adjust radius based on width
        $radiusY = $radius; // Vertical radius
        $radiusX = $radius * 2; // Horizontal radius (twice as wide to compensate for character aspect ratio)

        // Create an empty canvas for the pie
        $diameter = $radius * 2;
        $canvas = [];
        for ($y = 0; $y < $diameter; $y++) {
            $canvas[$y] = array_fill(0, $radiusX * 2, $this->emptyBlock);
        }

        // Draw the pie chart on the canvas
        $this->drawPieOnCanvas($canvas, $radiusX, $radiusY, $percentages);

        // Render the canvas
        for ($y = 0; $y < $diameter; $y++) {
            $output .= str_repeat(' ', $radius) . implode('', $canvas[$y]) . "\n";
        }

        $output .= "\n";

        // Calculate legend placement
        $legendWidth = $this->width - $radiusX * 2 - 2;

        // Draw the legend with percentages
        $legendLines = [];
        $i = 0;
        $maxLabelLength = $this->getMaxLabelLength();

        // Create legend items for sorted data
        foreach ($this->data as $label => $value) {
            $percentage = $percentages[$label];

            // Prepare colorized marker
            $colorKeys = array_keys($this->colorCodes);
            $colorIndex = $i % (count($colorKeys) - 1); // Skip 'reset'
            $color = $colorKeys[$colorIndex + 1]; // Skip 'reset'

            $marker = $this->colorize('■', $color);

            // Format: ■ Label: 42.5% (123)
            $formattedValue = number_format($value, 0, '.', ',');
            $legendItem = sprintf("%s %s: %.1f%% (%s)",
                $marker,
                str_pad(substr($label, 0, min($maxLabelLength, 12)), min($maxLabelLength, 12), ' '),
                $percentage,
                $formattedValue
            );

            $legendLines[] = $legendItem;
            $i++;
        }

        // Display the legend
        $maxLegendItems = floor($this->height / 2); // Limit legend items based on height

        for ($i = 0; $i < min(count($legendLines), $maxLegendItems); $i++) {
            $output .= str_repeat(' ', $radius) . $legendLines[$i] . "\n";
        }

        // If there are more items than can fit, add a note
        if (count($legendLines) > $maxLegendItems) {
            $output .= str_repeat(' ', $radius) . "(+" . (count($legendLines) - $maxLegendItems) . " more items)\n";
        }

        return $output;
    }

    /**
     * Draw the pie chart on the canvas
     *
     * @param array $canvas The canvas to draw on
     * @param int $radiusX The horizontal radius of the pie
     * @param int $radiusY The vertical radius of the pie
     * @param array $percentages The data percentages
     */
    protected function drawPieOnCanvas(&$canvas, $radiusX, $radiusY, $percentages)
    {
        $centerX = $radiusX;
        $centerY = $radiusY;

        // First, determine the start and end angles for each segment
        $angles = [];
        $startAngle = 0;

        // Calculate and store all segment angles
        foreach ($percentages as $label => $percentage) {
            $endAngle = $startAngle + ($percentage / 100) * 360;
            $angles[$label] = ['start' => $startAngle, 'end' => $endAngle];
            $startAngle = $endAngle;
        }

        // Now draw the filled ellipse
        for ($y = 0; $y < $radiusY * 2; $y++) {
            for ($x = 0; $x < $radiusX * 2; $x++) {
                // Calculate normalized distance from center (for an ellipse)
                $dx = $x - $centerX;
                $dy = $y - $centerY;
                $normalizedDistance = ($dx * $dx) / ($radiusX * $radiusX) + ($dy * $dy) / ($radiusY * $radiusY);

                // Only draw within the ellipse
                if ($normalizedDistance <= 1.0) {
                    // Calculate the angle of this pixel (in degrees, 0-360)
                    $angle = (atan2($dy, $dx) * 180 / M_PI + 360) % 360;

                    // Find which segment this pixel belongs to
                    $segmentIndex = 0;
                    foreach ($angles as $label => $segmentAngles) {
                        $start = $segmentAngles['start'];
                        $end = $segmentAngles['end'];

                        // Check if this pixel belongs to the current segment
                        // Need to handle cases where segment crosses 0/360 boundary
                        if ($start < $end) {
                            // Simple case: start < angle < end
                            if ($angle >= $start && $angle < $end) {
                                // Found the segment
                                break;
                            }
                        } else {
                            // Complex case: segment crosses 0/360 boundary
                            if ($angle >= $start || $angle < $end) {
                                // Found the segment
                                break;
                            }
                        }

                        $segmentIndex++;
                    }

                    // Colorize the pixel based on its segment
                    $colorKeys = array_keys($this->colorCodes);
                    $colorIndex = $segmentIndex % (count($colorKeys) - 1) + 1; // Skip 'reset'
                    $color = $colorKeys[$colorIndex];

                    $canvas[$y][$x] = $this->colorize($this->fullBlock, $color);
                }
            }
        }
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
