<?php

/*
 * This file is part of the WeeklyOverviewBundle.
 * All rights reserved by Maximilian Groß (www.maximiliangross.de).
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KimaiPlugin\WeeklyOverviewBundle\EventSubscriber;

use App\Event\ConfigureMainMenuEvent;
use App\Utils\MenuItemModel;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class MenuSubscriber implements EventSubscriberInterface
{
    /**
     * @var AuthorizationCheckerInterface
     */
    private $security;

    /**
     * MenuSubscriber constructor.
     * @param AuthorizationCheckerInterface $security
     */
    public function __construct(AuthorizationCheckerInterface $security)
    {
        $this->security = $security;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents(): array
    {
        return [
            ConfigureMainMenuEvent::class => ['onMenuConfigure', 100],
        ];
    }

    /**
     * @param \App\Event\ConfigureMainMenuEvent $event
     */
    public function onMenuConfigure(ConfigureMainMenuEvent $event): void
    {
        $auth = $this->security;

        if (!$auth->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            return;
        }

        if (!$auth->isGranted('weekly_overview')) {
            return;
        }

        $menu = $event->getTimesheetMenu();

        $menu->addChild(
            new MenuItemModel('weekly_overview', 'WeeklyOverview', 'weekly_overview', [], 'fas fa-hdd')
        );
    }
}
