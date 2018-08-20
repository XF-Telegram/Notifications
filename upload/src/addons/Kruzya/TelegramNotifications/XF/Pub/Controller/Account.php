<?php
namespace Kruzya\TelegramNotifications\XF\Pub\Controller;

use Kruzya\Telegram\Utils as CoreUtils;

class Account extends XFCP_Account {
  public function actionPreferences() {
    $view = parent::actionPreferences();
    $telegramUser = CoreUtils::getTelegramEntityByUser(\XF::visitor());
    if (get_class($view) == 'XF\Mvc\Reply\View' && \XF::visitor()->hasPermission('telegram', 'notifications'))
      $view->setParam('telegram', $telegramUser);

    return $view;
  }

  protected function preferencesSaveProcess(\XF\Entity\User $visitor) {
    $form = parent::preferencesSaveProcess($visitor);
    $telegramUser = CoreUtils::getTelegramEntityByUser($visitor);

    if ($telegramUser && $visitor->hasPermission('telegram', 'notifications')) {
      $result = $this->filter([
        'telegram'  => [
          'notifications' => 'bool'
        ]
      ]);
      $result = $result['telegram']['notifications'];

      $telegramUser->notifications = $result;
      $telegramUser->save();
    }

    return $form;
  }
}