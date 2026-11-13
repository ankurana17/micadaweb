/* Source-only importer for the MICADA native homepage. */
const fs = require('fs');
const path = require('path');
const source = process.argv[2];
if (!source || !fs.existsSync(path.join(source, 'angular.json'))) throw new Error('Pass existing Angular project path.');
const root = path.resolve(__dirname, '..');
const out = path.join(root, 'assets');
const assetPath = require('./asset-layout.cjs');
const parts = path.join(root, 'template-parts/home');
fs.mkdirSync(parts, { recursive:true });
const read = file => fs.readFileSync(path.join(source, file), 'utf8');
const ts = require(path.join(source, 'node_modules/typescript'));
const sass = require(path.join(source, 'node_modules/sass'));
const template = read('src/app/components/landing/landing.component.html').replace(/<!--[\s\S]*?-->/g, '');
const ast = ts.createSourceFile('home.ts', read('src/app/components/landing/landing.component.ts'), ts.ScriptTarget.Latest, true);
const keys = new Set();
const copyAsset = original => {
    const relative = decodeURIComponent(original.replace(/^(?:\.\.\/)*assets\//, ''));
    const input = path.join(source, 'src/assets', relative);
    if (!fs.existsSync(input)) { console.log('Using official asset: ' + relative); return 'https://micada.haryana.gov.in/assets/' + relative; }
    const destination = path.join(out, assetPath(relative));
    fs.mkdirSync(path.dirname(destination), { recursive: true });
    fs.copyFileSync(input, destination);
    return relative;
};
const literal = node => {
    if (ts.isStringLiteral(node)) return node.text;
    if (ts.isCallExpression(node) && node.expression.getText(ast).endsWith('.instant')) { keys.add(node.arguments[0].text); return '@' + node.arguments[0].text; }
    if (ts.isArrayLiteralExpression(node)) return node.elements.map(literal);
    if (ts.isObjectLiteralExpression(node)) return Object.fromEntries(node.properties.map(property => [property.name.getText(ast), literal(property.initializer)]));
    throw new Error('Unexpected literal: ' + node.getText(ast));
};
const data = {};
const names = { bannerScrollerImages: 'banners', leaders: 'leaders' };
function visit(node) {
    if (ts.isPropertyDeclaration(node) && names[node.name?.getText(ast)] && node.initializer && ts.isArrayLiteralExpression(node.initializer) && node.initializer.elements.length) data[names[node.name.getText(ast)]] = literal(node.initializer);
    if (ts.isBinaryExpression(node) && node.operatorToken.kind === ts.SyntaxKind.EqualsToken && ts.isArrayLiteralExpression(node.right)) {
        const name = node.left.getText(ast).replace(/^this\./, '');
        if (names[name]) data[names[name]] = literal(node.right);
    }
    ts.forEachChild(node, visit);
}
visit(ast);
for (const collection of Object.values(data)) for (const item of collection) for (const key of ['previewImageSrc', 'image']) if (item[key]) item[key] = copyAsset(item[key]);
data.notice = copyAsset('assets/landing page pop-up content/landing page pop-up.jpeg');
copyAsset('assets/activitiesImages/newitem.gif');
function convert(markup) {
    return markup.replace(/\[routerLink\]="\['\/([^']+)'\]"/g, (_, slug) => `href="<?php echo esc_url( micada_url( '${slug}' ) ); ?>"`)
        .replace(/(?:\.\.\/)*assets\/([^"<>]+)/g, (_, asset) => {
            const result = copyAsset('assets/' + asset);
            return result.startsWith('https:') ? result : `<?php echo esc_url( micada_asset( '${result.replace(/'/g, "\\'")}' ) ); ?>`;
        })
        .replace(/{{\s*(['"])(.*?)\1\s*\|\s*translate\s*}}/g, (_, quote, key) => { keys.add(key); return `<?php echo esc_html( micada_text( '${key}' ) ); ?>`; })
        .replace(/target="_blank"/g, 'target="_blank" rel="noopener noreferrer"');
}
const linksSection = template.slice(template.indexOf('<div #targetSection2'));
const quick = linksSection.match(/<marquee[\s\S]*?<\/marquee>/)[0];
const guard = "<?php defined( 'ABSPATH' ) || exit; ?>\n";
const eoi = `<li class="w3-padding-8 cursor-pointer-new w3-left-align"><a href="https://micada.haryana.gov.in/assets/quick_links_docs/20261016160550.pdf" target="_blank" rel="noopener noreferrer"><?php echo esc_html( micada_language() === 'hd' ? 'अभिरुचि की अभिव्यक्ति के लिए अनुरोध (EOI)' : 'Request for Expression of Interest (EOI)' ); ?> <img src="<?php echo esc_url( micada_asset( 'activitiesImages/newitem.gif' ) ); ?>" alt="New Item"></a></li>`;
fs.writeFileSync(path.join(parts, 'quick-links-default.php'), guard + convert(quick).replace(/(<marquee[^>]*>)/, '$1' + eoi));
const about = template.match(/<ul \*ngIf="showHome"[\s\S]*?<\/ul>/)[0].replace(' *ngIf="showHome"', '');
fs.writeFileSync(path.join(parts, 'about-default.php'), guard + convert(about));
const moreKeys = ['HOME','ABOUT_US','SUCCESS_STORIES','CADMIS','RTI','ORGANISATION','HEAD_OFF_EXECUTIVES','FIELD_EXECUTIVES','OFFICE_ORDERS','MI_CIRCULAR','TARGET','PHOTO_VIDEO_GALLERY','PHOTO_GALLERY','VIDEO_GALLERY','FAQs','FAQ_L1','FAQ_L2','FAQ_L3','LOGIN','OFFICER_LOGIN','VENDOR_LOGIN','NEW_REGISTRATION','CONTACT_US','FARMER_CORNER','PORTAL_OPEN','STP/TWW_BASED_MI','OVERFLOW_POND_BASED_MI','CANAL_BASED_MI','TUBEWELL_BASED_MI','APPLY_FOR_NOC(MI)','NEW_VENDOR_REGISTRATION_OPEN_NOW','QUICK_LINKS','NEWS_AND_EVENTS','FARMER_ONLINE_APPLICATION_FOR_MI','FARMER_ONLINE_APPLICATION_HAS_BEEN_INAUGURATED','MORE_PHOTOS','MORE_VIDEOS'];
for (const key of moreKeys) keys.add(key);
const translations = JSON.parse(fs.readFileSync(path.join(out, 'data/translations.json'), 'utf8'));
for (const lang of ['en','hd']) {
    const original = JSON.parse(read(`src/assets/i18n/${lang}.json`));
    for (const key of keys) translations[lang][key] = original[key] || key;
}
fs.writeFileSync(path.join(out, 'data/translations.json'), JSON.stringify(translations, null, 2));
fs.writeFileSync(path.join(out, 'data/home-data.json'), JSON.stringify(data, null, 2));
const scss = read('src/app/components/landing/landing.component.scss').replace(/:host\s*::ng-deep\s*/g, '');
fs.writeFileSync(path.join(out, 'css/home-source.css'), sass.renderSync({ data: `.micada-home {${scss}}`, outputStyle: 'expanded' }).css);
for (const weight of ['Regular','Medium','SemiBold','Bold']) fs.copyFileSync(path.join(source, `src/assets/theme/lara-light-indigo/fonts/Inter-${weight}.woff2`), path.join(out, `fonts/Inter-${weight}.woff2`));
for (const name of ['about-us','achievements','annual-plan','beneficiary','contact-us','evaluation-study','field-executives','faqs','head-office-executives','mi-circular','micro-irrigation','new-initiatives','office-orders','org-setup','photo-gallery','project','rti','statistics','video-gallery']) {
    const file = path.join(root, 'page-templates', name + '.php');
    if (!fs.existsSync(file)) fs.writeFileSync(file, `<?php\n/** Template Name: MICADA ${name.replace(/-/g, ' ')} */\ndefined( 'ABSPATH' ) || exit;\nrequire __DIR__ . '/public.php';\n`);
}
console.log(JSON.stringify(Object.fromEntries(Object.entries(data).map(([key,value])=>[key,Array.isArray(value)?value.length:value]))));
