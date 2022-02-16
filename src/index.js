import React from 'react';
import ReactDOM from 'react-dom';
import './styles/index.css';
import App from './components/App';

const reactApp = function(){
     return {
          init(element,parameter) {
               console.log('Rendering React Component Into:' + element);
               let live = '';
               /// Magento site live reloading feature
               if (window.location.hostname  == 'localhost' || window.location.hostname.includes('loc')){
                    live ='<script src=""></script>';
                    var imported = document.createElement('script');
                    imported.src = 'http://localhost:35729/livereload.js';
                    document.head.appendChild(imported);
               }

               return ReactDOM.render(<App parameter={parameter}/>, document.getElementById(element));
          }
}
}

define(reactApp);