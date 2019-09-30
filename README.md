# Do you have questions How to integrate ReactJS with Magento 2 frontend? This magento module will help you
React Magento 2 implementation. This Simple module explains how to add and use React Components with Magento 2 and forgot about Magento 2 UI.

It is not PWA or headless implementation which is impossible to use with existing web site and also they have issue with magento 2 API performance - to slow. This implementation is High Performance Hybrid React integration with magento2 (with magento 1also easy to use) it uses inline json (the same approach used in magento 2 backend and frontend by default) from page or also can use http call to fetch data (not the best solution magento API slow and will increase load on your server) or you can use our future project Microservicess magento to fetch data.
Our simple magento 2 module use WebPack for React Components Compilation and automatic static content deployment in magento pub/static folder.

You can develop React component even without magento at all. When you finished you can just copy your component inside magento Module and add some fixes into sources to work with Require JS and use React Component as Magento UI.

# How to use WebPack for React with Magento 2

Install Node.JS (https://github.com/nodesource/distributions/blob/master/README.md) From the extension root (React/React) folder run:
```
npm install
npm start
```

Web Puck compile everything into React/React/view/base/web/js/index_bundle.js and automatically deploy to pub/static without static:deploy. LiveReload Plugin will reload magento 2 pages automatically, you need disable Cache for you browser for development

# Magento 2 live reload

This project aims to solve the case where you want assets served by your Magento app server, but still want reloads triggered from webpack's build pipeline.

Add a script tag to your page pointed at the livereload server

```
<script src="http://localhost:35729/livereload.js"></script>
```
For development purpose better disable browser caching (https://www.technipages.com/google-chrome-how-to-completely-disable-cache)

Disable react bundle caching for development using Nginx new approach(not tested yet):

```
location ~ index_bundle\.js {
    add_header Cache-Control no-cache;
    expires 0;
  }
```

# Similar Projects:
Good Magento 2 react approach from good guy: (https://github.com/yireo-training/Yireo_ReactMinicart)

## A lot of Big e-commerce companies Already Use this React Approach :
BestBuy.mx
beautycounter.com
icuracao.com
bloomnation.com
plantt.com
gap.com
walmart.com

