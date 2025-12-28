/**
 * Upload and run the taxonomy fix script
 */

const SftpClient = require('ssh2-sftp-client');
const path = require('path');
const fs = require('fs');

// Load configuration
let config;
try {
    config = require('../deploy.config.js');
} catch (err) {
    console.error('❌ Missing deploy.config.js');
    process.exit(1);
}

async function uploadScript() {
    const sftp = new SftpClient();
    
    console.log('📤 Uploading Taxonomy Fix Script');
    console.log('=================================');
    
    try {
        await sftp.connect({
            host: config.host,
            port: config.port,
            username: config.username,
            password: config.password
        });
        console.log('✅ Connected!\n');
        
        const localFile = path.join(__dirname, 'fix-book-taxonomies.php');
        const remoteFile = '/www/fix-book-taxonomies.php';
        
        console.log(`Uploading: ${localFile}`);
        console.log(`To: ${remoteFile}\n`);
        
        await sftp.put(localFile, remoteFile);
        
        console.log('✅ Script uploaded!\n');
        console.log('Now run it by visiting:');
        console.log('https://dbankston.wordkeeper.net/fix-book-taxonomies.php');
        console.log('\n⚠️  DELETE the script after running it for security!');
        
    } catch (err) {
        console.error('❌ Error:', err.message);
        process.exit(1);
    } finally {
        await sftp.end();
    }
}

uploadScript();


