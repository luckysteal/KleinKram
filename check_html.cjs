const fs = require('fs');
const content = fs.readFileSync('resources/views/tools/schlossgraben-jump.blade.php', 'utf8');

// A more robust regex to find HTML attributes starting with x-, :, or @
// Format: x-something="expression" or x-something='expression'
const attrRegex = /(?:x-[\w.-]+|:[\w.-]+|@[\w.-]+)\s*=\s*(["'])([\s\S]*?)\1/g;

let match;
const expressions = [];

while ((match = attrRegex.exec(content)) !== null) {
    expressions.push({
        attr: match[0].split('=')[0].trim(),
        expr: match[2]
    });
}

console.log(`Found ${expressions.length} Alpine expressions. Verifying...`);

const vm = require('vm');
let errors = 0;

expressions.forEach((item, idx) => {
    const { attr, expr } = item;
    // Skip empty or simple literals
    if (!expr.trim()) return;
    
    // Replace Blade syntax like {{ csrf_token() }} or translation directives if any
    let jsExpr = expr;
    // Replace {{ ... }} with a dummy string
    jsExpr = jsExpr.replace(/\{\{[\s\S]*?\}\}/g, '"dummy"');
    
    // Skip simple event handlers that are just method calls or simple statements
    // but try to parse them if possible. For example @click="gameMode = 'continuous'"
    try {
        // We can wrap the expression in a function to check syntax
        new vm.Script(`(function() { ${jsExpr} })`);
        console.log(`[OK] ${attr}="${expr}"`);
    } catch (err) {
        console.error(`[ERROR] ${attr}="${expr}"`);
        console.error(err.message);
        errors++;
    }
});

if (errors === 0) {
    console.log('All Alpine expressions have valid JS syntax!');
} else {
    console.log(`Found ${errors} syntax errors in Alpine expressions.`);
}
