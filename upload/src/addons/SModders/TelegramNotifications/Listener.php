<?php

/**
 * This file is a part of [Telegram] Notifications.
 * All rights reserved.
 *
 * Developed by SourceModders.
 */

namespace SModders\TelegramNotifications;


use XF\Entity\UserAlert;

class Listener
{
    public static function entity_post_save(UserAlert $alert)
    {
        // Maybe this is update to exist alert.
        if (!$alert->isInsert())
        {
            return;
        }
        
        // Skip this alert, if user can't receive or view him.
        $user = $alert->Receiver;
        if (!$alert->canView() || !$alert->isAlertRenderable() || !$user->Option->doesReceiveTelegram($alert->content_type, $alert->action))
        {
            return;
        }

        // Render text.
        $text = \XF::asVisitor($user, function() use ($alert)
        {
            $oldLanguage = \XF::language();
            $app = \XF::app();
            \XF::setLanguage($app->language($alert->Receiver->language_id));
            
            /** @var \SModders\TelegramNotifications\Service\HtmlPurifier $purifier */
            $purifier = $app->service('SModders\TelegramNotifications:HtmlPurifier', [
                // URL links.
                'a' => ['href'],
    
                // Bold elements.
                'b' => [], 'strong' => [],
    
                // Italic.
                'i' => [], 'em' => [],
    
                // Code blocks.
                'pre' => [], 'code'  => [],
            ]);
            $text = $purifier->purify($alert->render());
            
            \XF::setLanguage($oldLanguage);
            return $text;
        });

        $boardUrl = \XF::app()->options()->boardUrl;
        $text = str_replace('href="/', "href=\"{$boardUrl}/", $text);
        
        try {
            /** @var \TelegramBot\Api\BotApi $api */
            $api = \XF::app()->get('smodders.telegram')->api();
            $api->sendMessage(
                $user->Profile->connected_accounts['smodders_telegram'],
                $text, 'HTML', true
            );
        }
        catch (\Exception $e)
        {
            // Unsubscribe users from default alerts. We don't want try repeatedly send message.
            $user->Option->smodders_tgnotifications_optout = [];
            $user->Option->save();
        }
    }
}