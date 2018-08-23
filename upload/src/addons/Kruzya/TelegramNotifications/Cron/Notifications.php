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

      $notification->status = ($result['ok']) ? 'finished' : 'failed';
      $notification->save();
    }
  }
}