<?php
namespace Kruzya\TelegramNotifications;

use Kruzya\Telegram\Utils as CoreUtils;

use XF\Mvc\Entity\Entity;
use XF\Entity\UserAlert;

use XF\Mvc\Entity\Manager;
use XF\Mvc\Entity\Structure;

class Listener {
  public static function saveAlert(Entity $entity) {
    // If notifications disabled globally, ignore this event.
    if (!$entity->Receiver->hasPermission('telegram', 'notifications'))
      return;

    // Before sending, we need check: this is new alert or not.
    if ($entity->view_date != 0) {
      // User viewed this alert now (or later). Just skip.
      return;
    }

    // Check alert as viewable.
    if (!$entity->canView())
      return;

    // Check existing a Telegram account.
    /** @var \Kruzya\Telegram\Entity\User TelegramUser */
    $TelegramUser = CoreUtils::getTelegramEntityByUser($entity->Receiver);
    if (!$TelegramUser || !$TelegramUser->notifications) {
      // skip this alert.
      return;
    }

    // Set new language.
    $old_language = \XF::language();
    \XF::setLanguage(\XF::app()->language($entity->Receiver->language_id));

    // Clear text.
    $text = \XF::asVisitor($entity->Receiver, function () use($entity) {
      return HtmlPurifier::purify($entity->render(), [
        // allow links with only URL.
        'a' => [
          'href',
        ],

        // allow bold text.
        'b' => [], 'strong' => [],

        // allow italic.
        'i' => [], 'em' => [],

        // Also Telegram allows a code block.
        'code' => [], 'pre' => [],
      ]);
    });

    $boardUrl = \XF::app()->options()->boardUrl;
    $text = str_replace('href="/', 'href="' . $boardUrl . '/', $text);

    // Add alert to queue.
    $TelegramUser->addNotification([
      'text'                      => $text,
      'parse_mode'                => 'HTML',
      'disable_web_page_preview'  => true,
    ]);

    // Reset language.
    \XF::setLanguage($old_language);
  }

  public static function editUserStructure(Manager $manager, Structure &$structure) {
    $structure->columns['notifications'] = [
      'type'    => Entity::BOOL,
      'default' => 0,
    ];
  }
}