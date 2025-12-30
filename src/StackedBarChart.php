<?php

declare(strict_types=1);

namespace Daikazu\CliCharts;

/**
 * Stacked Bar Chart - horizontal bar divided into colored segments
 */
class StackedBarChart extends Chart
{
    /**
     * Block characters for partial fills
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
     * Render a stacked horizontal bar chart
     */
    public function render(): string
    {
        $output = $this->drawTitle();

        $total = array_sum($this->data);
        if ($total <= 0) {
            return $output . "Error: Total value must be greater than zero.\n";
        }

        // Calculate available width for the bar (leave room for brackets)
        $barWidth = $this->width - 2;

        // Calculate segments
        $segments = [];
        $colorKeys = array_keys($this->colorCodes);

        $i = 0;
        foreach ($this->data as $label => $value) {
            $percentage = ($value / $total) * 100;
            $segmentWidth = ($value / $total) * $barWidth;
            $colorIndex = ($i % (count($colorKeys) - 1)) + 1;

            $segments[] = [
                'label'      => $label,
                'value'      => $value,
                'percentage' => $percentage,
                'width'      => $segmentWidth,
                'color'      => $colorKeys[$colorIndex],
            ];
            $i++;
        }

        // Draw the bar
        $output .= '[';
        $accumulatedWidth = 0;

        foreach ($segments as $segment) {
            $fullBlocks = (int) floor($segment['width']);
            $partialBlock = (int) round(($segment['width'] - $fullBlocks) * 8);

            // Draw full blocks
            if ($fullBlocks > 0) {
                $output .= $this->colorize(str_repeat('█', $fullBlocks), $segment['color']);
            }

            // Draw partial block
            if ($partialBlock > 0 && $partialBlock < 8) {
                $output .= $this->colorize(self::BLOCKS[$partialBlock], $segment['color']);
                $accumulatedWidth += 1;
            }

            $accumulatedWidth += $fullBlocks;
        }

        // Fill remaining space if any (due to rounding)
        $remaining = $barWidth - $accumulatedWidth;
        if ($remaining > 0) {
            $output .= str_repeat(' ', $remaining);
        }

        $output .= "]\n";

        // Draw legend below the bar
        $output .= $this->drawLegend($segments);

        return $output;
    }

    /**
     * Draw the legend with labels and percentages
     *
     * @param  array<int, array{label: string|int, value: int|float, percentage: float, width: float, color: string}>  $segments
     */
    private function drawLegend(array $segments): string
    {
        $output = ' ';
        $legendItems = [];

        foreach ($segments as $segment) {
            $label = $this->abbreviateLabel((string) $segment['label']);
            $pct = (int) round($segment['percentage']);
            $legendItems[] = [
                'text'  => "{$label} {$pct}%",
                'color' => $segment['color'],
            ];
        }

        // Calculate spacing
        $totalTextLength = array_sum(array_map(fn (array $item): int => strlen((string) $item['text']), $legendItems));
        $availableSpace = $this->width - 2 - $totalTextLength;
        $gaps = max(1, count($legendItems) - 1);
        $spacing = max(2, (int) floor($availableSpace / $gaps));

        foreach ($legendItems as $i => $item) {
            $output .= $this->colorize($item['text'], $item['color']);
            if ($i < count($legendItems) - 1) {
                $output .= str_repeat(' ', $spacing);
            }
        }

        return $output . "\n";
    }

    /**
     * Abbreviate long labels
     */
    private function abbreviateLabel(string $label): string
    {
        if (strlen($label) <= 8) {
            return $label;
        }

        // For multi-word labels, use initials + last word
        if (str_contains($label, ' ')) {
            $words = explode(' ', $label);
            if (count($words) === 2 && strlen($words[1]) <= 2) {
                return substr($words[0], 0, 3) . ' ' . $words[1];
            }

            return substr($words[0], 0, 2) . substr($words[count($words) - 1], 0, 4);
        }

        // Single long word - abbreviate
        return substr($label, 0, 6);
    }
}
