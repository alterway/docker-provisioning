<?php
declare(strict_types = 1);

namespace Dashboard\Infrastructure;

use Dashboard\Domain\Services\Summary;

/**
 * Trait TraitSummary
 *
 * Defines the method "setSummary" with its property which is a global summary for all tools.
 * @author Nicolas Giraud <nicolas.giraud@pigroupe.fr>
 */
trait TraitSummary
{
    /** @var Summary Object that summarize all data of all tools. */
    protected $summary;

    /**
     * Set the Summary object and add the summary for the given tool.
     *
     * @param Summary $summary
     * @return $this
     */
    public function setSummary(Summary $summary)
    {
        $this->summary = $summary->addSummary(static::LOG_FOLDER_NAME, $this->calculateSummary());
        return $this;
    }
}
