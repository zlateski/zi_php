<?php

//
// Copyright (C) 2011  Aleksandar Zlateski <zlateski@mit.edu>
// ----------------------------------------------------------
//
// This program is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with this program.  If not, see <http://www.gnu.org/licenses/>.
//


<?PHP

function _rgb2hsv($r, $g, $b) {
  $v = max($r, $g, $b);
  $t = min($r, $g, $b);
  $s = ($v == 0) ? 0 : ($v - $t) / $v;
  if ($s == 0)
    $h = -1;
  else {
    $a = $v-$t;
    $cr = ($v-$r) / $a;
    $cg = ($v-$g) / $a;
    $cb = ($v-$b) / $a;

    $h = ($r == $v) ?
      $cb-$cg :
      (($g == $v) ?
       2 + $cr - $cb :
       (($b == $v) ? $h = 4 + $cg - $cr : 0));

    $h *= 60;
    $h = ($h < 0) ? $h + 360 : $h;
  }
  return array($h, $s, $v);
}

function _hsv2rgb($h, $s, $v) {
  if ($s == 0)
    return array($v, $v, $v);
  else {
    $h = ($h %= 360) / 60;
    $i = floor($h);
    $f = $h - $i;
    $q[0] = $q[1] = $v * (1 - $s);
    $q[2] = $v * (1 - $s * (1 - $f) );
    $q[3] = $q[4] = $v;
    $q[5] = $v * (1 - $s * $f);

    return array(round($q[ ($i + 4) % 6 ]),
                 round($q[ ($i + 2) % 6 ]),
                 round($q[ $i % 6]) );
  }
}

class color
{
    public $hex;
    public $r = 0;
    public $g = 0;
    public $b = 0;
    public $opacity = 1;

    public function __construct( $r, $g, $b, $opacity = 1.0 )
    {
        $this->r = min($r, 255.0);
        $this->g = min($g, 255.0);
        $this->b = min($b, 255.0);
        $this->opacity = (double)min( $opacity, 1.0 );
    }

    public function to_hex()
    {
        return sprintf("%02x%02x%02x", $this->r, $this->g, $this->b);
    }

  public function getResource(&$zimage) {
    return $zimage->colorAllocateAlpha($this->r,
                                       $this->g,
                                       $this->b,
                                       127 * (1 - $this->opacity));
  }

  public function timesSaturationBrightness($timesS = 1, $timesB = 1) {
    list($h, $s, $v) = _rgb2hsv($this->r, $this->g, $this->b);
    $s *= $timesS;
    if ($s > 1) $s = 1;
    $v *= $timesB;
    if ($v > 255) $v = 255;
    list($r, $g, $b) = _hsv2rgb($h, $s, $v);
    return new Color($r, $g, $b, $this->opacity);
  }

  public function saturation($times = 1) {
    return $this->timesSaturationBrightness($times);
  }

  public function brightness($times = 1) {
    return $this->timesSaturationBrightness(1, $times);
  }

  public function blendWith($c, $r) {

    $O1 = $this->opacity * $r;
    $O2 = $c->opacity * (1 - $r);

    $O = $O1 + $O2;
    $Or = $O == 0 ? 0 : $O2 / $O;

    $R = $this->r - $Or * ($this->r - $c->r);
    $G = $this->g - $Or * ($this->g - $c->g);
    $B = $this->b - $Or * ($this->b - $c->b);

    return new Color($R, $G, $B, $O);

  }

  public static function factory($hex = "FFF", $opacity = 1) {
    return self::create($hex, $opacity);
  }

  public static function fromHSV($h, $s, $v) {
    list($r, $g, $b) = _hsv2rgb($h, $s, $v);
    return new Color($r, $g, $b);
  }

  public static function create($hex = "FFF", $opacity = 1) {

    $r = 0; $g = 0; $b = 0;
    $opacity = _LIMIT($opacity, 0, 1);

    $hex = preg_replace('/[^a-fA-F0-9]+/', '', $hex);
    if (preg_match('/^([a-fA-F0-9])([a-fA-F0-9])([a-fA-F0-9])$/', $hex, $m)) {

      $r = hexdec($m[1] . $m[1]);
      $g = hexdec($m[2] . $m[2]);
      $b = hexdec($m[3] . $m[3]);

      return new Color($r, $g, $b, $opacity);

    }

    if (preg_match('/^([a-fA-F0-9]{2})'.
                   '([a-fA-F0-9]{2})([a-fA-F0-9]{2})$/', $hex, $m)) {

      $r = hexdec($m[1]);
      $g = hexdec($m[2]);
      $b = hexdec($m[3]);

      return new Color($r, $g, $b, $opacity);

    }

    if (preg_match('/^([a-fA-F0-9]{2})([a-fA-F0-9]{2})'.
                   '([a-fA-F0-9]{2})([a-fA-F0-9]{2})$/', $hex, $m)) {

      $r = hexdec($m[1]);
      $g = hexdec($m[2]);
      $b = hexdec($m[3]);
      $a = (hexdec($m[4])/255);

      return new Color($r, $g, $b, $a);

    }

  }

  public function Copy() {
    return new Color($this->r, $this->g, $this->b, $this->opacity);
  }

  public function times($x) {
    return new Color(round($this->r * $x),
                     round($this->g * $x),
                     round($this->b * $x),
                     $this->opacity);
  }

  public function mergeTo($x) {
    return new Color(round(($this->r * $x->r) / 255),
                     round(($this->g * $x->g) / 255),
                     round(($this->b * $x->b) / 255),
                     $this->opacity * $x->opacity);
  }

  public function timesAlpha($x, $a) {
    return new Color(round($this->r * $x),
                     round($this->g * $x),
                     round($this->b * $x),
                     $this->opacity * $a);
  }

  public function timesAll($x, $y, $z, $a) {
    return new Color(round($this->r * $x),
                     round($this->g * $y),
                     round($this->b * $z),
                     $this->opacity * $a);
  }

}

