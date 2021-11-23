<?php

defined( 'ABSPATH' ) || exit;

/**
 * Class to work with  post cache
 *
 * @package default
 */
class TFCache {
    
    const sep=DIRECTORY_SEPARATOR;
    private static $cache_dir=null;
    public static $stopCache=false;

    /**
     * Start Caching
     *
     * @param string $tag
     * @param integer $post_id
     * @param array $args
     * @param integer $time
     *
     * return boolean
     */
    public static function start_cache($tag, $post_id = false, array $args = array(), $time = false) {//backward compatibility for addons
	return true;
    }

    public static function end_cache() {//backward compatibility for addons
    }

    /**
     * remove cache after some updates
     */
    public static function remove_cache($item_id = 'blog', $type =false, $blog_id = false) {
		
	static $queue = array();
	if(isset($queue['all'])){
		return true;
	}
	if ($item_id === 'all') {
	    $queue['all']=true;
	    return Themify_Filesystem::delete(self::get_cache_main_dir());
	}
	clearstatcache();
	$cache_dir= self::get_cache_blog_dir($blog_id);
	if (!isset($queue['blog']) && is_dir($cache_dir)) {
	    if ($item_id === 'blog') {
			$queue['blog']=true;
			return Themify_Filesystem::delete($cache_dir);
	    } 
	    else {
			if($type===false){
				$the_post = wp_is_post_revision($item_id);
				if ($the_post) {
				$item_id = $the_post;
				$the_post-null;
				}
				$post = get_post($item_id);
				if(empty($post)){
					return true;
				}
				if($type===false){
					$type=$post->post_type;
				}
				$post=null;
			}
			if(empty($type)){
				return self::remove_cache();
			}
			$k=$type.$item_id;
			if (!isset($queue[$k])) {
				$queue[$k] = true;
				$find=array(' post-' . $item_id);
				$item_id=(int)$item_id;
				$find[]=get_post_type($item_id)=== 'page' ? ' page-id-' . $item_id : ' postid-' . $item_id; //if there is any html associated with updated post  
				if($type==='comment' || $type==='term' || $type==='category'){
					$find[]=$type.'-' . $item_id;
					if($type!=='comment'){
						$find[]=$type==='category'?get_category_link($item_id):get_term_link($item_id);
						$temp = get_term( $item_id);
						$find[] = 'term-'.$temp->slug;
						$temp=null;
					}
				}
				set_time_limit(0);
				$type=$item_id=null;
				if(!self::clear_recursive($cache_dir, $find)){
					return self::remove_cache();
				}
			}
	    }
	}
	return true;
    }
    
    private static function clear_recursive($cache_dir,array $find){
		$dirHandle = opendir($cache_dir);
		if(empty($dirHandle)){
			return false;
		}
		clearstatcache();
		while($f = readdir($dirHandle)){
		    if($f!=='.' && $f!=='..'){
			$item = rtrim($cache_dir,self::sep) .self::sep.$f;
			if(is_dir($item)){
			   self::clear_recursive($item,$find);
			}
			elseif(strpos($item, '.html', 5) !== false && strpos($item, '.html.gz', 5)===false && is_file($item)){
			    $content = file_get_contents($item, FALSE, NULL, 2000);
			    if (!empty($content)){
					foreach($find as $v){
						if (strpos($content, $v, 10) !== false) {
							unlink($item);
							if(is_file($item.'.gz')){
								unlink($item.'.gz');
							}
							break;
						}
					}
			    }
			    $content = null;
			}
		    }
		}
		closedir($dirHandle);
		$dirHandle=null;
		return true;
    }

    /**
     * init hooks to update cache
     */
    public static function hooks() {
		add_action('save_post', array(__CLASS__, 'save'), 100,3);
		add_action('deleted_post', array(__CLASS__, 'save'), 100,1);
		add_action('comment_post', array(__CLASS__, 'comment_update'), 100, 2);
		add_action('deleted_comment', array(__CLASS__, 'comment_update'), 100, 2);
		add_action('wp_update_nav_menu', array(__CLASS__, 'menu_update'), 100);
		add_action('wp_update_nav_menu_item', array(__CLASS__, 'menu_update'), 100);
		add_action('activated_plugin', array(__CLASS__, 'plugin_active_deactive'), 100, 2);
		add_action('deactivated_plugin', array(__CLASS__, 'plugin_active_deactive'), 100, 2);
		add_action('admin_footer',array(__CLASS__,'admin_check'));
		add_action('wp_ajax_themify_write_config',array(__CLASS__,'ajax_write_wp_cache'));
		add_action('customize_save_after', array(__CLASS__, 'customizer'));
		add_action('switch_theme', array(__CLASS__, 'disable_cache'), 5);
		
		
		add_action('edit_term',array(__CLASS__, 'edit_term'), 100,3);
		add_action('delete_term_taxonomy',array(__CLASS__, 'edit_term'), 100,1);	
		
		add_action('check_ajax_referer',array(__CLASS__, 'widget_update'),100,2);//for widgets order,there is no hook
		
		
		$metas = array('post', 'comment','term', 'user');
		foreach ($metas as $m) {
			if($m!=='term' && $m!=='user'){
				add_action('added_' . $m . '_meta', array(__CLASS__, 'meta_update'), 100, 4);
			}
			add_action('updated_' . $m . '_meta', array(__CLASS__, 'meta_update'), 100, 4);
			add_action('deleted_' . $m . '_meta', array(__CLASS__, 'meta_update'), 100, 4);
		}
		if(!themify_isDevMode() && is_user_logged_in()){
			add_action('admin_bar_menu', array(__CLASS__, 'cache_menu'), 100);
			if(isset($_GET['tf-cache']) && $_GET['tf-cache']==='2' && current_user_can('manage_options')){
				add_action(is_admin()?'admin_head':'wp_head', array(__CLASS__, 'clear_css_cache'), 1);
			}
		}
        add_action( 'upgrader_process_complete', array(__CLASS__, 'themify_updated'),10, 2);
    }

    /**
     * comment update
     */
    public static function comment_update($comment_ID, $comment_approved) {
		$comment = get_comment($comment_ID);
		if (!empty($comment)) {
			self::remove_cache($comment->comment_post_ID,'comment');
		}
    }

    /**
     * plugin activatiion/deactivation
     */
    public static function plugin_active_deactive($plugin, $network_wide) {
		$type = $network_wide ? 'all' : 'blog';
		self::remove_cache($type);
    }

    /**
     * menu update
     */
    public static function menu_update($_menu_id) {
		themify_clear_menu_cache();
		remove_action('wp_update_nav_menu', array(__CLASS__, 'menu_update'), 100);
		remove_action('wp_update_nav_menu_item', array(__CLASS__, 'menu_update'), 100);
		self::remove_cache();
    }

    public static function customizer( $manager ) {
		if ( empty( $manager ) ) {
			return;
		}

		$post_id = $manager->changeset_post_id();
		if ( ! empty( $post_id ) ) {
		    self::remove_cache( $post_id );
		}
    }
	
	
	public static function edit_term( $term, $tt_id=null, $taxonomy=null){
		if(empty($taxonomy)){
			$temp=get_term($term);
			$taxonomy=$temp->taxonomy;
			$temp=null;
		}
		$type=$taxonomy==='category'?'category':'term';
		self::remove_cache($term,$type);
	}

    /**
     * meta update
     */
    public static function meta_update($meta_id, $post_id, $meta_key, $meta_value) {
		if (!empty($post_id)) {
		    $actions =  explode('_',current_action());
		    self::remove_cache($post_id,$actions[1]);
		}
    }
	
	public static function widget_update($action, $result){
		if($result!==false && $action==='save-sidebar-widgets'){
			self::remove_cache();
		}
	}

    public static function save($post_id,$post=false,$update=true) {
		if($update || current_action()==='deleted_post'){
			self::remove_cache($post_id);
		}
		elseif(!is_object($post) || $post->post_status!=='auto-draft'){
			self::remove_cache();
		}
    }
	
	public static function get_current_url(){
		if(empty($_SERVER['REQUEST_URI']) || empty($_SERVER['HTTP_HOST'])){
			return '';
		}
		$protocol=is_ssl() ? 'https://' : 'http://';
		return $protocol . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
	}

    /**
     * will be called in advanced-cache.php before wp full core load,a lot of functions from wp api and FW functions are't available in in this function be carefull!
     */
    public static function run(){
		if(self::$stopCache===true){
		    return;
		}
		self::$cache_dir=self::get_cache_main_dir();
		if(is_dir(self::$cache_dir) || mkdir(self::$cache_dir)){
			$isMulti=is_multisite();
			if($isMulti!==false){
				self::$cache_dir=self::get_cache_blog_dir();
			}	
			if($isMulti===false || is_dir(self::$cache_dir) || mkdir(self::$cache_dir,0777, true)){
				if(defined('TF_CACHE_RULES') && TF_CACHE_RULES){
					$ignore=explode('|TF|',TF_CACHE_RULES);
					if(!empty($ignore)){
						$request=$_SERVER['REQUEST_URI'];
						$server=is_ssl() ? 'https://' : 'http://';
						$server.=$_SERVER['HTTP_HOST'];
						$del='~';
						foreach($ignore as $r){
							$r=str_replace($server,'',$r);
							$p=$del.$r.$del;
							if(preg_match($p,$request)){
								self::$cache_dir=null;
								return;
							}
						}
						$request=$server=$ignore=null;
					}
				}
				$dir=null;
				self::$cache_dir=self::get_current_cache('',true);
				if(self::is_safari()){
					self::$cache_dir.='_safari';
				}
				self::$cache_dir.='.html';
				if(is_file(self::$cache_dir)){
					$ftime=filemtime(self::$cache_dir);
					$expire=defined('TF_CACHE_TIME') && TF_CACHE_TIME?(TF_CACHE_TIME*60):WEEK_IN_SECONDS;
					$liveTime=$expire+$ftime;	
					if($liveTime>time()){
						clearstatcache();
						if(is_file(self::$cache_dir)){//maybe another proccess has already removed it?
							$type = false;//self::get_available_gzip();temprorary disable gzip caching,because bug of cloudfare
							if($type!==false && is_file(self::$cache_dir.'.gz')){
								$type=key($type);
								if(isset( $_SERVER['HTTP_ACCEPT_ENCODING']) && strpos( $_SERVER['HTTP_ACCEPT_ENCODING'],$type)!==false){
									self::$cache_dir.='.gz';
									header('Content-Encoding:'.$type);
								}
							}
							
							header('Cache-Control:no-cache, must-revalidate, max-age=0');
							header('Content-Type:text/html;charset=UTF-8');
						//	header('Content-Length: '.filesize(self::$cache_dir));//temprorary disable,because when cd of cloudfare is enabled it will return compress brottil size 
							
							header('Last-Modified:' . gmdate('D, d M Y H:i:s', $ftime) . ' GMT' );
							header('Expires:'.gmdate('D, d M Y H:i:s', $liveTime).'GMT');
							readfile (self::$cache_dir);
							die;
						}
					}
					else{
						unlink(self::$cache_dir);
						if(is_file(self::$cache_dir.'.gz')){
							unlink(self::$cache_dir.'.gz');
						}
					}
				}
				add_filter( 'template_include', array( __CLASS__, 'template_include' ), -9999999 );
			}
			else{
				self::$cache_dir=null;
			}
		}
		else{
			self::$cache_dir=null;
		}
    }
    
    public static function get_current_cache($request='',$create_dir=false){
		if($request===''){
			$request=self::get_current_url();
		}
		return self::get_cache_folder($request,$create_dir).md5($request); 
    }
	
	public static function is_safari(){
		return isset($_SERVER['HTTP_USER_AGENT']) && strpos($_SERVER['HTTP_USER_AGENT'], 'Chrome')===false && strpos($_SERVER['HTTP_USER_AGENT'], 'Safari')!==false;
	}

	/**
	 * Initiate cache, just before page renders on frontend
	 *
	 * Hooked to "template_include"[0]
	 */
	public static function template_include( $template ) {
		if(!themify_isDevMode()){
			self::tf_cache_start();
		}
		return $template;
	}
   
    private static function tf_cache_start() {
		global $post;
		if(self::$stopCache===true || (isset($post,$post->post_password) && $post->post_password!=='') || is_user_logged_in() || is_admin() || self::$cache_dir===null  || is_404() || is_search() || themify_is_ajax() || post_password_required() || is_trackback() || is_robots() || is_preview() || is_customize_preview() || themify_is_login_page()|| (themify_is_woocommerce_active() && (is_checkout() || is_cart() || is_account_page()))){
			return;
		}
		if(defined('TF_CACHE_IGNORE') && TF_CACHE_IGNORE){
			$ignore=explode(',',trim(TF_CACHE_IGNORE));
			if(!empty($ignore)){
				foreach($ignore as $f){
					if(($f==='is_shop' && themify_is_shop()) || ($f!=='is_shop' && is_callable($f) && call_user_func($f))){
						return;
					}
				}
			}
			$ignore=null;
		}
		if(false !== self::get_cache_plugins()){
			self::disable_cache();
			return;
		}
		define('TF_CACHE',true);
		ob_start();
		add_action('wp_footer',array(__CLASS__,'tf_body_end'),9999999);
    }
	
	public static function tf_body_end(){
		add_action('shutdown',array(__CLASS__,'tf_cache_end'),0);
	}
	
    public static function tf_cache_end(){
		$html=ob_get_clean();
		if(!empty($html)){
			$html=preg_replace(array(
				'/\>[^\S ]+/s', // remove whitespaces after tags
				'/[^\S ]+\</s', // remove whitespaces before tags
				'/([\t ])+/s',//shorten multiple whitespace sequences; keep new-line characters because they matter in JS!!!
				'/\>[\r\n\t ]+\</s',//remove empty lines (between HTML tags); cannot remove just any line-end characters because in inline JS they can matter!
			), array('>','<',' ','><'), $html);
			echo $html;
			if(self::$stopCache===false){
				clearstatcache();
				$dir=rtrim(pathinfo(self::$cache_dir,PATHINFO_DIRNAME),self::sep).self::sep;  
				if((is_dir($dir) || mkdir($dir,0777, true)) && (file_put_contents(self::$cache_dir,$html,LOCK_EX) && themify_check('setting-cache_gzip',true))){
					$func =self::get_available_gzip();
					if($func!==false){
						$func=current($func);
						$html=call_user_func($func['f'],$html,$func['l']);
						$func=null;
						if(!empty($html)){
							file_put_contents(self::$cache_dir.'.gz',$html,LOCK_EX);
						}
					}
				}
			}
		}
    }
	
	private  static function get_available_gzip(){
		if(function_exists('brotli_compress') && ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') || (isset($_SERVER['SERVER_PORT']) && (int) $_SERVER['SERVER_PORT'] === 443))){
			return array('br'=>array('f'=>'brotli_compress','l'=>10));
		}
		if(function_exists('gzdeflate')){
			return array('deflate'=>array('f'=>'gzdeflate','l'=>8));
		}
		if(function_exists('gzcompress')){
			return array('deflate'=>array('f'=>'gzcompress','l'=>8));
		}
		if(function_exists('gzencode')){
			return array('gzip'=>array('f'=>'gzencode','l'=>8));
		}
		return false;
	}
    
    public static function create_config(array $data){
	$cache_dir=self::get_wp_content_dir();
	if(Themify_Filesystem::is_writable($cache_dir)){
	    if(!empty($data['setting-cache-html']) && false===self::get_cache_plugins()){
		$fw_dir=THEMIFY_DIR .self::sep.'cache'.self::sep;
		$fw_config=$fw_dir.'config.php';
		$cache_config=self::get_cache_config_file();
		$msg=sprintf(__('Can`t copy %s to %s. Please check permission or do manually it.','themify'),$fw_config,$cache_config);
		if(Themify_Filesystem::is_file($cache_config)){
		    include_once $cache_config;
		}
		$rules='';
		if(!empty($data['setting-cache-rule'])){
			$rules=explode(PHP_EOL,$data['setting-cache-rule']);
			foreach($rules as $i=>$r){
				$rules[$i]=trim(str_replace(array('"',"'"),'',$r));
				if(empty($rules[$i])){
					unset($rules[$i]);
				}
			}
			$rules=!empty($rules)?implode('|TF|',$rules):'';
		}
		$config=array(
		    '#TF_CACHE_FW#'=>trailingslashit($fw_dir),
		    '#TF_CACHE_TIME#'=>!empty($data['setting-cache-live'])?((int)$data['setting-cache-live']):WEEK_IN_SECONDS,
		    '#TF_CACHE_RULES#'=>$rules,
		    '#TF_CACHE_IGNORE#'=>''
		);
		$rules=null;
		$ignores=array();
		foreach($data as $k=>$v){
		    if(strpos($k,'setting-cache-ignore_')===0 && !empty($v)){
			$ignores[]=$v;
		    }
		}
		if(!empty($ignores)){
		    $config['#TF_CACHE_IGNORE#']=implode(',',$ignores);
		}
		$ignores=$data=null;
		$hasUpdate=(!defined('TF_CACHE_FW') || TF_CACHE_FW!==$config['#TF_CACHE_FW#']) || (!defined('TF_CACHE_RULES') || $config['#TF_CACHE_RULES#']!==TF_CACHE_RULES) || (!defined('TF_CACHE_IGNORE') || $config['#TF_CACHE_IGNORE#']!==TF_CACHE_IGNORE) || (!defined('TF_CACHE_TIME') || $config['#TF_CACHE_TIME#']!=TF_CACHE_TIME);
		if($hasUpdate===true){
		    if(!copy($fw_config, $cache_config)){
				self::disable_cache();
				return $msg;
		    }
		    $content=Themify_Filesystem::get_contents($cache_config);
		    if(empty($content)){
				self::disable_cache();
				return $msg;
		    }
		    if(!file_put_contents($cache_config, str_replace(array_keys($config),$config,$content),LOCK_EX)){
				self::disable_cache();
				return false;
		    }
		    $content=null;
		}
		$copy=true;
		if(Themify_Filesystem::is_file($cache_dir.'advanced-cache.php')){
		    $content=Themify_Filesystem::get_contents($cache_dir.'advanced-cache.php');
		    $copy=empty($content) || strpos($content,'class-themify-cache.php',10)===false;
		}
		if($copy===true && !copy($fw_dir.'advanced-cache.php', $cache_dir.'advanced-cache.php')){
		    unlink($cache_config);
			self::disable_cache();
		    return sprintf(__('Can`t copy %s to %s. Please check permission or do manually it.','themify'),$fw_dir.'advanced-cache.php',$cache_dir.'advanced-cache.php');
		}
			return self::write_wp_config();
	    }
	    else{
			self::disable_cache();
			return __('Themify Cache can not be enabled due to another cache plugin is activated.','themify');
	    }
	}
	else{
	    self::disable_cache();
	    return sprintf(__('Folder %s isn`t writable.Please check permission to allow write cache.','themify'),$cache_dir);
	}
	
    }
    
    public static function get_wp_content_dir(){
	return rtrim(WP_CONTENT_DIR,self::sep).self::sep;
    }
    
    public static function get_cache_main_dir(){
	return self::get_wp_content_dir().'tf_cache'.self::sep;
    }
    
    public static function get_cache_blog_dir($blog_id=false){
		$dir=self::get_cache_main_dir();
		if(is_multisite()){
			if($blog_id===false){
				static $bid=null;
				if($bid===null){
					$bid=get_current_blog_id();
				}
				$dir.=$bid.self::sep;
			}
			else{
				$dir .= $blog_id.self::sep;
			}
		}
		return $dir;
    }
    
     public static function get_cache_folder($request,$create=false){
		$dir=explode('?',$request);
		$dir=$dir[0];
		if($dir!=='/'){
			$dir = trim($dir,'/');
			//group the files in directory by the pre last slash(e.g /blog/slug return blog,2014/06/09/slug return 2014/06/09/)
			if(is_multisite()){
				$domain=apply_filters( 'site_url', get_option( 'siteurl' ), '', null,null );
			}
			else{
				$domain=parse_url($dir);
				$domain=$domain['host'];
			}
			$scheme=is_ssl() ? 'https' : 'http';
			$domain=str_replace(array('https:','http:'),'',trim($domain));
			$domain = $scheme . '://'.trim(ltrim($domain,'//'));
			$domain=trim(strtr($dir,array($domain=>'')),'/');
			if($domain===''){
				$dir='/';
			}
			else{
				if(strpos($domain,'/')!==false){
					$domain=explode('/',$domain);
					array_pop($domain);
					$dir=implode('/', $domain);
				}
				else{
					$dir=$domain;
				}
				
			}
			$domain=null;
		}
		$blog_dir=self::get_cache_blog_dir().md5($dir);
		if($create===true && !is_dir($blog_dir)){
			mkdir($blog_dir,0777, true);
		}
		return $blog_dir.self::sep;
    }
    
    public static function admin_check(){
		if(false !== self::get_cache_plugins()){
			self::disable_cache();
		}
    }
    
    public static function disable_cache(){
		$cache_dir=self::get_wp_content_dir();
		$config_f=self::get_cache_config_file();
		if(Themify_Filesystem::is_file($config_f)){
			 unlink($config_f);
		}
		if(!is_multisite()){
			$config_f=$cache_dir.'advanced-cache.php';
			$remove=false;
			if(Themify_Filesystem::is_file($config_f)){
				$content=Themify_Filesystem::get_contents($config_f);
				$remove=!empty($content) && strpos($content,'class-themify-cache.php',10)!==false;
			}
			else{
				$remove=true;
			}
			if($remove===true){//only when advanced-cache.php belongs to us or file doesn't exist try to disable WP_CACHE
				if(WP_CACHE){
				$wp_config=ABSPATH . 'wp-config.php';
				if(Themify_Filesystem::is_writable($wp_config )){
					$content=Themify_Filesystem::get_contents($wp_config);
					if(!empty($content)){
					$content=str_replace(array(self::get_replace_str(),"define('WP_CACHE',true);"),'', $content); 
						if(strpos($content,'WP_CACHE',5)!==false){//try again
							$content=preg_replace('/define/', self::get_replace_str().PHP_EOL.PHP_EOL.'define', $content, 1); 
						}
						if(!file_put_contents($wp_config, $content,LOCK_EX)){
							$remove=false;
						}
					}
				}
				else{
					$remove=false;//otherwise will give error file doesn't exist,it's safe to keep it
				}
				}
				if($remove===true && Themify_Filesystem::is_file($config_f)){
					unlink($config_f);
				}
			}
		}
    }
    
    private static function get_replace_str(){
	$replace='/* Themify Cache Start */'.PHP_EOL;
	$replace.="define('WP_CACHE',true);";
	$replace.=PHP_EOL.'/* Themify Cache End */';
	return $replace;
    }
    
    public static function ajax_write_wp_cache(){
	check_ajax_referer('ajax-nonce', 'nonce');
	if(!empty($_POST['data'])){
	    $data = themify_normalize_save_data($_POST['data']);
	    $msg=self::create_config($data);
	    if($msg===true){
		die(json_encode(array('remove_after'=>1)));
	    }
	    die(json_encode(array('error'=>$msg)));
	}
	die;
    }
   
    public static function write_wp_config(){
	$cache_dir=self::get_wp_content_dir();
	if(!WP_CACHE){
	    $wp_config=ABSPATH . 'wp-config.php';
	    if(Themify_Filesystem::is_writable($wp_config )){
			if(Themify_Filesystem::is_file(self::get_cache_config_file()) && Themify_Filesystem::is_file($cache_dir.'advanced-cache.php')){
				$content=Themify_Filesystem::get_contents($wp_config);
				$str=self::get_replace_str();
				if(!empty($content) && strpos($content,$str,3)===false){
				$content=preg_replace('/define/', $str.PHP_EOL.PHP_EOL.'define', $content, 1); 
				if(file_put_contents($wp_config, $content,LOCK_EX)){
					return true;
				}
				}
			}
	    }
	    else{
			return sprintf(__('File %s is`t writable. Please add %s %s.','themify'),$wp_config,"define('WP_CACHE',true)",$wp_config);
	    }
	}
	elseif(!Themify_Filesystem::is_file(self::get_cache_config_file())){
	    self::disable_cache();
	    return false;
	}
	return true;
    }
	
	
    public static function get_cache_config_file(){
	    $fname='site';
	    if(is_multisite()){
		$fname.='-'.get_current_blog_id();
	    }
	    $fname.='.php';
	    $dir=self::get_wp_content_dir().'tf_cache_config';
	    if(!is_dir($dir)){
		mkdir($dir,0777, true);
	    }
	    return $dir.self::sep.$fname;
    }
	
	public static function cache_menu($wp_admin_bar){
	    $is_admin=is_admin();
	    $can_manage_options = current_user_can('manage_options');
	    if(!$is_admin){
            $type='post';
            if(!is_date()){
                if (themify_is_shop()) {
                    $post_id = themify_shop_pageId();
                }
                else {
                    $p = get_queried_object();
                    if(is_archive() || is_post_type_archive()){
                        $type='term';
                        $post_id = isset($p->term_id) ? (int)$p->term_id : false;
                    }
                    elseif(is_singular()){
                        $post_id = isset($p->ID) ? (int)$p->ID : false;
                    }
                    $p=null;
                }
                if(!empty($post_id) && !current_user_can('edit_'.$type, $post_id)){
                    return;
                }elseif(empty($post_id)){
                    $is_admin=true;
                }
            }
        }
		elseif(!$can_manage_options){
	        return;
        }
		$link=remove_query_arg('tf-cache',self::get_current_url());
		$args = array(
			array(
				'id'=>'tf_clear_cache',
				'title'=>__('Themify Cache','themify')
			)
		);
		$hasCache=$hasMenuCache=false;
		$cache_plugins=false !== self::get_cache_plugins();
		$hasCache=WP_CACHE && $cache_plugins===false && Themify_Filesystem::is_file(self::get_cache_config_file());
		if(!$is_admin){
            $args[]=array(
                'id'=>'tf_clear_html',
                'parent'=>'tf_clear_cache',
                'href'=>add_query_arg(array('tf-cache'=>1),$link),
                'title'=>$hasCache===true?__('Purge Page Cache','themify'):__('Regenerate Page CSS','themify')
            );
        }
		if($can_manage_options){
            $args[]=array(
                'id'=>'tf_clear_all',
                'parent'=>'tf_clear_cache',
                'href'=>add_query_arg(array('tf-cache'=>2),$link),
                'title'=>$hasCache===true?__('Purge All Cache','themify'):__('Regenerate All CSS','themify')
            );
            if($hasCache===false && $cache_plugins===false && !themify_check('setting-cache-menu',true)){
                $hasMenuCache=true;
                $args[]=array(
                    'id'=>'tf_clear_menu',
                    'parent'=>'tf_clear_cache',
                    'href'=>add_query_arg(array('tf-cache'=>3),$link),
                    'title'=>__('Clear Menu Cache','themify')
                );
            }
        }
		$cache_plugins=null;
		foreach ($args as $arg) {
		    $wp_admin_bar->add_node($arg);
		}
		if(!empty($_GET['tf-cache'])){
			$cache_type=(int)$_GET['tf-cache'];
			
			if($cache_type===3 && $hasMenuCache===true){
				themify_clear_menu_cache();
			}
			elseif($cache_type===1){
				add_filter('themify_concate_css','__return_false');
				if($hasCache===true){
					$link = self::get_current_cache($link);
					if(Themify_Filesystem::is_file($link .'_safari.html')){
						unlink($link .'_safari.html');
					}
					if(Themify_Filesystem::is_file($link .'_safari.html.gz')){
						unlink($link .'_safari.html.gz');
					}
					$link.='.html';
					if(Themify_Filesystem::is_file($link )){
						unlink($link);
					}
					$link.='.gz';
					if(Themify_Filesystem::is_file($link)){
						unlink($link);
					}
				}
				themify_clear_menu_cache();
			}
		}
	}
	
	public static function clear_css_cache(){
        Themify_Enqueue_Assets::clearConcateCss();
        themify_clear_menu_cache();
	}

	public static function clear_3rd_plugins_cache($post_id=0){
	    $cache_plugins=self::get_cache_plugins('others');
	    if(false===$cache_plugins){
	        return;
        }
        $post_id=(int)$post_id<=0?0:(int)$post_id;
	    // Sometimes we need to clear all caches ex. when Pro template or Layout Part is edited
	    if($post_id>0){
	        $type=get_post_type($post_id);
	        $post_id='post'===$type || 'page'===$type?$post_id:0;
        }
        foreach($cache_plugins as $k=>$v){
            switch ( $k ) {
                case 'SC':
					if ( $post_id > 0 && function_exists( 'wp_cache_post_change' ) ) {
						wp_cache_post_change( $post_id );
					} elseif ( $post_id === 0 && function_exists( 'wp_cache_clear_cache' ) ) {
						wp_cache_clear_cache();
					}
                    break;
                case 'W3TC':
                    if($post_id>0 && function_exists( 'w3tc_flush_post' )){
                        w3tc_flush_post($post_id);
                    }elseif ($post_id===0 && function_exists( 'w3tc_flush_all' ) ) {
                        w3tc_flush_all();
                    }
                    break;
                case 'WPFC':
                    if($post_id>0 && function_exists( 'wpfc_clear_post_cache_by_id' )){
                        wpfc_clear_post_cache_by_id($post_id);
                    }else if($post_id===0 && function_exists( 'wpfc_clear_all_cache' )){
                        wpfc_clear_all_cache(true);
                    }
                    break;
                case 'AO':
                    if ( 0===$post_id && class_exists( 'autoptimizeCache' ) && method_exists( 'autoptimizeCache', 'clearall') ) {
                        autoptimizeCache::clearall();
                    }
                    break;
                case 'WPO':
                    if($post_id>0 && class_exists( 'WPO_Page_Cache' ) && method_exists( 'WPO_Page_Cache', 'delete_single_post_cache')){
                        WPO_Page_Cache::delete_single_post_cache($post_id);
                    }else if($post_id===0 && class_exists( 'WP_Optimize' ) && method_exists( 'WP_Optimize', 'get_page_cache')){
                        WP_Optimize()->get_page_cache()->purge();
                    }
                    break;
                case 'LSCWP':
                    if($post_id>0 && has_action( 'litespeed_purge_post' )){
                        do_action( 'litespeed_purge_post', $post_id );
                    }else if($post_id===0 && has_action( 'litespeed_purge_all' )){
                        do_action( 'litespeed_purge_all' );
                    }
                    break;
                case 'WPHB':
                    if ( has_action('wphb_clear_page_cache') ) {
                        if($post_id>0){
                            do_action( 'wphb_clear_page_cache', $post_id );
                        }else{
                            do_action( 'wphb_clear_page_cache' );
                        }
                    }
                    break;
                case 'CLFL':
                    // Cloudflare use this hook to purge the cache
                    if (0 === $post_id && has_action('autoptimize_action_cachepurged') ) {
                        do_action('autoptimize_action_cachepurged');
                    }
                    break;
                case 'SGO':
                    if (function_exists('sg_cachepress_purge_cache')) {
                        $post_id=$post_id>0?get_permalink($post_id):false;
                        if($post_id!==false){
                            sg_cachepress_purge_cache($post_id);
                        }else{
                            sg_cachepress_purge_cache();
                        }
                    }
                    break;
                case 'Breeze':
                    if($post_id===0 && class_exists('Breeze_Admin') && method_exists( 'Breeze_Admin', 'breeze_clear_all_cache')){
                        $breeze=new Breeze_Admin();
                        $breeze->breeze_clear_all_cache();
                        $breeze=null;
                    }
                    break;
                case 'ROCKET':
                    if (0===$post_id && function_exists('rocket_clean_domain')) {
                        rocket_clean_domain();
                    }elseif($post_id>0 && function_exists('rocket_clean_post')){
                        rocket_clean_post( $post_id );
                    }
                    break;
                case 'CE':
                    if (0===$post_id && has_action('cache_enabler_clear_complete_cache')) {
                        do_action('cache_enabler_clear_complete_cache');
                    }elseif($post_id>0 && has_action('cache_enabler_clear_site_cache_by_blog_id')){
                        do_action( 'cache_enabler_clear_site_cache_by_blog_id', $post_id );
                    }
                    break;
                case 'Cachify':
                    if (0===$post_id && has_action('cachify_flush_cache')) {
                        do_action('cachify_flush_cache');
                    }elseif($post_id>0 && has_action('cachify_remove_post_cache')){
                        do_action( 'cachify_remove_post_cache', $post_id );
                    }
                    break;
            }
        }
    }

    public static function get_cache_plugins($slug='others'){
	    static $items=null;
	    if($items===null){
			$items=array();
			//W3 Total Cache plugin
			if(defined( 'W3TC' ) ){
				$items['W3TC']=true;
			}
			//WP Super Cache
			if(function_exists('wp_cache_clear_cache')){
				$items['SC']=true;
			}
			//Hyper Cache
			if (function_exists('hyper_cache_invalidate')) {
				$items['HYPER']=true;
			}
			//Fastest Cache
			if (class_exists('WpFastestCache')) {
				$items['WPFC']=true;
			}
			//WP Rocket
			if (defined('WP_ROCKET_SLUG')) {
				$items['ROCKET']=true;
			}
			//wp-cloudflare-page-cache
			if (class_exists('SW_CLOUDFLARE_PAGECACHE')) {
				$items['SWCFPC']=true;
			}
			//WP-Optimiz
			if (class_exists('WP_Optimize') && method_exists('WP_Optimize','get_page_cache') && WP_Optimize()->get_page_cache()->is_enabled()) {
                $items['WPO']=true;
			}
			//LiteSpeed Cache
			if (defined('LSCWP_CONTENT_DIR')){
				$items['LSCWP']=true;
			}
			//Comet Cache
			if (class_exists('WebSharks\CometCache\Classes\ApiBase')) {
				$items['Comet']=true;
			}
			//Cache Enabler
			if (defined('CE_FILE')) {
				$items['CE']=true;
			}
			//Breeze
			if (defined('BREEZE_ROOT_DIR')) {
				$items['Breeze']=true;
			}
			//Hummingbird
			if (defined('WPHB_DIR_PATH')) {
				$items['WPHB']=true;
			}
			//WP Speed of Light
			if (defined('WPSOL_PLUGIN_URL')) {
				$items['WPSOL']=true;
			}
            //Auto optimize
            if ( class_exists( 'autoptimizeCache' ) ) {
                $items['AO']=true;
            }
            //Cloudflare
            if (defined('CLOUDFLARE_PLUGIN_DIR')) {
                $items['CLFL']=true;
            }
            //SG optimizer
            if (class_exists('Supercacher')) {
                $items['SGO']=true;
            }
            //Cachify
            if (defined('CACHIFY_FILE')) {
                $items['Cachify']=true;
            }
	    }
	    if($slug==='others'){
			unset($items['themify']);
		    return empty($items)?false:$items;
	    }
	    //themify cache
	    if(defined('TF_CACHE') && TF_CACHE){
			$items['themify']=true;
	    }
	    if($slug==='any'){
		    return !empty($items);
	    }
	    if($slug==='all'){
		    return $items;
	    }
	    return isset($items[$slug]);
    }

    public static function themify_updated($upgrader_object, $options){
        if ($options['action'] === 'update' ) {
            if($options['type'] === 'plugin' && defined('THEMIFY_BUILDER_SLUG')){
                if(isset($options['plugins'])){
                    foreach($options['plugins'] as $each_plugin) {
                        if ($each_plugin===THEMIFY_BUILDER_SLUG) {
                            self::clear_3rd_plugins_cache();
                            break;
                        }
                    }
                }
            }elseif($options['type'] === 'theme' && function_exists('themify_is_themify_theme') && themify_is_themify_theme()){
                self::clear_3rd_plugins_cache();
            }
        }

    }

}
add_action('after_setup_theme',array('TFCache','hooks'));
