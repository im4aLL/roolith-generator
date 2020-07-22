<?php
namespace Roolith;

use Roolith\Constants\ColorConstants;

class ConsoleColor
{
    private $foregroundColors;
    private $backgroundColors;

    public function __construct()
    {
        $this->foregroundColors = [];
        $this->backgroundColors = [];

        $this->defineForgroundColors()
            ->defineBackgroundColors();
    }

    public function getColoredString($string, $foregroundColor = null, $backgroundColor = null)
    {
        $colored_string = "";

        if (isset($this->foregroundColors[$foregroundColor])) {
            $colored_string .= "\033[" . $this->foregroundColors[$foregroundColor] . "m";
        }

        if (isset($this->backgroundColors[$backgroundColor])) {
            $colored_string .= "\033[" . $this->backgroundColors[$backgroundColor] . "m";
        }

        $colored_string .=  $string . "\033[0m";

        return $colored_string;
    }

    public function getForegroundColors()
    {
        return array_keys($this->foregroundColors);
    }

    public function getBackgroundColors()
    {
        return array_keys($this->backgroundColors);
    }

    private function defineForgroundColors()
    {
        $this->foregroundColors[ColorConstants::BLACK] = '0;30';
        $this->foregroundColors[ColorConstants::DARK_GRAY] = '1;30';
        $this->foregroundColors[ColorConstants::BLUE] = '0;34';
        $this->foregroundColors[ColorConstants::LIGHT_BLUE] = '1;34';
        $this->foregroundColors[ColorConstants::GREEN] = '0;32';
        $this->foregroundColors[ColorConstants::LIGHT_GREEN] = '1;32';
        $this->foregroundColors[ColorConstants::CYAN] = '0;36';
        $this->foregroundColors[ColorConstants::LIGHT_CYAN] = '1;36';
        $this->foregroundColors[ColorConstants::RED] = '0;31';
        $this->foregroundColors[ColorConstants::LIGHT_RED] = '1;31';
        $this->foregroundColors[ColorConstants::PURPLE] = '0;35';
        $this->foregroundColors[ColorConstants::LIGHT_PURPLE] = '1;35';
        $this->foregroundColors[ColorConstants::BROWN] = '0;33';
        $this->foregroundColors[ColorConstants::YELLOW] = '1;33';
        $this->foregroundColors[ColorConstants::LIGHT_GRAY] = '0;37';
        $this->foregroundColors[ColorConstants::WHITE] = '1;37';

        return $this;
    }

    private function defineBackgroundColors()
    {
        $this->backgroundColors[ColorConstants::BLACK] = '40';
        $this->backgroundColors[ColorConstants::RED] = '41';
        $this->backgroundColors[ColorConstants::GREEN] = '42';
        $this->backgroundColors[ColorConstants::YELLOW] = '43';
        $this->backgroundColors[ColorConstants::BLUE] = '44';
        $this->backgroundColors[ColorConstants::MAGENTA] = '45';
        $this->backgroundColors[ColorConstants::CYAN] = '46';
        $this->backgroundColors[ColorConstants::LIGHT_GRAY] = '47';

        return $this;
    }
}
