<?php
namespace Kruzya\TelegramNotifications\Cron;

use Kruzya\Telegram\Utils as CoreUtils;

class Notifications {
  public static function processNotifications() {
    $notifications = \XF::finder('Kruzya\\TelegramNotifications:Notification')
      ->where('status', '=', 'planned')
      ->limit(25)
      ->fetch();

    foreach ($notifications as $notification) {
      $result = CoreUtils::api()->sendMessage($notification->options);

      if ($result['ok'])
        $notification->status = 'finished';
      else {
        $notification->status = 'failed';
        file_put_contents('C:/OSPanel/domains/xf.kruzya.me/src/addons/Kruzya/TelegramNotifications/result.txt', json_encode($result));
      }

      $notification->save();
    }
  }
}