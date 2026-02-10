The React-Luma Module is a Faster and Free Open-Source Magento 2 Luma or another Theme Optimiser and Hyva Theme Alternative. Actually, you don’t even need to change the Theme. It works as a composer module to improve your existing theme without re-platforming to Hyva, or it can be used to improve Hyva.

React-Luma is 20% faster than any M2 front-end, including Hyva, today!

100% Vanilla.JS(no framework was used to improve performance) and default magento CSS without LESS compilation. However, it can be easily extended by any JS (ReactJS, VueJS) or CSS(Tilewind) library of your choice.

<img width="781" alt="image" src="https://github.com/user-attachments/assets/adad25f3-d394-4b00-8661-52e3165f6af7" />

## 📚 Documentation

- **[Architecture Documentation](ARCHITECTURE.md)** - Detailed architecture, design patterns, and core components
- **[Deployment Guide](DEPLOYMENT.md)** - Installation, configuration, testing, and troubleshooting
- **[Developer Guide](DEVELOPER_GUIDE.md)** - Extending the module, best practices, and development workflow
- **[CSS Purge Tool](PURGE_README.md)** - CSS optimization and purging documentation

## ✨ Key Features

- **🚀 Performance First**: 20% faster than standard Magento 2 and Hyva
- **🔒 Security Hardened**: Input validation, CSRF protection, safe HTML processing
- **🎨 No Theme Changes Required**: Works with any existing Magento 2 theme
- **🏗️ Clean Architecture**: Service-oriented design with dependency injection
- **🧪 Fully Tested**: Comprehensive unit test coverage
- **📦 Easy Installation**: Simple Composer installation
- **⚙️ Configurable**: Extensive configuration options via admin panel or CLI
- **🔧 Developer Friendly**: Well-documented, extensible, follows Magento best practices

## CSS Deployment Guide

**Known issue**: The CSS files are not loading correctly on the frontend. The layout is broken due to missing or non-deployed static CSS assets.
It is because not deployed React-Luma optimised CSS. 
If specific CSS files from the extension are missing, manually copy them:
‘’’
cp -R {path to extension}/pub/static/* pub/static/
‘’’

## SCSS to CSS Compilation Manual
To compile SCSS files in React-Luma, navigate to the module directory (vendor/genaker/magento-reactjs) and run node css-compile.js. This script automatically finds all .scss files in pub/static/, compiles them to minified CSS using Sass and PostCSS with autoprefixer and cssnano, and outputs detailed statistics including file sizes and selector counts. The compiled .min.css files are saved in the same directory, ready for production use. Ensure Node.js is installed and run npm install first to install dependencies (sass, postcss, autoprefixer, cssnano, glob).

## CSS Purge Tool
The project includes a powerful CSS purging tool that removes unused CSS selectors to optimize file sizes and improve performance. The tool uses PurgeCSS with support for URL fetching, local content scanning, and advanced configuration options including ignore patterns and blocklists. To use the CSS purge tool, navigate to `vendor/genaker/magento-reactjs/` and run `node css-purge.js --css path/to/your/file.css`. The tool supports file-specific configurations via `purge.json`, can fetch content from URLs using `--url` parameters, scan local files with `--path` parameters, and apply custom configurations with `--config`. It automatically removes development classes (`.debug-*`, `.test-*`) and deprecated code (`.deprecated-*`, `.old-*`) while preserving critical selectors through safelists. For detailed usage instructions and examples, see the comprehensive documentation in `vendor/genaker/magento-reactjs/PURGE_README.md`.

# Video overview of the React-Luma

Video is here -> https://www.youtube.com/watch?v=TJPNAgDCkWk

# Have you questions about integrating ReactJS or VueJS with Magento 2 frontend? This Magento module will help you
React Magento 2 implementation. This module explains how to add and use ReactJS or any other framework micro-frontend UI Components with Magento 2 and forget about Knockout/JQuery Magento 2 UI without migration to a new theme(Works with existing theme and designs). Checkout, admin, customer account, and any other part of your store can work using legacy Magento 2 JS implementation

# No Magento CSS and Magento JS
Enable only CSS Junk settings and 
```
php bin/magento config:set dev/js/move_script_to_bottom 1
```

![React + Magento 2](https://github.com/Genaker/reactmagento2/blob/master/KnockoutMagento2React.png)

## JavaScript Optimization Configuration

To optimize JavaScript performance with existing Magento JS no JS changes needed!!, configure the following settings:

**Important:** This module uses native Magento JavaScript methods and APIs. No custom JavaScript modifications are required - all optimizations work with Magento's existing JavaScript implementation out of the box. The module leverages Magento's built-in JavaScript functionality (`requirejs`, `mage/requirejs/mixins.js`, `requirejs-config.js`, etc.) without replacing or modifying core Magento JS files. The module leverages Magento's built-in JavaScript functionality (`requirejs`, `mage/requirejs/mixins.js`, `requirejs-config.js`, etc.) without replacing or modifying core Magento JS files.

### 1. Move JS to Bottom (Defer JS)
Enable the module's "Defer JS" feature to move JavaScript files to the bottom of the page for better performance. Scripts with `no-defer` attribute are preserved in their original position.

**Via Admin Panel:**
- Go to: Stores > Configuration > React-Luma integration > Configuration > Magento JS configuration
- Set "Defer JS (Move Scripts to Bottom)" to "Yes"
- Save config and clear cache

**Via CLI:**
```bash
php bin/magento config:set react_vue_config/junk/defer_js 1
php bin/magento cache:clean
```

**Via GET Parameter (for testing):**
- Add `?defer-js=true` to any URL to enable temporarily
- Add `?defer-js=false` to disable temporarily

### 2. Disable JavaScript Bundling (!Important)
**Important:** Disable Magento's JavaScript bundling to prevent conflicts and ensure proper script loading order.

**Why disable bundling?**
Magento's JavaScript bundling or any other MAgento bundlings creates large combined JavaScript files that block page rendering and increase blocking time. When bundling is enabled:
- Multiple JavaScript files are combined into large bundles that must be downloaded and parsed before the page can become interactive
- This increases **Total Blocking Time (TBT)** and delays **Time to Interactive (TTI)**
- Large bundles prevent parallel script loading and caching optimization
- Scripts that could load asynchronously are forced to load synchronously in the bundle
- Individual script updates require re-downloading the entire bundle

By disabling bundling:
- Scripts can load in parallel, reducing blocking time
- Individual scripts can be cached independently
- Smaller files load faster and parse quicker
- Better browser caching - only changed scripts need to be re-downloaded
- Improved performance metrics (LCP, FCP, TBT, TTI)

**Note:** Never use Magento JavaScript bundling - it doesn't have a positive influence on Core Web Vitals and actually degrades performance metrics.

**Via CLI:**
```bash
php bin/magento config:set dev/js/enable_js_bundling 0
php bin/magento cache:clean
```

**Via Admin Panel:**
- Go to: Stores > Configuration > Advanced > Developer > JavaScript Settings
- Set "Enable JavaScript Bundling" to "No"
- Save config and clear cache

### 3. Enable JavaScript Minification
Enable JavaScript minification to reduce file sizes and improve page load times.

**Via CLI:**
```bash
php bin/magento config:set dev/js/minify_files 1
php bin/magento cache:clean
```

**Via Admin Panel:**
- Go to: Stores > Configuration > Advanced > Developer > JavaScript Settings
- Set "Minify JavaScript Files" to "Yes"
- Save config and clear cache

**Note:** In production mode, the Developer section may be hidden in the Admin Panel. Use CLI commands instead.

### Complete JavaScript Optimization Setup
Run all three commands together for optimal JavaScript performance:
```bash
php bin/magento config:set react_vue_config/junk/defer_js 1
php bin/magento config:set dev/js/enable_js_bundling 0
php bin/magento config:set dev/js/minify_files 1
php bin/magento cache:clean
```

# Updates:
- VueJS implementation 
- Magento Configuration enable React, VueJS
- Remove Magento's default JS Junk (Require, Knockout, jQuery) configuration. You will need to implement the required functionality or use Magento Open Source ReactJS Luma Theme
- CSS optimizer feature added. Add optimized CSS files to pub/static/styles-(l|m).css and replace the default Magento one 
- Magento ReactJS Luma theme released
- React-Luma module. Just install the module and optimize the theme

# Magento Blazing-Fast React Luma Theme Implementation:

<img src="https://raw.githubusercontent.com/Genaker/Luma-React-PWA-Magento-Theme/master/web/images/logo.jpeg" alt="magento react theme"/>
<br>
Repo is here: https://github.com/Genaker/Luma-React-PWA-Magento-Theme

# About the Magento 2 React implementation module 
You can add any modern library to extend the magento feature. React-Luma has basic magento features implemented using native JS without dependencie.s 
It is not a PWA or headless implementation, which is impossible to use with an existing website. Also, Single Page Application (SPA) PWA Magento 2 implementations have issues with Magento 2 API performance - too slow. This implementation is High-Performance integration with magento2 (with Magento 1 also easy to use) it uses inline JSON directly from the page. The same approach is used in Magento 2 backend and frontend checkout, color swatches by default. Also can use Ajax HTTP call to fetch data (not the best solution Magento API is slow and will increase the load on your backend server). You can also use my future project, "Microservices Magento," to fetch data.


# CSS improvements

You can also replace Magento CSS with your custom CSS files if you put them compiled into pub/static
```
$optimisedCSSFilePath = BP . '/pub/static/styles-m.css';
```

## Store specific styles

The module supports store-specific CSS files for multi-store setups. By default, optimized CSS files are located in `/pub/static/` for the default store. For other stores, CSS files should be placed in `/pub/static/{store_code}/` directory. For example:
- Default store: `/pub/static/product-styles-m.css`
- French store (`fr`): `/pub/static/fr/product-styles-m.css`
- German store (`de`): `/pub/static/de/product-styles-m.css`

The module automatically detects the current store code and loads the appropriate CSS files. If store-specific files don't exist, it falls back to the default store files. This allows you to customize styles per store while maintaining a fallback mechanism.

# VueJS support 

![Logo-Vuejs](https://user-images.githubusercontent.com/9213670/150036919-3486e016-3d37-4ffd-b4ee-a3a3bbc961e9.png)

VueJS is a progressive framework for building user interfaces. Unlike Magento 2 UI monolithic component, Vue is designed from the ground up to be incrementally adoptable. The core library is focused on the view layer only, and is easy to pick up and integrate with other libraries or existing projects. 

## JS libraries footprint: 
* ReactJS 17 - 11KB
* ReactDOM - 120KB
* Preact - 12KB
* HTML for Preact - 1KB
* RequireJS - 17KB
* VueJS - 94KB
* KnockoutJS - 67KB
* jQuery - 89Kb
* AlpineJS - 34KB

## My thougts About VUE.js PWA and Magento 2

Read this article: https://blog.vuestorefront.io/yehor-shytikov-pwa-is-a-real-revolution-not-only-in-ecommerce/

Magento 2  has a better framework optimization from a development perspective. It allows developers to use the dependency injection, plugin system (**which is considered harmful AOP software development principle** read: https://yegorshytikov.medium.com/magento-2-plug-ins-aod-architecture-are-harmful-dc23c4edb534), and XML notations for layout. I personally like the folder structure in which one directory is one module. Magento 1 was messy since one module contains several folders.

Magento 2 uses a modern Symphony approach but it still has a lot of legacy code. Even though it introduces a new way of embracing front-end development, however from a nowadays perspective it is not enough(legacy). No wonder as Magento 2 was released before Vue.js and React took the world popularity. These JS frameworks add more features for developers and - in general - provide more possibilities. 

# Install Magento ReactJS/VueJS extension via composer:
```
composer require genaker/react-luma
```
# Deploy static
React Luma doesn't use native magento Less generated CSS. Instead you need copy pre-optimised style from module *pub/static* content(CSS) to the root *pub/static*

# Disable dafault Adobe's broken Magento 2 KnockoutJS UI Components from the frontend

![image](https://user-images.githubusercontent.com/9213670/154380709-8a0eaf05-266a-4aa6-9c1c-d79653dba38d.png)



# Magento 2 Admin module built with ReactJS instead of the legacy default JS:

(https://github.com/Genaker/Magento2OPcacheGUI)


# How to use WebPack with Magento 2

Install Node.JS (https://github.com/nodesource/distributions/blob/master/README.md) From the extension root (React/React) folder run:

```
npm install
npm start
```

Web Puck compiles everything automatically into React/React/view/base/web/js/index_bundle.js and deploys to pub/static without running static:deploy ssh command. LiveReload Plugin will reload Magento 2 pages automatically (not recommended solution by React Community. F5  more reliable solution). What you need just disable the Cache of your browser during development. Also, You can disable caching for single react bundle files via Nginx config.

# Easy deployment 

Usage of the web-pack sometimes is too difficult for Magento developers. I have created another way to use React with Magento without any compilation/complication.
Simply add these files to the Magento installation:
```
// React JS itself 
<script src='https://npmcdn.com/react-dom@15.3.0/dist/react-dom.min.js'></script>
// Babel to avoid compilation 
<script src="https://unpkg.com/@babel/standalone/babel.min.js"></script>

//Add to the page to render React component
<div id = "magentoReactApp"> </div>

//Write you scripts using babel 
<script type="text/babel">

var App = React.createClass({
	render: function() {
		return(
			<div className="App">
	             {/*Your APP code there */}
			</div>
		);
	}
});
ReactDOM.render(
	<App />,
	document.getElementById('magentoReactApp')
);
</script>
```

# Magento 2 live reload

This project aims to solve the case where you want assets served by your Magento app server, but still want reloads triggered from web packs build pipeline.

Add a script tag to your page pointed at the livereload server

```
<script src="http://localhost:35729/livereload.js"></script>
```
For development purposes, better disable browser caching (https://www.technipages.com/google-chrome-how-to-completely-disable-cache)

Disable react bundle caching for development purposes using Nginx (not tested yet):

```
location ~ index_bundle\.js {
    add_header Cache-Control no-cache;
    expires 0;
  }
```
