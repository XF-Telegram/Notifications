<?php

/**
 * This file is a part of [Telegram] Notifications.
 * All rights reserved.
 *
 * Developed by SourceModders.
 */

namespace SModders\TelegramNotifications\XF\Entity;


class User extends XFCP_User
{
    public function canReceiveTelegramNotifications()
    {
        if (!$this->app()->options()->smtgn_enabled)
        {
            return false;
        }

        if (!array_key_exists('smodders_telegram', $this->Profile->connected_accounts))
        {
            return false;
        }

        return $this->hasPermission('smtgn', 'smtgn_use');
    }
}