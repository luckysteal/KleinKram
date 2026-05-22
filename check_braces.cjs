const fs = require('fs');
const content = fs.readFileSync('rendered.html', 'utf8');

const scriptMatch = content.match(/<script>([\s\S]*?)<\/script>/);
if (!scriptMatch) {
    console.log('No script block found');
    process.exit(1);
}

const jsCode = scriptMatch[1];

let stack = [];
let line = 1;
let col = 1;
let insideString = null; // ' or " or `
let isComment = false;
let isMultilineComment = false;

for (let i = 0; i < jsCode.length; i++) {
    const char = jsCode[i];
    const nextChar = jsCode[i+1];
    
    if (char === '\n') {
        line++;
        col = 1;
        if (isComment) isComment = false;
        continue;
    } else {
        col++;
    }
    
    // Handle comments
    if (isComment) continue;
    if (isMultilineComment) {
        if (char === '*' && nextChar === '/') {
            isMultilineComment = false;
            i++;
        }
        continue;
    }
    
    if (char === '/' && nextChar === '/') {
        isComment = true;
        i++;
        continue;
    }
    if (char === '/' && nextChar === '*') {
        isMultilineComment = true;
        i++;
        continue;
    }
    
    // Handle strings
    if (insideString) {
        if (char === '\\') {
            i++; // skip escaped char
            continue;
        }
        if (char === insideString) {
            insideString = null;
        }
        continue;
    }
    
    if (char === "'" || char === '"' || char === '`') {
        insideString = char;
        continue;
    }
    
    // Handle brackets
    if (char === '(' || char === '{' || char === '[') {
        stack.push({ char, line, col });
    } else if (char === ')' || char === '}' || char === ']') {
        if (stack.length === 0) {
            console.log(`Extra closing bracket: '${char}' at line ${line}, col ${col}`);
            continue;
        }
        const last = stack.pop();
        const matches = (last.char === '(' && char === ')') ||
                        (last.char === '{' && char === '}') ||
                        (last.char === '[' && char === ']');
        if (!matches) {
            console.log(`Mismatched bracket: opened '${last.char}' at line ${last.line}, col ${last.col}; closed with '${char}' at line ${line}, col ${col}`);
        }
    }
}

if (stack.length > 0) {
    console.log(`Unclosed brackets/braces remaining:`);
    stack.forEach(item => {
        console.log(`  Opened '${item.char}' at line ${item.line}, col ${item.col}`);
    });
} else {
    console.log('All braces and parentheses match perfectly!');
}
