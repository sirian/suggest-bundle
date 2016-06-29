# SuggestBundle #

## About ##

This is a Symfony bundle which enables the popular [Select2](https://select2.github.io/) component to be used as a drop-in replacement for a standard `entity`, `document` and `choice` fields on a Symfony form.

The main feature of this bundle is that the list of choices is retrieved via a remote ajax call.

## 1. Installation ##

Add the `sirian/suggest-bundle` package to your `require` section in the `composer.json` file.

``` bash
$ composer require sirian/suggest-bundle
```

Add the SuggestBundle to your application's kernel:

``` php
<?php
public function registerBundles()
{
    $bundles = array(
        // ...
        new Sirian\SuggestBundle\SirianSuggestBundle(),
        // ...
    );
    ...
}
```

### 2. Configuration

After installing the bundle, make sure you add this route to your routing:

``` yaml
# app/config/routing.yml

_sirian_suggest:
    resource: "@SirianSuggestBundle/Resources/config/routing.yml"
    prefix:   /suggest
```

And choose default widget for form (depends on select2 version you use). One of `select2_v3`, `select2_v4`. You could also specify other default form options for `SuggestType::class`

```yaml
# app/config/config.yml

...
sirian_suggest:
    form_options:
        widget: select2_v4
        attr:
            placeholder: "Search..."
```

### 3. Configuring suggesters

#### 3.1. Doctrine ODM Document suggesters

```yaml
# app/config/config.yml

...
sirian_suggest:
    odm:
        category:
            class: "MainBundle:Category"
            property: name
        
        user:
            class: "MainBundle:User"
            property: username
            search:
                email: ~
                username: ~
```

#### 3.2. Doctrine ORM Entity suggesters

```yaml
# app/config/config.yml

...
sirian_suggest:
    orm:
        category:
            class: "MainBundle:Category"
            property: name
        
        user:
            class: "MainBundle:User"
            property: username
            search:
                email: ~
                username: ~
```

#### 3.3. Custom suggesters

When you need some additional logic - you could create your own suggester. For example let's create `AdminSuggester` which suggests only users having `ROLE_ADMIN` role
 
```php
<?php

namespace App\MainBundle\Suggest;

use App\MainBundle\Document\User;
use Doctrine\Common\Persistence\ManagerRegistry;
use Sirian\SuggestBundle\Suggest\DocumentSuggester;
use Sirian\SuggestBundle\Suggest\Item;
use Sirian\SuggestBundle\Suggest\SuggestQuery;

class AdminSuggester extends DocumentSuggester
{
    public function __construct(ManagerRegistry $registry)
    {
        $options = [
            'class' => User::class,
            'id_property' => 'id',
            'property' => 'username',
            'search' => ['name' => 1, 'username' => 1, 'email' => 1]
        ];

        parent::__construct($registry, $options);
    }

    protected function createSuggestQueryBuilder(SuggestQuery $query)
    {
        $qb = parent::createSuggestQueryBuilder($query);
        
        $qb->field('roles')->equals('ROLE_ADMIN');
        
        return $qb;
    }

    protected function transformObject($user)
    {
        $item = new Item();
        $item->id = $user->getId();
        $item->text = $user->getName() . ' ( ' . $user->getUsername() . ', ' . $user->getEmail() . ')';

        return $item;
    }
}


```

Define service `services.yml` with `sirian_suggest.suggester` tag
```yaml
    app.suggester.admin:
        class: App\MainBundle\Suggest\AdminSuggester
        arguments: ["@doctrine_mongodb"]
        tags: 
            - {name: 'sirian_suggest.suggester', alias: 'admin'}
```

Alias `admin` will be used in `suggester` option for `SuggestType::class` and in `/suggest/admin` url pattern.

### 4. Using 

Now you can use configured suggesters in forms

```php
$formBuilder->add('category', SuggestType::class, [
    'suggester' => 'category'
])
```


### 5. Security

You could restrict access to suggesters by securing [URL patterns](http://symfony.com/doc/current/book/security.html#securing-url-patterns-access-control)

```yaml
security:
   ...
   access_control:
        ...
        - { path: ^/suggest/category, roles: ROLE_USER }
        - { path: ^/suggest, roles: ROLE_ADMIN }
```
