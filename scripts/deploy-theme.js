/**
 * Drew Bankston Theme Deployment Script (SFTP)
 * Deploys the theme folder to the remote WordPress installation via SSH/SFTP
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

const localPath = path.join(__dirname, '..', config.paths.theme.local);
const remotePath = config.paths.theme.remote;

async function deploy() {
    const sftp = new SftpClient();

    console.log('🚀 Drew Bankston Theme Deployment (SFTP)');
    console.log('========================================');
    console.log(`Local:  ${localPath}`);
    console.log(`Remote: ${remotePath}`);
    console.log('');

    try {
        console.log(`🔌 Connecting to ${config.host}:${config.port} via SFTP...`);
        await sftp.connect({
            host: config.host,
            port: config.port,
            username: config.username,
            password: config.password,
            // If using SSH key instead:
            // privateKey: config.privateKey
        });
        console.log('✅ Connected successfully!');
        console.log('');

        // Check if remote directory exists, create if not
        console.log('📁 Ensuring remote directory exists...');
        const exists = await sftp.exists(remotePath);
        if (!exists) {
            await sftp.mkdir(remotePath, true);
            console.log('   Created remote directory.');
        }

        // Upload directory
        console.log('📤 Uploading theme files...');
        console.log('');
        
        await sftp.uploadDir(localPath, remotePath);

        console.log('');
        console.log('✅ Theme deployed successfully!');
        console.log('');
        console.log('Next steps:');
        console.log('1. Log into WordPress admin');
        console.log('2. Go to Appearance > Themes');
        console.log('3. Activate the "Drew Bankston" theme');

    } catch (err) {
        console.error('❌ Deployment failed:', err.message);
        process.exit(1);
    } finally {
        await sftp.end();
    }
}

deploy();
