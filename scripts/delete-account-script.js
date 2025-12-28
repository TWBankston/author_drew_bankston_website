/**
 * Delete the account page script from the server
 */

const SftpClient = require('ssh2-sftp-client');
const config = require('../deploy.config.js');

async function deleteScript() {
    const sftp = new SftpClient();
    
    console.log('🗑️  Deleting account page script...');
    
    try {
        await sftp.connect({
            host: config.host,
            port: config.port,
            username: config.username,
            password: config.password
        });
        
        await sftp.delete('/www/create-account-page.php');
        
        console.log('✅ Script deleted!');
        
    } catch (err) {
        console.error('❌ Error:', err.message);
    } finally {
        await sftp.end();
    }
}

deleteScript();

