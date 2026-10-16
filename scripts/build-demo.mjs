// Builds the static, backend-free demo into dist-demo/ (deployable on GitHub Pages).
import { cp, mkdir, rm } from 'node:fs/promises';
import { fileURLToPath } from 'node:url';

const root = fileURLToPath(new URL('..', import.meta.url));
const out = `${root}dist-demo`;

await rm(out, { recursive: true, force: true });
await mkdir(out, { recursive: true });
for (const file of ['index.html', 'app.js', 'data.js', 'rules.js']) await cp(`${root}demo/${file}`, `${out}/${file}`);
await cp(`${root}public/css/app.css`, `${out}/app.css`);
console.log('Demo built in dist-demo/');
