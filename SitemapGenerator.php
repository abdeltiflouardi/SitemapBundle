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
         * @var EntityManager $em
         */
        private $em;

        /**
         * @var
         */
        private $router;

        /**
         *
         * @var array $configs
         */
        private $configs;

        /**
         * @var DOMDocument $root
         */
        private $dom;

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
            $this->dom = new DOMDocument('1.0', 'UTF-8');
            $this->dom->formatOutput = true;
            $this->dom->substituteEntities = false;


            // Create <urlset> root tag
            $urlset = $this->dom->createElement('urlset');

            // Add attribute of urlset
            $xmlns = $this->dom->createAttribute('xmlns');
            $urlsetText = $this->dom->createTextNode('http://www.sitemaps.org/schemas/sitemap/0.9');
            $urlset->appendChild($xmlns);
            $xmlns->appendChild($urlsetText);

            // Iterate over all routes in the config.
            foreach($this->configs['routes'] as $route => $routeConfig) {

                // If entity in the config has been defined
                if (isset($this->configs['routes'][$route]['entity'])) {

                    // Fetch All entities
                    $entities = $this->em->getRepository($this->configs['routes'][$route]['entity'])->findAll();

                    // Creates an url node for each entity
                    foreach ($entities as $entity) {
                        $url = $this->createUrlNode($this->configs['routes'][$route], $entity);
                        $urlset->appendChild($url);
                    }
                } else {
                    $url = $this->createUrlNode($this->configs['routes'][$route]);
                    $urlset->appendChild($url);
                }
            }

            $this->dom->appendChild($urlset);

            if ($returnString == false)
                return $this->dom->save($this->configs['path']);

            return $this->dom->saveXML();
        }

        /**
         * Creates a url node which represents a singel url.
         * @param array configs
         */
        public function createUrlNode($configs, $entity=null)
        {
            /*
             *  Generate <url> tags and bind them in urlset
             *  <url>
             *     <loc>link</loc>
             *     <lastmod>date</lastmod>
             *     <priority>date</priority>
             *  </url>
             */
            $tags = array('loc', 'lastmod', 'priority');
            $url = $this->dom->createElement('url');
            foreach ($tags as $tag) {
                $text = $this->dom->createTextNode($this->{'create' . ucfirst($tag) . 'Node'}($configs, $entity));
                $elem = $this->dom->createElement($tag);
                $elem->appendChild($text);
                $url->appendChild($elem);
            }
            return $url;
        }

        /**
         * Creates a loc node (<loc><loc>)
         * @param array configs
         */
        public function createLocNode($configs, $entity=null)
        {
            $params = array();
            foreach ($configs['loc']['params'] as $key => $param) {
                if (is_array($param)) {
                    // params: { key: {value: <some value>, static: true}}
                    if (isset($param['static']) && $param['static']) { $params[$key]  = $param['value'];
                    // params: { key: {class: <some class>, method: <some method>, field: <some method>}}
                    } else {
                        $value        = $entity->{'get' . ucfirst($param['field'])}();
                        $object       = new $param['class'];
                        $params[$key] = $object->{$param['method']}($value);
                    }
                } else {
                    if(is_null($entity)) {
                        $params[$key]  = $param;
                    } else {
                        $value = $entity;

                        foreach (preg_split("/\./", $param) as $attr) {
                            $value = $value->{'get' . ucfirst($attr)}();
                        }

                        $params[$key] = $value;
                    }
                }
            }
            return $this->router->generate($configs['loc']['route'], $params, true);
        }

        /**
         * Creates a lastmod node (<lastmod></lastmod>)
         * @param array configs
         */
        public function createLastmodNode($configs, $entity=null)
        {
            if (!is_null($entity)) {
                if (method_exists($entity, 'get' . ucfirst($configs['lastmod']))) {

                    $value = $entity->{'get' . ucfirst($configs['lastmod'])}();

                    if ($value instanceof DateTime) {
                        return $value->format('Y-m-d');
                    } else {
                        return substr($value, 0, 100);
                    }
                }
            }
            return $configs['lastmod'];

        }

        /**
         * Creates a priority node (<node></node>)
         * @param array configs
         */
        public function createPriorityNode($configs)
        {
            return $configs['priority'];
        }
    }
?>
