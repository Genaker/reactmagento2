const path = require("path");
const CopyWebpackPlugin = require('copy-webpack-plugin');
const LiveReloadPlugin = require('webpack-livereload-plugin');

const liveReloadOptions = {

}

console.log('Dirrectory for compiling:');
console.log(path.join(__dirname, "/view/base/web/js/"));

module.exports = {
  entry: "./src/index.js",
  output: {
    path: path.join(__dirname, "view/base/web/js/"),
    filename: "index_bundle.js"
  },
  /*externals: {
    'React': 'React',
    'ReactDOM': 'ReactDOM',
    'ReactRouter': 'ReactRouter'
  },*/
  module: {
    rules: [
      {
        test: /\.js$/,
        exclude: /node_modules/,
        use: {
          loader: "babel-loader"
        },
      },
      {
        test: /\.css$/,
        use: ["style-loader", "css-loader"]
      }
    ]
  },
  ///var/www/html/magento
  ///var/www/html/magento/../../../../../../../../index_bundle.js
  plugins: [
    new LiveReloadPlugin(),
    new CopyWebpackPlugin([
        {
          from:path.join(__dirname, "/view/base/web/js/"),
          to:'../../../../../../../../pub/static/frontend/Curacao/curacao/en_US/Curacao_ReactTable/js/',
          force: true
        },
      {
        from:path.join(__dirname, "/view/base/web/js/"),
        to:'../../../../../../../../magento/pub/static/frontend/Curacao/curacao/en_US/Curacao_ReactTable/js/',
        force: true
      }
      ]),
]
};
