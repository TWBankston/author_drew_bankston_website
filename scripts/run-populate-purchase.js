/**
 * Upload and run the populate purchase options script
 */

const SFTPClient = require('ssh2-sftp-client');
const fs = require('fs');
const path = require('path');

const config = {
    host: '172.233.152.148',
    port: 2222,
    username: 'wordkeeper',
    privateKey: fs.readFileSync(path.join(process.env.USERPROFILE || process.env.HOME, '.ssh', 'id_rsa'))
};

const localScript = path.join(__dirname, 'populate-purchase-options.php');
const remoteScript = '/www/wp-content/themes/drew-bankston/populate-purchase.php';

async function run() {
    const sftp = new SFTPClient();
    
    console.log('📤 Uploading populate script...');
    
    try {
        await sftp.connect(config);
        await sftp.put(localScript, remoteScript);
        console.log('✅ Script uploaded to:', remoteScript);
        console.log('\n📋 To run the script:');
        console.log('   1. SSH into the server');
        console.log('   2. Run: cd /www && wp eval-file wp-content/themes/drew-bankston/populate-purchase.php');
        console.log('\n   Or access via browser (then delete):');
        console.log('   https://dbankston.wordkeeper.net/wp-content/themes/drew-bankston/populate-purchase.php');
        await sftp.end();
    } catch (err) {
        console.error('❌ Error:', err.message);
        await sftp.end();
        process.exit(1);
    }
}

run();

