<?php
defined( 'ABSPATH' ) || exit;
?>
<aside class="w3-col m2 w3-container info-container1 w3-center w3-hover-shadow w3-border events-links-container">
	<ul class="w3-ul w3-margin-top" style="font-size:12px">
		<li class="w3-large w3-padding-16 events-links-header micada-panel-heading"><b><?php echo esc_html( micada_text( 'QUICK_LINKS' ) ); ?></b></li>
	</ul>
	<?php if ( function_exists( 'micada_render_managed_quick_links' ) ) { micada_render_managed_quick_links(); } ?>
</aside>