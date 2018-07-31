<?php
declare(strict_types = 1);

namespace Dashboard\Domain\Services;

/**
 * Class Summary
 *
 * This class manages orders of summary calculation for all tools built.
 * @author Nicolas Giraud <nicolas.giraud@pigroupe.fr>
 */
class Summary
{
    /**
     * @var float[] List of summary values. Used to manage a global project summary.
     */
    protected $summaryList = [];

    /**
     * @var float Global note value of the whole current analytics.
     */
    protected $globalNote = null;

    /**
     * Add a summary value to the list to be able to calculate a global project value.
     *
     * @param string $toolName Name of the tool the value is owned.
     * @param null|float $value Value of the summary of a tool.
     * @return Summary
     */
    public function addSummary(string $toolName, ?float $value): Summary
    {
        if (null !== $value) {
            $this->summaryList[$toolName] = $value;
        }
        return $this;
    }

    /**
     * Calculates the global project note using all summaries in list.
     *
     * @return Summary
     */
    public function calculateGlobal(): Summary
    {
        if (empty($this->summaryList)) {
            return $this;
        }

        $this->globalNote = array_sum($this->summaryList) / count($this->summaryList);
        return $this;
    }

    /**
     * Exports all summaries in a JSON formatted file.
     *
     * @return string The JSON encoded exported.
     */
    public function export(): string
    {
        return json_encode(['global' => $this->globalNote, 'tools' => $this->summaryList]);
    }
}
