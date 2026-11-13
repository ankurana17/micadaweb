/** Import only assets used by the native public templates; no Angular runtime. */
const fs = require('fs');
const path = require('path');
const source = process.argv[2];
if (!source || !fs.existsSync(path.join(source, 'angular.json'))) throw new Error('Pass the existing Angular project path.');
const theme = path.resolve(__dirname, '..');
const output = path.join(theme, 'assets');
const assetPath = require('./asset-layout.cjs');
const parts = path.join(theme, 'template-parts');
fs.mkdirSync(output, { recursive: true });
for (const folder of ['css','js','images','fonts','documents','data']) fs.mkdirSync(path.join(output, folder), { recursive:true });
fs.mkdirSync(parts, { recursive: true });
const read = p => fs.readFileSync(path.join(source, p), 'utf8');
const stripComments = text => text.replace(/<!--[\s\S]*?-->/g, '');
let header = stripComments(read('src/app/components/landing/header/header.component.html'));
let footer = stripComments(read('src/app/components/landing/footer/footer.component.html'));
const keys = new Set(['BACK', 'PHOTO_GALLERY', 'VIDEO_GALLERY', 'SUCCESS_STORIES']);
function convert(markup) {
    return markup.replace(/(?:\.\.\/)*assets\/([^"<>]+)/g, (_, asset) => {
        const input = path.join(source, 'src/assets', asset);
        const destination = path.join(output, assetPath(asset));
        if (!fs.existsSync(input)) throw new Error('Missing asset: ' + asset);
        fs.mkdirSync(path.dirname(destination), { recursive: true });
        fs.copyFileSync(input, destination);
        return `<?php echo esc_url( micada_asset( '${asset}' ) ); ?>`;
    }).replace(/{{\s*(['"])(.*?)\1\s*\|\s*translate\s*}}/g, (_, quote, key) => {
        keys.add(key);
        const settings = { MICADA_HEADING: 'heading', BAYS_39_40_SECTOR_4_PANCHKULA: 'address' };
        const expression = settings[key] ? `micada_editorial_text( '${settings[key]}', '${key}' )` : `micada_text( '${key}' )`;
        return `<?php echo esc_html( ${expression} ); ?>`;
    }).replace(/target="_blank"/g, 'target="_blank" rel="noopener noreferrer"');
}
header = header.replace(/<p-dropdown[\s\S]*?<\/p-dropdown>/g, `<select id="micada-language" data-micada-language aria-label="Language"><option value="en" <?php selected( micada_language(), 'en' ); ?>>English</option><option value="hd" <?php selected( micada_language(), 'hd' ); ?>>Hindi</option></select>`);
header = header.replace('<label>', '<label for="micada-language">');
header = header.replace('<header class="">', '<header class="micada-header">');
header = header.replace(/<img src="([^\"]+)">/g, '<img src="$1" alt="MICADA Haryana">');
footer = footer.replace(/{{\s*lastUpdated\s*\|\s*date:'mediumDate'\s*}}/g, `<?php echo esc_html( wp_date( 'M j, Y', strtotime( '2026-08-24' ) ) ); ?>`);
footer = footer.replace('0172–2562275', `<?php echo esc_html( micada_editorial_text( 'phone', '0172–2562275' ) ); ?>`);
footer = footer.replace('cmda@hry.nic.in', `<?php echo esc_html( micada_editorial_text( 'email', 'cmda@hry.nic.in' ) ); ?>`);
footer = '<footer class="micada-footer">' + footer + '</footer>';
const guard = "<?php\n/** Ported from the existing MICADA Angular public layout. */\ndefined( 'ABSPATH' ) || exit;\n?>\n";
fs.writeFileSync(path.join(parts, 'header.php'), guard + convert(header));
fs.writeFileSync(path.join(parts, 'footer.php'), guard + convert(footer));
const translations = {};
for (const language of ['en', 'hd']) {
    const all = JSON.parse(read(`src/assets/i18n/${language}.json`));
    translations[language] = Object.fromEntries([...keys].map(key => [key, all[key] || key]));
}
fs.writeFileSync(path.join(output, 'data/translations.json'), JSON.stringify(translations, null, 2));
fs.copyFileSync(path.join(source, 'node_modules/primeflex/primeflex.css'), path.join(output, 'css/primeflex.css'));
fs.writeFileSync(path.join(output, 'css/primeicons.css'), read('node_modules/primeicons/primeicons.css').replaceAll('./fonts/', '../fonts/'));
fs.mkdirSync(path.join(output, 'fonts'), { recursive: true });
for (const font of fs.readdirSync(path.join(source, 'node_modules/primeicons/fonts'))) {
    fs.copyFileSync(path.join(source, 'node_modules/primeicons/fonts', font), path.join(output, 'fonts', font));
}
const sass = require(path.join(source, 'node_modules/sass'));
let css = '.micada-public{margin:0;background:#fff;color:#000;font-family:Arial,sans-serif;font-size:14px}.micada-public p{margin-top:0}.micada-public .back-button{border:0;border-radius:6px;color:white;padding:.6rem 1rem;cursor:pointer}.micada-public iframe{border:0}.micada-public .text-center{text-align:center}.micada-public .micada-editor-content{width:100%}.micada-header select{font:inherit;padding:.5rem;border:1px solid #ced4da;border-radius:6px;background:#fff}.micada-header .social-icon{width:24px;height:24px;object-fit:contain}';
for (const [scope, file] of [
    ['.micada-header', 'src/app/components/landing/header/header.component.scss'],
    ['.micada-footer', 'src/app/components/landing/footer/footer.component.scss'],
    ['.micada-public', 'src/app/components/gallery/video-gallery/video-gallery.component.scss']
]) {
    const scss = read(file).replace(/:host\s*::ng-deep\s*/g, '');
    css += sass.renderSync({ data: `${scope}{${scss}}`, outputStyle: 'expanded' }).css.toString();
}
fs.writeFileSync(path.join(output, 'css/public.css'), css);
console.log(`Imported public layouts, scoped styles, ${keys.size} translation keys per language and referenced image assets.`);
