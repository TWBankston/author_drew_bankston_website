/**
 * Upload the account page creation script to the server
 */

const SftpClient = require('ssh2-sftp-client');
const config = require('../deploy.config.js');
const path = require('path');

async function uploadScript() {
    const sftp = new SftpClient();
    
    console.log('📤 Uploading account page creation script...');
    
    try {
        await sftp.connect({
            host: config.host,
            port: config.port,
            username: config.username,
            password: config.password
        });
        
        const localFile = path.join(__dirname, 'create-account-page.php');
        const remoteFile = '/www/create-account-page.php';
        
        await sftp.put(localFile, remoteFile);
        
        console.log('✅ Script uploaded!');
        console.log('');
        console.log('🔗 Run the script by visiting:');
        console.log('   https://dbankston.wordkeeper.net/create-account-page.php');
        console.log('');
        console.log('⚠️  Remember to delete the script after running!');
        
    } catch (err) {
        console.error('❌ Error:', err.message);
    } finally {
        await sftp.end();
    }
}

uploadScript();

