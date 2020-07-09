<?php
if ( is_multisite() ) {
	$sites            = get_sites();
	$price_multiplier = get_site_option( 'woonet_price_multiplier', array() );
} else {
	$sites            = get_option( 'woonet_child_sites', array() );
	$price_multiplier = get_option( 'woonet_price_multiplier', array() );
}
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
				<?php
				foreach ( $sites as $site ) :
					if ( ! is_multisite() ) {
						$input_name = "woomulti_price_multiplier[ {$site['uuid']} ]";

						if ( isset( $price_multiplier[ $site['uuid'] ] ) ) {
							$input_value = $price_multiplier[ $site['uuid'] ];
						} else {
							$input_value = 0;
						}

						$site_url = str_replace( array( 'http://', 'https://' ), '', trim( $site['site_url'], '/' ) );
					} else {
						$input_name = "woomulti_price_multiplier[{$site->blog_id}]";

						if ( ! empty( $price_multiplier[ $site->blog_id ] ) ) {
							$input_value = $price_multiplier[ $site->blog_id ];
						} else {
							$input_value = 0;
						}

						$current_blog_details = get_blog_details( array( 'blog_id' => $site->blog_id ) );

						if ( ! empty( $current_blog_details ) && isset( $current_blog_details->blogname ) ) {
							$site_url = $current_blog_details->blogname;
						} else {
							$site_url = $site->domain . $site->path;
						}
					}
					?>
				<tr valign="top">
					<th scope="row">
						<input min='0' max='10000' type='number' name='<?php echo $input_name; ?>' value='<?php echo $input_value; ?>' />
					</th>
					<td>
						<label> <?php echo esc_html( $site_url ); ?></label>
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
