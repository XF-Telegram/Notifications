<?php

/**
 * This file is a part of [Telegram] Notifications.
 * All rights reserved.
 *
 * Developed by SourceModders.
 */

namespace SModders\TelegramNotifications;

use XF\AddOn\AbstractSetup;
use XF\AddOn\StepRunnerInstallTrait;
use XF\AddOn\StepRunnerUninstallTrait;
use XF\AddOn\StepRunnerUpgradeTrait;
use XF\Db\Schema\Alter;

class Setup extends AbstractSetup
{
    use StepRunnerInstallTrait;
    use StepRunnerUpgradeTrait;
    use StepRunnerUninstallTrait;
	
	public function installStep1()
    {
        $this->alterTable('xf_user_option', function (Alter $table)
        {
            $table->addColumn('smodders_tgnotifications_optout', 'text')
                ->comment('Comma-separated list of alerts from which the user has opted out. Example: \'post_like,user_trophy\'')
                ->after('push_optout');

            $table->addColumn('smodders_tgnotifications_on_conversation', 'bool')
                ->comment('Receive an Telegram upon receiving a conversation message')
                ->after('push_on_conversation');
        });
    }

    public function installStep2()
    {
        $this->applyGlobalPermission('smtgn', 'smtgn_use');
    }

    public function upgrade2000053Step1()
    {
        $this->app->jobManager()->enqueueUnique(
            'smtgn_reverseOptOut',
            'SModders\TelegramNotifications:UpgradeUserOptOut',
            [], false
        );
    }
    
    public function uninstallStep1()
    {
        $this->alterTable('xf_user_option', function (Alter $table)
        {
            $table->dropColumns(['smodders_tgnotifications_optout', 'smodders_tgnotifications_on_conversation']);
        });
    }
}