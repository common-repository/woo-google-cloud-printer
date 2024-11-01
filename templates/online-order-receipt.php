<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$text_align = is_rtl() ? 'right' : 'left';

?>

<h2>
    <?php
        $before = '';
        $after  = '';
        /* translators: %s: Order ID. */
        echo wp_kses_post( $before . sprintf( __( '[Order #%s]', 'woocommerce' ) . $after . ' (<time datetime="%s">%s</time>)', $order->get_order_number(), $order->get_date_created()->format( 'c' ), wc_format_datetime( $order->get_date_created() ) ) );
    ?>
</h2>

<div style="margin-bottom: 40px;">
    <div class="receipt-header"><?php echo isset($setting['online_receipt_header']) ? do_shortcode($setting['online_receipt_header']) : '' ?></div>
    <table class="td" cellspacing="0" cellpadding="6" style="width: 100%; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif;" border="1">
        <thead>
        <tr>
            <th class="td" scope="col" style="text-align:<?php echo esc_attr( $text_align ); ?>;"><?php esc_html_e( 'Product', 'woocommerce-google-cloud-print' ); ?></th>
            <th class="td" scope="col" style="text-align:<?php echo esc_attr( $text_align ); ?>;"><?php esc_html_e( 'Quantity', 'woocommerce-google-cloud-print' ); ?></th>
            <th class="td" scope="col" style="text-align:<?php echo esc_attr( $text_align ); ?>;"><?php esc_html_e( 'Price', 'woocommerce-google-cloud-print' ); ?></th>
        </tr>
        </thead>
        <tbody>
        <?php echo $receipt_items_html; ?>
        </tbody>
        <tfoot>
        <?php
        $totals = $order->get_order_item_totals();

        if ( $totals ) {
            $i = 0;
            foreach ( $totals as $total ) {
                $i++;
                ?>
                <tr>
                    <th class="td" scope="row" colspan="2" style="text-align:<?php echo esc_attr( $text_align ); ?>; <?php echo ( 1 === $i ) ? 'border-top-width: 4px;' : ''; ?>"><?php echo wp_kses_post( $total['label'] ); ?></th>
                    <td class="td" style="text-align:<?php echo esc_attr( $text_align ); ?>; <?php echo ( 1 === $i ) ? 'border-top-width: 4px;' : ''; ?>"><?php echo wp_kses_post( $total['value'] ); ?></td>
                </tr>
                <?php
            }
        }
        if ( $order->get_customer_note() ) {
            ?>
            <tr>
                <th class="td" scope="row" colspan="2" style="text-align:<?php echo esc_attr( $text_align ); ?>;"><?php esc_html_e( 'Note:', 'woocommerce-google-cloud-print' ); ?></th>
                <td class="td" style="text-align:<?php echo esc_attr( $text_align ); ?>;"><?php echo wp_kses_post( wptexturize( $order->get_customer_note() ) ); ?></td>
            </tr>
            <?php
        }
        ?>
        </tfoot>
    </table>
    <div class="receipt-footer"><?php echo isset($setting['online_receipt_footer']) ? do_shortcode($setting['online_receipt_footer']) : '' ?></div>
</div>

