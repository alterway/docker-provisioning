<?php
declare(strict_types = 1);

namespace Dashboard\Domain\Tool;

use Dashboard\Domain\Generalisation\{ToolDashboardBuilderInterface, ToolDashboardSummaryInterface};
use Dashboard\Infrastructure\TraitSummary;
use Dashboard\Infrastructure\Parameters;
use Dashboard\Infrastructure\SyntaxHighlighter;
use Dashboard\Infrastructure\View;
use SimpleXMLElement;

/**
 * Class PhpCpd
 *
 * This class manages data for the PHP Copy/Paste Detector Tool logs.
 * @author Nicolas Giraud <nicolas.giraud@pigroupe.fr>
 */
class PhpCpd implements ToolDashboardBuilderInterface, ToolDashboardSummaryInterface
{
    use TraitSummary;

    /** @var string Name of the folder where all logs of this tool are stored. */
    public const LOG_FOLDER_NAME = 'phpcpd';

    /**
     * PhpCpd constructor.
     */
    public function __construct()
    {
        $view = View::getInstance();
        $view->set('_phpcpd', $this);

        $folder = Parameters::get('pathlog') . '/' . static::LOG_FOLDER_NAME;

        if (!is_file($folder . '/php-cpd.xml')) {
            return;
        }

        $view->set('phpcpdData', true);

        $dataXml = trim(file_get_contents($folder . '/php-cpd.xml'));
        if (empty($dataXml)) {
            $view->set('phpcpd_isSuccess', true);
            $view->set('phpcpd_details', []);
            $view->set('phpcpd_duplicated_#', 0);
            $view->set('phpcpd_duplicated_%', '0.00%');
            return;
        }

        $view->set('phpcpd_isSuccess', false);

        $dataXml = simplexml_load_string($dataXml);
        $details = [];
        /** @var SimpleXMLElement $duplication */
        foreach ($dataXml as $duplication) {
            $details[] = [
                'nbLines' => (int)$duplication->attributes()->lines,
                'nbTokens' => (int)$duplication->attributes()->tokens,
                'fileA' => (string)$duplication->file[0]->attributes()->path,
                'lineA' => (int)$duplication->file[0]->attributes()->line,
                'fileB' => (string)$duplication->file[1]->attributes()->path,
                'lineB' => (int)$duplication->file[1]->attributes()->line,
                'snippet' => (string)$duplication->codefragment[0],
                'snippetColor' => SyntaxHighlighter::highlight((string)$duplication->codefragment[0]),
            ];
        }

        $view->set('phpcpd_details', $details);
        $view->set('phpcpd_duplicated_#', count($details));
        $view->set('phpcpd_duplicated_%', trim(file_get_contents($folder . '/percentage-report.txt')));
    }

    /**
     * @inheritDoc
     */
    public function getHTMLMenu(): string
    {
        return View::getInstance()->import('blocks/nav/phpcpd.phtml');
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
        if (!$view->get('phpcpdData', false)) {
            return null;
        }

        return (float)(100 - (float)$view->get('phpcpd_duplicated_%', null));
    }
}
