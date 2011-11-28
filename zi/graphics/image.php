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


class image
{
    protected $img = null;
    protected $w;
    protected $h;

    private function __construct($img)
    {
        $this->img = $img;
        $this->w   = imagesx($img);
        $this->h   = imagesy($img);
    }


    // Will try to call gd function
    public function __call( $function, $arguments )
    {
        $gd_function_name = "image" . strtolower($function);
        array_unshift( $arguments, $this->img );
        return call_user_func_array( $gd_function_name, $arguments );
    }

    // factory

    static function create( $w, $h, $bg_color = null )
    {
        $img = imagecreatetruecolor($w, $h);
        $new = new image($img);

        if ($bg_color)
        {
            $c = $new->colorAllocateAlpha($bg_color->r,
                                          $bg_color->g,
                                          $bg_color->b,
                                          127 * (1 - $this->opacity));
            $new->filledRectangle(0, 0, $w-1, $h-1, $c);
        }
        else
        {
            $c = $new->colorAllocateAlpha(255.0,255.0,255.0,0.0);
            $new->filledRectangle(0, 0, $w-1, $h-1, $c);
        }

        return $new;
    }

    static function from_file( $filename )
    {
        if (!file_exists($filename))
        {
            return null;
        }

        $info = getimagesize($filename);

        if (!$info)
        {
            return null;
        }

        switch ( exif_imagetype($filename) )
        {
        case IMAGETYPE_GIF:
            $img = imagecreatefromgif($filename);
            break;
        case IMAGETYPE_JPEG:
            $img = imagecreatefromjpeg($filename);
            break;
        case IMAGETYPE_PNG:
            $img = imagecreatefrompng($filename);
            break;
        }

        if (!$img)
        {
            return null;
        }

        return new image($img);
    }

    public function save_jpeg( $filename, $quality = 95 )
    {
        $this->jpeg($filename, $quality);
    }

    public function save_png( $filename )
    {
        $this->saveAlpha(true);
        $c = $this->colorAllocateAlpha(255.0, 255.0, 255.0, 0);
        $this->colorTransparent($c);
        $this->png($filename);
    }

    public function width()
    {
        return $this->w;
    }

    public function height()
    {
        return $this->h;
    }

    public function dimensions()
    {
        return array($this->w, $this->h);
    }

    function flip_horisontal()
    {
        $new = image::create($this->w, $this->h);
        $new->alphaBlending(false);

        for ($x = 0; $x < $this->w; $x++)
        {
            $new->copy($this->img, $x, 0, $this->w - $x - 1, 0, 1, $this->h);
        }

        $this->img = $new->img;
        return $this;
    }

    function flip_vertical()
    {

        $new = image::create($this->w, $this->h);
        $new->alphaBlending(false);

        for ($y = 0; $y < $this->h; $y++)
        {
            $new->copy($this->img, 0, $y, 0, $this->h - $y - 1, $this->w, 1);
        }

        $this->img = $new->img;
        return $this;
    }

    public function resample( $w, $h )
    {
        $new = image::create($w, $h);
        $new->copyResampled($this->img, 0, 0, 0, 0,
                            $w, $h, $this->w, $this->h );
        $this->img = $new->img;
        $this->w = $w;
        $this->h = $h;
        return $this;
    }


    public function fit_into( $maxw = 400, $maxh = 300)
    {

        if ( ($maxw < 1) || ($maxh < 1) )
        {
            return NULL;
        }

        $r = max( $this->w / $maxw, $this->h / $maxh);

        $neww = $this->w / $r;
        $newh = $this->h / $r;

        $new = image::create($neww, $newh);

        $new->copyResampled($this->img, 0, 0, 0, 0,
                            $neww, $newh, $this->w, $this->h);

        $this->img = $new->img;
        $this->w = $neww;
        $this->h = $newh;
        return $this;
    }
}

$i = image::from_file('/home/zlateski/Desktop/Screenshot-6.png');

$i->flip_horisontal();

$i->save_jpeg('/home/zlateski/Desktop/ale.jpg', 100);

$i->flip_vertical();
//$i->resample(300,200);

$i->fit_into(400,1200);

$i->save_png('/home/zlateski/Desktop/ale.png');