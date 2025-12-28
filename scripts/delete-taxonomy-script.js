/**
 * Delete the taxonomy fix script from server
 */

const SftpClient = require('ssh2-sftp-client');

let config;
try {
    config = require('../deploy.config.js');
} catch (err) {
    console.error('❌ Missing deploy.config.js');
    process.exit(1);
}

async function deleteScript() {
    const sftp = new SftpClient();
    
    try {
        await sftp.connect({
            host: config.host,
            port: config.port,
            username: config.username,
            password: config.password
        });
        
        await sftp.delete('/www/fix-book-taxonomies.php');
        console.log('✅ Script deleted from server!');
        
    } catch (err) {
        console.error('❌ Error:', err.message);
    } finally {
        await sftp.end();
    }
}

deleteScript();


