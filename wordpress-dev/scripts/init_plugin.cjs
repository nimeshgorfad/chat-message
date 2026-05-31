const fs = require('fs');
const path = require('path');

const pluginName = process.argv[2];
if (!pluginName) {
    console.error("Error: Please provide a plugin name (e.g., 'my-cool-plugin').");
    process.exit(1);
}

const slug = pluginName.toLowerCase().replace(/\s+/g, '-');
const pluginDir = path.join(process.cwd(), slug);

if (fs.existsSync(pluginDir)) {
    console.error(`Error: Directory '${slug}' already exists.`);
    process.exit(1);
}

fs.mkdirSync(pluginDir);

const pluginHeader = `<?php
/**
 * Plugin Name: ${pluginName}
 * Description: A brief description of the plugin.
 * Version:     1.0.0
 * Author:      Gemini CLI
 * Text Domain: ${slug}
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

// Start your plugin logic here.
`;

fs.writeFileSync(path.join(pluginDir, `${slug}.php`), pluginHeader);

console.log(`Success: Plugin '${pluginName}' scaffolded in directory '${slug}'.`);
console.log(`Main file: ${slug}/${slug}.php`);
