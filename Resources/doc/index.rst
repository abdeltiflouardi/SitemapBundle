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

Add in your file app/autoload.php

     $loader->registerNamespaces(array(
           ...
           'OS' => __DIR__ . '/../vendor/bundles',
           ...

Configuration
=============

example
-------

os_sitemap:
  path: "%kernel.root_dir%/../web/sitemap.xml"  # this is path where you want to save sitemap file
  entity: AppCoreBundle:Post  # Use this entity to generate my file
  loc: {route: _post, params: {post_id: id, title: {field: title, class: App\CoreBundle\Twig\OutputExtension, method: slug}}} # this route to generate loc link
  lastmod: updatedAt # use this field to generate lastmod
  priority: 0.5 # priority

In your controller
==================

        $sitemapGenerator = $this->get('sitemap.generator');
        $sitemapGenerator->generate();
