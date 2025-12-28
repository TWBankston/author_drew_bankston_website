/**
 * Deployment Configuration Template
 * Copy this file to deploy.config.js and fill in your credentials.
 * 
 * IMPORTANT: deploy.config.js is gitignored and will not be committed.
 */

module.exports = {
    host: 'your-server-ip-or-hostname',
    port: 22,  // SSH/SFTP port
    username: 'your-sftp-username',
    password: 'your-sftp-password',
    
    // Alternative: Use SSH key authentication (more secure)
    // privateKey: require('fs').readFileSync('/path/to/your/private/key'),
    
    // Paths for WordPress installation
    paths: {
        theme: {
            local: 'theme/drew-bankston',
            remote: '/public_html/wp-content/themes/drew-bankston'
        },
        plugin: {
            local: 'plugins/drew-bankston-custom',
            remote: '/public_html/wp-content/plugins/drew-bankston-custom'
        }
    }
};


