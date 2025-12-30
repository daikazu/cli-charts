<?php

declare(strict_types=1);

namespace Daikazu\CliCharts;

/**
 * Pie Chart implementation for CLI using Braille characters
 */
class PieChart extends Chart
{
    /**
     * Braille dot positions (each character is 2 columns x 4 rows)
     * Dot numbering:
     *   0  3
     *   1  4
     *   2  5
     *   6  7
     */
    private const int BRAILLE_BASE = 0x2800;

    /**
     * Render a pie chart using Braille characters
     */
    public function render(): string
    {
        $output = $this->drawTitle();

        // Calculate total
        $total = 0.0;
        foreach ($this->data as $value) {
            $total += (float) $value;
        }

        if ($total <= 0) {
            return $output . "Error: Total value must be greater than zero.\n";
        }

        // Calculate percentages and cumulative angles
        /** @var array<int, array{label: string, value: float, percentage: float, startAngle: float, endAngle: float}> $segments */
        $segments = [];
        $startAngle = -M_PI / 2; // Start at top (12 o'clock)

        foreach ($this->data as $label => $value) {
            $numericValue = (float) $value;
            $percentage = ($numericValue / $total) * 100;
            $sweepAngle = ($numericValue / $total) * 2 * M_PI;
            $segments[] = [
                'label'      => (string) $label,
                'value'      => $numericValue,
                'percentage' => $percentage,
                'startAngle' => $startAngle,
                'endAngle'   => $startAngle + $sweepAngle,
            ];
            $startAngle += $sweepAngle;
        }

        // Calculate dimensions
        // Braille: 2 dots wide x 4 dots tall per character
        // Terminal chars are ~2:1 (height:width), so we need to compensate
        $charRadius = (int) min(8, floor($this->width / 8));
        $dotRadius = $charRadius * 4; // Horizontal: double width to compensate for char aspect ratio
        $dotRadiusY = $charRadius * 4; // Vertical dot radius

        // Create dot grid (higher resolution than character grid)
        $dotWidth = $dotRadius * 2;
        $dotHeight = $dotRadiusY * 2;
        $centerX = $dotRadius;
        $centerY = $dotRadiusY;

        // Assign colors to segments
        $colorKeys = array_keys($this->colorCodes);
        /** @var array<int, string> $segmentColors */
        $segmentColors = [];
        foreach ($segments as $i => $segment) {
            $colorIndex = ($i % (count($colorKeys) - 1)) + 1;
            $segmentColors[$i] = $colorKeys[$colorIndex];
        }

        // Render character by character
        $charHeight = (int) ceil($dotHeight / 4);
        $charWidth = (int) ceil($dotWidth / 2);

        for ($charY = 0; $charY < $charHeight; $charY++) {
            // Add left padding
            $output .= str_repeat(' ', max(0, (int) (($this->width - $charWidth * 2) / 4)));

            for ($charX = 0; $charX < $charWidth; $charX++) {
                // For each character cell, check all 8 dot positions
                /** @var array<int, int> $dotsPerSegment */
                $dotsPerSegment = [];

                for ($dotRow = 0; $dotRow < 4; $dotRow++) {
                    for ($dotCol = 0; $dotCol < 2; $dotCol++) {
                        $dotX = $charX * 2 + $dotCol;
                        $dotY = $charY * 4 + $dotRow;

                        // Check if this dot is inside the circle
                        // Normalize to unit circle for proper aspect ratio
                        $normalizedX = ($dotX - $centerX) / $dotRadius;
                        $normalizedY = ($dotY - $centerY) / $dotRadiusY;
                        $distance = sqrt($normalizedX * $normalizedX + $normalizedY * $normalizedY);

                        if ($distance <= 0.95) {
                            // Find which segment this dot belongs to
                            // Use normalized coords for consistent angle calculation
                            $angle = atan2($normalizedY, $normalizedX);

                            foreach ($segments as $i => $segment) {
                                $start = $segment['startAngle'];
                                $end = $segment['endAngle'];

                                // Normalize angle comparison
                                if ($this->angleInRange($angle, $start, $end)) {
                                    if (! isset($dotsPerSegment[$i])) {
                                        $dotsPerSegment[$i] = 0;
                                    }
                                    $dotsPerSegment[$i] |= $this->getDotBit($dotRow, $dotCol);
                                    break;
                                }
                            }
                        }
                    }
                }

                // Render the character
                if ($dotsPerSegment === []) {
                    $output .= ' ';
                } else {
                    // Find the dominant segment (most dots)
                    $maxDots = 0;
                    $dominantSegment = 0;
                    $totalBits = 0;

                    foreach ($dotsPerSegment as $segmentIndex => $bits) {
                        $dotCount = $this->countBits($bits);
                        $totalBits |= $bits;
                        if ($dotCount > $maxDots) {
                            $maxDots = $dotCount;
                            $dominantSegment = $segmentIndex;
                        }
                    }

                    $brailleChar = mb_chr(self::BRAILLE_BASE | $totalBits);
                    $output .= $this->colorize($brailleChar, $segmentColors[$dominantSegment]);
                }
            }
            $output .= "\n";
        }

        $output .= "\n";

        // Draw legend
        $output .= $this->drawLegend($segments, $segmentColors);

        return $output;
    }

    /**
     * Get the braille dot bit for a given row/col position
     * Row 0-3 (top to bottom), Col 0-1 (left to right)
     */
    private function getDotBit(int $row, int $col): int
    {
        // Braille dot pattern bits
        $dotBits = [
            // col 0    col 1
            [0x01,     0x08],   // row 0
            [0x02,     0x10],   // row 1
            [0x04,     0x20],   // row 2
            [0x40,     0x80],   // row 3
        ];

        return $dotBits[$row][$col];
    }

    /**
     * Normalize angle to [0, 2*PI) range
     */
    private function normalizeAngle(float $angle): float
    {
        while ($angle < 0) {
            $angle += 2 * M_PI;
        }
        while ($angle >= 2 * M_PI) {
            $angle -= 2 * M_PI;
        }

        return $angle;
    }

    /**
     * Check if angle is within range (handling wraparound)
     */
    private function angleInRange(float $angle, float $start, float $end): bool
    {
        // Normalize all angles to [0, 2*PI) range
        $angle = $this->normalizeAngle($angle);
        $start = $this->normalizeAngle($start);
        $end = $this->normalizeAngle($end);

        // Handle segment that crosses the 0/2*PI boundary
        if ($end < $start) {
            return $angle >= $start || $angle < $end;
        }

        return $angle >= $start && $angle < $end;
    }

    /**
     * Count number of set bits
     */
    private function countBits(int $n): int
    {
        $count = 0;
        while ($n) {
            $count += $n & 1;
            $n >>= 1;
        }

        return $count;
    }

    /**
     * Draw the legend
     *
     * @param  array<int, array{label: string, value: float, percentage: float, startAngle: float, endAngle: float}>  $segments
     * @param  array<int, string>  $colors
     */
    private function drawLegend(array $segments, array $colors): string
    {
        $output = '';
        $maxLabelLen = 0;

        foreach ($segments as $segment) {
            $len = strlen($segment['label']);
            if ($len > $maxLabelLen) {
                $maxLabelLen = $len;
            }
        }

        $maxLabelLen = min($maxLabelLen, 15);

        foreach ($segments as $i => $segment) {
            $label = str_pad(
                substr($segment['label'], 0, $maxLabelLen),
                $maxLabelLen
            );
            $marker = $this->colorize('●', $colors[$i]);
            $output .= sprintf(
                "  %s %s %5.1f%% (%s)\n",
                $marker,
                $label,
                $segment['percentage'],
                number_format($segment['value'])
            );
        }

        return $output;
    }
}
