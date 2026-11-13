<?php
/** Ported from the existing MICADA Angular public layout. */
defined( 'ABSPATH' ) || exit;
?>
<footer class="micada-footer"><div class="useful-links-section">
    <p class="useful-links-title"><?php echo esc_html( micada_text( 'IMPORTANT_LINKS' ) ); ?></p>
    <div class="useful-links-scroll-container">
        <?php
        $important_links = function_exists( 'micada_get_important_links' ) ? micada_get_important_links() : array();
        foreach ( $important_links as $index => $important_link ) :
            $image_url = wp_get_attachment_image_url( (int) $important_link->image_id, 'full' );
            if ( ! $image_url ) {
                continue;
            }
            $link_label = 'hd' === micada_language() && ! empty( $important_link->label_hi ) ? $important_link->label_hi : $important_link->label_en;
            ?>
            <a href="<?php echo esc_url( micada_resolve_app_link( $important_link->url ) ); ?>" target="_blank" rel="noopener noreferrer" class="useful-link-card card-<?php echo esc_attr( $index + 1 ); ?>" aria-label="<?php echo esc_attr( $link_label ); ?>">
                <img src="<?php echo esc_url( $image_url ); ?>" alt="<?php echo esc_attr( $important_link->alt_text ?: $link_label ); ?>" />
            </a>
        <?php endforeach; ?>
    </div>
</div>
<div class="contact-content mt-0 pt-2">
    <div class="p-fluid p-formgrid justify-content-center" style="display: flex; flex-wrap:wrap">
        <div class="field sm:col-12 md:col-12 lg:col-12 my-0 py-2 mx-0 px-0 contact-heading">
            <p><?php echo esc_html( micada_text( 'CONTACT_US' ) ); ?></p>
        </div>
        <div class="field sm:col-12 md:col-4 lg:col-4 my-0 py-0 mx-0 px-0">
            <div class="field sm:col-12 md:col-12 lg:col-12 my-1 py-2 mx-2 px-0 footer-info">
                <?php echo micada_icon_svg( 'map-marker', 'footer-icon' ); ?>
                <span class="footer-text"><?php echo esc_html( micada_editorial_text( 'address', 'BAYS_39_40_SECTOR_4_PANCHKULA' ) ); ?></span>
            </div>
            <div class="field sm:col-12 md:col-12 lg:col-12 my-1 py-2 mx-2 px-0 footer-info">
                <?php echo micada_icon_svg( 'phone', 'footer-icon' ); ?>
                <span class="footer-text"><?php echo esc_html( micada_editorial_text( 'phone', '0172–2562275' ) ); ?></span>
            </div>
            <div class="field sm:col-12 md:col-12 lg:col-12 my-1 py-2 mx-2 px-0 footer-info">
                <?php echo micada_icon_svg( 'envelope', 'footer-icon' ); ?>
                <span class="footer-text"><?php echo esc_html( micada_text( 'EMAIL' ) ); ?>: <?php echo esc_html( micada_editorial_text( 'email', 'cmda@hry.nic.in' ) ); ?></span>
            </div>
            <div class="field sm:col-12 md:col-12 lg:col-12 mt-5 py-2 mx-2 px-0 footer-info">
                <span class="footer-text-last-date"><?php echo esc_html( micada_text( 'LAST_UPDATED_ON' ) ); ?> <?php echo esc_html( wp_date( 'M j, Y', strtotime( '2026-08-24' ) ) ); ?></span>
            </div>
        </div>
        <div class="field sm:col-12 md:col-8 lg:col-8 my-0 py-0 mx-0 px-0">
            <div class="py-2 px-2">
                <iframe
                    src="https://www.google.com/maps/embed?pb=!1m14!1m8!1m3!1d3431.003752862236!2d76.8517319!3d30.6901705!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x390f93dcb3863f59%3A0xd765a8911cdb34ac!2sMICADA%20Haryana%20Head%20office!5e0!3m2!1sen!2sin!4v1734435581462!5m2!1sen!2sin"
                    width="100%" height="100%" style="border:0;" object-fit="contain" allowfullscreen="true"
                    loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
            </div>
        </div>
    </div>
</div>
<div class="footer-copyright text-center text-white py-2">
    Copyright © 2021 Micro Irrigation & Command Area Development Authority, Haryana. All Rights Reserved.
</div></footer>
