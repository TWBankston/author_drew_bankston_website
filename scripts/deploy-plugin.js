/**
 * Drew Bankston Plugin Deployment Script (SFTP)
 * Deploys the custom plugin folder to the remote WordPress installation via SFTP
 */

const SftpClient = require('ssh2-sftp-client');
const path = require('path');
const fs = require('fs');

// Load configuration from external file
let config;
try {
    config = require('../deploy.config.js');
} catch (err) {
    console.error('❌ Missing deploy.config.js');
    console.error('   Copy deploy.config.example.js to deploy.config.js and add your credentials.');
    process.exit(1);
}

const localPath = path.join(__dirname, '..', config.paths.plugin.local);
const remotePath = config.paths.plugin.remote;

async function deploy() {
    const sftp = new SftpClient();

    console.log('🚀 Drew Bankston Plugin Deployment (SFTP)');
    console.log('=========================================');
    console.log(`Local:  ${localPath}`);
    console.log(`Remote: ${remotePath}`);
    console.log('');

    try {
        console.log(`🔌 Connecting to ${config.host}:${config.port} via SFTP...`);
        await sftp.connect({
            host: config.host,
            port: config.port,
            username: config.username,
            password: config.password
        });
        console.log('✅ Connected successfully!');
        console.log('');

        // Clean deployment - remove old files first
        console.log('🗑️  Removing old plugin files...');
        try {
            await sftp.rmdir(remotePath, true);
            console.log('✅ Old files removed.');
        } catch (e) {
            console.log('📝 No existing files to remove (or first deploy).');
        }
        
        // Ensure remote directory exists
        console.log('📁 Creating remote directory...');
        await sftp.mkdir(remotePath, true);

        // Upload directory
        console.log('📤 Uploading plugin files...');
        console.log('');
        
        await sftp.uploadDir(localPath, remotePath);

        console.log('');
        console.log('✅ Plugin deployed successfully!');
        console.log('');
        console.log('Site: https://dbankston.wordkeeper.net/');

    } catch (err) {
        console.error('❌ Deployment failed:', err.message);
        process.exit(1);
    } finally {
        await sftp.end();
    }
}

deploy();
