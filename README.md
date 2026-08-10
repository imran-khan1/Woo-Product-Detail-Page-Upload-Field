# WooCommerce Custom Product Fields

**WooCommerce Custom Product Fields** adds custom input fields to WooCommerce product pages and carries the submitted data throughout the complete shopping process.

The plugin currently provides a **Product Field** text input and a **Product File Upload** field. Customers can enter additional product-specific information and upload a file before adding the product to their cart.

### Features

* Add a custom text field to WooCommerce product pages.
* Add a file upload field to WooCommerce product pages.
* Validate required custom fields before adding products to the cart.
* Upload customer files through WordPress's media handling system.
* Store uploaded files as WordPress media attachments.
* Store custom field data with individual cart items.
* Display custom product data in the WooCommerce cart.
* Display uploaded files as clickable links in the cart.
* Save custom product data as WooCommerce order-item metadata.
* Preserve custom product information from product page through checkout and order creation.
* Uses WooCommerce hooks and WordPress APIs without modifying WooCommerce core files or templates.
* Uses WordPress sanitization and escaping practices for submitted and displayed data.

### Data Flow

```text
WooCommerce Product Page
        ↓
Product Field + File Upload
        ↓
Field Validation
        ↓
File Upload
        ↓
Cart Item Data
        ↓
WooCommerce Cart
        ↓
Checkout
        ↓
Order Item Metadata
        ↓
WooCommerce Order
```

### Use Cases

This plugin can be used when a store needs customers to provide additional information or upload files related to a product, such as:

* Custom product requirements
* Design files
* Reference documents
* Artwork
* Product specifications
* Customer instructions
* Personalized product information

The plugin is built using standard **WordPress and WooCommerce hooks**, making it lightweight, extensible, and suitable as a foundation for more advanced WooCommerce customization.
