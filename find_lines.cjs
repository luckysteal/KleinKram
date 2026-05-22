const fs = require('fs');
const lines = fs.readFileSync('rendered.html', 'utf8').split('\n');

lines.forEach((line, idx) => {
    const lineNum = idx + 1;
    if (line.includes('x-') || line.includes(':@') || /:[a-zA-Z]/.test(line) || line.includes('@click') || line.includes('@mousedown') || line.includes('@touchstart')) {
        console.log(`${lineNum}: ${line.trim()}`);
    }
});
