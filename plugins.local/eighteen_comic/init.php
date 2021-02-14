<?php
function increase_limit($memoryNeeded)
{
  if (function_exists('memory_get_usage') && memory_get_usage() + $memoryNeeded > (int) ini_get('memory_limit') * pow(1024, 2)) {
    ini_set('memory_limit', (int) ini_get('memory_limit') + ceil(((memory_get_usage() + $memoryNeeded) - (int) ini_get('memory_limit') * pow(1024, 2)) / pow(1024, 2)) . 'M');
    echo ini_get('memory_limit');
  }
}

function increase_limit_for_file($file)
{
  $imageInfo = getimagesize($file);
  $memoryNeeded = memory_need($imageInfo[0], $imageInfo[1], $imageInfo['bits'], $imageInfo['channels'] / 8);
  increase_limit($memoryNeeded);
}

function memory_need($width, $height, $channel = 3, $bits = 8)
{
  return round(($width * $height * $bits * $channel / 8 + Pow(2, 16)) * 1.65);
}

class eighteen_comic extends Plugin
{

  /* @var PluginHost $host */
  private $host;

  function about()
  {
    return array(
      1.0,
      "Fix 18comic image",
      "machsix"
    );
  }


  function crop_image($file)
  {
    increase_limit_for_file($file);
    $img = imagecreatefromjpeg($file);
    $w = imagesx($img);
    $h = imagesy($img);
    increase_limit(memory_need($w, $h) * 1.5);
    $dest = imagecreatetruecolor($w, $h);

    $num = 10;
    $remainder = $h % $num;
    $copyW = $w;

    for ($i = 0; $i < $num; $i++) {
      $copyH = intdiv($h, $num);
      $py = $copyH * $i;
      $y = $h - ($copyH * ($i + 1)) - $remainder;
      if ($i == 0) {
        $copyH += $remainder;
      } else {
        $py += $remainder;
      }
      // $temp_img = imagecrop($img, ['x' => 0, 'y' => $py, 'width' => $copyW, 'height' => $copyH]);
      imagecopy($dest, $img, 0, $py, 0, $y, $copyW, $copyH);
    }
    // Debug::log($file . ' width = '. strval($w));
    imagedestroy($img);
    imagejpeg($dest, $file);
    imagedestroy($dest);
  }

  function init($host)
  {
    $this->host = $host;
    $host->add_hook($host::HOOK_MODIFY_MEDIA, $this);
  }

  function hook_modify_media($fullpath, $site_url, &$entry)
  {
    $hostname = parse_url($site_url, PHP_URL_HOST);

    if (strcmp($hostname, '18comic.vip') == 0) {
      if (intVal($entry->getAttribute('data-scramble')) == 1) {
        $this->crop_image($fullpath);
         Debug::log($entry->getAttribute('src') . ' scramble');
         Debug::log($fullpath . ' scramble');
        return True;
      }
    } else {
      Debug::log($hostname . " not match");
    }

    return False;
  }

  function api_version()
  {
    return 2;
  }
}
