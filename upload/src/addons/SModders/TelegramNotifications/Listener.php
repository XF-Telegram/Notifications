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

        $app = $alert->app();

        /** @var \SModders\TelegramCore\SubContainer\Telegram $telegramContainer */
        $telegramContainer = $app->container('smodders.telegram');

        // Render text.
        $text = $telegramContainer->asVisitor($user, function () use ($alert, $app)
        {
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
            $text = $purifier->purify($alert->render(), sprintf('%s_%s', $alert->content_type, $alert->action));

            return $text;
        });

        // Build base URL.
        $boardUrl = parse_url(\XF::app()->options()->boardUrl);
        $baseUrl = sprintf("%s://%s", $boardUrl['scheme'], $boardUrl['host']);
        if (array_key_exists('port', $boardUrl))
        {
            $baseUrl .= sprintf(':%d', $boardUrl['port']);
        }
        
        $text = str_replace('href="/', sprintf('href="%s/', $baseUrl), $text);
        
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