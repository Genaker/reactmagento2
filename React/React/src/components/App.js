import React from 'react';

import AddToCartForm from './AddToCartForm';
//import jQuery from 'jquery';

class App extends React.Component {

  constructor(props) {
    super(props);
    //Add to cart Toggle flag
    document.afterAddToCart = false;
    document.warrantyPopUpWasOpen = false;

    this.state = {
        data: document.global_data_for_react_app,
        selected: false,
        selectedItem: null,
    };

    this.togglePopup = this.togglePopup.bind(this);
  }

  togglePopup() {

  }


  componentDidMount() {
     }




  render() {
    return (
        <div id="react">
            <h1>React Component<\h1>
            <AddToCartForm productId='null'>
        </div>
    );
  }
}

export default App;
