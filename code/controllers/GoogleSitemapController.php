<?php

use SilverStripe\CMS\Model\SiteTree;
use SilverStripe\Control\Controller;
use SilverStripe\Core\Config\Config;
use SilverStripe\Control\HTTPResponse;

/**
 * Controller for displaying the sitemap.xml. The module displays an index
 * sitemap at the sitemap.xml level, then outputs the individual objects
 * at a second level.
 *
 * <code>
 * http://site.com/sitemap.xml/
 * http://site.com/sitemap.xml/sitemap/$ClassName-$Page.xml
 * </code>
 *
 * @package googlesitemaps
 */
class GoogleSitemapController extends Controller
{

    /**
     * @var array
     */
    private static $allowed_actions = array(
        'index',
        'sitemap'
    );


    /**
     * Default controller action for the sitemap.xml file. Renders a index
     * file containing a list of links to sub sitemaps containing the data.
     *
     * @return mixed
     */
    public function index($url)
    {
        if (GoogleSitemap::enabled()) {
            $this->getResponse()->addHeader('Content-Type', 'application/xml; charset="utf-8"');
            $this->getResponse()->addHeader('X-Robots-Tag', 'noindex');

            $sitemaps = GoogleSitemap::inst()->getSitemaps();
            $this->extend('updateGoogleSitemaps', $sitemaps);

            return array(
                'Sitemaps' => $sitemaps
            );
        } else {
            return new HTTPResponse('Page not found', 404);
        }
    }

    /**
     * Specific controller action for displaying a particular list of links
     * for a class
     *
     * @return mixed
     */
    public function sitemap()
    {
        $class = $this->unsanitiseClassName($this->request->param('ID'));
        $page = $this->request->param('OtherID');

        if (GoogleSitemap::enabled() && $class && $page && ($class == SiteTree::class || $class == 'GoogleSitemapRoute' || GoogleSitemap::is_registered($class))) {
            $this->getResponse()->addHeader('Content-Type', 'application/xml; charset="utf-8"');
            $this->getResponse()->addHeader('X-Robots-Tag', 'noindex');

            $items = GoogleSitemap::inst()->getItems($class, $page);
            $this->extend('updateGoogleSitemapItems', $items, $class, $page);

            return array(
                'Items' => $items
            );
        } else {
            return new HTTPResponse('Page not found', 404);
        }
    }

    /**
     * Unsanitise a namespaced class' name from a URL param
     * @return string
     */
    protected function unsanitiseClassName($class)
    {
        return str_replace('-', '\\', $class);
    }
}
