<?php

class CustomTwigFilterExtension extends \Twig_Extension
{
  public function getName()
  {
    return "customtwig";
  }

  public function getFilters()
  {
    return array(
      "byte2size"                 => new \Twig_Filter_Method($this, "byte2size"),
      "sec2hms"                   => new \Twig_Filter_Method($this, "sec2hms"),
      "distance_of_time_in_words" => new \Twig_Filter_Method($this, "distance_of_time_in_words"),
      "trunk_tooltip"             => new \Twig_Filter_Method($this, "trunk_tooltip"),
      "transfer_type2image"       => new \Twig_Filter_Method($this, "transfer_type2image"),
    );
  }

  public function byte2size($bytes)
  {
    $size = $bytes / 1024;
    if($size < 1024)
    {
      return number_format($size, 2) . ' KB';
    }
    else
    {
      if($size / 1024 < 1024)
      {
        return number_format($size / 1024, 2) . ' MB';
      }
      else if ($size / 1024 / 1024 < 1024)
      {
        return number_format($size / 1024 / 1024, 2) . ' GB';
      }
    }
  }

  public function sec2hms($sec, $padHours = false)
  {
    $hms = "";
    $hours = intval(intval($sec) / 3600);
    $hms .= ($padHours)
          ? str_pad($hours, 2, "0", STR_PAD_LEFT). "h "
          : $hours. "h ";
    $minutes = intval(($sec / 60) % 60);
    $hms .= str_pad($minutes, 2, "0", STR_PAD_LEFT). "m ";
    $seconds = intval($sec % 60);
    $hms .= str_pad($seconds, 2, "0", STR_PAD_LEFT);
    return $hms.'s';
  }

  public function transfer_type2image($transfer)
  {
    switch($transfer)
    {
      case "STOR":  $image = 'up.png'; break;
      case "RETR":  $image = 'down.png'; break;
      case "DELE":  $image = 'delete.png'; break;
      case "MKD":   $image = 'folder.png'; break;
      case "RNTO":  $image = 'pencil.png'; break;
      default:      $image = 'unknown.png';
    }

    return 'assets/images/'.$image;
  }

  // from DateHelper.php
  public function distance_of_time_in_words($from_time, $to_time = null)
  {
    $distance_in_minutes = floor(abs(($to_time ?: time()) - strtotime($from_time)) / 60);

    $string = '';
    $parameters = array();

    if ($distance_in_minutes <= 1)
    {
      $string = $distance_in_minutes == 0 ? 'less than a minute' : '1 minute';
    }
    else if ($distance_in_minutes >= 2 && $distance_in_minutes <= 44)
    {
      $string = '%minutes% minutes';
      $parameters['%minutes%'] = $distance_in_minutes;
    }
    else if ($distance_in_minutes >= 45 && $distance_in_minutes <= 89)
    {
      $string = 'about 1 hour';
    }
    else if ($distance_in_minutes >= 90 && $distance_in_minutes <= 1439)
    {
      $string = 'about %hours% hours';
      $parameters['%hours%'] = round($distance_in_minutes / 60);
    }
    else if ($distance_in_minutes >= 1440 && $distance_in_minutes <= 2879)
    {
      $string = '1 day';
    }
    else if ($distance_in_minutes >= 2880 && $distance_in_minutes <= 43199)
    {
      $string = '%days% days';
      $parameters['%days%'] = round($distance_in_minutes / 1440);
    }
    else if ($distance_in_minutes >= 43200 && $distance_in_minutes <= 86399)
    {
      $string = 'about 1 month';
    }
    else if ($distance_in_minutes >= 86400 && $distance_in_minutes <= 525959)
    {
      $string = '%months% months';
      $parameters['%months%'] = round($distance_in_minutes / 43200);
    }
    else if ($distance_in_minutes >= 525960 && $distance_in_minutes <= 1051919)
    {
      $string = 'about 1 year';
    }
    else
    {
      $string = 'over %years% years';
      $parameters['%years%'] = floor($distance_in_minutes / 525960);
    }

    return strtr($string . ' ago', $parameters);
  }

  public function trunk_tooltip($string)
  {
  /*
  Array
  (
      [0] => /www/myfolder/mysubfolder/myfile.txt
      [1] => www
      [2] => myfolder/mysubfolder/
      [3] => myfolder/mysubfolder
      [4] => myfile.txt
  )

  Array
  (
      [0] => /www/MICHAEL CALFAN vs MASTERS AT WORK - Love Resurrection (CARL DUTT Bootleg).mp3
      [1] => www
      [2] =>
      [3] =>
      [4] => MICHAEL CALFAN vs MASTERS AT WORK - Love Resurrection (CARL DUTT Bootleg).mp3
  )
  */

    preg_match('/\/([a-zA-Z.-]*)\/((.*)\/)?(.*)/i', $string, $matches);

    // cut too long single file
    $file = $matches[count($matches)-1];
    if(strlen($file) > 50)
    {
      $file = substr($file, (strlen($file) - 40), strlen($file));
    }

    // only file at the end?
    $trail = '/';
    if(empty($matches[2]) && empty($matches[3]))
    {
      $trail = ' ';
    }

    $newString = '/'.$matches[1].'/...'.$trail.$file;


    return '<span class="tooltip" data-content="'.$string.'" data-original-title="Full filename">'.$newString.'</span>';
  }
}
