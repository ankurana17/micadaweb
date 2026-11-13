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

// Retrieve saved option values.
$accessibility_status = array(
	'show_accessibility'    => get_option( 'onetap_show_accessibility' ),
	'select_language'       => get_option( 'onetap_select_language' ),
	'company_name'          => get_option( 'onetap_company_name' ),
	'company_website'       => get_option( 'onetap_company_website' ),
	'business_email'        => get_option( 'onetap_business_email' ),
	'confirmation_checkbox' => get_option( 'onetap_confirmation_checkbox' ),
	'editor_generator'      => get_option( 'onetap_editor_generator' ),
);
?>

<div class="wrap">
	<style>
		#apop_accessibility_status .footer,
		#onetap_accessibility_status .footer {
			padding: 24px 28px 0 387px;
		}

		@media only screen and (max-width: 1200px) {
			#apop_accessibility_status .footer,
			#onetap_accessibility_status .footer {
				padding: 0;
				padding-top: 24px;
			}
		}

		@media only screen and (max-width: 768px) {
			#apop_accessibility_status .footer,
			#onetap_accessibility_status .footer {
				padding: 15px 15px 0 15px;
			}
		}
	</style>
	<?php onetap_load_template( 'admin/partials/header.php' ); ?>
	<div class="options-wrapper">
		<div id="onetap_accessibility_status" class="group" style="display: none;">
			<form method="post" action="options.php" class="accessibility-status">
				<?php settings_fields( 'options_group_onetap_accessibility_status' ); ?>
				<?php do_settings_sections( 'options_group_onetap_accessibility_status' ); ?>

				<div class="mycontainer accessibility-status-container">
					<!-- Left column -->
					<div class="left-column">
						<!-- Show Accessibility Statement -->
						<div class="box-control-switch custom-box-control">
							<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16" fill="none">
								<path d="M7.16196 3.39488C7.4329 3.35482 7.7124 3.33333 8.00028 3.33333C11.4036 3.33333 13.6369 6.33656 14.3871 7.52455C14.4779 7.66833 14.5233 7.74023 14.5488 7.85112C14.5678 7.93439 14.5678 8.06578 14.5487 8.14905C14.5233 8.25993 14.4776 8.3323 14.3861 8.47705C14.1862 8.79343 13.8814 9.23807 13.4777 9.7203M4.48288 4.47669C3.0415 5.45447 2.06297 6.81292 1.61407 7.52352C1.52286 7.66791 1.47725 7.74011 1.45183 7.85099C1.43273 7.93426 1.43272 8.06563 1.45181 8.14891C1.47722 8.25979 1.52262 8.33168 1.61342 8.47545C2.36369 9.66344 4.59694 12.6667 8.00028 12.6667C9.37255 12.6667 10.5546 12.1784 11.5259 11.5177M2.00028 2L14.0003 14M6.58606 6.58579C6.22413 6.94772 6.00028 7.44772 6.00028 8C6.00028 9.10457 6.89571 10 8.00028 10C8.55256 10 9.05256 9.77614 9.41449 9.41421" stroke="#414651" stroke-width="1.33333" stroke-linecap="round" stroke-linejoin="round"/>
							</svg>

							<div class="box-swich">
								<label class="switch" for="show-accessibility-statement">
									<span class="label"><?php esc_html_e( 'Show Accessibility Statement', 'accessibility-onetap' ); ?></span>
									<input type="checkbox" class="checkbox" id="show-accessibility-statement" name="onetap_show_accessibility" value="1" <?php checked( 1, $accessibility_status['show_accessibility'] ); ?> />
									<span class="slider round"></span>
								</label>
							</div>
						</div>

						<!-- Divider -->
						<div class="divider"></div>

						<!-- Language select dropdown -->
						<div class="box-control-select custom-box-controls margin-bottom-22">
							<label class="label" for="select_language"><?php esc_html_e( 'Select Language', 'accessibility-onetap' ); ?></label>
							<?php
							$apop_languages = array(
								'en'    => 'English',
								'de'    => 'Deutsch',
								'es'    => 'Español',
								'fr'    => 'Français',
								'it'    => 'Italiano',
								'pl'    => 'Polski',
								'se'    => 'Svenska',
								'fi'    => 'Suomi',
								'pt'    => 'Português',
								'ro'    => 'Română',
								'si'    => 'Slovenščina',
								'sk'    => 'Slovenčina',
								'nl'    => 'Nederlands',
								'dk'    => 'Dansk',
								'gr'    => 'Ελληνικά',
								'cz'    => 'Čeština',
								'hu'    => 'Magyar',
								'lt'    => 'Lietuvių',
								'lv'    => 'Latviešu',
								'ee'    => 'Eesti',
								'hr'    => 'Hrvatski',
								'ie'    => 'Gaeilge',
								'bg'    => 'Български',
								'no'    => 'Norsk',
								'tr'    => 'Türkçe',
								'id'    => 'Bahasa Indonesia',
								'pt-br' => 'Português (Brasil)',
								'ja'    => '日本語',
								'ko'    => '한국어',
								'zh'    => '简体中文',
								'ar'    => 'العربية',
								'ru'    => 'Русский',
								'hi'    => 'हिन्दी',
								'uk'    => 'Українська',
								'sr'    => 'Srpski',
								'gb'    => 'English (UK)',
								'ir'    => 'فارسی',
								'il'    => 'עברית',
								'mk'    => 'Македонски',
								'th'    => 'ไทย',
								'vn'    => 'Tiếng Việt',
								'bn'    => 'বাংলা',
								'tl'    => 'Filipino',
								'ur'    => 'اردو',
							);

							// Mapping language codes to flag image filenames.
							$apop_flag_images = array(
								'en'    => 'english.png',
								'de'    => 'german.png',
								'es'    => 'spanish.png',
								'fr'    => 'french.png',
								'it'    => 'italia.png',
								'pl'    => 'poland.png',
								'se'    => 'swedish.png',
								'fi'    => 'finnland.png',
								'pt'    => 'portugal.png',
								'ro'    => 'rumania.png',
								'si'    => 'slowenien.png',
								'sk'    => 'slowakia.png',
								'nl'    => 'netherland.png',
								'dk'    => 'danish.png',
								'gr'    => 'greece.png',
								'cz'    => 'czech.png',
								'hu'    => 'hungarian.png',
								'lt'    => 'lithuanian.png',
								'lv'    => 'latvian.png',
								'ee'    => 'estonian.png',
								'hr'    => 'croatia.png',
								'ie'    => 'ireland.png',
								'bg'    => 'bulgarian.png',
								'no'    => 'norwegan.png',
								'tr'    => 'turkish.png',
								'id'    => 'indonesian.png',
								'pt-br' => 'brasilian.png',
								'ja'    => 'japanese.png',
								'ko'    => 'korean.png',
								'zh'    => 'chinese-simplified.png',
								'ar'    => 'arabic.png',
								'ru'    => 'russian.png',
								'hi'    => 'hindi.png',
								'uk'    => 'ukrainian.png',
								'sr'    => 'serbian.png',
								'gb'    => 'england.png',
								'ir'    => 'iran.png',
								'il'    => 'israel.png',
								'mk'    => 'macedonia.png',
								'th'    => 'thailand.png',
								'vn'    => 'vietnam.png',
								'bn'    => 'bangladesh.png',
								'tl'    => 'philippines.png',
								'ur'    => 'pakistan.png',
							);

							// Get current selected language.
							$current_language = isset( $accessibility_status['select_language'] ) ? $accessibility_status['select_language'] : 'en';
							$current_label    = isset( $apop_languages[ $current_language ] ) ? $apop_languages[ $current_language ] : $apop_languages['en'];
							$current_flag     = isset( $apop_flag_images[ $current_language ] ) ? $apop_flag_images[ $current_language ] : $apop_flag_images['en'];
							$plugin_url       = plugin_dir_url( dirname( __DIR__ ) ) . 'assets/images/';

							// Build custom dropdown HTML.
							?>
							<div class="language-select-dropdown">
								<!-- Hidden input for form submission -->
								<input type="hidden" id="select_language" name="onetap_select_language" value="<?php echo esc_attr( $current_language ); ?>" class="language-select-input" />

								<!-- Dropdown wrapper -->
								<div class="language-select-wrapper">
									<!-- Dropdown trigger button -->
									<button type="button" class="language-select-trigger" aria-expanded="false">
										<span class="language-select-selected">
											<span class="language-select-flag">
												<img src="<?php echo esc_url( $plugin_url . $current_flag ); ?>" alt="<?php echo esc_attr( $current_language ); ?>" />
											</span>
											<span class="language-select-label"><?php echo esc_html( $current_label ); ?></span>
										</span>
										<span class="language-select-chevron">
											<svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
												<path d="M4 6L8 10L12 6" stroke="#A4A7AE" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
											</svg>
										</span>
									</button>
								</div>

								<!-- Dropdown options list -->
								<ul class="language-select-options">
									<?php
									foreach ( $apop_languages as $apop_code => $apop_label ) {
										$is_selected = ( $current_language === $apop_code );
										$flag_image  = isset( $apop_flag_images[ $apop_code ] ) ? $apop_flag_images[ $apop_code ] : 'english.png';
										?>
										<li class="language-select-option<?php echo $is_selected ? ' selected' : ''; ?>" data-value="<?php echo esc_attr( $apop_code ); ?>">
											<span class="language-select-option-flag">
												<img src="<?php echo esc_url( $plugin_url . $flag_image ); ?>" alt="<?php echo esc_attr( $apop_code ); ?>" />
											</span>
											<span class="language-select-option-label"><?php echo esc_html( $apop_label ); ?></span>
											<span class="language-select-checkmark"<?php echo $is_selected ? '' : ' style="display: none;"'; ?>>
												<svg width="15" height="11" viewBox="0 0 15 11" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M14.1666 0.833344L4.99992 10L0.833252 5.83334" stroke="#0048FE" stroke-width="1.66667" stroke-linecap="round" stroke-linejoin="round"/></svg>
											</span>
										</li>
										<?php
									}
									?>
								</ul>
							</div>
						</div>

						<!-- Company Name -->
						<div class="box-control-input-text custom-box-controls margin-bottom-22">
							<label class="label" for="company_name"><?php esc_html_e( 'Company Name', 'accessibility-onetap' ); ?></label>
							<input type="text" id="company_name" name="onetap_company_name" value="<?php echo esc_attr( $accessibility_status['company_name'] ); ?>" placeholder="<?php echo esc_attr__( 'Company Inc.', 'accessibility-onetap' ); ?>" />
						</div>

						<!-- Business Email -->
						<div class="box-control-input-email custom-box-controls margin-bottom-22">
							<label class="label" for="company_name"><?php esc_html_e( 'Business E-mail', 'accessibility-onetap' ); ?></label>
							<input type="email" id="business_email" name="onetap_business_email" value="<?php echo esc_attr( $accessibility_status['business_email'] ); ?>" placeholder="<?php echo esc_attr__( 'info@company.com', 'accessibility-onetap' ); ?>" />
						</div>

						<!-- Company Website -->
						<div class="box-control-input-text box-control-company-website custom-box-controls margin-bottom-22">
							<label class="label" for="company_name"><?php esc_html_e( 'Website', 'accessibility-onetap' ); ?></label>
							<span class="protocol">
								<?php esc_html_e( 'https://', 'accessibility-onetap' ); ?>
							</span>
							<input type="text" id="company_website" name="onetap_company_website" value="<?php echo esc_attr( $accessibility_status['company_website'] ); ?>" placeholder="<?php echo esc_attr__( 'www.company.com', 'accessibility-onetap' ); ?>" />
						</div>					

						<!-- Confirmation Checkbox -->
						<div class="box-control-input-checkbox box-control-confirm custom-box-controls margin-bottom-22">
							<label class="label">
								<input type="checkbox" name="onetap_confirmation_checkbox" value="1" <?php checked( 1, $accessibility_status['confirmation_checkbox'] ); ?>>
								<span class="text">
									<?php esc_html_e( 'I confirm that the generated accessibility status is based on my own assessment. The provider of this generator assumes no liability for its accuracy or compliance.', 'accessibility-onetap' ); ?>
								</span>
							</label>
						</div>

						<button class="button primary large save-changes generate-accessibility-statement">
							<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none">
							<g clip-path="url(#clip0_606_1101)">
								<path d="M3.74996 18.3334V14.1667M3.74996 5.83335V1.66669M1.66663 3.75002H5.83329M1.66663 16.25H5.83329M10.8333 2.50002L9.38815 6.25741C9.15314 6.86843 9.03563 7.17394 8.8529 7.43093C8.69095 7.65869 8.49196 7.85768 8.2642 8.01963C8.00722 8.20236 7.7017 8.31986 7.09068 8.55487L3.33329 10L7.09068 11.4452C7.7017 11.6802 8.00722 11.7977 8.2642 11.9804C8.49196 12.1424 8.69095 12.3414 8.8529 12.5691C9.03563 12.8261 9.15314 13.1316 9.38815 13.7426L10.8333 17.5L12.2784 13.7426C12.5135 13.1316 12.631 12.8261 12.8137 12.5691C12.9756 12.3414 13.1746 12.1424 13.4024 11.9804C13.6594 11.7977 13.9649 11.6802 14.5759 11.4452L18.3333 10L14.5759 8.55487C13.9649 8.31986 13.6594 8.20236 13.4024 8.01963C13.1746 7.85768 12.9756 7.65869 12.8137 7.43093C12.631 7.17394 12.5135 6.86843 12.2784 6.2574L10.8333 2.50002Z" stroke="#C8E0FF" stroke-width="1.66667" stroke-linecap="round" stroke-linejoin="round"/>
							</g>
							<defs>
								<clipPath id="clip0_606_1101">
								<rect width="20" height="20" fill="white"/>
								</clipPath>
							</defs>
							</svg>
							<?php esc_html_e( 'Generate Text', 'accessibility-onetap' ); ?>
						</button>

						<span class="note">
							<?php esc_html_e( 'Please note: Generating a new statement will automatically overwrite the previous version.', 'accessibility-onetap' ); ?>
						</span>
					</div>

					<!-- Right column -->
					<div class="right-column">

						<!-- English -->
						<div data-content-lang="en" class="status-message-accessibility" style="display: none;">
							<h2>Accessibility Commitment for [Company Name]</h2>
							At <strong>[Company Name]</strong>, we are committed to making our digital presence as accessible and inclusive as reasonably possible for all users, including individuals with disabilities. Our goal is to improve the usability of <strong>[Company Website]</strong> and to support a more accessible experience for everyone, regardless of their abilities or the technologies they use.
							<h3>Our Approach to Accessibility</h3>
							We aim to align with the Web Content Accessibility Guidelines (WCAG), which define internationally recognized standards for digital accessibility. While full compliance cannot always be guaranteed, we strive to implement improvements where feasible and regularly review accessibility-related aspects of our website.

							Accessibility is an ongoing process, and we are committed to improving the experience over time as technologies, standards, and user needs evolve.
							<h3>Accessibility Features</h3>
							To support accessibility, <strong>[Company Website]</strong> may utilize tools such as the OneTap accessibility toolbar. This interface provides users with a range of helpful features, including:
							<ul>
								<li>Adjustable text size and contrast settings</li>
								<li>Highlighting of links and text for better visibility</li>
								<li>Full keyboard navigation of the toolbar interface</li>
								<li>Quick launch via keyboard shortcut: <strong>Alt + .</strong> (Windows) or <strong>⌘ + .</strong> (Mac)</li>
							</ul>
							<strong>Please note the following:</strong>
							<ul>
								<li>The availability and effectiveness of these features depend on the website's configuration and ongoing maintenance.</li>
								<li>While we strive to ensure accessibility, we cannot guarantee that every part of <strong>[Company Website]</strong> will be fully accessible at all times. Some content may be provided by third parties or affected by technical constraints beyond our immediate control.</li>
							</ul>
							<h3>Feedback and Contact</h3>
							We welcome your feedback. If you experience any accessibility barriers or have suggestions for improvement, please contact us:

							Email: <strong>[Company E-Mail]</strong>

							We are committed to reviewing all inquiries and aim to respond within 3–5 business days. If you require assistance accessing any part of this website, we are happy to provide support through alternative channels upon request.
						<strong>Last updated:</strong> [March 9, 2025]
					</div>

					<!-- German -->
					<div data-content-lang="de" class="status-message-accessibility" style="display: none;">
						<h2>Barrierefreiheitserklärung für [Company Name]</h2>
						Bei <strong>[Company Name]</strong> setzen wir uns dafür ein, unsere digitale Präsenz so barrierefrei und inklusiv wie möglich zu gestalten – für alle Nutzer, einschließlich Menschen mit Behinderungen. Unser Ziel ist es, die Benutzerfreundlichkeit von <strong>[Company Website]</strong> zu verbessern und ein barrierefreies Erlebnis für alle zu unterstützen, unabhängig von ihren Fähigkeiten oder den verwendeten Technologien.

						<h3>Unser Ansatz zur Barrierefreiheit</h3>
						Wir orientieren uns an den Web Content Accessibility Guidelines (WCAG), die international anerkannte Standards für digitale Barrierefreiheit definieren. Auch wenn eine vollständige Konformität nicht immer garantiert werden kann, streben wir an, Verbesserungen umzusetzen, wo dies möglich ist, und überprüfen regelmäßig barrierebezogene Aspekte unserer Website.

						Barrierefreiheit ist ein fortlaufender Prozess, und wir sind bestrebt, das Nutzererlebnis kontinuierlich zu verbessern, während sich Technologien, Standards und Nutzerbedürfnisse weiterentwickeln.

						<h3>Barrierefreiheitsfunktionen</h3>
						Um die Barrierefreiheit zu unterstützen, kann <strong>[Company Website]</strong> Tools wie die OneTap-Barrierefreiheitstoolleiste verwenden. Diese Benutzeroberfläche bietet eine Reihe hilfreicher Funktionen, darunter:
						<ul>
							<li>Anpassbare Textgröße und Kontrasteinstellungen</li>
							<li>Hervorhebung von Links und Text zur besseren Sichtbarkeit</li>
							<li>Vollständige Tastaturnavigation der Toolleisten-Oberfläche</li>
							<li>Schnellstart über Tastenkombination: <strong>Alt + .</strong> (Windows) oder <strong>⌘ + .</strong> (Mac)</li>
						</ul>
						<strong>Bitte beachten Sie Folgendes:</strong>
						<ul>
							<li>Die Verfügbarkeit und Wirksamkeit dieser Funktionen hängen von der Konfiguration und laufenden Wartung der Website ab.</li>
							<li>Auch wenn wir bestrebt sind, Barrierefreiheit zu gewährleisten, können wir nicht garantieren, dass jeder Teil von <strong>[Company Website]</strong> jederzeit vollständig barrierefrei ist. Einige Inhalte können von Dritten bereitgestellt werden oder durch technische Einschränkungen außerhalb unserer Kontrolle beeinflusst sein.</li>
						</ul>
						<h3>Feedback und Kontakt</h3>
						Wir freuen uns über Ihr Feedback. Wenn Sie auf Barrieren stoßen oder Verbesserungsvorschläge haben, kontaktieren Sie uns bitte:

						E-Mail: <strong>[Company E-Mail]</strong>

						Wir sind bestrebt, alle Anfragen zu prüfen und innerhalb von 3–5 Werktagen zu antworten. Wenn Sie Unterstützung beim Zugriff auf Teile dieser Website benötigen, stellen wir auf Anfrage gerne alternative Zugangswege bereit.

						<strong>Letzte Aktualisierung:</strong> [March 9, 2025]
					</div>

					<!-- Spanish -->
					<div data-content-lang="es" class="status-message-accessibility" style="display: none;">
						<h2>Compromiso de Accesibilidad de [Company Name]</h2>
						En <strong>[Company Name]</strong>, estamos comprometidos a hacer que nuestra presencia digital sea lo más accesible e inclusiva posible para todos los usuarios, incluidas las personas con discapacidades. Nuestro objetivo es mejorar la usabilidad de <strong>[Company Website]</strong> y apoyar una experiencia más accesible para todos, independientemente de sus capacidades o las tecnologías que utilicen.

						<h3>Nuestro Enfoque sobre la Accesibilidad</h3>
						Aspiramos a alinearnos con las Pautas de Accesibilidad para el Contenido Web (WCAG), que definen estándares internacionalmente reconocidos para la accesibilidad digital. Si bien no siempre se puede garantizar el cumplimiento total, nos esforzamos por implementar mejoras cuando sea posible y revisar regularmente los aspectos relacionados con la accesibilidad de nuestro sitio web.

						La accesibilidad es un proceso continuo, y estamos comprometidos a mejorar la experiencia a lo largo del tiempo conforme evolucionan las tecnologías, los estándares y las necesidades de los usuarios.

						<h3>Funciones de Accesibilidad</h3>
						Para apoyar la accesibilidad, <strong>[Company Website]</strong> puede utilizar herramientas como la barra de accesibilidad OneTap. Esta interfaz proporciona a los usuarios una variedad de funciones útiles, que incluyen:
						<ul>
							<li>Ajuste del tamaño del texto y configuraciones de contraste</li>
							<li>Resaltado de enlaces y texto para mejorar la visibilidad</li>
							<li>Navegación completa por teclado en la interfaz de la barra</li>
							<li>Lanzamiento rápido mediante acceso directo del teclado: <strong>Alt + .</strong> (Windows) o <strong>⌘ + .</strong> (Mac)</li>
						</ul>
						<strong>Tenga en cuenta lo siguiente:</strong>
						<ul>
							<li>La disponibilidad y eficacia de estas funciones dependen de la configuración y el mantenimiento continuo del sitio web.</li>
							<li>Si bien nos esforzamos por garantizar la accesibilidad, no podemos asegurar que todas las partes de <strong>[Company Website]</strong> sean totalmente accesibles en todo momento. Parte del contenido puede ser proporcionado por terceros o estar afectado por limitaciones técnicas fuera de nuestro control inmediato.</li>
						</ul>
						<h3>Comentarios y Contacto</h3>
						Agradecemos sus comentarios. Si experimenta alguna barrera de accesibilidad o tiene sugerencias para mejorar, por favor contáctenos:

						Correo electrónico: <strong>[Company E-Mail]</strong>

						Estamos comprometidos a revisar todas las consultas y nuestro objetivo es responder dentro de los 3 a 5 días hábiles. Si necesita ayuda para acceder a cualquier parte de este sitio web, estaremos encantados de brindarle apoyo a través de canales alternativos a pedido.

						<strong>Última actualización:</strong> [March 9, 2025]
					</div>

					<!-- French -->
					<div data-content-lang="fr" class="status-message-accessibility" style="display: none;">
						<h2>Engagement en matière d'accessibilité de [Company Name]</h2>
						Chez <strong>[Company Name]</strong>, nous nous engageons à rendre notre présence numérique aussi accessible et inclusive que raisonnablement possible pour tous les utilisateurs, y compris les personnes en situation de handicap. Notre objectif est d'améliorer la convivialité de <strong>[Company Website]</strong> et de soutenir une expérience plus accessible pour tous, quelle que soit leur capacité ou la technologie qu'ils utilisent.

						<h3>Notre approche de l'accessibilité</h3>
						Nous visons à nous conformer aux directives pour l'accessibilité du contenu Web (WCAG), qui définissent des normes reconnues internationalement en matière d'accessibilité numérique. Bien que la conformité totale ne puisse pas toujours être garantie, nous nous efforçons de mettre en œuvre des améliorations lorsque cela est possible et révisons régulièrement les aspects liés à l'accessibilité de notre site.

						L'accessibilité est un processus continu, et nous nous engageons à améliorer l'expérience au fil du temps, à mesure que les technologies, les normes et les besoins des utilisateurs évoluent.

						<h3>Fonctionnalités d'accessibilité</h3>
						Pour favoriser l'accessibilité, <strong>[Company Website]</strong> peut utiliser des outils tels que la barre d'accessibilité OneTap. Cette interface propose diverses fonctionnalités utiles, notamment :
						<ul>
							<li>Paramètres de taille de texte et de contraste ajustables</li>
							<li>Surlignage des liens et du texte pour une meilleure visibilité</li>
							<li>Navigation complète au clavier de l'interface</li>
							<li>Lancement rapide via un raccourci clavier : <strong>Alt + .</strong> (Windows) ou <strong>⌘ + .</strong> (Mac)</li>
						</ul>
						<strong>Veuillez noter ce qui suit :</strong>
						<ul>
							<li>La disponibilité et l'efficacité de ces fonctionnalités dépendent de la configuration et de la maintenance continue du site.</li>
							<li>Bien que nous nous efforcions d'assurer l'accessibilité, nous ne pouvons garantir que chaque partie de <strong>[Company Website]</strong> soit toujours entièrement accessible. Certains contenus peuvent être fournis par des tiers ou être affectés par des contraintes techniques hors de notre contrôle immédiat.</li>
						</ul>
						<h3>Commentaires et contact</h3>
						Nous accueillons vos commentaires. Si vous rencontrez des obstacles liés à l'accessibilité ou si vous avez des suggestions d'amélioration, veuillez nous contacter :

						Email : <strong>[Company E-Mail]</strong>

						Nous nous engageons à examiner toutes les demandes et à répondre dans un délai de 3 à 5 jours ouvrables. Si vous avez besoin d'aide pour accéder à une partie de ce site, nous sommes heureux de vous fournir une assistance via des canaux alternatifs sur demande.

						<strong>Dernière mise à jour :</strong> [March 9, 2025]
						</div>

						<!-- Italian -->
						<div data-content-lang="it" class="status-message-accessibility" style="display: none;">
							<h2>Impegno per l'accessibilità di [Company Name]</h2>
							Presso <strong>[Company Name]</strong>, ci impegniamo a rendere la nostra presenza digitale il più accessibile e inclusiva possibile per tutti gli utenti, comprese le persone con disabilità. Il nostro obiettivo è migliorare l'usabilità di <strong>[Company Website]</strong> e supportare un'esperienza più accessibile per tutti, indipendentemente dalle capacità o dalle tecnologie utilizzate.

							<h3>Il nostro approccio all'accessibilità</h3>
							Cerchiamo di allinearci alle Linee guida per l'accessibilità dei contenuti web (WCAG), che definiscono standard riconosciuti a livello internazionale per l'accessibilità digitale. Sebbene non sia sempre possibile garantire la piena conformità, ci impegniamo ad apportare miglioramenti quando possibile e a rivedere regolarmente gli aspetti legati all'accessibilità del nostro sito.

							L'accessibilità è un processo continuo, e ci impegniamo a migliorare l'esperienza nel tempo, man mano che evolvono tecnologie, standard ed esigenze degli utenti.

							<h3>Funzionalità di accessibilità</h3>
							Per supportare l'accessibilità, <strong>[Company Website]</strong> può utilizzare strumenti come la barra di accessibilità OneTap. Questa interfaccia offre una serie di funzionalità utili, tra cui:
							<ul>
								<li>Dimensioni del testo e impostazioni di contrasto regolabili</li>
								<li>Evidenziazione di collegamenti e testi per una migliore visibilità</li>
								<li>Navigazione completa da tastiera dell'interfaccia</li>
								<li>Avvio rapido tramite scorciatoia da tastiera: <strong>Alt + .</strong> (Windows) o <strong>⌘ + .</strong> (Mac)</li>
							</ul>
							<strong>Si prega di notare quanto segue:</strong>
							<ul>
								<li>La disponibilità e l'efficacia di queste funzionalità dipendono dalla configurazione del sito e dalla sua manutenzione continua.</li>
								<li>Nonostante i nostri sforzi per garantire l’accessibilità, non possiamo assicurare che tutte le parti di <strong>[Company Website]</strong> siano sempre completamente accessibili. Alcuni contenuti potrebbero essere forniti da terze parti o influenzati da limitazioni tecniche al di fuori del nostro controllo.</li>
							</ul>
							<h3>Feedback e contatti</h3>
							Accogliamo con piacere i vostri feedback. Se riscontrate barriere all'accessibilità o avete suggerimenti per miglioramenti, vi preghiamo di contattarci:

							Email: <strong>[Company E-Mail]</strong>

							Siamo impegnati a esaminare tutte le richieste e a rispondere entro 3–5 giorni lavorativi. Se avete bisogno di assistenza per accedere a qualsiasi parte di questo sito web, saremo lieti di fornirla tramite canali alternativi su richiesta.

							<strong>Ultimo aggiornamento:</strong> [March 9, 2025]
						</div>

						<!-- Polish -->
						<div data-content-lang="pl" class="status-message-accessibility" style="display: none;">
							<h2>Zobowiązanie [Company Name] do dostępności</h2>
							W <strong>[Company Name]</strong> zobowiązujemy się do zapewnienia, aby nasza obecność cyfrowa była jak najbardziej dostępna i inkluzywna dla wszystkich użytkowników, w tym osób z niepełnosprawnościami. Naszym celem jest poprawa użyteczności <strong>[Company Website]</strong> i wspieranie bardziej dostępnego doświadczenia dla wszystkich, niezależnie od ich możliwości i używanych technologii.

							<h3>Nasze podejście do dostępności</h3>
							Dążymy do zgodności z Wytycznymi dotyczącymi dostępności treści internetowych (WCAG), które definiują uznane na całym świecie standardy dostępności cyfrowej. Chociaż nie zawsze można zagwarantować pełną zgodność, staramy się wdrażać ulepszenia tam, gdzie to możliwe, i regularnie przeglądamy aspekty związane z dostępnością naszej strony.

							Dostępność to proces ciągły, a my zobowiązujemy się do ciągłego ulepszania doświadczenia wraz z rozwojem technologii, standardów i potrzeb użytkowników.

							<h3>Funkcje dostępności</h3>
							Aby wspierać dostępność, <strong>[Company Website]</strong> może wykorzystywać narzędzia takie jak pasek narzędzi dostępności OneTap. Interfejs ten oferuje użytkownikom szereg przydatnych funkcji, w tym:
							<ul>
								<li>Regulacja rozmiaru tekstu i ustawień kontrastu</li>
								<li>Wyróżnianie linków i tekstu dla lepszej widoczności</li>
								<li>Pełna nawigacja klawiaturą po interfejsie</li>
								<li>Szybkie uruchamianie za pomocą skrótu klawiaturowego: <strong>Alt + .</strong> (Windows) lub <strong>⌘ + .</strong> (Mac)</li>
							</ul>
							<strong>Proszę zauważyć:</strong>
							<ul>
								<li>Dostępność i skuteczność tych funkcji zależy od konfiguracji i bieżącego utrzymania strony.</li>
								<li>Mimo że dokładamy starań, aby zapewnić dostępność, nie możemy zagwarantować, że każda część <strong>[Company Website]</strong> będzie zawsze w pełni dostępna. Niektóre treści mogą być dostarczane przez strony trzecie lub być ograniczone przez czynniki techniczne poza naszą bezpośrednią kontrolą.</li>
							</ul>
							<h3>Informacje zwrotne i kontakt</h3>
							Zachęcamy do przesyłania opinii. Jeśli napotkasz bariery dostępności lub masz sugestie dotyczące ulepszeń, skontaktuj się z nami:

							E-mail: <strong>[Company E-Mail]</strong>

							Zobowiązujemy się do przeglądania wszystkich zapytań i udzielenia odpowiedzi w ciągu 3–5 dni roboczych. Jeśli potrzebujesz pomocy w uzyskaniu dostępu do jakiejkolwiek części tej strony internetowej, z przyjemnością udzielimy wsparcia za pośrednictwem alternatywnych kanałów na życzenie.

							<strong>Ostatnia aktualizacja:</strong> [March 9, 2025]
						</div>

						<!-- Swedish -->
						<div data-content-lang="se" class="status-message-accessibility" style="display: none;">
							<h2>[Company Name]s tillgänglighetsåtagande</h2>
							På <strong>[Company Name]</strong> strävar vi efter att göra vår digitala närvaro så tillgänglig och inkluderande som möjligt för alla användare, inklusive personer med funktionsnedsättning. Vårt mål är att förbättra användbarheten på <strong>[Company Website]</strong> och erbjuda en mer tillgänglig upplevelse för alla, oavsett förmåga eller teknik som används.

							<h3>Vår tillgänglighetsstrategi</h3>
							Vi strävar efter att följa riktlinjerna för tillgängligt webbinnehåll (WCAG), som fastställer internationellt erkända standarder för digital tillgänglighet. Även om fullständig överensstämmelse inte alltid kan garanteras, arbetar vi kontinuerligt för att förbättra tillgängligheten och granskar regelbundet våra webbplatsfunktioner.

							Tillgänglighet är en pågående process, och vi är engagerade i att förbättra upplevelsen i takt med att teknologier, standarder och användarbehov utvecklas.

							<h3>Tillgänglighetsfunktioner</h3>
							För att stödja tillgänglighet kan <strong>[Company Website]</strong> använda verktyg som OneTap-tillgänglighetsfältet. Detta gränssnitt erbjuder funktioner som:
							<ul>
								<li>Justerbar textstorlek och kontrast</li>
								<li>Markering av länkar och text för bättre synlighet</li>
								<li>Fullständig tangentbordsnavigering</li>
								<li>Snabbstart via tangentbordsgenväg: <strong>Alt + .</strong> (Windows) eller <strong>⌘ + .</strong> (Mac)</li>
							</ul>
							<strong>Observera följande:</strong>
							<ul>
								<li>Tillgänglighet och funktionalitet beror på webbplatsens konfiguration och underhåll.</li>
								<li>Vi kan inte garantera att alla delar av <strong>[Company Website]</strong> alltid är helt tillgängliga. Viss funktionalitet kan påverkas av tredjepartsinnehåll eller tekniska begränsningar utanför vår kontroll.</li>
							</ul>
							<h3>Feedback och kontakt</h3>
							Vi välkomnar din feedback. Om du stöter på hinder eller har förslag på förbättringar, vänligen kontakta oss:

							E-post: <strong>[Company E-Mail]</strong>

							Vi svarar vanligtvis inom 3–5 arbetsdagar. Om du behöver hjälp med åtkomst till något på denna webbplats, hjälper vi dig gärna via alternativa kanaler.

							<strong>Senast uppdaterad:</strong> [March 9, 2025]
						</div>

						<!-- Finnish -->
						<div data-content-lang="fi" class="status-message-accessibility" style="display: none;">
							<h2>[Company Name] – Sitoutuminen saavutettavuuteen</h2>
							<strong>[Company Name]</strong> on sitoutunut tekemään digitaalisesta sisällöstään mahdollisimman saavutettavaa ja inklusiivista kaikille käyttäjille, myös vammaisille henkilöille. Tavoitteemme on parantaa <strong>[Company Website]</strong>-sivuston käytettävyyttä ja tukea esteetöntä käyttökokemusta kaikille käyttäjille.

							<h3>Lähestymistapamme saavutettavuuteen</h3>
							Tavoitteenamme on noudattaa WCAG-ohjeistusta (Web Content Accessibility Guidelines), jotka määrittelevät kansainvälisesti tunnustetut standardit digitaaliseen saavutettavuuteen. Vaikka täysi vaatimustenmukaisuus ei ole aina mahdollista, pyrimme jatkuvasti parantamaan saavutettavuutta ja tarkistamme säännöllisesti sivustomme ominaisuuksia.

							Saavutettavuus on jatkuva prosessi, ja kehitämme palveluamme jatkuvasti teknologian, standardien ja käyttäjätarpeiden kehittyessä.

							<h3>Saavutettavuusominaisuudet</h3>
							Tukeakseen saavutettavuutta, <strong>[Company Website]</strong> voi hyödyntää työkaluja kuten OneTap-saavutettavuuspalkkia. Tämä käyttöliittymä tarjoaa muun muassa seuraavia ominaisuuksia:
							<ul>
								<li>Säädettävä tekstin koko ja kontrasti</li>
								<li>Linkkien ja tekstien korostus näkyvyyden parantamiseksi</li>
								<li>Kokonaan näppäimistöllä ohjattava käyttöliittymä</li>
								<li>Pikanäppäin käynnistykseen: <strong>Alt + .</strong> (Windows) tai <strong>⌘ + .</strong> (Mac)</li>
							</ul>
							<strong>Huomioitavaa:</strong>
							<ul>
								<li>Ominaisuuksien toimivuus ja saatavuus riippuvat sivuston asetuksista ja ylläpidosta.</li>
								<li>Vaikka teemme parhaamme, emme voi taata, että <strong>[Company Website]</strong> on aina täysin saavutettava. Kolmannen osapuolen sisältö tai tekniset rajoitteet voivat vaikuttaa saavutettavuuteen.</li>
							</ul>
							<h3>Palaute ja yhteydenotto</h3>
							Odotamme mielellämme palautettasi. Jos kohtaat esteitä tai sinulla on parannusehdotuksia, voit ottaa meihin yhteyttä:

							Sähköposti: <strong>[Company E-Mail]</strong>

							Pyrimme vastaamaan kaikkiin viesteihin 3–5 arkipäivän kuluessa. Jos tarvitset apua jonkin sivun osan käyttämisessä, tarjoamme mielellämme apua vaihtoehtoisia kanavia pitkin.

							<strong>Viimeksi päivitetty:</strong> [March 9, 2025]
						</div>

						<!-- Portuguese -->
						<div data-content-lang="pt" class="status-message-accessibility" style="display: none;">
							<h2>Compromisso de Acessibilidade da [Company Name]</h2>
							Na <strong>[Company Name]</strong>, estamos comprometidos em tornar nossa presença digital o mais acessível e inclusiva possível para todos os usuários, incluindo pessoas com deficiência. Nosso objetivo é melhorar a usabilidade do <strong>[Company Website]</strong> e promover uma experiência acessível para todos, independentemente de suas capacidades ou tecnologia utilizada.

							<h3>Nossa abordagem à acessibilidade</h3>
							Buscamos seguir as Diretrizes de Acessibilidade para Conteúdo da Web (WCAG), que estabelecem padrões reconhecidos internacionalmente para acessibilidade digital. Embora não seja sempre possível garantir total conformidade, trabalhamos continuamente para implementar melhorias e revisar regularmente os aspectos de acessibilidade do nosso site.

							A acessibilidade é um processo contínuo, e estamos comprometidos em aprimorar a experiência ao longo do tempo, à medida que as tecnologias, normas e necessidades dos usuários evoluem.

							<h3>Recursos de acessibilidade</h3>
							Para promover a acessibilidade, o <strong>[Company Website]</strong> pode utilizar ferramentas como a barra de acessibilidade OneTap. Essa interface oferece uma variedade de recursos úteis, como:
							<ul>
								<li>Ajuste de tamanho de texto e contraste</li>
								<li>Destaque de links e textos para melhor visibilidade</li>
								<li>Navegação completa por teclado</li>
								<li>Atalho de teclado para ativação: <strong>Alt + .</strong> (Windows) ou <strong>⌘ + .</strong> (Mac)</li>
							</ul>
							<strong>Observações importantes:</strong>
							<ul>
								<li>A disponibilidade e a eficácia dessas funcionalidades dependem da configuração e da manutenção contínua do site.</li>
								<li>Embora nos esforcemos para garantir acessibilidade, não podemos garantir que todas as partes do <strong>[Company Website]</strong> sejam sempre totalmente acessíveis. Alguns conteúdos podem ser fornecidos por terceiros ou limitados por restrições técnicas fora do nosso controle imediato.</li>
							</ul>
							<h3>Feedback e contato</h3>
							Agradecemos o seu feedback. Se encontrar barreiras de acessibilidade ou tiver sugestões de melhoria, entre em contato conosco:

							E-mail: <strong>[Company E-Mail]</strong>

							Nos comprometemos a revisar todas as solicitações e responder em até 3 a 5 dias úteis. Se precisar de ajuda para acessar qualquer parte deste site, teremos prazer em fornecer suporte por canais alternativos mediante solicitação.

							<strong>Última atualização:</strong> [March 9, 2025]
						</div>

						<!-- Romanian -->
						<div data-content-lang="ro" class="status-message-accessibility" style="display: none;">
							<h2>Angajamentul [Company Name] privind accesibilitatea</h2>
							La <strong>[Company Name]</strong>, ne angajăm să oferim o experiență digitală cât mai accesibilă și incluzivă pentru toți utilizatorii, inclusiv pentru persoanele cu dizabilități. Obiectivul nostru este să îmbunătățim utilizabilitatea <strong>[Company Website]</strong> și să facilităm accesul pentru toată lumea, indiferent de abilități sau tehnologia utilizată.

							<h3>Abordarea noastră în materie de accesibilitate</h3>
							Urmărim respectarea Ghidurilor pentru Accesibilitatea Conținutului Web (WCAG), care stabilesc standarde recunoscute internațional pentru accesibilitatea digitală. Deși conformitatea completă nu poate fi întotdeauna garantată, lucrăm constant pentru a îmbunătăți accesibilitatea și revizuim periodic funcționalitățile site-ului nostru.

							Accesibilitatea este un proces continuu, iar noi ne angajăm să evoluăm odată cu tehnologiile, standardele și nevoile utilizatorilor.

							<h3>Funcționalități de accesibilitate</h3>
							Pentru a susține accesibilitatea, <strong>[Company Website]</strong> poate utiliza instrumente precum bara de accesibilitate OneTap. Aceasta oferă funcții utile precum:
							<ul>
								<li>Ajustarea dimensiunii textului și a contrastului</li>
								<li>Evidențierea linkurilor și textelor pentru o vizibilitate mai bună</li>
								<li>Navigare completă cu tastatura</li>
								<li>Comandă rapidă de tastatură: <strong>Alt + .</strong> (Windows) sau <strong>⌘ + .</strong> (Mac)</li>
							</ul>
							<strong>Vă rugăm să rețineți:</strong>
							<ul>
								<li>Disponibilitatea și funcționalitatea acestor instrumente depind de configurația și întreținerea site-ului.</li>
								<li>Nu putem garanta că toate secțiunile <strong>[Company Website]</strong> sunt mereu complet accesibile. Unele conținuturi pot proveni de la terți sau pot fi limitate din motive tehnice.</li>
							</ul>
							<h3>Feedback și contact</h3>
							Apreciem opiniile dumneavoastră. Dacă întâmpinați bariere de accesibilitate sau aveți sugestii, vă rugăm să ne contactați:

							E-mail: <strong>[Company E-Mail]</strong>

							De obicei răspundem în 3–5 zile lucrătoare. Dacă aveți nevoie de asistență pentru a accesa conținutul site-ului, vom oferi suport prin canale alternative.

							<strong>Ultima actualizare:</strong> [March 9, 2025]
						</div>

						<!-- Slovenian -->
						<div data-content-lang="si" class="status-message-accessibility" style="display: none;">
							<h2>Zaveza družbe [Company Name] k dostopnosti</h2>
							V podjetju <strong>[Company Name]</strong> si prizadevamo, da bi bila naša digitalna prisotnost čim bolj dostopna in vključujoča za vse uporabnike, tudi za osebe z oviranostmi. Naš cilj je izboljšati uporabnost spletne strani <strong>[Company Website]</strong> ter omogočiti enakovredno izkušnjo vsem uporabnikom.

							<h3>Naš pristop k dostopnosti</h3>
							Prizadevamo si slediti Smernicam za dostopnost spletnih vsebin (WCAG), ki določajo mednarodno priznane standarde digitalne dostopnosti. Čeprav popolne skladnosti ni vedno mogoče zagotoviti, si nenehno prizadevamo izboljšati dostopnost in redno pregledujemo funkcionalnosti spletne strani.

							Dostopnost je stalen proces in zavezani smo k izboljšavam, ki sledijo razvoju tehnologij, standardov in potreb uporabnikov.

							<h3>Funkcije dostopnosti</h3>
							Za podporo dostopnosti lahko <strong>[Company Website]</strong> uporablja orodja, kot je orodna vrstica za dostopnost OneTap. Ta vmesnik vključuje funkcije, kot so:
							<ul>
								<li>Prilagoditev velikosti besedila in kontrasta</li>
								<li>Poudarjanje povezav in besedila za boljšo vidljivost</li>
								<li>Popolna navigacija s tipkovnico</li>
								<li>Bližnjica na tipkovnici za aktivacijo: <strong>Alt + .</strong> (Windows) ali <strong>⌘ + .</strong> (Mac)</li>
							</ul>
							<strong>Pomembne opombe:</strong>
							<ul>
								<li>Funkcionalnost je odvisna od konfiguracije in vzdrževanja spletnega mesta.</li>
								<li>Ne moremo zagotoviti, da bodo vse vsebine na <strong>[Company Website]</strong> vedno popolnoma dostopne. Nekatere omejitve so lahko posledica vsebin tretjih oseb ali tehničnih omejitev.</li>
							</ul>
							<h3>Povratne informacije in stik</h3>
							Veseli bomo vaših povratnih informacij. Če naletite na ovire pri dostopnosti ali imate predloge za izboljšave, nam pišite:

							E-pošta: <strong>[Company E-Mail]</strong>

							Na sporočila običajno odgovorimo v roku 3–5 delovnih dni. V primeru potrebe po pomoči bomo z veseljem ponudili podporo po alternativnih kanalih.

							<strong>Zadnja posodobitev:</strong> [March 9, 2025]
						</div>

						<!-- Slovak -->
						<div data-content-lang="sk" class="status-message-accessibility" style="display: none;">
							<h2>Záväzok spoločnosti [Company Name] k prístupnosti</h2>
							V spoločnosti <strong>[Company Name]</strong> sa usilujeme o to, aby naša digitálna prezentácia bola čo najprístupnejšia a najinkluzívnejšia pre všetkých používateľov, vrátane osôb so zdravotným znevýhodnením. Naším cieľom je zlepšiť použiteľnosť stránky <strong>[Company Website]</strong> a zabezpečiť rovnaký prístup pre všetkých bez ohľadu na schopnosti alebo použitú technológiu.

							<h3>Náš prístup k prístupnosti</h3>
							Snažíme sa dodržiavať Smernice pre prístupnosť webového obsahu (WCAG), ktoré definujú medzinárodne uznávané štandardy digitálnej prístupnosti. Aj keď úplné splnenie všetkých požiadaviek nie je vždy možné, neustále pracujeme na zlepšovaní prístupnosti a pravidelne kontrolujeme funkcie našej webovej stránky.

							Prístupnosť je neustály proces a zaväzujeme sa ju zlepšovať v súlade s vývojom technológií, štandardov a potrieb používateľov.

							<h3>Funkcie prístupnosti</h3>
							Na podporu prístupnosti môže <strong>[Company Website]</strong> používať nástroje ako prístupový panel OneTap. Tento nástroj ponúka funkcie ako:
							<ul>
								<li>Nastaviteľná veľkosť písma a kontrast</li>
								<li>Zvýrazňovanie odkazov a textu pre lepšiu viditeľnosť</li>
								<li>Plná navigácia pomocou klávesnice</li>
								<li>Klávesová skratka na spustenie: <strong>Alt + .</strong> (Windows) alebo <strong>⌘ + .</strong> (Mac)</li>
							</ul>
							<strong>Upozornenia:</strong>
							<ul>
								<li>Dostupnosť a funkčnosť týchto nástrojov závisí od nastavenia a údržby stránky.</li>
								<li>Nie je možné zaručiť úplnú prístupnosť všetkých častí <strong>[Company Website]</strong>, najmä ak ide o obsah od tretích strán alebo technické obmedzenia.</li>
							</ul>
							<h3>Spätná väzba a kontakt</h3>
							Budeme radi za vašu spätnú väzbu. Ak narazíte na prekážky v prístupnosti alebo máte návrhy na zlepšenie, kontaktujte nás:

							E-mail: <strong>[Company E-Mail]</strong>

							Zvyčajne odpovedáme do 3–5 pracovných dní. V prípade potreby vám radi poskytneme pomoc alternatívnymi spôsobmi.

							<strong>Posledná aktualizácia:</strong> [March 9, 2025]
						</div>

						<!-- Dutch -->
						<div data-content-lang="nl" class="status-message-accessibility" style="display: none;">
							<h2>[Company Name] toegankelijkheidsverklaring</h2>
							Bij <strong>[Company Name]</strong> zetten we ons in voor een zo toegankelijk en inclusief mogelijke digitale ervaring voor alle gebruikers, inclusief mensen met een beperking. Ons doel is om de bruikbaarheid van <strong>[Company Website]</strong> te verbeteren en gelijkwaardige toegang te bieden aan iedereen, ongeacht vaardigheden of gebruikte technologie.

							<h3>Onze aanpak voor toegankelijkheid</h3>
							We streven ernaar om te voldoen aan de Web Content Accessibility Guidelines (WCAG), die internationaal erkende normen bieden voor digitale toegankelijkheid. Hoewel volledige naleving niet altijd mogelijk is, werken we voortdurend aan verbeteringen en voeren we regelmatig controles uit.

							Toegankelijkheid is een doorlopend proces en we blijven evolueren met technologie, standaarden en gebruikersbehoeften.

							<h3>Toegankelijkheidsfuncties</h3>
							Om toegankelijkheid te ondersteunen, kan <strong>[Company Website]</strong> tools gebruiken zoals de OneTap-toegankelijkheidsbalk. Deze interface biedt functies zoals:
							<ul>
								<li>Aanpassen van tekstgrootte en contrast</li>
								<li>Links en tekst markeren voor betere zichtbaarheid</li>
								<li>Volledige navigatie via het toetsenbord</li>
								<li>Sneltoets voor activering: <strong>Alt + .</strong> (Windows) of <strong>⌘ + .</strong> (Mac)</li>
							</ul>
							<strong>Let op:</strong>
							<ul>
								<li>De beschikbaarheid van deze functies is afhankelijk van de configuratie en het onderhoud van de website.</li>
								<li>We kunnen niet garanderen dat alle delen van <strong>[Company Website]</strong> altijd volledig toegankelijk zijn. Sommige inhoud kan van derden afkomstig zijn of technische beperkingen hebben.</li>
							</ul>
							<h3>Feedback en contact</h3>
							We waarderen uw feedback. Als u toegankelijkheidsproblemen ervaart of suggesties heeft, neem dan contact met ons op:

							E-mail: <strong>[Company E-Mail]</strong>

							We reageren doorgaans binnen 3–5 werkdagen. Indien nodig bieden we ondersteuning via alternatieve kanalen.

							<strong>Laatst bijgewerkt:</strong> [March 9, 2025]
						</div>

						<!-- Danish -->
						<div data-content-lang="dk" class="status-message-accessibility" style="display: none;">
							<h2>[Company Name]’s tilgængelighedserklæring</h2>
							Hos <strong>[Company Name]</strong> stræber vi efter at levere en så tilgængelig og inkluderende digital oplevelse som muligt for alle brugere, herunder personer med handicap. Vores mål er at forbedre brugervenligheden på <strong>[Company Website]</strong> og sikre lige adgang for alle, uanset evner eller teknologi.

							<h3>Vores tilgang til tilgængelighed</h3>
							Vi arbejder efter Web Content Accessibility Guidelines (WCAG), som er internationalt anerkendte standarder for digital tilgængelighed. Selvom fuld overensstemmelse ikke altid kan garanteres, arbejder vi løbende på forbedringer og gennemgår regelmæssigt funktionaliteten på vores side.

							Tilgængelighed er en løbende proces, og vi forpligter os til at udvikle os i takt med teknologi, standarder og brugernes behov.

							<h3>Tilgængelighedsfunktioner</h3>
							<strong>[Company Website]</strong> kan bruge værktøjer som OneTap-tilgængelighedsbjælken. Den indeholder funktioner såsom:
							<ul>
								<li>Justerbar tekststørrelse og kontrast</li>
								<li>Fremhævning af links og tekst for bedre synlighed</li>
								<li>Fuld tastaturnavigation</li>
								<li>Genvejstast til aktivering: <strong>Alt + .</strong> (Windows) eller <strong>⌘ + .</strong> (Mac)</li>
							</ul>
							<strong>Bemærk:</strong>
							<ul>
								<li>Funktionalitet afhænger af konfigurationen og vedligeholdelsen af websitet.</li>
								<li>Vi kan ikke garantere, at hele <strong>[Company Website]</strong> til enhver tid er fuldt tilgængelig. Nogle indholdselementer kan være leveret af tredjeparter eller være teknisk begrænsede.</li>
							</ul>
							<h3>Feedback og kontakt</h3>
							Vi sætter pris på din feedback. Hvis du oplever problemer med tilgængelighed eller har forslag, kontakt os gerne:

							E-mail: <strong>[Company E-Mail]</strong>

							Vi svarer normalt inden for 3–5 arbejdsdage. Vi tilbyder gerne hjælp via alternative kanaler, hvis det er nødvendigt.

							<strong>Senest opdateret:</strong> [March 9, 2025]
						</div>

						<!-- Greek -->
						<div data-content-lang="gr" class="status-message-accessibility" style="display: none;">
							<h2>Δήλωση προσβασιμότητας από την [Company Name]</h2>
							Στην <strong>[Company Name]</strong>, δεσμευόμαστε να παρέχουμε μια ψηφιακή εμπειρία όσο το δυνατόν πιο προσβάσιμη και χωρίς αποκλεισμούς για όλους τους χρήστες, συμπεριλαμβανομένων των ατόμων με αναπηρίες. Στόχος μας είναι η βελτίωση της χρηστικότητας της ιστοσελίδας <strong>[Company Website]</strong> και η εξασφάλιση ίσης πρόσβασης ανεξαρτήτως ικανοτήτων ή τεχνολογίας.

							<h3>Η προσέγγισή μας στην προσβασιμότητα</h3>
							Ακολουθούμε τις Οδηγίες για την Προσβασιμότητα Περιεχομένου Ιστού (WCAG), οι οποίες ορίζουν διεθνώς αποδεκτά πρότυπα για την ψηφιακή προσβασιμότητα. Αν και η πλήρης συμμόρφωση δεν είναι πάντα εφικτή, εργαζόμαστε διαρκώς για να βελτιώνουμε την προσβασιμότητα και επανεξετάζουμε τακτικά τη λειτουργικότητα της ιστοσελίδας μας.

							Η προσβασιμότητα είναι μια συνεχής διαδικασία και δεσμευόμαστε να προσαρμοζόμαστε σύμφωνα με τις εξελίξεις στην τεχνολογία και τις ανάγκες των χρηστών.

							<h3>Λειτουργίες προσβασιμότητας</h3>
							Η ιστοσελίδα <strong>[Company Website]</strong> μπορεί να χρησιμοποιεί εργαλεία όπως η γραμμή προσβασιμότητας OneTap, η οποία περιλαμβάνει λειτουργίες όπως:
							<ul>
								<li>Ρυθμιζόμενο μέγεθος κειμένου και αντίθεση</li>
								<li>Επισήμανση συνδέσμων και κειμένου για καλύτερη ορατότητα</li>
								<li>Πλήρης πλοήγηση μέσω πληκτρολογίου</li>
								<li>Συντόμευση για ενεργοποίηση: <strong>Alt + .</strong> (Windows) ή <strong>⌘ + .</strong> (Mac)</li>
							</ul>
							<strong>Σημειώσεις:</strong>
							<ul>
								<li>Η διαθεσιμότητα και η λειτουργία αυτών των εργαλείων εξαρτώνται από τη ρύθμιση και συντήρηση της ιστοσελίδας.</li>
								<li>Δεν μπορούμε να εγγυηθούμε πλήρη προσβασιμότητα σε όλες τις ενότητες του <strong>[Company Website]</strong>. Ορισμένο περιεχόμενο ενδέχεται να παρέχεται από τρίτους ή να υπόκειται σε τεχνικούς περιορισμούς.</li>
							</ul>
							<h3>Σχόλια και επικοινωνία</h3>
							Εκτιμούμε τα σχόλιά σας. Αν αντιμετωπίζετε προβλήματα προσβασιμότητας ή έχετε προτάσεις, επικοινωνήστε μαζί μας:

							E-mail: <strong>[Company E-Mail]</strong>

							Απαντούμε συνήθως εντός 3–5 εργάσιμων ημερών. Αν χρειάζεστε βοήθεια, μπορούμε να παρέχουμε υποστήριξη μέσω εναλλακτικών καναλιών.

							<strong>Τελευταία ενημέρωση:</strong> [March 9, 2025]
						</div>

						<!-- Czech -->
						<div data-content-lang="cz" class="status-message-accessibility" style="display: none;">
							<h2>Prohlášení o přístupnosti – [Company Name]</h2>
							Ve společnosti <strong>[Company Name]</strong> se snažíme poskytovat co nejpřístupnější a nejinkluzivnější digitální zážitek pro všechny uživatele, včetně osob se zdravotním postižením. Naším cílem je zlepšit použitelnost webu <strong>[Company Website]</strong> a zajistit rovný přístup pro každého bez ohledu na schopnosti nebo používanou technologii.

							<h3>Náš přístup k přístupnosti</h3>
							Usilujeme o dodržování Web Content Accessibility Guidelines (WCAG), které stanovují mezinárodní standardy pro digitální přístupnost. I když nemusíme vždy dosáhnout plné shody, neustále pracujeme na zlepšování a pravidelně provádíme kontroly.

							Přístupnost je kontinuální proces a přizpůsobujeme se vývoji technologií, standardů a potřeb uživatelů.

							<h3>Funkce pro přístupnost</h3>
							Web <strong>[Company Website]</strong> může využívat nástroje jako přístupovou lištu OneTap, která obsahuje funkce jako:
							<ul>
								<li>Změna velikosti písma a kontrastu</li>
								<li>Zvýraznění odkazů a textu</li>
								<li>Plná navigace pomocí klávesnice</li>
								<li>Klávesová zkratka pro aktivaci: <strong>Alt + .</strong> (Windows) nebo <strong>⌘ + .</strong> (Mac)</li>
							</ul>
							<strong>Upozornění:</strong>
							<ul>
								<li>Dostupnost těchto funkcí závisí na konfiguraci a údržbě webu.</li>
								<li>Nemůžeme zaručit, že všechny části <strong>[Company Website]</strong> budou vždy plně přístupné. Některý obsah může být poskytován třetími stranami nebo omezen technicky.</li>
							</ul>
							<h3>Zpětná vazba a kontakt</h3>
							Vaše zpětná vazba je pro nás důležitá. Pokud narazíte na problém s přístupností nebo máte návrh, kontaktujte nás:

							E-mail: <strong>[Company E-Mail]</strong>

							Odpovídáme obvykle do 3–5 pracovních dnů. V případě potřeby můžeme nabídnout pomoc i alternativními kanály.

							<strong>Poslední aktualizace:</strong> [March 9, 2025]
						</div>

						<!-- Hungarian -->
						<div data-content-lang="hu" class="status-message-accessibility" style="display: none;">
							<h2>[Company Name] elkötelezettsége az akadálymentesség iránt</h2>
							A <strong>[Company Name]</strong> elkötelezett amellett, hogy digitális tartalmait mindenki számára elérhetővé és befogadóvá tegye, beleértve a fogyatékkal élő személyeket is. Célunk, hogy javítsuk a <strong>[Company Website]</strong> használhatóságát, és egyenlő hozzáférést biztosítsunk minden felhasználónak – képességtől vagy használt technológiától függetlenül.

							<h3>Megközelítésünk az akadálymentességhez</h3>
							Igyekszünk megfelelni a nemzetközi szabványoknak, például a Web Content Accessibility Guidelines (WCAG) irányelveinek. Bár a teljes megfelelés nem minden esetben biztosítható, folyamatosan dolgozunk az akadálymentesség javításán, és rendszeresen felülvizsgáljuk az érintett tartalmakat.

							Az akadálymentesség egy folyamatos folyamat — elkötelezettek vagyunk a fejlesztés iránt, ahogy a technológia és a felhasználói igények változnak.

							<h3>Akadálymentes funkciók</h3>
							A <strong>[Company Website]</strong> használhat olyan eszközöket, mint a OneTap akadálymentességi sáv, amely a következőket kínálja:
							<ul>
								<li>Szövegméret és kontraszt beállítása</li>
								<li>Hivatkozások és szövegek kiemelése</li>
								<li>Teljes billentyűzetes navigáció</li>
								<li>Billentyűparancs: <strong>Alt + .</strong> (Windows) vagy <strong>⌘ + .</strong> (Mac)</li>
							</ul>
							<strong>Megjegyzések:</strong>
							<ul>
								<li>A funkciók a webhely beállításaitól és karbantartásától függenek.</li>
								<li>Nem tudjuk garantálni, hogy a <strong>[Company Website]</strong> minden része teljesen akadálymentes. Egyes tartalmak harmadik féltől származhatnak, vagy technikai korlátok lehetnek.</li>
							</ul>

							<h3>Visszajelzés és kapcsolat</h3>
							Értékeljük a visszajelzéseket. Ha bármilyen akadálymentességi problémát tapasztal, vagy javaslata van, kérjük, lépjen kapcsolatba velünk:

							E-mail: <strong>[Company E-Mail]</strong>

							Általában 3–5 munkanapon belül válaszolunk. Szükség esetén alternatív kommunikációs csatornán is tudunk segíteni.

							<strong>Utolsó frissítés:</strong> [2025. március 9.]
						</div>

						<!-- Lithuanian -->
						<div data-content-lang="lt" class="status-message-accessibility" style="display: none;">
							<h2>[Company Name] prieinamumo pareiškimas</h2>
							<strong>[Company Name]</strong> siekia užtikrinti kuo prieinamesnę ir labiau įtraukią skaitmeninę patirtį visiems naudotojams, įskaitant žmones su negalia. Mūsų tikslas – pagerinti <strong>[Company Website]</strong> naudojamumą ir užtikrinti vienodas galimybes visiems, nepriklausomai nuo gebėjimų ar naudojamų technologijų.

							<h3>Mūsų požiūris į prieinamumą</h3>
							Siekdami prieinamumo vadovaujamės žiniatinklio turinio prieinamumo gairėmis (WCAG), kurios yra tarptautiniu mastu pripažinti standartai. Nors ne visada įmanoma užtikrinti visišką atitiktį, mes nuolat dirbame siekdami tobulėjimo ir reguliariai tikriname savo svetainę.

							Prieinamumas yra nuolatinis procesas, kurį nuolat tobuliname kartu su technologijų, standartų ir naudotojų poreikių kaita.

							<h3>Prieinamumo funkcijos</h3>
							<strong>[Company Website]</strong> gali naudoti tokius įrankius kaip OneTap prieinamumo juosta. Ji apima:
							<ul>
								<li>Teksto dydžio ir kontrasto reguliavimą</li>
								<li>Nuorodų ir teksto paryškinimą</li>
								<li>Pilną naršymą klaviatūra</li>
								<li>Aktyvinimo spartusis klavišas: <strong>Alt + .</strong> (Windows) arba <strong>⌘ + .</strong> (Mac)</li>
							</ul>
							<strong>Pastabos:</strong>
							<ul>
								<li>Šios funkcijos priklauso nuo svetainės konfigūracijos ir priežiūros.</li>
								<li>Negalime garantuoti, kad visas <strong>[Company Website]</strong> turinys visada bus visiškai prieinamas. Kai kurios dalys gali būti trečiųjų šalių ar turėti techninių apribojimų.</li>
							</ul>
							<h3>Atsiliepimai ir kontaktai</h3>
							Jūsų atsiliepimai mums svarbūs. Jei pastebėjote prieinamumo problemų ar turite pasiūlymų, susisiekite su mumis:

							El. paštas: <strong>[Company E-Mail]</strong>

							Dažniausiai atsakome per 3–5 darbo dienas. Esant poreikiui, galime suteikti pagalbą ir kitais kanalais.

							<strong>Paskutinį kartą atnaujinta:</strong> [March 9, 2025]
						</div>

						<!-- Latvian -->
						<div data-content-lang="lv" class="status-message-accessibility" style="display: none;">
							<h2>[Company Name] pieejamības apņemšanās</h2>
							<strong>[Company Name]</strong> apņemas padarīt savu digitālo saturu pēc iespējas pieejamu un iekļaujošu visiem lietotājiem, tostarp personām ar invaliditāti. Mūsu mērķis ir uzlabot <strong>[Company Website]</strong> lietojamību un nodrošināt vienlīdzīgu pieeju neatkarīgi no lietotāja spējām vai tehnoloģijām.

							<h3>Mūsu pieejas veids pieejamībai</h3>
							Mēs cenšamies ievērot Web Content Accessibility Guidelines (WCAG), kas nosaka starptautiski atzītus standartus digitālajai pieejamībai. Lai gan pilnīga atbilstība dažkārt nav iespējama, mēs pastāvīgi uzlabojam pieejamību un regulāri pārskatām pār to atbildīgās sadaļas.

							Pieejamība ir nepārtraukts process – mēs apņēmīgi strādājam, lai uzlabotu pieredzi atbilstoši tehnoloģiju, standartu un lietotāju vajadzību attīstībai.

							<h3>Piezīmes par pieejamību</h3>
							Lai atbalstītu pieejamību, <strong>[Company Website]</strong> var izmantot tādus rīkus kā OneTap pieejamības rīkjosla, kas nodrošina:
							<ul>
								<li>Teksta izmēra un kontrasta regulēšana</li>
								<li>Saites un teksta izcelšana labākai redzamībai</li>
								<li>Pilna tastatūras navigācija rīkjoslā</li>
								<li>Ātrās palaišanas tastatūras saīsne: <strong>Alt + .</strong> (Windows) vai <strong>⌘ + .</strong> (Mac)</li>
							</ul>
							<strong>Jāņem vērā:</strong>
							<ul>
								<li>Rīku pieejamība un efektivitāte ir atkarīga no vietnes konfigurācijas un uzturēšanas.</li>
								<li>Mēs nevaram garantēt, ka visas vietnes <strong>[Company Website]</strong> daļas būs pilnībā pieejamas. Daļa saturu var būt nodrošināts trešo pušu vai tehnisku ierobežojumu dēļ.</li>
							</ul>

							<h3>Atsauksmes un kontakts</h3>
							Mēs ļoti vērtējam jūsu atsauksmes. Ja sastopaties ar pieejamības problēmām vai rodas ieteikumi, lūdzu, sazinieties ar mums:

							E-pasts: <strong>[Company E-Mail]</strong>

							Mēs atbildēsim 3–5 darba dienu laikā. Ja nepieciešama palīdzība piekļuvei kādai vietnes daļai, mēs nodrošināsim atbalstu arī caur alternatīviem kanāliem.

							<strong>Pēdējoreiz atjaunināts:</strong> [March 9, 2025]
							</div>

							<!-- Estonian -->
							<div data-content-lang="ee" class="status-message-accessibility" style="display: none;">
							<h2>[Company Name] juurdepääsetavuse lubadus</h2>
							<strong>[Company Name]</strong> pühendub pakkuma oma digitaalsetes kanalites võimalikult ligipääsetavat ja kaasavat kogemust kõigile kasutajatele, sealhulgas puuetega inimestele. Meie eesmärk on parandada <strong>[Company Website]</strong> kasutusmugavust ja tagada võrdsed võimalused kõigile, sõltumata nende võimetest või kasutatavatest tehnoloogiatest.

							<h3>Meie lähenemine juurdepääsetavusele</h3>
							Püüame järgida Web Content Accessibility Guidelines (WCAG), mis sätestavad rahvusvaheliselt tunnustatud standardid digitaalse juurdepääsetavuse tagamiseks. Kuigi täielik vastavus ei pruugi alati olla võimalik, teeme pidevalt tööd parenduste nimel ning vahetame regulaarselt üle juurdepääsetavuse aspektid.

							Juurdepääsetavus on pidev protsess – oleme pühendunud kasutajakogemuse arendamisele vastavalt tehnoloogia, standardite ja kasutajate vajadustele.

							<h3>Juurdepääsu funktsioonid</h3>
							<strong>[Company Website]</strong> võib kasutada tööriistu nagu OneTap juurdepääsetavuse riba, mis pakub:
							<ul>
								<li>Reguleeritav teksti suurus ja kontrast</li>
								<li>Lingide ja teksti esiletõstmine parema nähtavuse nimel</li>
								<li>Täielik navigeerimine klaviatuuri abil</li>
								<li>Kiirklahv alustamiseks: <strong>Alt + .</strong> (Windows) või <strong>⌘ + .</strong> (Mac)</li>
							</ul>
							<strong>Pange tähele:</strong>
							<ul>
								<li>Funktsioonide kättesaadavus sõltub veebisaidi konfiguratsioonist ja hooldusest.</li>
								<li>Me ei saa garanteerida, et kõik kohad <strong>[Company Website]</strong> on alati täielikult ligipääsetavad. Mõni sisu võib pärineda kolmandatelt osapooltelt või olla tehniliste piirangute tõttu.</li>
							</ul>

							<h3>Tagasiside ja kontakt</h3>
							Oleme tänulikud teie tagasiside eest. Kui puutute kokku juurdepääsetavuse takistustega või teil on ettepanekuid, võtke meiega ühendust:

							E-post: <strong>[Company E-Mail]</strong>

							Vastame tavaliselt 3–5 tööpäeva jooksul. Vajadusel pakume tuge ka alternatiivsete kanalite kaudu.

							<strong>Viimati uuendatud:</strong> [March 9, 2025]
						</div>

						<!-- Croatian -->
						<div data-content-lang="hr" class="status-message-accessibility" style="display: none;">
							<h2>[Company Name] izjava o pristupačnosti</h2>
							U <strong>[Company Name]</strong> posvećeni smo pružanju digitalnog sadržaja koji je što pristupačniji i inkluzivniji za sve korisnike, uključujući osobe s invaliditetom. Cilj nam je poboljšati upotrebljivost <strong>[Company Website]</strong> i omogućiti ravnopravan pristup svima, bez obzira na sposobnosti ili tehnologiju.

							<h3>Naš pristup pristupačnosti</h3>
							Nastojimo se pridržavati smjernica Web Content Accessibility Guidelines (WCAG), koje definiraju međunarodno priznate standarde digitalne pristupačnosti. Iako se potpuna usklađenost ne može uvijek zajamčiti, kontinuirano radimo na poboljšanjima i redovito pregledavamo aspekte pristupačnosti.

							Pristupačnost je stalan proces i predani smo unapređenju korisničkog iskustva zajedno s razvojem tehnologije, standarda i potreba korisnika.

							<h3>Funkcije pristupačnosti</h3>
							<strong>[Company Website]</strong> može koristiti alate poput OneTap trake za pristupačnost, koja uključuje:
							<ul>
								<li>Prilagodbu veličine teksta i kontrasta</li>
								<li>Isticanje poveznica i teksta radi bolje vidljivosti</li>
								<li>Punu navigaciju tipkovnicom</li>
								<li>Tipkovni prečac za aktivaciju: <strong>Alt + .</strong> (Windows) ili <strong>⌘ + .</strong> (Mac)</li>
							</ul>
							<strong>Napomena:</strong>
							<ul>
								<li>Dostupnost i funkcionalnost ovih značajki ovise o konfiguraciji i održavanju web‑stranice.</li>
								<li>Ne možemo jamčiti da će sve dijelove <strong>[Company Website]</strong> uvijek biti potpuno pristupačni. Dio sadržaja može biti od trećih strana ili pod tehničkim ograničenjima.</li>
							</ul>

							<h3>Povratne informacije i kontakt</h3>
							Cijenimo vaše povratne informacije. Ako naiđete na prepreke pristupačnosti ili imate prijedloge, molimo da nas kontaktirate:

							E‑mail: <strong>[Company E-Mail]</strong>

							Obično odgovaramo u roku od 3–5 radnih dana. Ako je potrebno, rado ćemo pružiti podršku i putem alternativnih kanala.

							<strong>Posljednje ažuriranje:</strong> [March 9, 2025]
							</div>

							<!-- Irish Gaelic -->
							<div data-content-lang="ie" class="status-message-accessibility" style="display: none;">
							<h2>Gealltanas Inrochtaineachta ó [Company Name]</h2>
							Tá <strong>[Company Name]</strong> tiomanta do thaithí dhigiteach a dhéanamh chomh incháilithe agus chomh cuimsithe agus is chomh bhfuil sé indéanta do gach úsáideoir, lena n-áirítear daoine le míchumas. Is é ár gcuspóir usability <strong>[Company Website]</strong> a fheabhsú agus taithí inrochta a chur ar fáil do chách, is cuma cén cumais nó teicneolaíochtaí atá á n-úsáid acu.

							<h3>Ábaltacht i leith an inrochta</h3>
							Déanaimid iarracht cloí leis na Treoirlínte Inrochta don Ábhar Gréasáin (WCAG), a leagann amach caighdeáin atá aitheanta go hidirnáisiúnta maidir le hinrochtaíocht dhigiteach. Cé nach gá dúinn a bheith comhlíontach go hiomlán i gcónaí, déanaimid feabhsuithe de réir mar is féidir agus déanaimid athbhreithniú rialta ar ghnéithe inrochta ár suíomh.

							Is próiseas leanúnach é inrochtaíocht, agus táimid tiomanta d’eispéireas a fheabhsú i gcónaí de réir mar a théann teicneolaíocht, caighdeáin agus riachtanais úsáideora chun cinn.

							<h3>Gnéithe inrochta</h3>
							Is féidir le <strong>[Company Website]</strong> uirlisí cosúil le barra inrochta OneTap a úsáid. Tá gnéithe léirithe aici, lena n-áirítear:
							<ul>
								<li>Uasméid lúide teanga agus codarsnachta</li>
								<li>Tógtar naisc agus téacs le fáil níos fearr orthu</li>
								<li>Nas clean scrapta ar an méarchlár go hiomlán</li>
								<li>Gearrthaisce gasta méarchláir: <strong>Alt + .</strong> (Windows) nó <strong>⌘ + .</strong> (Mac)</li>
							</ul>
							<strong>Tabhair faoi deara:</strong>
							<ul>
								<li>Tá rochtain ar na gnéithe agus a n-éifeachtúlacht ag brath ar chumraíocht agus cothabháil an tsuímh.</li>
								<li>Ní féidir linn a ráthú go mbeidh gach cuid de <strong>[Company Website]</strong> ar fáil i gcónaí go hiomlán. D’fhéadfadh codanna ábhair a bheith ó tríú páirtithe nó faoi theorainneacha teicniúla.</li>
							</ul>

							<h3>Aiseolas agus teagmháil</h3>
							Is mór againn do chuid aiseolais. Má bhíonn tú ag tabhairt aghaidh ar bhacainní inrochta nó má tá moltaí agat, déan teagmháil linn:

							Ríomhphost: <strong>[Company E-Mail]</strong>

							Freagraimid laistigh de 3–5 lá oibre de ghnáth. Má tá cúnamh ag teastáil uait rochtain a fháil ar aon chuid den suíomh, soláthróimid tacaíocht freisin trí chainéil malartacha.

							<strong>Nuashonraithe deireanach:</strong> [March 9, 2025]
						</div>

						<!-- Bulgarian -->
						<div data-content-lang="bg" class="status-message-accessibility" style="display: none;">
							<h2>Ангажимент за достъпност на [Company Name]</h2>
							<strong>[Company Name]</strong> се ангажира да осигури цифрово съдържание, което е възможно най-достъпно и приобщаващо за всички потребители, включително хора с увреждания. Нашата цел е да подобрим използваемостта на <strong>[Company Website]</strong> и да предоставим равен достъп, независимо от способностите или технологиите на потребителите.

							<h3>Нашият подход към достъпността</h3>
							Ние се стремим да следваме Насоките за достъпност на уеб съдържанието (WCAG), които определят международно признати стандарти. Въпреки че пълното съответствие невинаги е възможно, ние непрекъснато подобряваме достъпността и преглеждаме съответните части на сайта редовно.

							Достъпността е постоянен процес – ние се стремим към по-добро потребителско изживяване, докато технологиите и нуждите на потребителите се развиват.

							<h3>Функции за достъпност</h3>
							<strong>[Company Website]</strong> може да използва инструменти като лентата за достъпност OneTap, която предлага:
							<ul>
								<li>Регулиране на размера на текста и контраста</li>
								<li>Подчертаване на връзки и текст за по-добра видимост</li>
								<li>Пълна клавиатурна навигация</li>
								<li>Бърз клавиш за стартиране: <strong>Alt + .</strong> (Windows) или <strong>⌘ + .</strong> (Mac)</li>
							</ul>
							<strong>Забележка:</strong>
							<ul>
								<li>Функционалността зависи от конфигурацията и поддръжката на сайта.</li>
								<li>Не можем да гарантираме пълна достъпност на всички части на <strong>[Company Website]</strong>. Част от съдържанието може да бъде предоставено от трети страни или ограничено технически.</li>
							</ul>

							<h3>Обратна връзка и контакт</h3>
							Благодарим Ви за обратната връзка. Ако срещнете проблеми с достъпността или имате предложения, моля свържете се с нас:

							Имейл: <strong>[Company E-Mail]</strong>

							Ще отговорим в рамките на 3–5 работни дни. При нужда ще предоставим помощ и чрез алтернативни канали.

							<strong>Последна актуализация:</strong> [March 9, 2025]
						</div>

						<!-- Norwegian -->
						<div data-content-lang="no" class="status-message-accessibility" style="display: none;">
							<h2>[Company Name] sitt tilgjengelighetsløfte</h2>
							<strong>[Company Name]</strong> forplikter seg til å gjøre sitt digitale innhold så tilgjengelig og inkluderende som mulig for alle brukere, inkludert personer med funksjonsnedsettelser. Målet vårt er å forbedre brukervennligheten på <strong>[Company Website]</strong> og sikre like muligheter for alle, uavhengig av evner eller teknologi.

							<h3>Vår tilnærming til tilgjengelighet</h3>
							Vi følger retningslinjene i Web Content Accessibility Guidelines (WCAG), som definerer internasjonalt anerkjente standarder. Selv om full overensstemmelse ikke alltid er mulig, jobber vi kontinuerlig med forbedringer og gjennomgår relevante deler av nettstedet regelmessig.

							Tilgjengelighet er en kontinuerlig prosess – vi er dedikert til å forbedre brukeropplevelsen i tråd med utviklingen av teknologi og behov.

							<h3>Tilgjengelighetsfunksjoner</h3>
							<strong>[Company Website]</strong> kan bruke verktøy som OneTap tilgjengelighetslinje, som tilbyr:
							<ul>
								<li>Justerbar tekststørrelse og kontrast</li>
								<li>Utheving av lenker og tekst for bedre synlighet</li>
								<li>Full tastaturnavigasjon</li>
								<li>Hurtigtast for å starte: <strong>Alt + .</strong> (Windows) eller <strong>⌘ + .</strong> (Mac)</li>
							</ul>
							<strong>Merk:</strong>
							<ul>
								<li>Tilgjengelighet og funksjonalitet avhenger av nettstedets konfigurasjon og vedlikehold.</li>
								<li>Vi kan ikke garantere full tilgjengelighet i alle deler av <strong>[Company Website]</strong>. Noe innhold kan være levert av tredjeparter eller være teknisk begrenset.</li>
							</ul>

							<h3>Tilbakemelding og kontakt</h3>
							Vi setter stor pris på tilbakemeldinger. Hvis du opplever problemer eller har forslag, ta gjerne kontakt:

							E-post: <strong>[Company E-Mail]</strong>

							Vi svarer vanligvis innen 3–5 virkedager. Hjelp er også tilgjengelig via alternative kanaler om nødvendig.

							<strong>Sist oppdatert:</strong> [March 9, 2025]
						</div>

						<!-- Turkish -->
						<div data-content-lang="tr" class="status-message-accessibility" style="display: none;">
							<h2>[Company Name] Erişilebilirlik Taahhüdü</h2>
							<strong>[Company Name]</strong>, dijital içeriğini tüm kullanıcılar – engelli bireyler dahil – için olabildiğince erişilebilir ve kapsayıcı hale getirmeye kararlıdır. Amacımız, <strong>[Company Website]</strong> kullanımını geliştirmek ve tüm kullanıcılara eşit erişim sağlamaktır.

							<h3>Erişilebilirliğe Yaklaşımımız</h3>
							Web Content Accessibility Guidelines (WCAG) standartlarını takip etmeye çalışıyoruz. Bu yönergeler uluslararası olarak kabul edilmiş erişilebilirlik standartlarını tanımlar. Tam uyumluluk her zaman mümkün olmasa da, erişilebilirliği sürekli iyileştiriyoruz ve ilgili alanları düzenli olarak gözden geçiriyoruz.

							Erişilebilirlik süreklilik gerektiren bir süreçtir – teknoloji ve kullanıcı ihtiyaçları geliştikçe biz de deneyimi geliştirmeye devam ediyoruz.

							<h3>Erişilebilirlik Özellikleri</h3>
							<strong>[Company Website]</strong>, aşağıdaki özellikleri sunan OneTap erişilebilirlik araç çubuğunu kullanabilir:
							<ul>
								<li>Yazı tipi boyutu ve kontrast ayarı</li>
								<li>Bağlantı ve metin vurgulama</li>
								<li>Tam klavye navigasyonu</li>
								<li>Kısayol tuşu: <strong>Alt + .</strong> (Windows) veya <strong>⌘ + .</strong> (Mac)</li>
							</ul>
							<strong>Dikkat edilmesi gerekenler:</strong>
							<ul>
								<li>Özelliklerin etkinliği sitenin yapılandırmasına ve bakımına bağlıdır.</li>
								<li><strong>[Company Website]</strong>'in tüm bölümlerinin tamamen erişilebilir olması garanti edilemez. Bazı içerikler üçüncü taraflardan kaynaklanıyor olabilir.</li>
							</ul>

							<h3>Geri Bildirim ve İletişim</h3>
							Geri bildiriminiz bizim için çok değerlidir. Herhangi bir erişilebilirlik sorunu yaşarsanız ya da önerileriniz varsa, bizimle iletişime geçebilirsiniz:

							E-posta: <strong>[Company E-Mail]</strong>

							Genellikle 3–5 iş günü içinde yanıt veriyoruz. Gerekirse alternatif kanallar üzerinden destek sağlıyoruz.

							<strong>Son güncelleme:</strong> [March 9, 2025]
						</div>

						<!-- Indonesian -->
						<div data-content-lang="id" class="status-message-accessibility" style="display: none;">
							<h2>Komitmen Aksesibilitas dari [Company Name]</h2>
							<strong>[Company Name]</strong> berkomitmen untuk memastikan konten digital kami dapat diakses dan inklusif bagi semua pengguna, termasuk penyandang disabilitas. Tujuan kami adalah meningkatkan kegunaan <strong>[Company Website]</strong> dan memastikan semua orang memiliki akses yang setara, terlepas dari kemampuan atau teknologi yang digunakan.

							<h3>Pendekatan Kami terhadap Aksesibilitas</h3>
							Kami berusaha mematuhi pedoman Web Content Accessibility Guidelines (WCAG), yang merupakan standar internasional untuk aksesibilitas digital. Meskipun kesesuaian penuh tidak selalu dapat dicapai, kami terus melakukan perbaikan dan meninjau area yang relevan secara rutin.

							Aksesibilitas adalah proses berkelanjutan — kami berkomitmen untuk terus menyempurnakan pengalaman pengguna seiring perkembangan teknologi dan kebutuhan pengguna.

							<h3>Fitur Aksesibilitas</h3>
							<strong>[Company Website]</strong> dapat menggunakan alat seperti bilah aksesibilitas OneTap, yang menyediakan:
							<ul>
								<li>Penyesuaian ukuran teks dan kontras</li>
								<li>Sorotan tautan dan teks untuk visibilitas yang lebih baik</li>
								<li>Navigasi penuh menggunakan keyboard</li>
								<li>Shortcut keyboard: <strong>Alt + .</strong> (Windows) atau <strong>⌘ + .</strong> (Mac)</li>
							</ul>
							<strong>Catatan:</strong>
							<ul>
								<li>Ketersediaan dan fungsi fitur tergantung pada konfigurasi dan pemeliharaan situs.</li>
								<li>Kami tidak dapat menjamin bahwa semua bagian dari <strong>[Company Website]</strong> akan sepenuhnya dapat diakses. Beberapa konten mungkin berasal dari pihak ketiga atau memiliki batasan teknis.</li>
							</ul>

							<h3>Umpan Balik dan Kontak</h3>
							Kami menghargai umpan balik Anda. Jika Anda mengalami hambatan aksesibilitas atau memiliki saran, silakan hubungi kami:

							Email: <strong>[Company E-Mail]</strong>

							Kami akan merespons dalam waktu 3–5 hari kerja. Jika diperlukan, bantuan juga tersedia melalui saluran alternatif.

							<strong>Pembaruan terakhir:</strong> [March 9, 2025]
						</div>

						<!-- Portuguese (Brazil) -->
						<div data-content-lang="pt-br" class="status-message-accessibility" style="display: none;">
							<h2>Compromisso de Acessibilidade da [Company Name]</h2>
							A <strong>[Company Name]</strong> está comprometida em tornar seu conteúdo digital o mais acessível e inclusivo possível para todos os usuários, incluindo pessoas com deficiência. Nosso objetivo é melhorar a usabilidade do <strong>[Company Website]</strong> e garantir acesso igualitário, independentemente de habilidades ou tecnologia utilizada.

							<h3>Nossa abordagem à acessibilidade</h3>
							Buscamos seguir as diretrizes da Web Content Accessibility Guidelines (WCAG), que estabelecem padrões reconhecidos internacionalmente. Embora a conformidade total possa não ser possível em todos os momentos, estamos continuamente trabalhando para melhorar a acessibilidade e revisamos as seções relevantes do site regularmente.

							A acessibilidade é um processo contínuo — estamos comprometidos com a melhoria constante à medida que a tecnologia e as necessidades dos usuários evoluem.

							<h3>Recursos de acessibilidade</h3>
							O <strong>[Company Website]</strong> pode utilizar ferramentas como a barra de acessibilidade OneTap, que oferece:
							<ul>
								<li>Ajuste de tamanho do texto e contraste</li>
								<li>Destaque de links e textos para melhor visibilidade</li>
								<li>Navegação completa pelo teclado</li>
								<li>Atalho de teclado: <strong>Alt + .</strong> (Windows) ou <strong>⌘ + .</strong> (Mac)</li>
							</ul>
							<strong>Observações:</strong>
							<ul>
								<li>Os recursos dependem da configuração e manutenção do site.</li>
								<li>Não podemos garantir acessibilidade total em todas as áreas do <strong>[Company Website]</strong>. Parte do conteúdo pode ser fornecido por terceiros ou ter limitações técnicas.</li>
							</ul>

							<h3>Feedback e contato</h3>
							Agradecemos seu feedback. Se você tiver dificuldades de acesso ou sugestões, entre em contato conosco:

							E-mail: <strong>[Company E-Mail]</strong>

							Responderemos em até 3–5 dias úteis. Também podemos fornecer assistência por canais alternativos, se necessário.

							<strong>Última atualização:</strong> [March 9, 2025]
						</div>

						<!-- Japanese -->
						<div data-content-lang="ja" class="status-message-accessibility" style="display: none;">
							<h2>[Company Name] のアクセシビリティに関する取り組み</h2>
							<strong>[Company Name]</strong> は、すべてのユーザー、特に障がいのある方を含め、デジタルコンテンツを可能な限りアクセスしやすく、インクルーシブにすることを目指しています。私たちの目的は、<strong>[Company Website]</strong> の使いやすさを向上させ、すべての人が平等にアクセスできるようにすることです。

							<h3>アクセシビリティへの取り組み</h3>
							国際的に認められている Web Content Accessibility Guidelines (WCAG) に準拠するよう努めています。完全な準拠が常に可能であるとは限りませんが、継続的な改善に取り組み、定期的に該当部分を見直しています。

							アクセシビリティは継続的なプロセスであり、技術やユーザーのニーズに合わせてユーザー体験の改善を続けていきます。

							<h3>アクセシビリティ機能</h3>
							<strong>[Company Website]</strong> では、OneTap アクセシビリティバーなどのツールを使用して以下の機能を提供する場合があります：
							<ul>
								<li>テキストサイズとコントラストの調整</li>
								<li>リンクとテキストのハイライト</li>
								<li>キーボードによる完全な操作</li>
								<li>ショートカットキー：<strong>Alt + .</strong>（Windows）、<strong>⌘ + .</strong>（Mac）</li>
							</ul>
							<strong>注意点：</strong>
							<ul>
								<li>サイトの構成と保守状況によって、利用可能な機能は異なります。</li>
								<li><strong>[Company Website]</strong> のすべての部分において完全なアクセシビリティを保証することはできません。一部のコンテンツは第三者によって提供されているか、技術的に制限されている可能性があります。</li>
							</ul>

							<h3>フィードバックとお問い合わせ</h3>
							ご意見・ご要望をお待ちしています。アクセシビリティに関する問題や改善提案がございましたら、以下の連絡先までご連絡ください：

							メール：<strong>[Company E-Mail]</strong>

							通常、3～5営業日以内に返信いたします。必要に応じて代替手段でもサポート可能です。

							<strong>最終更新日：</strong> [March 9, 2025]
						</div>

						<!-- Korean -->
						<div data-content-lang="ko" class="status-message-accessibility" style="display: none;">
							<h2>[Company Name]의 접근성 약속</h2>
							<strong>[Company Name]</strong>는 모든 사용자, 특히 장애를 가진 사람들을 포함하여 디지털 콘텐츠가 가능한 한 접근 가능하고 포괄적이도록 만들기 위해 최선을 다하고 있습니다. 우리의 목표는 <strong>[Company Website]</strong>의 사용성을 개선하고 기술이나 능력에 관계없이 모든 사용자에게 동등한 접근을 보장하는 것입니다.

							<h3>접근성에 대한 우리의 접근 방식</h3>
							우리는 국제적으로 인정받는 Web Content Accessibility Guidelines (WCAG)에 따라 접근성을 향상시키기 위해 노력하고 있습니다. 항상 완벽하게 준수할 수 있는 것은 아니지만, 지속적으로 개선하고 있으며 관련된 부분을 정기적으로 검토하고 있습니다.

							접근성은 지속적인 과정이며, 기술과 사용자 요구가 변화함에 따라 사용자 경험을 향상시키기 위해 지속적으로 노력하고 있습니다.

							<h3>접근성 기능</h3>
							<strong>[Company Website]</strong>는 OneTap 접근성 도구와 같은 기능을 제공할 수 있습니다:
							<ul>
								<li>텍스트 크기 및 대비 조절</li>
								<li>링크 및 텍스트 강조 표시</li>
								<li>전체 키보드 탐색 지원</li>
								<li>단축키: <strong>Alt + .</strong> (Windows), <strong>⌘ + .</strong> (Mac)</li>
							</ul>
							<strong>주의 사항:</strong>
							<ul>
								<li>기능의 가용성은 웹사이트의 설정 및 유지 관리 상태에 따라 달라질 수 있습니다.</li>
								<li><strong>[Company Website]</strong>의 모든 콘텐츠가 완전히 접근 가능하다고는 보장할 수 없습니다. 일부 콘텐츠는 제3자에 의해 제공되거나 기술적 제한이 있을 수 있습니다.</li>
							</ul>

							<h3>피드백 및 문의</h3>
							귀하의 피드백은 소중합니다. 접근성 문제나 제안이 있으시면 다음 이메일로 연락해 주세요:

							이메일: <strong>[Company E-Mail]</strong>

							일반적으로 영업일 기준 3~5일 내에 응답해 드립니다. 필요한 경우 다른 채널을 통해 지원도 가능합니다.

							<strong>최종 업데이트 날짜:</strong> [March 9, 2025]
						</div>

						<!-- Chinese Simplified -->
						<div data-content-lang="zh" class="status-message-accessibility" style="display: none;">
							<h2>[Company Name] 无障碍承诺</h2>
							<strong>[Company Name]</strong> 承诺使我们的数字内容尽可能对所有用户，包括残障人士，保持可访问性和包容性。我们的目标是提升 <strong>[Company Website]</strong> 的可用性，并确保所有人都能平等访问，不论其能力或使用的技术。

							<h3>我们对无障碍的态度</h3>
							我们努力遵循《Web 内容可访问性指南（WCAG）》这一国际标准。虽然无法保证完全符合所有标准，但我们不断改进，并定期审查相关部分。

							无障碍是一个持续改进的过程——随着技术和用户需求的发展，我们也在不断优化用户体验。

							<h3>无障碍功能</h3>
							<strong>[Company Website]</strong> 可能使用 OneTap 无障碍工具栏，提供以下功能：
							<ul>
								<li>调整文本大小和对比度</li>
								<li>高亮链接和文本以提高可见性</li>
								<li>支持键盘完全导航</li>
								<li>快捷键启动：<strong>Alt + .</strong>（Windows）或 <strong>⌘ + .</strong>（Mac）</li>
							</ul>
							<strong>注意事项：</strong>
							<ul>
								<li>功能的可用性取决于网站的配置和维护。</li>
								<li>我们无法保证 <strong>[Company Website]</strong> 的所有内容都完全可访问，部分内容可能由第三方提供或存在技术限制。</li>
							</ul>

							<h3>反馈与联系</h3>
							如果您在访问过程中遇到任何问题或有改进建议，请联系我们：

							邮箱：<strong>[Company E-Mail]</strong>

							我们将在 3–5 个工作日内答复。如有需要，也可通过其他渠道协助您。

							<strong>最后更新：</strong> [March 9, 2025]
						</div>

						<!-- Arabic -->
						<div data-content-lang="ar" class="status-message-accessibility" dir="rtl" style="display: none;">
							<h2>التزام [Company Name] بإمكانية الوصول</h2>
							تلتزم <strong>[Company Name]</strong> بجعل المحتوى الرقمي في متناول الجميع، بما في ذلك الأشخاص ذوي الإعاقة. هدفنا هو تحسين سهولة استخدام <strong>[Company Website]</strong> وضمان الوصول العادل بغض النظر عن القدرات أو التقنية المستخدمة.

							<h3>نهجنا تجاه إمكانية الوصول</h3>
							نسعى للامتثال لإرشادات Web Content Accessibility Guidelines (WCAG) المعترف بها دوليًا. وعلى الرغم من أننا قد لا نتمكن دائمًا من الامتثال الكامل، إلا أننا نعمل باستمرار على تحسين إمكانية الوصول ونراجع الأجزاء ذات الصلة بانتظام.

							إمكانية الوصول هي عملية مستمرة — ونحن ملتزمون بالتحسين المستمر مع تطور التكنولوجيا واحتياجات المستخدمين.

							<h3>ميزات إمكانية الوصول</h3>
							قد يستخدم <strong>[Company Website]</strong> أدوات مثل شريط إمكانية الوصول OneTap، والذي يوفر:
							<ul>
								<li>ضبط حجم النص والتباين</li>
								<li>تمييز الروابط والنصوص</li>
								<li>التنقل الكامل باستخدام لوحة المفاتيح</li>
								<li>اختصار لوحة المفاتيح: <strong>Alt + .</strong> (Windows) أو <strong>⌘ + .</strong> (Mac)</li>
							</ul>
							<strong>ملاحظات:</strong>
							<ul>
								<li>تعتمد الميزات على إعداد الموقع وصيانته.</li>
								<li>لا يمكننا ضمان إمكانية الوصول الكاملة لجميع أجزاء <strong>[Company Website]</strong>، حيث قد يكون بعض المحتوى من أطراف خارجية أو يحتوي على قيود فنية.</li>
							</ul>

							<h3>الملاحظات والتواصل</h3>
							نرحب بملاحظاتكم. إذا واجهتم مشكلات في الوصول أو لديكم اقتراحات، يرجى الاتصال بنا:

							البريد الإلكتروني: <strong>[Company E-Mail]</strong>

							سنرد خلال 3 إلى 5 أيام عمل. يمكننا أيضًا تقديم المساعدة عبر قنوات بديلة إذا لزم الأمر.

							<strong>آخر تحديث:</strong> [March 9, 2025]
						</div>

						<!-- Russian -->
						<div data-content-lang="ru" class="status-message-accessibility" style="display: none;">
							<h2>Обязательство [Company Name] по обеспечению доступности</h2>
							<strong>[Company Name]</strong> стремится сделать свой цифровой контент максимально доступным и инклюзивным для всех пользователей, включая людей с инвалидностью. Наша цель — улучшить удобство использования <strong>[Company Website]</strong> и обеспечить равный доступ независимо от способностей или используемых технологий.

							<h3>Наш подход к доступности</h3>
							Мы стремимся соблюдать международные стандарты, такие как Web Content Accessibility Guidelines (WCAG). Несмотря на то, что полное соответствие может быть невозможно на всех участках, мы постоянно работаем над улучшением и регулярно пересматриваем соответствующие разделы сайта.

							Доступность — это непрерывный процесс, и мы продолжаем улучшать пользовательский опыт по мере развития технологий и потребностей пользователей.

							<h3>Функции доступности</h3>
							<strong>[Company Website]</strong> может использовать инструменты, такие как панель OneTap, предлагающие:
							<ul>
								<li>Настройка размера текста и контрастности</li>
								<li>Подсветка ссылок и текста</li>
								<li>Полноценная навигация с клавиатуры</li>
								<li>Горячие клавиши: <strong>Alt + .</strong> (Windows) или <strong>⌘ + .</strong> (Mac)</li>
							</ul>
							<strong>Примечания:</strong>
							<ul>
								<li>Функции зависят от настроек и поддержки сайта.</li>
								<li>Мы не можем гарантировать полную доступность всех разделов <strong>[Company Website]</strong>. Некоторые материалы предоставлены третьими сторонами или имеют технические ограничения.</li>
							</ul>

							<h3>Обратная связь и контакты</h3>
							Мы ценим ваш отклик. Если у вас возникли трудности или предложения, свяжитесь с нами:

							Электронная почта: <strong>[Company E-Mail]</strong>

							Мы ответим в течение 3–5 рабочих дней. При необходимости возможна помощь через альтернативные каналы.

							<strong>Последнее обновление:</strong> [March 9, 2025]
						</div>

						<!-- Hindi -->
						<div data-content-lang="hi" class="status-message-accessibility" style="display: none;">
							<h2>[Company Name] की पहुंच-सुलभता की प्रतिबद्धता</h2>
							<strong>[Company Name]</strong> सभी उपयोगकर्ताओं, विशेष रूप से दिव्यांगजन के लिए, अपने डिजिटल सामग्री को यथासंभव सुलभ और समावेशी बनाने के लिए प्रतिबद्ध है। हमारा लक्ष्य <strong>[Company Website]</strong> की उपयोगिता को बेहतर बनाना और सभी के लिए समान पहुंच सुनिश्चित करना है।

							<h3>हमारा दृष्टिकोण</h3>
							हम अंतरराष्ट्रीय मानकों जैसे कि Web Content Accessibility Guidelines (WCAG) का पालन करने का प्रयास करते हैं। यद्यपि पूर्ण अनुपालन हमेशा संभव नहीं होता, हम लगातार सुधार कर रहे हैं और संबंधित अनुभागों की नियमित समीक्षा करते हैं।

							पहुंच-सुलभता एक सतत प्रक्रिया है — हम तकनीकी और उपयोगकर्ता की जरूरतों के अनुसार सुधार करते रहेंगे।

							<h3>सुलभता की विशेषताएँ</h3>
							<strong>[Company Website]</strong> OneTap एक्सेसिबिलिटी बार जैसे उपकरणों का उपयोग कर सकता है, जो कि प्रदान करता है:
							<ul>
								<li>पाठ का आकार और कंट्रास्ट समायोजन</li>
								<li>लिंक और टेक्स्ट को हाइलाइट करना</li>
								<li>कीबोर्ड के माध्यम से पूरी नेविगेशन</li>
								<li>शॉर्टकट: <strong>Alt + .</strong> (Windows) या <strong>⌘ + .</strong> (Mac)</li>
							</ul>
							<strong>नोट्स:</strong>
							<ul>
								<li>सुविधाएँ साइट की सेटिंग और रख-रखाव पर निर्भर करती हैं।</li>
								<li>हम <strong>[Company Website]</strong> के हर भाग की पूर्ण सुलभता की गारंटी नहीं दे सकते। कुछ सामग्री तृतीय-पक्ष द्वारा हो सकती है या तकनीकी सीमाएं हो सकती हैं।</li>
							</ul>

							<h3>प्रतिक्रिया और संपर्क</h3>
							यदि आपको पहुँच में कोई समस्या है या आपके सुझाव हैं, तो कृपया हमसे संपर्क करें:

							ईमेल: <strong>[Company E-Mail]</strong>

							हम आमतौर पर 3–5 कार्यदिवसों के भीतर उत्तर देते हैं। आवश्यकतानुसार वैकल्पिक माध्यमों से सहायता भी प्रदान की जा सकती है।

							<strong>अंतिम अद्यतन:</strong> [March 9, 2025]
						</div>

						<!-- Ukrainian -->
						<div data-content-lang="uk" class="status-message-accessibility" style="display: none;">
							<h2>Зобов’язання [Company Name] щодо доступності</h2>
							<strong>[Company Name]</strong> прагне зробити свій цифровий контент максимально доступним для всіх користувачів, зокрема для людей з інвалідністю. Наша мета — покращити зручність <strong>[Company Website]</strong> і забезпечити рівний доступ незалежно від здібностей чи технологій.

							<h3>Наш підхід до доступності</h3>
							Ми орієнтуємося на міжнародні стандарти, зокрема Web Content Accessibility Guidelines (WCAG). Хоча повна відповідність не завжди можлива, ми постійно вдосконалюємо сайт і регулярно переглядаємо відповідні розділи.

							Доступність — це безперервний процес, і ми зобов’язані постійно покращувати користувацький досвід.

							<h3>Функції доступності</h3>
							<strong>[Company Website]</strong> може використовувати інструменти на кшталт панелі OneTap, що забезпечують:
							<ul>
								<li>Налаштування розміру тексту та контрасту</li>
								<li>Підсвічування посилань і тексту</li>
								<li>Повну навігацію за допомогою клавіатури</li>
								<li>Гарячі клавіші: <strong>Alt + .</strong> (Windows) або <strong>⌘ + .</strong> (Mac)</li>
							</ul>
							<strong>Примітки:</strong>
							<ul>
								<li>Доступні функції залежать від конфігурації та обслуговування сайту.</li>
								<li>Ми не гарантуємо повну доступність усіх розділів <strong>[Company Website]</strong>. Частина контенту може бути надана третіми сторонами або мати технічні обмеження.</li>
							</ul>

							<h3>Зв'язок і відгуки</h3>
							Якщо у вас є зауваження або пропозиції щодо доступності, зв’яжіться з нами:

							Електронна пошта: <strong>[Company E-Mail]</strong>

							Ми відповімо протягом 3–5 робочих днів. За потреби — надамо підтримку альтернативними каналами.

							<strong>Останнє оновлення:</strong> [March 9, 2025]
						</div>

						<!-- Serbian -->
						<div data-content-lang="sr" class="status-message-accessibility" style="display: none;">
							<h2>Obaveza [Company Name] za pristupačnost</h2>
							<strong>[Company Name]</strong> se zalaže za to da digitalni sadržaj bude što pristupačniji i inkluzivniji za sve korisnike, uključujući osobe sa invaliditetom. Naš cilj je poboljšati upotrebljivost <strong>[Company Website]</strong> i omogućiti ravnopravan pristup bez obzira na sposobnosti ili korišćenu tehnologiju.

							<h3>Naš pristup pristupačnosti</h3>
							Nastojimo da sledimo smernice Web Content Accessibility Guidelines (WCAG), koje su međunarodno priznati standardi. Iako potpuna usklađenost možda nije uvek moguća, stalno radimo na unapređenju i redovno pregledamo relevantne delove sajta.

							Pristupačnost je kontinuiran proces — posvećeni smo stalnom poboljšanju kako tehnologija i potrebe korisnika napreduju.

							<h3>Funkcije pristupačnosti</h3>
							<strong>[Company Website]</strong> može koristiti alate poput OneTap trake pristupačnosti koja nudi:
							<ul>
								<li>Podešavanje veličine teksta i kontrasta</li>
								<li>Isticanje linkova i teksta</li>
								<li>Potpuna navigacija tastaturom</li>
								<li>Prečice na tastaturi: <strong>Alt + .</strong> (Windows) ili <strong>⌘ + .</strong> (Mac)</li>
							</ul>
							<strong>Napomene:</strong>
							<ul>
								<li>Funkcionalnosti zavise od podešavanja i održavanja sajta.</li>
								<li>Ne možemo garantovati punu pristupačnost svih delova <strong>[Company Website]</strong>. Deo sadržaja može dolaziti od trećih strana ili imati tehnička ograničenja.</li>
							</ul>

							<h3>Povratne informacije i kontakt</h3>
							Vaše povratne informacije su nam važne. Ako imate problema sa pristupom ili predloge za poboljšanje, kontaktirajte nas:

							E-mail: <strong>[Company E-Mail]</strong>

							Odgovaramo u roku od 3–5 radnih dana. Po potrebi, dostupna je pomoć i putem alternativnih kanala.

						<strong>Poslednje ažuriranje:</strong> [March 9, 2025]
					</div>

					<!-- English UK -->
					<div data-content-lang="gb" class="status-message-accessibility" style="display: none;">
						<h2>[Company Name] Accessibility Commitment</h2>
						<strong>[Company Name]</strong> is committed to making our digital content as accessible and inclusive as possible for all users, including people with disabilities. Our goal is to improve the usability of <strong>[Company Website]</strong> and ensure equal access for everyone, regardless of abilities or technology used.

						<h3>Our Approach to Accessibility</h3>
						We strive to follow international standards such as the Web Content Accessibility Guidelines (WCAG). While full compliance may not always be possible, we continuously work to improve accessibility and regularly review relevant sections.

						Accessibility is an ongoing process — we are committed to constant improvement as technology and user needs evolve.

						<h3>Accessibility Features</h3>
						<strong>[Company Website]</strong> may use tools such as the OneTap accessibility toolbar, which provides:
						<ul>
							<li>Text size and contrast adjustments</li>
							<li>Link and text highlighting</li>
							<li>Full keyboard navigation</li>
							<li>Keyboard shortcut: <strong>Alt + .</strong> (Windows) or <strong>⌘ + .</strong> (Mac)</li>
						</ul>
						<strong>Notes:</strong>
						<ul>
							<li>Features depend on the site's configuration and maintenance.</li>
							<li>We cannot guarantee full accessibility of all parts of <strong>[Company Website]</strong>. Some content may be from third parties or have technical limitations.</li>
						</ul>

						<h3>Feedback and Contact</h3>
						We value your feedback. If you experience accessibility barriers or have suggestions, please contact us:

						Email: <strong>[Company E-Mail]</strong>

						We typically respond within 3–5 business days. Assistance is also available through alternative channels if needed.

						<strong>Last updated:</strong> [March 9, 2025]
					</div>

					<!-- Persian -->
					<div data-content-lang="ir" class="status-message-accessibility" dir="rtl" style="display: none;">
						<h2>تعهد [Company Name] به دسترسی‌پذیری</h2>
						<strong>[Company Name]</strong> متعهد است که محتوای دیجیتال خود را تا حد امکان برای همه کاربران، از جمله افراد دارای معلولیت، قابل دسترس و فراگیر کند. هدف ما بهبود قابلیت استفاده از <strong>[Company Website]</strong> و اطمینان از دسترسی برابر برای همه، صرف نظر از توانایی‌ها یا فناوری مورد استفاده است.

						<h3>رویکرد ما به دسترسی‌پذیری</h3>
						ما تلاش می‌کنیم از استانداردهای بین‌المللی مانند دستورالعمل‌های دسترسی‌پذیری محتوای وب (WCAG) پیروی کنیم. در حالی که رعایت کامل همیشه ممکن نیست، ما به طور مداوم برای بهبود دسترسی‌پذیری کار می‌کنیم و بخش‌های مرتبط را به طور منظم بررسی می‌کنیم.

						دسترسی‌پذیری یک فرآیند مداوم است — ما متعهد به بهبود مستمر با تکامل فناوری و نیازهای کاربران هستیم.

						<h3>ویژگی‌های دسترسی‌پذیری</h3>
						<strong>[Company Website]</strong> ممکن است از ابزارهایی مانند نوار ابزار دسترسی‌پذیری OneTap استفاده کند که موارد زیر را ارائه می‌دهد:
						<ul>
							<li>تنظیم اندازه متن و کنتراست</li>
							<li>برجسته‌سازی لینک‌ها و متن</li>
							<li>ناوبری کامل با صفحه کلید</li>
							<li>کلید میانبر: <strong>Alt + .</strong> (Windows) یا <strong>⌘ + .</strong> (Mac)</li>
						</ul>
						<strong>یادداشت‌ها:</strong>
						<ul>
							<li>ویژگی‌ها به پیکربندی و نگهداری سایت بستگی دارد.</li>
							<li>ما نمی‌توانیم دسترسی کامل به تمام بخش‌های <strong>[Company Website]</strong> را تضمین کنیم. برخی از محتوا ممکن است از طرف سوم باشد یا محدودیت‌های فنی داشته باشد.</li>
						</ul>

						<h3>بازخورد و تماس</h3>
						ما از بازخورد شما استقبال می‌کنیم. اگر با موانع دسترسی‌پذیری مواجه شدید یا پیشنهادی دارید، لطفاً با ما تماس بگیرید:

						ایمیل: <strong>[Company E-Mail]</strong>

						ما معمولاً در عرض 3 تا 5 روز کاری پاسخ می‌دهیم. در صورت نیاز، کمک نیز از طریق کانال‌های جایگزین در دسترس است.

						<strong>آخرین به‌روزرسانی:</strong> [March 9, 2025]
					</div>

					<!-- Hebrew -->
					<div data-content-lang="il" class="status-message-accessibility" dir="rtl" style="display: none;">
						<h2>התחייבות [Company Name] לנגישות</h2>
						<strong>[Company Name]</strong> מחויבת להפוך את התוכן הדיגיטלי שלנו לנגיש ומכיל ככל האפשר עבור כל המשתמשים, כולל אנשים עם מוגבלויות. המטרה שלנו היא לשפר את השימושיות של <strong>[Company Website]</strong> ולהבטיח גישה שווה לכולם, ללא קשר ליכולות או לטכנולוגיה המשמשת.

						<h3>הגישה שלנו לנגישות</h3>
						אנו שואפים לעמוד בסטנדרטים בינלאומיים כגון הנחיות נגישות תוכן האינטרנט (WCAG). בעוד שעמידה מלאה אינה תמיד אפשרית, אנו עובדים ללא הרף לשיפור הנגישות ובודקים באופן קבוע את החלקים הרלוונטיים.

						נגישות היא תהליך מתמשך — אנו מחויבים לשיפור מתמיד ככל שהטכנולוגיה וצרכי המשתמשים מתפתחים.

						<h3>תכונות נגישות</h3>
						<strong>[Company Website]</strong> עשוי להשתמש בכלים כגון סרגל הכלים לנגישות OneTap, המספק:
						<ul>
							<li>התאמת גודל טקסט וניגודיות</li>
							<li>הדגשת קישורים וטקסט</li>
							<li>ניווט מלא במקלדת</li>
							<li>קיצור מקלדת: <strong>Alt + .</strong> (Windows) או <strong>⌘ + .</strong> (Mac)</li>
						</ul>
						<strong>הערות:</strong>
						<ul>
							<li>התכונות תלויות בהגדרות האתר ובתחזוקתו.</li>
							<li>איננו יכולים להבטיח נגישות מלאה לכל חלקי <strong>[Company Website]</strong>. חלק מהתוכן עשוי להיות מצדדים שלישיים או להיות מוגבל טכנית.</li>
						</ul>

						<h3>משוב ויצירת קשר</h3>
						אנו מעריכים את המשוב שלכם. אם אתם נתקלים במכשולי נגישות או יש לכם הצעות, אנא צרו קשר:

						אימייל: <strong>[Company E-Mail]</strong>

						אנו בדרך כלל מגיבים תוך 3–5 ימי עסקים. סיוע זמין גם דרך ערוצים חלופיים במידת הצורך.

						<strong>עדכון אחרון:</strong> [March 9, 2025]
					</div>

					<!-- Macedonian -->
					<div data-content-lang="mk" class="status-message-accessibility" style="display: none;">
						<h2>Обврска на [Company Name] за пристапност</h2>
						<strong>[Company Name]</strong> се обврзува да направи нашиот дигитален содржина што е можно по пристапен и инклузивен за сите корисници, вклучувајќи ги и лицата со попреченост. Нашата цел е да ја подобриме употребливоста на <strong>[Company Website]</strong> и да обезбедиме еднаков пристап за сите, без разлика на способностите или технологијата што се користи.

						<h3>Нашиот пристап кон пристапноста</h3>
						Се стремиме да ги следиме меѓународните стандарди како што се Упатствата за пристапност на веб-содржината (WCAG). Иако целосно усогласување не е секогаш можно, постојано работиме за подобрување на пристапноста и редовно ги прегледуваме релевантните делови.

						Пристапноста е континуиран процес — обврзани сме на постојано подобрување како што се развиваат технологијата и потребите на корисниците.

						<h3>Функции за пристапност</h3>
						<strong>[Company Website]</strong> може да користи алатки како лентата за пристапност OneTap, која обезбедува:
						<ul>
							<li>Прилагодување на големината на текстот и контрастот</li>
							<li>Истакнување на врските и текстот</li>
							<li>Целосна навигација со тастатура</li>
							<li>Кратенка на тастатурата: <strong>Alt + .</strong> (Windows) или <strong>⌘ + .</strong> (Mac)</li>
						</ul>
						<strong>Забелешки:</strong>
						<ul>
							<li>Функциите зависат од конфигурацијата и одржувањето на веб-страницата.</li>
							<li>Не можеме да гарантираме целосна пристапност на сите делови од <strong>[Company Website]</strong>. Некој содржина може да биде од трети страни или да има технички ограничувања.</li>
						</ul>

						<h3>Повратни информации и контакт</h3>
						Ги цениме вашите повратни информации. Ако наидете на пречки во пристапноста или имате предлози, ве молиме контактирајте не:

						Е-пошта: <strong>[Company E-Mail]</strong>

						Обично одговараме во рок од 3–5 работни дена. Доколку е потребно, помош е достапна и преку алтернативни канали.

						<strong>Последна ажурирање:</strong> [March 9, 2025]
					</div>					

					<!-- Thai -->
					<div data-content-lang="th" class="status-message-accessibility" style="display: none;">
						<h2>พันธสัญญาการเข้าถึงของ [Company Name]</h2>
						<strong>[Company Name]</strong> มุ่งมั่นที่จะทำให้เนื้อหาดิจิทัลของเราสามารถเข้าถึงได้และครอบคลุมมากที่สุดสำหรับผู้ใช้ทุกคน รวมถึงผู้ที่มีความพิการ เป้าหมายของเราคือการปรับปรุงการใช้งานของ <strong>[Company Website]</strong> และให้แน่ใจว่าทุกคนสามารถเข้าถึงได้อย่างเท่าเทียมกัน โดยไม่คำนึงถึงความสามารถหรือเทคโนโลยีที่ใช้

						<h3>แนวทางของเราต่อการเข้าถึง</h3>
						เราพยายามปฏิบัติตามมาตรฐานสากล เช่น แนวทางการเข้าถึงเนื้อหาเว็บ (WCAG) แม้ว่าการปฏิบัติตามอย่างเต็มรูปแบบอาจไม่เป็นไปได้เสมอไป แต่เราทำงานอย่างต่อเนื่องเพื่อปรับปรุงการเข้าถึงและตรวจสอบส่วนที่เกี่ยวข้องเป็นประจำ

						การเข้าถึงเป็นกระบวนการที่ต่อเนื่อง — เรามุ่งมั่นที่จะปรับปรุงอย่างต่อเนื่องเมื่อเทคโนโลยีและความต้องการของผู้ใช้พัฒนาไป

						<h3>คุณสมบัติการเข้าถึง</h3>
						<strong>[Company Website]</strong> อาจใช้เครื่องมือ เช่น แถบเครื่องมือการเข้าถึง OneTap ซึ่งให้:
						<ul>
							<li>การปรับขนาดข้อความและคอนทราสต์</li>
							<li>การเน้นลิงก์และข้อความ</li>
							<li>การนำทางด้วยแป้นพิมพ์แบบเต็ม</li>
							<li>แป้นพิมพ์ลัด: <strong>Alt + .</strong> (Windows) หรือ <strong>⌘ + .</strong> (Mac)</li>
						</ul>
						<strong>หมายเหตุ:</strong>
						<ul>
							<li>คุณสมบัติขึ้นอยู่กับการกำหนดค่าและการบำรุงรักษาของเว็บไซต์</li>
							<li>เราไม่สามารถรับประกันการเข้าถึงอย่างเต็มรูปแบบของทุกส่วนของ <strong>[Company Website]</strong> เนื้อหาบางส่วนอาจมาจากบุคคลที่สามหรือมีข้อจำกัดทางเทคนิค</li>
						</ul>

						<h3>ข้อเสนอแนะและการติดต่อ</h3>
						เราขอขอบคุณข้อเสนอแนะของคุณ หากคุณพบอุปสรรคในการเข้าถึงหรือมีข้อเสนอแนะ กรุณาติดต่อเรา:

						อีเมล: <strong>[Company E-Mail]</strong>

						เรามักจะตอบกลับภายใน 3–5 วันทำการ หากจำเป็น ความช่วยเหลือยังสามารถใช้ได้ผ่านช่องทางอื่น

						<strong>อัปเดตล่าสุด:</strong> [March 9, 2025]
					</div>

					<!-- Vietnamese -->
					<div data-content-lang="vn" class="status-message-accessibility" style="display: none;">
						<h2>Cam kết Khả năng Truy cập của [Company Name]</h2>
						<strong>[Company Name]</strong> cam kết làm cho nội dung kỹ thuật số của chúng tôi có thể truy cập và bao trùm nhất có thể cho tất cả người dùng, bao gồm cả những người khuyết tật. Mục tiêu của chúng tôi là cải thiện khả năng sử dụng của <strong>[Company Website]</strong> và đảm bảo quyền truy cập bình đẳng cho mọi người, bất kể khả năng hay công nghệ được sử dụng.

						<h3>Cách tiếp cận của chúng tôi đối với Khả năng Truy cập</h3>
						Chúng tôi phấn đấu tuân theo các tiêu chuẩn quốc tế như Hướng dẫn Khả năng Truy cập Nội dung Web (WCAG). Mặc dù việc tuân thủ đầy đủ có thể không phải lúc nào cũng khả thi, chúng tôi liên tục làm việc để cải thiện khả năng truy cập và thường xuyên xem xét các phần liên quan.

						Khả năng truy cập là một quá trình liên tục — chúng tôi cam kết cải thiện liên tục khi công nghệ và nhu cầu người dùng phát triển.

						<h3>Tính năng Khả năng Truy cập</h3>
						<strong>[Company Website]</strong> có thể sử dụng các công cụ như thanh công cụ khả năng truy cập OneTap, cung cấp:
						<ul>
							<li>Điều chỉnh kích thước văn bản và độ tương phản</li>
							<li>Làm nổi bật liên kết và văn bản</li>
							<li>Điều hướng bàn phím đầy đủ</li>
							<li>Phím tắt: <strong>Alt + .</strong> (Windows) hoặc <strong>⌘ + .</strong> (Mac)</li>
						</ul>
						<strong>Lưu ý:</strong>
						<ul>
							<li>Các tính năng phụ thuộc vào cấu hình và bảo trì của trang web.</li>
							<li>Chúng tôi không thể đảm bảo khả năng truy cập đầy đủ cho tất cả các phần của <strong>[Company Website]</strong>. Một số nội dung có thể đến từ bên thứ ba hoặc có hạn chế kỹ thuật.</li>
						</ul>

						<h3>Phản hồi và Liên hệ</h3>
						Chúng tôi đánh giá cao phản hồi của bạn. Nếu bạn gặp phải rào cản về khả năng truy cập hoặc có đề xuất, vui lòng liên hệ với chúng tôi:

						Email: <strong>[Company E-Mail]</strong>

						Chúng tôi thường phản hồi trong vòng 3–5 ngày làm việc. Nếu cần, hỗ trợ cũng có sẵn thông qua các kênh thay thế.

						<strong>Cập nhật lần cuối:</strong> [March 9, 2025]
					</div>
					<!-- Bengali -->
					<div data-content-lang="bn" class="status-message-accessibility" style="display: none;">
						<h2>[Company Name] এর অ্যাক্সেসিবিলিটি প্রতিশ্রুতি</h2>
						<strong>[Company Name]</strong>-এ আমরা প্রতিবন্ধী ব্যক্তিসহ সকল ব্যবহারকারীর জন্য আমাদের ডিজিটাল উপস্থিতি যতটা সম্ভব অ্যাক্সেসযোগ্য ও অন্তর্ভুক্তিমূলক করতে প্রতিশ্রুতিবদ্ধ। আমাদের লক্ষ্য হলো <strong>[Company Website]</strong>-এর ব্যবহারযোগ্যতা উন্নত করা এবং সামর্থ্য বা ব্যবহৃত প্রযুক্তি নির্বিশেষে সবার জন্য আরও অ্যাক্সেসযোগ্য অভিজ্ঞতা নিশ্চিত করা।

						<h3>অ্যাক্সেসিবিলিটির প্রতি আমাদের দৃষ্টিভঙ্গি</h3>
						আমরা ওয়েব কনটেন্ট অ্যাক্সেসিবিলিটি গাইডলাইনস (WCAG) অনুসরণ করার লক্ষ্য রাখি, যা ডিজিটাল অ্যাক্সেসিবিলিটির আন্তর্জাতিকভাবে স্বীকৃত মান নির্ধারণ করে। সম্পূর্ণ সম্মতি সবসময় নিশ্চিত করা সম্ভব না হলেও, আমরা যেখানে সম্ভব উন্নতি বাস্তবায়নের চেষ্টা করি এবং নিয়মিতভাবে আমাদের ওয়েবসাইটের অ্যাক্সেসিবিলিটি-সম্পর্কিত দিকগুলো পর্যালোচনা করি।

						অ্যাক্সেসিবিলিটি একটি চলমান প্রক্রিয়া, এবং প্রযুক্তি, মান ও ব্যবহারকারীর চাহিদা বিকশিত হওয়ার সাথে সাথে আমরা এই অভিজ্ঞতা উন্নত করতে প্রতিশ্রুতিবদ্ধ।

						<h3>অ্যাক্সেসিবিলিটি বৈশিষ্ট্য</h3>
						অ্যাক্সেসিবিলিটিকে সহায়তা করতে <strong>[Company Website]</strong> OneTap অ্যাক্সেসিবিলিটি টুলবারের মতো সরঞ্জাম ব্যবহার করতে পারে। এই ইন্টারফেস ব্যবহারকারীদের নানা উপযোগী বৈশিষ্ট্য দেয়, যার মধ্যে রয়েছে:
						<ul>
							<li>সমন্বয়যোগ্য টেক্সট আকার ও কনট্রাস্ট সেটিংস</li>
							<li>ভালো দৃশ্যমানতার জন্য লিঙ্ক ও টেক্সট হাইলাইট করা</li>
							<li>টুলবার ইন্টারফেসের সম্পূর্ণ কীবোর্ড নেভিগেশন</li>
							<li>কীবোর্ড শর্টকাটের মাধ্যমে দ্রুত চালু: <strong>Alt + .</strong> (Windows) অথবা <strong>⌘ + .</strong> (Mac)</li>
						</ul>
						<strong>অনুগ্রহ করে নিম্নলিখিত বিষয়গুলো লক্ষ্য করুন:</strong>
						<ul>
							<li>এই বৈশিষ্ট্যগুলোর প্রাপ্যতা ও কার্যকারিতা ওয়েবসাইটের কনফিগারেশন ও নিয়মিত রক্ষণাবেক্ষণের উপর নির্ভর করে।</li>
							<li>আমরা অ্যাক্সেসিবিলিটি নিশ্চিত করার চেষ্টা করলেও, <strong>[Company Website]</strong>-এর প্রতিটি অংশ সবসময় সম্পূর্ণ অ্যাক্সেসযোগ্য থাকবে বলে আমরা নিশ্চয়তা দিতে পারি না। কিছু কনটেন্ট তৃতীয় পক্ষ সরবরাহ করতে পারে অথবা আমাদের সরাসরি নিয়ন্ত্রণের বাইরের কারিগরি সীমাবদ্ধতায় প্রভাবিত হতে পারে।</li>
						</ul>
						<h3>মতামত ও যোগাযোগ</h3>
						আমরা আপনার মতামতকে স্বাগত জানাই। আপনি যদি কোনো অ্যাক্সেসিবিলিটি প্রতিবন্ধকতার সম্মুখীন হন অথবা উন্নতির জন্য পরামর্শ থাকে, অনুগ্রহ করে আমাদের সাথে যোগাযোগ করুন:

						ইমেইল: <strong>[Company E-Mail]</strong>

						আমরা সব অনুসন্ধান পর্যালোচনা করতে প্রতিশ্রুতিবদ্ধ এবং ৩–৫ কর্মদিবসের মধ্যে উত্তর দেওয়ার লক্ষ্য রাখি। এই ওয়েবসাইটের কোনো অংশে প্রবেশ করতে আপনার সহায়তা প্রয়োজন হলে, অনুরোধের ভিত্তিতে আমরা বিকল্প মাধ্যমে সহায়তা দিতে প্রস্তুত।

						<strong>সর্বশেষ হালনাগাদ:</strong> [March 9, 2025]
					</div>

					<!-- Filipino -->
					<div data-content-lang="tl" class="status-message-accessibility" style="display: none;">
						<h2>Pangako sa Accessibility ng [Company Name]</h2>
						Sa <strong>[Company Name]</strong>, nakatuon kami sa paggawa ng aming digital na presensya na kasing accessible at inklusibo hangga't makatwirang posible para sa lahat ng user, kabilang ang mga indibidwal na may kapansanan. Layunin naming pahusayin ang usability ng <strong>[Company Website]</strong> at suportahan ang mas accessible na karanasan para sa lahat, anuman ang kanilang kakayahan o ang teknolohiyang ginagamit nila.

						<h3>Ang Aming Paglapit sa Accessibility</h3>
						Layunin naming sumunod sa Web Content Accessibility Guidelines (WCAG), na nagtatakda ng mga pamantayang kinikilala sa buong mundo para sa digital accessibility. Bagaman hindi laging masisiguro ang ganap na pagsunod, nagsisikap kaming magpatupad ng mga pagpapabuti kung saan posible at regular naming sinusuri ang mga aspetong may kaugnayan sa accessibility ng aming website.

						Ang accessibility ay isang patuloy na proseso, at nakatuon kami sa pagpapabuti ng karanasan sa paglipas ng panahon habang umuunlad ang teknolohiya, mga pamantayan, at pangangailangan ng user.

						<h3>Mga Tampok sa Accessibility</h3>
						Upang suportahan ang accessibility, maaaring gumamit ang <strong>[Company Website]</strong> ng mga tool tulad ng OneTap accessibility toolbar. Nagbibigay ang interface na ito sa mga user ng iba't ibang kapaki-pakinabang na tampok, kabilang ang:
						<ul>
							<li>Naaayos na laki ng teksto at mga setting ng kontrast</li>
							<li>Pag-highlight ng mga link at teksto para sa mas mahusay na visibility</li>
							<li>Buong nabigasyon sa keyboard ng interface ng toolbar</li>
							<li>Mabilis na paglulunsad sa pamamagitan ng keyboard shortcut: <strong>Alt + .</strong> (Windows) o <strong>⌘ + .</strong> (Mac)</li>
						</ul>
						<strong>Pakitandaan ang sumusunod:</strong>
						<ul>
							<li>Ang availability at bisa ng mga tampok na ito ay nakadepende sa configuration at patuloy na pagpapanatili ng website.</li>
							<li>Bagaman nagsisikap kaming tiyakin ang accessibility, hindi namin magagarantiya na ang bawat bahagi ng <strong>[Company Website]</strong> ay ganap na accessible sa lahat ng oras. Ang ilang nilalaman ay maaaring ibinibigay ng mga third party o naaapektuhan ng mga teknikal na limitasyon na labas sa aming agarang kontrol.</li>
						</ul>
						<h3>Feedback at Kontak</h3>
						Malugod naming tinatanggap ang inyong feedback. Kung makaranas kayo ng anumang hadlang sa accessibility o may mungkahi para sa pagpapabuti, mangyaring makipag-ugnayan sa amin:

						Email: <strong>[Company E-Mail]</strong>

						Nakatuon kami sa pagsusuri ng lahat ng katanungan at layunin naming tumugon sa loob ng 3–5 araw ng negosyo. Kung kailangan ninyo ng tulong sa pag-access ng anumang bahagi ng website na ito, malugod kaming magbibigay ng suporta sa pamamagitan ng alternatibong mga channel kapag hiniling.

						<strong>Huling na-update:</strong> [March 9, 2025]
					</div>

					<!-- Urdu -->
					<div data-content-lang="ur" class="status-message-accessibility" dir="rtl" style="display: none;">
						<h2>[Company Name] کے لیے رسائی کا عزم</h2>
						<strong>[Company Name]</strong> میں ہم اپنی ڈیجیٹل موجودگی کو تمام صارفین کے لیے، بشمول معذور افراد، ممکنہ حد تک قابلِ رسائی اور شامل کرنے والا بنانے کے لیے پرعزم ہیں۔ ہمارا مقصد <strong>[Company Website]</strong> کی استعمال پذیری کو بہتر بنانا اور ہر شخص کے لیے زیادہ قابلِ رسائی تجربہ فراہم کرنا ہے، خواہ اس کی صلاحیت یا استعمال کردہ ٹیکنالوجی کچھ بھی ہو۔

						<h3>رسائی کے بارے میں ہمارا نقطۂ نظر</h3>
						ہم ویب مواد رسائی رہنما اصول (WCAG) کے مطابق چلنے کی کوشش کرتے ہیں، جو ڈیجیٹل رسائی کے بین الاقوامی طور پر تسلیم شدہ معیارات متعین کرتے ہیں۔ اگرچہ مکمل تعمیل کی ہمیشہ ضمانت نہیں دی جا سکتی، ہم جہاں ممکن ہو بہتری لانے کی کوشش کرتے ہیں اور اپنی ویب سائٹ کے رسائی سے متعلق پہلوؤں کا باقاعدگی سے جائزہ لیتے ہیں۔

						رسائی ایک مسلسل عمل ہے، اور جیسے جیسے ٹیکنالوجی، معیارات اور صارفین کی ضروریات بدلتی ہیں، ہم اس تجربے کو بہتر بنانے کے لیے پرعزم ہیں۔

						<h3>رسائی کی خصوصیات</h3>
						رسائی میں مدد کے لیے <strong>[Company Website]</strong> OneTap رسائی ٹول بار جیسے آلات استعمال کر سکتی ہے۔ یہ انٹرفیس صارفین کو کئی مفید خصوصیات فراہم کرتا ہے، جن میں شامل ہیں:
						<ul>
							<li>قابلِ ترمیم متن کا سائز اور کنٹراسٹ کی ترتیبات</li>
							<li>بہتر نمائش کے لیے لنکس اور متن کو نمایاں کرنا</li>
							<li>ٹول بار انٹرفیس کی مکمل کی بورڈ نیویگیشن</li>
							<li>کی بورڈ شارٹ کٹ سے فوری اجرا: <strong>Alt + .</strong> (Windows) یا <strong>⌘ + .</strong> (Mac)</li>
						</ul>
						<strong>براہ کرم درج ذیل باتوں کا خیال رکھیں:</strong>
						<ul>
							<li>ان خصوصیات کی دستیابی اور افادیت ویب سائٹ کی ترتیب اور مسلسل دیکھ بھال پر منحصر ہے۔</li>
							<li>اگرچہ ہم رسائی یقینی بنانے کی کوشش کرتے ہیں، ہم اس بات کی ضمانت نہیں دے سکتے کہ <strong>[Company Website]</strong> کا ہر حصہ ہر وقت مکمل طور پر قابلِ رسائی ہوگا۔ کچھ مواد فریقِ ثالث فراہم کر سکتا ہے یا وہ ایسی تکنیکی حدود سے متاثر ہو سکتا ہے جو ہمارے براہِ راست اختیار سے باہر ہیں۔</li>
						</ul>
						<h3>رائے اور رابطہ</h3>
						ہم آپ کی رائے کا خیرمقدم کرتے ہیں۔ اگر آپ کو رسائی میں کوئی رکاوٹ پیش آئے یا بہتری کے لیے کوئی تجویز ہو، تو براہ کرم ہم سے رابطہ کریں:

						ای میل: <strong>[Company E-Mail]</strong>

						ہم تمام استفسارات کا جائزہ لینے کے لیے پرعزم ہیں اور 3–5 کاروباری دنوں کے اندر جواب دینے کا ہدف رکھتے ہیں۔ اگر آپ کو اس ویب سائٹ کے کسی حصے تک رسائی میں مدد درکار ہو تو درخواست پر ہم متبادل ذرائع سے معاونت فراہم کرنے کے لیے تیار ہیں۔

						<strong>آخری تجدید:</strong> [March 9, 2025]
					</div>


					<!-- Information -->
						<div class="box-information">
							<span class="setting-title">
								<?php esc_html_e( 'Generate Accessibility Status', 'accessibility-onetap' ); ?>
							</span>
							<span class="setting-description">
								<?php esc_html_e( 'You can edit or insert a Statement as needed. Click "Save settings" to save your changes.', 'accessibility-onetap' ); ?>
								<a href="https://wponetap.com/tutorial/accessibility-statement-generator/" target="_blank"><?php esc_html_e( 'See Documentation', 'accessibility-onetap' ); ?>→</a>
							</span>
						</div>

						<!-- WP Editor -->
						<div class="box-editor">
							<?php
							// Get plugin URL untuk CSS custom.
							$onetap_plugin_url = plugin_dir_url( __DIR__ );

							$onetap_editor_settings = array(
								'teeny'         => false,
								'textarea_name' => 'onetap_editor_generator',
								'media_buttons' => false,
								'tinymce'       => array(
									'toolbar1'    => 'formatselect bold italic underline bullist numlist blockquote alignleft aligncenter alignright link unlink',
									'content_css' => ACCESSIBILITY_ONETAP_PLUGINS_URL . 'assets/css/wp-editor-custom.css',
								),
								'quicktags'     => false,
								'editor_height' => 800,
							);

							wp_editor(
								wp_kses_post( $accessibility_status['editor_generator'] ),
								'editor_generator',
								$onetap_editor_settings
							);
							?>
						</div>
					</div>
				</div>

				<div class="submit-button">
					<?php submit_button(); ?>
				</div>
			</form>
			<?php onetap_load_template( 'admin/partials/footer.php' ); ?>
		</div>
	</div>
</div>
