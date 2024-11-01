<?php
/**
 * Created by PhpStorm.
 * User: anhvnit
 * Date: 1/15/19
 * Time: 14:03
 */
class OP_Google_Printer{

    public function __construct()
    {

        add_action( 'wp_ajax_op_google_print_setting', array($this,'save_setting') );

        add_action( 'op_register_form_end', array($this,'op_register_form_end') ,10,3);
        add_action( 'op_register_save_after', array($this,'op_register_save_after') ,10,3);

        add_filter('woocommerce_thankyou_order_id',array($this,'woocommerce_thankyou_order_id'),11,1);

        //end

        add_action( 'wp_ajax_nopriv_op_google_print_oauth', array($this,'get_oauth') );
        add_action( 'wp_ajax_op_google_print_oauth', array($this,'get_oauth') );
        add_action( 'wp_ajax_nopriv_op_google_print_authorization', array($this,'get_authorization') );
        add_action( 'wp_ajax_op_google_print_authorization', array($this,'get_authorization') );
    }
    public function init(){
        add_action( 'admin_menu', array($this,'pos_admin_menu'),100 );
        add_filter('plugin_row_meta',array($this,'plugin_row_meta'),100,3);
    }

    public function pos_admin_menu(){
        global $_has_openpos;
        if($_has_openpos)
        {
            $page = add_submenu_page( 'openpos-dasboard', __( 'Google Cloud Print', 'woo-book-price' ),  __( 'Google Cloud Print', 'woo-book-price' ) , 'manage_woocommerce', 'op-google-print', array( $this, 'setting_page' ) );

        }else{
            $page = add_submenu_page( 'woocommerce', __( 'Google Cloud Print', 'woo-book-price' ),  __( 'Google Cloud Print', 'woo-book-price' ) , 'manage_woocommerce', 'op-google-print', array( $this, 'setting_page' ) );

        }

        add_action( 'admin_print_styles-'. $page, array( $this, 'admin_enqueue' ) );
    }


    public function admin_enqueue(){

        wp_enqueue_style('op-printer-bootstrap', OPENPOS_GOOGLE_PRINT_URL.'assets/css/bootstrap.css');

        wp_enqueue_style('openpos-printer.admin', OPENPOS_GOOGLE_PRINT_URL.'assets/css/admin.css',array('op-printer-bootstrap'));

        wp_enqueue_script('openpos-printer.admin.bootstrap', OPENPOS_GOOGLE_PRINT_URL.'assets/js/bootstrap.js',array('jquery'));

    }
    public function setting_page(){
        global $_has_openpos;
        $setting = $this->get_setting();
        $printers = $this->getPrinters();
        require(OPENPOS_GOOGLE_PRINT_DIR.'templates/setting.php');
    }

    public function save_setting(){
        $setting_request = isset($_REQUEST['_op_google_cloud_print']) ? wp_unslash($_REQUEST['_op_google_cloud_print']) : array();
        $setting = array();
        foreach($setting_request as $key => $val)
        {
            if($key == 'active' || $key == 'online_active')
            {
                $setting[$key] = intval($val);
            }else{
                $setting[$key] = sanitize_text_field($val);
            }

        }
        update_option( '_op_google_cloud_print', json_encode($setting) );
        $result = array(
            'status' => '1',
            'message' => 'Your setting has been saved.'
        );
        echo json_encode($result);
        exit;
    }

    public function get_setting(){
        $default = array(
            'active' => 0,
            'online_active' => 0,
            'default_printer' => ''
        );
        $setting_json = get_option('_op_google_cloud_print',json_encode($default));
        $setting = json_decode($setting_json,ARRAY_A);
        if(!isset($setting['online_active']))
        {
            $setting['online_active'] = 0;
        }
        $setting['active'] = isset($setting['active']) ? $setting['active'] : 0;
        $setting['redirect_uri'] = admin_url('admin-ajax.php?action=op_google_print_authorization');
        $setting['accessToken'] = get_option('_op_google_print_accessToken','');
        $setting['offlineToken'] = get_option('_op_google_print_offlinetoken','');
        return $setting;
    }

    public function getPrinters(){
        global $_op_gcp;

        $list = array();

        $setting = $this->get_setting();
        if(isset($setting['accessToken']) && $setting['accessToken'] != '')
        {
            $_op_gcp->setAuthToken($setting['accessToken']);
            $printers = $_op_gcp->getPrinters();
            foreach($printers as $printer)
            {
                $key = $printer['id'];
                $name = implode('-',array($printer['name'],$printer['connectionStatus']));
                $printer['name'] = $name;
                $list[$key] = $printer;
            }
        }

        return $list;
    }
    public function op_register_save_after($id,$params,$op_register){
        if($id)
        {
            $printer = isset($params['_op_register_google_cloud_printer']) ? $params['_op_register_google_cloud_printer'] : '';
            update_post_meta($id,'_op_register_google_cloud_printer',$printer);
        }
    }
    public function op_register_form_end($default,$warehouses,$cashiers){
        $printers = $this->getPrinters();
        $current_printer = '';

        if($default['id'] > 0)
        {
            $current_printer = get_post_meta($default['id'],'_op_register_google_cloud_printer',true);
        }

        ?>
        <div class="form-group">
            <label class="col-sm-2 control-label"><?php echo __( 'Printer', 'openpos' ); ?></label>
            <div class="col-sm-10">
                <select class="form-control" name="_op_register_google_cloud_printer">
                    <option  value=""><?php echo __('Use Default Printer','openpos'); ?></option>
                    <option  value="-1" <?php echo $current_printer == -1 ? 'selected': ''; ?>><?php echo __('No use Google Cloud Print','openpos'); ?></option>
                    <?php foreach($printers as $key => $printer):  ?>
                        <option value="<?php echo $key; ?>"  <?php echo $current_printer == $key ? 'selected': ''; ?>><?php echo $printer['name']; ?><?php echo isset($printer['ClientType']) ? ' - '.$printer['ClientType'] : ''; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <?php
    }

    public function refreshAccessToken(){
        global $_op_gcp;
        $setting = $this->get_setting();
        $token = '';
        if($setting['offlineToken'])
        {
            $refreshTokenConfig = array(
                'refresh_token' => $setting['offlineToken'],
                'client_id' => $setting['client_id'],
                'client_secret' => $setting['client_secret'],
                'grant_type' => "refresh_token"
            );
            $token = $_op_gcp->getAccessTokenByRefreshToken('https://www.googleapis.com/oauth2/v3/token',http_build_query($refreshTokenConfig));
            update_option('_op_google_print_accessToken',$token) ;
        }

        return $token;
    }

    public function getOnlineOrderReceiptHtml($order_id){
        $setting = $this->get_setting();
        $order = wc_get_order($order_id);
        $wc_email = new WC_Emails();
        $receipt_items_html = $this->wc_get_receipt_order_items($order);

        ob_start();
        $located = rtrim(OPENPOS_GOOGLE_PRINT_DIR,'/').'/templates/online-order-receipt.php';
        include $located;
        $invoice_html = ob_get_clean();
        return $invoice_html;

    }
    function wc_get_receipt_order_items( $order, $args = array() ) {
        ob_start();

        $defaults = array(
            'show_sku'      => false,
            'show_image'    => false,
            'image_size'    => array( 32, 32 ),
            'plain_text'    => false,
            'sent_to_admin' => false,
        );

        $args     = wp_parse_args( $args, $defaults );
        $items               = $order->get_items();
        $show_sku            = $args['show_sku'];
        $show_purchase_note  = true;
        $show_image          = $args['show_image'];
        $image_size         = $args['image_size'];
        $located = rtrim(OPENPOS_GOOGLE_PRINT_DIR,'/').'/templates/online-order-items-receipt.php';
        include $located;
        return apply_filters( 'woocommerce_receipt_order_items_table', ob_get_clean(), $order );
    }

    public function woocommerce_thankyou_order_id($order_id){
        global $_op_gcp;
        global $_op_google_printer;
        $setting = $this->get_setting();
        if($setting['online_active']  > 0 )
        {
            $printer = $setting['default_printer'];
            $target_file = rtrim(OPENPOS_GOOGLE_PRINT_DIR,'/').'/files/'.$order_id.'.html';
            if($printer && !file_exists($target_file))
            {
                $invoice_html = $this->getOnlineOrderReceiptHtml($order_id);
                $printers = $this->getPrinters();
                if(empty($printers))
                {
                    $setting['accessToken'] = $_op_google_printer->refreshAccessToken();
                    $_op_gcp->setAuthToken($setting['accessToken']);
                }

                if (file_put_contents($target_file,$invoice_html) && $setting['accessToken']) {

                    $_op_gcp->sendPrintToPrinter($printer, $order_id, $target_file, 'text/html');

                }
            }
        }


        return $order_id;
    }

    public function get_oauth(){
        $params = array();
        $params['action'] = 'op_google_print_authorization';
        $redirect_url = admin_url('admin-ajax.php')."?".http_build_query($params);
        wp_redirect($redirect_url);
        exit;
    }
    public function get_authorization(){
        $setting = $this->get_setting();
        $redirectConfig = array(
            'client_id' 	=> $setting['client_id'],
            'redirect_uri' 	=> $setting['redirect_uri'],
            'response_type' => 'code',
            'scope'         => 'https://www.googleapis.com/auth/cloudprint',
        );
        $authConfig = array(
            'code' => '',
            'client_id' 	=> $setting['client_id'],
            'client_secret' => $setting['client_secret'],
            'redirect_uri' 	=> $setting['redirect_uri'],
            "grant_type"    => "authorization_code"
        );
        $offlineAccessConfig = array(
            'access_type' => 'offline'
        );
        $urlconfig = array(
            'authorization_url' 	=> 'https://accounts.google.com/o/oauth2/auth',
            'accesstoken_url'   	=> 'https://accounts.google.com/o/oauth2/token',
            'refreshtoken_url'      => 'https://www.googleapis.com/oauth2/v3/token'
        );
        if(isset($_GET['code']) && !empty($_GET['code'])) {
            $code = sanitize_text_field($_GET['code']);
            $authConfig['code'] = $code;

            // Create object
            $gcp = new GoogleCloudPrint();
            $responseObj = $gcp->getAccessToken($urlconfig['accesstoken_url'],$authConfig);
            $accessToken = $responseObj->access_token;
            update_option('_op_google_print_accessToken',$accessToken) ;
            // We requested offline access
            if (isset($responseObj->refresh_token)) {
                update_option('_op_google_print_offlinetoken',$responseObj->refresh_token) ;
            }
            wp_redirect(admin_url('admin.php?page=op-google-print'));

        }else{
            $params = http_build_query(array_merge($redirectConfig,$offlineAccessConfig));
            $redirect_url = $urlconfig['authorization_url'].'?'.$params;
            wp_redirect($redirect_url);
        }

        exit;
    }
    function plugin_row_meta($plugin_meta, $plugin_file, $plugin_data){
        global $_has_openpos;
        $plugin = isset($plugin_data['TextDomain']) ? $plugin_data['TextDomain']:'';

        if($plugin == 'woocommerce-google-cloud-print' && !$_has_openpos)
        {
            $plugin_meta[] = '<a target="_blank" href="'.esc_url('https://codecanyon.net/item/openpos-a-complete-pos-plugins-for-woocomerce/22613341').'">'.__('BUY Woocommerce Point Of Sale','openpos').'</a>';
        }
        return $plugin_meta;
    }

}