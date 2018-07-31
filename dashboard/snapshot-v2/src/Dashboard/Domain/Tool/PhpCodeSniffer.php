<?php
declare(strict_types = 1);

namespace Dashboard\Domain\Tool;

use Dashboard\Domain\Generalisation\{ToolDashboardBuilderInterface, ToolDashboardSummaryInterface};
use Dashboard\Infrastructure\TraitSummary;
use Dashboard\Infrastructure\Parameters;
use Dashboard\Infrastructure\View;

/**
 * Class PhpCodeSniffer
 *
 * This class manages data for the PhpCodeSniffer Tool logs.
 * @author Nicolas Giraud <nicolas.giraud@pigroupe.fr>
 */
class PhpCodeSniffer implements ToolDashboardBuilderInterface, ToolDashboardSummaryInterface
{
    use TraitSummary;

    /** @var string Name of the folder where all logs of this tool are stored. */
    public const LOG_FOLDER_NAME = 'phpcs';

    /**
     * PhpCodeSniffer constructor.
     */
    public function __construct()
    {
        $view = View::getInstance();
        $view->set('_phpcs', $this);

        $folder = Parameters::get('pathlog') . '/' . static::LOG_FOLDER_NAME;

        if (!is_file($folder . '/ruleset-cs-file.json')) {
            return;
        }

        // Use json report to get total information.
        $dataJson = json_decode(file_get_contents($folder . '/ruleset-cs-file.json'));

        $view->set('phpcsData', $dataJson);

        $nbErrors = $dataJson->totals->errors;
        $nbWarnings = $dataJson->totals->warnings;

        $view->set('phpcsData_hasErrors', 0 != $nbErrors);
        $view->set('phpcsData_hasWarnings', 0 != $nbWarnings);
        $view->set('phpcsData_isSuccess', 0 === ($nbErrors + $nbWarnings));

        $view->set('phpcsData_nb_total_errors', $nbErrors);
        $view->set('phpcsData_nb_total_warnings', $nbWarnings);
        $view->set('phpcsData_nb_total_analysed', count((array)$dataJson->files));

        $view->set('phpcsData_total_errors_#', number_format($nbErrors));
        $view->set('phpcsData_total_warnings_#', number_format($nbWarnings));
        $view->set('phpcsData_total_analysed_#', number_format(count((array)$dataJson->files)));

        // Use xml report to get detailed error and warning information.
        $dataXml = simplexml_load_file($folder . '/ruleset-cs-file.xml');

        $view->set('phpcsData_version_used', (string)$dataXml->attributes()->version);
        $aPhpCsDetailed = [];
        foreach ($dataXml->file as $fileTag) {
            $fileAttributes = $fileTag->attributes();
            $details = [];

            foreach ($fileTag->children() as $child) {
                /** @var \SimpleXMLElement $child */
                $details[] = [
                    'type' => str_replace('error', 'danger', $child->getName()),
                    'message' => (string)$child,
                    'line' => (int)$child->attributes()->line,
                    'column' => (int)$child->attributes()->column,
                ];
            }

            $aPhpCsDetailed[(string)$fileAttributes->name] = [
                'errors' => (int)$fileAttributes->errors,
                'warnings' => (int)$fileAttributes->warnings,
                'details' => $details
            ];
        }
//        print_r($aPhpCsDetailed);exit;
        $view->set('phpcsData_details', $aPhpCsDetailed);
    }

    /**
     * @inheritDoc
     */
    public function getHTMLMenu(): string
    {
        return View::getInstance()->import('blocks/nav/phpcs.phtml');
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
        $view = View::getInstance();
        if (!$view->get('phpcsData', false)) {
            return null;
        }

        $nbErrors = $view->get('phpcsData_nb_total_errors', 0);
        $nbWarnings = $view->get('phpcsData_nb_total_warnings', 0);
        $nbFiles = $view->get('phpcsData_nb_total_analysed', 1);

        return max(0, 100 - 100 * ($nbErrors + $nbWarnings) / $nbFiles);
    }
}
