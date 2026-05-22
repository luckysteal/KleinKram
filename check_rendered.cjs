const fs = require('fs');
const content = fs.readFileSync('rendered.html', 'utf8');

// Find all script blocks
const scriptRegex = /<script\b[^>]*>([\s\S]*?)<\/script>/g;
let match;
let count = 0;

const vm = require('vm');

while ((match = scriptRegex.exec(content)) !== null) {
    count++;
    const jsCode = match[1];
    if (!jsCode.trim()) continue;
    
    // Check if the script contains Vite client code or similar
    try {
        new vm.Script(jsCode, { filename: `script_${count}.js` });
        console.log(`Script block #${count} is valid.`);
    } catch (err) {
        console.error(`Script block #${count} has a Syntax Error:`);
        console.error(err);
        
        // Print the lines around the error
        const lines = jsCode.split('\n');
        const errLine = err.stack.match(/script_\d+\.js:(\d+)/);
        if (errLine) {
            const lineNum = parseInt(errLine[1], 10);
            console.error(`Error line: ${lineNum}`);
            for (let i = Math.max(0, lineNum - 5); i < Math.min(lines.length, lineNum + 5); i++) {
                console.error(`${i + 1}: ${lines[i]}`);
            }
        }
    }
}
