/**
 * Upload the free chapters setup script to the server
 */

const SftpClient = require('ssh2-sftp-client');
const config = require('../deploy.config.js');
const fs = require('fs');
const path = require('path');

async function uploadScript() {
    const sftp = new SftpClient();
    
    console.log('📤 Uploading free chapters setup script...');
    
    try {
        await sftp.connect({
            host: config.host,
            port: config.port,
            username: config.username,
            password: config.password
        });
        
        const localFile = path.join(__dirname, 'set-free-chapters.php');
        const remoteFile = '/www/set-free-chapters.php';
        
        await sftp.put(localFile, remoteFile);
        
        console.log('✅ Script uploaded!');
        console.log('');
        console.log('🔗 Run the script by visiting:');
        console.log('   https://dbankston.wordkeeper.net/set-free-chapters.php');
        console.log('');
        console.log('⚠️  Remember to delete the script after running!');
        
    } catch (err) {
        console.error('❌ Error:', err.message);
    } finally {
        await sftp.end();
    }
}

uploadScript();

