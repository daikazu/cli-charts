<?php

declare(strict_types=1);

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
    public function render(): string
    {
        $output = $this->drawTitle();

        // Calculate total and percentages
        $total = array_sum($this->data);
        if ($total <= 0) {
            return $output . "Error: Total value must be greater than zero.\n";
        }

        // Store a copy of the original data for consistent display
        $originalData = $this->data;

        // Calculate percentages using original data
        $percentages = [];
        foreach ($originalData as $label => $value) {
            $percentages[$label] = ($value / $total) * 100;
        }

        // Make a sorted copy for the legend
        $sortedData = $originalData;
        arsort($sortedData);

        // Calculate the pie chart dimensions
        $radius = (int) min(10, floor($this->width / 6)); // Adjust radius based on width
        $radiusY = $radius;
        $radiusX = $radius * 2;

        // Create an empty canvas for the pie
        $diameter = $radius * 2;
        $canvas = [];
        for ($y = 0; $y < $diameter; $y++) {
            $canvas[$y] = array_fill(0, $radiusX * 2, $this->emptyBlock);
        }

        // Draw the pie chart on the canvas using exact percentages
        $this->drawPieOnCanvas($canvas, $radiusX, $radiusY, $percentages);

        // Render the canvas
        for ($y = 0; $y < $diameter; $y++) {
            $output .= str_repeat(' ', $radius) . implode('', $canvas[$y]) . "\n";
        }

        $output .= "\n";

        // Draw the legend with percentages
        $legendLines = [];
        $i = 0;
        $maxLabelLength = $this->getMaxLabelLength();

        // Create legend items using sorted data for readability
        foreach ($sortedData as $label => $value) {
            $percentage = $percentages[$label];

            // Prepare colorized marker with consistent color mapping
            $colorKeys = array_keys($this->colorCodes);

            // Calculate the same color index as in drawPieOnCanvas for consistency
            $labels = array_keys($percentages);
            $labelIndex = array_search($label, $labels);
            $colorIndex = ($labelIndex % (count($colorKeys) - 1)) + 1; // Skip 'reset'

            $color = $colorKeys[$colorIndex];
            $marker = $this->colorize('■', $color);

            // Format: ■ Label: 42.5% (123)
            $formattedValue = number_format($value, 0, '.', ',');
            $legendItem = sprintf(
                '%s %s: %.1f%% (%s)',
                $marker,
                str_pad(substr((string) $label, 0, min($maxLabelLength, 12)), min($maxLabelLength, 12), ' '),
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
            $output .= str_repeat(' ', $radius) . '(+' . (count($legendLines) - $maxLegendItems) . " more items)\n";
        }

        return $output;
    }

    /**
     * Draw the pie chart on the canvas
     *
     * @param  array  $canvas  The canvas to draw on
     * @param  int  $radiusX  The horizontal radius of the pie
     * @param  int  $radiusY  The vertical radius of the pie
     * @param  array  $percentages  The data percentages
     */
    protected function drawPieOnCanvas(array &$canvas, $radiusX, $radiusY, array $percentages)
    {
        $centerX = $radiusX;
        $centerY = $radiusY;

        // Count the total number of pixels in the pie
        $totalPiePixels = 0;
        $piePixelCoordinates = [];

        // First pass: Identify all pixels that belong to the pie
        for ($y = 0; $y < $radiusY * 2; $y++) {
            for ($x = 0; $x < $radiusX * 2; $x++) {
                $dx = $x - $centerX;
                $dy = $y - $centerY;

                // Apply a correction to create a more circular appearance
                $distanceX = $dx / $radiusX;
                $distanceY = $dy / ($radiusY * 0.8); // Adjust Y to compensate for terminal character aspect ratio

                $distance = sqrt($distanceX * $distanceX + $distanceY * $distanceY);

                if ($distance <= 1.0) {
                    $totalPiePixels++;
                    $piePixelCoordinates[] = [$x, $y];
                }
            }
        }

        if ($totalPiePixels === 0) {
            return; // No pixels to draw
        }

        // Calculate the exact number of pixels for each segment
        $pixelsPerSegment = [];
        $totalPercentage = array_sum($percentages);
        $pixelsAssigned = 0;

        // Sort segments from largest to smallest for better distribution
        $segmentLabels = array_keys($percentages);
        $segmentPercentages = array_values($percentages);
        array_multisort($segmentPercentages, SORT_DESC, $segmentLabels);

        // Assign pixels to each segment based on percentages
        foreach ($segmentLabels as $index => $label) {
            $percentage = $percentages[$label];
            $pixelCount = round(($percentage / $totalPercentage) * $totalPiePixels);

            // Ensure at least 1 pixel for non-zero segments
            if ($percentage > 0 && $pixelCount == 0) {
                $pixelCount = 1;
            }

            // Last segment gets remaining pixels to ensure total is exact
            if ($index === count($segmentLabels) - 1) {
                $pixelCount = $totalPiePixels - $pixelsAssigned;
            }

            $pixelsPerSegment[$label] = $pixelCount;
            $pixelsAssigned += $pixelCount;
        }

        // Create color assignments
        $colorAssignments = [];
        $i = 0;
        $colorKeys = array_keys($this->colorCodes);
        $availableColors = count($colorKeys) - 1; // Skip 'reset'

        foreach ($segmentLabels as $label) {
            $colorIndex = ($i % $availableColors) + 1; // Skip 'reset'
            $colorAssignments[$label] = $colorKeys[$colorIndex];
            $i++;
        }

        // Sort pixels by angle to distribute segments properly
        usort($piePixelCoordinates, function (array $a, array $b) use ($centerX, $centerY): int {
            // Calculate angles for both pixels (in radians)
            $angleA = atan2($a[1] - $centerY, $a[0] - $centerX);
            $angleB = atan2($b[1] - $centerY, $b[0] - $centerX);

            // Sort by angle
            return $angleA <=> $angleB;
        });

        // Now assign pixels to segments
        $currentPixel = 0;
        foreach ($pixelsPerSegment as $label => $pixelCount) {
            $color = $colorAssignments[$label];

            // Assign this segment's pixels
            for ($i = 0; $i < $pixelCount && $currentPixel < count($piePixelCoordinates); $i++) {
                $coordinates = $piePixelCoordinates[$currentPixel];
                $x = $coordinates[0];
                $y = $coordinates[1];

                $canvas[$y][$x] = $this->colorize($this->fullBlock, $color);
                $currentPixel++;
            }
        }
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
