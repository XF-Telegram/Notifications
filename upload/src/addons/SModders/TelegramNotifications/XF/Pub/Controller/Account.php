<?php

/**
 * This file is a part of [Telegram] Notifications.
 * All rights reserved.
 *
 * Developed by SourceModders.
 */

namespace SModders\TelegramNotifications\XF\Pub\Controller;


class Account extends XFCP_Account
{
    protected function preferencesSaveProcess(\XF\Entity\User $visitor)
    {
        $form = parent::preferencesSaveProcess($visitor);
        
        $optOutActions = $this->repository('XF:UserAlert')->getAlertOptOutActions();
        $telegram = $this->filter('telegram', 'array-bool');
        $telegramConversations = $this->filter('option.smodders_tgnotifications_on_conversation', 'bool', false);

        $telegramOptOuts = [];
        foreach (array_keys($optOutActions) as $optOut)
        {
            if (!empty($telegram[$optOut]))
            {
                $telegramOptOuts[] = $optOut;
            }
        }

        $form->setupEntityInput($visitor->getRelationOrDefault('Option'), [
            'smodders_tgnotifications_optout' => $telegramOptOuts,
            'smodders_tgnotifications_on_conversation' => $telegramConversations
        ]);
        
        return $form;
    }
}