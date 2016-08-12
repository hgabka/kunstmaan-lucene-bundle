<?php

namespace Hgabka\LuceneBundle\Menu;

use Kunstmaan\AdminBundle\Helper\Menu\MenuAdaptorInterface;
use Kunstmaan\AdminBundle\Helper\Menu\MenuBuilder;
use Kunstmaan\AdminBundle\Helper\Menu\MenuItem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class SettingsMenuAdaptor implements MenuAdaptorInterface
{
    /**
     * @var AuthorizationCheckerInterface
     */
    protected $authorizationChecker;

    /**
     * @param AuthorizationCheckerInterface $authorizationChecker
     */
    public function __construct(AuthorizationCheckerInterface $authorizationChecker)
    {
        $this->authorizationChecker = $authorizationChecker;
    }

    public function adaptChildren(MenuBuilder $menu, array &$children, MenuItem $parent = null, Request $request = null)
    {
        if (!is_null($parent) && $parent->getUniqueId() === 'settings' && $this->authorizationChecker->isGranted('ROLE_SUPER_ADMIN')) {
            $menuItem = new MenuItem($menu);
            $menuItem
                ->setRoute('hgabka_lucene_bundle_settings_index')
                ->setUniqueId('lucene_index')
                ->setLabel('settings.lucene_index')
                ->setParent($parent)
            ;
            if (stripos($request->attributes->get('_route'), $menuItem->getRoute()) === 0) {
                $menuItem->setActive(true);
                $parent->setActive(true);
            }

            $children[] = $menuItem;
        }
    }
}
