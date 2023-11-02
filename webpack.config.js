const {minify} = require('terser');
const fs = require('fs');
const path = require('path');

const jsSourceDirectory = 'js';
const filesToMinify = fs.readdirSync('js');
const distDirectory = 'dist';
const minifyConfig = {
    compress: false,
    mangle: false,
    module: true,
    sourceMap: false,
    output: {
        comments: 'some'
    }
};

if (!fs.existsSync(distDirectory)) {
    fs.mkdirSync(distDirectory);
}

async function minifyAndSaveFile(inputFilePath, outputFilePath) {
    const code = fs.readFileSync(inputFilePath, 'utf8');
    const minified = await minify(code, minifyConfig);
    fs.writeFileSync(outputFilePath, minified.code);
}

filesToMinify.forEach((file) => {
    const inputFilePath = path.join(jsSourceDirectory, file);
    const outputFilePath = path.join(distDirectory, file.replace('.js', '.min.js'));

    minifyAndSaveFile(inputFilePath, outputFilePath)
        .then(() => {
            console.log(`Minified and saved: ${file}`);
        })
        .catch((error) => {
            console.error(`Error minifying ${file}: ${error}`);
        });
});
