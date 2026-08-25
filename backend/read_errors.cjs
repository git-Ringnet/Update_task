const fs = require('fs');
const content = fs.readFileSync('d:/Update_task/backend/storage/logs/laravel.log', 'utf8');
const lines = content.split('\n');
let errorBlocks = [];
let currentBlock = [];

lines.forEach(line => {
    if (line.match(/^\[\d{4}-\d{2}-\d{2}/)) {
        if (currentBlock.length > 0) {
            errorBlocks.push(currentBlock);
        }
        currentBlock = [line];
    } else {
        currentBlock.push(line);
    }
});
if (currentBlock.length > 0) {
    errorBlocks.push(currentBlock);
}

const lastBlock = errorBlocks[errorBlocks.length - 1];
if (lastBlock) {
    console.log("=== EXACT ERROR MESSAGE ===");
    console.log(lastBlock.slice(0, 5).join('\n'));
} else {
    console.log("No errors found in log.");
}
