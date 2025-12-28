/**
 * Server Directory Explorer (SFTP)
 * Explores the remote server to find WordPress installation
 */

const SftpClient = require('ssh2-sftp-client');

// Load configuration
let config;
try {
    config = require('../deploy.config.js');
} catch (err) {
    console.error('❌ Missing deploy.config.js');
    process.exit(1);
}

async function explore() {
    const sftp = new SftpClient();
    
    console.log('🔍 Server Directory Explorer');
    console.log('============================');
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
        
        // Get current working directory
        const cwd = await sftp.cwd();
        console.log(`📁 Current working directory: ${cwd}\n`);
        
        // List root directory
        console.log('📂 Listing home directory:');
        const homeList = await sftp.list(cwd);
        for (const item of homeList) {
            const type = item.type === 'd' ? '📁' : '📄';
            console.log(`   ${type} ${item.name}`);
        }
        console.log('');
        
        // Try common WordPress paths
        const pathsToTry = [
            `${cwd}/public`,
            `${cwd}/public_html`,
            `${cwd}/www`,
            `${cwd}/htdocs`,
            '/var/www',
            '/var/www/html',
        ];
        
        for (const testPath of pathsToTry) {
            try {
                const exists = await sftp.exists(testPath);
                if (exists) {
                    console.log(`✅ Found: ${testPath}`);
                    const list = await sftp.list(testPath);
                    for (const item of list.slice(0, 10)) {
                        const type = item.type === 'd' ? '📁' : '📄';
                        console.log(`   ${type} ${item.name}`);
                    }
                    if (list.length > 10) {
                        console.log(`   ... and ${list.length - 10} more`);
                    }
                    console.log('');
                }
            } catch (e) {
                // Path doesn't exist or no permission
            }
        }
        
        // Try to find wp-content
        console.log('🔍 Looking for wp-content...');
        const wpPaths = [
            `${cwd}/public/wp-content`,
            `${cwd}/public_html/wp-content`,
            `${cwd}/www/wp-content`,
            `${cwd}/wp-content`,
        ];
        
        for (const wpPath of wpPaths) {
            try {
                const exists = await sftp.exists(wpPath);
                if (exists) {
                    console.log(`✅ Found WordPress at: ${wpPath}`);
                    const list = await sftp.list(wpPath);
                    for (const item of list) {
                        const type = item.type === 'd' ? '📁' : '📄';
                        console.log(`   ${type} ${item.name}`);
                    }
                    
                    // Check themes folder
                    const themesPath = `${wpPath}/themes`;
                    const themesExist = await sftp.exists(themesPath);
                    if (themesExist) {
                        console.log(`\n   📂 Themes in ${themesPath}:`);
                        const themes = await sftp.list(themesPath);
                        for (const theme of themes) {
                            console.log(`      📁 ${theme.name}`);
                        }
                    }
                    
                    // Check plugins folder
                    const pluginsPath = `${wpPath}/plugins`;
                    const pluginsExist = await sftp.exists(pluginsPath);
                    if (pluginsExist) {
                        console.log(`\n   📂 Plugins in ${pluginsPath}:`);
                        const plugins = await sftp.list(pluginsPath);
                        for (const plugin of plugins.slice(0, 15)) {
                            console.log(`      📁 ${plugin.name}`);
                        }
                        if (plugins.length > 15) {
                            console.log(`      ... and ${plugins.length - 15} more`);
                        }
                    }
                    
                    console.log('');
                }
            } catch (e) {
                // Path doesn't exist
            }
        }
        
    } catch (err) {
        console.error('❌ Error:', err.message);
        process.exit(1);
    } finally {
        await sftp.end();
    }
}

explore();


