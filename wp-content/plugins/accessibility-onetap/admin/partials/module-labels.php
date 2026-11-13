<?php
/**
 * Content template for submenu page.
 *
 * @package    Accessibility_Onetap
 * @since      1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>

<div class="wrap">
	<?php onetap_load_template( 'admin/partials/header.php' ); ?>
	<div class="box-current-language" style="display: none;">
		<a href="<?php echo esc_url( admin_url() . 'admin.php?page=accessibility-onetap-settings' ); ?>" class="current-language">
			<ul style="display: none;">
				<li role="listitem" data-language="en">
					<button type="button">
						English
						<img src="<?php echo esc_url( ACCESSIBILITY_ONETAP_PLUGINS_URL . 'assets/images/english.png' ); ?>" alt="flag">
					</button>			
				</li>
				<li role="listitem" data-language="de">
					<button type="button">
						Deutsch
						<img src="<?php echo esc_url( ACCESSIBILITY_ONETAP_PLUGINS_URL . 'assets/images/german.png' ); ?>" alt="flag">	
					</button>
				</li>
				<li role="listitem" data-language="es">
					<button type="button">
						Español
						<img src="<?php echo esc_url( ACCESSIBILITY_ONETAP_PLUGINS_URL . 'assets/images/spanish.png' ); ?>" alt="flag">	
					</button>
				</li>
				<li role="listitem" data-language="fr">
					<button type="button">
						Français
						<img src="<?php echo esc_url( ACCESSIBILITY_ONETAP_PLUGINS_URL . 'assets/images/french.png' ); ?>" alt="flag">	
					</button>
				</li>
				<li role="listitem" data-language="it">
					<button type="button">
						Italiano
						<img src="<?php echo esc_url( ACCESSIBILITY_ONETAP_PLUGINS_URL . 'assets/images/italia.png' ); ?>" alt="flag">	
					</button>
				</li>
				<li role="listitem" data-language="pl">
					<button type="button">
						Polski
						<img src="<?php echo esc_url( ACCESSIBILITY_ONETAP_PLUGINS_URL . 'assets/images/poland.png' ); ?>" alt="flag">	
					</button>
				</li>
				<li role="listitem" data-language="se">
					<button type="button">
						Svenska
						<img src="<?php echo esc_url( ACCESSIBILITY_ONETAP_PLUGINS_URL . 'assets/images/swedish.png' ); ?>" alt="flag">	
					</button>
				</li>
				<li role="listitem" data-language="fi">
					<button type="button">
						Suomi
						<img src="<?php echo esc_url( ACCESSIBILITY_ONETAP_PLUGINS_URL . 'assets/images/finnland.png' ); ?>" alt="flag">	
					</button>
				</li>
				<li role="listitem" data-language="pt">
					<button type="button">
						Português
						<img src="<?php echo esc_url( ACCESSIBILITY_ONETAP_PLUGINS_URL . 'assets/images/portugal.png' ); ?>" alt="flag">	
					</button>
				</li>
				<li role="listitem" data-language="ro">
					<button type="button">
						Română
						<img src="<?php echo esc_url( ACCESSIBILITY_ONETAP_PLUGINS_URL . 'assets/images/rumania.png' ); ?>" alt="flag">	
					</button>
				</li>
				<li role="listitem" data-language="si">
					<button type="button">
						Slovenščina
						<img src="<?php echo esc_url( ACCESSIBILITY_ONETAP_PLUGINS_URL . 'assets/images/slowenien.png' ); ?>" alt="flag">	
					</button>
				</li>
				<li role="listitem" data-language="sk">
					<button type="button">
						Slovenčina
						<img src="<?php echo esc_url( ACCESSIBILITY_ONETAP_PLUGINS_URL . 'assets/images/slowakia.png' ); ?>" alt="flag">	
					</button>
				</li>					
				<li role="listitem" data-language="nl">
					<button type="button">
						Nederlands
						<img src="<?php echo esc_url( ACCESSIBILITY_ONETAP_PLUGINS_URL . 'assets/images/netherland.png' ); ?>" alt="flag">	
					</button>
				</li>
				<li role="listitem" data-language="dk">
					<button type="button">
						Dansk
						<img src="<?php echo esc_url( ACCESSIBILITY_ONETAP_PLUGINS_URL . 'assets/images/danish.png' ); ?>" alt="flag">	
					</button>
				</li>
				<li role="listitem" data-language="gr">
					<button type="button">
						Ελληνικά
						<img src="<?php echo esc_url( ACCESSIBILITY_ONETAP_PLUGINS_URL . 'assets/images/greece.png' ); ?>" alt="flag">	
					</button>
				</li>
				<li role="listitem" data-language="cz">
					<button type="button">
						Čeština
						<img src="<?php echo esc_url( ACCESSIBILITY_ONETAP_PLUGINS_URL . 'assets/images/czech.png' ); ?>" alt="flag">	
					</button>
				</li>
				<li role="listitem" data-language="hu">
					<button type="button">
						Magyar
						<img src="<?php echo esc_url( ACCESSIBILITY_ONETAP_PLUGINS_URL . 'assets/images/hungarian.png' ); ?>" alt="flag">	
					</button>
				</li>									
				<li role="listitem" data-language="lt">
					<button type="button">
						Lietuvių
						<img src="<?php echo esc_url( ACCESSIBILITY_ONETAP_PLUGINS_URL . 'assets/images/lithuanian.png' ); ?>" alt="flag">	
					</button>
				</li>
				<li role="listitem" data-language="lv">
					<button type="button">
						Latviešu
						<img src="<?php echo esc_url( ACCESSIBILITY_ONETAP_PLUGINS_URL . 'assets/images/latvian.png' ); ?>" alt="flag">	
					</button>
				</li>
				<li role="listitem" data-language="ee">
					<button type="button">
						Eesti
						<img src="<?php echo esc_url( ACCESSIBILITY_ONETAP_PLUGINS_URL . 'assets/images/estonian.png' ); ?>" alt="flag">	
					</button>
				</li>
				<li role="listitem" data-language="hr">
					<button type="button">
						Hrvatski
						<img src="<?php echo esc_url( ACCESSIBILITY_ONETAP_PLUGINS_URL . 'assets/images/croatia.png' ); ?>" alt="flag">	
					</button>
				</li>
				<li role="listitem" data-language="ie">
					<button type="button">
						Gaeilge
						<img src="<?php echo esc_url( ACCESSIBILITY_ONETAP_PLUGINS_URL . 'assets/images/ireland.png' ); ?>" alt="flag">	
					</button>
				</li>
				<li role="listitem" data-language="bg">
					<button type="button">
						Български
						<img src="<?php echo esc_url( ACCESSIBILITY_ONETAP_PLUGINS_URL . 'assets/images/bulgarian.png' ); ?>" alt="flag">	
					</button>
				</li>
				<li role="listitem" data-language="no">
					<button type="button">
						Norsk
						<img src="<?php echo esc_url( ACCESSIBILITY_ONETAP_PLUGINS_URL . 'assets/images/norwegan.png' ); ?>" alt="flag">	
					</button>
				</li>
				<li role="listitem" data-language="tr">
					<button type="button">
						Türkçe
						<img src="<?php echo esc_url( ACCESSIBILITY_ONETAP_PLUGINS_URL . 'assets/images/turkish.png' ); ?>" alt="flag">	
					</button>
				</li>
				<li role="listitem" data-language="id">
					<button type="button">
						Bahasa Indonesia
						<img src="<?php echo esc_url( ACCESSIBILITY_ONETAP_PLUGINS_URL . 'assets/images/indonesian.png' ); ?>" alt="flag">	
					</button>
				</li>		
				<li role="listitem" data-language="pt-br">
					<button type="button">
						Português (Brasil)
						<img src="<?php echo esc_url( ACCESSIBILITY_ONETAP_PLUGINS_URL . 'assets/images/brasilian.png' ); ?>" alt="flag">	
					</button>
				</li>	
				<li role="listitem" data-language="ja">
					<button type="button">
						日本語
						<img src="<?php echo esc_url( ACCESSIBILITY_ONETAP_PLUGINS_URL . 'assets/images/japanese.png' ); ?>" alt="flag">	
					</button>
				</li>	
				<li role="listitem" data-language="ko">
					<button type="button">
						한국어
						<img src="<?php echo esc_url( ACCESSIBILITY_ONETAP_PLUGINS_URL . 'assets/images/korean.png' ); ?>" alt="flag">	
					</button>
				</li>	
				<li role="listitem" data-language="zh">
					<button type="button">
						简体中文
						<img src="<?php echo esc_url( ACCESSIBILITY_ONETAP_PLUGINS_URL . 'assets/images/chinese-simplified.png' ); ?>" alt="flag">	
					</button>
				</li>	
				<li role="listitem" data-language="ar">
					<button type="button">
						العربية
						<img src="<?php echo esc_url( ACCESSIBILITY_ONETAP_PLUGINS_URL . 'assets/images/arabic.png' ); ?>" alt="flag">	
					</button>
				</li>	
				<li role="listitem" data-language="ru">
					<button type="button">
						Русский
						<img src="<?php echo esc_url( ACCESSIBILITY_ONETAP_PLUGINS_URL . 'assets/images/russian.png' ); ?>" alt="flag">	
					</button>
				</li>	
				<li role="listitem" data-language="hi">
					<button type="button">
						हिन्दी
						<img src="<?php echo esc_url( ACCESSIBILITY_ONETAP_PLUGINS_URL . 'assets/images/hindi.png' ); ?>" alt="flag">	
					</button>
				</li>	
				<li role="listitem" data-language="uk">
					<button type="button">
						Українська
						<img src="<?php echo esc_url( ACCESSIBILITY_ONETAP_PLUGINS_URL . 'assets/images/ukrainian.png' ); ?>" alt="flag">	
					</button>
				</li>	
				<li role="listitem" data-language="sr">
					<button type="button">
						Srpski
						<img src="<?php echo esc_url( ACCESSIBILITY_ONETAP_PLUGINS_URL . 'assets/images/serbian.png' ); ?>" alt="flag">	
					</button>
				</li>	
				<li role="listitem" data-language="gb">
					<button type="button">
						English (UK)
						<img src="<?php echo esc_url( ACCESSIBILITY_ONETAP_PLUGINS_URL . 'assets/images/england.png' ); ?>" alt="flag">
					</button>
				</li>
				<li role="listitem" data-language="ir">
					<button type="button">
						فارسی
						<img src="<?php echo esc_url( ACCESSIBILITY_ONETAP_PLUGINS_URL . 'assets/images/iran.png' ); ?>" alt="flag">
					</button>
				</li>
				<li role="listitem" data-language="il">
					<button type="button">
						עברית
						<img src="<?php echo esc_url( ACCESSIBILITY_ONETAP_PLUGINS_URL . 'assets/images/israel.png' ); ?>" alt="flag">
					</button>
				</li>
				<li role="listitem" data-language="mk">
					<button type="button">
						Македонски
						<img src="<?php echo esc_url( ACCESSIBILITY_ONETAP_PLUGINS_URL . 'assets/images/macedonia.png' ); ?>" alt="flag">
					</button>
				</li>
				<li role="listitem" data-language="th">
					<button type="button">
						ไทย
						<img src="<?php echo esc_url( ACCESSIBILITY_ONETAP_PLUGINS_URL . 'assets/images/thailand.png' ); ?>" alt="flag">
					</button>
				</li>
				<li role="listitem" data-language="vn">
					<button type="button">
						Tiếng Việt
						<img src="<?php echo esc_url( ACCESSIBILITY_ONETAP_PLUGINS_URL . 'assets/images/vietnam.png' ); ?>" alt="flag">
					</button>
				</li>
				<li role="listitem" data-language="bn">
					<button type="button">
						বাংলা
						<img src="<?php echo esc_url( ACCESSIBILITY_ONETAP_PLUGINS_URL . 'assets/images/bangladesh.png' ); ?>" alt="flag">
					</button>
				</li>
				<li role="listitem" data-language="tl">
					<button type="button">
						Filipino
						<img src="<?php echo esc_url( ACCESSIBILITY_ONETAP_PLUGINS_URL . 'assets/images/philippines.png' ); ?>" alt="flag">
					</button>
				</li>
				<li role="listitem" data-language="ur">
					<button type="button">
						اردو
						<img src="<?php echo esc_url( ACCESSIBILITY_ONETAP_PLUGINS_URL . 'assets/images/pakistan.png' ); ?>" alt="flag">
					</button>
				</li>
			</ul>
			<div class="image">
				<img src="<?php echo esc_url( ACCESSIBILITY_ONETAP_PLUGINS_URL . 'assets/images/english.png' ); ?>" alt="flag" class="active">
			</div>

			<span class="text-current-language">
				<strong class="language-editing"><?php esc_html_e( 'You’re currently editing', 'accessibility-onetap' ); ?></strong>
				<strong class="language-name">English</strong> 
				<strong class="language-labels"><?php esc_html_e( 'labels.', 'accessibility-onetap' ); ?></strong> 
				<span class="language-description"><?php esc_html_e( 'Choose another language to edit its translations.', 'accessibility-onetap' ); ?></span>
			</span>
		</a>

		<a href="<?php echo esc_url( admin_url() . 'admin.php?page=accessibility-onetap-settings#change-language' ); ?>" class="button outline change-language">
			<?php esc_html_e( 'Change Language', 'accessibility-onetap' ); ?>
		</a>
	</div>	
	<?php $this->settings_api->show_forms(); ?>
	<?php onetap_load_template( 'admin/partials/footer.php' ); ?>
</div>