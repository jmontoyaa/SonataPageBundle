<?php

/*
 * This file is part of the Sonata project.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace Sonata\PageBundle\Listener;

use Sonata\PageBundle\CmsManager\CmsManagerSelectorInterface;
use Sonata\PageBundle\Site\SiteSelectorInterface;

use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpFoundation\Request;

/**
 * This class redirect the onCoreResponse event to the correct
 * cms manager upon user permission
 */
class ResponseListener
{
    protected $cmsSelector;

    /**
     * @param \Sonata\PageBundle\CmsManager\CmsManagerSelectorInterface $cmsSelector
     */
    public function __construct(CmsManagerSelectorInterface $cmsSelector)
    {
        $this->cmsSelector = $cmsSelector;
    }

    /**
     * Filter the `core.response` event to decorated the action
     *
     * @param \Symfony\Component\HttpKernel\Event\FilterResponseEvent $event
     * @return void
     */
    public function onCoreResponse(FilterResponseEvent $event)
    {
        $cms = $this->cmsSelector->retrieve();

        $page = $cms->getCurrentPage();

        // only decorate hybrid page and page with decorate = true
        if (!$page || !$page->isHybrid() || !$page->getDecorate()) {
            return;
        }

        $response = $event->getResponse();

        if (!$cms->isDecorable($event->getRequest(), $event->getRequestType(), $response)) {
            return;
        }

        $response = $cms->renderPage($page, array('content' => $response->getContent()), $response);

        $event->setResponse($response);
    }
}