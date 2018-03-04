# Custom-Product-Options
Magento 2.2 Extention for Extended Custom Product Options supports Image Uploads, Swatch Colors, and Selector Grouping.

To Install:

Create a new folder inside the 'app' folder called Evil, create another folder inside 'Evil' folder called 'Custom'. Dump all the files inside '/app/Evil/Custom/'

Go to Command Line and in the root folder of Magento, enter 'php bin/magento setup:upgrade'

Now log into the Admin (CMS). Go to 'Catalog > Products' , Select or Add a Product > Edit, go to 'Customizable Options', Add Options > Option Type = Dropdown (now you can see the Extended Custom Options :)

I hope the instructions are good enough. You might have to reload the Product Detail Edit page the first time because of the Caching.

Front-End: 
 On the Product Details Page, the Variable 'EvilCustomOptions' is an array that contains all the new Custom Options, it has been added to the Product Data and can be accessed using  $this->_view->getProduct()->getData('EvilCustomOptions');

Let me know if you have prolems.
GL.
-David
