<?php

declare(strict_types=1);

namespace Daikazu\CliCharts;

/**
 * Line Chart implementation using Braille characters for smooth lines
 */
class LineChart extends Chart
{
    private const int BRAILLE_BASE = 0x2800;

    /**
     * Render a line chart with smooth Braille-based lines
     */
    public function render(): string
    {
        $output = $this->drawTitle();

        if ($this->data === []) {
            return $output;
        }

        // Prepare data - convert to properly typed arrays
        /** @var array<int, float> $points */
        $points = [];
        /** @var array<int, string> $labels */
        $labels = [];

        foreach ($this->data as $label => $value) {
            $labels[] = (string) $label;
            $points[] = (float) $value;
        }

        $numPoints = count($points);

        if ($numPoints < 2) {
            return $output . "Need at least 2 data points for a line chart.\n";
        }

        // Get color options
        $lineColor = isset($this->options['lineColor']) && is_string($this->options['lineColor'])
            ? $this->options['lineColor']
            : 'cyan';
        $pointColor = isset($this->options['pointColor']) && is_string($this->options['pointColor'])
            ? $this->options['pointColor']
            : null;

        $maxValue = $this->getMaxValue();
        $minValue = $this->getMinValue();
        $valueRange = $maxValue - $minValue;

        // Chart dimensions
        // Leave room for Y-axis labels (6 chars) and some padding
        $yAxisWidth = 6;
        $chartWidth = $this->width - $yAxisWidth - 2;
        $chartHeight = $this->height - 3; // Leave room for X-axis labels

        // Braille resolution: 2 dots per char width, 4 dots per char height
        $dotWidth = $chartWidth * 2;
        $dotHeight = $chartHeight * 4;

        // Create dot grid
        /** @var array<int, array<int, bool>> $grid */
        $grid = [];
        for ($y = 0; $y < $dotHeight; $y++) {
            $grid[$y] = array_fill(0, $dotWidth, false);
        }

        // Track which character cells contain data points (for coloring)
        /** @var array<string, bool> $pointCells */
        $pointCells = [];

        // Calculate point positions in dot coordinates
        /** @var array<int, array{x: int, y: int, value: float}> $pointPositions */
        $pointPositions = [];
        for ($i = 0; $i < $numPoints; $i++) {
            // X position: spread points evenly across width
            $x = (int) round(($i / ($numPoints - 1)) * ($dotWidth - 1));

            // Y position: scale value to height (inverted because Y grows downward)
            if ($valueRange == 0) {
                $y = (int) ($dotHeight / 2);
            } else {
                $normalizedValue = ($points[$i] - $minValue) / $valueRange;
                $y = (int) round(($dotHeight - 1) * (1 - $normalizedValue));
            }

            $pointPositions[] = ['x' => $x, 'y' => $y, 'value' => $points[$i]];

            // Track the character cell this point falls into
            $charX = (int) floor($x / 2);
            $charY = (int) floor($y / 4);
            $pointCells["{$charX},{$charY}"] = true;
        }

        // Draw lines between consecutive points using Bresenham's algorithm
        for ($i = 0; $i < $numPoints - 1; $i++) {
            $this->drawLine(
                $grid,
                $pointPositions[$i]['x'],
                $pointPositions[$i]['y'],
                $pointPositions[$i + 1]['x'],
                $pointPositions[$i + 1]['y']
            );
        }

        // Mark the actual data points (make them more visible)
        foreach ($pointPositions as $pos) {
            // Draw a small cross or dot at each point
            $grid[$pos['y']][$pos['x']] = true;
            if ($pos['y'] > 0) {
                $grid[$pos['y'] - 1][$pos['x']] = true;
            }
            if ($pos['y'] < $dotHeight - 1) {
                $grid[$pos['y'] + 1][$pos['x']] = true;
            }
            if ($pos['x'] > 0) {
                $grid[$pos['y']][$pos['x'] - 1] = true;
            }
            if ($pos['x'] < $dotWidth - 1) {
                $grid[$pos['y']][$pos['x'] + 1] = true;
            }
        }

        // Calculate Y-axis labels
        $yLabels = $this->calculateYLabels($minValue, $maxValue, $chartHeight);

        // Render the grid as Braille characters
        for ($charY = 0; $charY < $chartHeight; $charY++) {
            // Y-axis label
            $yValue = $this->getYLabelForRow($charY, $chartHeight, $yLabels);
            if ($yValue !== null) {
                $output .= str_pad((string) $yValue, $yAxisWidth - 1, ' ', STR_PAD_LEFT) . ' │';
            } else {
                $output .= str_repeat(' ', $yAxisWidth - 1) . ' │';
            }

            // Chart content
            for ($charX = 0; $charX < $chartWidth; $charX++) {
                $brailleChar = $this->getBrailleChar($grid, $charX, $charY);
                if ($brailleChar === ' ') {
                    $output .= ' ';
                } else {
                    // Use point color if this cell contains a data point
                    $isPointCell = isset($pointCells["{$charX},{$charY}"]);
                    $color = ($isPointCell && $pointColor !== null) ? $pointColor : $lineColor;
                    $output .= $this->colorize($brailleChar, $color);
                }
            }
            $output .= "\n";
        }

        // X-axis line
        $output .= str_repeat(' ', $yAxisWidth - 1) . ' └' . str_repeat('─', $chartWidth) . "\n";

        // X-axis labels
        $output .= $this->drawXAxisLabels($labels, $chartWidth, $yAxisWidth);

        return $output;
    }

    /**
     * Find the minimum value in the data set
     */
    protected function getMinValue(): float
    {
        $min = PHP_FLOAT_MAX;
        foreach ($this->data as $value) {
            $floatValue = (float) $value;
            if ($floatValue < $min) {
                $min = $floatValue;
            }
        }

        // Start from 0 if min is positive and not too far from 0
        if ($min > 0 && $min < 0.3 * $this->getMaxValue()) {
            return 0.0;
        }

        return $min;
    }

    /**
     * Draw a line between two points using Bresenham's algorithm
     *
     * @param  array<int, array<int, bool>>  $grid
     */
    private function drawLine(array &$grid, int $x0, int $y0, int $x1, int $y1): void
    {
        $dx = abs($x1 - $x0);
        $dy = abs($y1 - $y0);
        $sx = $x0 < $x1 ? 1 : -1;
        $sy = $y0 < $y1 ? 1 : -1;
        $err = $dx - $dy;

        $maxY = count($grid) - 1;
        $maxX = isset($grid[0]) ? count($grid[0]) - 1 : 0;

        while (true) {
            if ($y0 >= 0 && $y0 <= $maxY && $x0 >= 0 && $x0 <= $maxX) {
                $grid[$y0][$x0] = true;
            }

            if ($x0 === $x1 && $y0 === $y1) {
                break;
            }

            $e2 = 2 * $err;
            if ($e2 > -$dy) {
                $err -= $dy;
                $x0 += $sx;
            }
            if ($e2 < $dx) {
                $err += $dx;
                $y0 += $sy;
            }
        }
    }

    /**
     * Convert a 2x4 cell of the dot grid to a Braille character
     *
     * @param  array<int, array<int, bool>>  $grid
     */
    private function getBrailleChar(array $grid, int $charX, int $charY): string
    {
        $dotX = $charX * 2;
        $dotY = $charY * 4;

        $bits = 0;

        // Braille dot pattern:
        // 0 3
        // 1 4
        // 2 5
        // 6 7
        $dotMap = [
            [0, 0, 0x01], [1, 0, 0x02], [2, 0, 0x04], [3, 0, 0x40],
            [0, 1, 0x08], [1, 1, 0x10], [2, 1, 0x20], [3, 1, 0x80],
        ];

        foreach ($dotMap as [$row, $col, $bit]) {
            $y = $dotY + $row;
            $x = $dotX + $col;

            if (isset($grid[$y][$x]) && $grid[$y][$x] === true) {
                $bits |= $bit;
            }
        }

        if ($bits === 0) {
            return ' ';
        }

        return mb_chr(self::BRAILLE_BASE | $bits);
    }

    /**
     * Calculate Y-axis labels
     *
     * @return array<int, int>
     */
    private function calculateYLabels(float $min, float $max, int $height): array
    {
        /** @var array<int, int> $labels */
        $labels = [];

        if ($max === $min) {
            return [(int) round($max)];
        }

        // Show 3-5 labels depending on height
        $numLabels = min(5, max(3, (int) floor($height / 3)));

        for ($i = 0; $i < $numLabels; $i++) {
            $value = $max - ($i / ($numLabels - 1)) * ($max - $min);
            $labels[] = (int) round($value);
        }

        return $labels;
    }

    /**
     * Get the Y label for a specific row, if any
     *
     * @param  array<int, int>  $yLabels
     */
    private function getYLabelForRow(int $row, int $totalRows, array $yLabels): ?int
    {
        $numLabels = count($yLabels);
        if ($numLabels === 0) {
            return null;
        }

        // Calculate which label positions map to which rows
        for ($i = 0; $i < $numLabels; $i++) {
            $labelRow = $numLabels === 1 ? (int) ($totalRows / 2) : (int) round($i / ($numLabels - 1) * ($totalRows - 1));
            if ($labelRow === $row) {
                return $yLabels[$i];
            }
        }

        return null;
    }

    /**
     * Draw X-axis labels
     *
     * @param  array<int, string>  $labels
     */
    private function drawXAxisLabels(array $labels, int $chartWidth, int $yAxisWidth): string
    {
        $output = str_repeat(' ', $yAxisWidth);
        $numLabels = count($labels);

        if ($numLabels < 2) {
            return $output . "\n";
        }

        // Calculate positions for each label
        /** @var array<int, int> $positions */
        $positions = [];
        for ($i = 0; $i < $numLabels; $i++) {
            $positions[$i] = (int) round(($i / ($numLabels - 1)) * ($chartWidth - 1));
        }

        // Build label string
        $labelLine = str_repeat(' ', $chartWidth);
        foreach ($labels as $i => $label) {
            $abbrev = $this->abbreviateLabel($label);
            $pos = $positions[$i];

            // Center the label around its position
            $startPos = max(0, $pos - (int) floor(strlen($abbrev) / 2));

            // Place label characters
            for ($j = 0; $j < strlen($abbrev) && $startPos + $j < $chartWidth; $j++) {
                $labelLine[$startPos + $j] = $abbrev[$j];
            }
        }

        return $output . $labelLine . "\n";
    }

    /**
     * Abbreviate a label for X-axis display
     */
    private function abbreviateLabel(string $label): string
    {
        if (strlen($label) <= 3) {
            return $label;
        }

        // For multi-word labels, use initials
        if (str_contains($label, ' ')) {
            $words = explode(' ', $label);
            $abbrev = '';
            foreach ($words as $word) {
                $abbrev .= mb_substr($word, 0, 1);
            }

            return $abbrev;
        }

        // Single word: first 3 chars
        return substr($label, 0, 3);
    }
}
