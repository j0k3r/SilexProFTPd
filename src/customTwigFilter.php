<?php

function byte2size($bytes)
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

function sec2hms($sec, $padHours = false)
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

// from DateHelper.php
function distance_of_time_in_words($from_time, $to_time = null, $include_seconds = true)
{
  $from_time = strtotime($from_time);
  $to_time = $to_time? $to_time: time();

  $distance_in_minutes = floor(abs($to_time - $from_time) / 60);
  $distance_in_seconds = floor(abs($to_time - $from_time));

  $string = '';
  $parameters = array();

  if ($distance_in_minutes <= 1)
  {
    if (!$include_seconds)
    {
      $string = $distance_in_minutes == 0 ? 'less than a minute' : '1 minute';
    }
    else
    {
      if ($distance_in_seconds <= 5)
      {
        $string = 'less than 5 seconds';
      }
      else if ($distance_in_seconds >= 6 && $distance_in_seconds <= 10)
      {
        $string = 'less than 10 seconds';
      }
      else if ($distance_in_seconds >= 11 && $distance_in_seconds <= 20)
      {
        $string = 'less than 20 seconds';
      }
      else if ($distance_in_seconds >= 21 && $distance_in_seconds <= 40)
      {
        $string = 'half a minute';
      }
      else if ($distance_in_seconds >= 41 && $distance_in_seconds <= 59)
      {
        $string = 'less than a minute';
      }
      else
      {
        $string = '1 minute';
      }
    }
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

function trunk_tooltip($string)
{
  preg_match('/\/(\w+-?)*\.([a-z]{2,4})\/www\/(.*)\/(.*)/', $string, $matches);

  $newString = '/'.$matches[1].'.'.$matches[2].'/.../'.$matches[count($matches)-1];

  return '<span class="tooltip" data-content="'.$string.'" data-original-title="Full filename">'.$newString.'</span>';
}

$app['twig']->addFilter('byte2size', new Twig_Filter_Function('byte2size'));
$app['twig']->addFilter('sec2hms', new Twig_Filter_Function('sec2hms'));
$app['twig']->addFilter('distance_of_time_in_words', new Twig_Filter_Function('distance_of_time_in_words'));
$app['twig']->addFilter('trunk_tooltip', new Twig_Filter_Function('trunk_tooltip'));
