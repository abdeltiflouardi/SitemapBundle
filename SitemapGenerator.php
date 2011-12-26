<?php

namespace OS\SitemapBundle;

use Doctrine\ORM\EntityManager,
    DOMDocument,
    DateTime;

/**
 * @author ouardisoft
 */
class SitemapGenerator
{

    /**
     *
     * @var EntityManager $em
     */
    private $em;

    /**
     * @var $router
     */
    private $router;

    /**
     *
     * @var array $configs 
     */
    private $configs;

    /**
     *
     * @param EntityManager $em
     * @param Router $router
     * @param array $configs 
     */
    function __construct(EntityManager $em, $router, $configs)
    {
        $this->em = $em;
        $this->router = $router;
        $this->configs = $configs;
    }

    public function generate($returnString = false)
    {
        // Create dom object
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->formatOutput = true;
        $dom->substituteEntities = false;

        // Create <urlset> root tag
        $urlset = $dom->createElement('urlset');

        // Fetch All entities
        $entities = $this->em->getRepository($this->configs['entity'])->findAll();

        /*
         *  Generate <url> tags and bind them in urlset
         *  <url>
         *     <loc>link</loc>
         *     <lastmod>date</lastmod>
         *     <priority>date</priority>
         *  </url>
         */
        $tags = array('loc', 'lastmod', 'priority');
        foreach ($entities as $entity) {
            $url = $dom->createElement('url');
            foreach ($tags as $tag) {
                $text = $dom->createTextNode($this->getTagValue($entity, $tag));
                $elem = $dom->createElement($tag);
                $elem->appendChild($text);

                $url->appendChild($elem);
            }

            $urlset->appendChild($url);
        }

        $dom->appendChild($urlset);

        if ($returnString == false)
            return $dom->save($this->configs['path']);

        return $dom->saveXML();
    }

    /**
     * 
     * @param Entity $entity
     * @param string $tag
     * @return string 
     */
    public function getTagValue($entity, $tag)
    {
        if (!is_array($this->configs[$tag])) {
            $method = 'get' . ucfirst($this->configs[$tag]);
            if (method_exists($entity, $method)) {
                $value = $entity->$method();

                if ($value instanceof DateTime) {
                    $value = $value->format('r');
                } else {
                    $value = substr($value, 0, 100);
                }
            } else {
                $value = $this->configs[$tag];
            }

            return $value;
        } else {
            extract($this->configs[$tag]);

            foreach ($params as $key => $param) {
                if (is_array($param)) {
                    $value        = $entity->{'get' . ucfirst($param['field'])}();
                    $object       = new $param['class'];
                    $params[$key] = $object->{$param['method']}($value);
                } else {
                    $value        = $entity->{'get' . ucfirst($param)}();
                    $params[$key] = $value;
                }
            }
            return $this->router->generate($route, $params, true);
        }
    }

}

?>
