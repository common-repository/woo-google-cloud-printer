<?php
$base_dir = dirname(dirname(dirname(__DIR__)));
require_once ($base_dir.'/wp-load.php');

global $_op_gcp;
global $_op_google_printer;
global $op_session;

$result = array('message' => '','status' => 0);
$receipt_html = isset($_REQUEST['html_str']) ? $_REQUEST['html_str'] : '';
$session = isset($_REQUEST['session']) ? $_REQUEST['session'] : '';
$cashdrawer_id = isset($_REQUEST['cashdrawer_id']) ? $_REQUEST['cashdrawer_id'] : 0;


if($op_session->validate($session))
{
    $setting = $_op_google_printer->get_setting();
    $printers = $_op_google_printer->getPrinters();

    if(empty($printers) )
    {
        $setting['accessToken'] = $_op_google_printer->refreshAccessToken();
    }

    if(isset($setting['active']) && $setting['active'] && $setting['accessToken'] )
    {
        $printer = $setting['default_printer'];
        if($cashdrawer_id)
        {
            $register_mac = get_post_meta($cashdrawer_id,'_op_register_google_cloud_printer',true);
            if($register_mac)
            {
                $printer  = $register_mac;
            }
        }
        $_op_gcp->setAuthToken($setting['accessToken']);
        if($_FILES){
            foreach($_FILES as $file_key => $file)
            {
                if($file_key == 'file_html')
                {
                    // if you want use print html receipt
                }else{
                    $type = $file['type'];
                    if($type == 'image/png')
                    {
                        $target_file = rtrim(OPENPOS_GOOGLE_PRINT_DIR,'/').'/files/'.$file["name"];

                        $image_base64 =  file_get_contents($file["tmp_name"]);

                        if (file_put_contents($target_file,$image_base64)) {

                            $_op_gcp->sendPrintToPrinter($printer, $file["name"], $target_file, 'image/png');

                            $result['message'] =  "The file ". basename( $file["name"]). " has been uploaded.";
                            $result['status'] = 1;
                        } else {
                            $result['message'] = "Sorry, there was an error uploading your file.";
                        }
                    }
                }
            }
        }
    }
}



echo json_encode($result);exit;