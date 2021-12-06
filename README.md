# requestData (Req)

PHP easily provides data coming to the server with **$ _POST** and **$ _GET**.

However, obtaining the form data that comes with the PUT request type is not as easy as **GET** or **POST**.

**requestData** allows you to easily obtain form data submitted with the body regardless of the request type other than POST.

    <?php
    require 'Req.php';
    $_PUT = Req::body();
    
    //it can be used like $_POST, for example..
    $_PUT['title'] or Req::body('title')
    
**requestData** supports array keys from the form.

Don't forget to take a look at the **example folder**.
