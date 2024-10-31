<?php
/*
Plugin Name: Random Blog Description
Description: This is a plugin for random description,that is select a sentence with random in you like.
Author: z
Version: 1.0
Author URI: http://rndz.org/
*/

define('RNDESC',__FILE__.'.lst');

// 选择一个句子
function random_description($opt) {
	static $str = null;
	// 同一页面多次调用时返回相同的句子
	if($str === null){
		$str = file(RNDESC);
		$str = trim($str[array_rand($str)]);
	}
	return $str;
}

// 输出 Javascript 脚本，防止影响SEO
function random_description_footer(){
	echo '<script type="text/javascript">(function(){document.getElementById("site-description").innerHTML="'
	echo addslashes(random_description(null));
	echo '";})();</script>';
}

function random_description_option_form(){
	global $title;
	echo '<br>';
	if($_POST['sentence']){
		// 去除反斜线转意
		if(get_magic_quotes_gpc()){
			$sentences = stripslashes($_POST['sentence']);
		}else{
			$sentences = $_POST['sentence'];
		}

		// 保存选项
		update_option('random_description_format',$_POST['format']);
		file_put_contents(RNDESC,$sentences);

		echo '<div class="wrap"><div id="setting-error-settings_updated" class="updated settings-error"><p><strong>';
		echo __('Settings saved.');
		echo "</strong></p></div></div>";
	}
	echo '<div id="poststuff"><div class="postbox"><h3>';
	echo $title;
	echo '</h3><div class="inside less"><form method="post"><h4>';
	echo __('You like sentences');
	echo '</h4><textarea name="sentence" style="width:100%;height:200px;" wrap="off">';
	readfile(RNDESC);
	echo '</textarea><h4>';
	echo __('What is output format');
	echo '</h4>';
	foreach(array('Javascript','Direct') as $k => $v){
		echo '<label><input name="format" type="radio" value="';
		echo $k;
		echo $k==get_option('random_description_format')?'" checked':'"';
		echo ' />';
		echo $v;
		echo '</label><br />';
	}
	echo '<br /><input type="submit" value="';
	echo __('Save');
	echo '" class="button-primary" /></form></div></div></div>';
}

// 在插件菜单下添加菜单
function random_description_admin_menu(){
	add_submenu_page('plugins.php',__('Random Blog Description Settings'),__('Random Blog Description'),8,'RandomDescription',random_description_option_form);
}

// 添加链接到插件信息
function random_description_meta($links,$file){
	if($file==basename(__FILE__)){
		$links[] = '<a href="plugins.php?page=RandomDescription">' . __('Settings') . '</a>';
	}
	return $links;
}

// 禁用时删除选项
function random_description_deactivation(){
	delete_option('random_description_format');
}

// 启用时检查文件
function random_description_activation(){
	if(!file_exists(RNDESC)){
		file_put_contents(RNDESC,__('That is a random description'));
		touch(RNDESC);
	}
}

add_action('admin_menu',random_description_admin_menu);
add_filter('plugin_row_meta',random_description_meta,10,2);
register_activation_hook(__FILE__, random_description_activation);
register_deactivation_hook(__FILE__,random_description_deactivation);

// 后台管理员页面时不处理
if(is_admin()){

// 直接输出
}elseif(get_option('random_description_format')){
	add_filter('pre_option_blogdescription',random_description);
// 输出 Javascript
}else{
	add_action('wp_footer',random_description_footer);
}
