<?php

namespace Triangle\Controller;

!defined( 'WPINC ' ) or die;

/**
 * Initiate plugins
 *
 * @package    Triangle
 * @subpackage Triangle/Controller
 */

use Triangle\View;
use Triangle\Wordpress\Action;
use Triangle\Wordpress\MenuPage;
use Triangle\Wordpress\SubmenuPage;
use Triangle\Wordpress\Service;

use Parsedown;

class Page extends Base {

    /**
     * Admin constructor
     * @return void
     * @var    object   $plugin     Plugin configuration
     * @pattern prototype
     */
    public function __construct($plugin){
        parent::__construct($plugin);


        /** @backend - Add contact page to send an email */
        $action = new Action();
        $action->setComponent($this);
        $action->setHook('admin_menu');
        $action->setCallback('page_contact');
        $this->hooks[] = $action;

        /** @backend - Add custom admin page under settings */
        $action = new Action();
        $action->setComponent($this);
        $action->setHook('admin_menu');
        $action->setCallback('page_setting');
        $this->hooks[] = $action;

        /** @backend - Add template submenu link for template cpt */
        $action = clone $action;
        $action->setHook('admin_menu');
        $action->setCallback('link_email_template');
        $this->hooks[] = $action;
    }

    /**
     * Page Contact
     * @backend @submenu Triangle
     * @return  void
     */
    public function page_contact(){
        /** Handle submission */
        $menuSlug = strtolower(TRIANGLE_NAME);
        if(isset($_POST['field_menu_slug']) && $_POST['field_menu_slug']=='triangle-contact'){
            $this->loadController('EmailTemplate');
            $result = $this->EmailTemplate->send($_POST);
            $result = ($result) ? 'true' : 'false';
        }

        /** Set View */
        $view = new View();
        $view->setTemplate('default');
        $view->setOptions(['shortcode' => false]);
        $view->setSections(['Backend.contact' => ['name' => 'Contact', 'active' => true]]);
        $view->addData(['user_id' => (isset($_GET['user_id'])) ? $_GET['user_id'] : '']);
        $view->addData(['result' => isset($result) ? $result : '']);
        $view->addData(['title' => 'Contact User']);
        $view->addData(['background' => 'bg-carrot']);
        $view->addData(compact('menuSlug'));

        /** Set Main Page */
        $page = new MenuPage();
        $page->setPageTitle(TRIANGLE_NAME . ' Setting');
        $page->setMenuTitle(TRIANGLE_NAME);
        $page->setCapability('manage_options');
        $page->setMenuSlug($menuSlug);
        $page->setIconUrl('dashicons-email');
        $page->setView($view);
        $page->setPosition(90);
        $page->build();

        /** Set Page */
        $page = new SubmenuPage();
        $page->setParentSlug(strtolower(TRIANGLE_NAME));
        $page->setPageTitle('Contact User');
        $page->setMenuTitle('Contact');
        $page->setCapability('manage_options');
        $page->setFunction([$this, 'loadContent']);
        $page->setMenuSlug($menuSlug);
        $page->setView($view);
        $page->build();
    }

    /**
     * Page Setting
     * @backend @submenu setting
     * @return  void
     */
    public function page_setting(){
        /** Handle submission */
        $menuSlug = strtolower(TRIANGLE_NAME) . '-setting';
        if(isset($_POST['field_menu_slug']) && $_POST['field_menu_slug']=='triangle'){
            $this->loadController('Backend');
            $result = $this->Backend->saveSettings($_POST);
            $result = ($result) ? 'true' : 'false';
        }

        /** Set View */
        $view = new View();
        $view->setTemplate('default');
        $view->setOptions(['shortcode' => false]);
        $view->addData(compact('menuSlug'));
        $view->addData(['title' => 'Triangle']);
        $view->addData(['background' => 'bg-alizarin']);
        $view->addData(['result' => isset($result) ? $result : '']);
        $view->addData(['options' => [
            /** SMTP */
            'triangle_smtp' => Service::get_option('triangle_smtp'),
            'triangle_smtp_host' => Service::get_option('triangle_smtp_host'),
            'triangle_smtp_auth' => Service::get_option('triangle_smtp_auth'),
            'triangle_smtp_tls' => Service::get_option('triangle_smtp_tls'),
            'triangle_smtp_username' => Service::get_option('triangle_smtp_username'),
            'triangle_smtp_password' => Service::get_option('triangle_smtp_password'),
            /** Animation */
            'triangle_animation' => Service::get_option('triangle_animation'),
        ]]);
        $view->setSections([
            'Backend.setting.setting' => ['name' => 'Setting', 'active' => true],
            'Backend.setting.about' => ['name' => 'About']
        ]);

        /** Set Page */
        $page = new SubmenuPage();
        $page->setParentSlug(strtolower(TRIANGLE_NAME));
        $page->setPageTitle(TRIANGLE_NAME . ' Setting');
        $page->setMenuTitle('Setting');
        $page->setCapability('manage_options');
        $page->setMenuSlug($menuSlug);
        $page->setView($view);
        $page->build();
    }

    /**
     * Page Contact
     * @backend @submenu Triangle
     * @return  void
     */
    public function link_email_template(){
        $page = new SubmenuPage();
        $page->setParentSlug(strtolower(TRIANGLE_NAME));
        $page->setPageTitle('Email Template');
        $page->setMenuTitle('Template');
        $page->setCapability('manage_options');
        $page->setMenuSlug('edit.php?post_type=emailtemplate');
        $page->setFunction([]);
        $page->build();
    }

}