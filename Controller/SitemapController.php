<?php

namespace OS\SitemapBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;

class SitemapController extends Controller
{
    /**
     * @Route("/sitemap/generate")
     */
    public function generateAction()
    {
        $sitemapGenerator = $this->get('sitemap.generator');
        $r = $sitemapGenerator->generate();

        return new Response('<html><body><p>Created</p></body></html>');
    }
}
