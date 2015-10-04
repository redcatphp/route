Route your application's requests
=================================

 Route is a [micro-framework](https://en.wikipedia.org/wiki/Microframework) designed for manage requests with flexibility and simplicity.

FrontController
---------------

 This is a basic [front-controller](https://en.wikipedia.org/wiki/Front_Controller_pattern) class which you can extends to build the front map of your application. It's build with a router that will solve the request. Is implementing the [ArrayAccess](http://php.net/manual/en/class.arrayaccess.php) interface and *runFromGlobals* method which is used in the generic surikat's *index.php*.  
 Extending:  
 
```php
class MyFrontOffice extends \\Wild\\Route\\FrontOffice{  
    function \_\_construct(Router $router,Di $di){  
        parent::\_\_construct($router,$di);  
        $this->map([  
            ['backend/','new:Wild\\Plugin\\FrontController\\Backoffice'],  
            [['new:Wild\\Route\\Match\\Extension','css|js|png|jpg|jpeg|gif'],'new:Wild\\Plugin\\FrontController\\Synaptic'],  
            [['new:Wild\\Plugin\\RouteMatch\\ByTml'.($this->l10n?'L10n':''),'','template'],'new:Wild\\Plugin\\Templix\\Templix'.($this->l10n?'L10n':'')],  
        ]);  
    }  
    function run($path,$domain=null){  
        if(!parent::run($path,$domain)){  
            http\_response\_code(404);  
            print "Page not found !";  
            exit;  
        }  
        return true;  
    }  
}  
            
```
  
 And then, use it:  
 
```php
use Wild\\Route\\Match\\Prefix;  
use Wild\\Route\\Match\\Suffix;  
use Wild\\Route\\Match\\Regex;  
use Wild\\Route\\Match\\Extension;  
  
$f = new MyFrontOffice();  
            
```
 Append and prepend methods:  
 
```php
$this->append(new Prefix('test/'),function($path){  
    print "My url start with 'test' followed by '$path'";  
});  
$this->prepend(new Prefix('test/more'),function($path){  
    print "My url start with 'test/more' followed by '$path'";  
});  
$f->append(new Suffix('.stuff'),function($path){  
    print "My url end with '.stuff' preceded by '$path'";  
});  
            
```
 Z-index like api in third parameter (default is zero)  
 It will look first for ".stuff" matching, then "test/more", and finally "test/":  
 
```php
$this->append(new Prefix('test/'),function($path){  
    print "My url start with 'test' followed by '$path'";  
},2);  
$this->prepend(new Prefix('test/more'),function($path){  
    print "My url start with 'test/more' followed by '$path'";  
},2);  
$f->append(new Suffix('.stuff'),function($path){  
    print "My url end with '.stuff' preceded by '$path'";  
},1);  
            
```
 Parameters automatic wrap:  
 
```php
// test/more is a string, consequently it will be wrapped automaticaly by Prefix object  
$this->prepend('test/more',function($path){  
    print "My url start with 'test/more' followed by '$path'";  
});  
  
// string start with "/^" and end with "$/", consequently it will be wrapped automaticaly by Regex object  
$this->append('/^blog/(\\w+)/(\\d+)$/',function($category, $id){  
    // if url is blog/php/200 it will print "php:200"  
    print $category.':'.$id;  
});  
            
```
 Empty url handling:  
 
```php
$f->append('',function(){  
    print 'You are on home page !';  
});  
            
```
Lazy loading *match*, array containing first element starting with "new:", the object will be instantiated only if is necessary (previous didn't match):   
 
```php
$f->append(['new:Wild\\Route\\Match\\Suffix','.stuff'],function($path){  
    print "My url end with '.stuff' preceded by '$path'";  
});  
            
```
 Lazy loading *callback*, array containing first element or string starting with "new:", the object will be instantiated only if is necessary (matching):   
 
```php
// Class instanciation and method  
$f->append('hello',[['new:MyModuleClass'],'methodName']);  
  
// Class instanciation with construct params and method  
$f->append('hello',[['new:MyModuleClass',$param1ForInstanciation,$param2ForInstanciation],'methodName']);  
  
// Class instanciation and invokation  
//   object will be invoked after instanciation using \_\_invoke magic method if exists  
$f->append('hello','new:MyModuleClass');  
            
```
Run  
 
```php
//manual url  
$f->run('test/');  
  
//automatic current url  
$f->runFromGlobals();  
            
```


Router
------

 The router is the component which is used by *FrontController* to map, append and prepend pair of match to behaviour. It support the methods explained before in [*FrontController*](http://wildsurikat.com/Documentation/Route#frontcontroller) except *runFromGlobals*.

Match
-----

 The basic match components are distributed under the *Wild\\Route\\Match* namespace but there is also some examples of specific match in the *Wild\\Plugin\\RouteMatch* namespace. The only rule to make a *Match* object is that he have to be callable implementing \_\_invoke magic method. You can also use php [Closure](http://php.net/manual/en/class.closure.php) also called [anonymous function](http://php.net/manual/en/functions.anonymous.php).

Url
---

 Url is a tiny helper for extract some simple components from Url. 
```php
$url = new Url;  
  
\# http:// or https://  
$url->getProtocolHref();  
  
\# mydomain.com  
$url->getServerHref();  
  
\# output integer number of port if different from default (80 for http and 443 for https)  
$url->getPortHref();  
  
\# root-path-of-surikat/  
$url->getSuffixHref();  
  
\# http://mydomain.com/root-path-of-surikat/  
$url->getBaseHref();  
  
\# http://mydomain.com/root-path-of-surikat/current-path/  
$url->getLocation();  
  
\# http://mydomain.com/root-path-of-surikat/  
$url->getSubdomainHref();  
  
\# http://fr.mydomain.com/root-path-of-surikat/  
$url->getSubdomainHref('fr');  
  
\# if current subdomain contain 2 character it will output them  
$url->getSubdomainLang();  
            
```

