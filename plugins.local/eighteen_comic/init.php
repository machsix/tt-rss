<?php

class eighteen_comic extends Plugin {

  /* @var PluginHost $host */
  private $host;

  function about() {
    return array(1.0,
      "Fix 18comic image",
      "machsix");
  }

  function crop_image($file) {
    $img = imagecreatefromjpeg($file);
    $w = imagesx($img);
    $h = imagesy($img);
    $dest = imagecreatetruecolor($w, $h);

    $num = 10;
    $remainder = $h % $num;
    $copyW = $w;

    for ($i=0; $i < $num; $i++) {
        $copyH = intdiv($h, $num);
        $py = $copyH * $i;
        $y = $h - ($copyH * ($i + 1)) - $remainder;
        if ($i == 0) {
            $copyH += $remainder;
        } else {
            $py += $remainder;
        }
        // $temp_img = imagecrop($img, ['x' => 0, 'y' => $py, 'width' => $copyW, 'height' => $copyH]);
        imagecopy($dest, $img, 0, $y, 0, $py, $copyW, $copyH);
    }
    // Debug::log($file . ' width = '. strval($w));
    imagedestroy($img);
    imagejpeg($dest, $file);

  }

  function init($host)
  {
    $this->host = $host;
    $host->add_hook($host::HOOK_MODIFY_MEDIA, $this);
  }

  function hook_modify_media($fullpath, $site_url) {
    $hostname = parse_url($site_url, PHP_URL_HOST);

    if(strcmp($hostname, '18comic.vip') == 0) {
      $this->crop_image($fullpath);
      return True;
    }
  }

  function api_version() {
    return 2;
  }
}
