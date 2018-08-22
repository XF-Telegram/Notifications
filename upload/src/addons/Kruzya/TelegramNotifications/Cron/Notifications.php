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
      $result = Utils::api()->sendMessage($notification->options);

      if ($result['ok']) {
        $notification->status = 'failed';
      } else
        $notification->status = 'finished';

      $notification->save();
    }
  }
}