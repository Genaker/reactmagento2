# Do you have questions How to integrate ReactJS with Magento 2 frontend? This magento module will help you
React Magento 2 implementation. This Simple module explains how to add and use React Components with Magento 2 and forgot about Knockout/JQuery Magento 2 UI.
![React + Magento 2](https://github.com/Genaker/reactmagento2/blob/master/KnockoutMagento2React.png)

It is not PWA or headless implementation which is impossible to use with existing web site and also they have issue with magento 2 API performance - to slow. This implementation is High Performance Hybrid React integration with magento2 (with magento 1 also easy to use) it uses inline json directly from the page. The same approach used in magento 2 backend and frontend checkout, color swatches by default. Also can use Ajax http call to fetch data (not the best solution magento API is slow and will increase load on your beckend server). Or you  can use my future project "Microservicess magento" to fetch data.
Our simplest magento 2 module use WebPack for React Components Compilation and automatic static content deployment into magento pub/static folder.

You can develop React component even without magento instalation at all. You can just copy your component inside magento Module and add some fixes into sources to work with Require JS and use React Component as Magento UI component.

# Magento 2 Admin module built with ReactJS instead of legacy magento default JS:

(https://github.com/Genaker/Magento2OPcacheGUI)

# How to use WebPack for React with Magento 2

Install Node.JS (https://github.com/nodesource/distributions/blob/master/README.md) From the extension root (React/React) folder run:

```
npm install
npm start
```

Web Puck compiles everything automatically into React/React/view/base/web/js/index_bundle.js and deploys to pub/static without running static:deploy ssh command. LiveReload Plugin will reload magento 2 pages automatically (not recommended solution by React Community. F5  more reliable solution). What you need just disable Cache of your browser during development. Also You can disable caching for single react bundle file via Nginx config.

# Easy deployment 

Useage of webPack sometimes is to difficult for Magento developers. I have create anoter way to use React with magento without any compilation/complication.
Simply add this files to the Magento instalation:
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

# What is Babel for Magento?
Babel is a JavaScript compiler
Babel is a toolchain that is mainly used to convert ECMAScript 2015+ code into a backwards compatible version of JavaScript in current and older browsers or environments. Here are the main things Babel can do for you:

Transform syntax:
- Polyfill features that are missing in your target environment (through @babel/polyfill)
- Source code transformations (codemods)

# JSX and React with Magento 1,2 

JSX is an XML-like syntax extension to ECMAScript without any defined semantics. It's NOT intended to be implemented by engines or browsers. It's NOT a proposal to incorporate JSX into the ECMAScript spec itself. It's intended to be used by various preprocessors (transpilers) to transform these tokens into standard ECMAScript. In our case transpiler is Babel. 
Babel can convert JSX syntax!

Use Bable for Magento via UNPKG: https://unpkg.com/@babel/standalone/babel.min.js. This is a simple way to embed it on a webpage without having to do any other setup.

When loaded in a browser, @babel/standalone will automatically compile and execute all Magentos script tags with type 'text/babel' or 'text/jsx'

# Magento 2 live reload

This project aims to solve the case where you want assets served by your Magento app server, but still want reloads triggered from webpack's build pipeline.

Add a script tag to your page pointed at the livereload server

```
<script src="http://localhost:35729/livereload.js"></script>
```
For development purpose better disable browser caching (https://www.technipages.com/google-chrome-how-to-completely-disable-cache)

Disable react bundle caching for development purpose using Nginx (not tested yet):

```
location ~ index_bundle\.js {
    add_header Cache-Control no-cache;
    expires 0;
  }
```

# Similar Projects:
Magento 2 Admin module built with ReactJS instead of legacy magento default JS: (https://github.com/Genaker/Magento2OPcacheGUI)

Good Magento 2 react approach from good guy: (https://github.com/yireo-training/Yireo_ReactMinicart)

Something similar from integer_net GmbH : (https://github.com/integer-net/magento2-reactapp)


## A lot of Big e-commerce companies Already Use this React Approach :
BestBuy.mx
beautycounter.com
icuracao.com
bloomnation.com
plantt.com
gap.com
walmart.com

