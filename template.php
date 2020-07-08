<?php
	$sites            = get_option( 'woonet_child_sites', array() );
	$price_multiplier = get_option( 'woonet_price_multiplier', array() );
?>
<div class="wrap woonet-settings-page">
	<h2><?php esc_html_e( 'Price Multiplier Settings', 'woonet' ); ?></h2>
	<form method="post">
		<br/>
		<table class="form-table">
			<tbody>
			<tr valign="top">
				<th scope="row">
					<strong> Price Multiplier (%) </strong>
				</th>
				<td>
					<strong> Site URL </strong>
				</td>
			</tr>
			<?php if ( ! empty( $sites ) ) : ?>
				<?php foreach ( $sites as $site ) : ?>
				<tr valign="top">
					<th scope="row">
						<input min='0' max='100' type='number' name='woomulti_price_multiplier[<?php echo $site['uuid']; ?>]' value='<?php echo isset( $price_multiplier[ $site['uuid'] ] ) ? $price_multiplier[ $site['uuid'] ] : 0; ?>' />
					</th>
					<td>
						<label> <?php echo esc_html( str_replace( array( 'http://', 'https://' ), '', trim( $site['site_url'], '/' ) ) ); ?></label>
					</td>
				</tr>
			<?php endforeach; ?>
			<?php endif; ?>
			</tbody>
		</table>
		<p class="submit">
			<input type="submit" name="Submit" class="button-primary"
				   value="<?php esc_html_e( 'Save Settings', 'woonet' ); ?>">
		</p>

		<?php wp_nonce_field( 'mstore_pm_form_submit', 'mstore_pm_form_nonce' ); ?>
		<input type="hidden" name="mstore_price_multiplier_submit" value="true"/>
	</form>
</div>
