var FtpDeploy = require('ftp-deploy');
var ftpDeploy = new FtpDeploy();



var config = {
    host : "82.29.171.223",
    user : "u242434967.hsmaster.ai",
    password : "R@8d4E6YE;:lH|lg",
    port: 21,
    localRoot: __dirname + '/build',
    remoteRoot: '/',
    include: ['*'],
    //exclude: ["*.png","*.jpeg", "*.jpg","*.svg", "*.ttf","*.json"],
    //exclude: [""],
    deleteRemote: false
}

ftpDeploy.deploy(config, function(err, res) {
    if (err) console.log(err)
    else console.log('finished:', res);
});

ftpDeploy.on("uploading", function(data) {
    data.totalFilesCount; 
    data.transferredFileCount;
    data.filename; 
});
ftpDeploy.on("uploaded", function(data) {
    console.log(data); 
});
ftpDeploy.on("log", function(data) {
    console.log(data);
});
ftpDeploy.on("upload-error", function(data) {
    console.log(data.err);
});