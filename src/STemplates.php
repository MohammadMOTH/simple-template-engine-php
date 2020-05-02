<?php


namespace BrowserBotPHP\STemplates;

class STemplates
{


    public static $Methods = ["Print" => "_print_var"];


    private $_data = array();
    private $_file;
    private $_output = "";
    private const Print_start = '{{'; //{{same.var.in.value}}
    private const Print_end = '}}'; //{{same.var.in.value}}

    public function Set(string $name, $object)
    {
        $this->_data[$name] = $object;
        return $this;
    }

    public function __construct($file = null)
    {
        $this->_file = $file;
    }

    public function SetTemplate($File)
    {
        # code...

        $this->_file = $File;
        return $this;
    }

    public function Process()
    {
        $filestrings = file_get_contents($this->_file);

        foreach (self::$Methods as $value) {
            $filestrings = $this->{$value}($filestrings);
        }

        $this->_output = $filestrings;
        return $this;
    }
    public function SaveOutput($FileName)
    {
        return file_put_contents($FileName, $this->_output);
    }
    public function GetOutput()
    {
        return $this->_output;
    }


    private function _print_var($filestrings)
    {
        $printRegx =  "/\\" . self::Print_start . ".+" . self::Print_end . "/"; // '/\{{.+}}/'  {{same.var.in.value}}

        preg_match_all($printRegx, $filestrings, $matches, PREG_SET_ORDER, 0);
        $newarray = array();
        foreach ($matches as  $value) {
            $keymatches = $value[0];
            $value = $value[0];

            if (strpos($value, ".") !== false) {
                $valueAfter =    str_replace(self::Print_start, '', str_replace(' ', '', str_replace(self::Print_end, '', $value)));
                $explode = explode('.', $valueAfter);
                $objectData =  $this->_data;
                foreach ($explode as  $valuex) {
                    if (is_array($objectData)) {
                        $objectData  = (object) $objectData;
                    }
                    $objectData = $objectData->{$valuex};
                }
                $value = $objectData;
            } else {
                $valueAfter =    str_replace(self::Print_start, '', str_replace(' ', '', str_replace(self::Print_end, '', $value)));
                foreach ($this->_data as $key => $data) {
                    if ($valueAfter == $key) {
                        $value = $data;
                        break;
                    }
                }
            }

            $newarray[$keymatches] =  $value;
        }

        foreach ($newarray as  $key => $value) {

            $filestrings = str_replace($key, $value, $filestrings);
        }
        return $filestrings;
    }
}
