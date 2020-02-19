# php-highcharts-exporter

Generate Charts on Server Side using Highcharts without NodeJS

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Build Status][ico-travis]][link-travis]
[![Quality Score][ico-code-quality]][link-code-quality]
[![Total Downloads][ico-downloads]][link-downloads]


## Install

Via Composer

``` bash
$ composer require mikepsinn/php-highcharts-exporter
```

Add the following post-install-cmd and post-update-cmd to your `composer.json` file so that phantomjs is executable

```
  "scripts": {
    "post-install-cmd": [
      "set -x && sudo chmod +x vendor/mikepsinn/php-highcharts-exporter/phantomjs"
    ],
    "post-update-cmd": [
      "set -x && sudo chmod +x vendor/mikepsinn/php-highcharts-exporter/phantomjs"
    ]
  }
```

## Usage

``` php
$export = new HighchartsExport(HighchartsExport::getConfigContents('test-basic-line'));
$export->setOutputFileName("test-chart.png"); // optional
$export->setImageType("png"); // optional
$path = $export->getFilePath(); // Absolute output file path on server
$data = $export->getImageData(); // Raw image data can be saved to file
$html = $export->getHtml(); // Inline this in any html file
```

## Testing

``` bash
$ composer test
```

## Security

If you discover any security related issues, please email m@thinkbynumbers.org instead of using the issue tracker.

## Credits

- [Mike P. Sinn][link-author]
- [All Contributors][link-contributors]
- PhantomJS (phantomjs.org) is a headless WebKit scriptable with JavaScript.
- Highcharts JS (highcharts.com) is a JavaScript charting library based on SVG, with fallbacks to VML and canvas for old browsers.

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

[ico-version]: https://img.shields.io/packagist/v/mikepsinn/php-highcharts-exporter.svg?style=flat-square
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square
[ico-travis]: https://img.shields.io/travis/mikepsinn/php-highcharts-exporter/master.svg?style=flat-square
[ico-scrutinizer]: https://img.shields.io/scrutinizer/coverage/g/mikepsinn/php-highcharts-exporter.svg?style=flat-square
[ico-code-quality]: https://img.shields.io/scrutinizer/g/mikepsinn/php-highcharts-exporter.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/mikepsinn/php-highcharts-exporter.svg?style=flat-square

[link-packagist]: https://packagist.org/packages/mikepsinn/php-highcharts-exporter
[link-travis]: https://travis-ci.org/mikepsinn/php-highcharts-exporter
[link-scrutinizer]: https://scrutinizer-ci.com/g/mikepsinn/php-highcharts-exporter/code-structure
[link-code-quality]: https://scrutinizer-ci.com/g/mikepsinn/php-highcharts-exporter
[link-downloads]: https://packagist.org/packages/mikepsinn/php-highcharts-exporter
[link-author]: https://github.com/mikepsinn
[link-contributors]: ../../contributors


### Development Notes

#### Test to Make Sure it Works With Known Good Options File

-  cd <REPO> && ./phantomjs highcharts-convert.js -infile test-options.json -constr Chart -outfile images/test.png

#### Basic Command Line Test

- ./phantomjs highcharts-convert.js -infile test-basic-line.json -constr Chart -outfile images/basic-line.png

#### Command Line Usage

- Save your highchart config to images/options.json
- cd <REPO> && ./phantomjs highcharts-convert.js -infile images/options.json -constr Chart -outfile images/test.png

[Tutorial](http://kodeinfo.com/post/generate-charts-on-server-side-using-highcharts)
