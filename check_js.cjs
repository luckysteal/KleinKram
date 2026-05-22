const fs = require('fs');
const content = fs.readFileSync('resources/views/tools/schlossgraben-jump.blade.php', 'utf8');

// Find the script block
const scriptMatch = content.match(/<script>([\s\S]*?)<\/script>/);
if (!scriptMatch) {
    console.log('No script block found');
    process.exit(1);
}

let jsCode = scriptMatch[1];

// Replace PHP Blade syntax like @json($names) with dummy JS so that it parses
jsCode = jsCode.replace(/@json\([^)]*\)/g, '[]');
jsCode = jsCode.replace(/\{\{[\s\S]*?\}\}/g, '"dummy_value"');

// Save the jsCode to a temporary file
fs.writeFileSync('temp_check.cjs', jsCode);
console.log('Saved JS code to temp_check.cjs. Now verifying syntax...');

try {
    // Attempt to parse the JS code using the built-in VM module
    const vm = require('vm');
    new vm.Script(jsCode);
    console.log('JS syntax is valid!');
} catch (err) {
    console.error('JS Syntax Error found:');
    console.error(err);
}
