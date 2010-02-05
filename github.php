<?php
/*
Plugin Name: GitHub
Plugin URI: http://github.com/charroch/GitHub_wordpress_widget
Description: Minimalist GitHub plugin
Author: Carl Harroch
Version: 1
Author URI: http://novoda.com/
*/
add_action("widgets_init", array('GitHub', 'register'));
register_activation_hook( __FILE__, array('GitHub', 'activate'));
register_deactivation_hook( __FILE__, array('GitHub', 'deactivate'));
class GitHub {
    function activate(){
        $username = 'novoda';
        $title = 'Our Projects';
        if ( ! get_option('github_user')){
            add_option('github_user' , $username);
        } else {
            update_option('github_user' , $username);
        }
        if ( ! get_option('title')){
            add_option('title' , $title);
        } else {
            update_option('title' , $title);
        }
    }
    function deactivate(){
        delete_option('github_user');
        delete_option('title');
    }
    function control(){
        $username = get_option('github_user');
        $title = get_option('title');
        ?>
        <p><label>GitHub User: <input name="github_user" type="text" value="<?php echo $username; ?>" /></label></p>
        <p><label>Title: <input name="title" type="text" value="<?php echo $title; ?>" /></label></p>
        <?php
        if (isset($_POST['github_user'])){
            $username = attribute_escape($_POST['github_user']);
            update_option('github_user', $username);
        }
        if (isset($_POST['title'])){
            $title = attribute_escape($_POST['title']);
            update_option('title', $title);
        }
    }
    function widget($args){
        $curl_handle=curl_init();
        curl_setopt($curl_handle,CURLOPT_URL,'http://github.com/api/v2/json/repos/show/' . get_option('github_user'));
        curl_setopt($curl_handle,CURLOPT_CONNECTTIMEOUT,2);
        curl_setopt($curl_handle,CURLOPT_RETURNTRANSFER,1);
        $buffer = curl_exec($curl_handle);
        curl_close($curl_handle);
        echo $args['before_widget'];
        echo $args['before_title'] . get_option('title') . $args['after_title'];
        if (empty($buffer)){
            return;
        } else {
            $json = json_decode($buffer);
            $template = '<li><a title="%s" href="%s">%s</a></li>';
            echo "<ul>";
            foreach($json->{'repositories'} as $repo) {
            	printf($template, $repo->{'name'}, $repo->{'url'}, $repo->{'name'});
            }
            echo "</ul>";
        }
        echo $args['after_widget'];
    }
    function register(){
        register_sidebar_widget('GitHub', array('GitHub', 'widget'));
        register_widget_control('GitHub', array('GitHub', 'control'));
    }
}
?>

