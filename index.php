<?php
/*
Plugin Name: Woocommerce - Google Cloud Print
Plugin URI: http://openswatch.com
Description: Google Cloud Printer for woocommerce
Author: anhvnit@gmail.com
Author URI: http://openswatch.com/
Version: 1.1
WC requires at least: 2.6
Text Domain: woocommerce-google-cloud-print
License: GPL version 2 or later - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
*/

define('OPENPOS_GOOGLE_PRINT_DIR',plugin_dir_path(__FILE__));
define('OPENPOS_GOOGLE_PRINT_URL',plugins_url('/',__FILE__));

if(!class_exists('HttpRequest')) {
    require_once OPENPOS_GOOGLE_PRINT_DIR.'lib/HttpRequest.Class.php';
}

if(!class_exists('GoogleCloudPrint')) {
    require_once OPENPOS_GOOGLE_PRINT_DIR . 'lib/GoogleCloudPrint.php';
}
if(!class_exists('OP_Google_Printer'))
{
    require_once( OPENPOS_GOOGLE_PRINT_DIR.'includes/Printer.php' );
}


if(!function_exists('is_plugin_active'))
{
    include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
}

global $_op_google_printer;
global $_op_gcp;
global $_has_openpos;

$_has_openpos = false;
if(is_plugin_active( 'woocommerce-openpos/woocommerce-openpos.php' ))
{
    $_has_openpos = true;
}

$_op_gcp = new GoogleCloudPrint();
$_op_google_printer = new OP_Google_Printer();
$_op_google_printer->init();

if($_has_openpos)
{
    if(!function_exists('custom_op_get_login_cashdrawer_data_google_print'))
    {
        function custom_op_get_login_cashdrawer_data_google_print($session_response_data){
            global $_op_google_printer;
            $setting = $_op_google_printer->get_setting();
            if(isset($setting['active']) && $setting['active'])
            {
                $session_response_data['setting']['pos_cloud_print'] = array(
                    'url' =>  OPENPOS_GOOGLE_PRINT_URL.'pos.php'
                );
            }
            return $session_response_data;
        }
    }
    add_filter('op_get_login_cashdrawer_data','custom_op_get_login_cashdrawer_data_google_print',11,1);

    if(!function_exists('custom_google_print_op_api_result'))
    {
        function custom_google_print_op_api_result($result,$api_action){
            if($api_action == 'login_cashdrawer')
            {
                $login_cashdrawer_id = isset($result['data']['login_cashdrawer_id']) ? $result['data']['login_cashdrawer_id'] : 0 ;
                if($login_cashdrawer_id)
                {
                    $allow = get_post_meta($login_cashdrawer_id,'_op_register_google_cloud_printer',true);
                    if($allow == -1)
                    {
                        if(isset($result['data']['setting']))
                        {
                            if(isset($result['data']['setting']['pos_cloud_print']))
                            {
                                unset($result['data']['setting']['pos_cloud_print']);
                            }
                        }
                    }

                }
            }
            return $result;
        }
    }

    add_filter('op_api_result','custom_google_print_op_api_result',11,2);
}


