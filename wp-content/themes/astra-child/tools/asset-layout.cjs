/** Shared output paths for the source asset importers. */
const path = require('path');
module.exports = relative => {
    const extension = path.extname(relative).toLowerCase();
    if (relative.startsWith('fonts/')) return relative;
    const folders = { '.css':'css', '.js':'js', '.json':'data', '.pdf':'documents', '.woff':'fonts', '.woff2':'fonts', '.ttf':'fonts', '.eot':'fonts' };
    const folder = folders[extension] || 'images';
    return folder + '/' + (['css','js','data','fonts'].includes(folder) ? path.basename(relative) : relative);
};
