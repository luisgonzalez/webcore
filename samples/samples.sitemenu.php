<?php
/**
 * @category Web: The Cool Site Menu
 * @tutorial Nice-looking, multilevel site menu.
 * @author Mario Di Vece <mario@unosquare.com>
 */

class SiteMenuSampleWidget extends WidgetBase
{
    public function __construct()
    {
        parent::__construct(__CLASS__);
        
        $menu = new SiteMenu('mainMenu');
        $menu->addMenuItem('Item 1 - drop');
        $menu->getMenuItems()->getLastItem()->addMenuItem('menu 1.1');
        $menu->getMenuItems()->getLastItem()->addMenuItem('menu 1.2');
        $menu->getMenuItems()->getLastItem()->addMenuItem('menu 1.3');
        $menu->getMenuItems()->getLastItem()->getMenuItems()->getLastItem()->addMenuItem('Hello! This is cool');
        $menu->getMenuItems()->getLastItem()->getMenuItems()->getLastItem()->addMenuItem('Hello! This is cool');
        $menu->getMenuItems()->getLastItem()->addMenuItem('menu 1.4');
        $menu->addMenuItem('Item 2 - drop');
        $menu->getMenuItems()->getLastItem()->addMenuItem('menu 2.1');
        $menu->getMenuItems()->getLastItem()->addMenuItem('menu 2.2');
        $menu->getMenuItems()->getLastItem()->getMenuItems()->getLastItem()->addMenuItem('Hello! This is cool');
        $menu->getMenuItems()->getLastItem()->addMenuItem('menu 2.3');
        $menu->getMenuItems()->getLastItem()->addMenuItem('menu 2.4');
        $menu->addMenuItem('Item 3 - drop');
        $menu->getMenuItems()->getLastItem()->addMenuItem('menu 3.1');
        $menu->getMenuItems()->getLastItem()->getMenuItems()->getLastItem()->addMenuItem('Hello! This is cool');
        $menu->getMenuItems()->getLastItem()->getMenuItems()->getLastItem()->addMenuItem('Hello! This is cool');
        $menu->getMenuItems()->getLastItem()->addMenuItem('menu 3.2');
        $menu->getMenuItems()->getLastItem()->addMenuItem('menu 3.3');
        $menu->getMenuItems()->getLastItem()->addMenuItem('menu 3.4');
        $menu->getMenuItems()->getLastItem()->getMenuItems()->getLastItem()->addMenuItem('Hello! This is cool');
        $this->model = $menu;
        
        $menuView   = new HtmlSiteMenuView($menu);
        $this->view = $menuView;
    }
    
    public static function createInstance()
    {
        return new SiteMenuSampleWidget();
    }
}

$sample = new SiteMenuSampleWidget();
?>