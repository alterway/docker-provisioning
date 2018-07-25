<?php
declare(strict_types = 1);

namespace Dashboard\Domain\Tool;

use Dashboard\Domain\Generalisation\{ToolDashboardBuilderInterface, ToolDashboardSummaryInterface};
use Dashboard\Domain\Services\Summary;
use Dashboard\Infrastructure\TraitSummary;
use Dashboard\Infrastructure\Parameters;
use Dashboard\Infrastructure\View;

/**
 * Class PhpMetrics
 *
 * This class manages data for the PhpMetrics Tool logs.
 * @author Nicolas Giraud <nicolas.giraud@pigroupe.fr>
 */
class PhpMetrics implements ToolDashboardBuilderInterface, ToolDashboardSummaryInterface
{
    use TraitSummary;

    /** @var string Name of the folder where all logs of this tool are stored. */
    public const LOG_FOLDER_NAME = 'phpmetrics';

    /**
     * PhpMetrics constructor.
     */
    public function __construct()
    {
        $view = View::getInstance();
        $view->set('_phpmetrics', $this);

        $folder = Parameters::get('pathlog') . '/' . static::LOG_FOLDER_NAME;

        if (!is_file($folder . '/js/latest.json')) {
            return;
        }

        $data = json_decode(file_get_contents($folder . '/js/latest.json'));

        $view->set('phpMetricsData', $data);

        $view->set('phpMetricsData_ccn', $data->avg->ccn);

        $view->set('phpMetricsData_lcom', $data->avg->lcom);
        $view->set('phpMetricsData_lcom_#', number_format($data->avg->lcom, 2));
        $lcomStatus = ($data->avg->lcom <= 1 ? 'success' : ($data->avg->lcom <= 2 ? 'warning' : 'danger'));
        $view->set('phpMetricsData_lcom_status', $lcomStatus);

        $view->set('phpMetricsData_mi', $data->avg->mi);
        $view->set('phpMetricsData_mi_#', number_format($data->avg->mi, 2));
        $miStatus = ($data->avg->mi >= 85 ? 'success' : ($data->avg->mi >= 69 ? 'warning' : 'danger'));
        $view->set('phpMetricsData_mi_status', $miStatus);

        $view->set('phpMetricsData_bugs', $data->avg->bugs);
        $view->set('phpMetricsData_bugs_#', number_format($data->avg->bugs, 2));
        $bugsStatus = ($data->avg->bugs <= 0.7 ? 'success' : ($data->avg->bugs <= 2 ? 'warning' : 'danger'));
        $view->set('phpMetricsData_bugs_status', $bugsStatus);

        $view->set('phpMetricsData_volume', $data->avg->volume);
        $view->set('phpMetricsData_volume_#', number_format($data->avg->volume, 2));
        $volumeStatus = ($data->avg->volume <= 100 ? 'success' : ($data->avg->volume <= 8000 ? 'warning' : 'danger'));
        $view->set('phpMetricsData_volume_status', $volumeStatus);

        $totalLineOfCode = $data->sum->loc;
        $logicalLineOfCode = $data->sum->lloc;
        $commentedLineOfCode = $data->sum->cloc;

        $view->set('phpMetricsData_loc', $totalLineOfCode);
        $view->set('phpMetricsData_loc_#', number_format($totalLineOfCode));
        $view->set('phpMetricsData_lloc', $logicalLineOfCode);
        $view->set('phpMetricsData_lloc_#', number_format($logicalLineOfCode));
        $view->set('phpMetricsData_lloc_%', 100 * round($logicalLineOfCode / $totalLineOfCode, 2));
        $view->set('phpMetricsData_cloc', $commentedLineOfCode);
        $view->set('phpMetricsData_cloc_#', number_format($commentedLineOfCode));
        $view->set('phpMetricsData_cloc_%', 100 * round($commentedLineOfCode / $totalLineOfCode, 2));

        $view->set('phpMetricsData_nbMethods_#', number_format($data->sum->nbMethods));
        $view->set('phpMetricsData_nbClasses_#', number_format($data->sum->nbClasses));
        $view->set('phpMetricsData_nbInterfaces_#', number_format($data->sum->nbInterfaces));
    }

    /**
     * @inheritDoc
     */
    public function getHTMLMenu(): string
    {
        return View::getInstance()->import('blocks/nav/phpmetrics.phtml');
    }

    /**
     * @inheritDoc
     */
    public function getHTMLTab(): string
    {
        return '';
    }

    /**
     * @inheritDoc
     */
    public function getHTMLSummary(): string
    {
        return '';
    }

    /**
     * @inheritDoc
     */
    public function calculateSummary(): ?float
    {
        // Overwritten by multiple summaries in self::setSummary().
        return null;
    }

    /**
     * Overwrite the setting of the Summary object to manage several summaries to add.
     *
     * @param Summary $summary
     * @return $this
     */
    public function setSummary(Summary $summary)
    {
        $this->summary = $summary->addSummary(static::LOG_FOLDER_NAME . '_lcom', $this->calculateLcomSummary())
            ->addSummary(static::LOG_FOLDER_NAME . '_cyclomaticComplexity', $this->calculateCCSummary())
            ->addSummary(static::LOG_FOLDER_NAME . '_maintainabilityIndex', $this->calculateMISummary())
            ->addSummary(static::LOG_FOLDER_NAME . '_bugs', $this->calculateBugsSummary())
            ->addSummary(static::LOG_FOLDER_NAME . '_volume', $this->calculateVolumeSummary());

        return $this;
    }

    /**
     * Calculates the summary of the LCOM4 average.
     *
     * @return float|null
     */
    private function calculateLcomSummary(): ?float
    {
        $view = View::getInstance();
        if (!$view->get('phpMetricsData', false)) {
            return null;
        }

        return min(100, 100 - ($view->get('phpMetricsData_lcom') - 1) * 100);
    }

    /**
     * Calculates the summary of the cyclomatic complexity average.
     *
     * @return float|null
     */
    private function calculateCCSummary(): ?float
    {
        $view = View::getInstance();
        if (!$view->get('phpMetricsData', false)) {
            return null;
        }

        return max(0, 100 - ($view->get('phpMetricsData_ccn') - 1) * 100);
    }

    /**
     * Calculates the summary of the maintainability index average.
     *
     * @return float|null
     */
    private function calculateMISummary(): ?float
    {
        $view = View::getInstance();
        if (!$view->get('phpMetricsData', false)) {
            return null;
        }

        return min(100, 100 * $view->get('phpMetricsData_mi') / 85);
    }

    /**
     * Calculates the summary of the bugs probability average.
     *
     * @return float|null
     */
    private function calculateBugsSummary(): ?float
    {
        $view = View::getInstance();
        if (!$view->get('phpMetricsData', false)) {
            return null;
        }

        return max(0, min(100, 100 - ($view->get('phpMetricsData_volume') - 70) / 1.3));
    }

    /**
     * Calculates the summary of the Halstead volume average.
     *
     * @return float|null
     */
    private function calculateVolumeSummary(): ?float
    {
        $view = View::getInstance();
        if (!$view->get('phpMetricsData', false)) {
            return null;
        }

        return max(0, min(100, 100 - ($view->get('phpMetricsData_volume') - 100) / 79));
    }
}
