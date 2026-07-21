import fs from 'fs';
import path from 'path';
import vm from 'vm';
import { fileURLToPath } from 'url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);
const baseDir = path.resolve(__dirname, '..', '..');
const legacyDataDir = path.join(__dirname, 'legacy-react-data');
const webDataDir = path.join(baseDir, 'apps/web/src/data');
const apiDataDir = path.join(baseDir, 'apps/api/database/data');

if (!fs.existsSync(apiDataDir)) {
    fs.mkdirSync(apiDataDir, { recursive: true });
}

function sourceFile(fileName) {
    const legacyPath = path.join(legacyDataDir, fileName);
    if (fs.existsSync(legacyPath)) {
        return legacyPath;
    }

    const fallbackPath = path.join(webDataDir, fileName);
    if (fs.existsSync(fallbackPath)) {
        return fallbackPath;
    }

    throw new Error(`Missing legacy React data file: ${fileName}`);
}

// Helper to evaluate JS file content and extract exports
function evaluateFile(filePath, mocks = {}) {
    let content = fs.readFileSync(filePath, 'utf8');
    // Strip imports
    content = content.replace(/^import\s+[\s\S]*?;\s*$/gm, '');
    content = content.replace(/import\b/g, '// import');
    // Replace export statements with var so they attach to sandbox
    content = content.replace(/\bexport\s+const\b/g, 'var');
    content = content.replace(/\bexport\s+let\b/g, 'var');
    content = content.replace(/\bexport\s+default\b/g, 'var defaultExport =');

    const sandbox = {
        console,
        ...mocks
    };
    vm.createContext(sandbox);
    // Add lucide mock variables
    const lucideMocks = [
        'Laptop', 'Hammer', 'Building', 'TrendingUp', 'FlaskConical', 
        'Zap', 'Wrench', 'Shield', 'Coffee', 'Scissors', 'Leaf', 'Package', 
        'Gauge', 'Pickaxe', 'Factory', 'TreePine', 'Globe', 'Users', 'Award',
        'Activity', 'Broadcast', 'Easel', 'BoundingBox', 'Calendar4Week', 'ChatSquareText'
    ];
    lucideMocks.forEach(m => {
        sandbox[m] = m;
    });

    vm.runInContext(content, sandbox);
    return sandbox;
}

// Load all modules
console.log("Loading translations.js...");
const translationsModule = evaluateFile(sourceFile('translations.js'));
const translations = translationsModule.translations;

console.log("Loading programsData.js...");
const programsModule = evaluateFile(sourceFile('programsData.js'));
const programsData = programsModule.programsData;

console.log("Loading departmentsData.js...");
const departmentsModule = evaluateFile(sourceFile('departmentsData.js'));
const departmentsData = departmentsModule.departmentsData;

// Write files to JSON
fs.writeFileSync(path.join(apiDataDir, 'translations.json'), JSON.stringify(translations, null, 2));
fs.writeFileSync(path.join(apiDataDir, 'programs.json'), JSON.stringify(programsData, null, 2));
fs.writeFileSync(path.join(apiDataDir, 'departments.json'), JSON.stringify(departmentsData, null, 2));

console.log("Successfully extracted all React content files into JSON files!");
