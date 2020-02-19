<?php /** @noinspection PhpUnused */
namespace MikeSinn\HighchartsExporter;

use LogicException;
use RuntimeException;

class HighchartsExport
{
    const PNG = "png";
    const SVG = "svg";
    const JPG = "jpg";
    public $highchartConfig;
    public $imageType = self::PNG;
    public $scale;
    public $width;
    public $outputPath;
    public $useHighStock = false;
    /**
     * @var string
     */
    private $commandOutput;
    /**
     * @var false|string
     */
    private $imageData;
    /**
     * @var string
     */
    private $exportCommand;
    private $outputFileName;
    /**
     * @param $config
     */
    public function __construct($config)
    {
        $this->setHighchartConfig($config);
    }
    public static function execute($cmd)
    {
        $packagePath = self::getPackagePath();
        $cmd = "cd ".$packagePath." && ".$cmd; // Seems to have problems when putting variables within quotes sometimes
        $output = shell_exec($cmd); // Not sure what output ": not found" means but it seems to work anyway
        return $output;
    }
    /**
     * @return mixed
     */
    public function getImageType()
    {
        return $this->imageType;
    }
    /**
     * @param mixed $imageType
     * @return HighchartsExport
     */
    public function setImageType($imageType)
    {
        $this->imageType = $imageType;
        return $this;
    }
    /**
     * @param float $scale
     * @return HighchartsExport
     * Set the zoomFactor of the page rendered by PhantomJs. For example, if the chart.width option in the
     *     chart configuration is set to 600 and the scale is set to 2, the output raster image will have a pixel width
     *     of 1200. So this is a convenient way of increasing the resolution without decreasing the font size and line
     *     widths in the chart. This is ignored if the -width parameter is set.
     */
    public function setScale($scale)
    {
        $this->scale = $scale;
        return $this;
    }
    /**
     * @param float $width
     * @return HighchartsExport
     *  Set the exact pixel width of the exported image or pdf. This overrides the -scale parameter.
     */
    public function setWidth($width)
    {
        $this->width = $width;
        return $this;
    }
    /**
     * @return mixed
     */
    public function getHighchartConfig()
    {
        if (!$this->highchartConfig) {
            throw new LogicException("Please set highchartConfig");
        }
        return $this->highchartConfig;
    }
    /**
     * @param mixed $highchartConfig
     * @return HighchartsExport
     */
    public function setHighchartConfig($highchartConfig)
    {
        if (is_string($highchartConfig)) {
            $highchartConfig = json_decode($highchartConfig);
            $error = json_last_error_msg();
            if ($error && $error !== "No error") {
                throw new LogicException($error);
            }
        }
        $this->highchartConfig = $highchartConfig;
        return $this;
    }
    /**
     * @return string
     */
    public function getOutputFilePath()
    {
        $name = $this->getOutputFileName();
        if (!$this->outputPath) {
            return $this->outputPath = HighchartsExport::getOutputFolder()."/$name";
        }
        return $this->outputPath;
    }
    public static function getOutputFolder()
    {
        return HighchartsExport::getPackagePath()."/output";
    }
    /**
     * @param string $outputPath
     */
    public function setOutputPath($outputPath)
    {
        $this->outputPath = $outputPath;
    }
    /**
     * @param string $name
     * @return HighchartsExport
     */
    public function setOutputFileName($name)
    {
        $this->outputFileName = $name;
        return $this;
    }
    /**
     * @return string
     */
    public function getImageData()
    {
        if ($this->imageData) {
            return $this->imageData;
        }
        $this->export();
        return $this->imageData;
    }
    public function getFilePath()
    {
        $this->getImageData();
        return $this->getOutputFilePath();
    }
    /**
     * @param string $alt
     * @param string $title
     * @param string $elementId
     * @return string
     */
    public function getHtml($alt = "Chart", $title = "Chart", $elementId = "chart")
    {
        $data = $this->getImageData();
        return self::imageDataToHtml($this->getImageType(), $data, $alt, $title, $elementId);
    }
    /**
     * @return string
     */
    public function getConstructor()
    {
        $config = $this->getHighchartConfig();
        $useHighStock = isset($config->useHighStocks) ? $config->useHighStocks : $this->useHighStock;
        if ($useHighStock) {
            $constr = "StockChart";
        } else {
            $constr = "Chart";
        }
        return $constr;
    }
    private static function getPackagePath()
    {
        return realpath(__DIR__.'/..');
    }
    private function writeConfig()
    {
        $configPath = HighchartsExport::getConfigPath();
        $config = $this->getHighchartConfig();
        self::writeByFilePath($configPath, json_encode($config, JSON_PRETTY_PRINT));
    }
    /**
     * @param string $name
     * @return string
     */
    public static function getConfigPath($name = "config")
    {
        return HighchartsExport::getPackagePath()."/configs/$name.json";
    }
    /**
     * @param string $name
     * @return string
     */
    public static function getConfigContents($name = "config")
    {
        return json_decode(file_get_contents(self::getConfigPath($name)));
    }
    /**
     * @param $fileName
     */
    public static function deleteOutputImageFile($fileName)
    {
        $filePath = self::getOutputFolder()."/$fileName";
        try {
            unlink($filePath);
        } catch (\Throwable $e) {
        }
    }
    /**
     * @param string $name
     */
    public static function deleteConfig($name = "config")
    {
        $filePath = self::getConfigPath($name);
        try {
            unlink($filePath);
        } catch (\Throwable $e) {
        }
    }
    /**
     * @return string
     */
    private function getScaleWidthFlags()
    {
        $flags = "";
        if ($this->scale) {
            $flags .= " -scale $this->scale ";
        }
        if ($this->width) {
            $flags .= " -width $this->width ";
        }
        return $flags;
    }
    /**
     * @param string $filePath
     * @param $content
     * @return string
     */
    public function writeByFilePath($filePath, $content)
    {
        if (!is_string($content)) {
            $content = json_encode($content);
        }
        if (trim($content) === "") {
            throw new LogicException("$filePath is empty!");
        }
        $directory = dirname($filePath);
        self::createDirectoryIfNecessary($directory);
        if (self::existsAndHasNotChanged($filePath, $content)) {
            return false;
        }
        chmod($directory, 0777);
        if (!file_exists($filePath)) {
            if (!touch($filePath)) {
                throw new LogicException("No permission for $filePath");
            }
            chmod($filePath, 0777);
        }
        $fp = fopen($filePath, 'wb');
        fwrite($fp, $content);
        fclose($fp);
        return $filePath;
    }
    /**
     * @param string $directory
     */
    public static function createDirectoryIfNecessary($directory)
    {
        if (!file_exists($directory) && !mkdir($directory, 0777, true) && !is_dir($directory)) {
            throw new RuntimeException(sprintf('Directory "%s" was not created', $directory));
        }
    }
    /**
     * @param string $filePath
     * @param $content
     * @return bool
     */
    protected static function existsAndHasNotChanged($filePath, $content)
    {
        $existsAndHasNotChanged = false;
        try {
            $existing = file_get_contents($filePath);
        } catch (\Throwable $e) {
            return false;
        }
        if ($existing === $content) {
            $existsAndHasNotChanged = true;
        }
        return $existsAndHasNotChanged;
    }
    /**
     * @return string
     */
    private function getOutputFileName()
    {
        $name = $this->outputFileName;
        if (!$name) {
            $name = "chart.".$this->getImageType();
        }
        return $name;
    }
    private function export()
    {
        $constr = $this->getConstructor();
        $configPath = HighchartsExport::getConfigPath();
        $outputPath = $this->getOutputFilePath();
        $flags = $this->getScaleWidthFlags();
        $this->writeConfig();
        self::deleteOutputImageFile($this->getOutputFileName());
        // Seems to have problems when putting variables within quotes sometimes
        $this->exportCommand = "./phantomjs highcharts-convert.js -infile ".$configPath." -constr ".$constr.
            " -outfile ".$outputPath." ".$flags;
        $this->commandOutput = $output = self::execute($this->exportCommand);
        if (strpos($output, "Error") !== false) {
            throw new RuntimeException($output);
        }
        $this->imageData = file_get_contents($outputPath);
    }
    /**
     * @param string $type
     * @param string $data
     * @param string $alt
     * @param string $title
     * @param string $elementId
     * @return string
     */
    public static function imageDataToHtml($type, $data, $alt, $title, $elementId)
    {
        if ($type === self::SVG) {
            $imageHtml = $data;
        } else {
            // max-width: unset; width: unset; required to avoid blur from WP css
            $style = "max-width: 100%; width: unset; margin: auto;"; // DO NOT REMOVE THIS!!!
            $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
            $imageHtml = '
                <img id="' . $elementId . '-image"
                    class="chart-img"
                    style="' . $style . '"
                    src="' . $base64 . '"
                    alt="' . $alt . '"
                    title="' . $title . '"/>';
        }
        if (stripos($imageHtml, "position: absolute") !== false) {
            throw new \LogicException("Do not use position: absolute because all the images will pile on top ".
                "of each other!");
        }
        return $imageHtml;
    }
}
