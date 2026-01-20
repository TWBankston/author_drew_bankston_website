/**
 * Hostinger File Upload Script
 * Uploads theme, plugin, and media files to Hostinger server
 * 
 * Usage:
 *   $env:HOSTINGER_PASSWORD='your-password'; node scripts/migrate-to-hostinger.js
 */

const SftpClient = require('ssh2-sftp-client');
const path = require('path');
const fs = require('fs');

// Hostinger SFTP configuration
const hostinger = {
    host: '46.202.197.54',
    port: 65002,
    username: 'u666117388',
    password: process.env.HOSTINGER_PASSWORD || 'LiveHonestly25$'
};

const hostingerBasePath = '/home/u666117388/domains/drewbankston.com/public_html';
const localThemePath = path.join(__dirname, '..', 'theme', 'drew-bankston');
const localPluginPath = path.join(__dirname, '..', 'plugins', 'drew-bankston-custom');
const tempUploadsPath = path.join(__dirname, '..', 'temp-uploads');

async function uploadToHostinger() {
    const sftp = new SftpClient();

    console.log('🚀 Uploading to Hostinger');
    console.log('==========================');
    console.log('');

    try {
        // Connect to Hostinger
        console.log('🔌 Connecting to Hostinger...');
        await sftp.connect(hostinger);
        console.log('✅ Connected to Hostinger!');

        // ===== UPLOAD THEME =====
        console.log('\n📦 Uploading drew-bankston theme...');
        const themeRemotePath = hostingerBasePath + '/wp-content/themes/drew-bankston';
        
        try {
            await sftp.rmdir(themeRemotePath, true);
        } catch (e) {
            // Directory might not exist
        }
        
        await sftp.mkdir(themeRemotePath, true);
        await sftp.uploadDir(localThemePath, themeRemotePath);
        console.log('✅ Theme uploaded!');

        // ===== UPLOAD PLUGIN =====
        console.log('\n📦 Uploading drew-bankston-custom plugin...');
        const pluginRemotePath = hostingerBasePath + '/wp-content/plugins/drew-bankston-custom';
        
        try {
            await sftp.rmdir(pluginRemotePath, true);
        } catch (e) {
            // Directory might not exist
        }
        
        await sftp.mkdir(pluginRemotePath, true);
        await sftp.uploadDir(localPluginPath, pluginRemotePath);
        console.log('✅ Plugin uploaded!');

        // ===== UPLOAD MEDIA (if exists locally) =====
        if (fs.existsSync(tempUploadsPath)) {
            console.log('\n📤 Uploading media to Hostinger...');
            const hostingerUploadsPath = hostingerBasePath + '/wp-content/uploads';
            
            // Walk through temp uploads and upload to Hostinger
            const uploadYears = fs.readdirSync(tempUploadsPath);
            for (const year of uploadYears) {
                const yearPath = path.join(tempUploadsPath, year);
                if (fs.statSync(yearPath).isDirectory()) {
                    const months = fs.readdirSync(yearPath);
                    
                    for (const month of months) {
                        const monthPath = path.join(yearPath, month);
                        if (fs.statSync(monthPath).isDirectory()) {
                            const remoteMonthPath = `${hostingerUploadsPath}/${year}/${month}`;
                            
                            // Create directory on Hostinger
                            await sftp.mkdir(remoteMonthPath, true);
                            
                            // Upload files
                            const files = fs.readdirSync(monthPath);
                            console.log(`  📤 ${year}/${month}: ${files.length} files`);
                            
                            for (const file of files) {
                                const localFile = path.join(monthPath, file);
                                const remoteFile = `${remoteMonthPath}/${file}`;
                                await sftp.fastPut(localFile, remoteFile);
                            }
                        }
                    }
                }
            }
            console.log('✅ Media uploaded to Hostinger!');
        } else {
            console.log('\n⚠️  No temp-uploads directory found, skipping media upload');
        }

        await sftp.end();

        console.log('\n🎉 Upload Complete!');
        console.log('===================');
        console.log('✅ Theme: drew-bankston');
        console.log('✅ Plugin: drew-bankston-custom');
        console.log('✅ Media: ' + (fs.existsSync(tempUploadsPath) ? 'Uploaded' : 'Skipped'));
        console.log('');
        console.log('🌐 Site URL: https://honeydew-caribou-244132.hostingersite.com');

    } catch (err) {
        console.error('❌ Upload failed:', err.message);
        console.error(err.stack);
        process.exit(1);
    } finally {
        try { await sftp.end(); } catch (e) {}
    }
}

uploadToHostinger();
