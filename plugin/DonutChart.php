<?php

/**
 *
 *    Copyright (C) 2017 onOffice GmbH
 *
 *    This program is free software: you can redistribute it and/or modify
 *    it under the terms of the GNU Affero General Public License as published by
 *    the Free Software Foundation, either version 3 of the License, or
 *    (at your option) any later version.
 *
 *    This program is distributed in the hope that it will be useful,
 *    but WITHOUT ANY WARRANTY; without even the implied warranty of
 *    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *    GNU Affero General Public License for more details.
 *
 *    You should have received a copy of the GNU Affero General Public License
 *    along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

namespace onOffice\WPlugin;

use onOffice\WPlugin\EstateList;
/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2017, onOffice(R) GmbH
 *
 */
 class DonutChart
 {
     private $values;
     private $valuesTitle;
     private $colors = ['#3F9DE4', '#3ac411', '#9C27B0', '#D81B60', '#FEC800'];

     public function __construct(array $values, array $valuesTitle)
     {
         $this->values = $values;
         $this->valuesTitle = $valuesTitle;
     }

     private function toRadians($angle)
     {
         return $angle * pi() / 180;
     }

     private function polarToCartesian($radius, $angle, $subtractGap = false)
     {
         $adjustedAngle = $this->toRadians($angle - 90);
         if ($subtractGap) {
             $adjustedAngle -= asin(0 / $radius);
         }
         $x = 300+ $radius * cos($adjustedAngle);
         $y = 210 + $radius * sin($adjustedAngle);

         return sprintf('%0.2f,%0.2f', $x, $y);
     }
     
     public function generateSVG()
     {
         $total = array_sum($this->values);
         //$total = getTotalCostsData();
         $anglePerValue = 360 / $total;
         $angleStart = 0;
         
         $svgContent = "<svg xmlns=\"http://www.w3.org/2000/svg\" xmlns:xlink=\"http://www.w3.org/1999/xlink\"  viewbox=\"0 0 600 400\" width=\"100%\" height=\"100%\">\n";
         $svgContent .= "<g style=\"stroke:black;stroke-width:0\">\n";

         $counter = 0;
         foreach ($this->values as $value) {
             $angleDelta = $value * $anglePerValue;
             $largeArcFlag = $angleDelta > 180 ? 1 : 0;
             $angleEnd = $angleStart + $angleDelta;
             
             $path = [
                "M" . $this->polarToCartesian(190, $angleStart),
                "L" . $this->polarToCartesian(120, $angleStart),
                "A 120,120,0,{$largeArcFlag},1," . $this->polarToCartesian(120, $angleEnd, true),
                "L" . $this->polarToCartesian(190, $angleEnd, true),
                "A 190,190,0,{$largeArcFlag},0," . $this->polarToCartesian(190, $angleStart)
             ];

             $svgContent .= '<path d="' . implode(' ', $path) . '" class="oo-donut-chart-color'.$counter.'"><title>'.$this->valuesTitle[$counter].'</title></path>' . "\n";
             $angleStart = $angleEnd;
             $counter++;
         }

         $svgContent .= "</g>\n</svg>";
         return $svgContent;
     }
 }