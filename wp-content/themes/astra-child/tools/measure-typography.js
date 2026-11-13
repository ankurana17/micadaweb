(async () => {
    await Promise.race([document.fonts.ready, new Promise(resolve => setTimeout(resolve, 5000))]);
    const selectors = {
        root:'html', body:'body', heading:'.micada-heading p', language:'.language-translation label',
        languageValue:'.p-dropdown-label, [data-micada-language]', follow:'.social-icons span',
        menu:'.p-menubar-root-list > li .p-menuitem-text, .micada-menu > li > a',
        farmer:'.marquee-static-text p', ticker:'.marquee-text .s1, .micada-farmer-line .s1',
        maintenance:'.maintenance-notice span, .maintenance-notice', application:'.login-button-section b',
        officialName:'.text-box-leader-content h5', officialTitle:'.text-box-leader-content p', location:'.text-box-leader-content .location',
        vendor:'.new-registration p', banner:'.top-level-image p, .micada-slide figcaption',
        panelTitle:'.events-links-header b', quick:'.info-container1 marquee a',
        welcome:'.info-container2 h5, .micada-about h2', about:'.info-container2 h4 b, .micada-about h3',
        bodyCopy:'.info-container2 p b, .micada-about p', news:'.info-container3 marquee b',
        photoCaption:'.photo-section span', morePhotos:'.photo-gallery-section .button-34',
        important:'.useful-links-title', contact:'.contact-heading p', address:'.footer-text',
        updated:'.footer-text-last-date', copyright:'.footer-copyright'
    };
    const result = {};
    for (const [key, selector] of Object.entries(selectors)) {
        const element = document.querySelector(selector);
        if (!element) { result[key] = null; continue; }
        const style = getComputedStyle(element);
        result[key] = Object.fromEntries(['fontFamily','fontSize','fontWeight','fontStyle','lineHeight','letterSpacing'].map(property => [property, style[property]]));
    }
    return {width:innerWidth, measurements:result};
})();
