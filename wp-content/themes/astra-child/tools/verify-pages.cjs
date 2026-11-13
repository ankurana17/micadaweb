/* Read-only HTTP checks of native public pages and homepage theme assets. */
const http = require('http');
const get = url => new Promise((resolve, reject) => {
    const request = http.get(url, response => { let body = ''; response.on('data', chunk => body += chunk); response.on('end', () => resolve({ status: response.statusCode, body })); });
    request.setTimeout(20000, () => request.destroy(new Error('Request timed out')));
    request.on('error', reject);
});
(async () => {
    const base = 'http://127.0.0.1/micadaweb/';
    const ids = [1331,1332,1333,1334,1335,1336,1337,1338,1339,1340,1341,1342,1343,1344,1345,1346,1349,1350,1351,1352,1353];
    let pages = 0;
    for (let start = 0; start < ids.length; start += 4) {
        const batch = ids.slice(start, start + 4);
        await Promise.all(batch.map(async id => {
            const result = await get(base + '?page_id=' + id);
            if (result.status !== 200 || !result.body.includes('class="micada-header"') || /Fatal error|Parse error/.test(result.body)) throw new Error('Native page failed: ' + id);
            pages++;
        }));
    }
    const home = await get(base);
    if (home.status !== 200 || !home.body.includes('data-carousel')) throw new Error('Native homepage failed');
    const assets = [...new Set([...home.body.matchAll(/(?:src|href)=["']([^"']+)["']/g)].map(match => match[1].replace(/&#0?38;|&amp;/g, '&')).filter(url => url.startsWith(base) && url.includes('/themes/astra-child/assets/')))];
    for (let start = 0; start < assets.length; start += 6) {
        await Promise.all(assets.slice(start, start + 6).map(async url => {
            const result = await get(new URL(url));
            if (result.status !== 200 || !result.body.length) throw new Error('Asset failed: ' + url);
        }));
    }
    console.log(`PASS: homepage, ${pages} existing CMS public pages and ${assets.length} unique referenced local theme assets return HTTP 200.`);
})().catch(error => { console.error(error.message); process.exit(1); });
