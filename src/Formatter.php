<?php
/**
 * Created by PhpStorm.
 * User: Liukaho
 * Date: 2018-12-28
 * Time: 11:41
 */

namespace Liukaho\LoggerAzure;


use Monolog\Formatter\NormalizerFormatter;

class Formatter extends NormalizerFormatter
{

    const FORMAT = "[%datetime%] [%api%] %channel%.%level_name%: %message% %context% %extra%\n";

    protected $format;



    public function __construct($format = null, ?string $dateFormat = null)
    {
        $this->format = $format ?: static::FORMAT;
        parent::__construct($dateFormat);
    }

    public function format(array $record)
    {
        $records = parent::format($record);
        $records['api'] = $_SERVER['REQUEST_URI'];

        $output = $this->format;

        foreach($records as $key => $value) {
            if (false !== strpos($output, '%'.$key.'%')) {
                $output = str_replace('%'.$key.'%', $this->toString($value), $output);
            }
        }
        return $output;
    }

    protected function toString($value)
    {
        return $this->replaceNewLine($this->convertToString($value));
    }

    protected function replaceNewLine($str)
    {
        return str_replace(array("\r\n", "\r", "\n"), ' ', $str);
    }

    protected function convertToString($data)
    {
        if (null === $data || is_bool($data)) {
            return var_export($data, true);
        }

        if (is_scalar($data)) {
            return (string) $data;
        }

        if (version_compare(PHP_VERSION, '5.4.0', '>=')) {
            return $this->toJson($data, true);
        }

        return str_replace('\\/', '/', json_encode($data));
    }
}