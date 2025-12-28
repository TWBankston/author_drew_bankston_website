/**
 * File Comparison Script (SFTP)
 * Compares local theme/plugin files with what's on the server
 */

const SftpClient = require('ssh2-sftp-client');
const path = require('path');
const fs = require('fs');
const crypto = require('crypto');

// Load configuration
let config;
try {
    config = require('../deploy.config.js');
} catch (err) {
    console.error('❌ Missing deploy.config.js');
    process.exit(1);
}

// Calculate MD5 hash of file content
function getHash(content) {
    if (Buffer.isBuffer(content)) {
        return crypto.createHash('md5').update(content).digest('hex');
    }
    return crypto.createHash('md5').update(content, 'utf8').digest('hex');
}

// Get all files in a directory recursively (local)
function getLocalFiles(dir, baseDir = dir) {
    const files = [];
    
    if (!fs.existsSync(dir)) {
        return files;
    }
    
    const items = fs.readdirSync(dir);
    
    for (const item of items) {
        const fullPath = path.join(dir, item);
        const relativePath = path.relative(baseDir, fullPath).replace(/\\/g, '/');
        const stat = fs.statSync(fullPath);
        
        if (stat.isDirectory()) {
            files.push(...getLocalFiles(fullPath, baseDir));
        } else {
            const content = fs.readFileSync(fullPath);
            files.push({
                path: relativePath,
                size: stat.size,
                hash: getHash(content)
            });
        }
    }
    
    return files;
}

// Get all files in a directory recursively (remote via SFTP)
async function getRemoteFiles(sftp, dir, baseDir = dir) {
    const files = [];
    
    try {
        const list = await sftp.list(dir);
        
        for (const item of list) {
            const fullPath = `${dir}/${item.name}`;
            const relativePath = fullPath.replace(baseDir + '/', '');
            
            if (item.type === 'd') {
                files.push(...await getRemoteFiles(sftp, fullPath, baseDir));
            } else {
                try {
                    const content = await sftp.get(fullPath);
                    files.push({
                        path: relativePath,
                        size: item.size,
                        hash: getHash(content)
                    });
                } catch (e) {
                    console.log(`  ⚠️  Could not read: ${relativePath} - ${e.message}`);
                }
            }
        }
    } catch (e) {
        console.log(`  ⚠️  Could not list: ${dir} - ${e.message}`);
    }
    
    return files;
}

async function compare() {
    const sftp = new SftpClient();
    
    console.log('🔍 File Comparison Tool (Local vs Server)');
    console.log('==========================================');
    console.log('');
    
    try {
        console.log(`🔌 Connecting to ${config.host}:${config.port} via SFTP...`);
        await sftp.connect({
            host: config.host,
            port: config.port,
            username: config.username,
            password: config.password
        });
        console.log('✅ Connected!\n');
        
        // Compare Theme
        console.log('📂 THEME COMPARISON');
        console.log('-------------------');
        const localThemePath = path.join(__dirname, '..', config.paths.theme.local);
        const remoteThemePath = config.paths.theme.remote;
        
        console.log(`Local:  ${localThemePath}`);
        console.log(`Remote: ${remoteThemePath}\n`);
        
        console.log('  Loading local theme files...');
        const localThemeFiles = getLocalFiles(localThemePath);
        console.log(`  Found ${localThemeFiles.length} local files.`);
        
        console.log('  Loading remote theme files (this may take a moment)...');
        const remoteThemeFiles = await getRemoteFiles(sftp, remoteThemePath);
        console.log(`  Found ${remoteThemeFiles.length} remote files.\n`);
        
        // Create maps for easy lookup
        const localThemeMap = new Map(localThemeFiles.map(f => [f.path, f]));
        const remoteThemeMap = new Map(remoteThemeFiles.map(f => [f.path, f]));
        
        // Find differences
        const themeDifferences = [];
        const themeMissing = [];
        const themeExtra = [];
        
        for (const [filePath, local] of localThemeMap) {
            const remote = remoteThemeMap.get(filePath);
            if (!remote) {
                themeMissing.push(filePath);
            } else if (local.hash !== remote.hash) {
                themeDifferences.push({
                    path: filePath,
                    localSize: local.size,
                    remoteSize: remote.size
                });
            }
        }
        
        for (const [filePath] of remoteThemeMap) {
            if (!localThemeMap.has(filePath)) {
                themeExtra.push(filePath);
            }
        }
        
        if (themeDifferences.length === 0 && themeMissing.length === 0 && themeExtra.length === 0) {
            console.log('  ✅ Theme files are IN SYNC!\n');
        } else {
            if (themeDifferences.length > 0) {
                console.log(`  ❌ DIFFERENT (${themeDifferences.length} files):`);
                for (const diff of themeDifferences) {
                    console.log(`     - ${diff.path} (local: ${diff.localSize}b, remote: ${diff.remoteSize}b)`);
                }
                console.log('');
            }
            
            if (themeMissing.length > 0) {
                console.log(`  ⚠️  MISSING ON SERVER (${themeMissing.length} files):`);
                for (const p of themeMissing) {
                    console.log(`     - ${p}`);
                }
                console.log('');
            }
            
            if (themeExtra.length > 0) {
                console.log(`  📝 EXTRA ON SERVER (${themeExtra.length} files):`);
                for (const p of themeExtra) {
                    console.log(`     - ${p}`);
                }
                console.log('');
            }
        }
        
        // Compare Plugin
        console.log('📂 PLUGIN COMPARISON');
        console.log('--------------------');
        const localPluginPath = path.join(__dirname, '..', config.paths.plugin.local);
        const remotePluginPath = config.paths.plugin.remote;
        
        console.log(`Local:  ${localPluginPath}`);
        console.log(`Remote: ${remotePluginPath}\n`);
        
        console.log('  Loading local plugin files...');
        const localPluginFiles = getLocalFiles(localPluginPath);
        console.log(`  Found ${localPluginFiles.length} local files.`);
        
        console.log('  Loading remote plugin files...');
        const remotePluginFiles = await getRemoteFiles(sftp, remotePluginPath);
        console.log(`  Found ${remotePluginFiles.length} remote files.\n`);
        
        // Create maps for plugin
        const localPluginMap = new Map(localPluginFiles.map(f => [f.path, f]));
        const remotePluginMap = new Map(remotePluginFiles.map(f => [f.path, f]));
        
        // Find plugin differences
        const pluginDifferences = [];
        const pluginMissing = [];
        const pluginExtra = [];
        
        for (const [filePath, local] of localPluginMap) {
            const remote = remotePluginMap.get(filePath);
            if (!remote) {
                pluginMissing.push(filePath);
            } else if (local.hash !== remote.hash) {
                pluginDifferences.push({
                    path: filePath,
                    localSize: local.size,
                    remoteSize: remote.size
                });
            }
        }
        
        for (const [filePath] of remotePluginMap) {
            if (!localPluginMap.has(filePath)) {
                pluginExtra.push(filePath);
            }
        }
        
        if (pluginDifferences.length === 0 && pluginMissing.length === 0 && pluginExtra.length === 0) {
            console.log('  ✅ Plugin files are IN SYNC!\n');
        } else {
            if (pluginDifferences.length > 0) {
                console.log(`  ❌ DIFFERENT (${pluginDifferences.length} files):`);
                for (const diff of pluginDifferences) {
                    console.log(`     - ${diff.path} (local: ${diff.localSize}b, remote: ${diff.remoteSize}b)`);
                }
                console.log('');
            }
            
            if (pluginMissing.length > 0) {
                console.log(`  ⚠️  MISSING ON SERVER (${pluginMissing.length} files):`);
                for (const p of pluginMissing) {
                    console.log(`     - ${p}`);
                }
                console.log('');
            }
            
            if (pluginExtra.length > 0) {
                console.log(`  📝 EXTRA ON SERVER (${pluginExtra.length} files):`);
                for (const p of pluginExtra) {
                    console.log(`     - ${p}`);
                }
                console.log('');
            }
        }
        
        console.log('==========================================');
        console.log('Comparison complete!');
        
    } catch (err) {
        console.error('❌ Error:', err.message);
        process.exit(1);
    } finally {
        await sftp.end();
    }
}

compare();
