<?php

/**
 * This file is a part of [Telegram] Notifications.
 * All rights reserved.
 *
 * Developed by SourceModders.
 */

namespace SModders\TelegramNotifications\XF\Entity;

use XF\Mvc\Entity\Structure;

/**
 * FIELDS
 * @property array[] $smodders_tgnotifications_optout
 * @property bool $smodders_tgnotifications_on_conversation
 */
class UserOption extends XFCP_UserOption
{
    public static function getStructure(Structure $structure)
    {
        $structure = parent::getStructure($structure);
        $structure->columns += [
            'smodders_tgnotifications_optout'           => [
                'type'  => self::LIST_COMMA, 'default'  => [],
                'list'  => ['type'  => 'str', 'unique'  => true, 'sort' => true],
                'changeLog' => false
            ],

            'smodders_tgnotifications_on_conversation'  => [
                'type' => self::BOOL, 'default' => true
            ]
        ];
        return $structure;
    }
    
    public function doesReceiveTelegram($contentType, $action)
    {
        if (!$this->User->canReceiveTelegramNotifications())
        {
            return false;
        }

        return ($this->doesReceiveAlert($contentType, $action)
            && is_array($this->smodders_tgnotifications_optout) // in_array() expects parameter 2 to be array, null given 🤔
            && in_array("{$contentType}_{$action}", $this->smodders_tgnotifications_optout));
    }
}