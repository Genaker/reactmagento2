import React from 'react';
//import jQuery from 'jquery';

class AddToCartForm extends React.Component {
  constructor(props) {
    super(props);
    this.addToCartUrl = this.addToCartUrl.bind(this);
  }


  addToCartUrl()
  {

    return 'url';
  }


  render() {
    return (
        <div className='add-to-cart-form'>
        <form id ={'add_to_cart_' + this.props.rendererId} action={this.addToCartUrl()} method="post">
          <input name="qty" value='1'type="hidden"/>
          <input name='related_products' value = {this.props.selectedItem} type="hidden"/>
          <input name='form_key' value={document.form_key_react} type="hidden" />
        </form>
        </div>
        )
  }
}

export default AddToCartForm;
