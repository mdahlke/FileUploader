FileUploader

This is an easy to integrate PHP file uploader for handling image, audio, and various document uploads.

The main purpose of this script is to minimize the amount of coding you must to do implement a secure and fast uploading script to your site.


Minimum code required to start uploading
```php
require 'UploadImage.class.php
$img = new UploadImage( $_FILES['image'] );
$img->upload();
```
