<?php
namespace Kruzya\TelegramNotifications;

class Utils {
  public static function itemExists(array $array = [], $find = NULL) {
    foreach ($array as $key => $item) {
      if ($item == $find)
        return true;
    }

    return false;
  }

  public static function getArrayWithKeys(array $array = []) {
    $data = [];
    foreach ($array as $key => $value) {
      if (is_string($key))
        $data[] = $key;
    }

    return $data;
  }
}