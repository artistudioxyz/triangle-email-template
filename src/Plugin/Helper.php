<?php

namespace Triangle;

!defined( 'WPINC ' ) or die;

/**
 * Helper library for Triangle plugins
 *
 * @package    Triangle
 * @subpackage Triangle\Includes
 */

class Helper {

    /**
     * Debug script
     * @return void
     */
    public function debug($data){
        echo '<pre>';
        var_dump($data);
        exit;
    }

    /**
     * Define const which will be used within the plugin
     * @param   object   $plugin     Wordpress plugin object
     * @return void
     */
    public function defineConst($plugin){
        define('TRIANGLE_NAME', $plugin->getName());
        define('TRIANGLE_VERSION', $plugin->getVersion());
        define('TRIANGLE_PRODUCTION', $plugin->isProduction());
    }

    /**
     * Get lists of directories
     * @return  void
     * @var     string  $path   Directory path
     */
    public function getDir($path, $directories = []) {
        foreach(glob($path.'/*', GLOB_ONLYDIR) as $dir) {
            $directories[] = basename($dir);
        }
        return $directories;
    }

    /**
     * Get files within directory
     * @return  void
     * @var     string  $dir   plugin hooks directory (Api, Controller)
     */
    public function getDirFiles($dir, &$results = array()) {
        $files = scandir($dir);
        foreach ($files as $key => $value) {
            $path = realpath($dir . DIRECTORY_SEPARATOR . $value);
            if (!is_dir($path)) {
                $results[] = $path;
            } else if ($value != "." && $value != "..") {
                self::getDirFiles($path, $results);
            }
        }
        return $results;
    }

    /**
     * Delete directories and files
     * @return void
     */
    public function deleteDir($dirPath) {
        if (! is_dir($dirPath)) {
            throw new InvalidArgumentException("$dirPath must be a directory");
        }
        if (substr($dirPath, strlen($dirPath) - 1, 1) != '/') {
            $dirPath .= '/';
        }
        $files = glob($dirPath . '*', GLOB_MARK);
        foreach ($files as $file) {
            if (is_dir($file)) {
                self::deleteDir($file);
            } else {
                unlink($file);
            }
        }
        rmdir($dirPath);
    }

    /**
     * Copy directories contents (Files and Dir) to another directories
     * @return void
     */
    public function copyDir($src,$dst) {
        $dir = opendir($src);
        @mkdir($dst);
        while(false !== ( $file = readdir($dir)) ) {
            if (( $file != '.' ) && ( $file != '..' )) {
                if ( is_dir($src . '/' . $file) ) {
                    $this->copyDir($src . '/' . $file,$dst . '/' . $file);
                } else { copy($src . '/' . $file,$dst . '/' . $file); }
            }
        }
        closedir($dir);
    }

    /**
     * Convert html relative path into absolute path
     * @var     string  $path   Wordpress base path
     * @var     string  $html   Html string
     * @return  void
     */
    public function convertImagesRelativetoAbsolutePath($path, $html){
        $pattern = "/<img([^>]*) " .
            "src=\"([^http|ftp|https][^\"]*)\"/";
        $replace = "<img\${1} src=\"" . $path . "\${2}\"";
        return preg_replace($pattern, $replace, $html);
    }

    /**
     * Extract templates from config files
     * @var     array   $config         Lists of config templates
     * @var     array   $templates      Lists of templates, to return
     */
    public function getTemplatesFromConfig($config, $templates = []){
        foreach($config as $template){
            foreach($template->children as $children){
                $templates[$children->id] = $children;
            }
        }
        return $templates;
    }

}