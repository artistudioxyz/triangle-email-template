<?php

namespace Triangle\Model;

!defined( 'WPINC ' ) or die;

/**
 * Initiate plugins
 *
 * @package    Triangle
 * @subpackage Triangle/Model
 */

use Triangle\Wordpress\Action;
use Triangle\Wordpress\Meta;
use Triangle\Wordpress\Service;
use Triangle\Wordpress\Type;

class EmailTemplate extends Model {

    /**
     * Emailtemplate constructor
     * @return void
     * @var    object $plugin Plugin configuration
     * @pattern prototype
     */
    public function __construct($plugin)
    {
        parent::__construct($plugin);

        /** @backend - Post_type : emailtemplate */
        $this->args['publicly_queryable'] = false;
        $this->args['has_archive'] = false;
        $this->args['show_in_menu'] = false;
        $this->args['labels'] = ['name' => 'Email Template'];
        $this->args['supports'] = ['title'];

        /** @backend - Meta_fields : Based on plugin config templates */
        $templates = $plugin->getConfig()->templates;
        foreach($templates as $template) {
            foreach ($template->children as $children) {
                $key = 'template_' . $children->id;
                $meta = new Meta();
                $meta->setType($this);
                $meta->setKey($key);
                $this->metas[$key] = $meta;
            }
        }

        /** @backend - Hooks - Emailtemplate save post hook */
        $action = new Action();
        $action->setComponent($this);
        $action->setHook('save_post');
        $action->setCallback('save_emailtemplate');
        $action->setAcceptedArgs(3);
        $this->hooks[] = $action;

        /** @backend - Hooks - Emailtemplate save post hook */
        $action = clone $action;
        $action->setHook('before_delete_post');
        $action->setCallback('delete_emailtemplate');
        $action->setAcceptedArgs(1);
        $this->hooks[] = $action;
    }

    /**
     * Save emailtemplate hook
     * 1. Save page script as meta fields
     * 2. Build email template script (html, css, js) file
     * @backend - @emailtemplate
     * @return  void
     * @var     int     $post_id    Post ID
     * @var     object  $post       Post Object
     * @var     bool    $update     Whether this is an existing post being updated or not.
     */
    public function save_emailtemplate($post_id, $post, $update){
        $pagenow = $this->Helper->getScreen()->pagenow;
        if (!empty($_POST) && $post->post_type=='emailtemplate' && in_array($pagenow, ['post.php', 'post-new.php'])){
            /** Load Options */
            $this->loadController('EmailTemplate');
            $this->ID = $post_id;
            $templates = $this->Plugin->getConfig()->templates;
            $templates = $this->Helper->getTemplatesFromConfig($templates);
            $options = [
                'builder_inliner' => Service::get_option('triangle_builder_inliner'),
            ];
            /** Save meta field */
            $html = ''; $css = '';
            foreach($this->metas as $meta){
                $key = $meta->getKey();
                $name = str_replace('template_','',$meta->getKey());
                if(!isset($_POST[$key])) continue;
                if($templates[$name]->mode=='ace/mode/html') $html .= $_POST[$key];
                elseif($templates[$name]->mode=='ace/mode/css') $css .= $_POST[$key];
                $meta->setValue($_POST[$key]);
                $results[] = $meta->update_post_meta();
            }
            /** Build template */
            if($options['builder_inliner']=='juice') $html = html_entity_decode($_POST['juice_output']);
            $this->EmailTemplate->buildEmailTemplate($post->post_name, $html, $css);
            $this->EmailTemplate->standardizeEmailTemplate($post->post_name);
        }
    }

    /**
     * Delete emailtemplate hook
     * @backend - @emailtemplate
     * @return  void
     * @var     int     $post_id    Post ID
     */
    public function delete_emailtemplate($post_id){
        $post = Type::get_post($post_id);
        if($post->post_type==$this->name){
            $slug = str_replace('__trashed','',strtolower($post->post_name));
            $path = unserialize(TRIANGLE_PATH);
            $dir = $path['upload_dir']['basedir'] . '/EmailTemplate/' . $slug;
            if(is_dir($dir)) $this->Helper->deleteDir($dir);
        }
    }

}