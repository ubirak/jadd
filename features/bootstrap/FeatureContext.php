<?php

use mageekguy\atoum\asserter;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Behat\Context\Context;
use Behat\Behat\Context\SnippetAcceptingContext;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\PhpExecutableFinder;
use Rezzza\Jadd\Domain;

class FeatureContext implements Context, SnippetAcceptingContext
{
    private $asserter;

    private $phpBin;

    private $process;

    private $workingDir;

    private $moco;

    public function __construct()
    {
        $this->workingDir = self::workingDir().DIRECTORY_SEPARATOR.md5(microtime() * rand(0, 10000));
        $this->asserter = new asserter\generator();
    }

    /**
     * @BeforeSuite
     * @AfterSuite
     */
    public static function cleanTestFolders()
    {
        Domain\EndpointCollector::reset();
        $dir = self::workingDir();

        if (is_dir($dir)) {
            self::clearDirectory($dir);
        }
    }

    /**
     * @BeforeScenario
     */
    public function prepareScenario()
    {
        mkdir($this->workingDir.'/features/bootstrap', 0777, true);

        $phpFinder = new PhpExecutableFinder();

        if (false === $php = $phpFinder->find()) {
            throw new \RuntimeException('Unable to find the PHP executable.');
        }

        $this->phpBin = $php;
        $this->process = new Process(null);
    }

    /**
     * @AfterScenario
     */
    public function stopMoco()
    {
        if (null !== $this->moco) {
            $this->moco->stop();
        }
    }

    /**
     * @Given /^a file named "(?P<filename>[^"]*)" with:$/
     */
    public function aFileNamedWith($filename, PyStringNode $fileContent)
    {
        $content = strtr((string) $fileContent, array("'''" => '"""'));
        $this->createFile($this->workingDir.'/'.$filename, $content);
    }

    /**
     * @Given I start moco
     */
    public function iStartMoco()
    {
        $fixturesFile = sprintf('%s/features/fixtures.yml', $this->workingDir);
        file_put_contents($fixturesFile, '[]');
        $this->moco = new Process(sprintf('bin/moco start -p 9999 -c %s', $fixturesFile));
        $this->moco->start();
        sleep(2); // Let moco start
    }

    /**
     * @Given the tests have collected the following endpoints:
     */
    public function theTestsHaveCollectedTheFollowingEndpoints(PyStringNode $endpoints)
    {
        file_put_contents(Domain\EndpointCollector::filename(), (string) $endpoints);
    }

    /**
     * @Given my routing file :routingFile looks like:
     */
    public function myRoutingFileLooksLike($routingFile, PyStringNode $string)
    {
        file_put_contents(
            $this->workingDir.DIRECTORY_SEPARATOR.$routingFile,
            (string) $string
        );
    }

    /**
     * @When I generate the documentation from the routing file :routingFile
     */
    public function iGenerateTheDocumentation($routingFile)
    {
        $this->process->setWorkingDirectory($this->workingDir);
        $this->process->setCommandLine(sprintf(
            '%s %s %s %s',
            $this->phpBin,
            escapeshellarg(getcwd().'/bin/jadd'),
            'generate',
            $routingFile.' '.$this->workingDir.'/doc.md'
        ));
        $this->process->start();
        $this->process->wait();
    }

    /**
     * @When /^I run behat "(?P<arguments>[^"]*)"$/
     */
    public function iRunBehat($arguments)
    {
        $argumentsString = strtr($arguments, array('\'' => '"'));
        $this->process->setWorkingDirectory($this->workingDir);
        $this->process->setCommandLine(sprintf(
            '%s %s %s %s',
            $this->phpBin,
            escapeshellarg(BEHAT_BIN_PATH),
            $argumentsString,
            strtr('--no-colors', array('\'' => '"', '"' => '\"'))
        ));
        $this->process->start();
        $this->process->wait();
    }

    /**
     * @Then the documentation should be like
     */
    public function theDocumentationShouldBeLike(PyStringNode $documentation)
    {
        $content = str_replace("\r\n", "\n", trim(file_get_contents($this->workingDir.'/doc.md')));

        $this->asserter
            ->string($content)
            ->isEqualTo(trim((string) $documentation))
        ;
    }

    /**
     * @Then the tests should have collected the following endpoints:
     */
    public function theTestsShouldHaveCollectedTheFollowingEndpoints(PyStringNode $endpoints)
    {
        $content = file_get_contents(Domain\EndpointCollector::filename());

        $this->asserter
            ->string(trim($content))
            ->isEqualTo(trim((string) $endpoints))
        ;
    }

    /**
     * @Then /^it should (fail|pass) with:$/
     */
    public function itShouldTerminateWithStatusAndContent($exitStatus, PyStringNode $string)
    {
        if ('fail' === $exitStatus) {
            $this->asserter->integer($this->getExitCode())->isEqualTo(1);
        } elseif ('pass' === $exitStatus) {
            $this->asserter->integer($this->getExitCode())->isEqualTo(0);
        } else {
            throw new \LogicException('Accepts only "fail" or "pass"');
        }
        $this->asserter->phpString($this->getOutput())->contains((string) $string);
    }

    /**
     * @Then print output
     */
    public function printOutput()
    {
        echo $this->getOutput();
    }

    private function createFile($filename, $content)
    {
        $path = dirname($filename);

        if (!is_dir($path)) {
            mkdir($path, 0777, true);
        }

        file_put_contents($filename, $content);
    }

    public static function workingDir()
    {
        return sys_get_temp_dir().DIRECTORY_SEPARATOR.'json-api-behat';
    }

    private static function clearDirectory($path)
    {
        $files = scandir($path);
        array_shift($files);
        array_shift($files);

        foreach ($files as $file) {
            $file = $path.DIRECTORY_SEPARATOR.$file;
            if (is_dir($file)) {
                self::clearDirectory($file);
            } else {
                unlink($file);
            }
        }

        rmdir($path);
    }

    private function getOutput()
    {
        $output = $this->process->getErrorOutput().$this->process->getOutput();
        // Normalize the line endings in the output
        if ("\n" !== PHP_EOL) {
            $output = str_replace(PHP_EOL, "\n", $output);
        }

        return trim(preg_replace('/ +$/m', '', $output));
    }

    private function getExitCode()
    {
        return $this->process->getExitCode();
    }
}
