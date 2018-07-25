<?php
declare(strict_types = 1);

namespace Dashboard\Domain\Tool;

use Dashboard\Domain\Generalisation\{ToolDashboardBuilderInterface, ToolDashboardSummaryInterface};
use Dashboard\Domain\Services\Summary;
use Dashboard\Infrastructure\TraitSummary;
use Dashboard\Infrastructure\Parameters;
use Dashboard\Infrastructure\View;
use SimpleXMLElement;

/**
 * Class Phpunit
 *
 * This class manages data for the Phpunit Tool logs.
 * @author Nicolas Giraud <nicolas.giraud@pigroupe.fr>
 */
class Phpunit implements ToolDashboardBuilderInterface, ToolDashboardSummaryInterface
{
    use TraitSummary;

    /** @var string Name of the folder where all logs of this tool are stored. */
    public const LOG_FOLDER_NAME = 'phpunit';

    /** @var string File name of the JUnit XML file report on unit tests. */
    protected const PHPUNIT_REPORT_FILE_UNIT = 'phpunit-unit.xml';

    /** @var string File name of the JUnit XML file report on CouchDB functional tests. */
    protected const PHPUNIT_REPORT_FILE_FUNCTIONAL_COUCHDB = 'phpunit-functional-couchdb.xml';

    /** @var string File name of the JUnit XML file report on ODM functional tests. */
    protected const PHPUNIT_REPORT_FILE_FUNCTIONAL_ODM = 'phpunit-functional-odm.xml';

    /** @var string File name of the JUnit XML file report on ORM functional tests. */
    protected const PHPUNIT_REPORT_FILE_FUNCTIONAL_ORM = 'phpunit-functional-orm.xml';

    /**
     * Phpunit constructor.
     */
    public function __construct()
    {
        $view = View::getInstance();

        // Initialize total summary.
        $view->set('phpunitData_total_summary_is_success', true);
        $view->set('phpunitData_total_summary_tests_#', 0);
        $view->set('phpunitData_total_summary_assertions_#', 0);
        $view->set('phpunitData_total_summary_errors_#', 0);
        $view->set('phpunitData_total_summary_failures_#', 0);
        $view->set('phpunitData_total_summary_skipped_#', 0);

        $this->parsePhpUnitUnit()
            ->parsePhpUnitFunctionalOrm()
            ->parsePhpUnitFunctionalOdm()
            ->parsePhpUnitFunctionalCouchDb();

        // Review values of total for formatting.
        $view->set(
            'phpunitData_total_summary_tests_#',
            number_format($view->get('phpunitData_total_summary_tests_#', 0))
        );
        $view->set(
            'phpunitData_total_summary_assertions_#',
            number_format($view->get('phpunitData_total_summary_assertions_#', 0))
        );
        $view->set(
            'phpunitData_total_summary_errors_#',
            number_format($view->get('phpunitData_total_summary_errors_#', 0))
        );
        $view->set(
            'phpunitData_total_summary_failures_#',
            number_format($view->get('phpunitData_total_summary_failures_#', 0))
        );
        $view->set(
            'phpunitData_total_summary_skipped_#',
            number_format($view->get('phpunitData_total_summary_skipped_#', 0))
        );
    }

    /**
     * Prepares the summary and the details to help the view display the data of the PHPUnit tests.
     * @return Phpunit
     */
    protected function parsePhpUnitUnit(): Phpunit
    {
        $folder = Parameters::get('pathlog') . '/' . static::LOG_FOLDER_NAME;
        $jUnitReport = $folder . DIRECTORY_SEPARATOR . static::PHPUNIT_REPORT_FILE_UNIT;

        if (!is_file($jUnitReport)) {
            return $this;
        }

        return $this->parseData('unit', $jUnitReport);
    }

    /**
     * Prepares the summary and the details to help the view display the data of the PHPUnit functional Orm tests.
     * @return Phpunit
     */
    protected function parsePhpUnitFunctionalOrm(): Phpunit
    {
        $folder = Parameters::get('pathlog') . '/' . static::LOG_FOLDER_NAME;
        $jUnitReport = $folder . DIRECTORY_SEPARATOR . static::PHPUNIT_REPORT_FILE_FUNCTIONAL_ORM;

        if (!is_file($jUnitReport)) {
            return $this;
        }

        return $this->parseData('functional_orm', $jUnitReport);
    }


    /**
     * Prepares the summary and the details to help the view display the data of the PHPUnit functional Odm tests.
     * @return Phpunit
     */
    protected function parsePhpUnitFunctionalOdm(): Phpunit
    {
        $folder = Parameters::get('pathlog') . '/' . static::LOG_FOLDER_NAME;
        $jUnitReport = $folder . DIRECTORY_SEPARATOR . static::PHPUNIT_REPORT_FILE_FUNCTIONAL_ODM;

        if (!is_file($jUnitReport)) {
            return $this;
        }

        return $this->parseData('functional_odm', $jUnitReport);
    }


    /**
     * Prepares the summary and the details to help the view display the data of the PHPUnit functional CouchDb tests.
     * @return Phpunit
     */
    protected function parsePhpUnitFunctionalCouchDb(): Phpunit
    {
        $folder = Parameters::get('pathlog') . '/' . static::LOG_FOLDER_NAME;
        $jUnitReport = $folder . DIRECTORY_SEPARATOR . static::PHPUNIT_REPORT_FILE_FUNCTIONAL_COUCHDB;

        if (!is_file($jUnitReport)) {
            return $this;
        }

        return $this->parseData('functional_couchdb', $jUnitReport);
    }

    /**
     * Parses the jUnit reports to extract the values in summary and in details.
     *
     * @param string $type
     * @param string $jUnitReportFile
     * @return Phpunit
     */
    private function parseData(string $type, string $jUnitReportFile): Phpunit
    {
        // Reset each time the _phpunit variable to ensure its existence only if at least one of phpunit group has
        // been run.
        $view = View::getInstance()->set('_phpunit', $this);

        $view->set('phpunitData_' . $type . '_exists', true);

        // Use xml report to get detailed error and warning information.
        $dataXml = simplexml_load_file($jUnitReportFile);
        $summaryAttributes = $dataXml->testsuite[0]->attributes();

        $nbTests = (int)$summaryAttributes->tests;
        $nbAssertions = (int)$summaryAttributes->assertions;
        $nbErrors = (int)$summaryAttributes->errors;
        $nbFailures = (int)$summaryAttributes->failures;
        $nbSkipped = (int)$summaryAttributes->skipped;

        // Add in the total.
        $isSuccess = $view->get('phpunitData_total_summary_is_success', true);
        $view->set('phpunitData_total_summary_is_success', $isSuccess && 0 === ($nbErrors + $nbFailures + $nbSkipped));
        $view->set(
            'phpunitData_total_summary_tests_#',
            $view->get('phpunitData_total_summary_tests_#', 0) + $nbTests
        );
        $view->set(
            'phpunitData_total_summary_assertions_#',
            $view->get('phpunitData_total_summary_assertions_#', 0) + $nbAssertions
        );
        $view->set(
            'phpunitData_total_summary_errors_#',
            $view->get('phpunitData_total_summary_errors_#', 0) + $nbErrors
        );
        $view->set(
            'phpunitData_total_summary_failures_#',
            $view->get('phpunitData_total_summary_failures_#', 0) + $nbFailures
        );
        $view->set(
            'phpunitData_total_summary_skipped_#',
            $view->get('phpunitData_total_summary_skipped_#', 0) + $nbSkipped
        );

        $nbSuccess = $nbTests - ($nbErrors + $nbFailures + $nbSkipped);
        $view->set('phpunitData_' . $type . '_summary_is_success', $nbSuccess === $nbTests);
        $view->set('phpunitData_' . $type . '_summary_tests_#', number_format($nbTests));
        $view->set('phpunitData_' . $type . '_summary_assertions_#', number_format($nbAssertions));
        $view->set('phpunitData_' . $type . '_summary_errors_#', number_format($nbErrors));
        $view->set('phpunitData_' . $type . '_summary_failures_#', number_format($nbFailures));
        $view->set('phpunitData_' . $type . '_summary_skipped_#', number_format($nbSkipped));
        $view->set('phpunitData_' . $type . '_summary_success_#', number_format($nbSuccess));

        $view->set('phpunitData_' . $type . '_summary_tests_#', number_format($nbTests));
        $view->set('phpunitData_' . $type . '_summary_errors_%', 100 * round($nbErrors / $nbTests, 4));
        $view->set('phpunitData_' . $type . '_summary_failures_%', 100 * round($nbFailures / $nbTests, 4));
        $view->set('phpunitData_' . $type . '_summary_skipped_%', 100 * round($nbSkipped / $nbTests, 4));
        $view->set('phpunitData_' . $type . '_summary_success_%', 100 * round($nbSuccess / $nbTests, 4));

        $view->set('phpunitData_' . $type . '_summary_no_success_/', ($nbErrors + $nbFailures + $nbSkipped) / $nbTests);

        // Managed details.
        $aPhpunitDetailed = [];
        foreach ($dataXml->testsuite[0] as $testSuiteTag) {
            /** @var SimpleXMLElement $testSuiteTag */
            $testSuiteTagAttributes = $testSuiteTag->attributes();
            $testCaseDetails = [];

            foreach ($testSuiteTag->children() as $testCaseTag) {
                /** @var SimpleXMLElement $testCaseTag */
                $testCaseTagAttributes = $testCaseTag->attributes();
                /** @var SimpleXMLElement $infoTestTag */
                $infoTestTag = $testCaseTag->children()[0];

                // If information about the test case is empty, it is a success.
                if ($infoTestTag === null) {
                    $testCaseDetails[(string)$testCaseTagAttributes->name] = [
                        'file' => (string)$testCaseTagAttributes->file,
                        'line' => (int)$testCaseTagAttributes->line,
                        'type' => 'success',
                        'message' => null,
                    ];
                } else {
                    $testCaseDetails[(string)$testCaseTagAttributes->name] = [
                        'file' => (string)$testCaseTagAttributes->file,
                        'line' => (int)$testCaseTagAttributes->line,
                        'type' => $infoTestTag->getName(),
                        'message' => (string)$infoTestTag,
                    ];
                }
            }

            $aPhpunitDetailed[(string)$testSuiteTagAttributes->name] = [
                'tests' => (int)$testSuiteTagAttributes->tests,
                'assertions' => (int)$testSuiteTagAttributes->assertions,
                'errors' => (int)$testSuiteTagAttributes->errors,
                'failures' => (int)$testSuiteTagAttributes->failures,
                'skipped' => (int)$testSuiteTagAttributes->skipped,
                'details' => $testCaseDetails,
                // Score is used to rank the worst test cases. High score means lots of errors, failures and skipped.
                'score' => (int)$testSuiteTagAttributes->errors * ($nbTests ** 2)
                    + (int)$testSuiteTagAttributes->failures * $nbTests
                    + (int)$testSuiteTagAttributes->skipped
            ];
        }

        uasort($aPhpunitDetailed, function ($a, $b) {
            return ($a['score'] > $b['score'] ? -1 : ($a['score'] < $b['score'] ? 1 : 0));
        });

        $view->set('phpunitData_' . $type . '_details', $aPhpunitDetailed);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getHTMLMenu(): string
    {
        return View::getInstance()->import('blocks/nav/phpunit.phtml');
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
     * Calculates the summary of the PHPUnit average, for a given type of unit test.
     *
     * @param string $type Type of the unit test.
     * @return null|float The summary value calculated.
     */
    public function calculatePhpUnitSummary(string $type): ?float
    {
        $view = View::getInstance();
        if (!$view->get('phpunitData_' . $type . '_exists', false)) {
            return null;
        }

        return max(0, 100 - 100 * $view->get('phpunitData_' . $type . '_summary_no_success_/'));
    }

    /**
     * Overwrite the setting of the Summary object to manage several summaries to add.
     *
     * @param Summary $summary
     * @return $this
     */
    public function setSummary(Summary $summary)
    {
        $this->summary = $summary
            ->addSummary(static::LOG_FOLDER_NAME . '_unit', $this->calculatePhpUnitSummary('unit'))
            ->addSummary(static::LOG_FOLDER_NAME . '_functional_orm', $this->calculatePhpUnitSummary('functional_orm'))
            ->addSummary(static::LOG_FOLDER_NAME . '_functional_odm', $this->calculatePhpUnitSummary('functional_odm'))
            ->addSummary(
                static::LOG_FOLDER_NAME . '_functional_couchdb',
                $this->calculatePhpUnitSummary('functional_couchdb')
            );

        return $this;
    }
}
