/**
 * Delete the free chapters setup script from the server
 */

const SftpClient = require('ssh2-sftp-client');
const config = require('../deploy.config.js');

async function deleteScript() {
    const sftp = new SftpClient();
    
    console.log('🗑️  Deleting free chapters setup script...');
    
    try {
        await sftp.connect({
            host: config.host,
            port: config.port,
            username: config.username,
            password: config.password
        });
        
        await sftp.delete('/www/set-free-chapters.php');
        
        console.log('✅ Script deleted!');
        
    } catch (err) {
        console.error('❌ Error:', err.message);
    } finally {
        await sftp.end();
    }
}

deleteScript();

