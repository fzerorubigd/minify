<?php

/**
 * class for minifying js/css files
 *
 * @author behrooz shabani <behrooz@rock.com>
 * @author fzerorubigd <fzerorubigd@gmail.com>
 * @copyright 2012 Authors
 * @license GPL3
 */

class Minifier
{
	/**
	 * base path of project
	 *
	 * @static
	 */
	public static $BASE_PATH = '';
	
	
	
	public static $MINIFY_PATH = '/min';
	
	/**
	 * array to hold js files
	 * 
	 * @access private
	 * @static
	 */
	static private $JS = array();
	
	/**
	 * List of stand alone js files, 
	 * @var array
	 */
	static private $standaloneJS = array ();
	
	/**
	 * inline JS blocks
	 * @var array
	 */
	 static private $inlineJS = array();
	 
	/**
	 * array to hold css files
	 * 
	 * @access private
	 * @static
	 */
	static private $CSS = array();
	
	/**
	 * inline CSS block
	 * 
	 * @var array
	 */
	 static private $inlineCSS = array();

     /**
      * Nested level for buffering
      */
     static private $nestedLevel = 0;
     
     /**
      * This class is loaded or not
      */
     static private $initialized = false;
     /**
      * Dummy function just to load Minify library
      */
     static public function initialize()
     {
         if (self::$initialized) {
             return;
         }
         self::$initialized = true;
         //Add Minify path to search path. so there is no need to strip require_once codes
         /**set_include_path(
             get_include_path() . PATH_SEPARATOR 
             realpath(__DIR__ . '/Minify')
             );*/
     }
	/**
	 * adds new js file to be loaded into page
	 * 
	 * @param string $url url of js file relative to public directory
	 * @static
	 */
	static public function addJS($js, $standalone = false){
		if (!$standalone)
		{
			if (!in_array($js, self::$JS))
				self::$JS[] = $js;
		}
		else
		{
			if (!in_array($js, self::$standaloneJS))
				self::$standaloneJS[] = $js;			
		}
	}

	/**
	 * removes existant js file(s) from list of js files
	 *
	 * @param string $path path of js file relative to public directory
	 * @static
	 */
	static public function removeJS($js){
		foreach ( self::$JS as $key => $addedJs )
			if ($addedJs == $js)
				unset(self::$JS[$key]);
		foreach ( self::$standaloneJS as $key => $addedJs )
			if ($addedJs == $js)
			unset(self::$standaloneJS[$key]);		
	}

	/**
	 * add new css file to be loaded into page
	 *
	 * @param string $url url of css file, relative to public directory
	 * @static
	 */
	static public function addCSS($css){
		if (!in_array($css, self::$CSS))
			self::$CSS[] = $css;
	}

	/**
	 * removes css file(s) from list of css files
	 *
	 * @param string $path relative path of file based on public directory
	 * @static
	 */
	static public function removeCSS($css){
		foreach ( self::$CSS as $key => $addedCss )
			if ($addedCss == $cssl)
			unset(self::$CSS[$key]);
	}

	/**
	 * generates <link> tag(s) to load css files
	 *
	 * @static
	 */
	static public function getCSSTag(){
		$result = PHP_EOL;
		foreach(self::$CSS as $file)
			$result .= "\t\t<link rel=\"stylesheet\" href=\"" . self::$BASE_PATH . '/' . $file  . "\" type=\"text/css\" />" . PHP_EOL;
        if (count(self::inlineCSS)) {
            $result .= "\t\t<style type=\"text/css\""> . implode(PHP_EOL . self::inlineCSS) . "</style>";
        }
		return $result;
	}

	/**
	 * generates <script> tag(s) to load js files
	 *
	 * @static
	 */
	static public function getJSTag(){
		$result = PHP_EOL;
		foreach(self::$JS as $file)
			$result .= "\t\t<script src=\"" . self::$BASE_PATH . '/' . $file . "\" type=\"text/javascript\"></script>" . PHP_EOL;
		foreach(self::$standaloneJS as $file)
			$result .= "\t\t<script src=\"" . self::$BASE_PATH . '/' . $file . "\" type=\"text/javascript\"></script>" . PHP_EOL;		
        if (count(self::inlineJS)) {
            $result .= "\t\t<script type=\"text/javascript\""> . implode(PHP_EOL . self::inlineJS) . "</script>";
        }

		return $result;
	}

	/**
	 * generates <link> tag(s) to load minified css files
	 *
	 * @static
	 */
	static public function getMinifiedCSSTag(){
        self::initialize();
		$files = array(); // f parameter of minifier
		foreach(self::$CSS as $file)
		{
			$files[] = $file;
		}
        $result = '';
		if(!empty($files)) {
            $result .= PHP_EOL . "\t\t<link rel=\"stylesheet\" href=\"" . self::$BASE_PATH . self::$MINIFY_PATH . '?f=' . implode(',', $files) . "\" type=\"text/css\" />" . PHP_EOL;
        }
        if (count(self::inlineCSS)) {
            require_once('Minify/CSS/Compressor.php');
            $tmp = Minify_CSS_Compressor::process(implode(PHP_EOL . self::inlineCSS));
            $result .= "\t\t<style type=\"text/css\""> . $tmp . "</style>";
        }
	}

	/**
	 * generates <script> tag(s) to load minified js files
	 *
	 * @static
	 */
	static public function getMinifiedJSTag()
    {
        self::initialize();
		$files = array(); // f parameter of minifier
		foreach(self::$JS as $file)
		{
			$files[] = $file;
		}
        $result = '';
		if(!empty($files)) {
            $result = PHP_EOL . "\t\t<script src=\"" . self::$BASE_PATH . self::$MINIFY_PATH . '?f=' . implode(',', $files) . "\" type=\"text/javascript\"></script>" . PHP_EOL;
        }
		foreach(self::$standaloneJS as $file)
			$result .= "\t\t<script src=\"" . self::$BASE_PATH . '/' . $file . "\" type=\"text/javascript\"></script>" . PHP_EOL;
        if (count(self::inlineJS)) {
            require_once("JSMin.php");
            $tmp = JSMin::min(implode(PHP_EOL . self::inlineJS));
            $result .= "\t\t<script type=\"text/javascript\""> . $tmp . "</script>";
        }
		return $result;
	}
	
    /**
     * start new CSS capture
     *
     */
	static public function cssCaptureStart() 
	{
        if (self::nestedLevel != 0) {
            throw new Exception('Capture already started.');
        }
        self::nestedLevel = 1;
        ob_start();
	}

    /**
     * end CSS capture
     *
     */

    static public function cssCaptureEnd() 
    {
        if (self::nestedLevel != 1) {
            throw new Exception("Captire not started");
        }
        self::nestedLevel = 0;
        $data = ob_get_clean();
        self::inlineCSS[] = $data;
        return $data;
    }

    /**
     * start new JS capture
     *
     */
	static public function jsCaptureStart() 
	{
        if (self::nestedLevel != 0) {
            throw new Exception('Capture already started.');
        }
        self::nestedLevel = 2;
        ob_start();
	}

    /**
     * end JS capture
     *
     */

    static public function jsCaptureEnd() 
    {
        if (self::nestedLevel != 2) {
            throw new Exception("Captire not started");
        }
        self::nestedLevel = 0;
        $data = ob_get_clean();
        self::inlineJS[] = $data;
        return $data;
    }
    
}

/** 
I hate global code :( But in this case its ok
**/

Xamin_Minifier::initialize();
