<?php
/** Ported from the existing MICADA Angular public layout. */
defined( 'ABSPATH' ) || exit;
?>
<header class="micada-header">
    <div class="p-fluid p-formgrid justify-content-center top-header-sec" style="display: flex; flex-wrap:wrap">
        
        <div class="field sm:col-6 md:col-1 lg:col-1 my-0 py-0 mx-0 px-0 logo-continer">
            <img src="<?php echo esc_url( micada_asset( 'activitiesImages/new_logo.png' ) ); ?>" alt="MICADA Haryana">
        </div>
        <div class="p-fluid p-formgrid justify-content-center field sm:col-12 md:col-10 lg:col-10 my-0 py-0 mx-0 px-2" style="display: flex; flex-wrap:wrap">
            <div class="field sm:col-12 md:col-12 lg:col-12 my-0 py-0 mx-0 px-0 micada-heading">
                <p><?php echo esc_html( micada_editorial_text( 'heading', 'MICADA_HEADING' ) ); ?></p>
            </div>
            <div class="field sm:col-12 md:col-12 lg:col-12 my-0 py-0 mx-0">
                
                <div class="p-fluid p-formgrid justify-content-center" style="display: flex; flex-wrap:wrap">
                    <div class="field sm:col-6 md:col-6 lg:col-6 my-0 py-0">
                        <div class="language-translation social-icons d-flex">
                            <label for="micada-language"><?php echo esc_html( micada_text( 'LANGUAGE' ) ); ?></label>
                            <select id="micada-language" data-micada-language aria-label="Language"><option value="en" <?php selected( micada_language(), 'en' ); ?>>English</option><option value="hd" <?php selected( micada_language(), 'hd' ); ?>>Hindi</option></select>
                        </div>
                    </div>
    
                    <div class="field sm:col-6 md:col-6 lg:col-6 my-0 py-0 px-2">
                        <div class="language-translation social-icons d-flex">
                            <span class=""><?php echo esc_html( micada_text( 'FOLLOW_US' ) ); ?></span>
                            <a href="https://www.facebook.com/share/12BhJki9f8j/?mibextid=LQQJ4d" target="_blank" rel="noopener noreferrer">
                                <img src="<?php echo esc_url( micada_asset( 'home/logo/facebook.png' ) ); ?>" alt="Facebook" class="social-icon" />
                            </a>
                            <a href="https://x.com/Micadaharyana" target="_blank" rel="noopener noreferrer">
                                <img src="<?php echo esc_url( micada_asset( 'home/logo/x.png' ) ); ?>" alt="X (Twitter)" class="social-icon" />
                            </a>
                            
                        </div>
                    </div>
                </div>
    
                
            </div>
        </div>
        <div class="field sm:col-2 md:col-1 lg:col-1 my-0 py-0 mx-0 px-0 logo-continer hide-small">
            <img src="<?php echo esc_url( micada_asset( 'activitiesImages/logo.png' ) ); ?>" alt="MICADA Haryana">
        </div>
        

        
    </div>
    
</header>
