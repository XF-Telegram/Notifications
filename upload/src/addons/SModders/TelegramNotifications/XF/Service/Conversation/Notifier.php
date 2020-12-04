<?php

/**
 * This file is a part of [Telegram] Notifications.
 * All rights reserved.
 *
 * Developed by SourceModders.
 */

namespace SModders\TelegramNotifications\XF\Service\Conversation;


use XF\Entity\User;

class Notifier extends XFCP_Notifier
{
    protected function _sendNotifications($actionType, array $notifyUsers, \XF\Entity\ConversationMessage $message = null, User $sender = null)
    {
        $t = $this;
        \XF::runLater(function () use ($t, $sender, $message, $notifyUsers, $actionType)
        {
            if (!$sender && $message)
            {
                $sender = $message->User;
            }

            /** @var \SModders\TelegramNotifications\XF\Entity\User $user */
            foreach ($notifyUsers AS $user)
            {
                if (!$t->_canUserReceiveTelegramNotification($user, $sender))
                {
                    continue;
                }

                /** @var \SModders\TelegramNotifications\Service\Conversation\Telegram $service */
                $service = $t->service('SModders\TelegramNotifications:Conversation\Telegram',
                    $user, $message, $actionType, $sender);
                if ($service->sendNotification())
                {
                    $usersNotified[$user->user_id] = $user;
                }
            }
        });

        return parent::_sendNotifications($actionType, $notifyUsers, $message, $sender);
    }
    
    protected function _canUserReceiveTelegramNotification(User $user, User $sender = null)
    {
        return (
            $user->canReceiveTelegramNotifications()
            && $user->Option->smodders_tgnotifications_on_conversation
            && $this->_canUserReceiveNotification($user, $sender)
        );
    }
}