/* Local headless-Chrome inspection; uses the existing source project's ws package. */
const http = require('http');
const fs = require('fs');
const path = require('path');
const WebSocket = require('D:/Anku Rana/PTG/MICADA_Clone/node_modules/ws');
const get = url => new Promise((resolve, reject) => http.get(url, res => { let body = ''; res.on('data', chunk => body += chunk); res.on('end', () => resolve(JSON.parse(body))); }).on('error', reject));
(async () => {
    const tabs = await get('http://127.0.0.1:9227/json');
    const socket = new WebSocket(tabs.find(tab => tab.type === 'page').webSocketDebuggerUrl);
    await new Promise(resolve => socket.once('open', resolve));
    let id = 0;
    const pending = new Map();
    const errors = [];
    socket.on('message', raw => {
        const message = JSON.parse(raw);
        if (message.id && pending.has(message.id)) {
            const { resolve, reject } = pending.get(message.id);
            pending.delete(message.id);
            message.error ? reject(message.error) : resolve(message.result);
        }
        if (message.method === 'Runtime.exceptionThrown') {
            const detail = message.params.exceptionDetails;
            errors.push({ message: detail.exception?.description || detail.text, url: detail.url || detail.stackTrace?.callFrames?.[0]?.url });
        }
    });
    const call = (method, params = {}) => new Promise((resolve, reject) => { const number = ++id; pending.set(number, { resolve, reject }); socket.send(JSON.stringify({ id: number, method, params })); });
    const evaluate = async expression => (await call('Runtime.evaluate', { expression, returnByValue: true, awaitPromise: true })).result.value;
    await call('Page.enable');
    await call('Runtime.enable');
    await call('Emulation.setDeviceMetricsOverride', { width: Number(process.argv[4] || 1440), height: 1000, deviceScaleFactor: 1, mobile: false });
    await call('Page.navigate', { url: process.argv[2] });
    await new Promise(resolve => setTimeout(resolve, 10000));
    if (process.argv.includes('--dismiss')) {
        await evaluate(`document.querySelector('.popup-modal-close, [data-notice-close]')?.click()`);
        await new Promise(resolve => setTimeout(resolve, 500));
    }
    const languageIndex = process.argv.indexOf('--language');
    if (languageIndex !== -1) {
        const language = process.argv[languageIndex + 1];
        if (!['en','hd'].includes(language)) throw new Error('Invalid test language');
        await evaluate(`(() => {const select = document.querySelector('[data-micada-language]'); select.value = ${JSON.stringify(language)}; select.dispatchEvent(new Event('change')); return true;})()`);
        await new Promise(resolve => setTimeout(resolve, 5000));
        const state = await evaluate(`({code: new URL(location.href).searchParams.get('lang'), saved: JSON.parse(localStorage.getItem('lang')), selected: document.querySelector('[data-micada-language]').value, heading: document.querySelector('.micada-heading').innerText})`);
        if (state.code !== language || state.selected !== language || state.saved.code !== language) throw new Error('Language persistence failed');
        console.log('LANGUAGE ' + JSON.stringify(state));
        if (process.argv.includes('--dismiss')) await evaluate(`document.querySelector('[data-notice-close]')?.click()`);
    }
    const evalIndex = process.argv.indexOf('--evaluate');
    let evaluation;
    if (evalIndex !== -1) {
        const check = await call('Runtime.evaluate', { expression: fs.readFileSync(process.argv[evalIndex + 1], 'utf8'), returnByValue: true, awaitPromise: true });
        if (check.exceptionDetails) throw new Error(JSON.stringify(check.exceptionDetails));
        evaluation = check.result.value;
        console.log('INTERACTIONS ' + JSON.stringify(evaluation));
    }
    const directory = path.join(__dirname, 'qa');
    fs.mkdirSync(directory, { recursive: true });
    const name = process.argv[3] || 'capture';
    await evaluate('window.scrollTo(0,0)');
    await new Promise(resolve => setTimeout(resolve, 500));
    const screenshot = await call('Page.captureScreenshot', { format: 'png', captureBeyondViewport: true });
    fs.writeFileSync(path.join(directory, name + '.png'), Buffer.from(screenshot.data, 'base64'));
    const result = await evaluate(`JSON.stringify({url:location.href,title:document.title,text:document.body.innerText,html:document.documentElement.outerHTML,images:[...document.images].map(i=>({src:i.src,loaded:i.complete&&i.naturalWidth>0})),width:innerWidth,scrollWidth:document.documentElement.scrollWidth})`);
    const parsed = JSON.parse(result);
    parsed.evaluation = evaluation;
    fs.writeFileSync(path.join(directory, name + '.json'), JSON.stringify(parsed, null, 2));
    console.log(JSON.stringify({ url: parsed.url, title: parsed.title, text: parsed.text.slice(0, 1800), imageCount: parsed.images.filter(image => image.src).length, width: parsed.width, scrollWidth: parsed.scrollWidth, errors }, null, 2));
    if (process.argv.includes('--close-browser')) await call('Browser.close');
    socket.close();
})().catch(error => { console.error(error); process.exit(1); });
