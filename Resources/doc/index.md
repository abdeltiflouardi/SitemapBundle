## site using this bundle http://www.phphub.net/sitemap.xml

Installation using github
=========================

in your file deps add this lines

    [OSSitemapBundle]
        git=http://github.com/ouardisoft/SitemapBundle.git
        target=bundles/OS/SitemapBundle


Execute

    php bin/vendors install

Add in your file app/AppKernel.php

    ...
    public function registerBundles() {
       $bundles = array(
            ...
            new OS\SitemapBundle\OSSitemapBundle(),
            ...
    }

Add in your file app/autoload.php

    $loader->registerNamespaces(array(
        ...
        'OS' => __DIR__ . '/../vendor/bundles',
        ...


Configuration
=============

params
-------

* **path**: this is path where you want to save sitemap file
* **routes** : the routes
    * **entity**: Use this entity to generate my file, optional
    * **repository_method**: Use this param to specify method which returns entities (by default uses "findAll"), optional
    * **loc**: this is a sitemap tag. we can use our route to generate link.
    * **lastmod**: use this param to generate lastmod tag
    * **priority**: priority


examples
-------

     os_sitemap:
         path: "%kernel.root_dir%/../web/sitemap.xml"

         routes:
          - entity: AppCoreBundle:Post
            loc: {route: _post, params: {post_id: id, title: slug}}
            lastmod: updatedAt
            priority: 0.5

My route is:

    _post:
        pattern: /{category}/{post_id}/{title}/

My database table
  post(id, title, slug, text, createdAt, updatedAt, category)
  category(id, title)

if you have not slug field and you want to generate slug from title field use this configuration

loc: {route: _post, params: { category: category.title, post_id: id, {field: title, class: App\CodeBundle\Inflector, method: slug}}}

Variables in the `params` in the `loc` setting can be static. When static the string will
be passed to the controller as a string, and will not be fetched from the entity.

     os_sitemap:
         path: "%kernel.root_dir%/../web/sitemap.xml"

         routes:
          - entity: AppCoreBundle:Post
            repository_method: findEnablePosts
            loc: {route: _post, params: { category: category.title, post_id: id, {title: cookbook, static: true}}}
            lastmod: updatedAt
            priority: 0.5

Note the dotnotation to get the category title.
The generated url in the sitemap will look like: http://example.com/kitchen/1/cookbook

When a route does not contain entity variables, the entity parameter is not needed.
All parameters will be assumed static.

     os_sitemap:
         path: "%kernel.root_dir%/../web/sitemap.xml"

         routes:
          - loc: {route: _static, params: {page_name: about}}
            lastmod: 2013-05-16
            priority: 0.5

When my route is

    _static:
        pattern: /{page_name}

The generated url in the sitemap will look like http://example.com/about

When combining parts of the example:

     os_sitemap:
         path: "%kernel.root_dir%/../web/sitemap.xml"

         routes:
          - loc: {route: _static, params: {page_name: about}}
            lastmod: 2013-05-16
            priority: 0.5

          - entity: AppCoreBundle:Post
            loc: {route: _post, params: {post_id: id, {title: cookbook, static: true}}}
            lastmod: updatedAt
            priority: 0.5


In your controller
==================

     $sitemapGenerator = $this->get('sitemap.generator');
     $sitemapGenerator->generate();

