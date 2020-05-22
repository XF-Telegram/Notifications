<?php


namespace SModders\TelegramNotifications\Job;


use XF\Entity\UserOption;
use XF\Job\AbstractRebuildJob;

class UpgradeUserOptOut extends AbstractRebuildJob
{
    /**
     * @param $start
     * @param $batch
     * @return array
     */
    protected function getNextIds($start, $batch)
    {
        $db = $this->app->db();

        return $db->fetchAllColumn(
            $db->limit(
                "
                SELECT user_id
                FROM xf_user
                WHERE user_id > ?
                ORDER BY user_id
                ",
                $batch
            ),
            $start
        );
    }

    /**
     * @param $id
     * @throws \XF\PrintableException
     */
    protected function rebuildById($id)
    {
        /** @var \XF\Entity\UserOption $userOption */
        $userOption = $this->app->em()->find('XF:UserOption', $id);
        if (!$userOption)
        {
            return;
        }

        $this->reverseNotificationsOptOut($userOption);
        $userOption->save();
    }

    /**
     * @return \XF\Phrase
     */
    protected function getStatusType()
    {
        return \XF::phrase('users');
    }

    /**
     * @param UserOption $userOption
     */
    protected function reverseNotificationsOptOut(UserOption $userOption)
    {
        /** @var array $possibleAlertTypes */
        $possibleAlertTypes = $this->app->repository('XF:UserAlert')->getAlertOptOutActions();

        /** @var array $currentOptOut */
        $currentOptOut = $userOption->smodders_tgnotifications_optout;

        $newOptOut = array_filter($possibleAlertTypes, function ($val) use ($currentOptOut)
        {
            return !in_array($val, $currentOptOut);
        });

        $userOption->smodders_tgnotifications_optout = $newOptOut;
    }
}