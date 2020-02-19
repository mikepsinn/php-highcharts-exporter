<?php
/** @noinspection PhpParamsInspection */
namespace MikeSinn\HighchartsExporter\Test;
use MikeSinn\HighchartsExporter\HighchartsExport;
use PHPUnit\Framework\TestCase;
class HighChartExportTest extends TestCase
{
    public function testGeneratePng(){
        $name = "test-chart.png";
        $path = HighchartsExport::getOutputFolder()."/".$name;
        HighchartsExport::deleteOutputImageFile($name);
        $this->assertFileNotExists($path);
        $configPath = HighchartsExport::getConfigPath();
        HighchartsExport::deleteConfig();
        $this->assertFileNotExists($configPath);
        $data = HighchartsExport::getConfigContents('test-basic-line');
        $e = new HighchartsExport($data);
        $data = $e->setOutputFileName($name)->getImageData();
        $this->assertStringContainsString("PNG", $data);
        $this->assertFileExists($path);
    }
    public function testGeneratePngFromString(){
        $name = "test-chart.png";
        $path = HighchartsExport::getOutputFolder()."/".$name;
        HighchartsExport::deleteOutputImageFile($name);
        $configPath = HighchartsExport::getConfigPath();
        HighchartsExport::deleteConfig();
        $this->assertFileNotExists($configPath);
        $e = new HighchartsExport('{"series":[{"data":[29.9,71.5,106.4]}]}');
        $data = $e->setOutputFileName($name)->getImageData();
        $this->assertStringContainsString("PNG", $data);
        $this->assertFileExists($path);
        $html = $e->getHtml();
        $this->assertStringContainsString("<img id=\"chart-image\"", $html);
        $path = $e->getFilePath();
        $this->assertStringContainsString($name, $path);
    }
    public function testThrowsExceptionIfPhantomJsNotExecutable(){
        $name = "test-chart.png";
        $path = HighchartsExport::getOutputFolder()."/".$name;
        HighchartsExport::deleteOutputImageFile($name);
        $this->assertFileNotExists($path);
        HighchartsExport::execute("sudo chmod -x phantomjs");
        $e = new HighchartsExport('{"series":[{"data":[29.9,71.5,106.4]}]}');
        $data = $e->setOutputFileName($name)->getImageData();
        $this->assertFileNotExists($path);
    }
}
