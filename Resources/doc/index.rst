About LyraAdminBundle
=====================

LyraAdminBundle consists of a set of **Twig** templates, a base controller
for CRUD actions and some 'renderer' services to simplify the creation of a
backend area powered by **jQuery** and **jQuery UI** widgets.

This bundle is being developed to provide a common backend interface to all
bundles that make part of **Lyra CMS**, however it can also be used independently
from the CMS as a standalone standard Symfony2 bundle.

This is a work in progress and many essential features are still missing.
However existing features should work and it should be possible to successfully
follow the basic (currently incomplete) ``Getting started`` tutorial you will
find below.

If you test the bundle and find errors please use the GitHub issue tracker
to report them. Suggestions are also welcome.

Installation
============

From your project root folder run::

    git submodule add git://github.com/mgiagnoni/LyraAdminBundle.git vendor/bundles/Lyra/AdminBundle

To install the bundle as git submodule your whole project must be under version
control with git or the command ``git submodule add`` will return an error. In
this case, you can simply clone the repository::

    git clone git://github.com/mgiagnoni/LyraAdminBundle.git vendor/bundles/Lyra/AdminBundle

Register namespace
------------------

``Lyra`` namespace must be registered for use by the autoloader::

    // app/autoload.php

    $loader->registerNamespaces(array(
        // other namespaces
        'Lyra'  => __DIR__.'/../vendor/bundles',
    ));

    // ...

Add bundle to application kernel
--------------------------------

::

    // app/AppKernel.php

    public function registerBundles()
    {
        $bundles = array(
            // other bundles
            new Lyra\AdminBundle\LyraAdminBundle(),
        );

    // ...

Enable translator
-----------------

Translator must be always enabled as all messages in default templates
(i.e. button text used for default actions) are *keywords* while actual
text is in translation catalogues::

    # app/config/config.yml

    framework:
        translator: { fallback: en }

Publish bundle assets
---------------------

::

    app/console assets:install web

Load jQuery and jQuery UI
-------------------------

Javascript files needed by **jQuery** and **jQuery UI** scripts are not included
in the bundle package. The default base layout of the bundle loads these scripts
from **Google CDN**. If this doesn't fit your needs, for example because you
want to test the bundle on your *localhost* without an active Internet connection
or for any other reason, copy this file::

    [LyraAdminBundle folder]/Resources/views/Admin/jquery_js.html.twig

to::

    [Your project folder]/app/Resources/LyraAdminBundle/views/Admin/jquery_js.html.twig

Edit the file as you need. For example if you have stored *jquery.min.js* and
*jquery-ui.min.js* in ``web/js``::

    {# jquery_js.html.twig #}

    <script type="text/javascript" src="{{ asset('js/jquery.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('js/jquery-ui.min.js') }}"></script>

Getting started
===============

To demonstrate the very basic features of **LyraAdminBundle** let's generate a 
simple bundle and create an admin area for it. Our example bundle will be named
**AcmeClassifiedsBundle**: its purpose is managing a simple advertising board
where users and administrators of the site can post classified ads.

**SensioGeneratorBundle** (included in Symfony2 *Standard Edition*) is the ideal
tool to quickly generate the basic structure of the bundle. From your project
root folder run the following command::

    app/console generate:bundle --namespace=Acme/ClassifiedsBundle --dir=src --format=yml --no-interaction

Generate a ``Listing`` entity::

    app/console generate:doctrine:entity --entity=AcmeClassifiedsBundle:Listing --fields="ad_title:string(255) ad_text:text posted_at:datetime expires_at:datetime published:boolean" --with-repository --no-interaction

Create the table in the database::

    app/console doctrine:schema:update --force

Configure LyraAdminBundle to create an admin area where you will perform all
CRUD operations on the ``Listing`` entity::

    # app/config.yml

    lyra_admin:
        models:
            listing:
                class: 'Acme\ClassifiedsBundle\Entity\Listing'
                list:
                    title: Listings
                    columns:
                        ad_title: ~ 
                        published: ~
                        posted_at: ~


Access backend area
-------------------

If you go to ``http://.../app_dev.php/admin/listing/list`` you will see an
empty list of *Listings*: you can then add, edit, delete, publish/unpublish
a listing object.

Some configuration options are available to customize the list of records
(``Listings`` in our example).

Basic list configuration
------------------------

The label displayed inside colum headings is guessed from entity mapping
informations, you can change it for each column by explicitly setting the
``label`` option::

    # app/config.yml

        # ... #
            list:
                columns:
                    ad_title: ~ 
                    published: ~    
                    posted_at: 
                        label: Date

All list columns are sortable, you can change this default behavior with the
``sortable`` option. The following configuration will make the list not sortable
by the value of the *Published* column::

    # app/config.yml

        # ... #
            list:
                columns:
                    ad_title: ~ 
                    published: 
                        sortable: false
                    posted_at: ~ 

[to be continued ...]
