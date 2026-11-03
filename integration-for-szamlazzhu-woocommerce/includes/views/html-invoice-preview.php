<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

//Check for permissions
if ( !current_user_can( 'edit_shop_orders' ) ) {
  wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'wc-szamlazz' ) );
}

?>
<!DOCTYPE html>
<html lang="en" dir="ltr">
	<head>
		<meta charset="utf-8">
		<title><?php esc_html_e('Invoice preview', 'wc-szamlazz'); ?></title>
		<?php
		wp_register_style( 'wc-szamlazz-preview', WC_Szamlazz()::$plugin_url . 'assets/css/preview.css', array(), WC_Szamlazz::$version );
		wp_print_styles( 'wc-szamlazz-preview' );
		?>
	</head>
	<body>

		<div class="sheet">

			<?php if($invoice_xml['error']): ?>
				<?php if(!empty($invoice_xml['messages'])): ?>
					<?php foreach ($invoice_xml['messages'] as $message): ?>
						<p><?php echo esc_html($message); ?></p>
					<?php endforeach; ?>
				<?php endif; ?>
			<?php else: ?>

				<div class="header">
					<div class="logo">logo</div>
					<div class="seller">
						<strong><?php esc_html_e('Seller Name', 'wc-szamlazz'); ?></strong><br>
						1234 Város<br>
						Minta u. 1.<br>
						<strong><?php esc_html_e('VAT Number', 'wc-szamlazz'); ?>:</strong> XXXXXXXX-X-XX
					</div>
					<div class="seller">
						<strong><?php esc_html_e('Bank name:', 'wc-szamlazz'); ?>:</strong> <?php if($invoice['elado']['bank']): ?><?php echo esc_html( $invoice['elado']['bank'] ); ?><?php else: ?>Teszt bank<?php endif; ?><br>
						<strong><?php esc_html_e('Bank account number', 'wc-szamlazz'); ?>:</strong> <?php if($invoice['elado']['bankszamlaszam']): ?><?php echo esc_html( $invoice['elado']['bankszamlaszam'] ); ?><?php else: ?>XXXXXXXX-XXXXXXXX-XXXXXXXX<?php endif; ?>
					</div>
				</div>

				<h1 class="title"><?php echo esc_html_x('Preview', 'Invoice preview title', 'wc-szamlazz'); ?></h1>
				<div class="subtitle"><?php esc_html_e('Invoice number', 'wc-szamlazz'); ?>: <?php if(isset($invoice['fejlec']['szamlaszamElotag'])): ?><?php echo esc_html( $invoice['fejlec']['szamlaszamElotag'] ); ?>-<?php endif; ?>1234</div>

				<div class="info">

					<div class="buyer">
						<h2>Vevő:</h2>
						<?php echo esc_html( $invoice['vevo']['nev'] ); ?><br>
						<?php echo esc_html( $invoice['vevo']['orszag'] ); ?>, <?php echo esc_html( $invoice['vevo']['irsz'] ); ?> <?php echo esc_html( $invoice['vevo']['telepules'] ); ?><br>
						<?php echo esc_html( $invoice['vevo']['cim'] ); ?>
						<?php if($invoice['vevo']['adoszam']): ?><br><?php esc_html_e('VAT Number', 'wc-szamlazz'); ?>: <?php echo esc_html( $invoice['vevo']['adoszam'] ); ?><?php endif; ?>
						<?php if($invoice['vevo']['adoszamEU']): ?><br><?php esc_html_e('EU VAT Number', 'wc-szamlazz'); ?>: <?php echo esc_html( $invoice['vevo']['adoszamEU'] ); ?><?php endif; ?>
					</div>

					<table class="shop-details">
						<tr>
							<td><?php esc_html_e('Payment method', 'wc-szamlazz'); ?>:</td>
							<td><?php echo esc_html( $invoice['fejlec']['fizmod'] ); ?></td>
						</tr>
						<tr>
							<td><?php esc_html_e('Order number', 'wc-szamlazz'); ?>:</td>
							<td><?php echo esc_html( $invoice['fejlec']['rendelesSzam'] ); ?></td>
						</tr>
						<tr>
							<td><?php esc_html_e('Completion date', 'wc-szamlazz'); ?>:</td>
							<td><?php echo esc_html( $invoice['fejlec']['teljesitesDatum'] ); ?></td>
						</tr>
						<tr>
							<td><?php esc_html_e('Issue date', 'wc-szamlazz'); ?>:</td>
							<td><?php echo esc_html( $invoice['fejlec']['keltDatum'] ); ?></td>
						</tr>
						<tr>
							<td><?php esc_html_e('Due date', 'wc-szamlazz'); ?>:</td>
							<td><?php echo esc_html( $invoice['fejlec']['fizetesiHataridoDatum'] ); ?></td>
						</tr>
					</table>
				</div>

				<table class="line-items">
					<thead>
						<tr>
							<th><?php esc_html_e('Description', 'wc-szamlazz'); ?></th>
							<th><?php esc_html_e('Qty', 'wc-szamlazz'); ?></th>
							<th><?php esc_html_e('Unit price', 'wc-szamlazz'); ?></th>
							<th><?php esc_html_e('Net price', 'wc-szamlazz'); ?></th>
							<th><?php esc_html_e('VAT', 'wc-szamlazz'); ?></th>
							<th><?php esc_html_e('VAT value', 'wc-szamlazz'); ?></th>
							<th><?php esc_html_e('Gross price', 'wc-szamlazz'); ?></th>
						</tr>
					</thead>
					<?php
					$sum_netto = 0;
					$sum_afa = 0;
					$sum_brutto = 0;
					$sum_total = 0;
					$items = $invoice['tetelek']['tetel'];
					if(!isset($invoice['tetelek']['tetel'][0])) {
						$items = array($invoice['tetelek']['tetel']);
					}
					?>
					<?php foreach ($items as $tetel): ?>
						<tr>
							<td>
								<span><?php echo esc_html( $tetel['megnevezes'] ); ?> <?php if(isset($tetel['azonosito']) && !empty($tetel['azonosito'])): ?>(<?php echo esc_html( $tetel['azonosito'] ); ?>)<?php endif; ?></span>
								<?php if(!empty($tetel['megjegyzes'])): ?>
									<div class="note"><?php echo wp_kses_post( wpautop( $tetel['megjegyzes'] ) ); ?></div>
								<?php endif; ?>
							</td>
							<td><?php echo esc_html( $tetel['mennyiseg'] ); ?> <?php echo esc_html( $tetel['mennyisegiEgyseg'] ); ?></td>
							<td><?php echo esc_html( $tetel['nettoEgysegar'] ); ?></td>
							<td><?php echo esc_html( round( (float) $tetel['nettoErtek'], 2 ) ); ?></td>
							<td><?php echo esc_html( $tetel['afakulcs'] ); ?> <?php if(intval($tetel['afakulcs']) > 0): ?>%<?php endif; ?></td>
							<td><?php echo esc_html( round( (float) $tetel['afaErtek'], 2 ) ); ?></td>
							<td><?php echo esc_html( round( (float) $tetel['bruttoErtek'], 2 ) ); ?></td>
						</tr>
						<?php $sum_netto += (float) $tetel['nettoEgysegar']; ?>
						<?php $sum_afa += (float) $tetel['afaErtek']; ?>
						<?php $sum_brutto += (float) $tetel['bruttoErtek']; ?>
					<?php endforeach; ?>
					<tfoot>
						<tr>
							<td colspan="2"><?php esc_html_e('Total', 'wc-szamlazz'); ?>:</td>
							<td colspan="2"><?php echo esc_html( round( (float) $sum_netto, 2 ) ); ?></td>
							<td></td>
							<td><?php echo esc_html( round( (float) $sum_afa, 2 ) ); ?></td>
							<td><?php echo esc_html( round( (float) $sum_brutto, 2 ) ); ?></td>
						</tr>
					</tfoot>
				</table>

				<div class="total">
					<h2><?php esc_html_e('Total', 'wc-szamlazz'); ?>:</h2>
					<h3><?php echo esc_html( round( (float) $sum_brutto, 2 ) ); ?> <?php echo esc_html( $invoice['fejlec']['penznem'] ); ?></h3>
				</div>

				<?php if($invoice['fejlec']['megjegyzes']): ?>
					<div class="invoice-note"><?php echo wp_kses_post( wpautop( $invoice['fejlec']['megjegyzes'] ) ); ?></div>
				<?php endif; ?>

			<?php endif; ?>

		</div>

		<p class="footer"><?php esc_html_e('This is just a preview of the invoice and not all parameters will match the real invoice, like the colors, logo, language, and seller details.', 'wc-szamlazz'); ?></p>
		<?php if(WC_Szamlazz()->get_option('debug', 'no') == 'yes'): ?>
			<div class="debug"><textarea><?php esc_html_e('Development mode details:', 'wc-szamlazz'); ?> <?php echo esc_textarea( wp_json_encode($invoice, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) ); ?></textarea></div>
		<?php endif; ?>

	</body>
</html>
