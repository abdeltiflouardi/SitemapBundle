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
* **entity**: Use this entity to generate my file
* **loc**: this is a sitemap tag. we can use our route to generate link.
* **lastmod**: use this param to generate lastmod tag
* **priority**: priority


example
-------

     os_sitemap:
         path: "%kernel.root_dir%/../web/sitemap.xml"
         entity: AppCoreBundle:Post
         loc: {route: _post, params: {post_id: id, title: slug}}
         lastmod: updatedAt
         priority: 0.5

My route is:
_post:
  pattern: /{post_id}/{title}/

My database table
  post(id, title, slug, text, createdAt, updatedAt)

if you have not slug field and you want to generate slug from title field use this configuration

loc: {route: _post, params: {post_id: id, {field: title, class: App\CodeBundle\Inflector, method: slug}}}

In your controller
==================

     $sitemapGenerator = $this->get('sitemap.generator');
     $sitemapGenerator->generate();

