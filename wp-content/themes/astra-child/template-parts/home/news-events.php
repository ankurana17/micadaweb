<?php defined( 'ABSPATH' ) || exit; ?>
	<aside class="w3-col m2 w3-container info-container3 w3-center w3-hover-shadow w3-border events-links-container">
		<ul class="w3-ul w3-margin-top" style="font-size:12px"><li class="w3-large w3-padding-16 events-links-header micada-panel-heading"><b><?php echo esc_html( micada_text( 'NEWS_AND_EVENTS' ) ); ?></b></li></ul>
		<?php if ( ! micada_home_editor_content( 'news' ) && ! micada_render_managed_news_events() ) : ?>
			<marquee direction="up" scrollamount="3" data-pause-marquee style="max-height:300px"><ul class="w3-ul"><li class="w3-padding-16 w3-left-align"><b><?php echo esc_html( micada_text( 'FARMER_ONLINE_APPLICATION_FOR_MI' ) ); ?></b><br><p><?php echo esc_html( micada_text( 'FARMER_ONLINE_APPLICATION_HAS_BEEN_INAUGURATED' ) ); ?></p></li><li class="w3-padding-24"></li><li class="w3-padding-16"></li></ul></marquee>
		<?php endif; ?>
	</aside>
