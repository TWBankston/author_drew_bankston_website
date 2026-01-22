/**
 * Upload Drew Bankston Analytics Plugin to Hostinger
 * 
 * Usage:
 *   node scripts/upload-analytics-plugin.js
 */

const SftpClient = require('ssh2-sftp-client');
const path = require('path');

// Hostinger SFTP configuration
const hostinger = {
    host: '46.202.197.54',
    port: 65002,
    username: 'u666117388',
    password: process.env.HOSTINGER_PASSWORD || 'LiveHonestly25$'
};

const hostingerBasePath = '/home/u666117388/domains/drewbankston.com/public_html';
const localPluginPath = path.join(__dirname, '..', 'plugins', 'drew-bankston-analytics');

async function uploadPlugin() {
    const sftp = new SftpClient();

    console.log('🚀 Uploading Drew Bankston Analytics Plugin');
    console.log('============================================');
    console.log('');

    try {
        // Connect to Hostinger
        console.log('🔌 Connecting to Hostinger...');
        await sftp.connect(hostinger);
        console.log('✅ Connected to Hostinger!');

        // Upload analytics plugin
        console.log('\n📦 Uploading drew-bankston-analytics plugin...');
        const pluginRemotePath = hostingerBasePath + '/wp-content/plugins/drew-bankston-analytics';
        
        try {
            await sftp.rmdir(pluginRemotePath, true);
            console.log('   Removed existing plugin directory');
        } catch (e) {
            // Directory might not exist
        }
        
        await sftp.mkdir(pluginRemotePath, true);
        await sftp.uploadDir(localPluginPath, pluginRemotePath);
        console.log('✅ Analytics plugin uploaded!');

        await sftp.end();

        console.log('\n🎉 Upload Complete!');
        console.log('===================');
        console.log('');
        console.log('📋 Next Steps:');
        console.log('   1. Go to: https://drewbankston.com/wp-admin/plugins.php');
        console.log('   2. Find "Drew Bankston Analytics" and click "Activate"');
        console.log('   3. Go to Settings → Analytics to configure');
        console.log('');

    } catch (err) {
        console.error('❌ Upload failed:', err.message);
        console.error(err.stack);
        process.exit(1);
    } finally {
        try { await sftp.end(); } catch (e) {}
    }
}

uploadPlugin();
