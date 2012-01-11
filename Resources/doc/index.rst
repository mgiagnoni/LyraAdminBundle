About LyraAdminBundle
=====================

LyraAdminBundle consists of a set of **Twig** templates, a base controller
for CRUD actions and some 'renderer' services to simplify the creation of a
backend area powered by **jQuery** and **jQuery UI** widgets.

This bundle is being developed to provide a common backend interface to all
bundles that make part of **Lyra CMS**, however it can also be used independently
from the CMS as a standalone standard Symfony2 bundle.

This is a work in progress and documentation is still incomplete, however many
features already work and it should be possible to successfully follow the basic
``Getting started`` tutorial you will find below.

If you test the bundle and find errors please use the GitHub issue tracker
to report them. Suggestions are also welcome.

Note
====

Former symfony users will notice quite a few similarities between this bundle
and the symfony 1.x *Admin Generator*: the backend area is organized in a
similar way and offers the same kind of *views*.

List view
---------

This view displays a set of records in a grid layout with sortable columns and
pagination. From the list view you can perform different *actions*.

*   **List actions**: ``new`` is the default list action.

*   **Object actions**: these actions always affects a single record displayed
    in a grid row. Default object actions are ``edit`` and ``delete``.

*   **Batch actions**: these actions affects multiple records selected with
    the grid *check boxes*. Default batch actions is ``delete``.

Form view
---------

This view displays the form to insert and edit a record. Form fields can be
ordered and grouped in *panels*. Separate configuration options are available
for ``new`` and ``edit`` form.

----

That being said, there are important differences between LyraAdminBundle and
the symfony *Admin Generator*.

*   This bundle is not a **code generator**, it utilizes Twig template
    inheritance and class inheritance to provide a base backend interface users
    can extend to fit their needs.

*   In place of storing configuration options in a dedicated file (``generator.yml``),
    the bundle exposes a semantic configuration handled by a `service container
    extension`_ and stores configuration options as Dependency Injection Container
    parameters.

*   The overall layout of the backend area can be customized with one of the
    many jQuery UI *themes*, while the user interface is enhanced by standard 
    jQuery UI *widgets* (buttons, modal dialogs).

.. _service container extension: http://symfony.com/doc/current/book/service_container.html#importing-configuration-via-container-extensions

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

Importing routes
----------------

The bundle routing file must be imported in your application configuration::

    # app/config/routing.yml

    LyraAdminBundle:
        resource: "@LyraAdminBundle/Resources/config/routing.yml"


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

`AcmeClassifiedsBundle source code`_ is available at GitHub.

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

    # app/config/config.yml

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


Do not forget to clear cache before proceeding::

    app/console cache:clear

.. _AcmeClassifiedsBundle source code: https://github.com/mgiagnoni/AcmeClassifiedsBundle

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

    # app/config/config.yml

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

    # app/config/config.yml

        # ... #
            list:
                columns:
                    ad_title: ~ 
                    published: 
                        sortable: false
                    posted_at: ~ 

Use the ``format`` option to format a column content. For columns displaying
dates you can use all format strings allowed by the PHP function ``date``,for
any other column you can use all format placeholders allowed by PHP functions
``printf``, ``sprintf``::

    # app/config/config.yml

        # ... #
            list:
                columns:
                    ad_title: ~
                    published: ~
                    posted_at:
                        label: Date
                        format: 'j/M/Y'

Action buttons configuration
----------------------------

The button to create a new record has a generic text *New* and a default icon.
Here is how you can you change the configuration if you prefer a more descriptive
text and a different icon::

    # app/config/config.yml

        # ... #
            actions:
                new:
                    text: 'New listing'
                    icon: circle-plus
            list:
                columns:
                    # ... #

The value of the ``icon`` option must be the class name (without the ``ui-icon-``
part) used in **jQuery UI** theme stylesheet for the icon. You can find all
available icons on the `Theme roller`_  home page.

.. _Theme roller: http://jqueryui.com/themeroller/

You can customize all the other default actions (``edit``, ``delete``) in the
same way.

Filter configuration
--------------------

List results can be filtered by the value of one or more of the ``Listing``
entity fields. Example::

    # app/config/config.yml

        # ... #
            filter:
                # search dialog title
                title: Search listings
                fields:
                    ad_title: ~
                    posted_at: ~
                    published: ~
            list:
                columns:
                    # ... #

With these options ``Listing`` objects are searchable by title, posting date
(from/to range) and published status.

This feature is not fully implemented yet and it works only for string, datetime
and boolean fields.

Creating custom batch actions
-----------------------------

A batch action to delete multiple records is available by default. Here is
how you can add your own custom batch actions, for example to publish/unpublish
multiple listings::

    # app/config/config.yml

    lyra_admin:
        models:
            listing:
                class: 'Acme\ClassifiedsBundle\Entity\Listing'
                controller: 'AcmeClassifiedsBundle:Admin'
                actions:
                    publish:
                        # text displayed in drop down list
                        text: Publish
                    unpublish:
                        text: Unpublish
                list:
                    # ... #
                    batch_actions: [publish,unpublish,delete]

With the ``controller`` option you can use your own controller in place of
the default controller provided by the bundle. This is needed now because you
will write custom php code to process your batch actions::

    // Acme/ClassifiedsBundle/Controller/AdminController.php

    namespace Acme\ClassifiedsBundle\Controller;
    use Lyra\AdminBundle\Controller\AdminController as BaseAdminController;

    class AdminController extends BaseAdminController
    {
        protected function executeBatchPublish($ids)
        {
            $this->getModelManager()->setFieldValueByIds('published', true, $ids);
        }

        protected function executeBatchUnpublish($ids)
        {
            $this->getModelManager()->setFieldValueByIds('published', false, $ids);
        }
    }

Your controller class must extend LyraAdminBundle base controller. A method
created to process a batch action must be named ``executeBatch`` + action name.
It will receive as argument an array containing the primary keys of selected
records.

**getModelManager()** is a shortcut method defined in base controller that
returns an instance of the manager service for the ``listing`` model;
**setFieldValueByIds()** is one of the methods provided by the manager service
and allows you to modify a field value of multiple objects selected by primary key.

Creating custom list actions
----------------------------

You can also create buttons to perform administrative tasks. Assuming for example
that you want to provide backend users with a quick way to delete all expired
listings, you can configure a custom **list action**::

    # app/config/config.yml

    lyra_admin:
        models:
            listing:
                # ... #
                actions:
                    expired:
                        # action route is admin/listing/expired
                        route_pattern: expired
                        text: 'Delete expired'
                        icon: trash
                        dialog:
                            title: 'Confirm delete expired'
                            message: 'Do you really want to delete all expired listings?'
                    # ... #
                list:
                    # ... #
                    list_actions: [new,expired]

Because this action will permanently remove records from the database it's a
good idea to configure a confirmation dialog. Note that in ``list_actions``
option you need to also include the default list action ``new`` or it will be
removed.

The code that will be executed when the button is pressed and confirmation given
goes in the controller class you have already created for custom batch actions::

    // Acme/ClassifiedsBundle/Controller/AdminController.php

    namespace Acme\ClassifiedsBundle\Controller;
    use Lyra\AdminBundle\Controller\AdminController as BaseAdminController;

    class AdminController extends BaseAdminController
    {

        public function expiredAction()
        {
            if ('POST' === $this->getRequest()->getMethod()) {
                $this->getModelManager()->getRepository()->createQueryBuilder('a')
                    ->delete()->where('a.expires_at < :d')
                    ->setParameter('d', new \DateTime('now'))
                    ->getQuery()->execute();

                $this->setFlash('acme_classifieds success', 'Expired ads have been successfully deleted');

                return $this->getRedirectToListResponse();
            }

            $renderer = $this->getDialogRenderer();

            return $this->container->get('templating')
                ->renderResponse('LyraAdminBundle:Admin:dialog.html.twig', array(
                    'renderer' => $renderer
            ));
        }

        // ...
    }

When a confirmation dialog is configured, the controller displays the dialog
when the request method is GET and performs the action task when the method
is POST (i.e user has given confirmation through the dialog window).

This solution works and it's maybe acceptable for a simple action like this,
but for more complex tasks you should avoid to stuff everything inside a controller
as this will make a lot more difficult to reuse the code.

A far better solution involves the creation of a custom model manager for the
``Listing`` object and will be explained below (see 'Extending model manager services').

Basic form configuration
------------------------

Even if the form to create and edit a ``Listing`` object is fully functional
without any configuration, you will usually need to re-order the fields, group
them in panels or remove some fields from view. A simple example::

    # app/config/config.yml

    lyra_admin:
        models:
            listing:
                class: 'Acme\ClassifiedsBundle\Entity\Listing'
                form:
                    groups:
                        listing:
                            # panel title
                            caption: Listing
                            fields: [ad_title,ad_text]
                            # column break after this panel
                            break_after: true
                        status:
                            caption: Status
                            fields: [published,expires_at]
                list:
                    # ... #

With this configuration form fields are grouped in two panels displayed on two
columns (see the ``break_after`` option). You will notice that the ``posted_at``
field is not present in any panel: this field will not be visible and not
editable through the form. This can be useful for fields you want to automatically
update via a Doctrine *lifecycle callback* and that cannot be changed by users.

Change admin theme
------------------

The bundle includes two themes: ``ui-lightness`` (default) and ``smoothness``.
To change theme use this configuration::

    # app/config/config.yml

    lyra_admin:
        theme: smoothness
        models:
            listing:
                # ... #

You can get additional themes from the `Theme roller`_ page on the jQuery UI website.
Once you have downloaded the desired theme, *Redmond* for example, uncompress
the package::

    jquery-ui-#.#.#.custom.zip
        css
            redmond <- only this folder and its contents are needed
                images
                    jquery-ui-#.#.#.custom.css <- rename as jquery-ui.custom.css


The package contains some stuff you will not need for use with the bundle.
Move only the folder with the same name of the theme somewhere inside your
project public folder (usually ``web``), for example ``web/css/ui_themes``, 
renaming the theme css file as indicated above. To use the new theme edit the
bundle configuration in this way::

    # app/config/config.yml

    lyra_admin:
        # path to theme folder *relative* to application public folder
        theme: css/ui_themes/redmond
        models:
            listing:
                # ... #


.. _Theme roller: http://jqueryui.com/themeroller/

Extending model manager services
--------------------------------

All the essential operations needed to manage objects (create, update,
delete, find and more) are performed by a model manager service.
A default model manager is provided by the bundle and can be extended by
user defined model managers.

By definining a model manager for the ``Listing`` object you will be able
to clean up the controller that executes the custom list action to delete
expired listings. First create your service class::

    // Acme/ClassifiedsBundle/Model/ListingManager.php

    namespace Acme\ClassifiedsBundle\Model;

    use Lyra\AdminBundle\Model\ORM\ModelManager as BaseManager;

    class ListingManager extends BaseManager
    {
        public function deleteExpiredListings()
        {
            $this->getRepository()->createQueryBuilder('a')
                ->delete()
                ->where('a.expires_at < :d')
                ->setParameter('d', new \DateTime('now'))
                ->getQuery()->execute();

            return true;
        }
    }

You must extend the base model manager provided by LyraAdminBundle as
default functionalities cannot be lost. Define your service in configuration::

    // app/config/config.yml

    services:
        classifieds_listing_manager:
            class: Acme\ClassifiedsBundle\Model\ListingManager

See the file `Resources/config/services.yml`_ in AcmeClassifiedsBundle
repository for an example of how to define this service in a bundle configuration
file loaded by the bundle extension.

Change the configuration of the ``Listing`` model to use your custom manager::

    # app/config/config.yml

    lyra_admin:
        models:
            listing:
                # ... #
                services:
                    # service id of user defined model manager
                    model_manager: classifieds_listing_manager

The controller used by the custom action to delete expired listings can now
be cleaned up::

    // Acme/ClassifiedsBundle/Controller/AdminController.php

    namespace Acme\ClassifiedsBundle\Controller;
    use Lyra\AdminBundle\Controller\AdminController as BaseAdminController;

    class AdminController extends BaseAdminController
    {

        public function expiredAction()
        {
            if ('POST' === $this->getRequest()->getMethod()) {
                if ($this->getModelManager()->deleteExpiredListings()) {
                    $this->setFlash('acme_classifieds success', 'Expired ads have been successfully deleted');
                }

                return $this->getRedirectToListResponse();
            }
                // No changes from here
        }
    }

.. _Resources/config/services.yml: https://github.com/mgiagnoni/AcmeClassifiedsBundle/blob/master/Resources/config/services.yml

Improving the sample bundle
===========================

It's time to add more features to the sample bundle. Displaying a bunch of
uncategorized listings is not very useful, so let's see how to manage
listing **categories**.

Create a ``Category`` entity with the **SensioGeneratorBundle**::

    app/console generate:doctrine:entity --entity=AcmeClassifiedsBundle:Category --fields="name:string(255) description:text" --with-repository --no-interaction

Implement a *__toString()* method in the newly created entity::

    // Acme/ClassifiedsBundle/Entity/Category.php

    // ...
    class Category
    {
        // ...
        public function __toString()
        {
            return $this->name;
        }
    }

This step is needed as the value of the ``name`` property will be used to
build the options of the dropdown list used to select the listing category
on the listing form.

Edit the ``Listing`` entity to add a **many-to-one** relation with
``Category``::

    // Acme/ClassifiedsBundle/Entity/Listing.php
    // ...
    class Listing
    {
        // ...

        /**
         * @ORM\ManyToOne(targetEntity="Category")
         */
        private $category;

        public function setCategory(Category $category)
        {
            $this->category = $category;
        }

        public function getCategory()
        {
            return $this->category;
        }
    }

Update the database::

    app/console doctrine:schema:update --force

Create a model ``category`` in LyraAdminBundle configuration::

    # app/config/config.yml

    lyra_admin:
        models:
            listing:
                # ... #
            category:
                class: 'Acme\ClassifiedsBundle\Entity\Category'
                # title displayed in top menu
                title: Categories
                list:
                    title: Listing categories
                    columns:
                        name: ~
                        description: ~

Now you can follow the link ``Categories`` in the top menu to create new
categories. Then you need only to add the ``category`` property to the
configuration of the ``Listing`` form::

    # app/config/config.yml

    lyra_admin:
        models:
            listing:
                # ... #
                form:
                    groups:
                    listing:
                        caption: Listing
                        fields: [category,ad_title,ad_text]

The form to create / edit a listing now contains a dropdown list to select
the desired category.

Category fields can be also diplayed in a list column::

    # app/config/config.yml

    lyra_admin:
        models:
            listing:
                # ... #
                list:
                    columns:
                        category.name:
                            label: Category
                            sortable: false
                        # ... #

Note that currently the ``sortable`` option of a column displaying a field of
a related model must be set to false.

Configuration summary
=====================

Below you will find an example with all the configuration options you have
seen up to this point::

    # app/config/config.yml

    lyra_admin:
        theme: smoothness # or ui-lightness (default)
        # additional themes installed in web/css/ui_themes
        #theme: css/ui_themes/redmond
        models:
            listing:
                class: 'Acme\ClassifiedsBundle\Entity\Listing'
                controller: 'AcmeClassifiedsBundle:Admin'
                # title displayed in top menu
                title: Listings
                actions:
                    publish:
                        # for batch actions it's the text displayed in drop down list
                        text: Publish
                    unpublish:
                        text: Unpublish
                    new:
                        # for list/object actions it's the button text
                        text: 'New listing'
                        # button icon
                        icon: circle-plus
                    expired:
                        route_pattern: expired
                        text: 'Delete expired'
                        icon: trash
                        dialog:
                            title: 'Confirm delete expired'
                            message: 'Do you really want to delete all expired listings?'
                list:
                    title: Listings
                    columns:
                        category.name:
                            label: Category
                            sortable: false
                        ad_title: ~
                        published:
                            sortable: false
                        posted_at:
                            label: Date
                            format: 'j/M/Y'
                    batch_actions: [publish,unpublish,delete]
                    list_actions: [new,expired]
                filter:
                    # search dialog title
                    title: Search listings
                    fields:
                        ad_title: ~
                        posted_at: ~
                        published: ~
                form:
                    groups:
                        listing:
                            # panel title
                            caption: Listing
                            fields: [ad_title,ad_text]
                            # column break after this panel
                            break_after: true
                        status:
                            caption: Status
                            fields: [published,expires_at]
                services:
                    # service id of user defined model manager
                    model_manager: classifieds_listing_manager
            category:
                class: 'Acme\ClassifiedsBundle\Entity\Category'
                list:
                    title: Categories
                    columns:
                        name: ~
                        description: ~



[to be continued ...]
