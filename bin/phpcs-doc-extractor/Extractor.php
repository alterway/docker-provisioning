<?php
declare(strict_types = 1);

namespace PHPCSDocExtractor;

/**
 * Class Extractor
 * @package PHPCSDocExtractor
 */
class Extractor
{
    const NO_CONTENT = '*Empty content*';
    const PROJECT_DIR = __DIR__ . '/../../';
    const PHPCS_DIR = self::PROJECT_DIR . 'config/metrics/phpcs/';
    const VENDOR_BASE_DIR = self::PROJECT_DIR . 'vendor/squizlabs/php_codesniffer/CodeSniffer/Standards/';

    public static $baseDir = self::VENDOR_BASE_DIR;

    public static $xml;

    public static function main()
    {
        self::$xml = simplexml_load_file(self::PHPCS_DIR . 'ruleset.xml');

        self::checkBaseDir();

        $outputPath = self::PROJECT_DIR . 'data/generated/result.txt';
        if (!is_dir(dirname($outputPath))) {
            mkdir(dirname($outputPath), 0777, true);
        }
        file_put_contents($outputPath, self::buildDocumentation());
        echo 'SUCCESS: Documentation generated in file "' . realpath($outputPath) . '".' . PHP_EOL;
    }

    public static function checkBaseDir()
    {
        //Check the existence of PHP Code Sniffer's Standards sources.
        if (!is_dir(self::$baseDir)) {
            echo 'ERROR: Standards "' . self::$baseDir . '" not found.' . PHP_EOL;
            echo 'Please run `composer require squizlabs/php_codesniffer` command before running this tool.' . PHP_EOL;
            echo 'Please ensure that some home-made coding standard are referenced well.' . PHP_EOL;
            exit;
        }

        //Define it as the real path as it exists
        self::$baseDir = realpath(self::$baseDir);
    }

    /**
     * @return string
     */
    public static function buildDocumentation()
    {
        $finalString = '';
        foreach (self::$xml->rule as $oChild) {
            $ruleName = (string)$oChild['ref'];

            //If no '.' in the reference rule, we take the whole standard.
            if (false === strpos($ruleName, '.')) {
                $finalString .= self::buildFromRuleStandard($ruleName);
                continue;
            }

            //If we are working with internal PHPCS Standards.
            if (0 !== strpos($ruleName, './')) {
                //Reset the base dir if it was previously changed.
                self::$baseDir = self::VENDOR_BASE_DIR;
                self::checkBaseDir();
                $finalString .= self::buildFromRuleName($ruleName);
                continue;
            }

            //Look at home made sniffs folder.
            if (!is_dir(self::PHPCS_DIR . $ruleName)) {
                continue;
            }

            //Change the base dir as we are not working on the default one but the home-made one.
            self::$baseDir = self::PHPCS_DIR;
            self::checkBaseDir();

            $standardName = trim(str_replace('./', '', $ruleName), '/\\');
            $finalString .= self::buildFromRuleStandard($standardName);
            continue;
        }

        return $finalString;
    }

    /**
     * @param $ruleStandard
     * @return string
     */
    public static function buildFromRuleStandard($ruleStandard)
    {
        $string = '';
        //Build the ruleNames and the documentation for all Sniffs.
        foreach (glob(self::$baseDir . '/' . $ruleStandard . '/Sniffs/*/*Sniff.php') as $sniffName) {
            $ruleName = str_replace(
                [self::$baseDir . '/', '/', 'Sniff.php', '.Sniffs.'],
                ['', '.', '', '.'],
                $sniffName
            );
            $string .= self::buildFromRuleName($ruleName);
        }

        return $string;
    }

    /**
     * @param $ruleName
     * @return string
     */
    public static function buildFromRuleName($ruleName)
    {
        //Replace the first . with the '.Docs.' string to find the good folder-tree in the next steps.
        if (($pos = strpos($ruleName, '.')) === false) {
            return '';
        }

        $description = $good = $bad = self::NO_CONTENT;
        $goodTitle = $badTitle = '';

        $docRuleName = substr_replace($ruleName, '.Docs.', $pos, 1);
        $docFilename = self::$baseDir . '/' . str_replace('.', '/', $docRuleName) . 'Standard.xml';

        if (is_file($docFilename)) {
            $xmlRule = simplexml_load_file($docFilename);

            //Retrieve the description.
            (null === $xmlRule) ?: $description = trim(strip_tags((string)$xmlRule->standard ?? self::NO_CONTENT));

            //Retrieve the good and the bad example.
            if (null !== $xmlRule && isset($xmlRule->code_comparison[0]->code)) {
                $good = trim(strip_tags((string)$xmlRule->code_comparison[0]->code[0] ?? self::NO_CONTENT));
                $goodTitle = trim(strip_tags((string)$xmlRule->code_comparison[0]->code[0]['title'] ?? ''));
                $bad = trim(strip_tags((string)$xmlRule->code_comparison[0]->code[1] ?? self::NO_CONTENT));
                $badTitle = trim(strip_tags((string)$xmlRule->code_comparison[0]->code[1]['title'] ?? ''));
            }
        }

        //Now we have all elements, write the documentation.

        $docString = '----' . PHP_EOL . PHP_EOL;
        $docString .= '**' . $ruleName . '**' . PHP_EOL . PHP_EOL;

        if (!empty($description)) {
            $docString .= ':text-primary:`Description:` ' . $description . PHP_EOL . PHP_EOL;
        }

        if (!empty($good)) {
            $docString .= ':text-success:`Good:` ' . $goodTitle . PHP_EOL . PHP_EOL;
            $docString .= '.. code-block:: php' . PHP_EOL . PHP_EOL;
            $docString .= '    ' . str_replace(PHP_EOL, PHP_EOL . '    ', $good) . PHP_EOL . PHP_EOL;
        }

        if (!empty($bad)) {
            $docString .= ':text-danger:`Bad:` ' . $badTitle . PHP_EOL . PHP_EOL;
            $docString .= '.. code-block:: php' . PHP_EOL . PHP_EOL;
            $docString .= '    ' . str_replace(PHP_EOL, PHP_EOL . '    ', $bad) . PHP_EOL . PHP_EOL;
        }

        return $docString;
    }
}
