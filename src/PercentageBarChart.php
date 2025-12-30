<?php

declare(strict_types=1);

namespace Daikazu\CliCharts;

/**
 * Percentage Bar Chart - vertical list with horizontal percentage bars
 */
class PercentageBarChart extends Chart
{
    /**
     * Block characters for partial fills (eighths)
     */
    private const array BLOCKS = [
        0 => ' ',
        1 => '▏',
        2 => '▎',
        3 => '▍',
        4 => '▌',
        5 => '▋',
        6 => '▊',
        7 => '▉',
        8 => '█',
    ];

    /**
     * Render percentage bar chart
     */
    public function render(): string
    {
        $output = $this->drawTitle();

        $total = array_sum($this->data);
        if ($total <= 0) {
            return $output . "Error: Total value must be greater than zero.\n";
        }

        // Calculate label width
        $maxLabelLength = 0;
        foreach (array_keys($this->data) as $label) {
            $maxLabelLength = max($maxLabelLength, strlen((string) $label));
        }
        $labelWidth = min($maxLabelLength, 12);

        // Calculate bar width (leave room for label, spaces, and percentage)
        // Format: "Label        ████████████████ 45.2%"
        $barWidth = $this->width - $labelWidth - 8; // 8 = spaces + " XX.X%"

        // Get color keys
        $colorKeys = array_keys($this->colorCodes);

        $i = 0;
        foreach ($this->data as $label => $value) {
            $percentage = ($value / $total) * 100;
            $colorIndex = ($i % (count($colorKeys) - 1)) + 1;
            $color = $colorKeys[$colorIndex];

            // Format label
            $labelStr = str_pad(
                substr((string) $label, 0, $labelWidth),
                $labelWidth
            );

            // Calculate bar length
            $barLength = ($percentage / 100) * $barWidth;
            $fullBlocks = (int) floor($barLength);
            $partialBlock = (int) round(($barLength - $fullBlocks) * 8);

            // Build the bar
            $bar = '';
            if ($fullBlocks > 0) {
                $bar .= str_repeat('█', $fullBlocks);
            }
            if ($partialBlock > 0 && $partialBlock < 8) {
                $bar .= self::BLOCKS[$partialBlock];
                $fullBlocks++; // Count partial as one character
            }

            // Pad the bar area
            $barPadding = $barWidth - $fullBlocks;
            if ($barPadding > 0) {
                $bar .= str_repeat(' ', $barPadding);
            }

            // Format percentage
            $pctStr = sprintf('%5.1f%%', $percentage);

            // Output the line
            $output .= $this->colorize($labelStr, $color);
            $output .= ' ';
            $output .= $this->colorize($bar, $color);
            $output .= ' ' . $pctStr . "\n";

            $i++;
        }

        return $output;
    }
}
