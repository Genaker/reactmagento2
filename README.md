# Dou you have questions How to integrate ReactJS with Magento 2 frontend? this magento module will help you
React Magento 2 implementation. This Simple module explains how to add and use React Components with Magento 2 and forgot about Magento 2 UI. 

It is not PWA or headless implementation which is impossible to use with existing web site and also they have issue with magento 2 APi performance - to slow. This implementation is High Performance Hybrid React integration with magento2 (with magento 1 it is easy to use ) it use inline json (the same approach used in magento 2 backend and frontend by deffault) from page or also can use http call to fech necessary data(not the best solution magento API slow and will increase load on your server) or you can use Our future project Microservicess magento to fetch data.

Our simple magento 2 module use webpack for React Components Compilation and automatic static content deployment in magento pub/static folder.

What's good - you can develop React component even without magento at all. When you finished you can just copy your component inside magento Module and add some fixes into sources to work with  Require JS and use React Component as Magento UI.


# How to use WebPack for React with Magento 2 
Install Node.JS
From the extension root (React/React) folder run:
```
npm install
npm start
```
Web Puck compile everethin into 
React/React/view/base/web/js/index_bundle.js

A lot of Big e-commerce companies Already Use this React Approach :
- BestBuy.mx
- beautycounter.com
- icuracao.com
- bloomnation.com
- plantt.com
- gap.com
- walmart.com
