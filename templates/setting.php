<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
?>
<div class="wrap op-conten-wrap">
    <h1 style="text-align: center;margin-bottom: 15px;"><?php echo __( 'Google Cloud Print Setting', 'woocommerce-google-cloud-print' ); ?></h1>
    <div class="row">
        <div class="col-sm-2"></div>
        <div class="col-sm-10">
            <div class="bs-example" data-example-id="simple-ol">
                <dt><?php echo __( 'Google OAuth Prerequisites', 'woocommerce-google-cloud-print' ); ?></dt>
                <ol>
                    <li><?php echo __( 'Create Google API project and get OAuth credentials.', 'woocommerce-google-cloud-print' ); ?></li>
                    <li><?php echo __( 'Create Google OAuth Credentials', 'woocommerce-google-cloud-print' ); ?></li>
                    <li><?php echo __( 'Create new project and get the corresponding OAuth credentials using Google developer console https://console.developers.google.com/' ); ?></li>
                    <li><?php echo __( 'Select APIS & AUTH –> credentials from the left menu.', 'woocommerce-google-cloud-print' ); ?></li>
                    <li><?php echo __( 'Click Create new Client ID button. A popup will appear. In Authorized redirect URIs text area enter url at field "Authorized redirect URIs".', 'woocommerce-google-cloud-print' ); ?></li>
                    <li><?php echo __( 'After submitting this form, we can get the client Id, secret key etc.', 'woocommerce-google-cloud-print' ); ?></li>
                </ol>

            </div>

        </div>
    </div>
    <div class="row">
        <div class="col-md-offset-2 col-sm-10" id="setting-notification">

        </div>
    </div>

    <form class="form-horizontal" id="_op_cloud_prnt_form">
        <input type="hidden" name="action" value="op_google_print_setting">
        <?php if($_has_openpos): ?>
        <div class="form-group">
            <label class="col-sm-2 control-label"><?php echo __( 'Enable for OpenPOS', 'woocommerce-google-cloud-print' ); ?></label>
            <div class="col-sm-4">
                <select class="form-control" name="_op_google_cloud_print[active]">
                    <option value="0" <?php echo $setting['active'] == 0 ? 'selected': ''; ?>><?php echo __( 'No', 'woocommerce-google-cloud-print' ); ?></option>
                    <option value="1" <?php echo $setting['active'] == 1 ? 'selected': ''; ?> ><?php echo __( 'Yes', 'woocommerce-google-cloud-print' ); ?></option>
                </select>
            </div>
        </div>
        <?php endif; ?>
        <div class="form-group">
            <label class="col-sm-2 control-label"><?php echo __( 'Enable for Online Order', 'woocommerce-google-cloud-print' ); ?></label>
            <div class="col-sm-4">
                <select class="form-control" name="_op_google_cloud_print[online_active]">
                    <option value="0" <?php echo $setting['online_active'] == 0 ? 'selected': ''; ?>><?php echo __( 'No', 'woocommerce-google-cloud-print' ); ?></option>
                    <option value="1" <?php echo $setting['online_active'] == 1 ? 'selected': ''; ?> ><?php echo __( 'Yes', 'woocommerce-google-cloud-print' ); ?></option>
                </select>
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-2 control-label"><?php echo __( 'Online Order Receipt Header', 'woocommerce-google-cloud-print' ); ?></label>
            <div class="col-sm-4">
                <textarea class="form-control" rows="5"  name="_op_google_cloud_print[online_receipt_header]"><?php echo isset($setting['online_receipt_header'])? $setting['online_receipt_header'] : '' ?></textarea>
                <p><small><?php echo __( 'HTML code accept in this field', 'woocommerce-google-cloud-print' ); ?></small></p>
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-2 control-label"><?php echo __( 'Online Order Receipt Footer', 'woocommerce-google-cloud-print' ); ?></label>
            <div class="col-sm-4">
                <textarea class="form-control" rows="5" name="_op_google_cloud_print[online_receipt_footer]"><?php echo isset($setting['online_receipt_footer'])? $setting['online_receipt_footer'] : '' ?></textarea>
                <p><small><?php echo __( 'HTML code accept in this field', 'woocommerce-google-cloud-print' ); ?></small></p>
            </div>
        </div>

        <div class="form-group">
            <label for="inputEmail3" class="col-sm-2 control-label"><?php echo __( 'Authorized redirect URIs', 'woocommerce-google-cloud-print' ); ?></label>
            <div class="col-sm-10">
                <input type="text" disabled class="form-control"  value="<?php echo esc_url($setting['redirect_uri']); ?>">
            </div>
        </div>
        <div class="form-group">
            <label for="inputEmail3" class="col-sm-2 control-label"><?php echo __( 'Google Client Id', 'woocommerce-google-cloud-print' ); ?></label>
            <div class="col-sm-10">
                <input type="text"  class="form-control" name="_op_google_cloud_print[client_id]"  value="<?php echo isset($setting['client_id']) ? $setting['client_id'] : ''; ?>">
            </div>
        </div>
        <div class="form-group">
            <label for="inputEmail3" class="col-sm-2 control-label"><?php echo __( 'Google Client Secret', 'woocommerce-google-cloud-print' ); ?></label>
            <div class="col-sm-10">
                <input type="text"  class="form-control" name="_op_google_cloud_print[client_secret]"  value="<?php echo isset($setting['client_id']) ? $setting['client_secret'] : ''; ?>">
            </div>
        </div>

        <div class="form-group">
            <label class="col-sm-2 control-label"><?php echo __( 'Access Token', 'woocommerce-google-cloud-print' ); ?></label>
            <div class="col-sm-4">
                <input type="text" disabled class="form-control"  value="<?php echo isset($setting['accessToken']) ? $setting['accessToken'] : ''; ?>">


            </div>
            <div class="col-sm-6"><p><a href="<?php echo esc_url(admin_url('admin-ajax.php?action=op_google_print_oauth'))?>"><?php echo __( 'Click here to get new access token', 'woocommerce-google-cloud-print' ); ?></a></p></div>
        </div>
        <div class="form-group">
            <label class="col-sm-2 control-label"><?php echo __( 'Default Printer', 'woocommerce-google-cloud-print' ); ?></label>
            <div class="col-sm-4">
                <select class="form-control" name="_op_google_cloud_print[default_printer]">
                    <option value=""><?php echo __( 'Please choose', 'woocommerce-google-cloud-print' ); ?></option>
                    <?php foreach($printers as $key => $printer):  ?>
                        <option value="<?php echo $key; ?>"  <?php echo $setting['default_printer'] == $key ? 'selected': ''; ?>><?php echo $printer['name']; ?><?php echo isset($printer['ClientType']) ? ' - '.$printer['ClientType'] : ''; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

        </div>

        <div class="form-group">
            <div class="col-sm-offset-2 col-sm-10">
                <button type="button" id="save-setting" class="btn btn-default"><?php echo __( 'Save', 'woocommerce-google-cloud-print' ); ?></button>
            </div>
        </div>
    </form>
    <br class="clear">
</div>

<script type="text/javascript">

    (function($) {
        "use strict";

        $(document).on('click','#save-setting',function(){
            $.ajax({
                url: "<?php echo admin_url( 'admin-ajax.php' ); ?>",
                type: 'post',
                data: $('#_op_cloud_prnt_form').serialize(),
                dataType: 'json',
                beforeSend:function(){

                },
                success: function(response){
                    if(response['status'] == 1)
                    {
                        $('#setting-notification').html('<p class="bg-success">'+response['message']+'</p>');
                        var timeOutVar = setTimeout(function(){
                            $('#setting-notification').empty();
                        }, 5000);
                    }
                }
            });
        });



    })( jQuery );
</script>